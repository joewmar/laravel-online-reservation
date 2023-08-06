<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use App\Models\User;
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

        User::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'birthday' => '2001-02-25',
            'nationality' => 'Filipino',
            'country' => 'Philippines',
            'contact' => '09123456789',
            'email'=> 'juan@email.com',
            'password' => Hash::make('123456789'),
        ]);

        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthday' => '1999-12-25',
            'nationality' => 'American',
            'country' => 'Canada',
            'contact' => '123456897',
            'email'=> 'recelestino90@gmail.com',
            'password' => Hash::make('123456789'),
        ]);
        \App\Models\RoomRate::create([
            'name' => 'Single Room',
            'occupancy' => 1,
            'price' => 3150.00,
        ]);

        \App\Models\RoomRate::create([
            'name' => 'Double Bed',
            'occupancy' => 2,
            'price' => 2100.00,
        ]);
        \App\Models\RoomRate::create([
            'name' => 'Triple Bed',
            'occupancy' => 3,
            'price' => 1100.00,
        ]);

        \App\Models\Reservation::factory()->create([
            'user_id' => 1,
            // 'roomid' => 1, (1, 1)
            // 'roomrateid' => 1,
            'pax' => 1,
            // 'menu' => '2_1',
            'accommodation_type' => 'Room Only',
            'payment_method' => 'Gcash',
            'age' => User::findOrfail(1)->age(),
            'check_in' => Carbon::now()->addDays(30)->toDateTimeString(),
            'check_out' => Carbon::now()->addDays(34)->toDateTimeString(),
            'status' => 0,
            'valid_id' => 'valid_id/Valid_ID-sample.jpg',
            // 'additional_menu',
            // 'amount' => ['room1' => 3150.00],
            // 'total' => 3150.00,
        ]);
        \App\Models\Reservation::factory()->create([
            'user_id' => 2,
            // 'roomid' => 1,
            // 'roomrateid' => 1,
            'pax' => 2,
            'tour_pax' => 2,
            // 'menu' => [2],
            'accommodation_type' => 'Day Tour',
            'payment_method' => 'Gcash',
            'age' => User::findOrfail(1)->age(),
            'check_in' => Carbon::now()->addDays(15)->toDateTimeString(),
            'check_out' => Carbon::now()->addDays(20)->toDateTimeString(),
            'status' => 0,  /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => check-out */
            // 'additional_menu',
            'valid_id' => 'valid_id/Valid_ID-sample.jpg',
            'amount' => ['tm2' => 2100.00],
            'total' => 4200.00,
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
            'telegram_username' => 'joewmar',
            'telegram_chatID' => '5870248478'

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
            'telegram_username' => 'joewmar',
            'telegram_chatID' => '5870248478'
        ]);
        // \App\Models\System::factory(10)->create();

        \App\Models\TourMenuList::factory()->create([
            'title' => 'ATV Lahar Short Trail without Pinatubo Crater Hike',
            'category' => 'Rent ATV',
            'inclusion' => 'Duration: 1 hr. to 1.5 hrs.',
            'tour_type' => 'Day Tour',
            'no_day' => '1',
        ]);
        \App\Models\TourMenuList::factory()->create([
            'title' => 'Tambo Lake Trail Rate',
            'category' => 'ATV Trail Rate',
            'inclusion' => 'Free trek meal and bottled water, 100 add-ons (conservation fees, tourism fees)',
            'tour_type' => 'Day Tour',
            'no_day' => '1',
        ]);

        \App\Models\TourMenuList::factory()->create([
            'title' => 'ATV Long Trail (going to Pinatubo drop point car)',
            'category' => 'ATV Trail Rate',
            'inclusion' => 'Free trek meal and bottled water, 100 add-ons (conservation fees, tourism fees)',
            'tour_type' => 'Day Tour',
            'no_day' => '1',
        ]);

        \App\Models\TourMenu::factory()->create([
            'menu_id' => 1,
            'type' => 'Solo Rider',
            'price' => '2500',
            'pax' => '1',

        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 1,
            'type' => 'Double Rider',
            'price' => '3500',
            'pax' => '2',
        ]);

        \App\Models\TourMenu::factory()->create([
            'menu_id' => 2,
            'type' => 'Solo',
            'price' => '3750',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 2,
            'type' => 'Angkas',
            'price' => '5550',
            'pax' => '2',
        ]);

        \App\Models\TourMenu::factory()->create([
            'menu_id' => 3,
            'type' => 'Solo',
            'price' => '13200', /* '13200 + 4,000' */
            'pax' => '1',
        ]);

        /// ROom
        \App\Models\RoomList::create([
            'name' => 'Charlet',
            'min_occupancy' => 1,
            'max_occupancy' => 4,
            'many_room' => 5,
        ]);
        for($room_count = 0; $room_count < \App\Models\RoomList::findOrFail(1)->many_room; $room_count++){
            \App\Models\Room::create([
                'roomid' => \App\Models\RoomList::findOrFail(1)->id,
                'room_no' => 0,
            ]);
        }
        /// ROom
        \App\Models\RoomList::create([
            'name' => 'Big House',
            'min_occupancy' => 3,
            'max_occupancy' => 7,
            'many_room' => 5,
        ]);
        for($room_count = 0; $room_count < \App\Models\RoomList::findOrFail(2)->many_room; $room_count++){
            \App\Models\Room::create([
                'roomid' => \App\Models\RoomList::findOrFail(2)->id,
                'room_no' => 0,
            ]);
        }
        refreshRoomNumber();
        \App\Models\Archive::factory(10)->create();
        \App\Models\Addons::create([
            'title' => 'Coca Cola',
            'price' => 70.00,
        ]);
        \App\Models\Addons::create([
            'title' => 'Foam',
            'price' => 100,
        ]);

    }
}
