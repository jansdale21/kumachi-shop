<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('unit', 20)->default('unit')->after('inventory_id');
        });

        $inventoryUnits = DB::table('inventories')
            ->pluck('unit', 'id');

        DB::table('purchase_order_items')
            ->select(['id', 'inventory_id'])
            ->orderBy('id')
            ->get()
            ->each(function (object $item) use ($inventoryUnits): void {
                DB::table('purchase_order_items')
                    ->where('id', $item->id)
                    ->update([
                        'unit' => (string) ($inventoryUnits[$item->inventory_id] ?? 'unit'),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
