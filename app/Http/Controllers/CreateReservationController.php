<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Addons;
use App\Models\System;
use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\Reservation;
use App\Models\UserOffline;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

class CreateReservationController extends Controller
{
    private $system_user;
    public function __construct()
    {
        $this->system_user = auth()->guard('system');
        if(Str::contains(URL::previous(), route('home')) || Str::contains(URL::previous(), route('system.home'))) session()->forget('nwrinfo');

    }
    private function reservationValidation(Request $request){
        // Check in (startDate to endDate) trim convertion
        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');

        $validator = null;
        if($request['accommodation_type'] == 'Day Tour'){
            $validator = Validator::make($request->all(), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['check_in'], 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'room_rate' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
                'status' => ['required', 'numeric'],
                'payment_method' => ['required'],
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
                'room_rate' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
                'status' => ['required', 'numeric'],
                'payment_method' => ['required'],
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
                'room_rate' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'status' => ['required', 'numeric'],
                'payment_method' => ['required'],
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
        if ($validator->fails()) {            
            session(['ck' => false]);
            return back()
            ->withErrors($validator)
            ->withInput($request->all());
        }
        $validated = $validator->validated();
        return $validated;
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
    public function create(){
        $rooms = Room::all() ?? [];
        $rates = RoomRate::all() ?? [];
        return view('system.reservation.create.step1',  [
            'activeSb' => 'Reservation', 
            'rooms' => $rooms, 
            'rates' => $rates, 
        ]);
    }
    public function storeStep1(Request $request){
        $validated = $this->reservationValidation($request);
        if(!is_array($validated)) return $validated;

        if($request->has('room_rate')) $validated['room_rate'] = decrypt($request['room_rate']);
    
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($validated);
        else $validated['room_pax'] = $request['room_pax'];
        $rate = RoomRate::find($validated['room_rate']);

        // dd($validated);
        $rpax = 0;
        // dd($validated['room_pax']);
        foreach($validated['room_pax'] as $pax){
            $rpax += $pax;
            if($rpax > $validated['pax']) return back()->with('error', 'Guest you choose ('.$rpax.' pax) for Rooms does not match on Customer Guest ('.$validated['pax'].' pax)')->withInput($request->all());
        }
        $param = [
            'rt' => $rate->id,
            'rm' => $validated['room_pax'],
            'px' => $validated['pax'],
            'cin' => $validated['check_in'],
            'cout' => $validated['check_out'],
            'at' => $validated['accommodation_type'],
            'st' => $validated['status'],
            'py' => $validated['payment_method'],
        ];
        if($validated['accommodation_type'] === 'Day Tour' || $validated['accommodation_type'] === 'Overnight') $param['tpx'] = $validated['tour_pax'];
        
        $param = encryptedArray($param);
        $session = [];
        $session['rt'] = $param['rt'] ;
        $session['rm'] = $param['rm'] ;
        $session['px'] = $param['px'] ;
        $session['cin'] = $param['cin'] ;
        $session['cout'] = $param['cout'] ;
        $session['at'] = $param['at'] ;
        $session['st'] = $param['st'] ;
        $session['py'] = $param['py'] ;
        if($validated['accommodation_type'] === 'Day Tour' || $validated['accommodation_type'] === 'Overnight') $session['tpx'] = $param['tpx'] ;
        if(session()->has('nwrinfo')) {
            session(['nwrinfo' => $session]);
        }
        if($validated['accommodation_type'] === 'Day Tour' || $validated['accommodation_type'] === 'Overnight') return redirect()->route('system.reservation.create.step.two', Arr::query($param));
        else{
            session(['nwrinfo' => $session]);
            return redirect()->route('system.reservation.create.step.three');
        }

    }
    public function step2(Request $request){
        if(session()->has('nwrinfo') && !empty(session('nwrinfo')['tm'])){
            $decryptedTm = decrypt(session('nwrinfo')['tm']);
            $cmenu = [];
            foreach($decryptedTm as $key => $item){
                $tour = TourMenu::find($item);
                $cmenu[$key]['id'] = $tour->id;
                $cmenu[$key]['title'] = $tour->tourMenu->title;
                $cmenu[$key]['type'] = $tour->type;
                $cmenu[$key]['pax'] = $tour->pax;
                $cmenu[$key]['price'] = $tour->price;
            }
            return view('system.reservation.create.step2',  [
                'activeSb' => 'Reservation', 
                'tour_lists' => TourMenuList::all() ?? [], 
                'tour_category' => TourMenuList::distinct()->get('category') ?? [], 
                'cmenu' => $cmenu, 
                'tour_menus' => TourMenu::all() ?? [], 
                "user_days" => $noOfday ?? 1,
            ]);
        }
        return view('system.reservation.create.step2',  [
            'activeSb' => 'Reservation', 
            'tour_lists' => TourMenuList::all() ?? [], 
            'tour_category' => TourMenuList::distinct()->get('category') ?? [], 
            'tour_menus' => TourMenu::all() ?? [], 
            "user_days" => $noOfday ?? 1,
        ]);
    }
    public function storeStep21(Request $request){
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

        if(checkAvailRooms($request['pax'], $request['check_in'], $request['check_out'])){
            return back()->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
        }
        $validated = null;
        if($request['accommodation_type'] == 'Day Tour'){
            $validated = $request->validate([
                'status' => ['required', 'numeric'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out']],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
                'pax' => ['required', 'numeric', 'min:1'],
                'payment_method' => ['required'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'tour_pax.max' => 'Sorry, You can choose who will going the tour based on your preference and the number of guests you have',
            ]);
        }
        elseif($request['accommodation_type'] == 'Overnight'){
            $validated = $request->validate([
                'status' => ['required', 'numeric'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(1)],
                'accommodation_type' => ['required'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
                'pax' => ['required', 'numeric', 'min:1'],
                'payment_method' => ['required'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'tour_pax.max' => 'Sorry, You can choose who will going the tour based on your preference and the number of guests you have',
                'check_out.after_or_equal' => 'Choose within 1 day and above (Overnight)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Room Only'){
            $validated = $request->validate([
                'status' => ['required', 'numeric'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date', 'date_format:Y-m-d'],
                'accommodation_type' => 'required',
                'pax' => ['required', 'numeric', 'min:1'],
                'payment_method' => ['required'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     

            ]);
        }
        else{
            return back()->withErrors(['accommodation_type' => 'Choose the Accommodation type'])->withInput();
        }
        $encrypted = encryptedArray($validated);
        if(!empty($validated['tour_pax']) &&  $validated['accommodation_type'] !== 'Room Only'){
            $param = [
                'rt' => $request['rt'],  
                'rm' => $request['rm'],  
                'st' => $encrypted['status'],  
                'cin' => $encrypted['check_in'],  
                'cout' => $encrypted['check_out'],  
                'at' => $encrypted['accommodation_type'],  
                'px' => $encrypted['pax'],  
                'tpx' => $encrypted['tour_pax'],  
                'py' => $encrypted['payment_method'],  
            ];
              if(session()->has('nwrinfo')) {
                    $session = session('nwrinfo');
                    $session['st'] =  $encrypted['status'];  
                    $session['cin'] =  $encrypted['check_in'];  
                    $session['cout'] =  $encrypted['check_out'];  
                    $session['at'] =  $encrypted['accommodation_type'];  
                    $session['px'] =  $encrypted['pax'];  
                    $session['tpx'] =  $encrypted['tour_pax'];  
                    $session['py'] =  $encrypted['payment_method'];
                    session(['nwrinfo' => $session]);
            }
        }
        else{
            if(session()->has('nwrinfo')) {
                $session = session('nwrinfo');
                $session['st'] =  $encrypted['status'];  
                $session['cin'] =  $encrypted['check_in'];  
                $session['cout'] =  $encrypted['check_out'];  
                $session['at'] =  $encrypted['accommodation_type'];  
                $session['px'] =  $encrypted['pax'];  
                $session['py'] =  $encrypted['payment_method'];
                session(['nwrinfo' => $session]);
            }
            $session = [
                "rt" => $request['rt'],
                "rm" => $request['rm'],
                'st' => $encrypted['status'],  
                'cin' => $encrypted['check_in'],  
                'cout' => $encrypted['check_out'],  
                'at' => $encrypted['accommodation_type'],  
                'px' => $encrypted['pax'],  
                'py' => $encrypted['payment_method'],  
              ];

            session(['nwrinfo' => $session]);
            return redirect()->route('system.reservation.create.step.three');
        }
        return redirect()->route('system.reservation.create.step.two', [Arr::query($param), '#tourmenu']);
    }
    public function storeStep22(Request $request){
        if(empty($request['tour_menu'])) return back()->with('error', 'You have not selected anything in the cart yet. Please make a selection first.');

        if(session()->has('nwrinfo')){
            $session = session('nwrinfo');
            $session['tm'] = encrypt($request['tour_menu']);
        }
        else{
            $session = [
                "rt" => $request['rt'],
                "rm" => $request['rm'],
                "cin" => $request['cin'],
                "cout" => $request['cout'],
                "px" => $request['px'],
                "tpx" => $request['tpx'],
                "at" => $request['at'],
                "py" => $request['py'],
                "st" => $request['st'],
                "tm" => encrypt($request['tour_menu']),
              ];
        }
        session(['nwrinfo' => $session]);
        return redirect()->route('system.reservation.create.step.three');
    }
    public function step3(Request $request){
        return view('system.reservation.create.step3', [
            'activeSb' => 'Reservation', 
            'add_ons' => Addons::all(), 
        ]);
    }
    public function storeStep3(Request $request){
        $validated = $request->validate([
            'qty.*' => Rule::when(!empty($request['qty']), ['required', 'numeric']),
        ]);
        $encrypted = session('nwrinfo');
        if($validated){
            $encrypted['qty'] = encrypt($validated['qty']);
        }
        session(['nwrinfo' => $encrypted]);
        return redirect()->route('system.reservation.create.step.four');

    }
    public function step4(Request $request){
        $decrypted = decryptedArray(session('nwrinfo'));
        $tour_menus = [];
        $addons = [];
        $rooms = [];
        if(isset($decrypted['tm'])){
            foreach($decrypted['tm'] as $key => $id){
                $tour = TourMenu::find($id);
                $tour_menus[$key]['title'] =  $tour->tourMenu->title . ' ' . $tour->type;
                $tour_menus[$key]['price'] =  '₱ ' . number_format($tour->price, 2);
                $tour_menus[$key]['amount'] =  $tour->price * (int)$decrypted['tpx'];
            }
        }
        $count = 0;
        if(isset($decrypted['qty'])){
            foreach($decrypted['qty'] as $id => $qty){
                $addon = Addons::find($id);
                $addons[$count]['title'] =  $addon->title;
                $addons[$count]['pcs'] =  (int)$qty;;
                $addons[$count]['price'] =  '₱ ' . number_format($addon->price, 2);
                $addons[$count]['amount'] =  $addon->price * (int)$qty;
                $count++;
            }
        }
        foreach($decrypted['rm'] as $id => $pax){
            $room = Room::find($id);
            if($room) $rooms[] =  'Room No. ' .$room->room_no . ' ('.$pax.' guest assigned)';
            else $rooms[] =  'Room Data Missing';
        }
        $rate = RoomRate::find($decrypted['rt']);
        $rooms = implode(', ', $rooms);
        unset($count);
        return view('system.reservation.create.step4',  [
            'activeSb' => 'Reservation', 
            'tour_menus' => $tour_menus, 
            'addons' => $addons, 
            'rooms' => $rooms, 
            'rate' => $rate, 
            'other_info' => $decrypted, 
            "user_days" => getNoDays($decrypted['cin'], $decrypted['cout']) ?? 1,
        ]);
    }
    public function storeStep4(Request $request){
        $validated = $request->validate([
            'first_name' => ['required', 'min:1'],
            'last_name' => ['required', 'min:1'],
            'age' => ['required', 'numeric','min:8'],
            'payment_amount' => ['required', 'numeric','min:1000'],
            'country' => ['required', 'min:1'],
            'type' => ['required'],
            'nationality' => ['required'],
            'contact_code' => ['required'],
            'contact' => ['required', (new Phone)->international()->country(Str::upper($request['contact_code']))],
            'email' => ['required', 'email', Rule::unique('user_offlines', 'email')],
            'valid_id' => ['image', 'mimes:jpeg,png,jpg', 'max:5024'], 
        ], [
            'required' => 'This input are required',
            'image' => 'The file must be an image of type: jpeg, png, jpg',
            'mimes' => 'The image must be of type: jpeg, png, jpg',
            'max' => 'The image size must not exceed 5 MB',
            'payment_amount.min' => 'The amount must be ₱ 1,000 above',
        ]);
        $phone = new PhoneNumber($validated['contact'], Str::upper($validated['contact_code']));
        $validated['contact'] = $phone->formatInternational(); 

        if($request->hasFile('valid_id')){  
            $validated['valid_id'] = saveImageWithJPG($request, 'valid_id', 'valid_id', 'private');
        }
        $created = UserOffline::create([
            "first_name"  => $validated['first_name'],
            "last_name" => $validated['last_name'],
            "age" => $validated['age'],
            "country"  => $validated['country'],
            "nationality" => $validated['nationality'],
            "email" => $validated['email'],
            "contact" => $validated['contact'],
            "valid_id" => $validated['valid_id'] ?? null,
        ]);

        if($created){
            $transaction = [];
            $roomDetails = [];
            $decrypted = decryptedArray(session('nwrinfo'));

            if(isset($decrypted['tm'])){
                foreach($decrypted['tm'] as $key => $tour_id){
                    $tour_menu = TourMenu::find($tour_id);
                    $transaction['tm'. $tour_id] = [
                        'title' => $tour_menu->tourMenu->title . ' ' . $tour_menu->type . '('.$tour_menu->pax.' pax)',
                        'price' => (double)$tour_menu->price,
                        'amount' => (double)$tour_menu->price * (int)$decrypted['tpx']
                    ];
                }
            }
            if(isset($decrypted['qty'])){
                foreach($decrypted['qty'] as $key => $qty){
                    $addons = Addons::find($key);
                    $transaction['OA'. $addons->id] = [
                        'title' => $addons->title,
                        'price' => (double)$addons->price,
                        'pcs' => (int)$qty,
                        'amount' => (double)$addons->price * (int)$qty
                    ];
                }
            }
            if(isset($decrypted['rt'])){
                $rate = RoomRate::find($decrypted['rt']);
                $transaction['rid'. $rate->id] = [
                    'title' => $rate->name,
                    'price' => (double)$rate->price,
                    'amount' => (double)$rate->price * (int)$decrypted['px']
                ];
            }

            if($validated['type'] == 'cinpayment') $transaction['payment']['cinpay'] = $validated['payment_amount'];
            elseif($validated['type'] == 'coutpayment') $transaction['payment']['coutnpay'] = $validated['payment_amount'];
            else $transaction['payment']['downpayment'] = $validated['payment_amount'];

            $reserved = Reservation::create([
                'offline_user_id' => $created->id,
                'roomid' => array_keys($decrypted['rm']),
                'roomrateid' => $decrypted['rt'],
                'pax' => $decrypted['px'],
                'tour_pax' => $decrypted['tpx'] ?? null,
                'age' => $created->age,
                'accommodation_type' => $decrypted['at'],
                'payment_method' => $decrypted['py'],
                'check_in' => $decrypted['cin'],
                'check_out' => $decrypted['cout'],
                'status' => $decrypted['st'],
                'transaction' => $transaction,
            ]);
            if(isset($decrypted['rm'])){
                foreach($decrypted['rm'] as $id => $pax){
                    $room = Room::find($id);
                    $room->addCustomer($reserved->id, $pax);
                    $roomDetails[] = 'Room No. ' . $room->room_no . ' ('.$room->room->name.')';
                }
            }
        }


        $this->employeeLogNotif('Add Booking for ' . $reserved->userReservation->name(), route('system.reservation.show', encrypt($reserved->id)));
        $details = [
            'name' => $reserved->userReservation->name(),
            'title' => 'Reservation Complete',
            'body' => 'You Reservation at ' . Carbon::now()->format('F j, Y') .' and the Room Assign are '.implode(', ', $roomDetails).'. Be Enjoy our service with full happiness',
        ];
        Mail::to(env('SAMPLE_EMAIL') ?? $reserved->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
        session()->forget('nwrinfo');
        return redirect()->route('system.reservation.home')->with('success', $reserved->userReservation->name() . ' was added on Reservation');
    }
}
