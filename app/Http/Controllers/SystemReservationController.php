<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\TourMenu;
use App\Models\Reservation;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;


class SystemReservationController extends Controller
{
    public function index(){
        return view('system.reservation.index',  ['activeSb' => 'Reservation', 'r_list' => Reservation::all()]);
    }
    public function show($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $tour_menu = [];
        if(!$reservation->accommodation_type == 'Room Only'){
            foreach(explode(',' , $reservation->menu) as $key => $item){
                $tour_menu[$key]['title'] = TourMenuList::find(explode('_' , $item)[0])->title;
                $tour_menu[$key]['price'] = TourMenu::find(explode('_' , $item)[1])->price;
            }
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
        $systemUser = auth('system')->user();
        $reservation = Reservation::findOrFail(decrypt($request->id));
        $validator = Validator::make($request->all(), [
            'passcode' =>  ['required', 'numeric', 'digits:4'],
            'rooms.*' => ['required', 'numeric'],
        ]);
        $error = [];
        if(!empty($request['rooms'])){
            
            // $arrCus = array();
            $roomNo = [];
            $countPax = $reservation->pax;
            foreach($request['rooms'] as $key => $item){
                $room = Room::find($item);

                if($room->room->availability ===  true ){
                    $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') was not available';
                }   
                elseif( $reservation->pax >= $room->room->min_occupancy &&  $reservation->pax  <= $room->room->max_occupancy){
                    if($countPax == $reservation->pax){
                        $countPax++;
                        $roomNo[$key] = 'Room No.' . $room->room_no;
                    }
                    else{
                        $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') was avail due avail '. $reservation->pax .' pax on ' . implode(', ', $roomNo);
                    }
                }
                else{
                    $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') was not allowed to avail due range of pax ('. $reservation->pax .' pax)';
                }


            }
        }
        else{
            return back()->with('error', 'Need to choose rooms');
        }

        if($validator->fails()){
            return back()->withErrors($validator)->withInput();
        }
        if(!empty($error)){
            return back()->with('error', $error)->withInput();
        }
        
        // if($validator->valid() && empty($error)){
        //     $validated = $validator->validate();
        //     if(Hash::check($validated['passcode'], $systemUser->passcode)){
        //         foreach($validated['rooms'] as $item){
        //             $room = Room::find($item);
        //             $updatedCustomer = Arr::add($room->customer, implode('-', array($reservation->user_id, $reservation->pax)));
        //             $room->update([
        //                 'customer' => $updatedCustomer
        //             ]);

        //         }
        //         $reservation->update([
        //             'room_no' => implode('_', $validated['rooms']),
        //         ]);
        //         foreach (explode(',', $room->customer) as $key => $item) {
        //             $arrCus[$key]['cusid'] = explode('-', $item)[0] ?? '';
        //             $arrCus[$key]['pax'] = explode('-', $item)[1] ?? '';
        //             if($room->room->max_occupancy ==  $countPax ){
        //                 $room->update(['availability' => true]);
        //             }
        //             else{
        //                 $room->update(['availability' => false]);
        //                 $countPax += (int)$arrCus[$key]['pax'];
        //             }
        //         }
        //     }
        //     else{
        //         return back()->with('error', 'Invalid Passcode');
        //     }

        // }
        return redirect()->back()->with('success', 'Something Wrong');

    }
}
