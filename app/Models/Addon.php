<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'inventory_id',
        'inventory_usage_qty',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'inventory_usage_qty' => 'decimal:2',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_addons')
            ->withTimestamps();
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
