<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Symfony\Component\Uid\Ulid;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'user_id' => (string) Ulid::generate(),
            'name' => env('ADMIN_NAME'),
            'email' => env('ADMIN_EMAIL'),
            'role' => env('ADMIN_ROLE'),
            'password' => env('ADMIN_PASS'),
        ]);
    }
}
