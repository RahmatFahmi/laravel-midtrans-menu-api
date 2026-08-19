<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name'      => 'Administrator System',
            'username'  => 'admin',
            'email'     => 'admin@gmail.com',
            'address'   => 'Unknown, Unknown',
            'password'  => Hash::make('password123'), // Selalu gunakan Hash untuk password
            'role'      => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
