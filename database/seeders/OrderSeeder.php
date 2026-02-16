<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        if ($products->count() === 0) return;

        // Create 5 dummy orders
        for ($i = 0; $i < 5; $i++) {
            $selectedProducts = $products->random(rand(1, 3));
            $subtotal = 0;

            $order = Order::create([
                'subtotal' => 0, // Will update below
                'shipping_cost' => 15.00,
                'fees_cost' => 0,
                'total' => 0, // Will update below
                'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
                'tracking_code' => rand(0, 1) ? 'TRK-' . rand(10000, 99999) : null,
                'customer_name' => fake()->name('ar_SA'),
                'customer_phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'city' => fake()->city(),
                'customer_email' => fake()->email(),
            ]);

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                $price = $product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'price' => $price,
                    'cover_image' => $product->cover_image,
                ]);

                $subtotal += ($price * $qty);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + 15.00
            ]);
        }
    }
}
