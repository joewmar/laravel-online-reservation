<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\System;
use App\Models\Archive;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\Reservation;
use App\Models\WebContent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class MyReservationController extends Controller
{
    public function index(Request $request){
        $reservation = Reservation::where('user_id', auth('web')->user()->id)->where('status', 0)->latest()->paginate(5) ?? [];
        if($request['tab'] == 'confirmed'){
            $reservation = Reservation::where('user_id', auth('web')->user()->id)->where('status', 1)->latest()->paginate(5) ?? [];
        }
        if($request['tab'] == 'cin'){
            $reservation = Reservation::where('user_id', auth('web')->user()->id)->where('status', 2)->latest()->paginate(5) ?? [];
        }
        if($request['tab'] == 'cout'){
            $reservation = Reservation::where('user_id', auth('web')->user()->id)->where('status', 3)->latest()->paginate(5) ?? [];
        }
        if($request['tab'] == 'reshedule'){
            $reservation = Reservation::where(function ($query) {
                $query->where('user_id', auth('web')->user()->id)
                    ->where(function ($subQuery) {
                        $subQuery->where('status', 7)
                            ->orWhere('message->reschedule->prev_status', 4);
                    });
            })->latest()->paginate(5);  

        }
        if($request['tab'] == 'cancel'){
            $reservation = Reservation::where(function ($query) {
                $query->where('user_id', auth('web')->user()->id)
                    ->where(function ($subQuery) {
                        $subQuery->where('status', 5)
                            ->orWhere('status', 8);
                    });
            })->latest()->paginate(5);            

        }

        if($request['tab'] == 'previous'){
            $reservation = Reservation::with('previous')->where('user_id', auth('web')->user()->id)->latest()->paginate(5) ?? [];
        }
        return view('users.reservation.index', ['activeNav' => 'My Reservation', 'reservation' => $reservation]);
    }
    public function show($id) {
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = [];
        $tour_menu = [];
        $other_addons = [];
        $tour_addons = [];
        $rate = [];
        $total = 0;

        if($reservation->roomid){
            foreach($reservation->roomid as $item){
                $rooms[] = 'Room No.' . Room::find($item)->room_no . ' ('.Room::find($item)->room->name.')';
            }
        }
        $conflict = Reservation::all()->where('check_in', $reservation->check_in)->where('status', 0)->except($reservation->id);;
        $count = 0;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menuID = (int)str_replace('tm','', $key);
                $tour_menu[$count]['title'] = $reservation->transaction['tm'.$tour_menuID]['title'];
                $tour_menu[$count]['price'] = $reservation->transaction['tm'.$tour_menuID]['price'];
                $tour_menu[$count]['amount'] = $reservation->transaction['tm'.$tour_menuID]['amount'];
                $total += (double)$tour_menu[$count]['amount'];
            }
            // Rate
            if (strpos($key, 'rid') !== false) {
                $rateID = (int)str_replace('rid','', $key);
                $rate['name'] = $reservation->transaction['rid'.$rateID]['title'];;
                $rate['price'] = $reservation->transaction['rid'.$rateID]['price'];
                $rate['amount'] = $reservation->transaction['rid'.$rateID]['amount'];
                if(isset($reservation->transaction['rid'.$rateID]['orig_amount'])){
                    $rate['orig_amount'] = $reservation->transaction['rid'.$rateID]['orig_amount'];
                }
            }
            if (strpos($key, 'OA') !== false && is_array($item)) {
                $OAID = (int)str_replace('OA','', $key);
                foreach($item as $key => $dataAddons){
                    $other_addons[$count+$key]['title'] = $reservation->transaction['OA'.$OAID][$key]['title'];
                    $other_addons[$count+$key]['pcs'] = $reservation->transaction['OA'.$OAID][$key]['pcs'];
                    $other_addons[$count+$key]['price'] = $reservation->transaction['OA'.$OAID][$key]['price'];
                    $other_addons[$count+$key]['amount'] = $reservation->transaction['OA'.$OAID][$key]['amount'];
                }
            }
            if (strpos($key, 'TA') !== false && is_array($item)) {
                $TAID = (int)str_replace('TA','', $key);
                foreach($item as $key => $tourAddons){
                    $tour_addons[$count]['title'] = $reservation->transaction['TA'.$TAID][$key]['title'];
                    $tour_addons[$count]['price'] = $reservation->transaction['TA'.$TAID][$key]['price'];
                    $tour_addons[$count]['amount'] = $reservation->transaction['TA'.$TAID][$key]['amount'];
                }
            }
            $count++;
        }
        unset($count);
        return view('users.reservation.show',  ['activeNav' => 'My Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'rooms' => implode(',', $rooms), 'rate' => $rate, 'total' => $total, 'other_addons' => $other_addons, 'tour_addons' => $tour_addons]);
    }
    public function edit($id){
        dd($id);
    }
    public function cancel(Request $request, $id) {
        $validate = Validator::make($request->all('cancel_message'), [
            'cancel_message' => ['required'],
        ], ['required' => 'Need to input the reason']);
        if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
        $validate = $validate->validate();
        $reservation = Reservation::findOrFail(decrypt($id));
        $systemUser = System::all()->where('type', '>=', 0)->where('type', '<=', 1);
        $messages = $reservation->message;
        $messages['cancel'] = [
            'prev_status' => $reservation->status,
            'message' =>  $validate['cancel_message'],
        ];
        $updated = $reservation->update(['message' => $messages, 'status' => 8]);
        if($updated) {
            $text = 
            "Cancel Request Reservation!\n" .
            "Name: ". $reservation->userReservation->name() ."\n" . 
            "Age: " . $reservation->age ."\n" .  
            "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
            "Country: " . $reservation->userReservation->country ."\n" . 
            "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y') ."\n" . 
            "Check-out: " . Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y') ."\n" . 
            "Type: " . $reservation->accommodation_type ."\n" . 
            "Pax: " . $reservation->pax ."\n" . 
            "Why to Cancel: " .$validate['cancel_message']; 
            $keyboard = [
                [
                    ['text' => 'View Cancel Request', 'url' => route('system.reservation.show.cancel', encrypt($reservation->id))],
                ],
            ];
            foreach($systemUser as $user) if(!empty($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, $keyboard, 'bot1');
            return redirect()->route('user.reservation.home')->with('success', 'Cancel Request was succesfull to send. Just Wait Send Email or any Contact for Approval Request');
        }
    }
    public function reschedule(Request $request, $id) {
        $reservation = Reservation::findOrFail(decrypt( $id));
        if(str_contains($request['check_in'], 'to')){
            $dateSeperate = explode('to', $request['check_in']);
            $request['check_in'] = trim($dateSeperate[0]);
            $request['check_out'] = trim ($dateSeperate[1]);
        }
        // Check out convertion word to date format
        if(str_contains($request['check_out'], ', ')){
            $date = Carbon::createFromFormat('F j, Y', $request['check_out']);
            $request['check_out'] = $date->format('Y-m-d');
        }
        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');

        if(($request['check_in'] === $reservation->check_in && $request['check_out'] === $reservation->check_out) || $request['check_in'] === $request['check_out']){
            return back()->with('error', 'Your choose date does not change at all');
        }

        $validator = null;
        if($reservation->accommodation_type == 'Day Tour'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first (:attribute)',
                'date_equals' => 'Choose only one day (Day Tour)',
            ]);
        }
        elseif($reservation->accommodation_type == 'Overnight'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first (:attribute)',
                'check_out.after_or_equal' => 'Choose within 2 or 3 days (Overnight)',
            ]);
        }
        elseif($reservation->accommodation_type == 'Room Only'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
        }
        if ($validator->fails()) {            
            session(['ck' => false]);
            return back()
            ->with('error', $validator->errors()->all())
            ->withInput();
        }
        $validator = $validator->validate();

        $systemUser = System::all()->where('type', '>=', 0)->where('type', '<=', 1);
        $messages = $reservation->message;
        $messages['reschedule'] = [
            'message' => $validator['reschedule_message'],
            'check_in' => $validator['check_in'],
            'check_out' => $validator['check_out'],
            'prev_status' => $reservation->status,
        ];
        $updated = $reservation->update(['message' => $messages, 'status' => 7]);
        if($updated) {
            $text = 
            "Reschedule Request Reservation!\n" .
            "Name: ". $reservation->userReservation->name() ."\n" . 
            "Age: " . $reservation->age ."\n" .  
            "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
            "Country: " . $reservation->userReservation->country ."\n" . 
            "Type: " . $reservation->accommodation_type ."\n" . 
            "Pax: " . $reservation->pax ."\n" . 
            "Reschedule Check-in: " . Carbon::createFromFormat('Y-m-d', $validator['check_in'])->format('F j, Y') ."\n" . 
            "Reschedule Check-out: " . Carbon::createFromFormat('Y-m-d', $validator['check_out'])->format('F j, Y') ."\n" . 
            "Why: " . $validator['reschedule_message'];
            $keyboard = [
                [
                    ['text' => 'View Details', 'url' => route('system.reservation.show', encrypt($reservation->id))],
                ],
            ];
            foreach($systemUser as $user) if(!empty($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, $keyboard, 'bot1');
            return redirect()->route('user.reservation.home')->with('success', 'Reschedule Request was succesfull to send. Just Wait Send Email or any Contact for Approval Request');
        }
    }
    public function receipt($id)
    {
        $reservation = Reservation::findOrFail(decrypt($id));
        $tour_menu = [];
        $other_addons = [];
        $tour_addons = [];
        $rooms = [];
        $contacts = WebContent::all()->first()->contact['main'] ?? [];
        // Rooms
        foreach($reservation->roomid as $item){
            $rooms[$item]['no'] = Room::findOrFail($item)->room_no;
            $rooms[$item]['name'] = Room::findOrFail($item)->room->name;
        }
        
        $count = 0;
        foreach($reservation->transaction as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menuID = (int)str_replace('tm','', $key);
                $tour_menu[$count]['title'] = TourMenu::find($tour_menuID)->tourMenu->title;
                $tour_menu[$count]['type'] = TourMenu::find($tour_menuID)->type;
                $tour_menu[$count]['pax'] = TourMenu::find($tour_menuID)->pax;
                $tour_menu[$count]['price'] = $reservation->transaction['tm'.$tour_menuID]['price'];
                $tour_menu[$count]['amount'] = $reservation->transaction['tm'.$tour_menuID]['amount'];
            }
            // Rate
            if (strpos($key, 'rid') !== false) {
                $rateID = (int)str_replace('rid','', $key);
                $rate['name'] = RoomRate::find($rateID)->name;
                $rate['price'] = $reservation->transaction['rid'.$rateID]['price'];
                $rate['amount'] = $reservation->transaction['rid'.$rateID]['amount'];
            }
            if (strpos($key, 'OA') !== false && is_array($item)) {
                $OAID = (int)str_replace('OA','', $key);
                foreach($item as $key => $dataAddons){
                    $other_addons[$count+$key]['title'] = $reservation->transaction['OA'.$OAID][$key]['title'];
                    $other_addons[$count+$key]['price'] = $reservation->transaction['OA'.$OAID][$key]['price'];
                    $other_addons[$count+$key]['pcs'] = $reservation->transaction['OA'.$OAID][$key]['pcs'];
                    $other_addons[$count+$key]['amount'] = $reservation->transaction['OA'.$OAID][$key]['amount'];
                }
            }
            if (strpos($key, 'TA') !== false && is_array($item)) {
                $TAID = (int)str_replace('TA','', $key);
                foreach($item as $key => $tourAddons){
                    $tour_addons[$count]['title'] = $reservation->transaction['TA'.$TAID][$key]['title'];
                    $tour_addons[$count]['price'] = $reservation->transaction['TA'.$TAID][$key]['price'];
                    $tour_addons[$count]['amount'] = $reservation->transaction['TA'.$TAID][$key]['amount'];
                }
            }
            $count++;
        }
        unset($count);
        
        return view('reservation.receipt',  ['r_list' => $reservation, 'menu' => $tour_menu, 'tour_addons' => $tour_addons, 'other_addons' => $other_addons, 'rate' => $rate, 'rooms' => $rooms, 'contacts' => $contacts]);
    }
}