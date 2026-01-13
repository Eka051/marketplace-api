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
            $table->ulid('address_id')->primary();
            $table->foreignUlid('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('label'); // Home, Office, etc
            $table->string('receiver_name');
            $table->string('phone_number');
            $table->text('full_address'); // Detail address
            $table->foreign('province_id')->constrained('provinces', 'province_id');
            $table->foreign('city_id')->constrained('cities', 'city_id');
            $table->foreign('district_id')->constrained('districts', 'district_id');
            $table->foreign('village_id')->constrained('villages', 'village_id');
            $table->string('postal_code', 5);
            $table->boolean('is_main')->default(false);
            $table->decimal('latitude')->nullable();
            $table->decimal('longitude')->nullable();
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
