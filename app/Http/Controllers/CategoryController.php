<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /api/categories
    public function index(Request $request)
    {
        $limit = $request->query('limit');

        if ($limit) {
            $categories = Category::latest()->limit($limit)->get();
        } else {
            $categories = Category::latest()->get();
        }

        return CategoryResource::collection($categories);
    }

    // POST /api/categories
    public function store(CreateCategoryRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = ImageService::storeUnsafe(
                $request->file('image'),
                'categories'
            );
        }

        $category = Category::create($data);

        return new CategoryResource($category);
    }

    // GET /api/categories/{id}
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    // PUT/PATCH /api/categories/{id}
    // Note: For file uploads with PUT, sometimes using POST with _method=PUT is safer in Laravel
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = ImageService::storeUnsafe(
                $request->file('image'),
                'categories'
            );
        }

        $category->update($data);

        return new CategoryResource($category);
    }

    // DELETE /api/categories/{id}
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
    
    public function products ($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        return CategoryResource::collection($category->products()->paginate(3));
    }
}
