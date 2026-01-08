<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Items
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',

            // Customer Info (Nested object in request, flattened in DB)
            'customer' => 'required|array',
            'customer.name' => 'required|string|max:255',
            'customer.phone' => 'required|string|max:20',
            'customer.address' => 'required|string',
            'customer.city' => 'required|string|max:100',
            'customer.email' => 'nullable|email|max:255',

            // Optional: You might want to validate shipping cost if dynamic
            // 'shipping_cost' => 'nullable|numeric|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'سلة التسوق فارغة.',
            'items.*.product_id.exists' => 'أحد المنتجات المختارة غير متوفر.',
            'customer.name.required' => 'اسم العميل مطلوب.',
            'customer.phone.required' => 'رقم الهاتف مطلوب.',
            'customer.address.required' => 'العنوان مطلوب.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'بيانات الطلب غير صالحة',
            'errors' => $validator->errors()
        ], 422));
    }
}
