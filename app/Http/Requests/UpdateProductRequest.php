<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        Log::channel('server_debug') -> info($this);
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($this->product->id)],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive,draft',

            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',

            // New gallery images
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:3072',

            // IDs of images to KEEP or DELETE?
            // Usually easier to send "deleted_gallery_ids" array
            'deleted_gallery_ids' => 'nullable|array',
            'deleted_gallery_ids.*' => 'integer|exists:product_gallery_images,id',

            'attributes' => 'nullable|array',
            'attributes.*.key' => 'required_with:attributes|string|max:255',
            'attributes.*.value' => 'required_with:attributes|string|max:255',

            'features' => 'nullable|array',
            'features.*.key' => 'required_with:features|string|max:255',
            'features.*.value' => 'required_with:features|string',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'الرابط المستخدم موجود مسبقاً.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->attributes && is_string($this->attributes)) {
            $this->merge(['attributes' => json_decode($this->attributes, true)]);
        }
        if ($this->features && is_string($this->features)) {
            $this->merge(['features' => json_decode($this->features, true)]);
        }
        if ($this->deleted_gallery_ids && is_string($this->deleted_gallery_ids)) {
            $this->merge(['deleted_gallery_ids' => json_decode($this->deleted_gallery_ids, true)]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'بيانات التحديث غير صالحة',
            'errors' => $validator->errors()
        ], 422));
    }
}
