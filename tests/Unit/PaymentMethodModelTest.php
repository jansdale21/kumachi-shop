<?php

namespace Tests\Unit;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_method_casts_expiry_fields_and_default_flag(): void
    {
        $user = User::factory()->create();
        $method = PaymentMethod::query()->create([
            'user_id' => $user->id,
            'label' => 'Primary Visa',
            'cardholder_name' => 'Test User',
            'card_brand' => 'visa',
            'card_last4' => '4242',
            'cvv_hash' => 'hashed',
            'exp_month' => '8',
            'exp_year' => '2032',
            'is_default' => '1',
        ])->fresh();

        $this->assertIsInt($method->exp_month);
        $this->assertIsInt($method->exp_year);
        $this->assertIsBool($method->is_default);
        $this->assertTrue($method->is_default);
    }

    public function test_default_payment_method_relation_returns_only_default_record(): void
    {
        $user = User::factory()->create();

        PaymentMethod::query()->create([
            'user_id' => $user->id,
            'label' => 'Backup Card',
            'cardholder_name' => 'Test User',
            'card_brand' => 'visa',
            'card_last4' => '1111',
            'cvv_hash' => 'hashed',
            'exp_month' => 7,
            'exp_year' => 2031,
            'is_default' => false,
        ]);

        $defaultMethod = PaymentMethod::query()->create([
            'user_id' => $user->id,
            'label' => 'Default Card',
            'cardholder_name' => 'Test User',
            'card_brand' => 'mastercard',
            'card_last4' => '2222',
            'cvv_hash' => 'hashed',
            'exp_month' => 9,
            'exp_year' => 2033,
            'is_default' => true,
        ]);

        $resolvedDefault = $user->fresh()->defaultPaymentMethod;

        $this->assertNotNull($resolvedDefault);
        $this->assertTrue($resolvedDefault->is($defaultMethod));
    }
}
