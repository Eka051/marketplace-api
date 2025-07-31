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
        Schema::create('postal_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('postal_code_id')->primary();
            $table->string('code')->unique();
            $table->foreignId('district_id')->constrained('districts', 'district_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('postal_code', function (Blueprint $table) {
            //
        });
    }
};
