<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'nationality' => 'Filipino',
            'country' => 'Philippines',
            'contact' => '09123456789',
            'email'=> 'delacruxz@email.com',
            'password' => Hash::make('123456789'),
        ]);

        \App\Models\System::factory()->create([
            'first_name' => 'Hello',
            'last_name' => 'World',
            'contact' => '09987456321',
            'email'=> 'helloworld@email.com',
            'username'=> 'hello.123',
            'password' => Hash::make('987654321'),
        ]);
    }
}
