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
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CreateReservationController extends Controller
{
    private $system_user;
    public function __construct()
    {
        $this->system_user = auth()->guard('system');
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
        if($request->has('room_rate')) $request['room_rate'] = decrypt($request['room_rate']);
        $validated = $request->validate([
            'room_rate' => ['required', Rule::when($request->has('room_rate'), ['numeric'])],
            'pax' => ['required', 'numeric', 'min:1'],
        ], [
            'required' => 'Required'
        ]);
        
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($validated);
        else $validated['room_pax'] = $request['room_pax'];
        $rate = RoomRate::find($validated['room_rate']);
        $roomCustomer = [];
        // Room Update and Verification
        $reservationPax = 0;

        foreach($validated['room_pax'] as $room_id => $newPax){
            $room = Room::find($room_id);
            if($room->availability === true) return back()->with('error', 'Room No. ' . $room->room_no. ' is not available')->withInput($validated);
            if($newPax > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$newPax.' pax) and Room Capacity ('.$room->room->max_occupancy.' capacity)')->withInput($validated);
            if($newPax > $room->getVacantPax() && $reservationPax < $room->getVacantPax()) return back()->with('error', 'Room No. ' . $room->room_no. ' are only '.$room->getVacantPax().' pax to reserved and your guest ('.$reservationPax.' pax)')->withInput($validated);
            $reservationPax += (int)$newPax;
            $roomCustomer[$room_id] = $newPax;

        }
        if($reservationPax > $validated['pax'] || $reservationPax <  $validated['pax']) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$reservationPax.' pax) that already choose in previous room')->withInput($validated);
        
        $param = [
            'rt' => $rate->id,
            'rm' => $roomCustomer,
            'px' => $validated['pax'],
        ];
        $param = encryptedArray($param);
        if(session()->has('rinfo')) {
            $session = session('rinfo');
            $session['rt'] = $param['rt'] ;
            $session['rm'] = $param['rm'] ;
            $session['px'] = $param['px'] ;
            session(['rinfo' => $session]);
        }
        return redirect()->route('system.reservation.create.step.two', Arr::query($param));

    }
    public function step2(Request $request){
        if(session()->has('rinfo') && !empty(session('rinfo')['tm'])){
            $decryptedTm = decrypt(session('rinfo')['tm']);
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

        if(checkAvailRooms($request['pax'], $request['check_in'])){
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
                'pax' => ['required', 'numeric', 'min:1', 'max:'.(string)RoomList::max('max_occupancy')],
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
                'pax' => ['required', 'numeric', 'min:1', 'max:'.(string)RoomList::max('max_occupancy')],
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
              if(session()->has('rinfo')) {
                    $session = session('rinfo');
                    $session['st'] =  $encrypted['status'];  
                    $session['cin'] =  $encrypted['check_in'];  
                    $session['cout'] =  $encrypted['check_out'];  
                    $session['at'] =  $encrypted['accommodation_type'];  
                    $session['px'] =  $encrypted['pax'];  
                    $session['tpx'] =  $encrypted['tour_pax'];  
                    $session['py'] =  $encrypted['payment_method'];
                    session(['rinfo' => $session]);
            }
        }
        else{
            if(session()->has('rinfo')) {
                $session = session('rinfo');
                $session['st'] =  $encrypted['status'];  
                $session['cin'] =  $encrypted['check_in'];  
                $session['cout'] =  $encrypted['check_out'];  
                $session['at'] =  $encrypted['accommodation_type'];  
                $session['px'] =  $encrypted['pax'];  
                $session['py'] =  $encrypted['payment_method'];
                session(['rinfo' => $session]);
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
            session(['rinfo' => $session]);
            return redirect()->route('system.reservation.create.step.three');
        }

        return redirect()->route('system.reservation.create.step.two', [Arr::query($param), '#tourmenu']);
    }
    public function storeStep22(Request $request){
        if(empty($request['tour_menu'])) return back()->with('error', 'You have not selected anything in the cart yet. Please make a selection first.');

        if(session()->has('rinfo')){
            $session = session('rinfo');
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
        session(['rinfo' => $session]);
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
        $encrypted = session('rinfo');
        if($validated){
            $encrypted['qty'] = encrypt($validated['qty']);
        }
        session(['rinfo' => $encrypted]);
        return redirect()->route('system.reservation.create.step.four');

    }
    public function step4(Request $request){
        $decrypted = decryptedArray(session('rinfo'));
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
                $addons[$count]['pcs'] =  $addon->title;
                $addons[$count]['price'] =  '₱ ' . number_format($addon->price, 2);
                $addons[$count]['amount'] =  $addon->price * (int)$qty;
                $count++;
            }
        }
        foreach($decrypted['rm'] as $id => $pax){
            $room = Room::find($id);
            $rooms[] =  'Room No. ' .$room->room_no . ' ('.$pax.' guest assigned)';
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
        $sysUser = $this->system_user->user();
        $admins = System::where('type', 0);
        $validated = $request->validate([
            'first_name' => ['required', 'min:1'],
            'last_name' => ['required', 'min:1'],
            'age' => ['required', 'numeric','min:8'],
            'payment_amount' => ['required', 'numeric','min:1000'],
            'country' => ['required', 'min:1'],
            'nationality' => ['required'],
            'contact' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'email' => ['required', 'email', Rule::unique('user_offlines', 'email')],
            'valid_id' => ['image', 'mimes:jpeg,png,jpg', 'max:5024'], 
        ], [
            'required' => 'This input are required',
            'image' => 'The file must be an image of type: jpeg, png, jpg',
            'mimes' => 'The image must be of type: jpeg, png, jpg',
            'max' => 'The image size must not exceed 5 MB',
        ]);
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
            $decrypted = decryptedArray(session('rinfo'));

            if(isset($decrypted['tm'])){
                foreach($decrypted['tm'] as $key => $tour_id){
                    $tour_menu = TourMenu::find($tour_id);
                    $transaction['tm'. $tour_id] = [
                        'title' => $tour_menu->tourMenu->title . ' ' . $tour_menu->type,
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
            $text = 
            "Employee Action: Create Reservation !\n" .
            "Name: ". $reserved->userReservation->name() ."\n" . 
            "Age: " . $reserved->age ."\n" .  
            "Nationality: " . $reserved->userReservation->nationality  ."\n" . 
            "Country: " . $reserved->userReservation->country ."\n" . 
            "Check-in: " . Carbon::createFromFormat('Y-m-d', $reserved->check_in)->format('F j, Y') ."\n" . 
            "Check-out: " . Carbon::createFromFormat('Y-m-d', $reserved->check_out)->format('F j, Y') ."\n" . 
            "Type: " . $reserved->accommodation_type ."\n" . 
            "Rooms: " . implode(', ', $roomDetails) ."\n" . 
            "Who Approve: " . $sysUser->name();

            if($sysUser->role() === "Manager"){
                foreach($admins as $admin){
                    if(!empty($admin->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID'), $text, null, 'bot2');;
                }
            }
            $details = [
                'name' => $reserved->userReservation->name(),
                'title' => 'Reservation Complete',
                'body' => 'You Reservation at ' . Carbon::now()->format('F j, Y') .' and the Room Assign are '.implode(', ', $roomDetails).'. Be Enjoy our service with full happiness',
            ];
            Mail::to(env('SAMPLE_EMAIL', $reserved->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            session()->forget('rinfo');
            return redirect()->route('system.reservation.home')->with('success', $reserved->userReservation->name() . ' was added on Reservation');
    }
}
