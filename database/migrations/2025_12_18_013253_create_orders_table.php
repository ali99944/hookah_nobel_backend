<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Financials
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('fees_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // Status & Tracking
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->string('tracking_code')->nullable();

            // Customer Info (Stored directly on order to preserve history if user changes address later)
            $table->string('customer_name');
            $table->string('customer_phone');

            // used for checkout invoices and notifications, not necessarily the same as the user's email if they are logged in
            $table->string('customer_email')->nullable();


            $table->text('address');
            $table->string('city');
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        // 2. Order Items Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null'); // Keep record even if product deleted

            // Snapshot data
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Unit price at time of purchase
            $table->string('cover_image')->nullable(); // Snapshot of image path

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
