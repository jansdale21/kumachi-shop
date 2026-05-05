<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_name',
        'unit',
        'base_unit',
        'units_per_stock_unit',
        'quantity',
        'reorder_level',
    ];

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_items')
            ->withTimestamps();
    }

    /**
     * Total on-hand amount expressed in {@see $this->base_unit}.
     */
    public function quantityInBaseUnits(): float
    {
        return (float) $this->quantity * (float) $this->units_per_stock_unit;
    }

    /**
     * Reorder threshold expressed in {@see $this->base_unit}.
     */
    public function reorderLevelInBaseUnits(): float
    {
        return (float) $this->reorder_level * (float) $this->units_per_stock_unit;
    }

    /**
     * Convert a consumption amount (in base units) into stock units for decrement/increment.
     */
    public function baseUnitsToStockUnits(float $baseAmount): float
    {
        $factor = (float) $this->units_per_stock_unit;

        if ($factor <= 0) {
            return 0.0;
        }

        return $baseAmount / $factor;
    }
}

