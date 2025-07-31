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
        Schema::table('village', function (Blueprint $table) {
            $table->id('village_id');
            $table->string('name')->unique();
            $table->foreignId('postal_code_id')->constrained('postal_code', 'postal_code_id')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('district', 'district_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('village', function (Blueprint $table) {
            //
        });
    }
};
