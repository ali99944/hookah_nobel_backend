<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',

            // Images
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,webp|max:3072',

            // Attributes (Array of Objects)
            'attributes' => 'nullable|array',
            'attributes.*.key' => 'required_with:attributes|string|max:255',
            'attributes.*.value' => 'required_with:attributes|string|max:255',

            // Features (Array of Objects)
            'features' => 'nullable|array',
            'features.*.key' => 'required_with:features|string|max:255',
            'features.*.value' => 'required_with:features|string',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'التصنيف المختار غير صالح.',
            'cover_image.required' => 'صورة الغلاف مطلوبة.',
            'attributes.*.key.required_with' => 'اسم الخاصية مطلوب.',
        ];
    }

    protected function prepareForValidation()
    {
        // When sending arrays via FormData (e.g. from React), they might come as JSON strings
        // We decode them here so validation rules work
        if ($this->attributes && is_string($this->attributes)) {
            $this->merge(['attributes' => json_decode($this->attributes, true)]);
        }
        if ($this->features && is_string($this->features)) {
            $this->merge(['features' => json_decode($this->features, true)]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'بيانات المنتج غير صالحة',
            'errors' => $validator->errors()
        ], 422));
    }
}

