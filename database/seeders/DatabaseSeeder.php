<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Reservation;
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

        User::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'birthday' => '2001-02-25',
            'nationality' => 'Filipino',
            'country' => 'Philippines',
            'contact' => '09123456789',
            'email'=> 'recelestino90@gmail.com',
            'password' => Hash::make('123456789'),
            'valid_id' => 'valid_id/Valid_ID.sample.jpg',
        ]);

        if(env('FORCE_CREATE_USER') ?? false) User::factory(10)->create();
        
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
        //     'user_id' => 2,
        //     // 'roomid' => 1,
        //     // 'roomrateid' => 1,
        //     'pax' => 5,
        //     'tour_pax' => 4,
        //     'accommodation_type' => 'Overnight',
        //     'payment_method' => 'Gcash',
        //     'age' => User::findOrfail(1)->age(),
        //     'check_in' => Carbon::now('Asia/Manila')->addDays(15)->toDateTimeString(),
        //     'check_out' => Carbon::now('Asia/Manila')->addDays(20)->toDateTimeString(),
        //     'status' => 0,  /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => check-out */
        //     'transaction' => ['tm2' => ['title' => 'ATV Lahar Short Trail without Pinatubo Crater Hike Double Rider (2 pax)', 'price' => 2100.00, 'amount' => 2100.00 * 4]],
        // ]);

        \App\Models\Reservation::factory()->create([
            'user_id' => 1,
            // 'roomid' => 1,
            // 'roomrateid' => 1,
            'pax' => 4,
            'tour_pax' => 4,
            'accommodation_type' => 'Overnight',
            'payment_method' => 'Bank Transfer',
            'age' => User::findOrfail(1)->age(),
            'check_in' => Carbon::now('Asia/Manila')->addDays(16)->toDateTimeString(),
            'check_out' => Carbon::now('Asia/Manila')->addDays(18)->toDateTimeString(),
            'status' => 0,  /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => check-out */
            'transaction' => ['tm2' => ['title' => 'ATV Lahar Short Trail without Pinatubo Crater Hike Double Rider (2 pax)', 'price' => 2100.00, 'tpx' => 4, 'amount' => 2100.00 * 4]],
        ]);

        \App\Models\System::factory()->create([
            'first_name' => 'Alvin',
            'last_name' => 'Bognot',
            'contact' => '09987456321',
            'email'=> 'alvinbognot@email.com',
            'username'=> 'AlvinAdminBognot',
            'password' => Hash::make('987654321'),
            'type' => '0',
            'passcode' => Hash::make('2255'),
            'telegram_username' => 'joewmar',
            'telegram_chatID' => '5870248478'

        ]);
        \App\Models\System::factory()->create([
            'first_name' => 'Angelita',
            'last_name' => 'Bognot',
            'contact' => '09987456321',
            'email'=> 'angelitabognot@email.com',
            'username'=> 'angelitaBognotadmin',
            'password' => Hash::make('147258369'),
            'type' => '1',
            'passcode' => Hash::make('5566'),
            'telegram_username' => 'joewmar',
            'telegram_chatID' => '5870248478'
        ]);
        \App\Models\System::factory()->create([
            'first_name' => 'Raul',
            'last_name' => 'David',
            'contact' => '09987456321',
            'email'=> 'raulDavid@email.com',
            'username'=> 'raulDavidManager',
            'password' => Hash::make('123789456'),
            'type' => '2',
            'passcode' => Hash::make('1234'),
            'telegram_username' => 'joewmar',
            'telegram_chatID' => '5870248478'
        ]);
        // \App\Models\System::factory(10)->create();

        // Rent ATV //
        \App\Models\TourMenuList::factory()->create([
            'title' => 'ATV Lahar Short Trail without Pinatubo Crater Hike',
            'category' => 'Rent ATV',
            'inclusion' => 'Duration: 1 hr. to 1.5 hrs.(..)Free trek meal, parking space and bottled water',
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
            'inclusion' => 'Duration: 2 hrs. to 2.5 hrs(..)Free trek meal, parking space and bottled water',
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

        \App\Models\TourMenuList::factory()->create([
            'title' => 'ATV Long Trail (going to Pinatubo drop point car)',
            'category' => 'Rent ATV',
            'inclusion' => 'Free trek meal, parking space and bottled water',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 4,
            'type' => 'Solo Rider with assistance 4x4',
            'price' => '13200',
            'pax' => '1',
        ]);

        // Mt. Pinatubo 4x4 rate day tour //
        \App\Models\TourMenuList::factory()->create([
            'title' => 'Mt. Pinatubo 4x4 Ride',
            'category' => '4x4 Ride Rate',
            'inclusion' => 'Local guide(..)All mandatory fees (conservation fees, tourism fees)(..)Trek meal (packed lunch)(..)Use of toilet & shower facilities(..)Parking facilities',
            'no_day' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 5,
            'type' => '1 PAX',
            'price' => '6600',
            'pax' => '1',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 5,
            'type' => '2 PAX',
            'price' => '3750',
            'pax' => '2',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 5,
            'type' => '3 PAX',
            'price' => '2900',
            'pax' => '3',
        ]);
        \App\Models\TourMenu::factory()->create([
            'menu_id' => 5,
            'type' => '4 PAX',
            'price' => '2700',
            'pax' => '4',
        ]);


        /// Room
        \App\Models\RoomList::create([
            'image' => 'rooms/big-house-room.jpg',
            'name' => 'Big House',
            // 'min_occupancy' => 3,
            'max_occupancy' => 3,
            'many_room' => 3,
        ]);
        \App\Models\RoomList::create([
            'image' => 'rooms/multi-sharing-room.jpg',
            'name' => 'Multi-Sharing Room',
            // 'min_occupancy' => 3,
            'max_occupancy' => 6,
            'many_room' => 5,
        ]);
        \App\Models\RoomList::create([
            'image' => 'rooms/kubo-house-room.jpg',
            'name' => 'Kubo House',
            // 'min_occupancy' => 3,
            'max_occupancy' => 4,
            'many_room' => 4,
        ]);

        \App\Models\RoomList::create([
            'image' => 'rooms/new-house-room.jpg',
            'name' => 'New House',
            // 'min_occupancy' => 3,
            'max_occupancy' => 4,
            'many_room' => 3,
        ]);
        foreach(\App\Models\RoomList::all() as $room){
            for($room_count = 0; $room_count < $room->many_room; $room_count++){
                $rdata = [
                    'roomid' => $room->id,
                    'room_no' => 0,
                    // 'availability' => 1,
                    // 'customer' => [
                    //     Reservation::all()->first()->id => $room->max_occupancy
                    // ],
                ];
                // if($room_count == 3){
                //     $rdata = [
                //         'roomid' => $room->id,
                //         'room_no' => 0,
                //     ];
                // }

                \App\Models\Room::create($rdata);
                

            }
        }
        refreshRoomNumber();
        // \App\Models\Archive::factory(50)->create();
        \App\Models\Addons::create([
            'title' => 'Soda',
            'price' => 50,
        ]);
        \App\Models\Addons::create([
            'title' => 'Beer',
            'price' => 70,
        ]);
        \App\Models\Addons::create([
            'title' => 'Coffee',
            'price' => 50,
        ]);
        \App\Models\Addons::create([
            'title' => 'Hot Tea',
            'price' => 50,
        ]);
        \App\Models\Addons::create([
            'title' => 'Water 500ml',
            'price' => 35,
        ]);
        \App\Models\Addons::create([
            'title' => 'Water 1L',
            'price' => 50,
        ]);
        \App\Models\Addons::create([
            'title' => 'Foam',
            'price' => 100,
        ]);
        // \App\Models\Feedback::factory(50)->create();
        // \App\Models\Archive::factory(50)->create();

        \App\Models\WebContent::create([
            'operation' => 1,
            'hero' => [
                "main_hero1" => "hero/main-hero1.jpg",
                "main_hero2" => "hero/main-hero2.jpg",
                "main_hero3" => "hero/main-hero3.jpg",
                "main_hero4" => "hero/main-hero4.jpg",
                "main_hero5" => "hero/main-hero5.jpg",
                "main_hero6" => "hero/main-hero6.jpg",
            ],
            'gallery' => [
                "gallery1" => "gallery/gallery_1.jpg",
                "gallery2" => "gallery/gallery_2.jpg",
                "gallery3" => "gallery/gallery_3.jpg",
                "gallery4" => "gallery/gallery_4.jpg",
                "gallery5" => "gallery/gallery_5.jpg",
                "gallery6" => "gallery/gallery_6.jpg",
                "gallery7" => "gallery/gallery_7.jpg",
                "gallery8" => "gallery/gallery_8.jpg",
                "gallery9" => "gallery/gallery_9.jpg",
                "gallery10" => "gallery/gallery_10.jpg",
            ],
            'tour' => [
                'mainTour' => [
                    'mt1' => [
                        'image' => 'tour/main_tour/pinatubo-crate-lake.jpg',
                        'title' => 'MT. PINATUBO',
                        'location' => 'Located on the tripoint boundary of the Philippine provinces of Zambales, Tarlac and Pampanga, all in Central Luzon on the northern island of Luzon',
                    ],
                    'mt2' => [
                        'image' => 'tour/main_tour/tambo-lakes.jpg',
                        'title' => 'TAMBO LAKE',
                        'location' => 'Located on Tambo Lake, Capas, Tarlac, Philippines',
                    ],
                    'mt3' => [
                        'image' => 'tour/main_tour/tarukan-village.jpg',
                        'title' => 'TARUKAN VILLAGE',
                        'location' => 'Located on Sitio Tarukan, Capas Tarlac, Capas, Philippines',
                    ],
                ],
                'sideTour' => [
                    'st1' => [
                        'image' => 'tour/side_tour/monasterio-de-tarlac.jpg',
                        'title' => 'Monasterio De Tarlac',
                        'location' => 'Located on Barangay Lubigan, San José, Tarlac',
                    ],
                    'st2' => [
                        'image' => 'tour/side_tour/lubigan.jpg',
                        'title' => 'Lubigan Lake',
                        'location' => 'Located on Barangay Lubigan, San José, Tarlac',
                    ],
                    'st3' => [
                        'image' => 'tour/side_tour/bueno-hot-spring.jpg',
                        'title' => 'Bueno Hot Spring',
                        'location' => 'Located in Sitio Danum Mapali Capas, Tarlac',
                    ],
                ],
            ],
            'contact' => [
                'main' => [
                    'email' => 'bognothomestay@gmail.com',
                    'contactno' => '09198614102',
                    'whatsapp' => '09198614102',
                    'fbuser' => 'https://www.facebook.com/p/Alvin-and-Angie-Bognot-mt-Pinatubo-Guesthouse-and-Tours-100057519735244',
                ],
            ],
            'payment' => [
                "gcash" => [
                    [
                        "name" => "Juan Dela Cruz",
                        "number" => "09123456789",
                        "qrcode" => "ref_gcash/gcash.jpg",
                        "priority" => true
                    ],
                ],
                "paypal" => [
                    [
                        "name" => "Juan Dela Cruz",
                        "email" => "delacruz@email.com",
                        "image" => "ref_paypal/reciepient-paypal.png",
                        "number" => "09147258369",
                        "priority" => true,
                        "username" => "delajuan.cruz"
                    ]
                    ],
                "bankTransfer" => [
                    [
                        "name" => "Juan Dela Cruz",
                        "acc_no" => "654-456-123",
                        "contact" => "09147258369",
                        "priority" => true,
                    ]
                ]
            ]
        ]);

    }
}
