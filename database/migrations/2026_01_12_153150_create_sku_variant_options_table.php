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
        Schema::create('sku_variant_options', function (Blueprint $table) {
            $table->foreignId('sku_id')->constrained('product_skus', 'sku_id')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('attribute_options', 'option_id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sku_variant_options');
    }
};
