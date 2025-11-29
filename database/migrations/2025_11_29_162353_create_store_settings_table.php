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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->onDelete('cascade');
            $table->json('operating_hours')->nullable();
            $table->boolean('tax_enabled')->default(false);
            $table->string('tax_name')->default('PPN');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('tax_calculation', ['inclusive', 'exclusive'])->default('inclusive');
            $table->decimal('markup_percentage', 5, 2)->default(0);
            $table->enum('rounding_rule', ['none', '100', '500', '1000'])->default('none');
            $table->decimal('max_discount_per_item', 5, 2)->default(0);
            $table->decimal('max_discount_per_transaction', 5, 2)->default(0);
            $table->decimal('discount_requires_approval_above', 5, 2)->default(0);
            $table->boolean('auto_print_receipt')->default(false);
            $table->timestamps();

            $table->index('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
