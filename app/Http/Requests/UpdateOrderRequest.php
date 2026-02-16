<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('tracking_code') && !$this->filled('tracking_number')) {
            $this->merge([
                'tracking_number' => $this->input('tracking_code'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|in:pending,paid,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_code' => 'nullable|string|max:255',
            'is_paid' => 'nullable|boolean',

            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',

            'items' => 'nullable|array|min:1',
            'items.*.id' => 'required_with:items|integer|exists:order_items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Order update data is invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
