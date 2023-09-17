<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomRate;
use App\Models\Reservation;
use Illuminate\Http\Request;

class EditReservationController extends Controller
{
    public function information($id){
        return view('system.reservation.edit.information', ['activeSb' => 'Reservation', 'r_list' => Reservation::findOrFail(decrypt($id))]);
    }
    public function updateInfo(Request $request, $id){
        dd($id);
        // return view('system.reservation.edit.information', ['activeSb' => 'Reservation', 'r_list' => Reservation::findOrFail(decrypt($id))]);
    }
    public function services($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $count = 0;
        foreach($reservation->transaction ?? [] as $transKey => $item){
            if (strpos($transKey, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menu[$count]['id'] = $transKey;
                $tour_menu[$count]['title'] = $item['title'];
                $tour_menu[$count]['price'] = $item['price'];
                $tour_menu[$count]['amount'] = $item['amount'];
            }
            if (strpos($transKey, 'TA') !== false && is_array($item)) {
                foreach($item as $key => $tourAddons){
                    $tour_addons[$count]['id'] = $transKey;
                    $tour_addons[$count]['title'] = $tourAddons['title'];
                    $tour_addons[$count]['price'] = $tourAddons['price'];
                    $tour_addons[$count]['amount'] = $tourAddons['amount'];
                }
            }
            if (strpos($transKey, 'OA') !== false && is_array($item)) {
                foreach($item as $key => $tourAddons){
                    $other_addons[$count]['id'] = $transKey;
                    $other_addons[$count]['title'] = $tourAddons['title'];
                    $other_addons[$count]['pcs'] = $tourAddons['pcs'];
                    $other_addons[$count]['price'] = $tourAddons['price'];
                    $other_addons[$count]['amount'] = $tourAddons['amount'];
                }
            }
            $count++;
        }
        unset($count);
        return view('system.reservation.edit.services', ['activeSb' => 'Reservation', 'r_list' => $reservation, 'tour_menu' => $tour_menu ?? [],  'tour_addons' => $tour_addons ?? [],  'other_addons' => $other_addons ?? [],]);
    }
    public function rooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $rateID = null;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'rid') !== false)  $rateID= explode('rid', $key)[1];
        }
        return view('system.reservation.edit.rooms',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'rateid' => $rateID]);
    }
}
