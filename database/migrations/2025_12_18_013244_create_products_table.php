<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Main Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->nullable()->default(0.00);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });

        // 2. Product Gallery Images
        Schema::create('product_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('url'); // Path to image
            $table->timestamps();
        });

        // 3. Product Attributes (Technical Details: Height, Material)
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->string('value');
            $table->timestamps();
        });

        // 4. Product Features (High Level: Easy clean, Big clouds)
        Schema::create('product_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('key'); // Title of feature
            $table->text('value'); // Description
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_features');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_gallery_images');
        Schema::dropIfExists('products');
    }
};
