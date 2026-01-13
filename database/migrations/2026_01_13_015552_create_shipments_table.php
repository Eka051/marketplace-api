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
        Schema::create('shipments', function (Blueprint $table) {
            $table->ulid('shipment_id')->primary();
            $table->foreignUlid('order_id')->constrained('orders', 'order_id')->cascadeOnDelete();
            $table->string('courier_name');
            $table->string('courier_code');
            $table->string('courier_service');
            $table->string('tracking_number')->nullable();
            $table->enum('status', ['pending', 'packed', 'shipped', 'in_transit', 'delivered', 'failed'])->default('pending');
            $table->integer('total_weight');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
