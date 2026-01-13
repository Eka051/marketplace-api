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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('payment_id')->primary();
            $table->foreignId('order_id')->constrained('orders', 'order_id');
            $table->string('external_id')->nullable();
            $table->string('method');
            $table->enum('status', ['pending', 'success', 'failed', 'expired']);
            $table->integer('amount');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
