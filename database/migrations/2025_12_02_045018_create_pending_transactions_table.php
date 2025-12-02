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
        Schema::create('pending_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->string('hold_number', 50)->unique();
            $table->json('transaction_data');
            $table->timestamp('held_at');
            $table->timestamps();

            $table->index(['store_id', 'cashier_id']);
            $table->index('held_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_transactions');
    }
};
