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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->string('transaction_number', 50)->unique();
            $table->date('transaction_date');
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_email', 100)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'ewallet', 'split'])->default('cash');
            $table->enum('status', ['completed', 'voided', 'pending', 'held'])->default('completed');
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'transaction_date']);
            $table->index(['cashier_id', 'status']);
            $table->index(['status', 'transaction_date']);
            $table->index('store_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
