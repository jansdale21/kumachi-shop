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
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('base_unit', 20)->default('unit')->after('unit');
            $table->decimal('units_per_stock_unit', 12, 4)->default(1)->after('base_unit');
        });

        DB::table('inventories')->update([
            'base_unit' => DB::raw('unit'),
            'units_per_stock_unit' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['base_unit', 'units_per_stock_unit']);
        });
    }
};
