<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
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
            'scheduled_for' => ['required', 'date'],
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

            $scheduledForRaw = (string) $this->input('scheduled_for', '');
            try {
                $scheduledFor = Carbon::parse($scheduledForRaw, config('app.timezone'));
            } catch (\Throwable) {
                $scheduledFor = null;
            }

            if (! $scheduledFor) {
                $validator->errors()->add('scheduled_for', 'Please select a valid pickup/delivery time.');
                return;
            }

            $maxDays = (int) config('shop.schedule_max_days', 7);
            $now = now(config('app.timezone'));
            if ($scheduledFor->lessThan($now->copy()->subMinutes(1))) {
                $validator->errors()->add('scheduled_for', 'Scheduled time must be in the future.');
                return;
            }

            if ($scheduledFor->greaterThan($now->copy()->addDays($maxDays))) {
                $validator->errors()->add('scheduled_for', "Scheduled time must be within the next {$maxDays} days.");
                return;
            }

            $weekday = (int) $scheduledFor->dayOfWeekIso; // 1..7
            $hours = (array) config("shop.hours.{$weekday}", []);
            $open = (string) ($hours['open'] ?? '');
            $close = (string) ($hours['close'] ?? '');

            if ($open === '' || $close === '') {
                $validator->errors()->add('scheduled_for', 'Shop hours are not available for the selected date.');
                return;
            }

            $openAt = Carbon::parse($scheduledFor->format('Y-m-d')." {$open}", config('app.timezone'));
            $closeAt = Carbon::parse($scheduledFor->format('Y-m-d')." {$close}", config('app.timezone'));

            if ($scheduledFor->lt($openAt) || $scheduledFor->gt($closeAt)) {
                $validator->errors()->add('scheduled_for', "Please select a time within shop hours ({$open}–{$close}).");
                return;
            }

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
            'scheduled_for' => trim((string) $this->input('scheduled_for', '')) ?: null,
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
