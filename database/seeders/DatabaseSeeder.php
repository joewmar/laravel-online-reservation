<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
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
            'birthday' => '2001-02-25',
            'nationality' => 'Filipino',
            'country' => 'Philippines',
            'contact' => '09123456789',
            'email'=> 'delacruxz@email.com',
            'password' => Hash::make('123456789'),
        ]);

        \App\Models\Reservation::factory()->create([
            // 'user_id' => 1,
            // 'room_id' => 1,
            // 'menu',
            'check_in' => Carbon::now()->toDateTimeString(),
            'check_out' => Carbon::now()->addDays(3)->toDateTimeString(),
            'status' => "Pending",
            // 'additional_menu',
            // 'total',
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

        \App\Models\TourMenu::factory()->create([
            'title' => 'ATV Lahar Short Trail without Pinatubo Crater Hike',
            'type' => 'Solo Rider',
            'category' => 'Rent ATV',
            'inclusion' => 'Duration: 1 hr. to 1.5 hrs.',
            'no_day' => '1',
            'hrs' => '1.5',
            'price' => '2500',
            'pax' => '1',

        ]);
        \App\Models\TourMenu::factory()->create([
            'title' => 'ATV Lahar Short Trail without Pinatubo Crater Hike',
            'type' => 'Double Rider',
            'category' => 'Rent ATV',
            'inclusion' => 'Duration: 1 hr. to 1.5 hrs.',
            'no_day' => '1',
            'hrs' => '1.5',
            'price' => '3500',
            'pax' => '2',
        ]);
        \App\Models\TourMenu::factory()->create([
            'title' => 'Tambo Lake Trail Rate',
            'type' => 'Solo',
            'category' => 'ATV Trail Rate',
            'inclusion' => 'Free trek meal and bottled water, 100 add-ons (conservation fees, tourism fees)',
            'no_day' => '1',
            'hrs' => '1.5',
            'price' => '3750',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'title' => 'Tambo Lake Trail Rate',
            'type' => 'Angkas',
            'category' => 'ATV Trail Rate',
            'inclusion' => 'Free trek meal and bottled water, 100 add-ons (conservation fees, tourism fees)',
            'no_day' => '1',
            'hrs' => '1.5',
            'price' => '5550',
            'pax' => '2',
        ]);
        \App\Models\TourMenu::factory()->create([
            'title' => 'ATV Long Trail (going to Pinatubo drop point car)',
            'type' => 'Solo',
            'category' => 'ATV Trail Rate',
            'inclusion' => 'Free trek meal and bottled water, 100 add-ons (conservation fees, tourism fees)',
            'no_day' => '1',
            'hrs' => '1.5',
            'price' => '13200', /* '13200 + 4,000' */
            'pax' => '1',
        ]);
    }
}
