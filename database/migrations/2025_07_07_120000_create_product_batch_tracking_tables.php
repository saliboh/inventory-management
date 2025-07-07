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
        // Create a table to track batches of product entries with their prices
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_movement_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_remaining');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
        });

        // Create a table to track which batches were used for exits
        Schema::create('product_exit_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_movement_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_taken');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_exit_batches');
        Schema::dropIfExists('product_batches');
    }
};
