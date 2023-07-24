<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\RoomList;
use App\Models\TourMenu;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Laravel\Facades\Telegram;

class ReservationController extends Controller
{
    public function date(Request $request){
        $dateList = [];
        if(session()->has('rinfo')){
            $dateList = decryptedArray(session('rinfo'));
            return view('reservation.step1', ['cin' => $dateList['cin'], 'cout' => $dateList['cout'], 'at' => $dateList['at'], 'px' => $dateList['px']]);
        }
        if($request->exists('cin', 'cout', 'px', 'at', 'ck')){
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
        // return view('reservation.step1', ['cin' => $request['check_in'], 'cout' => $request['check_out'], 'at' => $request['accommodation_type'], 'px' => $request['pax'], 'ck' => $request['ck'] ?? '0']);
    }
    public function dateCheck(Request $request){
        if(checkAvailRooms($request['pax'])){
            return redirect()->route('reservation.date')->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput();
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

        $validator = null;
        if($request['accommodation_type'] == 'Day Tour'){
            $validator = Validator::make($request->all('check_in','check_out', 'accommodation_type', 'pax'), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
    
            ]);
        }
        elseif($request['accommodation_type'] == 'Overnight'){
            $validator = Validator::make($request->all('check_in','check_out', 'accommodation_type', 'pax'), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
                'check_out.after_or_equal' => 'Choose within 2 day and above (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] == 'Room Only'){
            $validator = Validator::make($request->all('check_in','check_out', 'accommodation_type', 'pax'), [
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
                'accommodation_type' => 'required',
                'pax' => ['required', 'numeric', 'min:1', 'max:'.(string)RoomList::max('max_occupancy')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
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

        $validated = $validator->validated();
        if($validated){
            session(['ck' => true]);
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
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Overnight'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
                'check_out.after_or_equal' => 'Choose within 2 day and above (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Room Only'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', 'max:'.(string)RoomList::max('max_occupancy')],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
        }
        else{
            session(['ck' => false]);
            return redirect()->route('reservation.date')->with('error',"Choose the Accommodation type")->withInput();
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
        if(Reservation::where('user_id', '=' , auth('web')->user()->id )->first()) {
            session(['ck' => false]);
            return redirect()->route('home')->with('error',"Sorry you can 1 only fill up the reservation");
        }
        if(session()->has('rinfo')){
            $dateList = decryptedArray(session('rinfo'));
            $noOfday = checkDiffDates($dateList['cin'], $dateList['cout']);
            $chooseMenu = [];
            if($dateList['at'] !== 'Room Only' && $dateList['tm'] != null){
                foreach(explode(',', $dateList['tm']) as $key => $item){
                    $chooseMenu[$key]['price'] = TourMenu::findOrFail($item[$key])->price;
                    $chooseMenu[$key]['title'] = TourMenu::findOrFail($item[$key])->tourMenu->title;
                }
                
                return view('reservation.step2', [
                    'tour_lists' => TourMenuList::all(), 
                    'tour_category' => TourMenuList::distinct()->get('category'), 
                    'tour_menus' => TourMenu::all(), 
                    "cin" =>  $dateList['cin'],
                    "cout" => $dateList['cout'],
                    "px" => $dateList['px'],
                    "at" => $dateList['at'],
                    "py" =>  $dateList['py'],
                    'cmenu' => $chooseMenu ?? '',
                    "user_days" => isset($noOfday) ? $noOfday : '',
                ]); 
            }
        }
        $noOfday = checkDiffDates(decrypt(request('cin')), decrypt(request('cout')));
        if(request()->has(['cin', 'cout', 'px', 'py', 'at'])){
            return view('reservation.step2', [
                'tour_lists' => TourMenuList::all(), 
                'tour_category' => TourMenuList::distinct()->get('category'), 
                'tour_menus' => TourMenu::all(), 
                "cin" =>  decrypt(request('cin')) ?? '',
                "cout" =>  decrypt(request('cout')) ?? '',
                "at" =>  decrypt(request('at')) ?? '',
                "px" => decrypt(request('px')) ?? '',
                "py" =>   decrypt(request('py')) ?? '',
                "user_days" => $noOfday ?? '',
            ]); 
        }
        if(request()->has(['cin', 'cout', 'px', 'at'])){
            return view('reservation.step2', [
                'tour_lists' => TourMenuList::all(), 
                'tour_category' => TourMenuList::distinct()->get('category'), 
                'tour_menus' => TourMenu::all(), 
                "cin" =>  decrypt(request('cin')) ?? '',
                "cout" =>  decrypt(request('cout')) ?? '',
                "at" =>  decrypt(request('at')) ?? '',
                "px" => decrypt(request('px')) ?? '',
                "py" =>  '',
                "user_days" => $noOfday ?? '',
            ]); 
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

        $validated = null;
        if($request['accommodation_type'] === 'Day Tour'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
                'payment_method' => ['required'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'date_equals' => 'Choose only one day (Day Tour)',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Overnight'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
                'payment_method' => ['required'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Tour)',
                'check_out.after_or_equal' => 'Choose within 2 day and above (Overnight)',
    
            ]);
        }
        elseif($request['accommodation_type'] === 'Room Only'){
            $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(2)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
                'accommodation_type' => ['required'],
                'pax' => ['required', 'numeric', 'min:1', 'max:'.(string)RoomList::max('max_occupancy')],
                'payment_method' => ['required'],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'check_out.unique' => 'Sorry, this date is not available',
                'required' => 'Need fill up first',
                'pax.exists' => 'Sorry, this guest you choose is not available (Room Capacity)',     
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
            $reservationInfo = [
                "cin" =>   $validated['check_in'] ?? '',
                "cout" => $validated['check_out'] ?? '',
                "at" => $validated['accommodation_type'] ??  '',
                "px" => $validated['pax'] ?? '',
                "py" => $validated['payment_method'] ?? '',
            ];
            $reservationInfo = encryptedArray($reservationInfo);
            if(session()->has('rinfo')) foreach($reservationInfo as $key => $item) session('rinfo')[$key] = $reservationInfo[$key]; 
            else session(['rinfo' => $reservationInfo]);
            return redirect()->route('reservation.details');

        }
        else{
            session(['ck' => false]);
            return back()->with('error',"Choose the Accommodation type")->withInput();
        }

        if($validated){
            $getParamStep1 = [
                "cin" =>  encrypt($validated['check_in']),
                "cout" =>  encrypt($validated['check_out']),
                "px" =>  encrypt($validated['pax']),
                "at" =>  encrypt($validated['accommodation_type']),
                "py" =>  encrypt($validated['payment_method']),
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
                "at" => $request->has('at') != null ? request('at') : '',
                "px" => $request->has('px') != null ? request('px'): '',
                "py" =>  $request->has('py') != null ? request('py') : '',
            ];
            return redirect()->route('reservation.choose', [Arr::query($getParam), '#tour-menu'])
            ->with('error', 'You have not selected anything in the cart yet. Please make a selection first.');
        }
        $validate = $validate->validate();
        $reservationInfo = [
            "cin" =>  $request->has('cin') != null ? request('cin') : '',
            "cout" => $request->has('cout') != null ? request('cout') : '',
            "at" => $request->has('at') != null ? request('at'): '',
            "px" => $request->has('px') != null ? request('px'): '',
            "py" =>  $request->has('py') != null? request('py') : '',
            "tm" => encrypt(implode(',', $validate['tour_menu'])),
        ];
        if(session()->has('rinfo')) foreach($reservationInfo as $key => $item) session('rinfo')[$key] = $reservationInfo[$key]; 
        else session(['rinfo' => $reservationInfo]);
       
        return redirect()->route('reservation.details');

    }
    public function details(Request $request){
        return view('reservation.step3');
    }
    public function detailsStore(Request $request){
        $user = User::findOrFail(auth('web')->user()->id);
        $validated =[
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'age' => $user->age(),
            'country' => $user->country,
            'nationality' => $user->nationality,
            'contact' => $user->contact,
            'email' => $user->email,
        ];
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
        if($uinfo['at'] !== 'Room Only' && $uinfo['tm'] != null){
            foreach(explode(',', $uinfo['tm']) as $key => $item){
                $user_menu[$key]['id'] = TourMenu::findOrFail($item[$key])->id;
                $user_menu[$key]['price'] = TourMenu::findOrFail($item[$key])->price;
                $user_menu[$key]['title'] = TourMenu::findOrFail($item[$key])->tourMenu->title;
                $user_menu[$key]['type'] = TourMenu::findOrFail($item[$key])->type;
                $user_menu[$key]['pax'] = TourMenu::findOrFail($item[$key])->pax . ' pax';
            }
        }
        return view('reservation.step4', [
            'user_menu' => $user_menu ?? '',
            'uinfo' => $uinfo,
        ]);
    }
    public function storeReservation(Request $request){
        $uinfo = decryptedArray(session()->get('rinfo')) ?? '';
        $reserve_info = null;
        if($uinfo['at'] !== 'Room Only'){
            $validated = $request->validate(['amount.*' => ['required']]);
            $total = 0;
            foreach($validated['amount'] as $amount) $total += (double)explode('-', $amount)[1];
            if($validated){
                $reserve_info = Reservation::create([
                    'user_id' => auth('web')->user()->id,
                    'pax' => $uinfo['px'] ?? '',
                    'age' => $uinfo['age'] ?? '',
                    'menu' => $uinfo['tm'] ?? '',
                    'payment_method' => $uinfo['py'] ?? '',
                    'accommodation_type' => $uinfo['at'] ?? '',
                    'check_in' => $uinfo['cin'] ?? '',
                    'check_out' => $uinfo['cout'] ?? '',
                    'amount' => implode(', ', $validated['amount']) ?? '',
                    'total' => $total ?? '',
                ]);
            }
        }
        else{
            $reserve_info = Reservation::create([
                'user_id' => auth('web')->user()->id,
                'pax' => $uinfo['px'] ?? '',
                'age' => $uinfo['age'] ?? '',
                'check_in' => $uinfo['cin'] ?? '',
                'check_out' => $uinfo['cout'] ?? '',
                'accommodation_type' => $uinfo['at'] ?? '',
            ]);
        }
        if(env('TELEGRAM_USERNAME') != null){
            $text = 
            "New Reservation!\n" .
            "Name: ". $reserve_info->userReservation->first_name . " " . $reserve_info->userReservation->last_name ."\n" . 
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
            Telegram::sendMessage([
                'chat_id' => getChatIdByUsername(env('TELEGRAM_USERNAME') ?? 'joewmar'),
                'parse_mode' => 'HTML',
                'text' => $text,
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            ]);
        }
        $details = [
            'title' => 'Reservation Complete',
            'body' => 'Your Reservation are done, We just send email for the approve or disapprove confirmation'
        ];
        Mail::to($reserve_info->userReservation->email)->send(new ReservationMail($details, 'reservation.mail'));
        session()->forget('rinfo');
        session()->forget('ck');
        return redirect()->route('reservation.done');
    }
}
