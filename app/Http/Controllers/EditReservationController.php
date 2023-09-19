<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomRate;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EditReservationController extends Controller
{
    public function information($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $rateID = null;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'rid') !== false)  $rateID= explode('rid', $key)[1];
        }
        return view('system.reservation.edit.information', ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'rateid' => $rateID]);
    }
    public function updateInfo(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $validate = Validator::make($request->all(), [
            'age' => ['required', 'numeric'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after_or_equal:'.$request['check_in']],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric'],
            'payment_method' => ['required'],
            'status' => ['required'],
            'passcode' => Rule::when(isset($request['passcode']) && $reservation->pax == $request['pax'], ['required', 'digits:4']),
            'room_rate' => Rule::when($reservation->pax != $request['pax'], ['required']),
            'room_pax' => Rule::when($reservation->pax != $request['pax'], ['required']),
        ], [
            'required' => 'Required (:attribute)',
        ]);
        
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        // dd($validate);
        if($validate['pax'] != $reservation->pax){

            // foreach($reservation->roomid as $item){
            //     $room = Room::find($item);
            //     $room->removeCustomer($reservation->id);
            // }

        }
        else{
            if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
            unset($validate['room_pax'], $validate['room_rate'], $validate['passcode']);
            $reservation->update($validate);
        }
        return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' Information was Updated');
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
