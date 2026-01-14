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
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('product_id')->primary();
            $table->string('name');
            $table->integer('price');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUlid('shop_id')->constrained('shops', 'shop_id')->cascadeOnDelete();
            $table->unique(['shop_id', 'slug'], 'uq_products_shop_slug');
            $table->foreignUlid('category_id')->nullable()
                ->constrained('categories', 'category_id')
                ->nullOnDelete();
            $table->foreignUlid('brand_id')->nullable()->constrained('brands', 'brand_id')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
