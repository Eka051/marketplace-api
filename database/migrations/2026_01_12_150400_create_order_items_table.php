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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->foreignUlid('order_id')->constrained('orders', 'order_id')->cascadeOnDelete();
            $table->foreignId('sku_id')->nullable()->constrained('product_skus', 'sku_id')->nullOnDelete();
            // SNAPSHOT COLUMNS
            $table->string('product_name');
            $table->string('sku_code')->nullable();
            $table->string('variant_name');
            $table->integer('price');
            $table->integer('weight');
            $table->integer('quantity');
            $table->integer('sub_total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
