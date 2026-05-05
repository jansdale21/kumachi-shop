<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users',
            'addresses',
            'payment_methods',
            'addons',
            'categories',
            'products',
            'inventories',
            'suppliers',
            'promotions',
            'purchase_orders',
            'cart_items',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'addresses',
            'payment_methods',
            'addons',
            'categories',
            'products',
            'inventories',
            'suppliers',
            'promotions',
            'purchase_orders',
            'cart_items',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropSoftDeletes();
            });
        }
    }
};
