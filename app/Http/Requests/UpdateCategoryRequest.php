<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // Unique rule ignores the current category ID during update
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($this->category)],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean', // In FormData this might come as "1" or "0", Laravel handles it usually, but sometimes validation needs 'in:0,1,true,false'
        ];
    }

    protected function prepareForValidation()
    {
        // Handle string "true"/"false" or "1"/"0" from FormData
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
