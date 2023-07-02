<?php

namespace App\Http\Controllers;

use App\Models\TourMenu;
use App\Models\TourMenuList;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function date(Request $request){
        return view('reservation.step1', ['cin' => $request['check_in'], 'cout' => $request['check_out']]);
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

        $validator = Validator::make($request->all('check_in','check_out'), [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
        ], [
            'unique' => 'Sorry, this date is not available',
            'after' => 'The :attribute was chose',

        ]);
        
        if ($validator->fails()) {            
            return redirect()->route('reservation.date')
            ->withErrors($validator)
            ->withInput();
        }
        else{
            $validated = $validator->validated();
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

        $validator = Validator::make($request->all('check_in','check_out'), [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', Rule::unique('reservations', 'check_in'), Rule::unique('reservations', 'check_out')],
            'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
        ], [
            'unique' => 'Sorry, this date is not available',
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
        );
        // dd(Arr::query($paramsDates));

        if(Auth::check()){
            return redirect()->route('reservation.choose', Arr::query($paramsDates));
        }
        else{
            return redirect()->route('login', Arr::query($paramsDates))->with('info', 'You need to login first');
        }

    }
    // Choose Form
    public function choose(Request $request){

        if (checkAllArrayValue([$request['cin'], $request['cout']]) === true) 
            return view('reservation.step1');

        if($request->has(['cin', 'cout'])){
            $noOfday = checkDiffDates(decrypt(request('cin')), decrypt(request('cout')));
        }

        return view('reservation.step2', [
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
            'tour_menus' => TourMenu::all(), 
            "cin" =>  $request->has('cin') != null ? decrypt(request('cin')) : '',
            "cout" => $request->has('cout') != null ? decrypt(request('cout')) : '',
            "px" => $request->has('px') != null ? decrypt(request('px')) : '',
            "at" =>   $request->has('at') != null ? decrypt(request('px')) : '',
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
                'accommodation_type' => 'required',
                'payment_method' => 'required',
            ], 
            [
                'after' => 'The :attribute was chose',
                'pax.required' => 'The number of guest are required',
            ]
        );
        if($validated){
            $getParamStep1 = [
                "cin" =>  encrypt($validated['check_in'] === '' ? null : $validated['check_in']),
                "cout" =>  encrypt($validated['check_out'] === '' ? null : $validated['check_out']),
                "px" =>  encrypt($validated['pax'] === '' ? null : $validated['pax']),
                "at" =>  encrypt($validated['accommodation_type'] === '' ? null : $validated['accommodation_type']),
                "py" =>  encrypt($validated['payment_method'] === '' ? null : $validated['payment_method']),
            ];
            return redirect()->route('reservation.choose', [Arr::query($getParamStep1), '#tour-menu']);
            
        }

    }
    // public function chooseCheckAll(Request $request){

    //     // // $check = TourMenu::distinct()->get('category');
    //     // // dd($check);
    //     // if ($request->session()->has(['reservation'])) {
    //     //     return view('reservation.step2', ['tour_lists' => TourMenuList::all(), 'tour_category' => TourMenuList::distinct()->get('category'), 'tour_menus' => TourMenu::all()]);
    //     // }
    //     // else{
    //     //     return view('reservation.step1');
    //     // }
    //     dd($request->all());
    // }
}
