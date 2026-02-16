<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $guestToken = $request->header('X-Cart-Token');
        if (!$guestToken) {
            return response()->json([
                'data' => [],
                'guest_cart_token' => null,
                'total' => 0.00,
                'meta' => [
                    'total_price' => 0.00,
                    'count' => 0,
                ],
            ]);
        }

        $cartItems = CartItem::query()
            ->with(['product', 'product.category'])
            ->forGuestToken($guestToken)
            ->get();

        $total = $cartItems->sum(function ($item) {
            return round(((float) ($item->product->price ?? 0)) * $item->quantity, 2);
        });

        return response()->json([
            'data' => CartItemResource::collection($cartItems),
            'guest_cart_token' => $guestToken,
            'total' => round($total, 2),
            'meta' => [
                'total_price' => round($total, 2),
                'count' => (int) $cartItems->sum('quantity'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $guestToken = $request->header('X-Cart-Token');
        if (!$guestToken) {
            return response()->json([
                'message' => 'Cart token is missing.',
            ], 422);
        }

        $validated = $validator->validated();
        $productId = $validated['product_id'];
        $quantity = $validated['quantity'];

        Product::findOrFail($productId);

        $cartItem = CartItem::query()
            ->forGuestToken($guestToken)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'guest_cart_token' => $guestToken,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return response()->json([
            'message' => 'Item added to cart.',
            'item' => new CartItemResource($cartItem->load('product.category')),
            'guest_cart_token' => $guestToken,
        ], 201);
    }

    public function update(Request $request, int $cartItemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $guestToken = $request->header('X-Cart-Token');
        if (!$guestToken) {
            return response()->json([
                'message' => 'Cart token is missing.',
            ], 422);
        }

        $cartItem = CartItem::query()
            ->forGuestToken($guestToken)
            ->find($cartItemId);

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        $cartItem->quantity = (int) $request->input('quantity');
        $cartItem->save();

        return new CartItemResource($cartItem->load('product.category'));
    }

    public function destroy(Request $request, int $cartItemId)
    {
        $guestToken = $request->header('X-Cart-Token');
        if (!$guestToken) {
            return response()->json([
                'message' => 'Cart token is missing.',
            ], 422);
        }

        $cartItem = CartItem::query()
            ->forGuestToken($guestToken)
            ->find($cartItemId);

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        $cartItem->delete();

        return response()->json(null, 204);
    }

    public function clear(Request $request)
    {
        $guestToken = $request->header('X-Cart-Token');
        if (!$guestToken) {
            return response()->json([
                'message' => 'Cart token is missing.',
            ], 422);
        }

        CartItem::query()
            ->forGuestToken($guestToken)
            ->delete();

        return response()->json([
            'data' => [],
            'guest_cart_token' => $guestToken,
            'total' => 0.00,
            'meta' => [
                'total_price' => 0.00,
                'count' => 0,
            ],
        ]);
    }
}
