<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'availability')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->string('availability')->default('available')->after('base_price');
            });
        }

        // Backfill from the old `stock_quantity` column if it still exists (older DBs).
        if (Schema::hasColumn('products', 'stock_quantity')) {
            DB::statement("
                UPDATE products
                SET availability = CASE
                    WHEN stock_quantity > 0 THEN 'available'
                    ELSE 'unavailable'
                END
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'availability')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('availability');
            });
        }
    }
};

