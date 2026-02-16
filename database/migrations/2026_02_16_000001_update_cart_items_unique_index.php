<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_product_id_unique');
            $table->unique(['guest_cart_token', 'product_id'], 'cart_items_guest_product_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_guest_product_unique');
            $table->unique(['product_id'], 'cart_items_product_id_unique');
        });
    }
};
