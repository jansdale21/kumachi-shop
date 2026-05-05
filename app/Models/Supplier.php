<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    public function inventoryItems(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'supplier_items')
            ->withTimestamps();
    }
}
