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
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('system_quantity')->default(0); // Quantity from stocks table
            $table->integer('physical_quantity')->default(0); // Actual counted quantity
            $table->integer('variance')->default(0); // physical - system
            $table->decimal('variance_percentage', 8, 2)->default(0); // variance / system * 100
            $table->string('variance_reason')->nullable(); // Required if variance > threshold
            $table->timestamps();

            // Indexes
            $table->index('stock_opname_id');
            $table->index('product_id');
            $table->unique(['stock_opname_id', 'product_id']); // One product per opname
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};
