<?php

namespace App\Http\Controllers;

use App\Models\TourMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    // private $reservationInfo = [];

    public function date (Request $request){
        return view('reservation.step1');
    }
    public function dateCheck(Request $request){
       
        $validator = Validator::make($request->all('check_in','check_out'), [
            'check_in' => ['required', Rule::unique('reservations', 'check_in')],
            'check_out' => ['required'],
        ], [
            'unique' => 'Sorry, this date is not available'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('reservation.date')
            ->withErrors($validator)
            ->withInput();
        }
        $validated = $validator->validated();

        if(str_contains($validated['check_in'], 'to') && $validated){
            $dateSeperate = explode('to', $validated['check_in']);
            $validated['check_in'] = trim($dateSeperate[0]);
            $validated['check_out'] =trim($dateSeperate[1]);
        }
        session(['reservation.check_in' => $validated['check_in']]);
        session(['reservation.check_out' => $validated['check_out']]);

        // $data = $request->session()->all();
        // dd($data);

        if(Auth::check()){
            return redirect()->route('reservation.choose');
        }
        else{
            return redirect()->route('login')->with('info', 'You need login first');
        }

    }
    public function choose(Request $request){

        if ($request->session()->has(['reservation'])) {
            return view('reservation.step2', ['tour_menus' => TourMenu::all(), 'tour_category' => TourMenu::distinct()->get(['category'])]);
        }
        else{
            return view('reservation.step1');
        }
    }
}
