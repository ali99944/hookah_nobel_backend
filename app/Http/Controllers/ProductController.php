<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category'); // Eager load category

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $query->latest();

        // Return all products without pagination
        return ProductResource::collection($query->get());
    }

    public function store(CreateProductRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data) {
            // 1. Handle Cover Image
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('products/covers', 'public');
            }

            // 2. Create Product
            $product = Product::create($data);

            // 3. Handle Gallery Images
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    $path = $image->store('products/gallery', 'public');
                    $product->gallery()->create(['url' => $path]);
                }
            }

            // 4. Handle Attributes
            if (!empty($data['attributes'])) {
                $product->attributes()->createMany($data['attributes']);
            }

            // 5. Handle Features
            if (!empty($data['features'])) {
                $product->features()->createMany($data['features']);
            }

            return new ProductResource($product->load(['gallery', 'attributes', 'features']));
        });
    }

    public function show($id)
    {
        $product = Product::with(['category', 'gallery', 'attributes', 'features'])->findOrFail($id);
        return new ProductResource($product);
        // return new ProductResource($product->load(['category', 'gallery', 'attributes', 'features']));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        Log::channel('server_debug') -> info($request->validated());
        // Log::channel('custom_payment') -> info($request->all());
        $data = $request->validated();

        return DB::transaction(function () use ($request, $product, $data) {
            // 1. Update Cover Image
            if ($request->hasFile('cover_image')) {
                if ($product->cover_image) {
                    Storage::disk('public')->delete($product->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('products/covers', 'public');
            }

            $product->update($data);

            // 2. Add NEW Gallery Images
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    $path = $image->store('products/gallery', 'public');
                    $product->gallery()->create(['url' => $path]);
                }
            }

            // 3. Delete REMOVED Gallery Images
            if (!empty($data['deleted_gallery_ids'])) {
                $imagesToDelete = $product->gallery()->whereIn('id', $data['deleted_gallery_ids'])->get();
                foreach ($imagesToDelete as $img) {
                    Storage::disk('public')->delete($img->url);
                    $img->delete();
                }
            }

            // 4. Sync Attributes (Delete all and recreate is simplest for this logic)
            if (isset($data['attributes'])) {
                $product->attributes()->delete();
                $product->attributes()->createMany($data['attributes']);
            }

            // 5. Sync Features
            if (isset($data['features'])) {
                $product->features()->delete();
                $product->features()->createMany($data['features']);
            }

            return new ProductResource($product->fresh(['gallery', 'attributes', 'features', 'category']));
        });
    }

    public function destroy(Product $product)
    {
        return DB::transaction(function () use ($product) {
            // Delete Cover
            if ($product->cover_image) {
                Storage::disk('public')->delete($product->cover_image);
            }

            // Delete Gallery Files
            foreach ($product->gallery as $img) {
                Storage::disk('public')->delete($img->url);
            }

            // Relations cascade delete via DB, but files need manual cleanup
            $product->delete();

            return response()->json(['message' => 'Product deleted successfully']);
        });
    }
}
