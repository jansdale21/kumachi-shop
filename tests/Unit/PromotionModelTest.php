<?php

namespace Tests\Unit;

use App\Models\Promotion;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_by_code_scope_is_case_insensitive_and_trims_input(): void
    {
        $target = Promotion::query()->create([
            'code' => 'SUMMER50',
            'discount_value' => 50,
            'expires_at' => null,
        ]);

        Promotion::query()->create([
            'code' => 'WELCOME10',
            'discount_value' => 10,
            'expires_at' => null,
        ]);

        $found = Promotion::query()->byCode('  summer50  ')->first();

        $this->assertNotNull($found);
        $this->assertTrue($found->is($target));
    }

    public function test_available_scope_only_returns_non_expired_promotions(): void
    {
        $now = CarbonImmutable::now();

        $noExpiry = Promotion::query()->create([
            'code' => 'NOEXP',
            'discount_value' => 20,
            'expires_at' => null,
        ]);
        $future = Promotion::query()->create([
            'code' => 'FUTURE',
            'discount_value' => 25,
            'expires_at' => $now->addDay(),
        ]);
        $expired = Promotion::query()->create([
            'code' => 'EXPIRED',
            'discount_value' => 30,
            'expires_at' => $now->subDay(),
        ]);

        $ids = Promotion::query()->available()->pluck('id');

        $this->assertTrue($ids->contains($noExpiry->id));
        $this->assertTrue($ids->contains($future->id));
        $this->assertFalse($ids->contains($expired->id));
    }

    public function test_available_scope_does_not_return_soft_deleted_promotions(): void
    {
        $active = Promotion::query()->create([
            'code' => 'ACTIVE',
            'discount_value' => 15,
            'expires_at' => CarbonImmutable::now()->addDays(2),
        ]);

        $deleted = Promotion::query()->create([
            'code' => 'DELETED',
            'discount_value' => 15,
            'expires_at' => CarbonImmutable::now()->addDays(2),
        ]);
        $deleted->delete();

        $ids = Promotion::query()->available()->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertFalse($ids->contains($deleted->id));
    }
}
