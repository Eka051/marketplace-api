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
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('category_id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops', 'shop_id')->cascadeOnDelete();
            $table->foreignUlid('parent_id')->nullable()
                ->constrained('categories', 'category_id')
                ->nullOnDelete();
            $table->string('name')->collate('utf8mb4_bin');
            $table->string('slug');
            $table->unique(['shop_id', 'name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
