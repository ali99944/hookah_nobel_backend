<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // GET /api/orders (Admin: All, User: Own)
    public function index(Request $request)
    {
        // $query = Order::with('items');

        // // Filter by status
        // if ($request->has('status')) {
        //     $query->where('status', $request->status);
        // }

        // // Search by customer name or ID
        // if ($request->has('search')) {
        //     $search = $request->search;
        //     $query->where(function($q) use ($search) {
        //         $q->where('id', 'like', "%$search%")
        //           ->orWhere('customer_name', 'like', "%$search%")
        //           ->orWhere('customer_phone', 'like', "%$search%");
        //     });
        // }

        // $query->latest();

        // return OrderResource::collection($query->paginate($request->get('per_page', 10)));

        $orders = Order::all();

        return OrderResource::collection($orders);
    }

    // POST /api/orders
    public function store(CreateOrderRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            $subtotal = 0;
            $orderItemsData = [];

            // 1. Calculate Totals & Prepare Items (Server-side validation of price)
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock (Optional but recommended)
                if ($product->stock < $item['quantity']) {
                    abort(422, "الكمية المطلوبة لـ {$product->name} غير متوفرة.");
                }

                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;

                // Snapshot data for OrderItem
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'cover_image' => $product->cover_image, // Stored path
                ];

                // Decrement stock
                $product->decrement('stock', $item['quantity']);
            }

            $shippingCost = 10.00; // This logic can be dynamic based on city/address
            $total = $subtotal + $shippingCost;

            // 2. Create Order
            $order = Order::create([
                'user_id' => $request->user('sanctum')?->id, // If authenticated
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => 'pending',

                // Flatten Customer Info
                'customer_name' => $data['customer']['name'],
                'customer_phone' => $data['customer']['phone'],
                'customer_address' => $data['customer']['address'],
                'customer_city' => $data['customer']['city'],
                'customer_email' => $data['customer']['email'] ?? null,
            ]);

            // 3. Save Items
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return new OrderResource($order->load('items'));
        });
    }

    // GET /api/orders/{id}
    public function show(Order $order)
    {
        return new OrderResource($order->load('items'));
    }

    // PUT /api/orders/{id} (Admin: Update Status/Tracking)
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());
        return new OrderResource($order->load('items'));
    }

    // DELETE /api/orders/{id} (Admin)
    public function destroy(Order $order)
    {
        $order->delete(); // Items cascade delete via Migration
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
