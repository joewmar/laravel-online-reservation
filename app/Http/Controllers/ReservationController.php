<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\System;
use App\Models\Archive;
use App\Models\Feedback;
use App\Models\RoomList;
use App\Models\TourMenu;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use App\Models\OnlinePayment;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Notifications\EmailNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notification;
use AmrShawky\LaravelCurrency\Facade\Currency;



class ReservationController extends Controller
{
    private $user;
    // private $ruleRoomOnly = []
    public function __construct()
    {
        $this->user = auth('web');
        $this->middleware(function ($request, $next) {
            // dd($this->user->user()->id);
            $existingReservation = Reservation::where('user_id', $this->user->user()->id)->where('status', '<', 3)->first();

            if ($existingReservation) {
                session(['ck' => false]);
                return redirect()->route('home')->with('error', "Sorry, you can only make one reservation.");
            }

            return $next($request);
        })->except(['index', 'show', 'done', 'storeMessage', 'gcash', 'doneGcash', 'donePayPal', 'paymentStore','paypal', 'feedback', 'storeFeedback', 'date', 'dateCheck', 'dateStore', 'cancel','reschedule']); // You can specify the specific method where this middleware should be applied.
    }

    public function date(Request $request){
        $dateList = [];
        if(session()->has('rinfo')){
            $dateList = decryptedArray(session('rinfo'));
            return view('reservation.step1', ['cin' => $dateList['cin'], 'cout' => $dateList['cout'], 'at' => $dateList['at'], 'px' => $dateList['px']]);
        }
        if($request->has(['cin', 'cout', 'px', 'at', 'tpx', 'py', 'ck'])){
            $reserve = [
              "cin" => request('cin'),
              "cout" => request('cout'),
              "px" => request('px'),
              "at" => request('at'),
            ];
            $reserve =  decryptedArray($reserve);
            return view('reservation.step1', ['cin' => $reserve['cin'], 'cout' => $reserve['cout'], 'at' => $reserve['at'], 'px' => $reserve['px']]);

        }
        return view('reservation.step1');
    }
    public function dateCheck(Request $request){
        if(Str::contains(URL::previous(), route('home'))){
            session()->forget('rinfo');
        }
        // Check in (startDate to endDate) trim convertion
        if(str_contains($request['check_in'], 'to')){
            $dateSeperate = explode('to', $request['check_in']);
            $request['check_in'] = trim($dateSeperate[0]);
            $request['check_out'] = trim ($dateSeperate[1]);
        }
        // Check out convertion word to date format
        if(str_contains($request['check_out'], ', ')){
            $date = Carbon::createFromFormat('F j, Y', $request['check_out']);
            $request['check_out'] = $date->format('Y-m-d');
        }

        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');


        if(checkAvailRooms($request['pax'] ?? 0, $request['check_in'], $request['check_out']) && !empty($request['pax'])) {
            return redirect()->route('reservation.date')->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
        }
        

        $validator = null;
        if($request['accommodation_type'] == 'Day Tour'){
            $validator = Validator::make($request->all('check_in','check_out', 'accommodation_type', 'pax'), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Overnight'){
            $validator = Validator::make($request->all('check_in','check_out', 'accommodation_type', 'pax'), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'check_out.after_or_equal' => 'Choose within 2 or 3 days (Overnight)',
            ]);
        }
        elseif($request['accommodation_type'] == 'Room Only'){
            $validator = Validator::make($request->all('check_in','check_out', 'accommodation_type', 'pax'), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
                'accommodation_type' => 'required',
                'pax' => ['required', 'numeric', 'min:1'],
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
            session(['ck' => false]);
            return redirect()->route('reservation.date')->withErrors(['accommodation_type' => 'Choose the Accommodation type']);
        }
        if ($validator->fails()) {            
            session(['ck' => false]);
            return redirect()->route('reservation.date')
            ->withErrors($validator)
            ->withInput();
        }

        $validator = $validator->validated();
        if($validator){
            session(['ck' => true]);
            return redirect()->route('reservation.date')->with('success',"Your choose date is now available, Let s proceed if you will make reservation")->withInput($validator);
        }
    }
    // Data Check
    public function dateStore(Request $request){
        // Check in (startDate to endDate) trim convertion
        if(str_contains($request['check_in'], 'to')){
            $dateSeperate = explode('to', $request['check_in']);
            $request['check_in'] = trim($dateSeperate[0]);
            $request['check_out'] = trim ($dateSeperate[1]);
        }
        // Check out convertion word to date format
        if(str_contains($request['check_out'], ', ')){
            $date = Carbon::createFromFormat('F j, Y', $request['check_out']);
            $request['check_out'] = $date->format('Y-m-d');
        }

        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        
        if(checkAvailRooms($request['pax'] ?? 0, $request['check_in'], $request['check_out'] && !empty($request['pax']))){
            return redirect()->route('reservation.date')->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
        }
        $validated = null;
        if($request['accommodation_type'] === 'Day Tour'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Overnight'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
                'check_out.after_or_equal' => 'Choose within 2 or 3 days (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Room Only'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
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
            session(['ck' => false]);
            return back()->withErrors(['accommodation_type' => "Choose the Accommodation type"])->withInput($request->input());
        }
        if(str_contains($validated['check_in'], 'to')){
            $dateSeperate = explode('to', $validated['check_in']);
            $validated['check_in'] = trim($dateSeperate[0]);
            $validated['check_out'] = trim ($dateSeperate[1]);
        }
        
        $paramsDates = array(
            "cin" =>  $validated['check_in'] === '' ? null : encrypt($validated['check_in']),
            "cout" =>  $validated['check_out'] === '' ? null : encrypt($validated['check_out']),
            "at" =>  $validated['accommodation_type'] === '' ? null : encrypt($validated['accommodation_type']),
            "px" =>  $validated['pax'] === '' ? null : encrypt($validated['pax']),
        );
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
    public function choose(){
        if(session()->has('rinfo')){
            $dateList = decryptedArray(session('rinfo'));
            $noOfday = getNoDays($dateList['cin'], $dateList['cout']);
            $chooseMenu = [];
            if(isset($dateList['tm'])){
                foreach($dateList['tm'] as $key => $item){
                    $chooseMenu[$key]['price'] = TourMenu::findOrFail($item)->price;
                    $chooseMenu[$key]['title'] = TourMenu::findOrFail($item)->tourMenu->title;
                    $chooseMenu[$key]['type'] = TourMenu::findOrFail($item)->type;
                    $chooseMenu[$key]['pax'] = TourMenu::findOrFail($item)->pax;
                }
            }
            return view('reservation.step2', [
                'tour_lists' => TourMenuList::all(), 
                'tour_category' => TourMenuList::distinct()->get('category'), 
                "cin" =>  $dateList['cin'],
                "cout" => $dateList['cout'],
                "px" => $dateList['px'],
                "at" => $dateList['at'],
                "py" =>  $dateList['py'],
                'cmenu' => $chooseMenu ,
                "user_days" => isset($noOfday) ? $noOfday : 1,
            ]); 
            
        }
        if(request()->has(['cin', 'cout', 'px', 'py', 'tpx','at'])){
            return view('reservation.step2', [
                'tour_lists' => TourMenuList::all(), 
                'tour_category' => TourMenuList::distinct()->get('category'), 
                'tour_menus' => TourMenu::all(), 
                "user_days" => $noOfday ?? 1,
            ]); 
        }
        if(request()->has(['cin', 'cout', 'px', 'at'])){
            return view('reservation.step2'); 
        }
    }
    // Check Step 1 on Choose Form
    public function chooseCheck1(Request $request){
        // Check in (startDate to endDate) trim convertion
        if(str_contains($request['check_in'], 'to')){
            $dateSeperate = explode('to', $request['check_in']);
            $request['check_in'] = trim($dateSeperate[0]);
            $request['check_out'] = trim ($dateSeperate[1]);
        }
        // Check out convertion word to date format
        if(str_contains($request['check_out'], ', ')){
            $date = Carbon::createFromFormat('F j, Y', $request['check_out']);
            $request['check_out'] = $date->format('Y-m-d');
        }
        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        
        $validated = null;
        if($request['accommodation_type'] === 'Day Tour'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
                'payment_method' => ['required'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'tour_pax.exists' => 'Sorry, This number of guest are invalid for choose tour services',    
            ]);
        }
        elseif($request['accommodation_type'] === 'Overnight'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1'],
                'tour_pax' => ['required', 'numeric', 'min:1', 'max:'.$request['pax']],
                'payment_method' => ['required'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after_or_equal' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'tour_pax.exists' => 'Sorry, This number of guest are invalid for choose tour services',                'check_out.after_or_equal' => 'Choose within 2 or 3 days (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Room Only'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
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
            $reservationInfo = [
                "cin" =>   $validated['check_in'] ?? '',
                "cout" => $validated['check_out'] ?? '',
                "at" => $validated['accommodation_type'] ??  '',
                "tpx" => $validated['tour_pax'] ?? '',
                "px" => $validated['pax'] ?? '',
                "py" => $validated['payment_method'] ?? '',
            ];
            $reservationInfo = encryptedArray($reservationInfo);
            if(session()->has('rinfo')) {
                $replaceRInfo = session('rinfo');
                foreach($reservationInfo as $key => $item) $replaceRInfo[$key] = $reservationInfo[$key];
                session(['rinfo' => $replaceRInfo]); 
            }

            else session(['rinfo' => $reservationInfo]);
            return redirect()->route('reservation.details');

        }
        else{
            session(['ck' => false]);
            return back()->withErrors(['error' => "Choose the Accommodation type"])->withInput($validated);
        }

        if($validated){
            $getParamStep1 = [
                "cin" =>  encrypt($validated['check_in']),
                "cout" =>  encrypt($validated['check_out']),
                "px" =>  encrypt($validated['pax']),
                "tpx" =>  encrypt($validated['tour_pax']),
                "at" =>  encrypt($validated['accommodation_type']),
                "py" =>  encrypt($validated['payment_method']),
            ];
            if(session()->has('rinfo')) {
                $replaceRInfo = session('rinfo');
                foreach($getParamStep1 as $key => $item) $replaceRInfo[$key] = $getParamStep1[$key];
                session(['rinfo' => $replaceRInfo]); 
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
            $getParam = encryptedArray($getParam);
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
    
        session(['rinfo' => $reservationInfo]);
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
            'contact' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'email' => Rule::when($user->email === $request['email'], ['required', 'email'] ,['required', 'email',  Rule::unique('users', 'email')]),
        ]);
        $user->update($validated);
        if($user) return redirect()->route('reservation.details')->with('success', $user->name() . ' was updated!');
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
        $encryptedArray  = encryptedArray($unfo);
        $reserveInfo = session('rinfo');
        foreach(array_keys($encryptedArray) as $item) $reserveInfo[$item] = $encryptedArray[$item];
        // Reset Session Info
        session(['rinfo' => $reserveInfo]);
        return redirect()->route('reservation.confirmation');
        
    }
    public function confirmation(Request $request){
        $uinfo = decryptedArray(session('rinfo')) ?? '';
        if(empty($uinfo['tm']) && $uinfo['at'] !== 'Room Only') return redirect()->route('reservation.choose', Arr::query(['cin' => session()->get('rinfo')['cin'], 'cout' => session()->get('rinfo')['cout'], 'px' => session()->get('rinfo')['px'], 'tpx' => session()->get('rinfo')['tpx'], 'py' => session()->get('rinfo')['py'], 'at' => session()->get('rinfo')['at']], '#tourMenu'))->with('info', 'Your Tour Menu was empty');
        $user_menu = [];
        if($uinfo['at'] !== 'Room Only' && isset($uinfo['tm'])){
            foreach($uinfo['tm'] as $key => $item){
                $tour = TourMenu::findOrFail($item);
                $user_menu[$key]['id'] = $tour->id;
                if($request->has('cur') && !empty($request['cur'])){
                    $converted = Currency::convert()->from('PHP')->to($request['cur'])->amount($tour->price)->get();
                    $user_menu[$key]['price'] = $converted;
                    $user_menu[$key]['orig_price'] = $tour->price;
                    $user_menu[$key]['amount'] = (int)$uinfo['tpx'] * (double)$user_menu[$key]['price'];

                }
                else{
                    $user_menu[$key]['price'] = $tour->price;
                    $user_menu[$key]['orig_price'] = $tour->price;
                    $user_menu[$key]['amount'] = (int)$uinfo['tpx'] * $tour->price;

                }
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
    public function convert(Request $request){
        $validated = $request->validate(['cur' => 'required']);
        return redirect()->route('reservation.confirmation', ['cur' => $validated['cur']]);
    }
    public function destroyTour(Request $request){
        $decrypted_uinfo = decryptedArray(session('rinfo')) ?? '';
        $encrypted_uinfo = session()->get('rinfo') ?? '';
        if($decrypted_uinfo['at'] === 'Room Only' || !session()->has('rinfo')) return back();
        $id = decrypt($request['id']);
        $tm = $decrypted_uinfo['tm'];
        if(!empty($tm)){
            $newTm = [];
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
            return back()->with('success', TourMenu::find($id)->tourMenu->title . ' was Removed');
        }
    }
    public function storeReservation(Request $request){
        $user = User::findOrFail(auth('web')->user()->id);
        $systemUser = System::all()->where('type', '>=', 0)->where('type', '<=', 1);
        $uinfo = decryptedArray(session('rinfo')) ?? '';
        $validated = $request->validate([
            'valid_id' => Rule::when(!$user->valid_id, ['required' ,'image', 'mimes:jpeg,png,jpg', 'max:5024']), 
            'tour' => Rule::when(!empty($request['tour']), ['required', 'array'], ['nullable']), 
        ], [
            'required' => 'The image is required',
            'image' => 'The file must be an image of type: jpeg, png, jpg',
            'mimes' => 'The image must be of type: jpeg, png, jpg',
            'max' => 'The image size must not exceed 5 MB',
        ]);
        $reserve_info = null;
        if($uinfo['at'] !== 'Room Only'){
            foreach(decryptedArray($validated['tour']) as $key => $tour_id) {
                $tour_menu = TourMenu::find($tour_id);
                $tours['tm'. $tour_id] = [
                    'title' => $tour_menu->tourMenu->title . ' ' . $tour_menu->type,
                    'price' => (double)$tour_menu->price,
                    'amount' => (double)$tour_menu->price * (int)$uinfo['tpx']
                ];
            }
            $reserve_info = Reservation::create([
                'user_id' => $user->id,
                'pax' => $uinfo['px'] ?? '',
                'tour_pax' => $uinfo['tpx'],
                'age' => $uinfo['age'] ?? '',
                'payment_method' => $uinfo['py'] ?? '',
                'accommodation_type' => $uinfo['at'] ?? '',
                'check_in' => $uinfo['cin'] ?? '',
                'check_out' => $uinfo['cout'] ?? '',
                'transaction' => $tours,
                
            ]);
            
            
        }
        else{
            $reserve_info = Reservation::create([
                'user_id' => $user->id,
                'pax' => $uinfo['px'] ?? '',
                'tour_pax' => $uinfo['px'],
                'age' => $uinfo['age'] ?? '',
                'check_in' => $uinfo['cin'] ?? '',
                'check_out' => $uinfo['cout'] ?? '',
                'accommodation_type' => $uinfo['at'] ?? '',
                'payment_method' => $uinfo['py'] ?? '',
            ]);
        }
        if($request->hasFile('valid_id')){  
            $validated['valid_id'] = saveImageWithJPG($request, 'valid_id', 'valid_id', 'private');
            $user->update(['valid_id' => $validated['valid_id']]);
        }
        $text = 
        "New Reservation!\n" .
        "Name: ". $reserve_info->userReservation->name() ."\n" . 
        "Age: " . $reserve_info->age ."\n" .  
        "Nationality: " . $reserve_info->userReservation->nationality  ."\n" . 
        "Country: " . $reserve_info->userReservation->country ."\n" . 
        "Check-in: " . Carbon::createFromFormat('Y-m-d', $reserve_info->check_in)->format('F j, Y') ."\n" . 
        "Check-out: " . Carbon::createFromFormat('Y-m-d', $reserve_info->check_out)->format('F j, Y') ."\n" . 
        "Type: " . $reserve_info->accommodation_type ;
        // Send Notification to 
        $keyboard = [
            [
                ['text' => 'View Details', 'url' => route('system.reservation.show', encrypt($reserve_info->id))],
            ],
        ];
        $details = [
            'name' => $reserve_info->userReservation->name(),
            'title' => 'Reservation Complete',
            'body' => 'Your Reservation are done, We just send email for the approve or disapprove confirmation'
        ];
        Mail::to(env('SAMPLE_EMAIL') ?? $reserve_info->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
        foreach($systemUser as $user){
            if(!empty($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, $keyboard, 'bot1');
        }
        session()->forget('rinfo');
        session()->forget('ck');

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
        $message = $reservation->getAllArray('message');
        $message['request'] = $validate['message'];
        $reservation->update(['message' => $message]);
        return redirect()->route('home')->with('success', 'Thank you for your request');
    }
    public function gcash($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $reference = WebContent::all()->first()->payment['gcash'] ?? [];
        foreach($reference as $key => $item){
            if($reference[$key]['priority'] === true){
                $reference = $reference[$key];
                break;
            }
        }
        // $references = $references->payment['gcash'];
        if(!($reservation->status() === 'Confirmed' && $reservation->payment_method === 'Gcash'))  abort(404);
        return view('reservation.gcash.index', ['reservation' => $reservation, 'reference' => $reference]);
    }
    public function paymentStore(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if(!($reservation->status() === 'Confirmed'))  abort(404);
        $systemUser = System::all()->where('type', 0)->where('type', 1);
        if($reservation->status() === 'Confirmed'){
            $validator = Validator::make($request->all(), [
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'amount' => ['required', 'numeric'],
                'reference_no' => ['required'],
                'payment_name' => ['required'],
            ],[
                'required' => 'Need to fill up this information (:attribute)',
            ]);
            if($validator->fails()){
                return back()->with('error', $validator->errors()->all());
            }
            if($validator->valid()){
                $validated =  $validator->validate();
                $validated['image'] = saveImageWithJPG($request, 'image', 'online_payment', 'private');
                $validated['reservation_id'] = $reservation->id;
                $validated['payment_method'] = $reservation->payment_method;
                $sended = OnlinePayment::create($validated);
                if($sended) {
                    $text = 
                    "Payment Reservation !\n" .
                    "Name: ". $reservation->userReservation->name() ."\n" . 
                    "Country: " . $reservation->userReservation->country ."\n" . 
                    "Payment Method: " . $validated['payment_method'] ."\n" . 
                    "Payment Name: " . $validated['payment_name'] ."\n" . 
                    "Total Amount " . number_format($validated['amount'], 2) ."\n" . 
                    "Reference No: " . $validated['reference_no'];
                    $keyboard = [
                        [
                            ['text' => 'View Details', 'url' => route('system.reservation.show.online.payment', encrypt($reservation->id))],
                        ],
                    ];
                    // foreach($systemUser as $user){
                    //     if($user->telegram_chatID != null){
                    //         telegramSendMessage($user->telegram_chatID, $text, $keyboard); 
                    //     }
            
                    // }
                    telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID'), $text, $keyboard); 

                    $text = null;
                    $keyboard = null;
                    if($reservation->payment_method === 'Gcash') return redirect()->route('reservation.gcash.done', encrypt($sended->id));
                    if($reservation->payment_method === 'PayPal') return redirect()->route('reservation.paypal.done', encrypt($sended->id));
                }
            }
        }
        else{
            abort(404);
        }
        

    }
    public function doneGcash($id){
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        if(!($online_payment->reserve->status() === 'Confirmed' && $online_payment->reserve->payment_method === 'Gcash'))  abort(404);
        $contacts = WebContent::all()->first()->contact ?? [];
        if($online_payment) return view('reservation.gcash.success', ['contacts' => $contacts]);
    }
    public function donePayPal($id){
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        if(!($online_payment->reserve->status() === 'Confirmed' && $online_payment->reserve->payment_method === 'PayPal'))  abort(404);
        $contacts = WebContent::all()->first()->contact ?? [];
        if($online_payment) return view('reservation.paypal.success', ['contacts' => $contacts]);
    }
    public function paypal($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $reference = WebContent::all()->first()->payment['paypal'] ?? [];
        foreach($reference as $key => $item){
            if($reference[$key]['priority'] === true){
                $reference = $reference[$key];
                break;
            }
        }    
        if(!($reservation->status() === 'Confirmed' && $reservation->payment_method === 'PayPal')) abort(404);
        return view('reservation.paypal.index', ['reservation' => $reservation, 'reference' => $reference]);
            
    }
    public function feedback($id){
        return view('reservation.feedback', ['reservationID' => $id]);
    }
    public function storeFeedback(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $validated = $request->validate([
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'message' => ['required'],
        ], [
            'required' => 'Required',
        ]);
        $created = Feedback::create([
            'reservation_id' => $reservation->id,
            'rating' => (int)$validated['rating'],
            'message' => $validated['message'],
        ]);
        if($created) return redirect()->route('home')->with('success', 'Thank you for your opinion. Come Again');
    }
}
