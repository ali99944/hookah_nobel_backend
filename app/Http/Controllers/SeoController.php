<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeoResource;
use App\Models\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SeoController extends Controller
{
    // For the control panel list page
    public function index()
    {
        return SeoResource::collection(Seo::all());
    }


    // For the public-facing website to fetch SEO by key
    public function show(string $key)
    {
        $seo = Seo::where('key', $key)->firstOrFail();
        return new SeoResource($seo);
    }

    // The ONLY update endpoint
    public function update(Request $request, Seo $seo)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'keywords' => 'nullable|string|max:255',

            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|max:2048', // Validate if a new image is uploaded
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'keywords' => $validated['keywords'] ?? null,

            'og_title' => $validated['og_title'] ?? $validated['title'],
            'og_description' => $validated['og_description'] ?? $validated['description'],
        ];

        if ($request->hasFile('og_image')) {
            // Delete old image if it exists
            if ($seo->og_image) {
                Storage::disk('public')->delete($seo->og_image);
            }
            // Store new one
            $updateData['og_image'] = $request->file('og_image')->store('seo', 'public');
        }

        $seo->update($updateData);

        return new SeoResource($seo);
    }
}
