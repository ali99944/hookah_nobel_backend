<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'address' => $this->input('address', $this->input('shipping_info.address')),
            'city' => $this->input('city', $this->input('shipping_info.city')),
            'notes' => $this->input('notes', $this->input('shipping_info.notes')),
            'customer_phone' => $this->input('customer_phone', $this->input('phone_number')),
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'address' => 'required|string|max:1000',
            'city' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Customer name is required.',
            'customer_phone.required' => 'Phone number is required.',
            'address.required' => 'Address is required.',
            'city.required' => 'City is required.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Order data is invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
