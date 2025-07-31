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
        Schema::create('postal_code', function (Blueprint $table) {
            $table->id('postal_code_id');
            $table->string('code')->unique();
            $table->foreignId('district_id')->constrained('district', 'district_id')->onDelete('cascade');
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
