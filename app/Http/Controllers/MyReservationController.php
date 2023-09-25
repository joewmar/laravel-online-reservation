<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\System;
use App\Models\Archive;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Validator as ValidationValidator;

class MyReservationController extends Controller
{   
    private function systemNotification($text, $link = null){
        $systems = System::whereBetween('type', [0, 1])->get();
        $keyboard = null;
        if(isset($link)){
            $keyboard = [
                [
                    ['text' => 'View', 'url' => $link],
                ],
            ];
        }
        foreach($systems as $system){
            if(isset($system->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $system->telegram_chatID), $text, $keyboard);
        }

        Notification::send($systems, new SystemNotification('Employee Action from '.auth()->guard('system')->user()->name().': ' . Str::limit($text, 10, '...'), $text, route('system.notifications')));
    }
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
                    $tour_menu[$count]['title'] = $reservation->transaction['TA'.$TAID][$key]['title'];
                    $tour_menu[$count]['price'] = $reservation->transaction['TA'.$TAID][$key]['price'];
                    $tour_menu[$count]['amount'] = $reservation->transaction['TA'.$TAID][$key]['amount'];
                }
            }
            $count++;
        }
        unset($count);
        return view('users.reservation.show',  ['activeNav' => 'My Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'rooms' => implode(',', $rooms), 'rate' => $rate, 'total' => $total, 'other_addons' => $other_addons]);
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

        if(($request['check_in'] === $reservation->check_in && $request['check_out'] === $reservation->check_out)){
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
    public function receipt($id){
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
                $tour_menu[$count]['title'] = $item['title'];
                $tour_menu[$count]['price'] = $item['price'];
                $tour_menu[$count]['amount'] = $item['amount'];
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
    public function updateDetails(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status > 0) abort(404);
        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');

        if(checkAvailRooms($request['pax'] ?? 0, $request['check_in'], $request['check_out']) && !empty($request['pax'])) {
            if(isset($view)) return redirect()->route($view)->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
            else return back()->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
        }
        $web_contents = WebContent::all()->first();
        if(isset($web_contents->from) && isset($web_contents->to)){
            if(Carbon::createFromFormat('Y-m-d', $request['check_in'])->timestamp >= Carbon::createFromFormat('Y-m-d', $web_contents->from)->timestamp && Carbon::createFromFormat('Y-m-d', $request['check_in'])->timestamp <= Carbon::createFromFormat('Y-m-d', $web_contents->to)->timestamp) {
                if(isset($view)) return redirect()->route($view)->with('error', 'Sorry, this date cannot be allowed due ' . $web_contents->reason)->withInput($request->input());
                else return back()->with('error', 'Sorry, this date cannot be allowed due ' . $web_contents->reason)->withInput($request->input());
            }
        }

        $validator = Validator::make($request->all('accommodation_type'), [
            'accommodation_type' => ['required'],
        ], [
            'required' => 'Need fill up first',
        ]);
        $dayTour = [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after_or_equal:'.Carbon::now()->addDays(1)],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['check_in'], 'date_equals:'.$request['check_in']],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required'],

        ];
        $overnight = [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)->format('Y-m-d')],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required'],
        ];
        $roomOnly = [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['check_in']],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required'],
        ];
        

        // dd($dayTour);
        if($request['accommodation_type'] == 'Day Tour'){
            $validated = Validator::make($request->all(), $dayTour, [
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Overnight'){
            $validated = Validator::make($request->all(),  $overnight, [
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'check_out.after_or_equal' => 'Choose within 2 or 3 days (Overnight)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Room Only'){
            $validated = Validator::make($request->all(), $roomOnly, [
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
            ]);
        }
        else{
            return back()->with('error', $validator->errors()->all())->withInput($request->all());
        }
        if ($validated->fails()) {            
            return back()
            ->with('error', $validator->errors()->all())
            ->withInput($request->all());
        }

        $validated = $validated->validated();
        if($reservation->update($validated)){
            return redirect()->route('user.reservation.show', $id)->with('success', 'Reservation Details was updated');
        }
    }
    public function editTour($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status > 2) abort(404);
        // dd('Panis');
        $chooseMenu = [];
        $count = 0;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $id = explode('tm',$key)[1];
                $chooseMenu[$count]['id'] = $id;
                $chooseMenu[$count]['title'] = $item['title'];
                $chooseMenu[$count]['price'] = $item['price'];
                $chooseMenu[$count]['amount'] = $item['amount'];
            }
            if (strpos($key, 'TA') !== false && is_array($item)) {
                $id = explode('TA', $key)[1];
                foreach($item as $key => $tourAddons){
                    $chooseMenu[$count]['id'] =  $id.$count;
                    $chooseMenu[$count]['title'] = $tourAddons['title'];
                    $chooseMenu[$count]['price'] = $tourAddons['price'];
                    $chooseMenu[$count]['amount'] = $tourAddons['amount'];
                }
                
            }

            $count++;
        }
        // dd($chooseMenu);
        return view('users.reservation.show-tour', [
            'activeNav' => 'My Reservation', 
            'r_list' => $reservation, 
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
            'cmenu' => $chooseMenu ,
            "user_days" => isset($noOfday) ? $noOfday : 1,
        ]); 
    }
    public function updateTour(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status > 2) abort(404);
        $transaction = $reservation->transaction;
        // dd($request->all());
        $validate = Validator::make($request->all(), [
            'tour_menu' => ['required'],
            'new_pax' => ['required', 'numeric', 'min:1' , 'max:'.$reservation->pax],
        ], [
            'tour_menu.required' => 'Your Cart is empty',
            'new_pax.required' => 'Required to fill up number of guest ',
            'new_pax.numeric' => 'Number of guest should be number only',
            'new_pax.min' => 'Number of guest should be 1 and above',
            'new_pax.max' => 'INumber of guest should be '.$reservation->pax.' guest below',
        ]);     
        if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput();
        
        $validated = $validate->validate();

        // Remove All Tours
        foreach($transaction ?? [] as $key => $item){
            if (strpos($key, 'TA') !== false || strpos($key, 'tm') !== false) {
                unset($transaction[$key]);
            }
        }
        foreach($validated['tour_menu'] as $item){
            $tours = TourMenu::find($item);
            $transaction['TA'.$item][] = [
                'title' => $tours->tourMenu->title . ' ' . $tours->type . '('.$tours->pax.' pax)',
                'price' => $tours->price ?? 0,
                'amount' => ((double)$tours->price ?? 0) * (int)$validated['new_pax'],
            ];
        }
        if($reservation->update(['transaction' => $transaction, 'tour_pax' => $validated['new_pax']])){
            return redirect()->route('user.reservation.show', $id)->with('success', 'Tour Menu was updated');
        }
    }
    public function updateRequest(Request $request, $id){
        // dd($request->all());
        $validate = Validator::make($request->all('service_message'), [
            'service_message' => ['required'],
        ], ['required' => 'Required (Request Service)']);
        if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
        $validate = $validate->validate();
        $reservation = Reservation::findOrFail(decrypt($id));
        $messages = $reservation->message;
        $messages['request'] =   $validate['service_message'];
        if($reservation->update(['message' => $messages])){
            return redirect()->route('user.reservation.show', $id)->with('success', 'Service Request Message was updated');
        }
    }
    public function updateCancel(Request $request, $id){
        // dd($request->all());
        $validate = Validator::make($request->all('cancel_message'), [
            'cancel_message' => ['required'],
        ], ['required' => 'Need to input the reason']);
        if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
        $validate = $validate->validate();
        $reservation = Reservation::findOrFail(decrypt($id));
        $messages = $reservation->message;
        $messages['cancel'] = [
            'message' =>  $validate['cancel_message'],
        ];
        if($reservation->update(['message' => $messages])){
            return redirect()->route('user.reservation.show', $id)->with('success', 'Cancel Request Message was updated');
        }
    }
    public function updateReschedule(Request $request, $id){
        // dd($request->all());
        $reservation = Reservation::findOrFail(decrypt( $id));
        $messages = $reservation->message;
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

        if(($request['check_in'] === $messages['reschedule']['check_in'] && $request['check_out'] === $messages['reschedule']['check_out'] )){
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
            return back()
            ->with('error', $validator->errors()->all())
            ->withInput();
        }
        $validator = $validator->validate();

        $messages = $reservation->message;
        $messages['reschedule'] = [
            'message' => $validator['reschedule_message'],
            'check_in' => $validator['check_in'],
            'check_out' => $validator['check_out'],
        ];
        if($reservation->update(['message' => $messages])) return redirect()->route('user.reservation.show', $id)->with('success', 'Reschedule Request was updated');
        

    }
    public function destroyReservation(Request $request, $id){
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'required' => 'Need fill up first (:attribute)',
        ]);
        if ($validator->fails()) {            
            return back()->with('error', $validator->errors()->all());
        }
        $validator = $validator->validate();
        if(!Hash::check($validator['password'], auth('web')->user()->password)){
            return back()->with('error', 'Invalid Password');
        }
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->delete()) return redirect()->route('user.reservation.home')->with('success', 'Your Reservation was removed');

        
    }
}
