<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\System;
use App\Models\TourMenu;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Illuminate\Validation\Rule;
use App\Notifications\UserNotif;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Notification;
use AmrShawky\LaravelCurrency\Facade\Currency;

class ReservationController extends Controller
{
    private $customer;
    // private $ruleRoomOnly = []
    public function __construct()
    {
        $this->customer = auth('web');
        $this->middleware(function ($request, $next) {
            // dd($this->user->user()->id);
            $existingReservation = Reservation::where('user_id', $this->customer->user()->id)->where('status', '<', 3)->first();

            if ($existingReservation) {
                session(['ck' => false]);
                return redirect()->route('home')->with('error', "Sorry, you still have pending your reservation");
            }

            return $next($request);
        })->except(['date', 'dateCheck', 'dateStore', 'done', 'storeMessage']); // You can specify the specific method where this middleware should be applied.


    }
    private function replaceRInfo(array $values, bool $isEncrypt = false){
        if(session()->has('rinfo')){
            $rinfo = session('rinfo');
            $v = $values;
            if(isset($v['tpx']) && $v['tpx'] != $rinfo['tpx']) $v['otpx'] = $rinfo['tpx'];
            if($isEncrypt) $v = encryptedArray($values);
            foreach($v  as $key => $value){
                $rinfo[$key] = $value;
            }
            session(['rinfo' => $rinfo]);
            return true;
        }
        else return false;
    }
    private function systemNotification($text, $link = null){
        $systems = System::whereBetween('type', [0, 1])->get();
        $keyboard = null;
        if(isset($link)){
            $keyboard = [
                [
                    ['text' => 'View', 'url' => $link],
                ],
            ];
        }
        foreach($systems as $system){
            if(isset($system->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $system->telegram_chatID), $text, $keyboard));
        }
        Notification::send($systems, new SystemNotification(Str::limit($text, 10), $text, route('system.notifications')));
    }
    private function reservationValidation(Request $request, string $view = null, bool $haveTpx = false, $havePy = false){
        if($request->has('accommodation_type') && $request['accommodation_type'] === 'Day Tour') $request['check_out'] = $request['check_in'];

        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');

        if(checkAvailRooms($request['pax'] ?? 0, $request['check_in'], $request['check_out']) && !empty($request['pax'])) {
            if(isset($view)) return redirect()->route($view)->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
            else return back()->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
        }
        $web_contents = WebContent::all()->first();
        if(isset($web_contents->from) && isset($web_contents->to)){
            if(Carbon::createFromFormat('Y-m-d', $request['check_in'])->timestamp >= Carbon::createFromFormat('Y-m-d', $web_contents->from)->timestamp && Carbon::createFromFormat('Y-m-d', $request['check_in'])->timestamp <= Carbon::createFromFormat('Y-m-d', $web_contents->to)->timestamp) {
                if(isset($view)) return redirect()->route($view)->with('error', 'Sorry, this date cannot be allowed due ' . $web_contents->reason)->withInput($request->input());
                else return back()->with('error', 'Sorry, this date cannot be allowed due ' . $web_contents->reason)->withInput($request->input());
            }
        }

        $validator = Validator::make($request->all('accommodation_type'), [
            'accommodation_type' => ['required'],
        ], [
            'required' => 'Need fill up first',
        ]);
        $dayTour = [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after_or_equal:'.Carbon::now()->addDays(1)],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['check_in'], 'date_equals:'.$request['check_in']],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric', 'min:1'],
        ];
        $overnight = [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)->format('Y-m-d')],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric', 'min:1'],
        ];
        $roomOnly = [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['check_in']],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric', 'min:1'],
        ];
        if($havePy){
            $dayTour['payment_method'] =  ['required'];
            $roomOnly['payment_method'] =  ['required'];
            $overnight['payment_method'] =  ['required'];
        }
        if($haveTpx){
            $dayTour['tour_pax'] = ['required', 'numeric', 'min:1', 'max:'.$request['pax']];
            $overnight['tour_pax'] = ['required', 'numeric', 'min:1', 'max:'.$request['pax']];
        }
        // dd($dayTour);
        if($request['accommodation_type'] == 'Day Tour'){
            $validator = Validator::make($request->all(), $dayTour, [
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Overnight'){
            $validator = Validator::make($request->all(),  $overnight, [
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'check_out.after_or_equal' => 'Choose within 2 or 3 days (Overnight)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Room Only'){
            $validator = Validator::make($request->all(), $roomOnly, [
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
        }
        else{
            session(['ck' => false]);
            if(isset($view)) return redirect()->route($view)->withErrors(['accommodation_type' => 'Choose the Accommodation type'])->withInput($validator->getData());
            return back()->withErrors(['accommodation_type' => 'Choose the Accommodation type'])->withInput($validator->getData());
        }
        if ($validator->fails()) {            
            session(['ck' => false]);
            if(isset($view)) {
                return redirect()->route($view)
                ->withErrors($validator)
                ->withInput($validator->getData());
            }
            else{
                return back()
                ->withErrors($validator)
                ->withInput($validator->getData());
            }

        }

        $validator = $validator->validated();
        return $validator;
    }
    public function date(Request $request){
        return view('reservation.step1');
    }
    public function dateCheck(Request $request){
        session()->forget('ck');

        $validator = $this->reservationValidation($request, 'reservation.date');
        if(!is_array($validator)) return $validator;
        if($validator){
            session(['ck' => true]);
            return redirect()->route('reservation.date')->with('success',"Your choose date is now available, Let s proceed if you will make reservation")->withInput($validator);
        }
    }
    // Date Store
    public function dateStore(Request $request){
        $validated = $this->reservationValidation($request);
        if(!is_array($validated)) return $validated;
        $paramsDates = array(
            "cin" =>  $validated['check_in'] === '' ? null : encrypt($validated['check_in']),
            "cout" =>  $validated['check_out'] === '' ? null : encrypt($validated['check_out']),
            "at" =>  $validated['accommodation_type'] === '' ? null : encrypt($validated['accommodation_type']),
            "px" =>  $validated['pax'] === '' ? null : encrypt($validated['pax']),
        );
        $this->replaceRInfo($paramsDates);

        unset($validated);
        if(session()->has('rinfo')){
            $paramsDates = array(
                "cin" =>  session('rinfo')['cin'] ?? '',
                "cout" =>   session('rinfo')['cout'] ?? '',
                "at" =>   session('rinfo')['at'] ?? '',
                "px" =>  session('rinfo')['px'] ?? '',
            );            
        }

        return redirect()->route('reservation.choose', Arr::query($paramsDates));

    }
    // Choose Form
    public function choose(Request $request){
        $noOfday = 100;
        if($request->has('cin') && $request->has('cout')){
            $noOfday = getNoDays(decrypt($request->query('cin')), decrypt($request->query('cout')));
        }
        $TourInfo = [
            "cin" => request()->has('cin') ? decrypt(request('cin')) : old('check_in'),
            "cout" => request()->has('cout') ?  decrypt(request('cout')) : old('check_out'),
            "px" => request()->has('px') ? decrypt(request('px')) : old('pax'),
            "tpx" => request()->has('tpx') ? decrypt(request('tpx')) : old('tour_pax'),
            "otpx" => 0,
            "at" => request()->has('at') ? decrypt(request('at')) : old('accommodation_type'),
            "py" => request()->has('py') ? decrypt(request('py')) : old('payment_method'),
            "tamount" => 0,
        ];
        $tourListCart = [];
        if(session()->has('rinfo')){
          $decrypted = decryptedArray(session('rinfo'));
          $noOfday = getNoDays($decrypted['cin'], $decrypted['cout']);

          $TourInfo = [
            "cin" => old('check_in') ?? $decrypted['cin'],
            "cout" => old('check_out') ?? $decrypted['cout'],
            "px" => old('pax') ?? $decrypted['px'],
            "tpx" => old('tour_pax') ?? (request()->has('tpx') ? decrypt(request('tpx')) : $decrypted['tpx']  ),
            "at" => old('accommodation_type') ?? $decrypted['at'],
            "py" =>  old('payment_method') ?? $decrypted['py'],
            "otpx" =>  isset($decrypted['otpx']) ? $decrypted['otpx'] : $TourInfo['tpx'],
            "ck" => request('ck') ?? '',
            "tamount" => 0,

          ];
          if(isset($decrypted['tm'])) {
            foreach($decrypted['tm'] as $key => $item){
                $tour = TourMenu::withTrashed()->findOrFail($item);
                $tourListCart[$key]['id'] = $tour->id;
                $tourListCart[$key]['price'] = $tour->price;
                $tourListCart[$key]['title'] = $tour->tourMenu->title;
                $tourListCart[$key]['type'] = $tour->type;
                $tourListCart[$key]['pax'] = $tour->pax;
                $TourInfo['tamount'] += $tour->price * (int)$TourInfo['tpx'];
            }
          };
          if($decrypted['at'] === 'Room Only') $decrypted['tpx'] = $decrypted['px']; 
          if($decrypted['at'] === 'Room Only') $tourListCart = []; 
          if($TourInfo['tpx'] != $TourInfo['otpx']) $tourListCart = []; 
        //   dd($TourInfo['otpx']);

          if($decrypted['at'] != (request()->has('at') ? decrypt(request('at')) : '')) $tourListCart = []; 
        }

        $TourInfoEncrypted = [
            "cin" => request()->has('cin') ? request('cin') : old('check_in'),
            "cout" => request()->has('cout') ? request('cout') : old('check_out'),
            "px" => request()->has('px') ? request('px') : old('pax'),
            "tpx" => request()->has('tpx') ? request('tpx') : old('tour_pax'),
            "at" => request()->has('at') ? request('at') : old('accommodation_type'),
            "py" => request()->has('py') ? request('py') : old('payment_method'),
        ];

        return view('reservation.step2', [
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
            'TourInfo' => $TourInfo ,
            'tourListCart' => $tourListCart ,
            'TourInfoEncrypted' => $TourInfoEncrypted ,
            "user_days" => $noOfday,
        ]); 
    }
    // Check Step 1 on Choose Form
    public function chooseCheck1(Request $request){
        $validated = $this->reservationValidation($request, null, true, true);
        if(!is_array($validated)) return $validated;

        if($validated){
            $reservationInfo = [
                "cin" =>   $validated['check_in'] ?? '',
                "cout" => $validated['check_out'] ?? '',
                "at" => $validated['accommodation_type'] ??  '',
                "px" => $validated['pax'] ?? '',
                "py" => $validated['payment_method'] ?? '',
            ];  
            if(session()->has('rinfo')){
                $dcrytd = decryptedArray(session('rinfo'));
                if($dcrytd['at'] != $validated['accommodation_type'] || $validated['accommodation_type'] == 'Room Only') unset($dcrytd['tm']);
                session(['rinfo' => encryptedArray($dcrytd)]);
            }

            if($validated['accommodation_type'] === 'Room Only'){            
                if(!$this->replaceRInfo($reservationInfo, true)){
                    if(session()->has('rinfo') && decrypt(session('rinfo')['at']) != $validated['accommodation_type']); unset(session('rinfo')['tm']);

                    $reservationInfo = encryptedArray($reservationInfo);
                    session(['rinfo' => $reservationInfo]);
                }
                return redirect()->route('reservation.details');
            }
            $getParamStep1 = [
                "cin" =>  encrypt($validated['check_in']),
                "cout" =>  encrypt($validated['check_out']),
                "px" =>  encrypt($validated['pax']),
                "tpx" =>  encrypt($validated['tour_pax']),
                "at" =>  encrypt($validated['accommodation_type']),
                "py" =>  encrypt($validated['payment_method']),
            ];
            if(!$this->replaceRInfo($getParamStep1)){
                session(['rinfo' => $getParamStep1]);
            }
            return redirect()->route('reservation.choose', [Arr::query($getParamStep1), '#tourmenu']);
        }

    }
    public function chooseCheckAll(Request $request){
        $validate = Validator::make($request->all('tour_menu'), [
            'tour_menu.*' => 'required',
        ]);
        if(empty($request['tour_menu']) || $validate->fails()){
            $getParam = [
                "cin" =>   $request['cin'] ?? '',
                "cout" => $request['cout'] ?? '',
                "at" =>  $request['at'] ?? '',
                "tpx" => $request['tpx'] ?? '',
                "px" =>  $request['px']?? '',
                "py" =>   $request['py'] ?? '',
            ];
            return redirect()->route('reservation.choose', [Arr::query($getParam), '#tour-menu'])
            ->with('error', 'You have not selected anything in the cart yet. Please make a selection first.');
        }
        $validated = $validate->validate();
        $reservationInfo = [
            "cin" =>  $request['cin'] ?? '',
            "cout" => $request['cout'] ?? '',
            "at" => $request['at'] ?? '',
            "px" => $request['px'] ?? '',
            "tpx" =>  $request['tpx'] ?? '',
            "py" =>  $request['py'] ?? '',
            "tm" =>  encrypt($validated['tour_menu']) ?? '',
        ];
    
        if(!$this->replaceRInfo($reservationInfo)){
            $reservationInfo = encryptedArray($reservationInfo);
            session(['rinfo' => $reservationInfo]);
        }
        return redirect()->route('reservation.details');

    }
    public function details(Request $request){
        if($request->has('details') && $request['details'] === "update"){
            $user = User::find(decrypt($request['user']) ?? null);
            return view('reservation.step3', ['user' => $user]);
        }
        return view('reservation.step3');
    }
    public function detailsUpdate(Request $request){
        $user = User::find(decrypt($request->id));
        $validated = $request->validate([
            'first_name' => ['required', 'min:1'],
            'last_name' => ['required', 'min:1'],
            'birthday' => ['required', 'date'],
            'country' => ['required', 'min:1'],
            'nationality' => ['required'],
            'contact_code' => ['required'],
            'contact' => ['required', (new Phone)->international()->country(Str::upper($request['contact_code']))],
            'email' => Rule::when($user->email === $request['email'], ['required', 'email'] ,['required', 'email',  Rule::unique('users', 'email')]),
        ]);
        $phone = new PhoneNumber($validated['contact'], Str::upper($validated['contact_code']));
        $validated['contact'] = $phone->formatInternational(); 

        $user->update($validated);
        if($user) return redirect()->route('reservation.details')->with('success', $user->name() . ' information was updated');
    }
    public function detailsStore(){
        $user = User::find(auth('web')->user()->id);
        if(empty($user)) return back()->wtih('error', 'Sorry, you are not register the account');
        $unfo = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'age' => $user->age(),
            'country' => $user->country,
            'nationality' => $user->nationality,
            'contact' => $user->contact,
            'email' => $user->email,
        ];
        if(!$this->replaceRInfo($unfo, true)){
            $unfo = encryptedArray($unfo);
            session(['rinfo' => $unfo]);
        }
        return redirect()->route('reservation.confirmation');
        
    }
    public function confirmation(Request $request){
        $uinfo = decryptedArray(session('rinfo')) ?? '';
        if(empty($uinfo['tm']) && $uinfo['at'] !== 'Room Only') return redirect()->route('reservation.choose', Arr::query(['cin' => session()->get('rinfo')['cin'], 'cout' => session()->get('rinfo')['cout'], 'px' => session()->get('rinfo')['px'], 'tpx' => session()->get('rinfo')['tpx'], 'py' => session()->get('rinfo')['py'], 'at' => session()->get('rinfo')['at']], '#tourMenu'))->with('info', 'Your Tour Menu was empty');
        $user_menu = [];
        if(isset($uinfo['tm'])){
            foreach($uinfo['tm'] as $key => $item){
                $tour = TourMenu::withTrashed()->findOrFail($item);
                $user_menu[$key]['id'] = $tour->id;
                $user_menu[$key]['price'] = $tour->price;
                $user_menu[$key]['amount'] = (int)$uinfo['tpx'] * $tour->price;

                $user_menu[$key]['title'] = $tour->tourMenu->title;
                $user_menu[$key]['type'] = $tour->type;
                $user_menu[$key]['pax'] = $tour->pax . ' guest';
                $user_menu[$key]['tour_pax'] = $uinfo['tpx'] . ' guest';
            }
        }
        return view('reservation.step4', [
            'user_menu' => $user_menu,
            'uinfo' => $uinfo,
        ]);
    }
    public function destroyTour(Request $request){
        $decrypted_uinfo = decryptedArray(session('rinfo')) ?? '';
        $encrypted_uinfo = session()->get('rinfo') ?? '';
        if($decrypted_uinfo['at'] === 'Room Only' || !session()->has('rinfo')) return back();
        $id = decrypt($request['id']);
        $tm = $decrypted_uinfo['tm'];
        $newTm = [];
        if(!empty($tm)){
            foreach($tm as $item){
                if($item == $id) continue;
                else $newTm[] = $item;
            }
        }
        else{
            return back();
        }                                                                                                                                                                                       
        if(empty($newTm)) {
            $encrypted_uinfo['tm'] = encrypt($newTm);
            foreach($encrypted_uinfo as $key => $item)if($key != 'tm') $new_unfo[$key] = $item;
            session(['rinfo' => $new_unfo]);
            return redirect()->route('reservation.choose', Arr::query(['cin' => $encrypted_uinfo['cin'], 'cout' => $encrypted_uinfo['cout'], 'px' => $encrypted_uinfo['px'], 'tpx' => $encrypted_uinfo['tpx'], 'py' => $encrypted_uinfo['py'], 'at' => $encrypted_uinfo['at'], 'cf' => encrypt(true)], '#tourmenu'))->with('info', 'Your Tour Menu was empty');
        }
        else{
            $encrypted_uinfo['tm'] = encrypt($newTm);
            session()->forget('rinfo');
            session(['rinfo' => $encrypted_uinfo]);
            return back()->with('success', TourMenu::withTrashed()->find($id)->tourMenu->title . ' was Removed');
        }

    }
    public function storeReservation(Request $request){
        $user = User::findOrFail(auth('web')->user()->id);
        $uinfo = decryptedArray(session('rinfo')) ?? '';
        $validated = $request->validate([
            'valid_id' => Rule::when(!isset($user->valid_id), ['required' ,'image', 'mimes:jpeg,png,jpg', 'max:5024']), 
            'tour' => Rule::when(!empty($request['tour']), ['required', 'array'], ['nullable']), 
        ], [
            'required' => 'The image is required',
            'image' => 'The file must be an image of type: jpeg, png, jpg',
            'mimes' => 'The image must be of type: jpeg, png, jpg',
            'max' => 'The image size must not exceed 5 MB',
        ]);
        $reserve_info = [
            'user_id' => $user->id,
            'pax' => $uinfo['px'] ?? '',
            'check_in' => Carbon::createFromFormat('Y-m-d', $uinfo['cin'])->setTimezone('Asia/Manila')->format('Y-m-d') ?? '',
            'check_out' => Carbon::createFromFormat('Y-m-d', $uinfo['cout'])->setTimezone('Asia/Manila')->format('Y-m-d') ?? '',
            'accommodation_type' => $uinfo['at'] ?? '',
            'payment_method' => $uinfo['py'] ?? '',
        ];
        if($uinfo['at'] !== 'Room Only' && isset($validated['tour'])){
            foreach(decryptedArray($validated['tour']) as $key => $tour_id) {
                $tour_menu = TourMenu::find($tour_id);
                $tours['tm'. $tour_id] = [
                    'id' => $tour_menu->id,
                    'title' => $tour_menu->tourMenu->title,
                    'type' => $tour_menu->type,
                    'pax' => $tour_menu->pax,
                    'used' => false,
                    'price' => (double)$tour_menu->price,
                    'created' => now('Asia/Manila')->format('YmdHis'),
                    'tpx' => $uinfo['tpx'],
                    'amount' => (double)$tour_menu->price * (int)$uinfo['tpx']
                ];
            }
            $reserve_info['tour_pax'] = $uinfo['tpx'];
            $reserve_info['transaction'] = $tours;
        }
        
        $reserve_info = Reservation::create($reserve_info);

        if($request->hasFile('valid_id')){  
            if($user->valid_id) deleteFile($user->valid_id, 'private');
            $validated['valid_id'] = saveImageWithJPG($request, 'valid_id', 'valid_id', 'private');
            $user->update(['valid_id' => $validated['valid_id']]);
        }
        $text = 
        "New Reservation!\n" .
        "Name: ". $reserve_info->userReservation->name() ."\n" . 
        "Age: " . $reserve_info->userReservation->age() ."\n" .  
        "Nationality: " . $reserve_info->userReservation->nationality  ."\n" . 
        "Country: " . $reserve_info->userReservation->country ."\n" . 
        "Check-in: " . Carbon::createFromFormat('Y-m-d', $reserve_info->check_in)->setTimezone('Asia/Manila')->format('F j, Y') ."\n" . 
        "Check-out: " . Carbon::createFromFormat('Y-m-d', $reserve_info->check_out)->setTimezone('Asia/Manila')->format('F j, Y') ."\n" . 
        "Type: " . $reserve_info->accommodation_type ;
        // Send Notification 
        $details = [
            'name' => $reserve_info->userReservation->name(),
            'title' => 'Reservation Complete',
            'body' => 'Your Reservation are done, We just send email for the approval'
        ];

        session()->forget(['rinfo', 'ck']);
        $reserve_info->userReservation->notify((new UserNotif(route('user.reservation.home') ,$details['body'], $details, 'reservation.mail')));
        $this->systemNotification($text, route('system.reservation.show', encrypt($reserve_info->id)));
        unset($text, $keyboard, $details, $uinfo);
        return redirect()->route('reservation.done', ['id' => encrypt($reserve_info->id)]);
    }
    public function done($id){
        $reserve_info = Reservation::findOrFail(decrypt($id));
        return view('reservation.done', ['id' => $reserve_info->id]);
    }
    public function storeMessage(Request $request){
        $reservation = Reservation::findOrFail($request->id);
        $validate = $request->validate([
            'message' => 'required',
        ]);
        $message = $reservation->message;
        $message['request'] = $validate['message'];
        $reservation->message = $message;
        $reservation->save();
        return redirect()->route('home')->with('success', 'Thank you for your request');
    }

}
