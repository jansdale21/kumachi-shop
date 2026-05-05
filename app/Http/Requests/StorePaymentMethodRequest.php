<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
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
        $rules = [
            'label' => ['required', 'string', 'max:100'],
            'cardholder_name' => ['required', 'string', 'max:255'],
            'card_brand' => ['nullable', 'string', 'max:50'],
            'exp_month' => ['required', 'integer', 'between:1,12'],
            'exp_year' => ['required', 'integer', 'min:2024', 'max:2100'],
            'card_cvv' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
            'is_default' => ['nullable', 'boolean'],
        ];

        if ($this->isMethod('post')) {
            $rules['card_number'] = ['required', 'string', 'regex:/^[0-9\s-]{12,25}$/'];
        } else {
            $rules['card_number'] = ['nullable', 'string', 'regex:/^[0-9\s-]{12,25}$/'];
        }

        return $rules;
    }
}
