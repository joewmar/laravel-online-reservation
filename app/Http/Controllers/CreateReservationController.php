<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Addons;
use App\Models\System;
use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\AuditTrail;
use App\Models\Reservation;
use App\Models\UserOffline;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Illuminate\Validation\Rule;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Notification;

class CreateReservationController extends Controller
{
    private $system_user;
    public function __construct()
    {
        $this->system_user = auth()->guard('system');
    }
    private function deleteValidID($array){
        if(isset($array['vid'])){
            Storage::delete('private/'.decrypt($array['vid']));
            unset($array['vid']);
        }
    }
    private function reservationValidation(Request $request){
        // Check in (startDate to endDate) trim convertion
        if($request->has('accommodation_type') && $request['accommodation_type'] == 'Day Tour') $request['check_out'] = $request['check_in'];
        $request['check_in'] = Carbon::parse($request['check_in'])->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->setTimezone('Asia/Manila')->format('Y-m-d');

        $validator = null;
        if($request['accommodation_type'] == 'Day Tour'){
            $validator = Validator::make($request->all(), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d'],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['check_in'], 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
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
                'pax' => ['required', 'numeric', 'min:1'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
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
                'pax' => ['required', 'numeric', 'min:1'],
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
                if(!empty($admin->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID') ?? $admin->telegram_chatID, $text, $keyboard, 'bot2'));

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
    public function create(Request $request){
        $roomInfo = [
            'at' =>    old('accommodation_type'),
            'cin' =>    old('check_in'),
            'cout' =>   old('check_out'),
            'px' =>   old('pax'),
            'tpx' =>  old('tour_pax'),
            'py' =>  old('payment_method'),
        ];
        if(session()->has('nwrinfo')){
            $roomInfo = $request->session()->get('nwrinfo');
            $roomInfo = [
                'at' => isset($roomInfo['at']) ? decrypt($roomInfo['at']) : old('accommodation_type'),
                'cin' => isset($roomInfo['cin']) ? decrypt($roomInfo['cin']) : old('check_in'),
                'cout' => isset($roomInfo['cout']) ? decrypt($roomInfo['cout']) : old('check_out'),
                'px' => isset($roomInfo['px']) ? decrypt($roomInfo['px']) : old('pax'),
                'tpx' => isset($roomInfo['tpx']) ? decrypt($roomInfo['tpx']) : old('tour_pax'),
                'py' => isset($roomInfo['py']) ? decrypt($roomInfo['py']) : old('payment_method'),
            ];
        }
        return view('system.reservation.create.step0',  [
            'activeSb' => 'Reservation', 
            'roomInfo' => $roomInfo, 
        ]);
    }
    public function storeStep0(Request $request){
        if($request->has('accommodation_type') && $request['accommodation_type'] === 'Day Tour') $request['check_out'] = $request['check_in'];
        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        // dd($request->all());
        $validated = $this->reservationValidation($request);
        if(!is_array($validated)) return $validated; 
        $param = [
            'cin' => $validated['check_in'],
            'cout' => $validated['check_out'],
            'at' => $validated['accommodation_type'],
            'px' => $validated['pax'],
            'py' => $validated['payment_method'],
        ];
        if($validated['accommodation_type'] != 'Room Only') $param['tpx'] = $validated['tour_pax'] ?? null;
        $param = encryptedArray($param);

        $session = session('nwrinfo');
        $session['cin'] = $param['cin'] ;
        $session['cout'] = $param['cout'] ;
        $session['px'] = $param['px'] ;
        $session['py'] = $param['py'] ;
        $session['at'] = $param['at'] ;
        if($validated['accommodation_type'] != 'Room Only') $session['tpx'] = $param['tpx'] ?? null;
        else unset($session['tpx']);

        session(['nwrinfo' => $session]);

        return redirect()->route('system.reservation.create.step.one');

    }
    public function step1(Request $request){
        $rooms = Room::all() ?? [];
        $rates = RoomRate::all() ?? [];
        if(!session()->has('nwrinfo')) return redirect()->route('system.reservation.create');
        $params = decryptedArray(session('nwrinfo'));
        $roomReserved = [];
        if(!isset($param['rm']))$params['rm'] = [];
        if(!isset($param['rt']))$params['rt'] = '';
        $r_lists = Reservation::where(function ($query) use ($params) {
            $query->whereBetween('check_in', [$params['cin'], $params['cout']])
                  ->orWhereBetween('check_out', [$params['cin'], $params['cout']])
                  ->orWhere(function ($query) use ($params) {
                      $query->where('check_in', '<=', $params['cin'])
                            ->where('check_out', '>=', $params['cout']);
                  });
        })->whereBetween('status', [1, 2])->pluck('id');
        
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
        if(auth('system')->user()->type == 2){
            $roomInfo['cin'] = request('cin') ? decrypt(request('cin')) : (old('check_in') ?? Carbon::now('Asia/Manila')->format('Y-m-d'));
        }
        if(session()->has('nwrinfo')){
            $ri = $request->session()->get('nwrinfo');
            $roomInfo = [
                'at' => isset($ri['at']) ? decrypt($ri['at']) : old('accommodation_type'),
                'px' => isset($ri['px']) ? decrypt($ri['px']) : old('pax'),
                'rm' => isset($ri['rm']) ? decrypt($ri['rm']) : old('room_pax'),
                'rt' => isset($ri['rt']) ? decrypt($ri['rt']) : old('room_rate'),
                'cin' => isset($ri['cin']) ? decrypt($ri['cin']) : old('check_in'),
                'cout' => isset($ri['cout']) ? decrypt($ri['cout']) : old('check_out'),
                'py' => isset($ri['py']) ? decrypt($ri['py']) : old('payment_method'),
                'tpx' => isset($ri['tpx']) ? decrypt($ri['tpx']) : old('tour_pax'),
                'st' => isset($ri['st']) ? decrypt($ri['st']) : old('status'),
            ];
        }
        return view('system.reservation.create.step1',  [
            'activeSb' => 'Reservation', 
            'rooms' => $rooms, 
            'rates' => $rates, 
            'reserved' => $roomReserved, 
            'roomInfo' => $params , 
        ]);
    }
    public function storeStep1(Request $request){
        if(!session()->has('nwrinfo')) return redirect()->route('system.reservation.create');
   
        $param = decryptedArray(session('nwrinfo'));
        if($request->has('room_rate')) $validated['room_rate'] = decrypt($request['room_rate']);

        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($validated);
        else $validated['room_pax'] = $request['room_pax'];
        $rate = RoomRate::findOrFail($validated['room_rate']);

        // dd($validated);
        $rpax = 0;
        // dd($validated['room_pax']);
        foreach($validated['room_pax'] as $pax){
            $rpax += $pax;
            if($rpax > $param['px']) return back()->with('error', 'Guest you choose ('.$rpax.' pax) for Rooms does not match on Customer Guest ('.$param['px'].' pax)')->withInput($request->all());
        }
        $param['rt'] = $rate->id ;
        $param['rm'] = $validated['room_pax'];
        if($param['at'] === 'Room Only') unset($param['tm']);
        session(['nwrinfo' => encryptedArray($param)]);
        if($param['at'] === 'Day Tour' || $param['at'] === 'Overnight')  return redirect()->route('system.reservation.create.step.two');

        else return redirect()->route('system.reservation.create.step.three');
        
    }
    public function step2(Request $request){
        // dd($request->query());
        if(session()->has('nwrinfo') && !empty(session('nwrinfo')['tm'])){
            $decryptedTm = decrypt(session('nwrinfo')['tm']);
            $cmenu = [];
            foreach($decryptedTm as $key => $item){
                $tour = TourMenu::withTrashed()->find($item);
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
    public function storeStep22(Request $request){
        if(!session()->has('nwrinfo')) return redirect()->route('system.reservation.create');
        if(empty($request['tour_menu'])) return back()->with('error', 'You have not selected anything in the cart yet. Please make a selection first.');
        $session = session('nwrinfo');
        $session['tm'] = encrypt($request['tour_menu']);

        session(['nwrinfo' => $session]);
        return redirect()->route('system.reservation.create.step.three');
    }
    public function step3(Request $request){
        if(!session()->has('nwrinfo')) return redirect()->route('system.reservation.create');
        $info = decryptedArray(session('nwrinfo'));
        // dd($info);
        return view('system.reservation.create.step3', [
            'activeSb' => 'Reservation', 
            'add_ons' => Addons::all(), 
            'info' =>$info , 
        ]);
    }
    public function storeStep3(Request $request){
        if(!session()->has('nwrinfo')) return redirect()->route('system.reservation.create');
        $validated = $request->validate([
            'qty.*' => Rule::when(!empty($request['qty']), ['required', 'numeric'], ['nullable']),
        ]);
        $encrypted = session('nwrinfo');
        if(isset($validated['qty']) && !empty($validated['qty'])){
            $encrypted = session('nwrinfo');
            $encrypted['qty'] = encrypt($validated['qty']);
            session(['nwrinfo' => $encrypted]);
        }
        if(isset($encrypted['uid'])) return redirect()->route('system.reservation.create.step.four', Arr::query(['uof' => $encrypted['uid']]));
        else return redirect()->route('system.reservation.create.step.four');

    }
    public function step4(Request $request){
        if(!session()->has('nwrinfo')) return to_route('system.reservation.create');
        $user = [];
        if($request->has('uof')){
            $user = UserOffline::find(decrypt($request->query('uof'))) ?? [];
        }
        $info = decryptedArray($request->session()->get('nwrinfo'));

        return view('system.reservation.create.step4',  [
            'activeSb' => 'Reservation', 
            'user' => $user,  
            'info' => $info,  

        ]);
    }
    public function step4Search(Request $request){
        $search = $request->input('query');
        $names = [];
        if($search){
            $results = UserOffline::where(function ($query) use ($search) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            })->get();
            foreach($results as $list){
                $names[] = [
                    'title' => $list->name(),
                    'link' => route('system.reservation.create.step.four', Arr::query(['uof' => encrypt($list->id)])),
                ];
            }
        }
        return response()->json($names);
    }
    public function storeStep4(Request $request){
        if(!session()->has('nwrinfo')) return to_route('system.reservation.create');
        $validated = Validator::make($request->all(),[
            'uid' => Rule::when(!($request->has('ncus') && $request['ncus'] == 'on'), ['required']), 
            'first_name' => ['required', 'min:1'],
            'last_name' => ['required', 'min:1'],
            'birthday' => ['required', 'date'],
            'country' => ['required', 'min:1'],
            'nationality' => ['required'],
            'contact' => ['nullable'],
            'email' => Rule::when($request->has('ncus') && $request['ncus'] == 'on',['email', Rule::unique('user_offlines', 'email')]),
            'valid_id_clear' => ['required'], 
            'valid_id' => Rule::when($request['valid_id_clear'] == true, ['image', 'mimes:jpeg,png,jpg', 'max:5024'], ['nullable']), 
        ], [
            'required' => 'This input are required (:attribute)',
            'image' => 'The file must be an image of type: jpeg, png, jpg',
            'mimes' => 'The image must be of type: jpeg, png, jpg',
            'max' => 'The image size must not exceed 5 MB',
        ]);
        if($validated->fails()) return back()->with('error', $validated->errors()->all())->withInput($request->all());
        $validated = $validated->validate();
        // dd($validated);
        $info = session('nwrinfo');
        $info['fn'] = encrypt($validated['first_name']);
        $info['ln'] = encrypt($validated['last_name']);
        $info['bday'] = encrypt($validated['birthday']);
        $info['ctct'] = encrypt($validated['contact']);
        $info['eml'] = encrypt($validated['email']);
        $info['ntnlt'] = encrypt($validated['nationality']);
        $info['ctry'] = encrypt($validated['country']);
        if(!($request->has('ncus') && $request['ncus'] == 'on') && isset($validated['uid'])) {
            $info['uid'] =  $validated['uid'];
        }
        else{
            if(isset($info['uid'])) unset($info['uid']);
        }
        if($validated['valid_id_clear'] == true) {
            $this->deleteValidID($info);
        }
        if($request->hasFile('valid_id')){  
            $image = $request->file('valid_id');
            $imageName = Str::random(4). now()->format('YmdHis') . '.jpg';
            $encodedImage = Image::make($image)->encode('jpg', 65); // Encoding the image to JPG format with 65% quality
            $destinationPath = 'temp/' . $imageName;
            Storage::put('private/'.$destinationPath, (string) $encodedImage, 'private');
            $info['vid'] = encrypt($destinationPath);
        }
        session(['nwrinfo' => $info]);
        return redirect()->route('system.reservation.create.step.five');
    }
    public function step5(Request $request){
        if(!session()->has('nwrinfo')) return to_route('system.reservation.create');
        $decrypted = decryptedArray(session('nwrinfo'));
        // dd($decrypted);
        if(!session()->has('nwrinfo')) return to_route('system.reservation.create');
        if(isset($decrypted['uid']) && !isset($decrypted['vid'])){
            $decrypted['vid'] = UserOffline::find($decrypted['uid'])->valid_id ?? '';
        }
        $tour_menus = [];
        $addons = [];
        $rooms = [];
        $noDays = getNoDays($decrypted['cin'], $decrypted['cout']) ?? 1;
        if(isset($decrypted['tm'])){
            foreach($decrypted['tm'] ?? [] as $key => $id){
                $tour = TourMenu::withTrashed()->find($id);
                $tour_menus[$key]['title'] =  $tour->tourMenu->title . ' ' . $tour->type;
                $tour_menus[$key]['price'] =  '₱ ' . number_format($tour->price, 2);
                $tour_menus[$key]['amount'] =  $tour->price * (int)$decrypted['tpx'];
            }
        }
        $count = 0;
        if(isset($decrypted['qty'])){
            foreach($decrypted['qty'] ?? [] as $id => $qty){
                $addon = Addons::find($id);
                $addons[$count]['title'] =  $addon->title;
                $addons[$count]['pcs'] =  (int)$qty;;
                $addons[$count]['price'] =  '₱ ' . number_format($addon->price, 2);
                $addons[$count]['amount'] =  $addon->price * (int)$qty;
                $count++;
            }
        }
        if(isset($decrypted['rm'])){
            foreach($decrypted['rm'] ?? [] as $id => $pax){
                $room = Room::find($id);
                if($room) $rooms[] =  'Room No. ' .$room->room_no . ' ('.$pax.' guest assigned)';
                else $rooms[] =  'Room Data Missing';
            }
            $rooms = implode(', ', $rooms);    
        }
        $roomTotal = 0;
        if(array_key_exists('rt', $decrypted)){
            $rate = RoomRate::find($decrypted['rt']);
            $ratePrice = $rate->price;
            $ratePerson = (double)$ratePrice * $noDays;
            $roomTotal += $ratePerson * $decrypted['px'];
        }

        unset($count);
        return view('system.reservation.create.step5',  [
            'activeSb' => 'Reservation', 
            'tour_menus' => $tour_menus, 
            'addons' => $addons, 
            'rooms' => $rooms ?? [], 
            'other_info' => $decrypted ?? [], 
            "user_days" => $noDays,
            'rate' => $rate,  
            'ratePerson' => $ratePerson, 
            'roomTotal' => $roomTotal,
        ]);
    }
    public function storeStep5(Request $request){
        if(!session()->has('nwrinfo')) return redirect()->route('system.reservation.create');
        $decrypted = decryptedArray($request->session()->get('nwrinfo'));
        // dd($decrypted);
        // $pathValidID = $decrypted['vid'] ?? null;
        if(isset($decrypted['vid'])){
            $imageData = Storage::get('private/'.$decrypted['vid']);
            $newPath = 'valid_id/'.Str::random(4). now()->format('YmdHis') .'.jpg';
            Storage::put('private/'.$newPath, $imageData);
            $decrypted['vid'] = $newPath;
        }
        $ui = [ 
            "first_name"  => $decrypted['fn'],
            "last_name" => $decrypted['ln'],
            "birthday" => $decrypted['bday'],
            "country"  => $decrypted['ctry'],
            "nationality" => $decrypted['ntnlt'],
            "email" => $decrypted['eml'],
            "contact" => $decrypted['ctct'],
            "valid_id" => $decrypted['vid'] ?? null,
        ];

        if(isset($decrypted['uid'])){
            $created = UserOffline::find($decrypted['uid']) ?? [];
            $created->update($ui);
        }
        else $created = UserOffline::create($ui);
        
        if($created){
            $transaction = [];
            $roomDetails = [];
            $totalAll = 0;
            if(isset($decrypted['tm'])){
                foreach($decrypted['tm'] as $key => $tour_id){
                    $tour_menu = TourMenu::withTrashed()->find($tour_id);
                    $transaction['tm'. $tour_id] = [
                        'tpx' => (int)$decrypted['tpx'],
                        'price' => (double)$tour_menu->price,
                        'title' => $tour_menu->tourMenu->title,
                        'type' => $tour_menu->type,
                        'pax' => $tour_menu->pax,
                        'used' => false,
                        'created' => now('Asia/Manila')->format('YmdHis'),
                        'amount' => (double)$tour_menu->price * (int)$decrypted['tpx'],
                    ];
                    $totalAll += (double)$tour_menu->price * (int)$decrypted['tpx'];
                }
            }
            if(isset($decrypted['qty'])){
                foreach($decrypted['qty'] as $key => $qty){
                    $addons = Addons::find($key);
                    $transaction['OA'. $addons->id][now('Asia/Manila')->format('YmdHis')] = [
                        'title' => $addons->title,
                        'price' => (double)$addons->price,
                        'pcs' => (int)$qty,
                        'amount' => (double)$addons->price * (int)$qty,
                    ];
                    $totalAll += (double)$addons->price * (int)$qty;
                }
            }
            $noDays = getNoDays($decrypted['cin'], $decrypted['cout']);
            if(isset($decrypted['rt'])){
                $rate = RoomRate::find($decrypted['rt']);
                $person = (double)$rate->price * $noDays;
                $amount = $person * $decrypted['px'];
                $transaction['rid'. $rate->id] = [
                    'title' => $rate->name,
                    'price' => (double)$rate->price,
                    'person' => $person,
                    'amount' => $amount,
                ];
            }

            $reserved = Reservation::create([
                'offline_user_id' => $created->id,
                'roomid' => isset($decrypted['rm']) ?array_keys($decrypted['rm']) : null,
                'roomrateid' => isset($decrypted['rt']) ? $decrypted['rt'] : null,
                'pax' => $decrypted['px'],
                'tour_pax' => $decrypted['tpx'] ?? null,
                'accommodation_type' => $decrypted['at'],
                'payment_method' => $decrypted['py'],
                'check_in' => $decrypted['cin'],
                'check_out' => $decrypted['cout'],
                'status' => 1,
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

        $details = [
            'name' => $reserved->userReservation->name(),
            'title' => 'Reservation Complete',
            'body' => 'You Reservation at ' . Carbon::now()->format('F j, Y') .' (UTC) and the Room Assign are '.implode(', ', $roomDetails).'. Be Enjoy our service with full happiness',
        ];
        // Storage::delete('private/'.$pathValidID);
        Mail::to(env('SAMPLE_EMAIL') ?? $reserved->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
        session()->forget('nwrinfo');
        $this->employeeLogNotif('Add Booking for ' . $reserved->userReservation->name(), route('system.reservation.show', encrypt($reserved->id)));
        return redirect()->route('system.reservation.home')->with('success', $reserved->userReservation->name() . ' was added on Reservation');
    }
}
