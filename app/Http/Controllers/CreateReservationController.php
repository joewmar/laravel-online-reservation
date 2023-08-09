<?php

namespace App\Http\Controllers;

use App\Models\Addons;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreateReservationController extends Controller
{
    public function create(){
        $rooms = Room::all() ?? [];
        $rates = RoomRate::all() ?? [];
        return view('system.reservation.create.step1',  [
            'activeSb' => 'Reservation', 
            'rooms' => $rooms, 
            'rates' => $rates, 
        ]);
    }
    public function storeStep1(Request $request){
        $validator = Validator::make($request->all(), [
            'passcode' =>  ['required', 'numeric', 'digits:4'],
            'room_rate' =>  ['required', 'numeric'],
            'room_pax.*' => ['required', 'array'],
        ]);
        if($validator->fails()) return back()->withErrors($validator)->withInput();

        if(empty($validator['room_pax'])) return back()->with('error', 'Required to choose rooms');


    }
    public function step2(Request $request){
        return view('system.reservation.create.step2',  [
            'activeSb' => 'Reservation', 
            'tour_lists' => TourMenuList::all() ?? [], 
            'tour_category' => TourMenuList::distinct()->get('category') ?? [], 
            'tour_menus' => TourMenu::all() ?? [], 
            "user_days" => $noOfday ?? 1,
        ]);
    }

    public function step3(Request $request){
        return view('system.reservation..create.step3', [
            'activeSb' => 'Reservation', 
            'addons' => Addons::all(), 
        ]);
    }
    public function step4(Request $request){
        return view('system.reservation.create.step3',  [
            'activeSb' => 'Reservation', 
            'tour_lists' => TourMenuList::all() ?? [], 
            'tour_category' => TourMenuList::distinct()->get('category') ?? [], 
            'tour_menus' => TourMenu::all() ?? [], 
            "user_days" => $noOfday ?? 1,
        ]);
    }
}
