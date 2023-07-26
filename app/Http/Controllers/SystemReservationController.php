<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmation;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use App\Models\Archive;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Laravel\Facades\Telegram;

class SystemReservationController extends Controller
{
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
        return redirect()->route('system.reservation.home', Arr::query(['search' => $request['search']]));
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
    public function receipt($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $tour_menu = [];
        $rates = RoomRate::findOrFail($reservation->room_rate_id);
        $rooms = [];
        foreach(explode(',',  $reservation->room_id) as $key => $item){
            $rooms[$key] = Room::findOrFail($reservation->room_rate_id)->toArray();
        }
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
        return view('reservation.receipt',  ['r_list' => $reservation, 'menu' => $tour_menu, 'conflict' => $conflict, 'rate' => $rates, 'rooms' => $rooms]);
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
                        $total += (double)explode('-', $item)[1];
                    }

                }
                $amount = implode(',', $amount);

                // UPdate Reservatoin
                $reserved = $reservation->update([
                    'room_id' => implode(',', $roomReservation),
                    'room_rate_id' => $rate->id,
                    'amount' => $amount,
                    'total' => $total,
                    'status' => 1,
                ]);
                $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
                $reservation->save();
                if($reserved){
                    foreach($roomReservation as $item){
                        $room = Room::find($item);
                        $newCus = $reservation->user_id . '_' . $room->id;
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
                            $arrPreCus[$key]['pax'] = explode('_', $item)[1] ?? '';
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
                    $text = 
                    "Employee Action: Approved Reservation !\n" .
                    "Name: ". $reservation->userReservation->first_name . " " . $reservation->userReservation->last_name ."\n" . 
                    "Age: " . $reservation->age ."\n" .  
                    "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
                    "Country: " . $reservation->userReservation->country ."\n" . 
                    "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y') ."\n" . 
                    "Check-out: " . Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y') ."\n" . 
                    "Type: " . $reservation->accommodation_type ."\n" . 
                    "Who Approve: " . auth('system')->user()->first_name . ' ' .auth('system')->user()->last_name ;
                    if(!auth('system')->user()->type === 0){
                        Telegram::bot('bot2')->sendMessage([
                            'chat_id' => auth()->user()->telegram_chatID,
                            'parse_mode' => 'HTML',
                            'text' => $text,
                        ]);
                    }
                    $text = null;
                    $url = null;
                    if($reservation->payment_method == "Gcash"){
                        $url = route('reservation.gcash', encrypt($reservation->id));
                    }
                    // if($reservation->payment_method == "PayPal"){
                    //     $url = route('reservation.paypal');
                    // }
                    $details = [
                        'name' => $reservation->userReservation->first_name . ' ' . $reservation->userReservation->last_name,
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
                        "room_no" =>  'Room No ' . $room->room_no . ' (' . $room->room->name .")",
                        "room_type" => $rate->name,
                        'room_rate' => $rate->price,
                        'total' => $reservation->total,
                        'payment_link' => $url,
                        'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y, g:i A'),
                    ];
                    Mail::to('recelestino90@gmail.com')->send(new ReservationConfirmation($details['title'], $details, 'reservation.confirm-mail'));                    return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->first_name .' '. $reservation->userReservation->last_name . ' was Confirmed');
                    // Mail::to($reservation->userReservation->email)->send(new ReservationMail($details, 'reservation.mail', $details['title']));                    return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->first_name .' '. $reservation->userReservation->last_name . ' was Confirmed');
                    $details = null;
                }
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
            "user_id" => $reservation->user_id,
            "room_id" => $reservation->room_id,
            "room_rate_id" => $reservation->room_rate_id,
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
            "Who Disaprove?: " . auth('system')->user()->first_name . ' ' .auth('system')->user()->last_name ;
            "Reason to Disaprove: " . $acrhived->message ;
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
}
