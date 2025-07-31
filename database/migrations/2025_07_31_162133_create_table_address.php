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
        Schema::create('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('receiver_name');
            $table->string('phone_number');
            $table->string('address_line');
            $table->foreignId('village_id')->constrained('villages', 'village_id')->onDelete('cascade');
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('address', function (Blueprint $table) {
            //
        });
    }
};
