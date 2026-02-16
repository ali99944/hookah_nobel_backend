<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()
            ->with('items')
            ->latest();

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if ($status === 'paid') {
                $query->where('is_paid', true)->where('status', 'pending');
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $searchQuery->orWhere('id', (int) $search);
                }
            });
        }

        $perPage = (int) $request->input('limit', $request->input('per_page', 10));
        $perPage = max(1, min($perPage, 100));

        return OrderResource::collection($query->paginate($perPage));
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load('items.product'));
    }

    public function store(CreateOrderRequest $request)
    {
        $guestToken = $request->header('X-Cart-Token');
        if (!$guestToken) {
            return response()->json([
                'message' => 'Cart token is missing.',
            ], 422);
        }

        $validated = $request->validated();

        $cartItems = CartItem::query()
            ->with('product')
            ->forGuestToken($guestToken)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty.',
            ], 400);
        }

        $subtotal = 0.00;
        $orderItemsPayload = [];

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;

            if (!$product) {
                return response()->json([
                    'message' => "Product with id {$cartItem->product_id} is unavailable.",
                ], 422);
            }

            if ($product->stock < $cartItem->quantity) {
                return response()->json([
                    'message' => "Insufficient stock for '{$product->name}'.",
                    'available_stock' => $product->stock,
                ], 422);
            }

            $lineTotal = round(((float) $product->price) * $cartItem->quantity, 2);
            $subtotal += $lineTotal;

            $orderItemsPayload[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $cartItem->quantity,
                'price' => $product->price,
                'cover_image' => $product->cover_image,
            ];
        }

        $shippingCost = 0.00;
        $feesCost = 0.00;
        $total = round($subtotal + $shippingCost + $feesCost, 2);

        $order = DB::transaction(function () use (
            $validated,
            $orderItemsPayload,
            $cartItems,
            $guestToken,
            $subtotal,
            $shippingCost,
            $feesCost,
            $total
        ) {
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'],
                'status' => 'pending',
                'is_paid' => false,
                'tracking_code' => Order::generateTrackingCode(),
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'fees_cost' => $feesCost,
                'total' => $total,
                'address' => $validated['address'],
                'city' => $validated['city'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $order->items()->createMany($orderItemsPayload);

            foreach ($cartItems as $cartItem) {
                if ($cartItem->product) {
                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }
            }

            CartItem::query()
                ->forGuestToken($guestToken)
                ->delete();

            return $order->load('items.product');
        });

        return response()->json([
            'message' => 'Order submitted successfully.',
            'tracking_code' => $order->tracking_code,
            'tracking_number' => $order->tracking_code,
            'data' => (new OrderResource($order))->resolve(),
        ], 201);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $validated = $request->validated();

        if (empty($validated)) {
            return response()->json([
                'message' => 'Nothing to update.',
            ], 422);
        }

        DB::transaction(function () use ($order, $validated) {
            $fillableOrderUpdates = Arr::only($validated, [
                'customer_name',
                'customer_phone',
                'customer_email',
                'address',
                'city',
                'notes',
            ]);

            if (!empty($fillableOrderUpdates)) {
                $order->fill($fillableOrderUpdates);
            }

            if (array_key_exists('status', $validated)) {
                if ($validated['status'] === 'paid') {
                    $order->is_paid = true;
                } else {
                    $order->status = $validated['status'];
                }
            }

            if (array_key_exists('is_paid', $validated)) {
                $order->is_paid = (bool) $validated['is_paid'];
            }

            if (array_key_exists('tracking_number', $validated)) {
                $order->tracking_code = $validated['tracking_number'] ?: null;
            } elseif (array_key_exists('tracking_code', $validated)) {
                $order->tracking_code = $validated['tracking_code'] ?: null;
            }

            if (array_key_exists('items', $validated)) {
                $existingItems = $order->items()
                    ->with('product')
                    ->get()
                    ->keyBy('id');

                $incomingItems = collect($validated['items']);
                $incomingItemIds = $incomingItems->pluck('id')->map(fn ($id) => (int) $id);

                $removedItems = $existingItems->reject(function ($item) use ($incomingItemIds) {
                    return $incomingItemIds->contains((int) $item->id);
                });

                foreach ($removedItems as $removedItem) {
                    if ($removedItem->product) {
                        $removedItem->product->increment('stock', $removedItem->quantity);
                    }
                    $removedItem->delete();
                }

                foreach ($incomingItems as $incomingItem) {
                    /** @var OrderItem|null $item */
                    $item = $existingItems->get((int) $incomingItem['id']);
                    if (!$item) {
                        throw ValidationException::withMessages([
                            'items' => ["Item #{$incomingItem['id']} does not belong to this order."],
                        ]);
                    }

                    $newQuantity = (int) $incomingItem['quantity'];
                    $newPrice = (float) $incomingItem['price'];
                    $oldQuantity = (int) $item->quantity;
                    $quantityDiff = $newQuantity - $oldQuantity;

                    if ($quantityDiff > 0 && $item->product) {
                        if ($item->product->stock < $quantityDiff) {
                            throw ValidationException::withMessages([
                                'items' => ["Insufficient stock for '{$item->product_name}'."],
                            ]);
                        }
                        $item->product->decrement('stock', $quantityDiff);
                    } elseif ($quantityDiff < 0 && $item->product) {
                        $item->product->increment('stock', abs($quantityDiff));
                    }

                    $item->quantity = $newQuantity;
                    $item->price = $newPrice;
                    $item->save();
                }

                $order->load('items');
                $subtotal = $order->items->sum(function ($item) {
                    return ((float) $item->price) * ((int) $item->quantity);
                });

                $order->subtotal = round($subtotal, 2);
                $order->total = round($order->subtotal + ((float) $order->shipping_cost) + ((float) $order->fees_cost), 2);
            }

            $order->save();
        });

        return new OrderResource($order->fresh(['items.product']));
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    public function track(string $trackingCode)
    {
        $order = Order::query()
            ->with('items.product')
            ->where('tracking_code', $trackingCode)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        return new OrderResource($order);
    }
}
