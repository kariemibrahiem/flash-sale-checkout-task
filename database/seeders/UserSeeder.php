<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    // created by kariem ibrahiem
    public function run(): void
    {
        User::factory()->count(10)->create();

        User::create([
            'name' => 'getPayIn User',
            'phone' => '01000000001',
            "email" => "getPayIn@gmail.com",
            'password' => bcrypt('getPayIn'),
        ]);
    }
}
