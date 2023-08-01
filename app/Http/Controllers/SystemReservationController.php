<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\System;
use App\Models\Archive;
use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Notifications\EmailNotification;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationMail;
use App\Mail\ReservationConfirmation;
use Illuminate\Support\Facades\Validator;
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
        if($reservation->room_id){
            foreach(json_decode($reservation->room_id) ?? $reservation->room_id as $item ){
                $rooms[] = 'Room No.' . Room::find($item)->room_no . ' ('.Room::find($item)->room->name.')';
            }
        }

        $conflict = Reservation::all()->where('check_in', $reservation->check_in)->where('status', 0)->except($reservation->id);;
        // dd($reservation->amount);

        if($reservation->accommodation_type != 'Room Only'){
            foreach($reservation->menu as $key => $item){
                $tour_menu[$key]['title'] = TourMenu::find($item)->tourMenu->title;
                $tour_menu[$key]['type'] = TourMenu::find($item)->type;
                $tour_menu[$key]['pax'] = TourMenu::find($item)->pax;
                $tour_menu[$key]['price'] = $reservation->amount['tm'.$item];
            }
        }
        return view('system.reservation.show',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'conflict' => $conflict, 'rooms' => implode(',', $rooms)]);
    }
    public function receipt($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $tour_menu = [];
        $addtional_menu = [];
        $rates = RoomRate::findOrFail($reservation->room_rate_id);
        $rooms = [];
        foreach(json_decode($reservation->room_id) as $item){
            $rooms[$item]['no'] = Room::findOrFail($item)->room_no;
            $rooms[$item]['name'] = Room::findOrFail($item)->room->name;
        }

        if($reservation->accommodation_type != 'Room Only'){
            
            foreach($reservation->menu as $key => $item){
                $tour_menu[$key]['title'] = TourMenu::find($item)->tourMenu->title;
                $tour_menu[$key]['type'] = TourMenu::find($item)->type;
                $tour_menu[$key]['pax'] = TourMenu::find($item)->pax;
                $tour_menu[$key]['price'] = $reservation->amount['tm'.$item];
            }
            // if($reservation->additional_menu != null){
            //     foreach(explode(',' , $reservation->additional_menu) as $key => $item){
            //         $addtional_menu[$key]['title'] = TourMenu::find($item)->tourMenu->title;
            //         $addtional_menu[$key]['type'] = TourMenu::find($item)->type;
            //         $addtional_menu[$key]['pax'] = TourMenu::find($item)->pax;
            //         if(explode('-' , explode(',' , $reservation->amount)[$key])[0] == 'tm'.$item)
            //             $addtional_menu[$key]['price'] = explode('-' , explode(',' , $reservation->amount)[$key])[1];
                    
            //     }
            // }
        }
        return view('reservation.receipt',  ['r_list' => $reservation, 'menu' => $tour_menu, 'add_menu' => $addtional_menu, 'rate' => $rates, 'rooms' => $rooms]);
    }
    public function showRooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status <= 1) abort(404);
        $rooms = Room::all();
        $rates = RoomRate::all();
        return view('system.reservation.show-room',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rates]);
    }
    public function updateReservation(Request $request){
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0)->where('type', 1);
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status <= 1) abort(404);
        $validator = Validator::make($request->all(), [
            'passcode' =>  ['required', 'numeric', 'digits:4'],
            'room_rate' =>  ['required', 'numeric'],
            'rooms.*' => ['required', 'numeric'],
        ]);
        $error = [];
        $roomReservation = [];
        if(!Hash::check($request['passcode'], $system_user->passcode)) $error[] = 'Invalid Passcode';
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
                    $roomCustomer[$item] = [$reservation->id => $countOccupancy];
                    $roomReservation[] = $room->id;
                } 

            }
            unset($roomNo, $totalPax);

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
                    'room_id' => (array) $roomReservation,
                    'room_rate_id' => $rate->id,
                    'amount' => $amount,
                    'total' => $total,
                    'status' => 1,
                ]);
                $reservation->payment_cutoff = Carbon::now()->addDays(2)->format('Y-m-d H:i:s');
                $reservation->save();

                // Update Room Availability
                if($reserved){
                    foreach($roomReservation as $item){
                        $room = Room::find($item);
                        if( $room->customer == null){
                            $room->update([
                                'customer' => [$reservation->id => $room->id],
                            ]);
                        }
                        else{
                            $newCus = [];
                            foreach($room->customer ?? [] as $key => $item){
                                $newCus[$key] = $item;
                            }
                            $newCus[$reservation->id] = $room->id;
                            $room->update([
                                'customer' => $newCus,
                            ]);
                            unset($newCus);
                        }
                        $countOccupancy = 0;
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
                    unset($roomReservation);
                    $tour_menu = [];
                    // Get Tour Menu for Mail
                    if($reservation->accommodation_type != 'Room Only'){
                        foreach($reservation->menu as $key => $item){
                            $tour_menu[$key]['title'] = TourMenu::find($item)->tourMenu->title;
                            $tour_menu[$key]['type'] = TourMenu::find($item)->type;
                            $tour_menu[$key]['pax'] = TourMenu::find($item)->pax;
                            $tour_menu[$key]['price'] = $reservation->amount['tm'.$item];
                        }
                    }
                    $roomDetails = [];
                    foreach($reservation->room_id as $item){
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
                    "Who Approve: " . $system_user->name;
        
                    foreach($admins as $admin){
                        if($admin->telegram_chatID != null) telegramSendMessage($admin->telegram_chatID, $text, null, 'bot2');
                    }
                    
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
                        'body' => 'Your Reservation has Confirmed, Be on time',
                        "age" => $reservation->age,  
                        "nationality" =>  $reservation->userReservation->nationality , 
                        "country" =>  $reservation->userReservation->country, 
                        "check_in" =>  Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'), 
                        "check_out" =>  Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y'), 
                        "accommodation_type" =>  $reservation->accommodation_type,
                        "payment_method" =>  $reservation->payment_method,
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
                    // $project = [
                    //     'greeting' => 'Hi '.$reservation->userReservation->first_name . ' ' . $reservation->userReservation->last_name.',',
                    //     'body' => 'This is the project assigned to you.',
                    //     'thanks' => 'Thank you this is from codeanddeploy.com',
                    //     'actionText' => 'View Details',
                    //     'actionURL' => url('/'),
                    //     'id' => 57
                    // ];

                    // Notification::send($reservation->userReservation, new EmailNotification($project));
                    Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->send(new ReservationConfirmation($details['title'], $details, 'reservation.confirm-mail'));                  
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
            'another_payment' => Rule::when(($request['$request'] == 'partial'), ['required', 'numeric']),
        ]);
        if($validated['payments'] == 'partial'){
            $validated['downpayment'] = abs((double)$reservation->downpayment -(double)$validated['payments']);
            $validated['status'] = 2;
        }
        else{
            $validated['downpayment'] = $reservation->total;
            $validated['status'] = 2;
        }
        $updated = $reservation->update([
            'downpayment' => (double)$validated['downpayment'],
            'status' => (int)$validated['status'],
        ]);
        $text = 
        "Employee Action: Check-in !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Who Approve: " . $system_user->name ;
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Check-in',
            'body' => 'You now checked in at ' . Carbon::now()->format('F j, Y, g:i A'),
        ];
        if($updated){
            foreach($admins as $admin){
                if($admin->telegram_chatID != null) telegramSendMessage($admin->telegram_chatID, $text, null, 'bot2');
            }
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->send(new ReservationMail($details, 'reservation.mail', $details['title']));                  
            unset($text, $details);
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked in');
        }
       

    }
    public function disaprove($id){
        $reservation = Reservation::findOrFail(decrypt($id));
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
        return view('system.reservation.disaprove',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu]);
    }
    public function disaproveStore(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
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
        if(!Hash::check($validated['passcode'], auth('system')->user()->passcode))  return back()->with('error', 'Invalid Passcode, Try Again')->withInput($validated);

        $arrAcrhive = [
            "name" => $reservation->userReservation->name(),
            "age" => $reservation->age,
            "country" => $reservation->userReservation->country,
            "nationality" => $reservation->userReservation->nationality,
            "pax" => $reservation->pax,
            "accommodation_type" => $reservation->accommodation_type,
            "payment_method" => $reservation->payment_method,
            "age" => $reservation->age,
            "menu" => $reservation->menu,
            "check_in" => $reservation->check_in,
            "check_out" => $reservation->check_out,
            "status" => 1,
            "additional_menu" => $reservation->additional_menu,
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
            "Who Disaprove?: " . auth('system')->user()->name . ' (' . auth('system')->user()->role . ')' ;
            "Reason to Disaprove: " . $acrhived->message  ;
            // Send Notification to 
            // $keyboard = [
            //     [
            //         ['text' => 'View Details', 'url' => route('system.reservation.show', encrypt($reserve_info->id))],
            //     ],
            // ];
            if(!auth('system')->user()->type === 0){
                Telegram::bot('bot2')->sendMessage([
                    'chat_id' => auth()->user()->telegram_chatID,
                    'parse_mode' => 'HTML',
                    'text' => $text,
                    // 'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
                ]);
            }
            $text = null;
            $details = [
                'name' => $acrhived->userArchive->first_name . ' ' . $acrhived->userArchive->last_name,
                'title' => 'Reservation Disaprove',
                'body' => 'Your Reservation are disapprove due of ' . $acrhived->message. 'Sorry for waiting. Please try again to make reservation in another dates',
            ];
            Mail::to($acrhived->userArchive->email)->send(new ReservationMail($details, 'reservation.mail', $details['title']));
            $details = null;
            return redirect()->route('system.reservation.home')->with('success', 'Disaprove of ' . $acrhived->userArchive->first_name . ' ' . $acrhived->userArchive->last_name . ' was Successful');
        }
        else{
            return back()->with('error', 'Something Wrong on database, Try Again')->withInput($validated);
        }
    }
    public function showOnlinePayment($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        // $onlinePayment = $reservation->payment;
        return view('system.reservation.onlinepayment.index', ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function storeOnlinePayment($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        // $onlinePayment = $reservation->payment;
        return view('system.reservation.onlinepayment.index', ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
}
