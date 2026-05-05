<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $promotionId = $this->route('promotion')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('promotions', 'code')->ignore($promotionId),
            ],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'expires_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $code = strtoupper(trim((string) $this->input('code')));

        $this->merge([
            'code' => $code,
        ]);
    }
}
