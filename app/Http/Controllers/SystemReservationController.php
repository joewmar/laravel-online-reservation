<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\TourMenu;
use App\Models\Reservation;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SystemReservationController extends Controller
{
    public function index(){
        return view('system.reservation.index',  ['activeSb' => 'Reservation', 'r_list' => Reservation::all()]);
    }
    public function show($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $tour_menu = [];
        foreach(explode(',' , $reservation->menu) as $key => $item){
            $tour_menu[$key]['title'] = TourMenuList::find(explode('_' , $item)[0])->title;
            $tour_menu[$key]['price'] = TourMenu::find(explode('_' , $item)[1])->price;
        }
        return view('system.reservation.show',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu]);
    }
    public function showRooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = Room::all();
        return view('system.reservation.show-room',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms]);
    }
    public function updateReservation(Request $request){
        $reservation = Reservation::findOrFail(decrypt($request->id));
        $validator = Validator::make($request->all(), [
            'passcode' =>  ['required', 'numeric', 'digits:4'],
            'rooms.*' => ['required', 'numeric'],
        ]);
        $error = [];
        if(!empty($request['rooms'])){
            // $guest = (int)$reservation->pax;
            // foreach($request['rooms'] as $key => $item){
            //     $room = Room::find($item);
            //     if(!($guest == $room->room->min_occupancy || $guest <= ((int)$room->room->max_occupancy))){
            //         $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') capacity does not match ('. $room->room->min_occupancy . ' up to '. $room->room->max_occupancy  .' capacity. However ' . $reservation->userReservation->first_name . ' ' . $reservation->userReservation->last_name . ' Guest was ' . $reservation->pax . ')';
            //     }
            //     else{
            //         $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') can reserve';
            //         $guest = (int)$room->room->max_occupancy;
            //     }
            // }
        }
        else{
            return back()->with('error', 'Need to choose rooms');
        }

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }
        if(!empty($error)){
            return back()->with('error', $error);
        }
        
        // if($validator->fails()){
        //     return redirect()->back()->with('error', 'Something Wrong');
        // }
        // return redirect()->back()->with('success', 'Something Wrong');

    }
}
