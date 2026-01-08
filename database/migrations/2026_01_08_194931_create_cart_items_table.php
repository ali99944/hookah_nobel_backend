<?php

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->string('guest_cart_token')->nullable()->index(); // UUID for better uniqueness
            $table->foreignIdFor(Product::class)->constrained()->onDelete('cascade');

            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // Ensure a cart item belongs to either a customer or a guest token
            $table->unique(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
