<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Room;
use App\Models\System;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\AuditTrail;
use App\Models\Reservation;
use App\Models\UserOffline;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
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
            'role' => $user->type ?? '',
            'action' => $action,
            'module' => 'Reservation',
        ]);
    }
    private function roomAssign(array $rooms, Reservation $reservation, $validated, bool $forceAssign = false, bool $changeAssign = false, $allPax = false){
        $roomCustomer = [];
        $reservationPax = 0;
        if($allPax) $allPaxes = $allPax;
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
            if(isset($allPaxes) && $allPaxes != $reservationPax) return back()->with('error', 'Cannot proceed due the guest does not match between customer ('.$allPaxes.' pax) and choose guest ('.$reservationPax.' pax)')->withInput($validated);
        }
        return $roomCustomer; 
    }
    private function reservationValidation(Request $request, $id = null){
        $validator = null;
        if($request['accommodation_type'] == 'Day Tour'){
            $validator = Validator::make($request->all(), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['check_in'], 'date_equals:'.$request['check_in']],
                'payment_method' => ['required'],
                'accommodation_type' => ['required'],
                'tour_pax' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'status' => ['required', 'numeric'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Overnight'){
            $validator = Validator::make($request->all(), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)->format('Y-m-d')],
                'accommodation_type' => ['required'],
                'tour_pax' => ['required'],
                'payment_method' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'status' => ['required', 'numeric'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'check_out.after_or_equal' => 'Choose within 2 or 3 days (Overnight)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Room Only'){
            $validator = Validator::make($request->all(), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'payment_method' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'status' => ['required', 'numeric'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
            ]);
        }
        else{
            return back()->withErrors(['accommodation_type' => 'Choose the Accommodation type'])->withInput($request->all());
        }
        if ($validator->fails()) return back()->withErrors($validator)->withInput($request->all());
        
        return $validator->validated();
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
    public function step1(Request $request, $id){
        $rlist = Reservation::findOrFail(decrypt($id));
        $roomInfo = [
            'at' =>    $rlist->accommodation_type,
            'cin' =>   $rlist->check_in,
            'cout' =>  $rlist->check_out,
            'px' =>  $rlist->pax,
            'tpx' =>  $rlist->tour_pax,
            'py' =>  $rlist->payment_method,
            'st' =>  $rlist->status,
        ];
        if(session()->has('esrinfo')){
            $esrinfo = $request->session()->get('esrinfo');
            $roomInfo = [
                'at' => isset($esrinfo['at']) ? decrypt($esrinfo['at']) : $roomInfo['at'],
                'cin' => isset($esrinfo['cin']) ? decrypt($esrinfo['cin']) : $roomInfo['cin'],
                'cout' => isset($esrinfo['cout']) ? decrypt($esrinfo['cout']) : $roomInfo['cout'],
                'px' => isset($esrinfo['px']) ? decrypt($esrinfo['px']) : $roomInfo['px'],
                'tpx' => isset($esrinfo['tpx']) ? decrypt($esrinfo['tpx']) : $roomInfo['tpx'],
                'py' => isset($esrinfo['py']) ? decrypt($esrinfo['py']) : $roomInfo['py'],
                'st' => isset($esrinfo['st']) ? decrypt($esrinfo['st']) : $roomInfo['st'],
            ];
        }
        return view('system.reservation.edit.step1',  [
            'activeSb' => 'Reservation', 
            'roomInfo' => $roomInfo, 
            'id' => $id, 
            'name' => $rlist->userReservation->name(), 
        ]);
    }
    public function storeStep1(Request $request, $id){
        $rlist = Reservation::findOrFail(decrypt($id));
        if($request->has('accommodation_type') && $request['accommodation_type'] === 'Day Tour') $request['check_out'] = $request['check_in'];
        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');


        $validated = $this->reservationValidation($request);
        if(!is_array($validated)) return $validated;

        $session = session('esrinfo');
        $session['cin'] = encrypt($validated['check_in']);
        $session['cout'] = encrypt($validated['check_out']) ;
        if(isset($session['oat'])) $session['oat'] = $session['at'];
        else $session['oat'] = encrypt($rlist->accommdation_type);
        $session['at'] = encrypt($validated['accommodation_type']) ;
        $session['px'] = encrypt($validated['pax']) ;
        $session['py'] = encrypt($validated['payment_method']) ;
        $session['st'] = encrypt((int)$validated['status']) ;
        if(isset($validated['tour_pax']) && $validated['accommodation_type'] != 'Room Only') {
            if(isset($session['otpx'])) $session['otpx'] = $session['tpx'];
            else $session['otpx'] = encrypt($rlist->tour_pax);
            $session['tpx'] = encrypt($validated['tour_pax']) ;
        }
        else unset($session['tpx']);
        session(['esrinfo' => $session]);

        // dd(decryptedArray(session('esrinfo')));
        if($validated['status'] == 0) return redirect()->route('system.reservation.edit.step3', $id);
        else return redirect()->route('system.reservation.edit.step2', $id);
    }
    public function step2(Request $request, $id){
        $rlist = Reservation::findOrFail(decrypt($id));
        if(!session()->has('esrinfo')) return redirect()->route('system.reservation.edit.step1', $id);
        $rooms = Room::all() ?? [];
        $rates = RoomRate::all() ?? [];
        $params = decryptedArray(session('esrinfo'));
        $roomReserved = [];
        $r_lists = Reservation::where(function ($query) use ($params) {
            $query->whereBetween('check_in', [$params['cin'], $params['cout']])
                  ->orWhereBetween('check_out', [$params['cin'], $params['cout']])
                  ->orWhere(function ($query) use ($params) {
                      $query->where('check_in', '<=', $params['cin'])
                            ->where('check_out', '>=', $params['cout']);
                  });
        })->whereBetween('status', [1, 2])->whereNot('id', $rlist->id)->pluck('id');
        
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
        $roomInfo = [
            'at' =>   $params['at'] ?? old('accommodation_type'),
            'px' =>  $params['px'] ?? old('pax'),
            'cin' => $params['cin'] ?? old('check_in'),
            'cout' => $params['cout'] ?? old('check_out'),
            'rm' =>  $params['rm'] ?? ($rlist->roomid ?? []),
            'rt' => $params['rt'] ?? ($rlist->roomrateid ?? ''),
            'py' => $params['py'] ?? ($rlist->payment_method ?? ''),
            'tpx' => $params['tpx'] ?? ($rlist->tour_pax ?? 0),
            'st' =>  $params['st'] ?? ($rlist->status ?? ''),
        ];
        // dd($rlist->roomid);
        return view('system.reservation.edit.step2',  [
            'activeSb' => 'Reservation', 
            'rooms' => $rooms, 
            'rates' => $rates, 
            'reserved' => $roomReserved, 
            'roomInfo' => $roomInfo, 
            'rlist' => $rlist, 
        ]);
    }
    public function storeStep2(Request $request, $id){
        $rlist = Reservation::findOrFail(decrypt($id));
        if(!session()->has('esrinfo')) return redirect()->route('system.reservation.edit.step1', $id);
        $roomCustomer = [];
        if($request->has('room_rate')) $validated['room_rate'] = decrypt($request['room_rate']);
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($validated);
        else $validated['room_pax'] = $request['room_pax'];

        $session = session('esrinfo');

        if(isset($request['force'])) $roomCustomer = $this->roomAssign($validated['room_pax'], $rlist, $validated, true, true, decrypt( $session['px']));
        else $roomCustomer = $this->roomAssign($validated['room_pax'], $rlist, $validated, false, true, decrypt( $session['px']));
        
        if(!is_array($roomCustomer)) return $roomCustomer;
        
        $session['rm'] = encrypt($roomCustomer);
        $session['rt'] = encrypt($validated['room_rate']);
        session(['esrinfo' => $session]);
        return redirect()->route('system.reservation.edit.step4', $id);

    }
    public function step4($id){
        $rlist = Reservation::findOrFail(decrypt($id));
        if(!session()->has('esrinfo')) return redirect()->route('system.reservation.edit.step1', $id);
        $decrypted = decryptedArray(session('esrinfo'));
        // dd($decrypted);
        $tour_menus = [];
        $rooms = [];
        $noDays = getNoDays($decrypted['cin'], $decrypted['cout']) ?? 1;
        // dd($decrypted);
        if(isset($decrypted['rm'])){
            foreach($decrypted['rm'] ?? [] as $rID => $pax){
                $room = Room::find($rID);
                if($room) $rooms[] =  'Room No. ' .$room->room_no . ' ('.$pax.' guest)';
                else $rooms[] =  'Room Data Missing';
            }
            $rooms = implode(', ', $rooms);
        }

        if(isset($decrypted['rt'])){
            $rate = RoomRate::find($decrypted['rt']);
            $rates['name'] = $rate->name;
            $rates['price'] = $rate->price;
            $rates['amount'] = (double)$rate->price * $noDays;
            if($rlist->countSenior()){
                $rates['orig_amount'] =  $rates['amount'];
                $rates['amount'] = $rlist->discounted($rates['amount'], $rlist->countSenior());
            }
        }
        $decrypted['scount'] = $rlist->countSenior();
        // if($other_info['cinpy'])
        // unset($count);

        return view('system.reservation.edit.step4',  [
            'activeSb' => 'Reservation', 
            'tour_menus' => $tour_menus, 
            'rooms' => $rooms ?? [], 
            'rates' => $rates ?? [], 
            'other_info' => $decrypted, 
            "user_days" => $noDays,
            "name" => $rlist->userReservation->name(),
            "id" => $id,
        ]);
    }
    public function storeStep4($id){
        $rlist = Reservation::findOrFail(decrypt($id));
        $rooms = Room::all();
        if(!session()->has('esrinfo')) return redirect()->route('system.reservation.edit.step1', $id);
        $system_user = $this->system_user->user();
        $esrinfo = decryptedArray(session('esrinfo'));
        $transaction = $rlist->transaction ?? [];
        $noDays = getNoDays($esrinfo['cin'], $esrinfo['cout']);
        if($esrinfo['st'] == 0) {
            unset($esrinfo['rt'], $esrinfo['rm']);
            foreach($transaction ?? [] as $key => $item){
                if(strpos($key, 'rid') !== false) unset($transaction[$key]);
                if(strpos($key, 'payment') !== false) unset($transaction[$key]);
                foreach($rooms as $room) $room->removeCustomer($rlist->id);
            }
        }

        if(isset($esrinfo['rt'])){
            foreach($transaction ?? [] as $key => $item) if(strpos($key, 'rid') !== false) unset($transaction[$key]);
            $rate = RoomRate::withTrashed()->findOrFail($esrinfo['rt']);
            $person = $rate->price * $noDays;
            $transaction['rid'.$rate->id]['title'] = $rate->name;
            $transaction['rid'.$rate->id]['price'] = $rate->price;
            $transaction['rid'.$rate->id]['person'] = $person;
            $transaction['rid'.$rate->id]['amount'] = $person * (int)$esrinfo['px'];
            $discounted = $rlist->discounted($person);

            if($rlist->countSenior() > 0){
                $amount = $rate->price * $noDays;
                $transaction['rid'.$rate->id]['orig_amount'] =  $amount * (int)$esrinfo['px'];
                $roomtotal = 0;
                for($i = 1; $i <= (int)$esrinfo['px']; $i++){
                    if($i <= (int)$rlist->countSenior()) $roomtotal += $discounted;
                    else $roomtotal += (double)$person;
                }
                $transaction['rid'.$rate->id]['amount'] = $roomtotal;
                $transaction['rid'.$rate->id]['discounted'] = $discounted;
            }
        }
        $rlist->status = (int)$esrinfo['st'];
        $rlist->check_in = $esrinfo['cin'];
        $rlist->check_out = $esrinfo['cout'];
        $rlist->accommodation_type = $esrinfo['at'];
        $rlist->pax = (int)$esrinfo['px'];
        $rlist->payment_method = $esrinfo['py'];
        $rlist->tour_pax = isset($esrinfo['tpx']) ? (int)$esrinfo['tpx'] : null;
        $rlist->roomrateid = isset($esrinfo['rt']) ? (int)$esrinfo['rt'] : null;
        $rlist->roomid = isset($esrinfo['rm']) ? array_keys($esrinfo['rm']) : null;
        $rlist->payment_cutoff = ($esrinfo['st'] = 1 && $rlist->downpayment() > 0) ? Carbon::now()->addDays(1)->format('Y-m-d H:i:s') : null;
        $rlist->transaction = $transaction;
        $rlist->save();

        if(isset($esrinfo['rm'])) foreach($rooms as $room) $room->removeCustomer($rlist->id);
        foreach($esrinfo['rm'] as $rID => $pax) {
            $room = Room::find($rID);
            $room->addCustomer($rlist->id, $pax);
        }
        $details = [
            'name' => $rlist->userReservation->name(),
            'title' => "Edit Information",
            'body' => "Your Reservation was updated by " . $system_user->name(). '. If you have Concern Please Contact of Owner',
        ];
        if(isset($rlist->user_id)) $rlist->userReservation->notify((new UserNotif(route('user.reservation.show', $id) ,$details['body'], $details, 'reservation.mail')));
        $this->employeeLogNotif('Update Reservation for ' . $rlist->userReservation->name(), route('system.reservation.show', $id));
        return to_route('system.reservation.show', $id)->with('success', $rlist->userReservation->name() . ' information was updated');
        
    }
    public function customer($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        abort_if(!isset($reservation->offline_user_id),404);
        $user = UserOffline::findOrFail($reservation->offline_user_id);
        return view('system.reservation.edit.customer', ['activeSb' => 'Reservation', 'user' => $user, 'r_list' => $reservation]);
    }
    public function updateCustomer(Request $request, $id){
        $user = UserOffline::findOrFail(decrypt($id));
        $validated = $request->validate([
            'passcode' => ['required', 'numeric', 'digits:4'],
            'first_name' => ['required', 'min:1'],
            'last_name' => ['required', 'min:1'],
            'birthday' => ['required', 'date'],
            'country' => ['required', 'min:1'],
            'nationality' => ['required'],
            'contact' => ['nullable'],
            'email' => ['required', 'email', Rule::when($request->has('email') && $request['email'] != $user->email, Rule::unique('user_offlines', 'email'))],
            'valid_id_clear' => ['required'], 
            'valid_id' => Rule::when($request['valid_id_clear'] == true, ['image', 'mimes:jpeg,png,jpg', 'max:5024'], ['nullable']), 
        ], [
            'required' => 'This input are required',
            'image' => 'The file must be an image of type: jpeg, png, jpg',
            'mimes' => 'The image must be of type: jpeg, png, jpg',
            'max' => 'The image size must not exceed 5 MB',
        ]);
        if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        if($request->hasFile('valid_id') && $validated['valid_id_clear'] == 0){
            if(isset($user->valid_id)) deleteFile($user->valid_id, 'private');
            $validated['valid_id'] = saveImageWithJPG($request, 'valid_id', 'valid_id', 'private');
        }
        else{
            unset($validated['valid_id']);
        }
        unset($validated['passcode']);
        unset($validated['valid_id_clear']);
        $updated = $user->update($validated);
        $this->employeeLogNotif($user->name() . ' customer information was updated');
        if($updated) return back()->with('success', $user->name() . ' was updated');
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
    // public function updateInfoRoom(Request $request, $id){
    //     $reservation = Reservation::findOrFail(decrypt($id));
    //     $info = decryptedArray($request->query());
    //     if(empty($info)) abort(404);
    //     $system_user = $this->system_user->user();

    //     $isNotPending = $reservation->pax != $info['px'] || $info['st'] == 1 || $info['st'] == 2;
    //     $validate = Validator::make($request->all(), [
    //         'force' => Rule::when(isset($request['force']), ['required']),
    //         'passcode' => Rule::when(isset($request['passcode']) && $reservation->pax == $request['pax'], ['required', 'digits:4']),
    //         'room_rate' => Rule::when($isNotPending, ['required']),
    //         'room_pax' => Rule::when($isNotPending, ['required']),
    //         'senior_count' => Rule::when($info['st'] == 2 && isset($request['hs']) && $request['hs'] == 'on', ['required', 'numeric']),
    //         'amountdy' => Rule::when($info['st'] == 1, ['required', 'numeric', 'min:1000', Rule::when($reservation->balance() >= 1000, 'min:1000')]),
    //         'amountcinp' => Rule::when($info['st'] == 2, ['required', 'numeric', 'min:1']),
    //     ], [
    //         'required' => 'Required (:attribute)',
    //         'amountdy.required' => 'Required (Check-in Downpayment)',
    //         'amountcinp.required' => 'Required (Check-in Payment)',
    //     ]);
        
    //     if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($request->all());
    //     $validate = $validate->validate();
    //     $rp = 0;
    //     foreach($validate['room_pax'] ?? [] as $newPax){
    //         $rp += (int)$newPax;
    //         if($rp > $info['px'] && $rp < $info['px']) return back()->with('error', 'Guest you choose ('.$rp.' pax) does not match on Customer Guest ('.$validate['pax'].' pax)')->withInput($validate);
    //     }
    //     $roomCustomer = $this->roomAssign($validate['room_pax'], $reservation, $validate, ($request['force'] === 'on' ? true : false), true);
    //     if(!is_array($roomCustomer)) return $roomCustomer;
        
    
    //     $this->deleteInfo($reservation);
    //     $transaction = $reservation->transaction;

    //     $rate = RoomRate::find(decrypt($validate['room_rate'])) ?? [];

    //     if(!empty($rate)){
    //         $transaction['rid'.$rate->id]['title'] = $rate->name;
    //         $transaction['rid'.$rate->id]['price'] = $rate->price;
    //         $transaction['rid'.$rate->id]['amount'] = $rate->price * $reservation->getNoDays();
    //     }
    //     if(isset($validate['senior_count'])) {
    //         $transaction['payment']['discountPerson'] = $validate['senior_count'];
    //         $discounted = (20 / 100) * (int)$validate['senior_count'];
    //         $discounted = (double)($transaction['rid'.$rate->id]['amount'] * $discounted);
    //         $discounted = (double)($transaction['rid'.$rate->id]['amount'] - $discounted);
    //         $transaction['rid'.$rate->id]['orig_amount'] = $transaction['rid'.$rate->id]['amount'];
    //         $transaction['rid'.$rate->id]['amount'] = $discounted;
    //     } 
    //     $reservation->transaction = $transaction;

    //     $reservation->save();
    //     if($info['st'] == 2) $transaction['payment']['cinpay'] = $validate['amountcinp'];
        
    //     if($info['st'] == 1) $transaction['payment']['downpayment'] = $validate['amountdy'];

    //     unset($validate['room_pax'], $validate['passcode'], $validate['room_rate']);
    //     if(isset($validate['force'])) unset($validate['force']);

    //     $info = [
    //         'roomid' => array_keys($roomCustomer),
    //         'roomrateid' => $rate->id,
    //         'check_in' => $info["cin"],
    //         'check_out' => $info["cout"],
    //         'accommodation_type' => $info["at"],
    //         'pax' => $info["px"],
    //         'payment_method' => $info["py"],
    //         'status' => $info["st"],
    //         'transaction' => $transaction,
    //     ];

    //     $updated = $reservation->update($info);

    //     if($updated) {
    //         foreach($roomCustomer as $roomid => $pax){
    //             $room = Room::find($roomid);
    //             $room->addCustomer($reservation->id, $pax);
    //         }
    //         $details = [
    //             'name' => $reservation->userReservation->name(),
    //             'title' => "Edit Information",
    //             'body' => "Your Reservation was updated by " . $system_user->name(). '. If you have Concern Please Contact of Owner',
    //         ];
    //         if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail')));
    //         $this->employeeLogNotif('Update Reservation for ' . $reservation->userReservation->name(), route('system.reservation.show', $id));
    //         return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' Information was Updated');
    //     }
    // }
    public function services($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $count = 0;
        foreach($reservation->transaction ?? [] as $transKey => $item){
            if (strpos($transKey, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menu[$count]['id'] = $transKey;
                $tour_menu[$count]['title'] = $item['title'];
                $tour_menu[$count]['tpx'] = $item['tpx'];
                $tour_menu[$count]['created'] = Carbon::createFromFormat('YmdHis', $item['created'])->format('M j, Y');
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
    public function updateServices(Request $request, $id){
        $system_user = $this->system_user->user();
        $rlist = Reservation::findOrFail(decrypt($id));
        $tours = $request->input('tour_menu');
        $tours = decryptedArray($tours);
        $transaction = $rlist->transaction;
        foreach($tours ?? [] as $tr) {
            if(array_key_exists($tr, $transaction)) unset($transaction[$tr]);
        }
        if($rlist->update(['transaction' => $transaction])){
            $details = [
                'name' => $rlist->userReservation->name(),
                'title' => 'Change Tour/Addon Service',
                'body' => "Your Service was changed by " . $system_user->name(),
            ];
            $message = 'Tour/Addon Services selected was removed';
            $this->employeeLogNotif($message);
            if(isset($rlist->user_id)) $rlist->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($rlist->id)) ,$details['body'], $details, 'reservation.mail')));
            return redirect()->route('system.reservation.show', $id)->with('success', $message);
        }
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

        abort_if(!($reservation->status >= 1 && $reservation->status <= 3), 404);
        // dd($reservation);

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
            $message = 'Change Room Assign of '.$reservation->userReservation->name().' was updated';
            $this->employeeLogNotif($message, route('system.reservation.show', $id));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $message);
        }
    }
    public function payment(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($request->query('tab') === 'CINP') if(!($reservation->status === 2)) abort(404);
        else if(!($reservation->status >= 1 && $reservation->status <= 2)) abort(404);
        return view('system.reservation.edit.payment', ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function updateDY(Request $request, $id){
            $system_user = $this->system_user->user();
            $reservation = Reservation::findOrFail(decrypt($id));
            if(!($reservation->status >= 1 && $reservation->status <= 2)) abort(404);
            $validated = Validator::make($request->all(), [
                'amount' => ['required', 'numeric'],
                'passcode' => ['required', 'numeric', 'digits:4'],
            ], [
                'passcode.required' => 'Required to fill up (Passcode)', 
                'amount.required' => 'Required to fill up (Downpayment)', 
                'amount.numeric' => 'Number only',
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
    public function updateCINP(Request $request, $id){
        // try{
            $system_user = $this->system_user->user();
            $reservation = Reservation::findOrFail(decrypt($id));
            if(!($reservation->status == 2)) abort(404);
            $haveSenior = $request->has('hs') && $request['hs'] == "on";
            $validated = Validator::make($request->all(), [
                'amount' => ['required', 'numeric'],
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
            // dd($validated);
            if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');

            $cinpayment = $reservation->transaction;

            if($haveSenior){
                $cinpayment['payment']['discountPerson'] = $validated['senior_count'];
                foreach($cinpayment ?? [] as $key => $item){
                    if (strpos($key, 'rid') !== false ) {
                        $rateID = (int)str_replace('rid','', $key);
                        $cinpayment['rid'.$rateID]['orig_amount'] = $item['person'] * $reservation->pax;
                        $discounted = $reservation->discounted($item['person']);
                        // dd($discounted);
                        $roomtotal = 0;
                        for($i = 1; $i <= $reservation->pax; $i++){
                            if($i <= (int)$validated['senior_count']) $roomtotal += $discounted;
                            else $roomtotal += (double)$item['person'];
                        }
                        $cinpayment['rid'.$rateID]['amount'] = $roomtotal;
                        $cinpayment['rid'.$rateID]['discounted'] = $discounted;

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
            $cinpayment['payment']['cinpay'] = (double)$validated['amount'];
            $reservation->transaction = $cinpayment;
            $reservation->save();

            // $updated = $reservation->update(['transaction' => $cinpayment]);

            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => "Edit Check-in Payment",
                'body' => "Your Check-in Payment was updated by " . $system_user->name() . " with  ₱" . number_format($validated['amount'], 2),
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));

            $this->employeeLogNotif('Fill the Payment of Force Payment for ' . $reservation->userReservation->name() . ' in ' . $validated['amount'] . ' pesos', route('system.reservation.show', $id));
            return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' downpayment was updated in amount of ₱ ' . number_format($validated['amount'], 2));
             
        // }
        // catch(Exception $e){
        //     return redirect()->route('system.reservation.home');
        // }
    }
}
