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
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('stock_alerts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->timestamps();
        });

        Schema::create('stock_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_quantity', 10, 2);
            $table->decimal('reorder_level', 10, 2);
            $table->timestamps();
        });
    }
};
