<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
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
        $orderType = (string) $this->input('order_type', 'pickup');
        $paymentMethod = (string) $this->input('payment_method', 'cash');
        $hasSavedPaymentMethod = filled($this->input('saved_payment_method_id'));
        $requiresNewCardFields = $paymentMethod === 'card' && ! $hasSavedPaymentMethod;

        return [
            'order_type' => ['required', 'string', Rule::in(['pickup', 'delivery'])],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'card'])],
            'promo_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::exists('promotions', 'code')->where(function ($query) {
                    $query->whereNull('deleted_at')
                        ->where(function ($innerQuery) {
                            $innerQuery->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', now());
                        });
                }),
            ],
            'redeem_points' => ['nullable', 'integer', 'min:0'],
            'saved_payment_method_id' => [
                'nullable',
                'integer',
                Rule::exists('payment_methods', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()?->id);
                }),
            ],
            'address_id' => [
                Rule::requiredIf($orderType === 'delivery' && empty($this->input('full_name'))),
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()?->id);
                }),
            ],
            'full_name' => [Rule::requiredIf($orderType === 'delivery' && empty($this->input('address_id'))), 'nullable', 'string', 'max:255'],
            'phone' => [Rule::requiredIf($orderType === 'delivery' && empty($this->input('address_id'))), 'nullable', 'string', 'max:20'],
            'street' => [Rule::requiredIf($orderType === 'delivery' && empty($this->input('address_id'))), 'nullable', 'string', 'max:255'],
            'city' => [Rule::requiredIf($orderType === 'delivery' && empty($this->input('address_id'))), 'nullable', 'string', 'max:255'],
            'save_new_card' => ['nullable', 'boolean'],
            'card_holder_name' => [Rule::requiredIf($requiresNewCardFields), 'nullable', 'string', 'max:255'],
            'card_number' => [Rule::requiredIf($requiresNewCardFields), 'nullable', 'string', 'regex:/^[0-9\s-]{12,25}$/'],
            'card_expiry' => [Rule::requiredIf($requiresNewCardFields), 'nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/?([0-9]{2}|[0-9]{4})$/'],
            'saved_card_cvv' => [
                Rule::requiredIf($paymentMethod === 'card' && $hasSavedPaymentMethod),
                'nullable',
                'string',
                'regex:/^[0-9]{3,4}$/',
            ],
            'card_cvv' => [
                Rule::requiredIf($requiresNewCardFields),
                'nullable',
                'string',
                'regex:/^[0-9]{3,4}$/',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $orderType = (string) $this->input('order_type', 'pickup');
            $paymentMethod = (string) $this->input('payment_method', 'cash');
            $user = $this->user();

            if (
                $user
                && $orderType === 'pickup'
                && $paymentMethod === 'cash'
                && (bool) $user->cash_on_pickup_restricted
            ) {
                $validator->errors()->add(
                    'payment_method',
                    'Cash on pickup is restricted for your account. Please use card payment.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $promoCode = strtoupper(trim((string) $this->input('promo_code', '')));

        $this->merge([
            'promo_code' => $promoCode === '' ? null : $promoCode,
        ]);
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'The address field is required.',
        ];
    }

    public function attributes(): array
    {
        return [
            'address_id' => 'address',
        ];
    }
}
