<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class SystemReservationController extends Controller
{
    public function index(Request $request){
        $r_list = Reservation::latest()->paginate(5);
        if($request['tab'] === 'pending'){
            $r_list = Reservation::where('status', 0)->latest()->paginate(5);
        }
        if($request['tab'] === 'confirmed'){
            $r_list = Reservation::where('status', 1)->latest()->paginate(5);
        }
        if($request['tab'] === 'checkin'){
            $r_list = Reservation::where('status', 2)->latest()->paginate(5);
        }
        if($request['tab'] === 'checkout'){
            $r_list = Reservation::where('status', 3)->latest()->paginate(5);
        }
        return view('system.reservation.index',  ['activeSb' => 'Reservation', 'r_list' => $r_list]);
    }
    public function event(){
        if(!Auth::guard('system')->check()) abort(404);
        $reservations = Reservation::all();
        $arrEvent = [];
        foreach($reservations as $reservation){
            $color = '';
            if($reservation->status() == 'Confirmed') $color = '#22c55e';
            if($reservation->status() == 'Check-in') $color = '#eab308';
            if($reservation->status() == 'Check-out') $color = '#64748b';
            $arrEvent[] = [
                'title' => $reservation->userReservation->first_name . ' ' .  $reservation->userReservation->last_name . ' (' . $reservation->status() . ')', 
                'start' => $reservation->check_in,
                'end' => $reservation->check_out,        
                'url' => route('system.reservation.show', encrypt($reservation->id)), // URL na ipapunta kapag na-click ang event

                'color' => $color ?? '',
            ];
        }
        return response()->json($arrEvent);
        
    }
    public function show($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $tour_menu = [];
        $conflict = Reservation::all()->where('check_in', $reservation->check_in)->where('status', 0)->except($reservation->id);;
        if($reservation->accommodation_type != 'Room Only'){
            foreach(explode(',' , $reservation->menu) as $key => $item){
                $tour_menu[$key]['title'] = TourMenu::find($item)->tourMenu->title;
                $tour_menu[$key]['type'] = TourMenu::find($item)->type;
                $tour_menu[$key]['pax'] = TourMenu::find($item)->pax;
                if(explode('-' , explode(',' , $reservation->amount)[$key])[0] == 'tm'.$item)
                    $tour_menu[$key]['price'] = explode('-' , explode(',' , $reservation->amount)[$key])[1];
                
            }
        }
        return view('system.reservation.show',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'conflict' => $conflict]);
    }
    public function showRooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = Room::all();
        $rates = RoomRate::all();
        return view('system.reservation.show-room',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rates]);
    }
    public function updateReservation(Request $request){
        // dd($request->all());
        $systemUser = auth('system')->user();
        $reservation = Reservation::findOrFail(decrypt($request->id));
        $validator = Validator::make($request->all(), [
            'passcode' =>  ['required', 'numeric', 'digits:4'],
            'room_rate' =>  ['required', 'numeric'],
            'rooms.*' => ['required', 'numeric'],
        ]);
        $error = [];
        
        if(!Hash::check($request['passcode'], $systemUser->passcode)) $error[] = 'Invalid Passcode';

        if(!empty($request['rooms'])){
            
            // $arrCus = array();
            $roomNo = [];
            $totalPax = 0;
            foreach($request['rooms'] as $key => $item){
                $room = Room::find($item);
                $countOccupancy = 0;
                if($room->room->availability ===  true ){
                    $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') was not available';
                }   
                while($countOccupancy <= $room->room->max_occupancy){
                    if($countOccupancy == $room->room->max_occupancy || $totalPax == $reservation->pax){
                        break;
                    }
                    else{
                        $roomNo[$key] = 'Room No.' . $room->room_no;
                        $countOccupancy++;
                        $totalPax++;
                    }
                }
                if($countOccupancy == 0) $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') was avail due avail '. $reservation->pax .' pax on ' . implode(', ', $roomNo);
                else{
                    $roomCustomer[$item] = $reservation->user_id .'-' . ($countOccupancy);
                    $roomReservation[] = $room->id;
                } 

            }
            // dd($roomReservation);

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
        
        if($validator->valid() && empty($error)){
                $validated = $validator->validate();
                $rate = RoomRate::find($validated['room_rate']);
                if($rate->occupancy != $reservation->pax){
                    return back()->withErrors(['room_rate' => 'Not equal to your pax in the rate you selected'])->withInput();
                }
                $total = $reservation->total;
                $amount = ($reservation->amount != null ? explode(',', $reservation->amount) : []);
                $amount[] = 'rid' . $rate->id .'-'. $rate->price;
                foreach($amount as $item) {
                    if(explode('-', $item)){
                        
                    }
                }
                $total += (double)$item;
                $amount = implode(',', $amount);

                // UPdate Reservatoin
                $reserved = $reservation->update([
                    'room_id' => implode(',', $roomReservation),
                    'room_rate_id' => $rate->id,
                    'amount' => $amount,
                    'total' => $total,
                    'status' => 1,
                ]);
                if($reserved){
                    foreach($roomReservation as $item){
                        $room = Room::find($item);
                        $newCus = $reservation->user_id . '-' . $room->id;
                        if( $room->customer == null){
                            $room->update([
                                'customer' => $newCus,
                            ]);
                        }
                        else{
                            $arrCus = explode(',', $room->customer);
                            $arrCus[] = $newCus;
                            $arrCus = implode(',', $arrCus);
                            $room->update([
                                'customer' => $arrCus,
                            ]);
    
                        }
                        $countOccupancy = 0;
                        foreach (explode(',', $room->customer) as $key => $item) {
                            $arrPreCus[$key]['pax'] = explode('-', $item)[1] ?? '';
                            if($room->room->max_occupancy ==  $countOccupancy ){
                                $room->update(['availability' => true]);
                            }
                            else{
                                $room->update(['availability' => false]);
                                $countOccupancy += (int)$arrPreCus[$key]['pax'];
                            }
                        }
                    }
                    $tour_menu = [];
                    if($reservation->accommodation_type != 'Room Only'){
                        foreach(explode(',' , $reservation->menu) as $key => $item){
                            $tour_menu[$key]['title'] = TourMenu::find($item)->tourMenu->title;
                            $tour_menu[$key]['type'] = TourMenu::find($item)->type;
                            $tour_menu[$key]['pax'] = TourMenu::find($item)->pax;
                            if(explode('-' , explode(',' , $reservation->amount)[$key])[0] == 'tm'.$item)
                                $tour_menu[$key]['price'] = explode('-' , explode(',' , $reservation->amount)[$key])[1];
    
                        }
                    }
                    $details = [
                        'title' => 'Reservation has Confirmed',
                        'body' => 
                        "Name: ". $reservation->userReservation->first_name . " " . $reservation->userReservation->last_name ."<br>" . 
                        "Age: " . $reservation->age ."<br>" .  
                        "Nationality: " . $reservation->userReservation->nationality  ."<br>" . 
                        "Country: " . $reservation->userReservation->country ."<br>" . 
                        "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y') ."<br>" . 
                        "Check-out: " . Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y') ."<br>" . 
                        "Type: " . $reservation->accommodation_type ."<br>" .
                        "Menu <br>" .
                        "Room No: " . 'Room No' . $room->room_no . '(' . $room->room->name .")<br>" .
                        "Room Type: " .  $rate->name . '<br>',
                        'list' => $tour_menu,
                    ];
                    Mail::to($reservation->userReservation->email)->send(new ReservationMail($details, 'reservation.mail'));
                    return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->first_name .' '. $reservation->userReservation->last_name . ' was Confirmed');

                }
        }

    }
}
