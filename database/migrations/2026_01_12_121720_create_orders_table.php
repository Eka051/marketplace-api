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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->foreignUuid('user_id')->constrained('users', 'user_id');
            $table->foreignId('shop_id')->constrained('shops', 'shop_id');
            $table->string('order_number')->unique();
            $table->enum('status', ['unpaid', 'paid', 'processing', 'shipped', 'completed', 'cancelled']);
            $table->integer('total_price');
            $table->integer('shipping_cost');
            $table->integer('grand_total');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
