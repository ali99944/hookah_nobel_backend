<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware should handle admin check
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,paid,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'بيانات التحديث غير صالحة',
            'errors' => $validator->errors()
        ], 422));
    }
}
