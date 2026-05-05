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
            $table->string('unit', 20)->default('unit')->after('item_name');
        });

        DB::table('inventories')
            ->whereIn('item_name', [
                'Milk - Whole',
                'Milk - Skim',
                'Almond Milk',
                'Oat Milk',
                'Soy Milk',
                'Cream',
                'Whipped Cream',
                'Honey',
                'Vanilla Syrup',
                'Caramel Syrup',
                'Hazelnut Syrup',
                'Espresso Shot',
            ])
            ->update(['unit' => 'ml']);

        DB::table('inventories')
            ->whereIn('item_name', [
                'Coffee Beans - Arabica',
                'Coffee Beans - Robusta',
                'Sugar',
            ])
            ->update(['unit' => 'g']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
