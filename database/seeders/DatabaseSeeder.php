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
            'valid_id' => 'valid_id/IkYI5uA6.jpg',
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
        \App\Models\RoomRate::create([
            'name' => 'Multi-Sharing',
            'occupancy' => 4,
            'price' => 700.00,
        ]);

        // \App\Models\Reservation::factory()->create([
        //     'user_id' => 1,
        //     // 'roomid' => 1, (1, 1)
        //     // 'roomrateid' => 1,
        //     'pax' => 1,
        //     // 'menu' => '2_1',
        //     'accommodation_type' => 'Room Only',
        //     'payment_method' => 'Gcash',
        //     'age' => User::findOrfail(1)->age(),
        //     'check_in' => Carbon::now()->addDays(30)->toDateTimeString(),
        //     'check_out' => Carbon::now()->addDays(34)->toDateTimeString(),
        //     'status' => 0,
        //     'valid_id' => 'valid_id/Valid_ID-sample.jpg',
        //     // 'additional_menu',
        //     // 'amount' => ['room1' => 3150.00],
        //     // 'total' => 3150.00,
        // ]);
        \App\Models\Reservation::factory()->create([
            'user_id' => 2,
            // 'roomid' => 1,
            // 'roomrateid' => 1,
            'pax' => 4,
            'tour_pax' => 4,
            'accommodation_type' => 'Overnight',
            'payment_method' => 'Gcash',
            'age' => User::findOrfail(1)->age(),
            'check_in' => Carbon::now()->addDays(15)->toDateTimeString(),
            'check_out' => Carbon::now()->addDays(18)->toDateTimeString(),
            'status' => 0,  /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => check-out */
            'transaction' => ['tm2' => ['title' => 'ATV Lahar Short Trail without Pinatubo Crater Hike Double Rider', 'price' => 2100.00, 'amount' => 2100.00 * 4]],
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
            'first_name' => 'Panis',
            'last_name' => 'Doe',
            'contact' => '09987456321',
            'email'=> 'joew@email.com',
            'username'=> 'joewmar',
            'password' => Hash::make('123456789'),
            'type' => '1',
            'passcode' => Hash::make('5566'),
            'telegram_username' => 'joewmar',
            'telegram_chatID' => '5870248478'
        ]);
        // \App\Models\System::factory(10)->create();

        // Rent ATV //
        \App\Models\TourMenuList::factory()->create([
            'title' => 'ATV Lahar Short Trail without Pinatubo Crater Hike',
            'category' => 'Rent ATV',
            'inclusion' => 'Duration: 1 hr. to 1.5 hrs.',
            'tour_type' => 'All',
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

        \App\Models\TourMenuList::factory()->create([
            'title' => 'ATV Tambo Lake and San Marcos Lake Trail without Pinatubo Crater Hike',
            'category' => 'Rent ATV',
            'inclusion' => 'Duration: 1 hr. to 1.5 hrs.',
            'tour_type' => 'All',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 2,
            'type' => 'Solo Rider',
            'price' => '3750',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 2,
            'type' => 'Double Rider',
            'price' => '5550',
            'pax' => '2',
        ]);

        \App\Models\TourMenuList::factory()->create([
            'title' => 'ATV Combo Trail without Pinatubo Crater Hike',
            'category' => 'Rent ATV',
            'inclusion' => 'Duration: 5 hrs(..)Destination: Lahar Canyons, Tambo Lake, Aeta Village, Bulacan',
            'tour_type' => 'All',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 3,
            'type' => 'Solo Rider',
            'price' => '5500',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 3,
            'type' => 'Double Rider',
            'price' => '9000',
            'pax' => '2',
        ]);



        // ATV Trail Rate //
        \App\Models\TourMenuList::factory()->create([
            'title' => 'Tambo Lake Trail',
            'category' => 'ATV Trail Rate',
            'inclusion' => '100 add-ons (conservation fees, tourism fees)(..)Free trek meal and bottled water',
            'tour_type' => 'All',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 4,
            'type' => 'Solo Rider',
            'price' => '3750',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 4,
            'type' => 'Double Rider',
            'price' => '5500',
            'pax' => '2',
        ]);

        \App\Models\TourMenuList::factory()->create([
            'title' => 'Combo Trail',
            'category' => 'ATV Trail Rate',
            'inclusion' => '100 add-ons (conservation fees, tourism fees)(..)Free trek meal and bottled water',
            'tour_type' => 'All',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 5,
            'type' => 'Solo Rider',
            'price' => '5500',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 5,
            'type' => 'Double Rider',
            'price' => '7000',
            'pax' => '2',
        ]);

        \App\Models\TourMenuList::factory()->create([
            'title' => 'Short trail via ATV',
            'category' => 'ATV Trail Rate',
            'inclusion' => '100 add-ons (conservation fees, tourism fees)(..)Free trek meal and bottled water',
            'tour_type' => 'All',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 6,
            'type' => 'Solo Rider',
            'price' => '2500',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 6,
            'type' => 'Double Rider',
            'price' => '1000',
            'pax' => '2',
        ]);

        \App\Models\TourMenuList::factory()->create([
            'title' => 'ATV Long Trail (going to Pinatubo drop point car)',
            'category' => 'ATV Trail Rate',
            'inclusion' => '700 add-ons (conservation fees, tourism fees)(..)Free trek meal and bottled water',
            'tour_type' => 'Day Tour',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 7,
            'type' => 'Solo',
            'price' => '13200', /* '13200 + 4,000' */
            'pax' => '1',
        ]);



        // Mt. Pinatubo 4x4 rate day tour //
        \App\Models\TourMenuList::factory()->create([
            'title' => 'Mt. Pinatubo 4x4 Ride',
            'category' => '4x4 Ride Rate',
            'inclusion' => 'Local guide(..)All mandatory fees (conservation fees, tourism fees)(..)Trek meal (packed lunch)(..)Use of toilet & shower facilities(..)Parking facilities',
            'tour_type' => 'Day Tour',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 8,
            'type' => '1 PAX',
            'price' => '6600',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 8,
            'type' => '2 PAX',
            'price' => '3750',
            'pax' => '2',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 8,
            'type' => '3 PAX',
            'price' => '2900',
            'pax' => '3',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 8,
            'type' => '4 PAX',
            'price' => '2700',
            'pax' => '4',
        ]);


        /// Room
        \App\Models\RoomList::create([
            'name' => 'Charlet',
            // 'min_occupancy' => 1,
            'max_occupancy' => 4,
            'many_room' => 5,
        ]);
        $room = \App\Models\RoomList::findOrFail(1);
        for($room_count = 0; $room_count < $room->many_room; $room_count++){
            \App\Models\Room::create([
                'roomid' => $room->id,
                'room_no' => 0,
            ]);
        }

        /// Room
        \App\Models\RoomList::create([
            'name' => 'Big House',
            // 'min_occupancy' => 3,
            'max_occupancy' => 7,
            'many_room' => 3,
        ]);
        \App\Models\RoomList::create([
            'name' => 'Multi-Sharing Room',
            // 'min_occupancy' => 3,
            'max_occupancy' => 7,
            'many_room' => 5,
        ]);
        \App\Models\RoomList::create([
            'name' => 'Kubo House',
            // 'min_occupancy' => 3,
            'max_occupancy' => 4,
            'many_room' => 4,
        ]);

        \App\Models\RoomList::create([
            'name' => 'New House',
            // 'min_occupancy' => 3,
            'max_occupancy' => 2,
            'many_room' => 3,
        ]);

        foreach(\App\Models\RoomList::all() as $room){
            for($room_count = 0; $room_count < $room->many_room; $room_count++){
                \App\Models\Room::create([
                    'roomid' => $room->id,
                    'room_no' => 0,
                ]);
            }
        }
        refreshRoomNumber();
        // \App\Models\Archive::factory(50)->create();
        \App\Models\Addons::create([
            'title' => 'Coca Cola',
            'price' => 70.00,
        ]);
        \App\Models\Addons::create([
            'title' => 'Foam',
            'price' => 100,
        ]);
        // \App\Models\Feedback::factory(50)->create();
        // \App\Models\Archive::factory(50)->create();

        \App\Models\WebContent::create([
            'operation' => 1,
        ]);

    }
}
