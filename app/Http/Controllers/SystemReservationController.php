<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Addons;
use App\Models\System;
// use App\Models\RoomList;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use App\Notifications\EmailNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
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
        if($request['tab'] === 'reschedule'){
            $r_list = Reservation::where('status', 4)->latest()->paginate(5);
        }
        if($request['tab'] === 'cancellation'){
            $r_list = Reservation::where('status', 5)->latest()->paginate(5);
        }
        if($request['tab'] === 'disaprove'){
            $r_list = Reservation::where('status', 6)->latest()->paginate(5);
        }
        // if($request->has('search')){
        //     $search = $request['search'];
        //     $r_list = Reservation::with(['userReservation' => function($query, $search =) {
        //         $query->where('first_name', 'like', '%'.$search.'%');
        //     }])
        //     ->get();
        //     dd($r_list); 
        // }
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
                if(isset($reservation->transaction['rid'.$rateID]['orig_amount'])){
                    $rate['orig_amount'] = $reservation->transaction['rid'.$rateID]['orig_amount'];
                }
            }
            if (strpos($key, 'OA') !== false && is_array($item)) {
                $OAID = (int)str_replace('OA','', $key);
                foreach($item as $key => $dataAddons){
                    $other_addons[$key]['title'] = $reservation->transaction['OA'.$OAID][$key]['title'];
                    $other_addons[$key]['pcs'] = $reservation->transaction['OA'.$OAID][$key]['pcs'];
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
    public function showCancel($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        return view('system.reservation.show-cancel',  ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function showReschedule($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $availed = Reservation::all()->where('check_in', $reservation->check_in)->where('check_out', $reservation->check_out)->except($reservation->id);;
        return view('system.reservation.show-reschedule',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'availed' => $availed]);
    }
    public function updateCancel(Request $request, $id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if(!($reservation->status <= 4 || $reservation->status <= 5)) abort(404);

        $validator = Validator::make($request->all('passcode'), [
            'passcode' => ['required', 'digits:4', 'numeric'],
        ]);
        if($validator->fails()) return back ()->with('error', $validator->errors()->all());
        $validator = $validator->validate();
        if(!Hash::check($validator['passcode'], $this->system_user->user()->passcode)) return back ()->with('error', 'Invalid Passcode');
        // Remove reserved rooms
        if(isset($reservation->roomid)){
            $rooms = Room::all();
            foreach($rooms as $room){
                $customers = $room->customer ?? [];
                if(array_key_exists($reservation->id, $customers)) unset($customers[$reservation->id]);
                $room->update(['customer' => $customers]);
            }
        }
        $reservation->status = 5;
        $updated = $reservation->save();
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Cancellation',
                'body' => 'Your Reservation Cancel Request are now approved. '
            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
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
        if(isset($message['reschedule'])) unset($message['reschedule']);
        $updated = $reservation->update(['message' => $message]);
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Reschedule',
                'body' => 'Sorry, Your Reservation Reschedule Request are now disapproved due to ' . $validator['reason'] . '. If you want concern. Please contact the owner'
            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Reschedule Request of '.$reservation->userReservation->name().'was successful disapproved');
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
        if(isset($message['cancel'])) unset($message['cancel']);
        $updated = $reservation->update(['message' => $message]);
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Cancellation',
                'body' => 'Sorry, Your Reservation Cancel Request are now disapproved due to ' . $validator['reason'] . '. If you want concern. Please contact the owner'
            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Cancel Request of '.$reservation->userReservation->name().'was successful disapproved');
        }
       
        
    }
    
    public function edit($id){
        if(!$this->system_user->user()->role() === "Admin") abort(404);
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status === 3) abort(404);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $tour_menu = [];
        $other_addons = [];
        $tour_addons = [];
        $count = 0;
        foreach($reservation->transaction ?? [] as $transKey => $item){
            if (strpos($transKey, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menuID = (int)str_replace('tm','', $transKey);
                $tour_menu[$count]['id'] = $transKey;
                $tour_menu[$count]['title'] = $reservation->transaction['tm'.$tour_menuID]['title'];
                $tour_menu[$count]['price'] = $reservation->transaction['tm'.$tour_menuID]['price'];
                $tour_menu[$count]['amount'] = $reservation->transaction['tm'.$tour_menuID]['amount'];
            }
            // Rate
            if (strpos($transKey, 'rid') !== false) {
                $rateID = (int)str_replace('rid','', $transKey);
                $your_rate['name'] = $reservation->transaction['rid'.$rateID]['title'];;
                $your_rate['price'] = $reservation->transaction['rid'.$rateID]['price'];
                $your_rate['amount'] = $reservation->transaction['rid'.$rateID]['amount'];
            }
            if (strpos($transKey, 'TA') !== false && is_array($item)) {
                $TAID = (int)str_replace('TA','', $transKey);
                foreach($item as $key => $tourAddons){
                    $tour_addons[$count]['id'] = $key;
                    $tour_addons[$count]['title'] = $reservation->transaction['TA'.$TAID][$key]['title'];
                    $tour_addons[$count]['price'] = $reservation->transaction['TA'.$TAID][$key]['price'];
                    $tour_addons[$count]['amount'] = $reservation->transaction['TA'.$TAID][$key]['amount'];
                }
            }
            if (strpos($transKey, 'OA') !== false && is_array($item)) {
                $OAID = (int)str_replace('OA','', $transKey);
                foreach($item as $key => $tourAddons){
                    $other_addons[$count]['id'] = $transKey;
                    $other_addons[$count]['title'] = $reservation->transaction['OA'.$OAID][$key]['title'];
                    $other_addons[$count]['pcs'] = $reservation->transaction['OA'.$OAID][$key]['pcs'];
                    $other_addons[$count]['price'] = $reservation->transaction['OA'.$OAID][$key]['price'];
                    $other_addons[$count]['amount'] = $reservation->transaction['OA'.$OAID][$key]['amount'];
                }
            }
            $count++;
        }
        unset($count);
        return view('system.reservation.edit',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'tour_addons' => $tour_addons, 'tour_menu' => $tour_menu, 'other_addons' => $other_addons]);

    }
    public function updateRInfo(Request $request, $id){ 
        if(!$this->system_user->user()->role() === "Admin") abort(404);
        $reservation = Reservation::findOrFail(decrypt($id));
        $rooms = Room::all();
        if(str_contains($request['check_in'], 'to')){
            $dateSeperate = explode('to', $request['check_in']);
            $request['check_in'] = trim($dateSeperate[0]);
            $request['check_out'] = trim ($dateSeperate[1]);
        }
        if(str_contains($request['check_out'], ', ')){
            $date = Carbon::createFromFormat('F j, Y', $request['check_out']);
            $request['check_out'] = $date->format('Y-m-d');
        }
        $request['check_in'] = Carbon::parse($request['check_in'] , 'Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'] , 'Asia/Manila')->format('Y-m-d');
        // Check out convertion word to date format

        if(checkAvailRooms($request['pax'], $request['check_in'])){
            return back()->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
        }

        $validator = null;
        if($request['accommodation_type'] == 'Day Tour'){
            $validator = Validator::make($request->all(), [
                'age' => ['required', 'numeric', 'min:8'],
                'room_rate' => ['required', 'numeric'],
                'status' => ['required', 'numeric'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
                'pax' => ['required', 'numeric', 'min:1'],
                'room_pax' => ['required', 'array'],
                'payment_method' => ['required'],
                'tour_menu' => Rule::when(!empty($request['tour_menu']), ['array']),
                'other_addons' => Rule::when(!empty($request['other_addons']), ['array']),
                'tour_addons' => Rule::when(!empty($request['tour_addons']), ['array']),
                'valid_id' => Rule::when(!empty($request['valid_id']), ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5024']),
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'tour_pax.max' => 'Sorry, You can choose who will going the tour based on your preference and the number of guests you have',
                'image' => 'The file must be an image of type: jpeg, png, jpg',
                'mimes' => 'The image must be of type: jpeg, png, jpg',
                'max' => 'The image size must not exceed 5 MB',
            ]);
        }
        elseif($request['accommodation_type'] == 'Overnight'){
            $validator = Validator::make($request->all(), [
                'age' => ['required', 'numeric', 'min:8'],
                'room_rate' => ['required', 'numeric'],
                'status' => ['required', 'numeric'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
                'accommodation_type' => ['required'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
                'pax' => ['required', 'numeric', 'min:1'],
                'room_pax' => ['required', 'array'],
                'payment_method' => ['required'],
                'tour_menu' => Rule::when(!empty($request['tour_menu']), ['array']),
                'other_addons' => Rule::when(!empty($request['other_addons']), ['array']),
                'tour_addons' => Rule::when(!empty($request['tour_addons']), ['array']),
                'valid_id' => Rule::when(!empty($request['valid_id']), ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5024']),
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'check_out.after_or_equal' => 'Choose within 2 day and above (Overnight)',
                'tour_pax.max' => 'Sorry, You can choose who will going the tour based on your preference and the number of guests you have',
                'image' => 'The file must be an image of type: jpeg, png, jpg',
                'mimes' => 'The image must be of type: jpeg, png, jpg',
                'max' => 'The image size must not exceed 5 MB',
            ]);
        }
        elseif($request['accommodation_type'] == 'Room Only'){
            $validator = Validator::make($request->all(), [
                'age' => ['required', 'numeric', 'min:8'],
                'room_rate' => ['required', 'numeric'],
                'status' => ['required', 'numeric'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
                'accommodation_type' => 'required',
                'pax' => ['required', 'numeric', 'min:1', 'max:'.(string)RoomList::max('max_occupancy')],
                'room_pax' => ['required', 'array'],
                'payment_method' => ['required'],
                'other_addons' => Rule::when(!empty($request['other_addons']), ['array']),
                'tour_addons' => Rule::when(!empty($request['tour_addons']), ['array']),
                'valid_id' => Rule::when(!empty($request['valid_id']), ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5024']),
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
                'tour_pax.max' => 'Sorry, You can choose who will going the tour based on your preference and the number of guests you have',
                'image' => 'The file must be an image of type: jpeg, png, jpg',
                'mimes' => 'The image must be of type: jpeg, png, jpg',
                'max' => 'The image size must not exceed 5 MB',
            ]);
        }
        else{
            return back()->withErrors(['accommodation_type' => 'Choose the Accommodation type'])->withInput();
        }
        if(empty($validator->getData()['room_pax'])) return back()->with('error', 'Required to choose room')->withInput($validator->getData());
        if ($validator->fails()) {   
            // $validator->         
            return back()->with('error', $validator->errors())->withInput();
            // return back()->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();

        $roomCustomer = [];
        // Room Update and Verification
        $reservationPax = 0;
        foreach($rooms as $room){
            $room->removeCustomer($reservation->id);
        }
        $rate = RoomRate::find($validated['room_rate']);
        foreach($validated['room_pax'] as $room_id => $newPax){
            $room = Room::find($room_id);
            if($room->availability === true) return back()->with('error', 'Room No. ' . $room->room_no. ' is not available')->withInput($validated);
            if($newPax > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$newPax.' pax) and Room Capacity ('.$room->room->max_occupancy.' capacity)')->withInput($validated);
            if($newPax > $room->getVacantPax() && $reservationPax < $room->getVacantPax()) return back()->with('error', 'Room No. ' . $room->room_no. ' are only '.$room->getVacantPax().' pax to reserved and your guest ('.$reservationPax.' pax)')->withInput($validated);
            if($newPax > $rate->occupancy) return back()->with('error', 'Room No. '.$room->room_no.' Guest you choose does not match on room rate')->withInput($validated);
            $reservationPax += (int)$newPax;
            $roomCustomer[$room_id] = $newPax;

        }
        if($reservationPax > $rate->occupancy || $reservationPax < $rate->occupancy) return back()->with('error', 'All Room Guest you choose does not match on room rate')->withInput($validated);
        if($reservationPax > $reservation->pax || $reservationPax < $reservation->pax) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$reservationPax.' pax) that already choose in previous room')->withInput($validated);

        if($request->hasFile('valid_id')){  
            if($reservation->valid_id) deleteFile($reservation->valid_id, 'private');
            $validated['valid_id'] = saveImageWithJPG($request, 'valid_id', 'valid_id', 'private');
        }
        $reservation->update([
            'age' => $validated['age'],
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'accommodation_type' => $validated['accommodation_type'],
            'pax' => $validated['pax'],
            'tour_pax' => $validated['tour_pax'],
            'payment_method' => $validated['payment_method'],
            'status' => $validated['status'],
            'roomrateid' => $validated['room_rate'],
            'roomid' => array_keys($roomCustomer),
            'valid_id' => $validated['valid_id'] ?? $reservation->valid_id,
        ]);
        foreach($roomCustomer as $key => $newCustomer){
            $room = Room::find($key);
            $room->addCustomer($reservation->id, $newCustomer);
        }

        if(!empty($validated['tour_menu'])){
            foreach($validated['tour_menu'] as $item){
                $transaction = $reservation->transaction;
                if (isset($transaction[decrypt($item)])) {
                    unset($transaction[decrypt($item)]);
                    $reservation->update(['transaction' => $transaction]);
                }
            }
        }
        if(!empty($validated['tour_addons'])){
            foreach($validated['tour_addons'] as $item){
                $transaction = $reservation->transaction;
                if (isset($transaction[decrypt($item)])) {
                    unset($transaction[decrypt($item)]);
                    $reservation->update(['transaction' => $transaction]);
                }
            }
        }
        if(!empty($validated['other_addons'])){
            foreach($validated['other_addons'] as $item){
                $transaction = $reservation->transaction;
                if (isset($transaction[decrypt($item)])) {
                    unset($transaction[decrypt($item)]);
                    $reservation->update(['transaction' => $transaction]);
                }
            }
        }
        return redirect()->route('system.reservation.edit', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' information was updated');

        
    }
    public function receipt($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $tour_menu = [];
        $other_addons = [];
        $tour_addons = [];
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
        if($request->has('room_rate')) $request['room_rate'] = decrypt($request['room_rate']);
        $validated = $request->validate([
            'room_rate' => ['required', Rule::when($request->has('room_rate'), ['numeric'])],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0);
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status >= 1) abort(404);
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($validated);
        else $validated['room_pax'] = $request['room_pax'];

        $rate = RoomRate::find($validated['room_rate']);
        $roomCustomer = [];
        $reservationPax = 0;
        foreach($validated['room_pax'] as $room_id => $newPax){
            $room = Room::find($room_id);
            if($room->availability === true) return back()->with('error', 'Room No. ' . $room->room_no. ' is not available')->withInput($validated);
            if($newPax > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$newPax.' pax) and Room Capacity ('.$room->room->max_occupancy.' capacity)')->withInput($validated);
            if($newPax > $room->getVacantPax() && $reservationPax < $room->getVacantPax()) return back()->with('error', 'Room No. ' . $room->room_no. ' are only '.$room->getVacantPax().' pax to reserved and your guest ('.$reservationPax.' pax)')->withInput($validated);
            if($newPax > $rate->occupancy) return back()->with('error', 'Room No. '.$room->room_no.' Guest you choose does not match on room rate')->withInput($validated);
            $reservationPax += (int)$newPax;
            $roomCustomer[$room_id] = $newPax;

        }
        if($reservationPax > $rate->occupancy || $reservationPax < $rate->occupancy) return back()->with('error', 'All Room Guest you choose does not match on room rate')->withInput($validated);
        if($reservationPax > $reservation->pax || $reservationPax < $reservation->pax) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$reservationPax.' pax) that already choose in previous room')->withInput($validated);

        $transaction = $reservation->transaction;
        $transaction['rid'.$rate->id]['title'] = $rate->name;
        $transaction['rid'.$rate->id]['price'] = $rate->price;
        $transaction['rid'.$rate->id]['amount'] = $rate->price * $reservation->getNoDays();
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
            $rooms = Room::all();
            foreach($rooms as $room){
                if ($room->checkAvailability()) continue;
            }
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
            foreach($reservation->roomid as $item)$roomDetails[] = 'Room No ' . Room::find($item)->room_no . ' ('.Room::find($item)->room->name.')';
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

            if($system_user->role() === "Manager"){
                foreach($admins as $admin){
                    if(isset($admin->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID'), $text, null, 'bot2');;
                }
            }
            $text = null;
            $url = null;
            if($reservation->payment_method == "Gcash"){
                $url = route('reservation.gcash', encrypt($reservation->id));
            }
            if($reservation->payment_method == "PayPal"){
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
            foreach($admins as $admin) {
                if($admin->telegram_chatID != null) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, null, 'bot2');
            }
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            unset($text, $details);
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
            'body' => 'You now checked out at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A') . ' If you have time, you can feedback your experience',
            'link' => route('reservation.feedback', encrypt($reservation->id)),
        ];   
        if($system_user->role() === "Manager"){
            foreach($admins as $admin) {
                if($admin->telegram_chatID != null) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, null,'bot2');
            }
        }
        Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
        unset($text, $details);
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
            if($system_user->role() === "Manager"){
                foreach($admins as $admin) {
                    if($admin->telegram_chatID != null) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, null,'bot2');
                }
            }    
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Disaprove',
                'body' => 'Your Reservation are disapprove due of ' . $messages['disaprove']. 'Sorry for waiting. Please try again to make reservation in another dates',
            ];
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            unset($details, $text);
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
        $admins = System::all()->where('type', 0);

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
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
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
            Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.online-payment-mail', $details['title']));
        }
        $text = 
        "Employee Action: Approve Online Payment !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Amount: " . $reservation->userReservation->nationality  ."\n" . 
        "Who Approve Payment: " . $system_user->name() ;
        if($system_user->role() === "Manager"){
            foreach($admins as $admin) {
                if($admin->telegram_chatID != null) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, null,'bot2');
            }
        }   
        unset($downpayment);
        return redirect()->route('system.reservation.show.online.payment', encrypt($reservation->id))->with('success', 'Approved payment successful');
    }
    public function disaproveOnlinePayment(Request $request, $id){
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0);
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
        $text = 
        "Employee Action: Disaprove Online Payment !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Amount: " . $reservation->userReservation->nationality  ."\n" . 
        "Who Disaprove Payment: " . $system_user->name() ;
        if($system_user->role() === "Manager"){
            foreach($admins as $admin) {
                if($admin->telegram_chatID != null) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, null,'bot2');
            }
        }  
        Mail::to(env('SAMPLE_EMAIL', $reservation->userReservation->email))->queue(new ReservationMail($details, 'reservation.online-payment-mail', $details['title']));
    }
    public function storeForcePayment(Request $request, $id){
        $system_user = $this->system_user->user();
        $reservation = Reservation::findOrFail(decrypt($id));
        $admins = System::all()->where('type', 0);
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
        $reservation->payment_cutoff = null;
        $reservation->save();

        if($updated){
            $text = 
            "Employee Action: Force Payment !\n" .
            "Name: ". $reservation->userReservation->name() ."\n" . 
            "Age: " . $reservation->age ."\n" .  
            "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
            "Who Approve Force Payment: " . $system_user->name() ;
            if($system_user->role() === "Manager"){
                foreach($admins as $user){
                    if(isset($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, null, 'bot2');
                }
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
        $system_user = $this->system_user->user();
        $admins = System::all()->where('type', 0);
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
            $text = 
            "Employee Action: Additional tour !\n" .
            "Name: ". $reservation->userReservation->name() ."\n" . 
            "Age: " . $reservation->age ."\n" .  
            "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
            "What kind of Addons: Tour Services \n" . 
            "Who Update Addons: " . $system_user->name() ;
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
            $text = 
            "Employee Action: Other Additional !\n" .
            "Name: ". $reservation->userReservation->name() ."\n" . 
            "Age: " . $reservation->age ."\n" .  
            "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
            "What kind of Addons: Other Request Addons \n" . 
            "Who Update Addons: " . $system_user->name() ;
        }
        $updated = $reservation->update([
            'transaction' => $transaction,
        ]);

        foreach($admins as $user){
            if(isset($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, null, 'bot2');
        }
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
        $text = 
        "Employee Action: Extend Days !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "No. of days: " .$validated['no_days'] > 1 ? $validated['no_days'] . " days" : $validated['no_days'] . " day" . "\n" . 
        "Who Update Addons: " . $system_user->name() ;
        foreach($admins as $user){
            if(isset($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, null, 'bot2');
        }
        if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->useReservation()->name() . ' was extend in ' . ($validated['no_days'] > 1 ? $validated['no_days'] . ' days' : $validated['no_days'] . ' day'));
    }
}
