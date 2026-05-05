<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        $productId = $product?->id;

        return [
            'product_size_id' => [
                'required',
                'integer',
                Rule::exists('product_sizes', 'id')->where(function ($query) use ($productId) {
                    if ($productId) {
                        $query->where('product_id', $productId);
                    }
                }),
            ],
            'sugar_level' => ['required', 'integer', 'min:0', 'max:100'],
            'ice_level' => ['required', 'integer', 'min:0', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', Rule::exists('addons', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_size_id.required' => 'Please select a size.',
            'product_size_id.exists' => 'The selected size is invalid.',
            'sugar_level.required' => 'Please choose a sugar level.',
            'ice_level.required' => 'Please choose an ice level.',
            'quantity.required' => 'Please provide quantity.',
        ];
    }
}
