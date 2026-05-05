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
        Schema::table('addons', function (Blueprint $table): void {
            $table->foreignId('inventory_id')
                ->nullable()
                ->after('price')
                ->constrained('inventories')
                ->nullOnDelete();
            $table->decimal('inventory_usage_qty', 10, 2)
                ->default(1)
                ->after('inventory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addons', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('inventory_id');
            $table->dropColumn('inventory_usage_qty');
        });
    }
};
