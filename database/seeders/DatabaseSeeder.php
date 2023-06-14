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
            'type' => '0',
            'passcode' => Hash::make('2255'),

        ]);
        \App\Models\System::factory()->create([
            'first_name' => 'Joe',
            'last_name' => 'Doe',
            'contact' => '09987456321',
            'email'=> 'joew@email.com',
            'username'=> 'joewmar',
            'password' => Hash::make('147852369'),
            'type' => '1',
            'passcode' => Hash::make('5566'),
        ]);
    }
}
