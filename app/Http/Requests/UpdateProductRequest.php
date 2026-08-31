<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'price' => [
                'sometimes',
                'numeric',
                'min:0.01',
            ],

            'stock' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'The product name cannot exceed 255 characters.',

            'price.numeric' => 'The product price must be a valid number.',
            'price.min' => 'The product price must be greater than zero.',

            'stock.integer' => 'The product stock must be an integer.',
            'stock.min' => 'The product stock cannot be negative.',
        ];
    }
}