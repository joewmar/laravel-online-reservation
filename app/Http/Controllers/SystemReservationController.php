<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Addons;
// use App\Models\RoomList;
use App\Models\System;
use App\Models\Archive;
use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\Reservation;
use App\Models\RoomReserve;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use App\Models\OnlinePayment;
use Illuminate\Validation\Rule;
// use App\Notifications\EmailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use PDO;

class SystemReservationController extends Controller
{
    private $system_user, $admins;
    public function __construct(){
        $this->system_user = auth()->guard('system');
    }
    private function employeeLogNotif($action, $link = null){
        if(auth()->guard('system')->user()->role() !== "Admin"){
            $admins = System::all()->where('type', 0);
            $text = "New Employee Log! \n" .
            "Employee: " . auth()->guard('system')->user()->name() ."\n" .
            "Action: ".  $action ."\n" .
            "Date: " .  Carbon::now('Asia/Manila')->format('F j, Y g:ia');

            $keyboard = null;
            if(isset($link)){
                $keyboard = [
                    [
                        ['text' => 'View Log', 'url' => $link],
                    ],
                ];
            }
            foreach($admins as $admin){
                if(isset($admin->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, $keyboard, 'bot2');
            }
            Notification::send($admins, new SystemNotification('Employee Action from '.auth()->guard('system')->user()->name().': ' . Str::limit($action, 10, '...'), $text, route('system.notifications')));
        }
    }
    private function roomAssign(array $rooms, Reservation $reservation, $validated, bool $forceAssign = false, bool $changeAssign = false){
        $roomCustomer = [];
        $reservationPax = 0;

        if($forceAssign){
            foreach($rooms as $room_id => $newPax){
                $reservationPax += (int)$newPax;
                $room = Room::find($room_id);
                $roomCustomer[$room_id] = $newPax;
            }
        }
        else{
            if(Room::checkAllAvailable()){
                foreach($rooms as $room_id => $newPax){
                    $reservationPax += (int)$newPax;
                    $room = Room::find($room_id);
                    if($newPax > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$newPax.' pax) and Room Capacity ('.$room->room->max_occupancy.' capacity)')->withInput($validated);
                    if($newPax > $room->getVacantPax() && $reservationPax <= $room->getVacantPax()) return back()->with('error', 'Room No. ' . $room->room_no. ' are only '.$room->getVacantPax().' pax to reserved and your guest ('.$reservationPax.' pax)')->withInput($validated);
                    $roomCustomer[$room_id] = $newPax;
                }
            }
            else{
                $r_lists = Reservation::where(function ($query) use ($reservation) {
                    $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                          ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                          ->orWhere(function ($query) use ($reservation) {
                              $query->where('check_in', '<=', $reservation->check_in)
                                    ->where('check_out', '>=', $reservation->check_out);
                          });
                })->where('id', '!=', $reservation->id)->pluck('id');
    
                foreach($rooms as $room_id => $newPax){
                    $reservationPax += (int)$newPax;
                    $count_paxes = 0;
                    foreach($r_lists as $r_list){
                        $rooms = Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->where('id', $room_id)->get();
                        foreach($rooms as $room) $count_paxes += $room->customer[$r_list];
                    }
                    // dd($count_paxes);
                    $room = Room::find($room_id);
    
                    if($count_paxes > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot proceed due not Available based on guest ('.$newPax.' pax) on '.Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'))->withInput($validated);
            
                    if($count_paxes > $reservationPax && $reservationPax < $count_paxes)  return back()->with('error', 'Room No. ' . $room->room_no. ' cannot proceed due invalid guest between customer ('.$newPax.' pax) and vacant guest ('.$count_paxes.' pax) on '.Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'))->withInput($validated);
    
                    $roomCustomer[$room_id] = $newPax;
                }
            }
            if($reservationPax > $reservation->pax || $reservationPax < $reservation->pax) return back()->with('error', 'Guest you choose ('.$reservationPax.' pax) does not match on Customer Guest ('.$reservation->pax.' pax)')->withInput($validated);
        }
        if($changeAssign){
            foreach(Room::all() as $value){
                $value->removeCustomer($reservation->id);
            }
        }
        return $roomCustomer; 
    }
    public function index(Request $request){
        $r_list = Reservation::latest()->paginate(10);
        if($request['tab'] === 'pending'){
            $r_list = Reservation::where('status', 0)->latest()->paginate(10);
        }
        if($request['tab'] === 'confirmed'){
            $r_list = Reservation::where('status', 1)->latest()->paginate(10);
        }
        if($request['tab'] === 'cin'){
            $r_list = Reservation::where('status', 2)->latest()->paginate(10);
        }
        if($request['tab'] === 'cout'){
            $r_list = Reservation::where('status', 3)->latest()->paginate(10);
        }
        if($request['tab'] === 'reschedule'){
            $r_list = Reservation::where(function ($query) {
                $query->where('status', 7)->orWhere('message->reschedule->prev_status', 4);
            })->latest()->paginate(10);  
        }
        if($request['tab'] === 'cancel'){
            $r_list = Reservation::where('status', 5)->orWhere('status', 8)->latest()->paginate(10);
        }
        if($request['tab'] == 'previous'){
            $r_list = Reservation::with('previous')->latest()->paginate(10)?? [];
        }
        // if($request['tab'] === 'disaprove'){
        //     $r_list = Reservation::where('status', 6)->latest()->paginate(10);
        // }
        if(isset($request['search'])){
            $names = explode(' ', $request['search']);
            $firstName = $names[0];
            $lastName = isset($names[1]) ? $names[1] : '';
            $r_list = Reservation::join('users', 'reservations.user_id', '=', 'users.id')
            ->where('users.first_name', 'like', '%' . $firstName . '%')
            ->orWhere('users.last_name', 'like', '%' . $lastName . '%')
            ->paginate(10);
            if(!$r_list){
                $r_list = Reservation::join('user_offlines', 'reservations.offline_user_id', '=', 'user_offlines.id')
                ->where('user_offlines.first_name', 'like', '%' . $firstName . '%')
                ->orWhere('user_offlines.last_name', 'like', '%' . $lastName . '%')
                ->paginate(10);
            }
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
        if($reservation->roomid){
            foreach($reservation->roomid as $item) {
                $room = Room::find($item);
                if($room) $rooms[] = 'Room No ' . $room->room_no . ' ('.$room->room->name.')';
                else $rooms[] = 'Room Data Missing';
            }
        }
        $conflict = Reservation::all()->where('check_in', $reservation->check_in)->where('status', 0)->except($reservation->id);;
        $count = 0;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menu[$count]['title'] = $item['title'];
                $tour_menu[$count]['price'] = $item['price'];
                $tour_menu[$count]['amount'] = $item['amount'];
            }

            // Rate
            if (strpos($key, 'rid') !== false) {
                $rate['name'] = $item['title'];;
                $rate['price'] = $item['price'];
                $rate['amount'] = $item['amount'];
                if(isset($item['orig_amount'])){
                    $rate['orig_amount'] = $item['orig_amount'];
                }
            }
            if (strpos($key, 'OA') !== false && is_array($item)) {
                foreach($item as $key => $dataAddons){
                    $other_addons[$count+$key]['title'] = $dataAddons['title'];
                    $other_addons[$count+$key]['pcs'] = $dataAddons['pcs'];
                    $other_addons[$count+$key]['price'] = $dataAddons['price'];
                    $other_addons[$count+$key]['amount'] = $dataAddons['amount'];
                }
            }
            if (strpos($key, 'TA') !== false && is_array($item)) {
                foreach($item as $key => $tourAddons){
                    $tour_addons[$count]['title'] = $tourAddons['title'];
                    $tour_addons[$count]['price'] = $tourAddons['price'];
                    $tour_addons[$count]['amount'] = $tourAddons['amount'];
                }
                
            }

            $count++;
        }

        unset($count);
        return view('system.reservation.show',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'conflict' => $conflict, 'rooms' => implode(',', $rooms), 'rate' => $rate, 'other_addons' => $other_addons, 'tour_addons' => $tour_addons]);
    }
    public function showCancel($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        return view('system.reservation.show-cancel',  ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function showReschedule($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $availed = Reservation::all()->where('check_in', $reservation->check_in)->where('check_out', $reservation->check_out)->except($reservation->id);
        $rooms = Room::all();
        return view('system.reservation.show-reschedule',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'availed' => $availed, 'rooms' => $rooms]);
    }
    public function updateCancel(Request $request, $id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if(!$reservation->status >= 5) abort(404);
        $validator = Validator::make($request->all('passcode'), [
            'passcode' => ['required', 'digits:4', 'numeric'],
        ]);
        if($validator->fails()) return back ()->with('error', $validator->errors()->all());
        $validator = $validator->validate();
        if(!Hash::check($validator['passcode'], $this->system_user->user()->passcode)) return back ()->with('error', 'Invalid Passcode');
        // Remove reserved rooms
        if(isset($reservation->roomid)){
            $rooms = Room::all();
            foreach($rooms as $room) $room->removeCustomer($reservation->id) ;
        }
        $reservation->status = 5;
        $updated = $reservation->save();
        $message = $reservation->message;

        if(isset($message['cancel'])) unset($message['cancel']);

        if($updated){
            $this->employeeLogNotif('Approve Cancel Request Reservation of ' . $reservation->userReservation->name());
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Cancellation',
                'body' => 'Your Reservation Cancel Request are now approved. '
            ];
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Cancel Request of '.$reservation->userReservation->name().'was approved');
        }
    }
    public function updateDisaproveCancel(Request $request, $id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $validator = Validator::make($request->all('reason'), [
            'reason' => ['required'],
        ]);
        if($validator->fails()) return back ()->with('error', $validator->errors()->all())->withInput($validator->getData());
        $validator = $validator->validate();
        $message = $reservation->message;
        $reservation->status = $message['cancel']['prev_status'];
        $reservation->save();
        if(isset($message['cancel'])) unset($message['cancel']);
        $updated = $reservation->update(['message' => $message]);
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Cancel',
                'body' => 'Sorry, Your Cancel Request are now disapproved due to ' . $validator['reason'] . '. If you want concern. Please contact the owner'
            ];
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Reschedule Request of '.$reservation->userReservation->name().'was successful disapproved');
        }
    
    }
    public function updateReschedule(Request $request, $id){
        $system_user = $this->system_user->user();
        $validator = Validator::make($request->all('passcode'), [
            'passcode' => ['required', 'digits:4', 'numeric'],
        ]);

        if($validator->fails()) return back ()->with('error', $validator->errors()->all());
        $validator = $validator->validate();
        if(!Hash::check($validator['passcode'], $system_user->passcode)) return back ()->with('error', 'Invalid Passcode');
        $admins = System::all()->where('type', 0);
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if(!$reservation->status == 7) abort(404);
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($request['room_pax']);
        else $validator['room_pax'] = $request['room_pax'];

        $roomCustomer = $this->roomAssign($validator['room_pax'], $reservation, $validator, false, true);

        if(!is_array($roomCustomer)){
            return $roomCustomer;
        }

        foreach($roomCustomer as $key => $pax){
            $room = Room::find($key);
            $room->addCustomer($reservation->id, $pax);
        }
        $message = $reservation->message;

        $updated = $reservation->update([
            'check_in' => $message['reschedule']['check_in'],
            'check_out' => $message['reschedule']['check_out'],
            'roomid' => array_keys($roomCustomer),
            'status' => $message['reschedule']['prev_status'],
        ]);

        if($updated){

            if(isset($message['reschedule']['message'])) unset($message['reschedule']['message']);
            $message['reschedule']['prev_status'] = 4; // For User Resevation List
            $reservation->update([
                'message' => $message,
                'roomid' => array_keys($roomCustomer),
            ]);
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Reschedule',
                'body' => 'Your Request are now approved. '
            ];
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Reschedule Request of '.$reservation->userReservation->name().' was approved');
        }
    }
    public function updateDisaproveReschedule(Request $request, $id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $validator = Validator::make($request->all('reason'), [
            'reason' => ['required'],
        ]);
        if($validator->fails()) return back ()->with('error', $validator->errors()->all())->withInput($validator->getData());
        $validator = $validator->validate();
        $message = $reservation->message;
        $reservation->status = $message['reschedule']['prev_status'];
        $reservation->save();
        $message['reschedule']['prev_status'] = 7;
        if(isset($message['reschedule']['message'])) unset($message['reschedule']['message']);
        $updated = $reservation->update(['message' => $message]);
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Cancellation',
                'body' => 'Sorry, Your Reservation Cancel Request are now disapproved due to ' . $validator['reason'] . '. If you want concern. Please contact the owner'
            ];
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Cancel Request of '.$reservation->userReservation->name().'was successful disapproved');
        }
       
        
    }
    public function showRooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status >= 1) abort(404);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $roomReserved = [];
        $r_lists = Reservation::where(function ($query) use ($reservation) {
            $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                  ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                  ->orWhere(function ($query) use ($reservation) {
                      $query->where('check_in', '<=', $reservation->check_in)
                            ->where('check_out', '>=', $reservation->check_out);
                  });
        })->where('id', '!=', $reservation->id)->pluck('id');
        
        foreach($rooms as $key => $room){
            $count_paxes = 0;
            foreach($r_lists as $r_list){
                $rs= Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->where('id', $room->id)->get();
                foreach($rs as $room) $count_paxes += $room->customer[$r_list];
            }
            if($count_paxes >= $room->room->max_occupancy) {
                $roomReserved[] = $room->id;
            }

        }
        return view('system.reservation.show-room',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'reserved' => $roomReserved]);
    }
    public function updateReservation(Request $request){
        $roomCustomer = [];
        if($request->has('room_rate')) $request['room_rate'] = decrypt($request['room_rate']);
        $validated = $request->validate([
            'room_rate' => ['required', Rule::when($request->has('room_rate'), ['numeric'])],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        
        $system_user = $this->system_user->user();
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status >= 1) abort(404);
        if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($request['room_pax']);
        else $validated['room_pax'] = $request['room_pax'];
        $rate = RoomRate::find($validated['room_rate']);

        if(isset($request['force'])) $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated, true);
        else $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated);

        if(!is_array($roomCustomer)){
            return $roomCustomer;
        }

        $transaction = $reservation->transaction;
        $transaction['rid'.$rate->id]['title'] = $rate->name;
        $transaction['rid'.$rate->id]['price'] = $rate->price;
        $transaction['rid'.$rate->id]['amount'] = $rate->price * $reservation->getNoDays();
        // Update Reservation
        $reserved = $reservation->update([
            'roomid' => array_keys($roomCustomer),
            'roomrateid' => $rate->id,
            'transaction' => $transaction,
            'status' => 1,
        ]);
        $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        $reservation->save();

        // Update Room Availability
        if($reserved){
            foreach($roomCustomer as $key => $pax){
                $room = Room::find($key);
                $room->addCustomer($reservation->id, $pax);
            }
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
            foreach($reservation->roomid as $item) {
                $room = Room::find($item);
                if($room) $roomDetails[] = 'Room No ' . $room->room_no . ' ('.$room->room->name.')';
                else $roomDetails[] = 'Room Data Missing';
            }

            $text = null;
            $url = null;
            if($reservation->payment_method == "Gcash"){
                $url = route('reservation.gcash', encrypt($reservation->id));
            }
            if($reservation->payment_method == "PayPal"){
                $url = route('reservation.paypal', encrypt($reservation->id));
            }
            if($reservation->payment_method == "Bank Transfer"){
                $url = route('reservation.paypal', encrypt($reservation->id));
            }

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
                'total' => $reservation->getTotal(),
                'payment_link' => $url,
                'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('F j Y \a\t g:iA'),
            ];
            $this->employeeLogNotif('Chose to Approve of ' . $reservation->userReservation->name() . ' Reservation with Room Assign '.implode(',', $roomDetails), route('system.reservation.show', encrypt($reservation->id)));
            unset($roomDetails);
            // Notification::send($reservation->userReservation, new EmailNotification($project));
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationConfirmation($details['title'], $details, 'reservation.confirm-mail'));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Confirmed');
            unset($details, $text, $url);
        }
    }
    public function updateCheckin(Request $request){
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0);
        $reservation = Reservation::findOrFail(decrypt($request->id));
        $transaction = $reservation->transaction;

        if($request->has('senior_count')){
            $validate = Validator::make(['senior_count' => $request['senior_count']], [
                'senior_count' => ['required', 'numeric', 'min:1', 'max:'.$reservation->pax]
            ], [
                'senior_count.required' => 'Required Input (Senior Guest Discount)',
                'senior_count.max' => 'Input based on Room Guest of Customer',
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());

            $discounted = $validate->validate();
            $transaction['payment']['discountPerson'] = $discounted['senior_count'];
            foreach($transaction ?? [] as $key => $item){
                if (strpos($key, 'rid') !== false) {
                    $rateID = (int)str_replace('rid','', $key);
                    $discounted = (20 / 100) * $discounted['senior_count'];
                    $discounted = (double)($transaction['rid'.$rateID]['amount'] * $discounted);
                    $discounted = (double)($transaction['rid'.$rateID]['amount'] - $discounted);
                    $transaction['rid'.$rateID]['orig_amount'] = $transaction['rid'.$rateID]['amount'];
                    $transaction['rid'.$rateID]['amount'] = $discounted;
                    break;
                }
            }

        }
        $downpayment = $transaction['payment']['downpayment'] ?? 0;
        $balance = abs($reservation->getTotal() - $downpayment);
        if($request['payments'] == 'partial'){
            $validate = Validator::make($request->all(['payments', 'another_payment']), [
                'payments' => ['required'],
                'another_payment' =>['required', 'numeric', 'max:'.(int)$balance],
            ], [
                'required' => 'Required to choose',
                'max' => 'Fill the amount up to ₱' . number_format($balance, 2),
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            
            $transaction['payment']['cinpay'] = (double)$validate['another_payment'];
            $message = 'Partial Payment (₱ '.number_format($validate ['another_payment'], 2).')';

        }
        else if($request['payments'] == 'fullpayment'){
            $validate = Validator::make($request->all(['payments', 'another_payment']), [
                'payments' => ['required'],
            ], [
                'required' => 'Required to choose',
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
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
        unset($transaction, $discounted);
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
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            unset($text, $details);
            $this->employeeLogNotif('Checked in' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked in');
            
        }
    }
    public function updateCheckout(Request $request){
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0);
        $reservation = Reservation::findOrFail(decrypt($request->id));
        $validated = $request->validate([
            'fullpay' => ['accepted'],
        ],[
            'accepted' => 'Before proceeding, full payment must be made first.'
        ]);
        $transaction = $reservation->transaction;
        $transaction['payment']['coutpay'] = $reservation->balance();
        if($validated) {
            $reservation->update(['status' => 3, 'transaction' => $transaction]);
            $reservation->checkedOut();
        }
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Check-out',
            'body' => 'You now checked out at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A'),
            'receipt_link' => route('reservation.receipt', encrypt($reservation->id)),
            'feedback_link' => route('reservation.feedback', encrypt($reservation->id)),
        ];   

        Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.checkout-mail', $details['title']));
        $this->employeeLogNotif('Check-out of ' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
        unset($text, $details, $transaction);
        return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked out');
        
    }
    public function disaprove($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $admins = System::all()->where('type', 0);

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
        $admins = System::all()->where('type', 0);
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

            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Disaprove',
                'body' => 'Your Reservation are disapprove due of ' . $messages['disaprove']. 'Sorry for waiting. Please try again to make reservation in another dates',
            ];
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            unset($details, $text);
            $this->employeeLogNotif('Chose to Disaprove of ' . $reservation->userReservation->name() . ' Reservation', route('system.reservation.show', encrypt($reservation->id)));
            return redirect()->route('system.reservation.home')->with('success', 'Disaprove of ' . $reservation->userReservation->name() . ' was Successful');
        }
        else{
            return back()->with('error', 'Something Wrong, Try Again')->withInput($validated);
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
        $downpayment = $reservation->transaction;
        if(isset($downpayment['payment']['downpayment'])) $downpayment['payment']['downpayment'] += (double)$validated['amount'];
        else $downpayment['payment']['downpayment'] = (double)$validated['amount'];
        $reservation->update(['transaction' => $downpayment]);
        $online_payment->approval = true;
        $online_payment->save();
        if($downpayment['payment']['downpayment'] >= 1000){
            $reservation->payment_cutoff = null;
            $reservation->save();
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Your online payment was approved',
                'body' => 'Downpayment: ' .  $downpayment['payment']['downpayment'],
            ];
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
        }
        else{
            $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s'); 
            $reservation->attempt += 1; 
            $reservation->save(); 
            if($reservation->payment_method == "Gcash") $url = route('reservation.gcash', encrypt($reservation->id));
            
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Your online payment was approved, but the amount you paid was insufficient. There is a chance for you to make another payment',
                'body' => 'Downpayment: ' .  $downpayment['payment']['downpayment'] .' but minimuim payment is 1000 philippine pesos' ,            
                'link' => $url,
                'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y'),
            ];
            Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.online-payment-mail', $details['title']));
        }
        $this->employeeLogNotif('Approve Downpayment of ' . $reservation->userReservation->name() . ' with Paid ' . $downpayment['payment']['downpayment'] . ' pesos', route('system.reservation.show.online.payment', encrypt($reservation->id)));
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
        if($reservation->payment_method == "PayPal"){
            $url = route('reservation.paypal', encrypt($reservation->id));
        }
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Your online payment was approved',
            'body' => 'Reason:  ' .  $validated['reason'] . '. I will give you a chance to make payment',
            'link' => $url,
            'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y'),
        ];
        
        Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.online-payment-mail', $details['title']));
        $this->employeeLogNotif('Choose Disaprove on Downpayment of ' . $reservation->userReservation->name(), route('system.reservation.show.online.payment', encrypt($reservation->id)));
        return redirect()->route('system.reservation.show.online.payment', encrypt($reservation->id))->with('success', 'Disaprove payment successful');

    }
    public function storeForcePayment(Request $request, $id){
        $system_user = $this->system_user->user();
        $reservation = Reservation::findOrFail(decrypt($id));
        $admins = System::all()->where('type', 0);
        $validated = Validator::make($request->all('amount'), [
            'amount' => ['required', 'numeric', 'min:1000', 'max:'.$reservation->getTotal()],
        ], [
            'required' => 'Required to fill up', 'numeric' => 'Number only',
            'min' => 'The amount must be ₱ 1,000 above',
            'max' => 'The amount must exact ₱ '.number_format($reservation->getTotal(), 2).' below',
        ]);
        if($validated->fails()){
            return back()->with('error', $validated->errors()->all());
        }
        $validated = $validated->validate();
        $downpayment = $reservation->transaction;
        if(isset($downpayment['payment']['downpayment'] )) $downpayment['payment']['downpayment'] += (double)$validated['amount'];
        else $downpayment['payment']['downpayment'] = (double)$validated['amount'];
        $updated = $reservation->update(['transaction' => $downpayment]);
        $reservation->payment_cutoff = null;
        $reservation->save();

        if($updated){
            $this->employeeLogNotif('Fill the Payment of Force Payment for ' . $reservation->userReservation->name() . ' in ' . $validated['amount'] . ' pesos', route('system.reservation.show', $id));
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
            'user_days' => $reservation->getNoDaysInToday(),
        ]);
    }
    public function updateAddons(Request $request, $id){
        // dd($request->all());
        $reservation = Reservation::findOrFail(decrypt($id));
        $transaction = $reservation->transaction;
        $type = "Other Addons";
        if($request->has('tab') && $request['tab'] == 'TA'){
            $validate = Validator::make($request->all(), [
                'tour_menu' => ['required'],
                'new_pax' => ['required', 'numeric', 'min:1' , 'max:'.$reservation->pax],
                'passcode' => ['required', 'numeric', 'digits:4'],
            ], [
                'tour_menu.required' => 'Your Cart is empty',
                'new_pax.required' => 'Required to fill up number of guest ',
                'new_pax.numeric' => 'Number of guest should be number only',
                'new_pax.min' => 'Number of guest should be 1 and above',
                'new_pax.max' => 'INumber of guest should be '.$reservation->pax.' guest below',
            ]);     
            if($validate->fails()){
                return back()->with('error', $validate->errors()->all())->withInput();
            }
            $validated = $validate->validate();
            if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid passcode')->withInput($validated);
            foreach($validated['tour_menu'] as $item){
                $transaction['TA'.$item][] = [
                    'title' => TourMenu::find($item)->tourMenu->title . ' ' . TourMenu::find($item)->type . '('.TourMenu::find($item)->pax.' pax)',
                    'price' => TourMenu::find($item)->price ?? 0,
                    'amount' => ((double)TourMenu::find($item)->price ?? 0) * (int)$validated['new_pax'],
                ];
            }
            $type = "Tour Addons";
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

        $this->employeeLogNotif('Add Addons Package of ' . $reservation->userReservation->name() . '(' . $type . ')');
        if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Other Add-ons for '.$reservation->userReservation->name().' was successful');

    }
    public function showExtend($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        return view('system.reservation.extend.index',  ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function updateExtend(Request $request, $id){
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0);
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
        $this->employeeLogNotif('Add days of Extend Day for ' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
        if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->useReservation()->name() . ' was extend in ' . ($validated['no_days'] > 1 ? $validated['no_days'] . ' days' : $validated['no_days'] . ' day'));
    }
}
