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
use App\Models\WebContent;
use App\Models\Reservation;
use App\Models\RoomReserve;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use App\Models\OnlinePayment;
// use App\Notifications\EmailNotification;
use Illuminate\Validation\Rule;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Models\AuditTrail;
use App\Notifications\SystemNotification;
use App\Notifications\UserNotif;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class SystemReservationController extends Controller
{
    private $system_user; // Walang employeeLogNotif sa mga functions
    public function __construct(){
        $this->system_user = auth('system');
        $this->middleware(function ($request, $next){
            if(!($this->system_user->user()->type === 0 || $this->system_user->user()->type === 1 )) abort(404);
            return $next($request);

        })->except(['updateCheckin', 'updateCheckout', 'show', 'index', 'search', 'event']);
    }   
    private function countTour(Reservation $rlist){
        $trans = $rlist->transaction;
        $c = 0;
        foreach($trans as $key => $item){
            if (strpos($key, 'tm') !== false ) {
                $c++;
            }
            if (strpos($key, 'TA') !== false ) {
                $c++;
            }
        }
        return $c;
    }
    private function employeeLogNotif($action, $link = null){
        $user = auth()->guard('system')->user();
        if($user->role() !== "Admin"){
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
                if(isset($admin->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, $keyboard, 'bot2'));
            }
            Notification::send($admins, new SystemNotification('Employee Action from '.auth()->guard('system')->user()->name().': ' . Str::limit($action, 10, '...'), $text, route('system.notifications')));
        }
        AuditTrail::create([
            'system_id' => $user->id,
            'role' => $user->type ?? '',
            'action' => $action,
            'module' => 'Reservation',
        ]);
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
                })->whereBetween('status', [1, 2])->where('id', '!=', $reservation->id)->pluck('id');
    
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

        return $roomCustomer; 
    }
    public function index(Request $request){
        $r_list = Reservation::latest()->paginate(10);
        if($this->system_user->user()->type == 2) $r_list = Reservation::whereBetween('status', [2,3])->latest()->paginate(10);
        if(!$this->system_user->user()->type == 2) {
            if($request['tab'] === 'pending'){
                $r_list = Reservation::where('status', 0)->latest()->paginate(10);
            }
            if($request['tab'] === 'confirmed'){
                $r_list = Reservation::where('status', 1)->latest()->paginate(10);
            }
            if($request['tab'] === 'reschedule'){
                $r_list = Reservation::where('status', 7)->orWhere('message->reschedule->prev_status', 4)->latest()->paginate(10);  
            }
            if($request['tab'] === 'cancel'){
                $r_list = Reservation::where('status', 5)->orWhere('status', 8)->latest()->paginate(10);
            }
        } 

        if($request['tab'] === 'cin'){
            $r_list = Reservation::where('status', 2)->latest()->paginate(10);
        }
        if($request['tab'] === 'cout'){
            $r_list = Reservation::where('status', 3)->latest()->paginate(10);
        }
        if($request['tab'] == 'walkin'){
            $r_list = Reservation::whereNotNull('offline_user_id')->where('payment_method', 'Walk-in')->latest()->paginate(10)?? [];
        }
        if($request['tab'] == 'othbook'){
            $r_list = Reservation::whereNotNull('offline_user_id')->where('payment_method', 'Other Online Booking')->latest()->paginate(10)?? [];
        }
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

        $search = $request->input('query');
        $names = [];
        if($search){
            $results = Reservation::whereHas('userReservation', function ($query) use ($search) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            })->get();
            foreach($results as $list){
                $names[] = [
                    'title' => $list->userReservation->name(),
                    'link' => route('system.reservation.show', encrypt($list->id)),
                ];
            }
        }
        return response()->json($names);
            
    }
    public function event(){
        if(!Auth::guard('system')->check()) abort(404);
        $reservations = Reservation::all();
        $arrEvent = [];
        foreach($reservations as $reservation){
            $color = '';
            if($reservation->status() == 'Pending') $color = '#2a5adf';
            if($reservation->status() == 'Confirmed') $color = '#22c55e';
            if($reservation->status() == 'Check-in') $color = '#eab308';
            if($reservation->status() == 'Check-out') $color = '#64748b';
            if($reservation->status() == 'Previous') $color = '#64748b';
            if($reservation->status() == 'Cancel') $color = '#f43f5e';
            if($reservation->status() == 'Pending Reschedule') $color = '#eab308';
            if($reservation->status() == 'Pending Cancel') $color = '#fb7185';
            /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => done, 4 => canceled, 5 => disaprove, 6 => reshedule*/
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
        $other_addons = [];
        $rate = [];
        if($reservation->roomid){
            foreach($reservation->roomid as $item) {
                $room = Room::find($item);
                if($room) $rooms[] = 'Room No ' . $room->room_no . ' ('.$room->room->name.')';
                else $rooms[] = 'Room Data Missing';
            }
        }
        $conflict =  Reservation::where(function ($query) use ($reservation) {
            $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                  ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                  ->orWhere(function ($query) use ($reservation) {
                      $query->where('check_in', '<=', $reservation->check_in)
                            ->where('check_out', '>=', $reservation->check_out);
                  });
        })->where('id', '!=', $reservation->id)->where('status', 0)->get();
        $count = 0;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menu[$count]['title'] = $item['title'] . ' '.$item['type'] . ' ('.$item['pax'].' pax)';
                $tour_menu[$count]['price'] = $item['price'];
                $tour_menu[$count]['tpx'] = $item['tpx'];
                $tour_menu[$count]['used'] = $item['used'];
                $tour_menu[$count]['amount'] = $item['amount'];
                $tour_menu[$count]['key'] = $key;
                
                // 'id' => $tour_menu->id,
                // 'title' => $tour_menu->tourMenu->title,
                // 'type' => $tour_menu->type,
                // 'pax' => $tour_menu->pax,
                // 'price' => (double)$tour_menu->price,
                // 'created' => now('Asia/Manila')->format('YmdHis'),
                // 'tpx' => $uinfo['tpx'],
            }

            // Rate
            if (strpos($key, 'rid') !== false) {
                // dd($item['amount']);
                $rate['name'] = $item['title'];;
                $rate['price'] = $item['price'];
                $rate['person'] = $item['person'];
                $rate['amount'] = $item['amount'];
                $rate['discounted'] = $item['discounted'] ?? 0;
                if(isset($item['orig_amount'])){
                    $rate['orig_amount'] = $item['orig_amount'];
                }
            }
            if (strpos($key, 'OA') !== false) {
                foreach($item as $key => $value){
                    $other_addons[$count]['title'] = $value['title'];
                    $other_addons[$count]['pcs'] = $value['pcs'];
                    $other_addons[$count]['price'] = $value['price'];
                    $other_addons[$count]['amount'] = $value['amount'];
                }
            }
            if (strpos($key, 'TA') !== false) {
                foreach($item as $created => $value){
                    $tour_menu[$count]['title'] = $value['title'] . ' '.$value['type'] . ' ('.$value['pax'].' pax)';
                    $tour_menu[$count]['tpx'] = $value['tpx'];
                    $tour_menu[$count]['price'] = $value['price'];
                    $tour_menu[$count]['used'] = $value['used'];
                    $tour_menu[$count]['key'] = $key . '(_)' . $created;
                    // dd($item);
                    $tour_menu[$count]['amount'] = $value['amount'];
                }
            }
            $count++;
        }

        unset($count);
        return view('system.reservation.show',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'conflict' => $conflict, 'rooms' => implode(',', $rooms), 'rate' => $rate, 'other_addons' => $other_addons]);
    }
    public function showCancel($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status == 3) abort(404);
        return view('system.reservation.show-cancel',  ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function showReschedule($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status == 3) abort(404);
        $rooms = Room::all();

        $availed = Reservation::where(function ($query) use ($reservation) {
            $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                  ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                  ->orWhere(function ($query) use ($reservation) {
                      $query->where('check_in', '<=', $reservation->check_in)
                            ->where('check_out', '>=', $reservation->check_out);
                  });
        })->where('id', '!=', $reservation->id)->whereBetween('status', [1, 2, 3])->get();

        $roomReserved = [];
        
        foreach($rooms as $key => $room){
            $count_paxes = 0;
            foreach($availed as $r_list){
                $rs= Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->where('id', $room->id)->get();
                foreach($rs as $room) $count_paxes += $room->customer[$r_list];
            }
            if($count_paxes >= $room->room->max_occupancy) {
                $roomReserved[] = $room->id;
            }

        }

        $web_contents = WebContent::all()->first() ?? [];
        return view('system.reservation.show-reschedule',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'availed' => $availed, 'rooms' => $rooms, 'reserved' => $roomReserved, 'web_contents' => $web_contents]);
    }
    public function updateCancel(Request $request, $id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status == 3) abort(404);

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
        $reservation->save();
        $reservation->message;
        $reservation->save();

        if(isset($message['cancel'])) unset($message['cancel']);
            $this->employeeLogNotif('Approve Cancel Request Reservation of ' . $reservation->userReservation->name());
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Cancellation',
                'body' => 'Your Reservation Cancel Request are now approved. '
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab' => 'cancel'])) ,$details['body'], $details, 'reservation.mail')));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Cancel Request of '.$reservation->userReservation->name().' was approved');
        
    }
    public function updateDisaproveCancel(Request $request, $id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status == 3) abort(404);
        $validator = Validator::make($request->all('reason'), [
            'reason' => ['required'],
        ]);
        if($validator->fails()) return back ()->with('error', $validator->errors()->all())->withInput($validator->getData());
        $validator = $validator->validate();
        $message = $reservation->message;
        $reservation->status = $message['cancel']['prev_status'];
        $reservation->save();
        if(isset($message['cancel'])) unset($message['cancel']);
        $reservation->message = $message;
        $reservation->save();
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Cancel',
            'body' => 'Sorry, Your Cancel Request are now disapproved due to ' . $validator['reason'] . '. If you want concern. Please contact the owner'
        ];
        if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home'),$details['body'], $details, 'reservation.mail')));
        return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Reschedule Request of '.$reservation->userReservation->name().'was successful disapproved');
        
    
    }
    public function forceCancel($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status == 3) abort(404);

        return view('system.reservation.cancel.force-cancel',  ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function forceReschedule(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status == 3) abort(404);
        if($reservation->accommodation_type == 'Day Tour') $request['check_out'] = $request['check_in'];
        
        if($reservation->accommodation_type == 'Day Tour'){
            $validated = Validator::make($request->all(), [
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
            $validated = Validator::make($request->all(), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Select date with 2 to 3 days',
                'required' => 'Need fill up first (:attribute)',
                'check_out.after_or_equal' => 'Select within 2 or 3 days above (Overnight)',
            ]);
        }
        elseif($reservation->accommodation_type == 'Room Only'){
            $validated = Validator::make($request->all(), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first (:attribute)',
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
        }
        if ($validated->fails()) return back()->with('error', $validated->errors()->all())->withInput($request->all());
        $validated = $validated->validate();

        $noDays = getNoDays($validated['check_in'], $validated['check_out']);

        $tourValidator = Validator::make(['days' => $noDays],[
            'days' => 'gte:'.$this->countTour($reservation),
        ], [
            'days.gte' => 'Select dates that match the number of your chosen tour menu ('.$this->countTour($reservation).' tours).'
        ]);
        if ($tourValidator->fails()) return back()->with('error', $tourValidator->errors()->all())->withInput($request->all());
        $roomReserved = [];
        $rooms = Room::all();
        $r_lists = Reservation::where(function ($query) use ($validated) {
            $query->where(function ($query) use ($validated) {
                $query->where('check_in', '>=', $validated['check_in'])
                      ->where('check_in', '<', $validated['check_out']);
            })->orWhere(function ($query) use ($validated) {
                $query->where('check_out', '>', $validated['check_in'])
                      ->where('check_out', '<=', $validated['check_out']);
            })->orWhere(function ($query) use ($validated) {
                $query->where('check_in', '<=', $validated['check_in'])
                      ->where('check_out', '>=', $validated['check_out']);
            });
        })->pluck('id');
        
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
        return view('system.reservation.reschedule.force-reschedule',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'reserved' => $roomReserved, 'rooms' => $rooms, 'new_cin' => $validated['check_in'], 'new_cout' => $validated['check_out']]);
    }
    public function updateForceCancel(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status == 3) abort(404);
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
        $updated = $reservation->update(['status' => 5]);
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Cancellation',
                'body' => 'Your Reservation are Forced Cancel from '.$system_user->name().' due of ' . $validated['message']. '. Sorry for waiting. Please try again to make reservation in another dates',
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'canceled'])) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
            unset($details, $text);
            $this->employeeLogNotif('Cancel Forced of ' . $reservation->userReservation->name() . ' Reservation', route('system.reservation.show', encrypt($reservation->id)));
            return redirect()->route('system.reservation.home')->with('success', 'Force Cancel of ' . $reservation->userReservation->name() . ' was Successful');
        }
        else{
            return back()->with('error', 'Something Wrong, Try Again')->withInput($request->all());
        }
    }
    public function updateForceReschedule(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status == 3) abort(404);
        $validator = Validator::make($request->all(),  [
            'passcode' => ['required', 'digits:4', 'numeric'],
            'message' => ['required', 'string'],
        ]);
        if($validator->fails()) return back ()->with('error', $validator->errors()->all())->withInput($request->all());

        $validator = $validator->validate();
        $validator['ncin'] = decrypt($request['ncin']);
        $validator['ncout'] = decrypt($request['ncout']);

        $system_user = $this->system_user->user();
        if(!Hash::check($validator['passcode'], $system_user->passcode)) return back ()->with('error', 'Invalid Passcode');

        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($request['room_pax']);
        else $validator['room_pax'] = $request['room_pax'];

        if(isset($request['force']) && $request['force'] == "on") $roomCustomer = $this->roomAssign($validator['room_pax'], $reservation, $validator, true, true);
        else $roomCustomer = $this->roomAssign($validator['room_pax'], $reservation, $validator, false, true);

        if(!is_array($roomCustomer)){
            return $roomCustomer;
        }
        foreach(Room::all() as $value) $value->removeCustomer($reservation->id);
        
        foreach($roomCustomer as $key => $pax){
            $room = Room::find($key);
            $room->addCustomer($reservation->id, $pax);
        }
        $updated = $reservation->update([
            'check_in' => $validator['ncin'],
            'check_out' => $validator['ncout'],
            'status' => 1,
            'roomid' => array_keys($roomCustomer),
        ]);

        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Force Reservation Reschedule',
                'body' => 'Your Force to reschedule from' . Carbon::createFromFormat('Y-m-d', $validator['ncin'])->setTimezone('UTC')->format('F j, Y') . ' to ' . Carbon::createFromFormat('Y-m-d', $validator['ncout'])->setTimezone('UTC')->format('F j, Y') . ' (UTC) by ' . $system_user->name() . ' due ' . $validator['message'],
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home') ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Force Reschedule Request of '.$reservation->userReservation->name().' was approved');
        }

    }
    public function updateReschedule(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status == 3) abort(404);
        $system_user = $this->system_user->user();

        $admins = System::all()->where('type', 0);

        if(!$reservation->status == 7) abort(404);
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($request['room_pax']);
        else $validator['room_pax'] = $request['room_pax'];

        if(isset($request['force'])) $roomCustomer = $this->roomAssign($validator['room_pax'], $reservation, $validator, true, true);
        else $roomCustomer = $this->roomAssign($validator['room_pax'], $reservation, $validator, false, true);

        if(!is_array($roomCustomer)){
            return $roomCustomer;
        }
        foreach(Room::all() as $value) $value->removeCustomer($reservation->id);

        foreach($roomCustomer as $key => $pax){
            $room = Room::find($key);
            $room->addCustomer($reservation->id, $pax);
        }
        $message = $reservation->message;

        $reservation->check_in = $message['reschedule']['check_in'];
        $reservation->check_out = $message['reschedule']['check_out'];
        $reservation->roomid = array_keys($roomCustomer);
        $reservation->status = 1;
        $reservation->save();
        unset($message['reschedule']);
        $reservation->message = $message;
        $reservation->roomid = array_keys($roomCustomer);
        $reservation->save();
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Reschedule',
            'body' => 'Your Request are now approved. '
        ];
        if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home') ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
        return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Reschedule Request of '.$reservation->userReservation->name().' was approved');
        
    }

}
