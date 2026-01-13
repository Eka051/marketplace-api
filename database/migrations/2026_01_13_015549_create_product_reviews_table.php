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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->ulid('review_id')->primary();
            $table->foreignUlid('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('products', 'product_id')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items', 'item_id')->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
