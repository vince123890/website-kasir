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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action'); // login, logout, create, update, delete, etc.
            $table->string('model_type')->nullable(); // Model class name (e.g., App\Models\Product)
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the model
            $table->text('description')->nullable(); // Human-readable description
            $table->json('properties')->nullable(); // Old values, new values, etc.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device')->nullable(); // browser, OS
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
