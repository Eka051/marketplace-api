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
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id('usage_id');
            $table->foreignUlid('voucher_id')->constrained('vouchers', 'voucher_id')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignUlid('order_id')->constrained('orders', 'order_id')->cascadeOnDelete();
            $table->integer('discount_amount');
            $table->timestamp('used_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
    }
};
