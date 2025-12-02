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
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'ewallet']);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
    }
};
