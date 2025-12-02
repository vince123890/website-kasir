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
        Schema::create('store_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_register_id')->nullable()->constrained('cash_registers')->nullOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_number', 50)->unique();
            $table->date('session_date');
            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('closing_cash', 15, 2)->nullable();
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('actual_cash', 15, 2)->nullable();
            $table->decimal('variance', 15, 2)->nullable();
            $table->text('variance_reason')->nullable();
            $table->enum('status', ['open', 'closed', 'pending_approval', 'approved'])->default('open');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'session_date']);
            $table->index(['cashier_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_sessions');
    }
};
