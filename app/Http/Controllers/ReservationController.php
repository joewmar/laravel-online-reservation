<?php

namespace App\Http\Controllers;

use App\Mail\ReservationMail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\TourMenu;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function date(Request $request){
        $dateList = [];
        if(session()->has('rinfo')){
            $dateList = decryptedArray(session('rinfo'));
            return view('reservation.step1', ['cin' => $dateList['cin'], 'cout' => $dateList['cout'], 'at' => $dateList['at'], 'px' => $dateList['px']]);
        }
        return view('reservation.step1', ['cin' => $request['check_in'], 'cout' => $request['check_out'], 'at' => $request['accommodation_type'], 'px' => $request['pax']]);
    }
    public function dateCheck(Request $request){
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

        $validator = [];
        if($request['accommodation_type'] === 'Day Tour'){
            $validator = Validator::make($request->all('check_in','check_out', 'pax'), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out'), 'date_equals:'.$request['check_out']],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Overnight'){
            $validator = Validator::make($request->all('check_in','check_out', 'pax'), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2), Rule::unique('reservations', 'check_out')],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
                'after_or_equal' => 'Choose within 2 day and above (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Room Only'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in'], Rule::unique('reservations', 'check_out')],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('room_rates', 'occupancy')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
        }
        if ($validator->fails()) {            
            return redirect()->route('reservation.date')
            ->withErrors($validator)
            ->withInput();
        }

        $validated = $validator->validated();
        if($validated){
            return redirect()->route('reservation.date')->with('success',"Your choose date is now available, Let s proceed if you will make reservation")->withInput();
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

        $validated = null;
        if($request['accommodation_type'] === 'Day Tour'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out'), 'date_equals:'.$request['check_out']],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Overnight'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2), Rule::unique('reservations', 'check_out')],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
                'after_or_equal' => 'Choose within 2 day and above (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Room Only'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in'], Rule::unique('reservations', 'check_out')],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('room_rates', 'occupancy')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
        }
        else{
            return redirect()->route('reservation.date')->with('error',"Something Wrong!")->withInput();
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
        if(session()->has('rinfo')){
            $paramsDates = array(
                "cin" =>  session('rinfo')['cin'] ?? '',
                "cout" =>   session('rinfo')['cout'] ?? '',
                "at" =>   session('rinfo')['at'] ?? '',
                "px" =>  session('rinfo')['px'] ?? '',
            );
            return redirect()->route('reservation.choose', Arr::query($paramsDates));
            
        } 
        return redirect()->route('reservation.choose', Arr::query($paramsDates));

    }
    // Choose Form
    public function choose(Request $request){

        if(session()->has('rinfo')){
            $dateList = decryptedArray(session('rinfo'));
            $chooseMenu = [];
            foreach(explode(',', $dateList['tm']) as $key => $item){
                $chooseMenu[$key] = explode('_', $item);
            }
            foreach($chooseMenu as $key => $item){
                $chooseMenu[$key]['title'] = TourMenuList::select('title')->findOrFail($chooseMenu[$key][0])->title;
                $chooseMenu[$key]['price'] = TourMenu::select('price')->findOrFail($chooseMenu[$key][1])->price;

            }
            $noOfday = checkDiffDates($dateList['cin'], $dateList['cout']);
            return view('reservation.step2', [
                'tour_lists' => TourMenuList::all(), 
                'tour_category' => TourMenuList::distinct()->get('category'), 
                'tour_menus' => TourMenu::all(), 
                "cin" =>  $dateList['cin'],
                "cout" => $dateList['cout'],
                "px" => $dateList['px'],
                "at" => $dateList['at'],
                "py" =>  $dateList['py'],
                'cmenu' => $chooseMenu,
                "user_days" => isset($noOfday) ? $noOfday : '',
            ]); 
            dd($dateList['py']);
        }
        
        if($request->has(['cin', 'cout', 'px', 'at'])){
            $noOfday = checkDiffDates(decrypt(request('cin')), decrypt(request('cout')));
        }
        return view('reservation.step2', [
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
            'tour_menus' => TourMenu::all(), 
            "cin" =>  $request->has('cin') != null ? decrypt(request('cin')) : '',
            "cout" => $request->has('cout') != null ? decrypt(request('cout')) : '',
            "at" => $request->has('at') != null ? decrypt(request('at')) : '',
            "px" => $request->has('px') != null ? decrypt(request('px')) : '',
            "py" =>  $request->has('py') != null ? decrypt(request('py')) : '',
            "user_days" => isset($noOfday) ? $noOfday : '',
        ]);      



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

        $validated = [];
        if($request['accommodation_type'] === 'Day Tour'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out'), 'date_equals:'.$request['check_out']],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Overnight'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2), Rule::unique('reservations', 'check_out')],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
                'after_or_equal' => 'Choose within 2 day and above (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Room Only'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in'], Rule::unique('reservations', 'check_out')],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('room_rates', 'occupancy')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
            $reservationInfo = [
                "cin" =>  $request->has('cin') != null ? request('cin') : '',
                "cout" => $request->has('cout') != null ? request('cout') : '',
                "px" => $request->has('px') != null ? request('px'): '',
                "at" => $request->has('at') != null ? request('at'): '',
                "py" =>  $request->has('py') != null ? request('py') : '',
            ];
    
            if(session()->has('rinfo')) foreach($reservationInfo as $key => $item) session('rinfo')[$key] = $reservationInfo[$key]; 
            else session(['rinfo' => $reservationInfo]);
        }
        if($validated){
            $getParamStep1 = [
                "cin" =>  encrypt($validated['check_in'] ?? ''),
                "cout" =>  encrypt($validated['check_out'] ?? ''),
                "px" =>  encrypt($validated['pax'] ?? ''),
                "py" =>  encrypt($validated['payment_method'] ?? ''),
            ];
            return redirect()->route('reservation.choose', [Arr::query($getParamStep1), '#tourmenu']);
        }

    }
    public function chooseCheckAll(Request $request){
        $validate = Validator::make($request->all('tour_menu'), [
            'tour_menu.*' => 'required',
        ]);
        if(!$request->has('tour_menu') || $validate->fails()){
            $getParam = [
                "cin" =>  $request->has('cin') != null ? request('cin') : '',
                "cout" => $request->has('cout') != null ? request('cout') : '',
                "px" => $request->has('px') != null ? request('px'): '',
                "py" =>  $request->has('py') != null ? request('px') : '',
            ];
            return redirect()->route('reservation.choose', [Arr::query($getParam), '#tour-menu'])
            ->with('error', 'You have not selected anything in the cart yet. Please make a selection first.');
        }
        $validate = $validate->validate();
        $reservationInfo = [
            "cin" =>  $request->has('cin') != null ? request('cin') : '',
            "cout" => $request->has('cout') != null ? request('cout') : '',
            "px" => $request->has('px') != null ? request('px'): '',
            "py" =>  $request->has('py') != null ? request('py') : '',
            "tm" => encrypt(implode(', ', $validate['tour_menu'])),
        ];

        if(session()->has('rinfo')) foreach($reservationInfo as $key => $item) session('rinfo')[$key] = $reservationInfo[$key]; 
        else session(['rinfo' => $reservationInfo]);
       
        if(Auth::check()){
            return redirect()->route('reservation.details');
        }
        else{
            return redirect()->route('login')->with('info', 'You need to login first');
        }
    }
    public function details(Request $request){
        return view('reservation.step3');
    }
    public function detailsStore(Request $request){
        $validated = $request->validate([
            'first_name' => ['required'],
            'last_name' => ['required'],
            'age' => ['required', 'numeric'],
            'country' => ['nullable'],
            'nationality' => ['nullable'],
            'contact' => ['required'],
            'email' => ['required'],
        ]);
        if($validated){
            $encryptedArray  = encryptedArray($validated);
            $reserveInfo = session('rinfo');
            foreach(array_keys($encryptedArray) as $item){
                $reserveInfo[$item] = $encryptedArray[$item];
            }
            // Reset Session Info
            session(['rinfo' => $reserveInfo]);
            return redirect()->route('reservation.confirmation');
        }
    }

    public function confirmation(){
        $uinfo = decryptedArray(session()->get('rinfo')) ?? '';
        $user_menu = [];
        foreach(explode(',', $uinfo['tm']) as $key => $item) $umenu[$key] = explode('_', $item);
        foreach($umenu as $key => $item){
            $user_menu[$key]['title'] = TourMenuList::select('title')->findOrFail($umenu[$key][0])->title;
            $user_menu[$key]['price'] = TourMenu::select('price')->findOrFail($umenu[$key][1])->price;
        }
        return view('reservation.step4', [
            'user_menu' => $user_menu,
            'uinfo' => $uinfo,
        ]);
    }
    public function storeReservation(Request $request){
        $uinfo = decryptedArray(session()->get('rinfo')) ?? '';
        $validated = $request->validate([
            'amount.*' => ['required', 'numeric', 'decimal:0,2'],
        ]);
        $total = 0;
        foreach( $validated['amount'] as $amount){
            $total += (double)$amount;

        }
        if($validated){
            $reserve_info = Reservation::create([
                'user_id' => auth('web')->user()->id,
                'pax' => $uinfo['px'] ?? '',
                'age' => $uinfo['age'] ?? '',
                'menu' => $uinfo['tm'] ?? '',
                'accommodation_type' => $uinfo['at'] ?? '',
                'check_in' => $uinfo['cin'] ?? '',
                'check_out' => $uinfo['cout'] ?? '',
                'check_out' => $uinfo['cout'] ?? '',
                'amount' => implode(', ', $validated['amount']) ?? '',
                'total' => $total ?? '',
            ]);
            $details = [
                'title' => 'Reservation Complete',
                'body' => 'Your Reservation are done, We just send email for the confirmation'
            ];
            Mail::to($reserve_info->userReservation->email)->send(new ReservationMail($details, 'reservation.mail'));
            session()->forget('rinfo');
            return redirect()->route('reservation.done');
        }

    }
}
