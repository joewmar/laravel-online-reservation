<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Room;
use App\Models\System;
use App\Models\RoomRate;
use App\Models\AuditTrail;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Notifications\UserNotif;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class EditReservationController extends Controller
{
    private $system_user;
    public function __construct()
    {
        $this->system_user = auth('system');
        $this->middleware(function ($request, $next){
            if(!($this->system_user->user()->type === 0 || $this->system_user->user()->type === 1 )) abort(404);
            return $next($request);

        });
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
        }
        return $roomCustomer; 
    }
    private function deleteInfo(Reservation $r_list){   
        foreach(Room::all() as $room) $room->removeCustomer($r_list->id);
        $transaction = $r_list->transaction ?? [];
        foreach($transaction as $key => $item){
            if (strpos($key, 'rid') !== false) unset($transaction[$key]);
            if (strpos($key, 'payment') !== false) unset($transaction[$key]);
        }
        $updated = $r_list->update(['roomid' => null, 'roomrateid' => null, 'transaction' => $transaction]);
        return $updated;
    }
    public function information($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        return view('system.reservation.edit.information', ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function updateInfo(Request $request, $id){
        $system_user = $this->system_user->user();
        $reservation = Reservation::findOrFail(decrypt($id));
        $validate = Validator::make($request->all(), [
            'age' => ['required', 'numeric'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after_or_equal:'.$request['check_in']],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric'],
            'payment_method' => ['required'],
            'status' => ['required'],
            'passcode' => Rule::when($reservation->pax == $request['pax'] && $request['status'] == $reservation->status, ['required', 'digits:4']),
        ], [
            'required' => 'Required (:attribute)',
        ]);
        
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();

        if($reservation->pax == $validate['pax'] && $reservation->status == $validate['status']){
            if(!Hash::check($validate['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');
            if($validate['check_in'] != $reservation->check_in || $validate['check_out'] != $reservation->check_out){
                $transaction = $reservation->transaction ?? [];
                $reservation->check_in = $validate['check_in'];
                $reservation->check_out = $validate['check_out'];
                foreach($transaction as $key => $item){
                    if (strpos($key, 'rid') !== false) {
                        // dd('Working');
                        $transaction[$key]['amount'] = (double)$transaction[$key]['price'] * $reservation->getNoDays();
                        if(isset($transaction['payment']['discountPerson'])) {
                            $discounted = (20 / 100) * (int)$transaction['payment']['discountPerson'];
                            $discounted = (double)($transaction[$key]['amount'] * $discounted);
                            $discounted = (double)($transaction[$key]['amount'] - $discounted);
                            $transaction[$key]['orig_amount'] = $transaction[$key]['amount'];
                            $transaction[$key]['amount'] = $discounted;
                        } 
                    }
                }
                $validate['transaction'] = $transaction;
                $reservation->save();
            }
            unset($validate['passcode']);
            $updated = $reservation->update($validate);
            if($updated) return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' Information was Updated');
        }
        else{
            $encrypted = encryptedArray([
                'age' => $validate['age'],
                'cin' => $validate['check_in'],
                'cout' => $validate['check_out'],
                'at' => $validate['accommodation_type'],
                'px' => $validate['pax'],
                'py' => $validate['payment_method'],
                'st' => $validate['status'],
            ]);
            // dd($reservation->downpayment());
            if($reservation->pax != $validate['pax'] || $validate['status'] == 1 || $validate['status'] == 2 ){
                return redirect()->route('system.reservation.edit.information.room', ['id' => encrypt($reservation->id), Arr::query($encrypted)]);
            }
            if($validate['status'] == 0){
                $this->deleteInfo($reservation);
                $transaction = $reservation->transaction ?? [];
                foreach($transaction as $key => $item){
                    if (strpos($key, 'TA') !== false) unset($transaction[$key]);
                    if (strpos($key, 'OA') !== false) unset($transaction[$key]);
                }
                $message = $reservation->message ?? [];
                foreach($message as $key => $item){
                    if (strpos($key, 'cancel') !== false) unset($message[$key]);
                    if (strpos($key, 'reschedule') !== false) unset($message[$key]);
                }
                $validate['transaction'] = $transaction;
                $validate['message'] = $message;
            }
            $updated = $reservation->update($validate);
            if($updated) {
                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => "Edit Information",
                    'body' => "Your Reservation was updated by " . $system_user->name(). '. If you have Concern Please Contact us',
                ];
                if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail')));
                $this->employeeLogNotif('Update Reservation for ' . $reservation->userReservation->name(), route('system.reservation.show', $id));
                return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' Information was Updated');
            }
        }


    }
    public function changeRoom(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $save = decryptedArray($request->query());
        if(empty($save)) abort(404);
        if(!($reservation->pax != $save['px']  || $save['st']  == 1 || $save['st'] == 2 )) abort(404);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $rateID = null;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'rid') !== false)  $rateID= explode('rid', $key)[1];
        }
        $roomReserved = [];
        $check_in = $save['cin'];
        $check_out = $save['cout'];
        $r_lists = Reservation::where(function ($query) use ($check_in, $check_out) {
            $query->whereBetween('check_in', [$check_in, $check_out])
                  ->orWhereBetween('check_out', [$check_in, $check_out])
                  ->orWhere(function ($query) use ($check_in, $check_out) {
                      $query->where('check_in', '<=', $check_in)
                            ->where('check_out', '>=', $check_out);
                  });
        })->whereBetween('status', [1, 2])->where('id', '!=', $reservation->id)->pluck('id');
        
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
        return view('system.reservation.edit.information-room', ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'rateid' => $rateID, 'reserved' => $roomReserved, 'info' => $save]);
    }
    public function updateInfoRoom(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $info = decryptedArray($request->query());
        if(empty($info)) abort(404);
        $system_user = $this->system_user->user();

        $isNotPending = $reservation->pax != $info['px'] || $info['st'] == 1 || $info['st'] == 2;
        $validate = Validator::make($request->all(), [
            'force' => Rule::when(isset($request['force']), ['required']),
            'passcode' => Rule::when(isset($request['passcode']) && $reservation->pax == $request['pax'], ['required', 'digits:4']),
            'room_rate' => Rule::when($isNotPending, ['required']),
            'room_pax' => Rule::when($isNotPending, ['required']),
            'senior_count' => Rule::when($info['st'] == 2 && isset($request['hs']) && $request['hs'] == 'on', ['required', 'numeric']),
            'cnpy' => Rule::when($info['st'] == 2, ['required']),
            'amountdy' => Rule::when($info['st'] == 1, ['required', 'numeric', 'min:1000']),
            'amountcinp' => Rule::when($info['st'] == 2 && (isset($request['cnpy']) && $request['cnpy'] == 'partial'), ['required', 'numeric', 'min:1']),
        ], [
            'required' => 'Required (:attribute)',
            'cnpy.required' => 'Required (Check-in Payment Selection)',
            'amountdy.required' => 'Required (Check-in Downpayment)',
            'amountcinp.required' => 'Required (Check-in Payment Selection)',
        ]);
        
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        $rp = 0;
        foreach($validate['room_pax'] ?? [] as $newPax){
            $rp += (int)$newPax;
            if($rp > $info['px'] && $rp < $info['px']) return back()->with('error', 'Guest you choose ('.$rp.' pax) does not match on Customer Guest ('.$validate['pax'].' pax)')->withInput($validate);
        }
        $roomCustomer = $this->roomAssign($validate['room_pax'], $reservation, $validate, ($request['force'] === 'on' ? true : false), true);
        if(!is_array($roomCustomer)) return $roomCustomer;
        
    
        $this->deleteInfo($reservation);
        $transaction = $reservation->transaction;

        $rate = RoomRate::find(decrypt($validate['room_rate'])) ?? [];

        if(!empty($rate)){
            $transaction['rid'.$rate->id]['title'] = $rate->name;
            $transaction['rid'.$rate->id]['price'] = $rate->price;
            $transaction['rid'.$rate->id]['amount'] = $rate->price * $reservation->getNoDays();
        }
        if(isset($validate['senior_count'])) {
            $transaction['payment']['discountPerson'] = $validate['senior_count'];
            $discounted = (20 / 100) * (int)$validate['senior_count'];
            $discounted = (double)($transaction['rid'.$rate->id]['amount'] * $discounted);
            $discounted = (double)($transaction['rid'.$rate->id]['amount'] - $discounted);
            $transaction['rid'.$rate->id]['orig_amount'] = $transaction['rid'.$rate->id]['amount'];
            $transaction['rid'.$rate->id]['amount'] = $discounted;
        } 
        $reservation->transaction = $transaction;

        $reservation->save();
        if($info['st'] == 2) {
            if($validate['cnpy'] == 'partial') $transaction['payment']['cinpay'] = $validate['amountcinp'];
            else $transaction['payment']['cinpay'] = $reservation->balance();
        };
        if($info['st'] == 1) $transaction['payment']['downpayment'] = $validate['amountdy'];

        unset($validate['room_pax'], $validate['passcode'], $validate['room_rate']);
        if(isset($validate['force'])) unset($validate['force']);

        $info = [
            'roomid' => array_keys($roomCustomer),
            'roomrateid' => $rate->id,
            'age' => $info["age"],
            'check_in' => $info["cin"],
            'check_out' => $info["cout"],
            'accommodation_type' => $info["at"],
            'pax' => $info["px"],
            'tour_pax' => $reservation->pax > $info["px"] ? $info["px"] : $reservation->pax,
            'payment_method' => $info["py"],
            'status' => $info["st"],
            'transaction' => $transaction,
        ];

        $updated = $reservation->update($info);

        if($updated) {
            foreach($roomCustomer as $roomid => $pax){
                $room = Room::find($roomid);
                $room->addCustomer($reservation->id, $pax);
            }
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => "Edit Information",
                'body' => "Your Reservation was updated by " . $system_user->name(). '. If you have Concern Please Contact of Owner',
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail')));
            $this->employeeLogNotif('Update Reservation for ' . $reservation->userReservation->name(), route('system.reservation.show', $id));
            return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' Information was Updated');
        }
    }
    public function services($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $count = 0;
        foreach($reservation->transaction ?? [] as $transKey => $item){
            if (strpos($transKey, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menu[$count]['id'] = $transKey;
                $tour_menu[$count]['title'] = $item['title'];
                $tour_menu[$count]['tpx'] = $item['tpx'];
                $tour_menu[$count]['price'] = $item['price'];
                $tour_menu[$count]['amount'] = $item['amount'];
            }
            if (strpos($transKey, 'TA') !== false && is_array($item)) {
                foreach($item as $key => $tourAddons){
                    $tour_addons[$count]['id'] = $transKey;
                    $tour_addons[$count]['title'] = $tourAddons['title'];
                    $tour_addons[$count]['created'] = Carbon::createFromFormat('YmdHis', $key)->format('M j, Y');
                    $tour_addons[$count]['tpx'] = $tourAddons['tpx'];
                    $tour_addons[$count]['price'] = $tourAddons['price'];
                    $tour_addons[$count]['amount'] = $tourAddons['amount'];
                }
            }
            if (strpos($transKey, 'OA') !== false && is_array($item)) {
                foreach($item as $key => $tourAddons){
                    $other_addons[$count]['id'] = $transKey;
                    $other_addons[$count]['title'] = $tourAddons['title'];
                    $other_addons[$count]['created'] = Carbon::createFromFormat('YmdHis', $key)->format('M j, Y g:ia');
                    $other_addons[$count]['pcs'] = $tourAddons['pcs'];
                    $other_addons[$count]['price'] = $tourAddons['price'];
                    $other_addons[$count]['amount'] = $tourAddons['amount'];
                }
            }
            $count++;
        }
        unset($count);
        return view('system.reservation.edit.services', ['activeSb' => 'Reservation', 'r_list' => $reservation, 'tour_menu' => $tour_menu ?? [],  'tour_addons' => $tour_addons ?? [],  'other_addons' => $other_addons ?? [],]);
    }
    public function rooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $rateID = null;
        $roomReserved = [];
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'rid') !== false)  $rateID= explode('rid', $key)[1];
        }
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
        return view('system.reservation.edit.rooms',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'rateid' => $rateID, 'reserved' => $roomReserved]);
    }
    public function updateRooms(Request $request, $id){
        $roomCustomer = [];

        if($request->has('room_rate')) $request['room_rate'] = decrypt($request['room_rate']);
        $validated = $request->validate([
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        
        $system_user = auth('system')->user();
        $reservation = Reservation::findOrFail(decrypt($id));

        if(!($reservation->status >= 2 && $reservation->status <= 3)) abort(404);

        if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($request['room_pax']);
        else $validated['room_pax'] = $request['room_pax'];

        $rp=0;
        foreach($validated['room_pax'] as $newPax) $rp += (int)$newPax;
        
        if($rp != $reservation->pax) return back()->with('error', 'Guest you choose ('.$rp.' pax) does not match on Customer Guest ('.$reservation->pax.' pax)')->withInput($validated);


        if(isset($request['force'])) $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated, true, true);
        else $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated, false, true);

        if(!is_array($roomCustomer)){
            return $roomCustomer;
        }

        foreach(Room::all() as $value){
            $value->removeCustomer($reservation->id);
        }
        $roomDetails = [];
        foreach($roomCustomer as $key => $pax){
            $room = Room::find($key);
            if($room) $room->addCustomer($reservation->id, $pax);
            $roomDetails[] = 'Room No. ' . $room->room_no . '('.$room->room->name.')';
        }
        if($reservation->update(['roomid' => array_keys($roomCustomer), 'transaction'])){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Change Room Assign',
                'body' => 'The business has changed your room assignment. Your room is now ' . implode(', ', $roomDetails),
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail')));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Change Room Assign of '.$reservation->userReservation->name().' was updated');
        }
    }
    public function payment(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($request->query('tab') === 'CINP') if(!($reservation->status === 2)) abort(404);
        else if(!($reservation->status >= 1 && $reservation->status <= 2)) abort(404);
        return view('system.reservation.edit.payment', ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function updateDY(Request $request, $id){
        try{
            $system_user = $this->system_user->user();
            $reservation = Reservation::findOrFail(decrypt($id));
            if(!($reservation->status >= 1 && $reservation->status <= 2)) abort(404);
            $validated = Validator::make($request->all(), [
                'amount' => ['required', 'numeric', 'max:'.$reservation->getTotal()],
                'passcode' => ['required', 'numeric', 'digits:4'],
            ], [
                'passcode.required' => 'Required to fill up (Passcode)', 
                'amount.required' => 'Required to fill up (Downpayment)', 
                'amount.numeric' => 'Number only',
                'amount.max' => 'The amount must exact ₱ '.number_format($reservation->getTotal(), 2).' below',
            ]);
            if($validated->fails()){
                return back()->with('error', $validated->errors()->all());
            }
            $validated = $validated->validate();

            if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');

            $downpayment = $reservation->transaction;
            $downpayment['payment']['downpayment'] = (double)$validated['amount'];
            $updated = $reservation->update(['transaction' => $downpayment]);
            if($updated){
                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => "Edit Downpayment",
                    'body' => "Your Downpayment was updated by " . $system_user->name() . " with  ₱" . number_format($validated['amount'], 2),
                ];
                if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));

                $this->employeeLogNotif('Fill the Payment of Force Payment for ' . $reservation->userReservation->name() . ' in ' . $validated['amount'] . ' pesos', route('system.reservation.show', $id));
                return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' downpayment was updated in amount of ₱ ' . number_format($validated['amount'], 2));
            } 
        }
        catch(Exception $e){
            return redirect()->route('system.reservation.home');
        }
    }
    public function updateCINP(Request $request, $id){
        // try{
            $system_user = $this->system_user->user();
            $reservation = Reservation::findOrFail(decrypt($id));
            if(!($reservation->status == 2)) abort(404);
            // dd($request->all());
            $haveSenior = $request->has('hs') && $request['hs'] == "on";
            $isPartial = isset($request['cnpy']) && $request['cnpy'] == "partial";
            $validated = Validator::make($request->all(), [
                'cnpy' => ['required'],
                'amount' => Rule::when($isPartial, ['required', 'numeric', 'max:'.$reservation->balance()+1]),
                'senior_count' => Rule::when($haveSenior, ['required']),
                'passcode' => ['required', 'numeric', 'digits:4'],
            ], [
                'passcode.required' => 'Required to fill up (Passcode)', 
                'amount.required' => 'Required to fill up (Check-in Payment)', 
                'amount.numeric' => 'Number only',
                'amount.min' => 'The amount must be ₱ 1,000 above',
                'amount.max' => 'The amount must exact ₱ '.number_format(($reservation->balance() - $reservation->downpayment()), 2).' below',
            ]);
            if($validated->fails()){
                return back()->with('error', $validated->errors()->all());
            }
            $validated = $validated->validate();

            if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');


            $cinpayment = $reservation->transaction;

            if($haveSenior){
                $discounted = null;
                $cinpayment['payment']['discountPerson'] = $validated['senior_count'];
                foreach($cinpayment ?? [] as $key => $item){
                    if (strpos($key, 'rid') !== false ) {
                        $rateID = (int)str_replace('rid','', $key);
                        $discounted = (20 / 100) * (int)$cinpayment['payment']['discountPerson'];
                        $discounted = (double)($cinpayment['rid'.$rateID]['amount'] * $discounted);
                        $discounted = (double)($cinpayment['rid'.$rateID]['amount'] - $discounted);
                        $cinpayment['rid'.$rateID]['orig_amount'] = $cinpayment['rid'.$rateID]['amount'];
                        $cinpayment['rid'.$rateID]['amount'] = $discounted;

                    }
                }   
            }
            
            else{
                if(isset($cinpayment['payment']['discountPerson'])) unset($cinpayment['payment']['discountPerson']);
                foreach($cinpayment ?? [] as $key => $item){
                    if (strpos($key, 'rid') !== false ) {
                        $rateID = (int)str_replace('rid','', $key);
                        if(isset($cinpayment['rid'.$rateID]['orig_amount'])) $cinpayment['rid'.$rateID]['amount'] = $cinpayment['rid'.$rateID]['orig_amount'];
                    }
                } 
            }
            $reservation->transaction = $cinpayment;
            if(!$isPartial){
                $cinpayment['payment']['cinpay'] = $reservation->getTotal() - $reservation->downpayment();
                $validated['amount'] = (double)$cinpayment['payment']['cinpay'];
            }
            else{
                $cinpayment['payment']['cinpay'] = (double)$validated['amount'];
            }
            $reservation->save();

            $updated = $reservation->update(['transaction' => $cinpayment]);
            if($updated){
                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => "Edit Check-in Payment",
                    'body' => "Your Check-in Payment was updated by " . $system_user->name() . " with  ₱" . number_format($validated['amount'], 2),
                ];
                if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));

                $this->employeeLogNotif('Fill the Payment of Force Payment for ' . $reservation->userReservation->name() . ' in ' . $validated['amount'] . ' pesos', route('system.reservation.show', $id));
                return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' downpayment was updated in amount of ₱ ' . number_format($validated['amount'], 2));
            } 
        // }
        // catch(Exception $e){
        //     return redirect()->route('system.reservation.home');
        // }
    }
}
