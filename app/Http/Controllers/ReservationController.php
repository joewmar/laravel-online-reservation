<?php

namespace App\Http\Controllers;

use App\Models\TourMenu;
use App\Models\TourMenuList;
use App\Models\User;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function date(Request $request){
        $dateList = [];
        if(session()->has('rinfo')){
            $dateList = decryptedArray(session('rinfo'));
            return view('reservation.step1', ['cin' => $dateList['cin'], 'cout' => $dateList['cout'], 'px' => $dateList['px']]);
        }
        return view('reservation.step1', ['cin' => $request['check_in'], 'cout' => $request['check_out'], 'px' => $request['pax']]);
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

        $validator = Validator::make($request->all('check_in','check_out', 'pax'), [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in'], Rule::unique('reservations', 'check_out')],
            'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
        ], [
            'check_in.unique' => 'Sorry, this date is not available',
            'check_out.unique' => 'Sorry, this date is not available',
            'pax.required' => 'Need fill up first',
            'pax.exists' => 'Sorry, this guest you choose is not available',
            'after' => 'The :attribute was chose',

        ]);

        
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

        $validator = Validator::make($request->all('check_in','check_out', 'pax'), [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in'], Rule::unique('reservations', 'check_out')],
            'pax' => ['required', 'numeric', 'min:1', Rule::exists('tour_menus', 'pax')],
        ], [
            'check_in.unique' => 'Sorry, this date is not available',
            'check_out.unique' => 'Sorry, this date is not available',
            'pax.required' => 'Need fill up first',
            'pax.exists' => 'Sorry, this guest you choose is not available',
            'after' => 'The :attribute was chose',

        ]);

        if ($validator->fails()) {
            return redirect()->route('reservation.date')
            ->withErrors($validator)
            ->withInput();
        }
        $validated = $validator->validated();

        if(str_contains($validated['check_in'], 'to') && $validator->validated()){
            $dateSeperate = explode('to', $validated['check_in']);
            $validated['check_in'] = trim($dateSeperate[0]);
            $validated['check_out'] = trim ($dateSeperate[1]);
        }
        
        $paramsDates = array(
            "cin" =>  $validated['check_in'] === '' ? null : encrypt($validated['check_in']),
            "cout" =>  $validated['check_out'] === '' ? null : encrypt($validated['check_out']),
            "px" =>  $validated['pax'] === '' ? null : encrypt($validated['pax']),
        );
        if(session()->has('rinfo')){
            $paramsDates = array(
                "cin" =>  session('rinfo')['cin'] ?? '',
                "cout" =>   session('rinfo')['cout'] ?? '',
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
                "py" =>  $dateList['py'],
                'cmenu' => $chooseMenu,
                "user_days" => isset($noOfday) ? $noOfday : '',
            ]); 
            dd($dateList['py']);
        }
        
        if($request->has(['cin', 'cout', 'px'])){
            $noOfday = checkDiffDates(decrypt(request('cin')), decrypt(request('cout')));
        }
        return view('reservation.step2', [
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
            'tour_menus' => TourMenu::all(), 
            "cin" =>  $request->has('cin') != null ? decrypt(request('cin')) : '',
            "cout" => $request->has('cout') != null ? decrypt(request('cout')) : '',
            "px" => $request->has('px') != null ? decrypt(request('px')) : '',
            "py" =>  $request->has('py') != null ? decrypt(request('px')) : '',
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

        $validated = $request->validate([
                'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
                'pax' => 'required', 'min:1', 'numeric',
                'payment_method' => 'required',
            ], 
            [
                'check_out.after' => 'The :attribute was chose',
                'pax.required' => 'The number of guest are required',
            ]
        );
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
}
