<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'discount_value',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->whereRaw('LOWER(code) = ?', [strtolower(trim($code))]);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function (Builder $inner): void {
            $inner->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now());
        });
    }
}
