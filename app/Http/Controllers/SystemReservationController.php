<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\System;
use App\Models\Archive;
use App\Models\RoomList;
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
use App\Notifications\EmailNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;

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
    public function create(){
        if(request()->has(['dy', 'px', 'at'])){
            $rooms = Room::all();
            $rates = RoomRate::all();
        }
        return view('system.reservation.create.step1',  ['activeSb' => 'Reservation', 'rooms' => $rooms ?? '', 'rates' => $rates ?? '']);
    }
    public function storeStep1(Request $request){
        // Check in (startDate to endDate) trim convertion
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

        $validated = null;
        if($request['accommodation_type'] === 'Day Tour'){
            $validated = $request->validate([
                'days' => ['required', 'numeric', 'min:1', 'max:1'],
                // 'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after:'.Carbon::now()->addDays(2)],
                // 'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                // 'check_in.unique' => 'Sorry, this date is not available',
                // 'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'days.min' => 'Choose only one day (Day Tour)',
                'days.max' => 'Choose only one day (Day Tour)',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Overnight'){
            $validated = $request->validate([
                'days' => ['required', 'numeric', 'min:2', 'max:3'],
                // 'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                // 'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                // 'check_in.unique' => 'Sorry, this date is not available',
                // 'check_in.after' => 'Choose date with 2 to 3 days',
                // 'check_out.unique' => 'Sorry, this date is not available',
                'days.min' => 'Choose date with 2 to 3 days',
                'days.max' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
                'check_out.after_or_equal' => 'Choose within 2 day and above (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Room Only'){
            $validated = $request->validate([
                'days' => ['required', 'numeric', 'min:1'],
                // 'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                // 'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', 'max:'.(string)RoomList::max('max_occupancy')],
                'payment_method' => ['required'],
            ], [
                // 'check_in.unique' => 'Sorry, this date is not available',
                // 'check_in.after' => 'Choose date with 2 to 3 days',
                // 'check_out.unique' => 'Sorry, this date is not available',
                'days.min' => 'Choose within one day',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
            $reservationInfo = [
                "cin" =>   $validated['check_in'] ?? '',
                "cout" => $validated['check_out'] ?? '',
                "at" => $validated['accommodation_type'] ??  '',
                "px" => $validated['pax'] ?? '',
                "py" => $validated['payment_method'] ?? '',
            ];
            $reservationInfo = encryptedArray($reservationInfo);
            if(session()->has('rinfo')) foreach($reservationInfo as $key => $item) session('rinfo')[$key] = $reservationInfo[$key]; 
            else session(['rinfo' => $reservationInfo]);
            return redirect()->route('reservation.details');

        }
        else{
            return back()->withErrors(['accommodation_type' => "Choose the Accommodation type"])->withInput();
        }
        if($validated){
            $getParamStep1 = [
                "dy" =>  encrypt($validated['days']),
                "px" =>  encrypt($validated['pax']),
                "at" =>  encrypt($validated['accommodation_type']),
            ];
            return redirect()->route('system.reservation.create', [Arr::query($getParamStep1), '#rooms']);
        }

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
            $arrEvent[] = [
                'title' =>  $reservation->userReservation->name() . ' (' . $reservation->status() . ')', 
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
        $rates = [];
        if($reservation->roomid){
            foreach($reservation->roomid as $item){
                $rooms[] = 'Room No.' . Room::find($item)->room_no . ' ('.Room::find($item)->room->name.')';
            }
        }

        $conflict = Reservation::all()->where('check_in', $reservation->check_in)->where('status', 0)->except($reservation->id);;
        // dd($reservation->amount);

        if($reservation->accommodation_type != 'Room Only'){
            $count = 0;
            foreach($reservation->amount as $key => $item){
                if (strpos($key, 'tm') !== false) {
                    $tour_menuID = (int)str_replace('tm','', $key);
                    $tour_menu[$count]['title'] = TourMenu::find($tour_menuID)->tourMenu->title;
                    $tour_menu[$count]['type'] = TourMenu::find($tour_menuID)->type;
                    $tour_menu[$count]['pax'] = TourMenu::find($tour_menuID)->pax;
                    $tour_menu[$count]['price'] = $reservation->amount['tm'.$tour_menuID];
                    $tour_menu[$count]['amount'] = $reservation->amount['tm'.$tour_menuID] * (int)$reservation->tour_pax;
                }
                if (strpos($key, 'rid') !== false) {
                    $rateID = (int)str_replace('rid','', $key);
                    $rates['name'] = RoomRate::find($rateID)->name;
                    $rates['no_days'] = checkDiffDates($reservation->check_in, $reservation->check_out);
                    $rates['amount'] = RoomRate::find($rateID)->price;
                    $rates['price'] = $reservation->amount['rid'.$rateID];
                }
                $count++;
            }
            unset($count);
        }
        return view('system.reservation.show',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'conflict' => $conflict, 'rooms' => implode(',', $rooms), 'rates' => $rates]);
    }
    public function receipt($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $tour_menu = [];
        $addtional_menu = [];
        
        $rates = RoomRate::findOrFail($reservation->roomrateid);
        $rooms = [];
        foreach($reservation->roomid as $item){
            $rooms[$item]['no'] = Room::findOrFail($item)->room_no;
            $rooms[$item]['name'] = Room::findOrFail($item)->room->name;
        }

                if($reservation->accommodation_type != 'Room Only'){
            $count = 0;
            foreach($reservation->amount as $key => $item){
                if (strpos($key, 'tm') !== false) {
                    $tour_menuID = (int)str_replace('tm','', $key);
                    $tour_menu[$count]['title'] = TourMenu::find($tour_menuID)->tourMenu->title;
                    $tour_menu[$count]['type'] = TourMenu::find($tour_menuID)->type;
                    $tour_menu[$count]['pax'] = TourMenu::find($tour_menuID)->pax;
                    $tour_menu[$count]['price'] = $reservation->amount['tm'.$tour_menuID];
                    $tour_menu[$count]['amount'] = $reservation->amount['tm'.$tour_menuID] * (int)$reservation->tour_pax;
                }
                if (strpos($key, 'rid') !== false) {
                    $rateID = (int)str_replace('rid','', $key);
                    $rates['name'] = RoomRate::find($rateID)->name;
                    $rates['amount'] = RoomRate::find($rateID)->price;
                    $rates['price'] = $reservation->amount['rid'.$rateID];
                }
                $count++;
            }
            unset($count);
        }
        return view('reservation.receipt',  ['r_list' => $reservation, 'menu' => $tour_menu, 'add_menu' => $addtional_menu, 'rate' => $rates, 'rooms' => $rooms]);
    }
    public function showRooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status >= 1) abort(404);
        $rooms = Room::all();
        $rates = RoomRate::all();
        return view('system.reservation.show-room',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rates]);
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
                $room = Room::find($key);
                if($room->room->availability === true ){
                    $error[$key] = 'Room No.' . $room->room_no . ' (' . $room->room->name . ') was not available';
                    break;
                }
                if((int)$item == $room->room->max_occupancy) {
                    $error[$key] = 'Sorry, you cannot choose Room No.' . $room->room_no . ' (' . $room->room->name . ') due customer guest was '. $reservation->pax . 'pax and your choose Room No. ' . $room_no[$count-1] . ' with '.$room_pax[$count-1] .' pax ';
                    break;
                }
                if($totalRoomPax == $reservation->pax ) {
                    $error[$key] = 'Sorry, you cannot choose Room No.' . $room->room_no . ' (' . $room->room->name . ')  due avail '. $item .' pax on ' . implode(', ', $room_no);
                    break;
                } 
                $roomReservation[$room->id] = [$reservation->id => $item];
                $totalRoomPax += (int)$item;
                $room_no[] = 'Room No.' . $room->room_no;
                $room_pax[] = $item;
                $count ++;

            }
            unset($room_details, $totalRoomPax, $room_pax ,$room_no);
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
                $total = 0;
                $amount = $reservation->amount;
                $amount['rid'.$rate->id] = $rate->price * (int)checkDiffDates($reservation->check_in, $reservation->check_out);
                foreach($amount as $item) {
                    $total += $item;
                }
                // Update Reservation
                $reserved = $reservation->update([
                    'roomid' => array_keys($roomReservation),
                    'roomrateid' => $rate->id,
                    'amount' => $amount,
                    'total' => $total,
                    'status' => 1,
                ]);
                $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
                $reservation->save();

                // Update Room Availability
                if($reserved){
                    foreach($roomReservation as $key => $item){
                        $room = Room::find($key);
                        if(empty($room->customer)){
                            $room->update([
                                'customer' => $item,
                            ]);
                        }
                        else{
                            $newCus = [];
                            foreach($room->customer as $key => $item) $newCus[$key] = $item;
                            $newCus[$reservation->id] = $room->id;
                            $room->update([
                                'customer' => $newCus,
                            ]);
                            unset($newCus);
                        }
                        $countOccupancy = 0;
                        // Check if Availability All
                        foreach ($room->customer as $key => $item) {
                            $arrPreCus[$key] = $item;
                            if($room->room->max_occupancy ==  $countOccupancy ){
                                $room->update(['availability' => true]);
                            }
                            else{
                                $room->update(['availability' => false]);
                                $countOccupancy += (int)$arrPreCus[$key];
                            }
                        }
                    }
                    unset($roomReservation, $countOccupancy);
                    $tour_menu = [];
                    // Get Tour Menu for Mail
                    if($reservation->accommodation_type != 'Room Only'){
                        $count = 0;
                        foreach($reservation->amount as $key => $item){
                            if (strpos($key, 'tm') !== false) {
                                $tour_menuID = (int)str_replace('tm','', $key);
                                $tour_menu[$count]['title'] = TourMenu::find($tour_menuID)->tourMenu->title;
                                $tour_menu[$count]['type'] = TourMenu::find($tour_menuID)->type;
                                $tour_menu[$count]['pax'] = TourMenu::find($tour_menuID)->pax;
                                $tour_menu[$count]['price'] = $reservation->amount['tm'.$tour_menuID];
                                $tour_menu[$count]['amount'] = $reservation->amount['tm'.$tour_menuID] * (int)$reservation->tour_pax;
                                $count++;
                            }
                        }
                        unset($count);
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
                    telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID'), $text, 'bot2');

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
                        'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y'),
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
        $validated = $request->validate([
            'payments' => ['required'],
            'another_payment' => Rule::when(($request['$request'] == 'partial'), ['required', 'numeric'], ['nullable']),
        ]);

        if($validated['payments'] == 'partial'){
            $validated['downpayment'] = (double)$reservation->downpayment + (double)$validated['another_payment'];
            $validated['status'] = 2;
        }
        else{
            $validated['downpayment'] = $reservation->total;
            $validated['status'] = 2;
        }
        $downpayment = [];
        // foreach($reservation->downpayment as $key => $item) $downpayment[$key] = $item;
        // $downpayment['checkin'] = $validated['downpayment'];
        $updated = $reservation->update([
            'downpayment' => (double)$validated['downpayment'],
            // 'downpayment' => $downpayment,
            'status' => (int)$validated['status'],
        ]);
        unset($downpayment);
        $text = 
        "Employee Action: Check-in !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Who Approve: " . $system_user->name() ;
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Check-in',
            'body' => 'You now checked in at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A'),
        ];
        if($updated){
            // foreach($admins as $admin) if($admin->telegram_chatID != null) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, 'bot2');
            telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID'), $text, null, 'bot2');
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            unset($text, $details);
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked in');
        }

    }
    public function disaprove($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $tour_menu = [];
                if($reservation->accommodation_type != 'Room Only'){
            $count = 0;
            foreach($reservation->amount as $key => $item){
                if (strpos($key, 'tm') !== false) {
                    $tour_menuID = (int)str_replace('tm','', $key);
                    $tour_menu[$count]['title'] = TourMenu::find($tour_menuID)->tourMenu->title;
                    $tour_menu[$count]['type'] = TourMenu::find($tour_menuID)->type;
                    $tour_menu[$count]['pax'] = TourMenu::find($tour_menuID)->pax;
                    $tour_menu[$count]['price'] = $reservation->amount['tm'.$tour_menuID];
                    $tour_menu[$count]['amount'] = $reservation->amount['tm'.$tour_menuID] * (int)$reservation->tour_pax;
                }
                if (strpos($key, 'rid') !== false) {
                    $rateID = (int)str_replace('rid','', $key);
                    $rates['name'] = RoomRate::find($rateID)->name;
                    $rates['amount'] = RoomRate::find($rateID)->price;
                    $rates['price'] = $reservation->amount['rid'.$rateID];
                }
                $count++;
            }
            unset($count);
        }
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

        $arrAcrhive = [
            "name" => $reservation->userReservation->name(),
            "age" => $reservation->age,
            "country" => $reservation->userReservation->country,
            "nationality" => $reservation->userReservation->nationality,
            "pax" => $reservation->pax,
            "accommodation_type" => $reservation->accommodation_type,
            "payment_method" => $reservation->payment_method,
            "age" => $reservation->age,
            // "menu" => $reservation->menu,
            "check_in" => $reservation->check_in,
            "check_out" => $reservation->check_out,
            "status" => 1,
            // "additional_menu" => $reservation->additional_menu,
            "amount" => $reservation->amount,
            "total" => $reservation->total,
            "message" => $validated['message'],
        ];
        $reservation->delete(); // delete on reservation
        $acrhived = Archive::create($arrAcrhive); // create on archive
        if($acrhived){
            $text = 
            "Employee Action: Disaprove Reservation !\n" .
            "Name: ". $acrhived->userArchive->first_name . " " . $acrhived->userArchive->last_name ."\n" . 
            "Age: " . $acrhived->age ."\n" .  
            "Nationality: " . $acrhived->userArchive->nationality  ."\n" . 
            "Country: " . $acrhived->userArchive->country ."\n" . 
            "Check-in: " . Carbon::createFromFormat('Y-m-d', $acrhived->check_in)->format('F j, Y') ."\n" . 
            "Check-out: " . Carbon::createFromFormat('Y-m-d', $acrhived->check_out)->format('F j, Y') ."\n" . 
            "Type: " . $reservation->accommodation_type ."\n" . 
            "Who Disaprove?: " . $system_user->name . ' (' . $system_user->role . ')' ;
            "Reason to Disaprove: " . $acrhived->message  ;
            // Send Notification to 
            // $keyboard = [
            //     [
            //         ['text' => 'View Details', 'url' => route('system.reservation.show', encrypt($reserve_info->id))],
            //     ],
            // ];
            if(!auth('system')->user()->type === 0){
                telegramSendMessage($system_user->telegram_chatID, $text, 'bot2');
            }
            $text = null;
            $details = [
                'name' => $acrhived->userArchive->first_name . ' ' . $acrhived->userArchive->last_name,
                'title' => 'Reservation Disaprove',
                'body' => 'Your Reservation are disapprove due of ' . $acrhived->message. 'Sorry for waiting. Please try again to make reservation in another dates',
            ];
            Mail::to(env('SAMPLE_EMAIL', $acrhived->userArchive->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            $details = null;
            return redirect()->route('system.reservation.home')->with('success', 'Disaprove of ' . $acrhived->userArchive->first_name . ' ' . $acrhived->userArchive->last_name . ' was Successful');
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
        $validated = $request->validate(['amount' => ['required', 'numeric']]);
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        $reservation = Reservation::findOrFail($online_payment->reservation_id);
        if(!empty($reservation->downpayment))  (double)$reservation->downpayment += (double)$validated['amount'];
        else $reservation->downpayment = (double)$validated['amount'];
        $reservation->save();
        $online_payment->approval = true;
        $online_payment->save();
        if($reservation->downpayment >= 1000){
            $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Your online payment was approved',
                'body' => 'Downpayment: ' .  $reservation->downpayement,

            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
        }
        else{
            if($reservation->payment_method == "Gcash"){
                $url = route('reservation.gcash', encrypt($reservation->id));
            }
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Your online payment was approved, but the amount you paid was insufficient. There is a chance for you to make another payment',
                'body' => 'Downpayment: ' .  $reservation->downpayment .' but minimuim payment is 1000 philippine pesos' ,            
                'link' => $url,
                'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y'),
            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.online-payment-mail', $details['title']));
        }

        return redirect()->route('system.reservation.show.online.payment', encrypt($reservation->id))->with('success', 'Approved payment successful');
    }
    public function disaproveOnlinePayment(Request $request, $id){
        $validated = $request->validate(['reason' => ['required']]);
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        $reservation = Reservation::findOrFail($online_payment->reservation_id);
        $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        $online_payment->approval = false;

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
    public function showAddons($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $noOfday = checkDiffDates(Carbon::now('Asia/Manila')->format('Y-m-d'), $reservation->check_out);
        return view('system.reservation.addons.index',  [
            'activeSb' => 'Reservation', 
            'r_list' => $reservation,
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
            'tour_menus' => TourMenu::all(),
            'addons_list' => Addons::all(),
            'user_days' => $noOfday,
        ]);
    }
    public function showExtend($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        return view('system.reservation.extend.index',  ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
}
