<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\System;
use App\Models\Archive;
// use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use App\Models\OnlinePayment;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Models\Addons;
// use App\Notifications\EmailNotification;
use Illuminate\Support\Facades\Validator;

class SystemReservationController extends Controller
{
    private $system_user;
    public function __construct()
    {
        $this->system_user = auth()->guard('system');
    }
    public function index(Request $request){
        $r_list = Reservation::latest()->paginate(5);
        // if($request->has('search')){
        //     $r_list  = Reservation::where('first_name', 'like', '%' . $request['search'] . '%')
        //     ->orWhere('last_name', 'like', '%' . $request['search'] . '%')
        //     ->orWhere('age', 'like', '%' . $request['search'] . '%')
        //     ->orWhere('nationality', 'like', '%' . $request['search'] . '%')
        //     ->orWhere('country', 'like', '%' . $request['search'] . '%')
        //     ->paginate(5);  
        // }
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
        if($request['tab'] === 'Previous'){
            $r_list = Archive::where('status', 0)->latest()->paginate(5);
        }
        if($request['tab'] === 'archive'){
            $r_list = Archive::latest()->paginate(5);
        }
        if($request['tab'] === 'cancelation'){
            $r_list = Archive::where('status', 2)->latest()->paginate(5);
        }
        if($request['tab'] === 'disaprove'){
            $r_list = Archive::where('status', 1)->latest()->paginate(5);
        }
        return view('system.reservation.index',  ['activeSb' => 'Reservation', 'r_list' => $r_list]);
    }
    public function search(Request $request){
        if(!empty($request['action'])){
            $param = [
                'rtab' => 'list', 
                'tab' => $request['action'],
                'search' => $request['search'],
            ];
        }
        else{
            $param = [
                'rtab' => 'list', 
                'search' => $request['search'],
            ];
        }
        return redirect()->route('system.reservation.home', Arr::query($param));
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
            if($reservation->status() == 'Previous') $color = '#64748b';
            if($reservation->status() == 'Cancel') $color = '#fb7185';
            if($reservation->status() == 'Disaprove') $color = '#C70039';
            if($reservation->status() == 'Reshedule') $color = '#f7e488';
            /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => done, 4 => canceled, 5 => disaprove, 6 => reshedule*/
            $arrEvent[] = [
                'title' =>  $reservation->userReservation->name() . ' from '. $reservation->userReservation->country . ' (' . $reservation->status() . ')', 
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
            }
            if (strpos($key, 'OA') !== false && is_array($item)) {
                $OAID = (int)str_replace('OA','', $key);
                foreach($item as $key => $dataAddons){
                    $other_addons[$key]['title'] = $reservation->transaction['OA'.$OAID][$key]['title'];
                    $other_addons[$key]['price'] = $reservation->transaction['OA'.$OAID][$key]['price'];
                    $other_addons[$key]['amount'] = $reservation->transaction['OA'.$OAID][$key]['amount'];
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
        return view('system.reservation.show',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'conflict' => $conflict, 'rooms' => implode(',', $rooms), 'rate' => $rate, 'total' => $total, 'other_addons' => $other_addons, 'tour_addons' => $tour_addons]);
    }
    public function edit($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $rooms = Room::all();
        $rate = RoomRate::all();
        $tour_menu = [];
        $other_addons = [];
        $tour_addons = [];
        $count = 0;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menuID = (int)str_replace('tm','', $key);
                $tour_menu[$count]['id'] = encrypt($key);
                $tour_menu[$count]['title'] = $reservation->transaction['tm'.$tour_menuID]['title'];
                $tour_menu[$count]['price'] = $reservation->transaction['tm'.$tour_menuID]['price'];
                $tour_menu[$count]['amount'] = $reservation->transaction['tm'.$tour_menuID]['amount'];
            }
            // Rate
            if (strpos($key, 'rid') !== false) {
                $rateID = (int)str_replace('rid','', $key);
                $your_rate['name'] = $reservation->transaction['rid'.$rateID]['title'];;
                $your_rate['price'] = $reservation->transaction['rid'.$rateID]['price'];
                $your_rate['amount'] = $reservation->transaction['rid'.$rateID]['amount'];
            }
            if (strpos($key, 'TA') !== false && is_array($item)) {
                $TAID = (int)str_replace('TA','', $key);
                foreach($item as $key => $tourAddons){
                    $tour_addons[$count]['id'] = encrypt($key);
                    $tour_addons[$count]['title'] = $reservation->transaction['TA'.$TAID][$key]['title'];
                    $tour_addons[$count]['price'] = $reservation->transaction['TA'.$TAID][$key]['price'];
                    $tour_addons[$count]['amount'] = $reservation->transaction['TA'.$TAID][$key]['amount'];
                }
            }
            if (strpos($key, 'OA') !== false && is_array($item)) {
                $OAID = (int)str_replace('OA','', $key);
                foreach($item as $key => $tourAddons){
                    $other_addons[$count]['id'] = encrypt($key);
                    $other_addons[$count]['title'] = $reservation->transaction['OA'.$OAID][$key]['title'];
                    $other_addons[$count]['price'] = $reservation->transaction['OA'.$OAID][$key]['price'];
                    $other_addons[$count]['amount'] = $reservation->transaction['OA'.$TAID][$key]['amount'];
                }
            }
            $count++;
        }
        unset($count);
        return view('system.reservation.edit',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'tour_addons' => $tour_addons, 'tour_menu' => $tour_menu, 'other_addons']);

    }
    public function updateRInfo(Request $request){
        dd($request->all());
    }
    public function receipt($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $tour_menu = [];
        $addtional_menu = [];
        $rooms = [];
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
                    $other_addons[$key]['title'] = $reservation->transaction['OA'.$OAID][$key]['title'];
                    $other_addons[$key]['price'] = $reservation->transaction['OA'.$OAID][$key]['price'];
                    $other_addons[$key]['amount'] = $reservation->transaction['OA'.$OAID][$key]['amount'];
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
        
        return view('reservation.receipt',  ['r_list' => $reservation, 'menu' => $tour_menu, 'tour_addons' => $tour_addons, 'other_addons' => $other_addons, 'rate' => $rate, 'rooms' => $rooms]);
    }
    public function showRooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status >= 1) abort(404);
        $rooms = Room::all();
        $rate = RoomRate::all();
        return view('system.reservation.show-room',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate]);
    }
    public function updateReservation(Request $request){
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0)->where('type', 1);
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status >= 1) abort(404);
        $validator = Validator::make($request->all(), [
            'passcode' =>  ['required', 'numeric', 'digits:4'],
            'room_rate' =>  ['required', 'numeric'],
            'room_pax.*' => ['required', 'numeric'],
        ]);
        $error = [];
        $roomReservation = [];
        if(!Hash::check($request['passcode'], $system_user->passcode)) $error[] = 'Invalid Passcode';
        if(!empty($request['room_pax'])){
            // $arrCus = array();
            $room_no = [];
            $room_pax = [];
            $totalRoomPax = 0;
            $count = 0;
            foreach($request['room_pax'] as $key => $item){
                $room = Room::find(decrypt($key));
                if($room->room->availability === true && $room->checkAvailability()){
                    $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') was not available';
                }
                if(array_key_exists($reservation->id, $item->customer ?? [])){
                    $error[$key] = $reservation->userReservation->name() . 'was already avail on Room No.' . $room->room_no . ' (' . $room->room->name . ')';
                }
                if((int)$item == $room->room->max_occupancy) {
                    $error[$key] = 'Sorry, you cannot choose Room No.' . $room->room_no . ' (' . $room->room->name . ') due customer guest was '. $reservation->pax . 'pax and your choose Room No. ' . $room_no[$count-1] . ' with '.$room_pax[$count-1] .' pax ';
                    break;
                }
                if($totalRoomPax == $reservation->pax ) {
                    $error[$key] = 'Sorry, you cannot choose Room No.' . $room->room_no . ' (' . $room->room->name . ')  due avail '. $item .' pax on ' . implode(', ', $room_no);
                    break;
                } 
                $roomReservation[$room->id] =  $item;
                $totalRoomPax += (int)$item;
                $room_no[] = 'Room No.' . $room->room_no;
                $room_pax[] = $item;
                $count ++;

            }
            unset($room_details, $totalRoomPax, $room_pax ,$room_no);
        }
        else{
            return back()->with('error', 'Required to choose rooms');
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
                $transaction = $reservation->transaction;
                $transaction['rid'.$rate->id]['title'] = $rate->name;
                $transaction['rid'.$rate->id]['price'] = $rate->price;
                $transaction['rid'.$rate->id]['amount'] = $rate->price * $reservation->getNoDays();
                $transaction['rid'.$rate->id]['amount'] = $rate->price * $reservation->getNoDays();
                // Update Reservation
                $reserved = $reservation->update([
                    'roomid' => array_keys($roomReservation),
                    'roomrateid' => $rate->id,
                    'transaction' => $transaction,
                    'status' => 1,
                ]);
                $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
                $reservation->save();

                $rooms = Room::all();
                foreach($rooms as $room){
                    if ($room->checkAvailability()) continue;
                }
                
                // Update Room Availability
                if($reserved){
                    foreach($roomReservation as $key => $item){
                        $room = Room::find($key);
                        $customer = $room->customer;
                        $customer[$reservation->id] = $item;
                        $room->update([
                            'customer' => $customer,
                        ]);
                    }
                    unset($roomReservation);
                    $tour_menu = [];
                    // Get Tour Menu for Mail
                    $count = 0;
                    foreach($reservation->transaction ?? [] as $key => $item){
                        if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                            $tour_menuID = (int)str_replace('tm','', $key);
                            $tour_menu[$count]['title'] = $reservation->transaction['tm'.$tour_menuID]['title'];
                            $tour_menu[$count]['price'] = $reservation->transaction['tm'.$tour_menuID]['price'] . ' philippnine peso';
                            $tour_menu[$count]['amount'] = $reservation->transaction['tm'.$tour_menuID]['amount'] * (int)$reservation->tour_pax . ' philippnine peso';
                        }
                        $count++;
                    }
                    
                    $roomDetails = [];
                    foreach($reservation->roomid as $item){
                        $roomDetails[] = 'Room No' . Room::find($item)->room_no . '('.Room::find($item)->room->name.')';
                    }
                    $text = 
                    "Employee Action: Approved Reservation !\n" .
                    "Name: ". $reservation->userReservation->name() ."\n" . 
                    "Age: " . $reservation->age ."\n" .  
                    "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
                    "Country: " . $reservation->userReservation->country ."\n" . 
                    "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y') ."\n" . 
                    "Check-out: " . Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y') ."\n" . 
                    "Type: " . $reservation->accommodation_type ."\n" . 
                    "Rooms: " . implode(',', $roomDetails) ."\n" . 
                    "Who Approve: " . $system_user->name();
        
                    // foreach($admins as $admin){
                    //     if(!empty($admin->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, 'bot2');
                    // }
                    telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID'), $text, null, 'bot2');

                    $text = null;
                    $url = null;
                    if($reservation->payment_method == "Gcash"){
                        $url = route('reservation.gcash', encrypt($reservation->id));
                    }
                    // if($reservation->payment_method == "PayPal"){
                    //     $url = route('reservation.paypal', encrypt($reservation->id));
                    // }

                    $details = [
                        'name' => $reservation->userReservation->name(),
                        'title' => 'Reservation has Confirmed',
                        'body' => 'Your Reservation has Confirmed, Be on time at ' . Carbon::createFromFormat('Y-m-d', $reservation->check_in, Carbon::now()->timezone->getName())->format('F j, Y') . ' to ' . Carbon::createFromFormat('Y-m-d', $reservation->check_in, Carbon::now()->timezone)->addDays(3)->format('F j, Y'),
                        "age" => $reservation->age,  
                        "nationality" =>  $reservation->userReservation->nationality , 
                        "country" =>  $reservation->userReservation->country, 
                        "check_in" =>  Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'), 
                        "check_out" =>  Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y'), 
                        "accommodation_type" =>  $reservation->accommodation_type,
                        "payment_method" =>  $reservation->payment_method,
                        "pax" =>  $reservation->pax,
                        "tour_pax" =>  $reservation->tour_pax,
                        'menu' => $tour_menu,
                        "room_no" =>  implode(',', $roomDetails),
                        "room_type" => $rate->name,
                        'room_rate' => $rate->price,
                        'total' => $reservation->total,
                        'receipt_link' => route('reservation.receipt', encrypt($reservation->id)),
                        'payment_link' => $url,
                        'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('F j Y \a\t g:iA'),
                    ];
                    unset($roomDetails);
                    // Notification::send($reservation->userReservation, new EmailNotification($project));
                    Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationConfirmation($details['title'], $details, 'reservation.confirm-mail'));
                    return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Confirmed');
                    unset($details, $text, $url);
                }
        }

    }
    public function updateCheckin(Request $request){
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0)->where('type', 1);
        $reservation = Reservation::findOrFail(decrypt($request->id));
        $transaction = $reservation->transaction;
        $downpayment = $transaction['payment']['downpayment'] ?? 0;
        $balance = abs($reservation->getTotal() - $downpayment);
        if($request['payments'] == 'partial'){
            $validated = $request->validate([
                'payments' => ['required'],
                'another_payment' =>['required', 'numeric', 'max:'.(int)$balance],
            ], [
                'required' => 'Required to choose',
                'max' => 'Fill the amount up to ₱' . number_format($balance, 2),
            ]);
            $transaction['payment']['cinpay'] = (double)$validated['another_payment'];
            $message = 'Partial Payment (₱ '.number_format($validated ['another_payment'], 2).')';

        }
        else if($request['payments'] == 'fullpayment'){
            $validated = $request->validate([
                'payments' => ['required'],
            ], [
                'required' => 'Required to choose',
                'max' => 'Fill the amount up to ₱' . number_format($balance, 2),
            ]);
            $transaction['payment']['cinpay'] = $balance;
            $message = 'Full Payment (₱ '.number_format($balance, 2).')';
        }
        else{
            return back();
        }
        $updated = $reservation->update([
            'transaction' => $transaction,
            'status' => 2,
        ]);
        unset($transaction);
        $text = 
        "Employee Action: Check-in !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Payment: " . $message  ."\n" . 
        "Who Approve: " . $system_user->name() ;
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Check-in',
            'body' => 'You now checked in at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A'),
        ];
        if($updated){
            foreach($admins as $admin) if($admin->telegram_chatID != null) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, 'bot2');
            telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', ), $text, null, 'bot2');
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            unset($text, $details);
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked in');
        }

    }
    public function updateCheckout(Request $request){
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0)->where('type', 1);
        $reservation = Reservation::findOrFail(decrypt($request->id));
        $validated = $request->validate([
            'fullpay' => ['accepted'],
        ],[
            'accepted' => 'Before proceeding, full payment must be made first.'
        ]);
        if($validated) {
            $reservation->update(['status' => 3]);
            $reservation->checkedOut();
        }
        $text = 
        "Employee Action: Check-out !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Who Approve: " . $system_user->name() ;
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Check-out',
            'body' => 'You now checked out at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A'),
        ];   
        // foreach($admins as $admin) if($admin->telegram_chatID != null) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, 'bot2');
        telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID'), $text, null, 'bot2');
        Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
        unset($text, $details);
        return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked out');
        
    }
    public function disaprove($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $tour_menu = [];
        $count = 0;
        foreach($reservation->transaction as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menuID = (int)str_replace('tm','', $key);
                $tour_menu[$count]['title'] = TourMenu::find($tour_menuID)->tourMenu->title;
                $tour_menu[$count]['type'] = TourMenu::find($tour_menuID)->type;
                $tour_menu[$count]['pax'] = TourMenu::find($tour_menuID)->pax;
                $tour_menu[$count]['price'] = $reservation->transaction['tm'.$tour_menuID];
                $tour_menu[$count]['amount'] = $reservation->transaction['tm'.$tour_menuID]['amount'];
            }
            if (strpos($key, 'rid') !== false) {
                $rateID = (int)str_replace('rid','', $key);
                $rates['name'] = RoomRate::find($rateID)->name;
                $rates['amount'] = RoomRate::find($rateID)->price;
                $rates['price'] = $reservation->transaction['rid'.$rateID];
            }
            $count++;
        }
        unset($count);
        return view('system.reservation.disaprove',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu]);
    }
    public function disaproveStore(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $system_user = $this->system_user->user();

        if($request['reason'] === 'Other'){
            $validated = $request->validate([
                'reason' => ['required'],
                'message' => ['required'],
                'passcode' => ['required', 'digits:4', 'numeric'],
            ], [
                'required' => 'Need to fill up this form'
            ]);

        }
        else{
            $validated = $request->validate([
                'reason' => ['required'],
                'passcode' => ['required', 'digits:4', 'numeric'],
            ], [
                'required' => 'Need to fill up this form'
            ]);
            $validated['message'] =  $validated['reason'];
        }
        if(!Hash::check($validated['passcode'], $system_user->passcode))  return back()->with('error', 'Invalid Passcode, Try Again')->withInput($validated);
        $messages = $reservation->message;
        $messages['disaprove'] = $validated['message'];
        $updated = $reservation->update(['status' => 6, 'message' => $messages ]); // delete on reservation
        if($updated){
            $text = 
            "Employee Action: Disaprove Reservation !\n" .
            "Name: ". $reservation->userReservation->name() ."\n" . 
            "Age: " . $reservation->age ."\n" .  
            "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
            "Country: " . $reservation->userReservation->country ."\n" . 
            "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y') ."\n" . 
            "Check-out: " . Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y') ."\n" . 
            "Type: " . $reservation->accommodation_type ."\n" . 
            "Who Disaprove?: " . $system_user->name() . ' (' . $system_user->role() . ')' ;
            "Reason to Disaprove: " . $messages['disaprove'];
            // Send Notification to 
            // $keyboard = [
            //     [
            //         ['text' => 'View Details', 'url' => route('system.reservation.show', encrypt($reserve_info->id))],
            //     ],
            // ];
            telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $system_user->telegram_chatID), $text, null, 'bot2');
    
            $text = null;
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Disaprove',
                'body' => 'Your Reservation are disapprove due of ' . $messages['disaprove']. 'Sorry for waiting. Please try again to make reservation in another dates',
            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            unset($details);
            return redirect()->route('system.reservation.home')->with('success', 'Disaprove of ' . $updated->userArchive->first_name . ' ' . $updated->userArchive->last_name . ' was Successful');
        }
        else{
            return back()->with('error', 'Something Wrong on database, Try Again')->withInput($validated);
        }
    }
    public function showOnlinePayment($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        return view('system.reservation.onlinepayment.index', ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function storeOnlinePayment(Request $request, $id){
        $system_user = $this->system_user->user();
        $validated = $request->validate(['amount' => ['required', 'numeric']]);
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        $admins = System::all()->where('type', 0)->where('type', 1);
        $reservation = Reservation::findOrFail($online_payment->reservation_id);
        $downpayment = $reservation->transaction;
        if(isset($downpayment['payment']['downpayment'])) $downpayment['payment']['downpayment'] += (double)$validated['amount'];
        else $downpayment['payment']['downpayment'] = (double)$validated['amount'];
        $reservation->update(['transaction' => $downpayment]);
        $online_payment->approval = true;
        $online_payment->save();
        if($reservation->downpayment >= 1000){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Your online payment was approved',
                'body' => 'Downpayment: ' .  $reservation->downpayement,
            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
        }
        else{
            $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
            if($reservation->payment_method == "Gcash") $url = route('reservation.gcash', encrypt($reservation->id));
            
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Your online payment was approved, but the amount you paid was insufficient. There is a chance for you to make another payment',
                'body' => 'Downpayment: ' .  $downpayment['payment']['downpayment'] .' but minimuim payment is 1000 philippine pesos' ,            
                'link' => $url,
                'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y'),
            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.online-payment-mail', $details['title']));
        }
        $text = 
        "Employee Action: Online Payment !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Amount: " . $reservation->userReservation->nationality  ."\n" . 
        "Who Approve: " . $system_user->name() ;
        foreach($admins as $user){
            if(isset($user->telegram_chatID))
                telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, null, 'bot2');
        }
        unset($downpayment);
        return redirect()->route('system.reservation.show.online.payment', encrypt($reservation->id))->with('success', 'Approved payment successful');
    }
    public function disaproveOnlinePayment(Request $request, $id){
        $validated = $request->validate(['reason' => ['required']]);
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        $reservation = Reservation::findOrFail($online_payment->reservation_id);
        $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        $reservation->save();
        $online_payment->approval = false;
        $online_payment->save();

        if($reservation->payment_method == "Gcash"){
            $url = route('reservation.gcash', encrypt($reservation->id));
        }
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Your online payment was approved',
            'body' => 'Reason:  ' .  $validated['reason'] . '. I will give you a chance to make payment',
            'link' => $url,
            'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y'),
        ];
        Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.online-payment-mail', $details['title']));
    }
    public function storeForcePayment(Request $request, $id){
        $system_user = $this->system_user->user();
        $reservation = Reservation::findOrFail(decrypt($id));
        $admins = System::all()->where('type', 0)->where('type', 1);
        $validated = Validator::make($request->all('amount'), [
            'amount' => ['required', 'numeric'],
        ], ['required' => 'Required to fill up', 'numeric' => 'Number only']);
        if($validated->fails()){
            return back()->with('error', $validated->errors()->all());
        }
        $validated = $validated->validate();
        $downpayment = $reservation->transaction;
        $downpayment['payment']['downpayment'] = (double)$validated['amount'];
        $updated = $reservation->update(['transaction' => $downpayment]);
        if($updated){
            $text = 
            "Employee Action: Force Payment !\n" .
            "Name: ". $reservation->userReservation->name() ."\n" . 
            "Age: " . $reservation->age ."\n" .  
            "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
            "Who Approve: " . $system_user->name() ;
            foreach($admins as $user){
                if(isset($user->telegram_chatID))
                    telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, null, 'bot2');
            }
            return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . 'was now paid on ₱ ' . number_format($validated['amount'], 2));
        } 
    }
    public function showAddons(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($request->has('tab') && $request['tab'] === 'TA'){
            $tour_list = TourMenuList::all();
            $tour_menu = TourMenuList::all();
            $tour_category = TourMenuList::distinct()->get('category');
            if(old('tour_menu')){
                $temp = [];
                foreach(old('tour_menu') as $key => $item){
                    $temp[$key]['id'] = TourMenu::find($item)->id;
                    $temp[$key]['title'] = TourMenu::find($item)->tourMenu->title;
                    $temp[$key]['type'] =  TourMenu::find($item)->type . '('.TourMenu::find($item)->pax .' guest)';
                    $temp[$key]['price'] = number_format(TourMenu::find($item)->price, 2);
                }
            }
        }

        return view('system.reservation.addons.index',  [
            'activeSb' => 'Reservation', 
            'r_list' => $reservation,
            'tour_lists' => $tour_list ?? [], 
            'tour_category' =>  $tour_category ?? [], 
            'tour_menus' => $tour_menu ?? [],
            'temp_tour' => $temp ?? [],
            'addons_list' => Addons::all(),
            'user_days' => $reservation->getNoDays(),
        ]);
    }
    public function updateAddons(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $transaction = $reservation->transaction;
        if($request->has('tab') && $request['tab'] == 'TA'){
            $validate = Validator::make($request->all(), [
                'tour_menu' => ['required'],
                'new_pax' => ['required', 'numeric'],
                'passcode' => ['required', 'numeric', 'digits:4'],
            ], [
                'tour_menu.required' => 'Your Cart is empty',
                'new_pax.required' => 'Required to fill up number of guest ',
                'new_pax.numeric' => 'Number of guest should be number only',
            ]);     
            if($validate->fails()){
                return back()->with('error', $validate->errors()->all())->withInput();
            }
            $validated = $validate->validate();
            if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid passcode')->withInput($validated);
            foreach($validated['tour_menu'] as $item){
                $transaction['TA'.$item][] = [
                    'title' => TourMenu::find($item)->tourMenu->title ?? '',
                    'price' => TourMenu::find($item)->price ?? 0,
                    'amount' => ((double)TourMenu::find($item)->price ?? 0) * (int)$validated['new_pax'],
                ];
            }
        }
        else{
            $validated = $request->validate([
                'addons' => ['required'],
                'pcs' => ['required', 'numeric'],
                'passcode' => ['required', 'numeric', 'digits:4'],
            ]);
            if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid passcode')->withInput($validated);
            $id = decrypt($validated['addons']);
            $adddon = Addons::find($id);
            $transaction = $reservation->transaction;
            $transaction['OA'.$id][] = [
                'title' => $adddon->title,
                'amount' => $adddon->price * (int)$validated['pcs'],
                'pcs' => $validated['pcs'],
                'price' => $adddon->price ,
            ];  
        }
        $updated = $reservation->update([
            'transaction' => $transaction,
        ]);
        if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Other Add-ons for '.$reservation->userReservation->name().' was successful');

    }
    public function showExtend($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        return view('system.reservation.extend.index',  ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function updateExtend(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $validated = $request->validate([
            'no_days' => ['required', 'numeric', 'min:1'],
        ]);
        $extended = Carbon::now('Asian/Manila')->addDays((int)$validated['no_days'])->format('Y-m-d');
        $rate = $reservation->transaction;
        $rate['rid'.$reservation->roomRate->id]['amount'] = $rate['rid'.$reservation->roomRate->id]['price'] * $reservation->getNoDays();
        $updated = $reservation->update([
            'check_out' => $extended, 
            'status' => 2, 
            'transaction' => $rate
        ]);
        if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->useReservation()->name() . ' was extend in ' . ($validated['no_days'] > 1 ? $validated['no_days'] . ' days' : $validated['no_days'] . ' day'));
    }
}
