<?php

namespace App\Http\Controllers;

use App\Models\TourMenu;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class EditUserReserveController extends Controller
{
    private function reservationValidation(Request $request, bool $haveTpx = false, $havePy = false, $id){
        if($request->has('accommodation_type') && $request['accommodation_type'] === 'Day Tour') $request['check_out'] = $request['check_in'];

        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');

        if(checkAvailRooms($request['pax'] ?? 0, $request['check_in'], $request['check_out'], $id) && !empty($request['pax'])) {
            return back()->withErrors(['check_in' => 'Sorry this date was not available for rooms'])->withInput($request->input());
        }
        $web_contents = WebContent::all()->first();
        if(isset($web_contents->from) && isset($web_contents->to)){
            if(Carbon::createFromFormat('Y-m-d', $request['check_in'])->timestamp >= Carbon::createFromFormat('Y-m-d', $web_contents->from)->timestamp && Carbon::createFromFormat('Y-m-d', $request['check_in'])->timestamp <= Carbon::createFromFormat('Y-m-d', $web_contents->to)->timestamp) {
                 return back()->with('error', 'Sorry, this date cannot be allowed due ' . $web_contents->reason)->withInput($request->input());
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
            return back()->withErrors(['accommodation_type' => 'Choose the Accommodation type'])->withInput($validator->getData());
        }
        if ($validator->fails()) {            
            return back()
            ->withErrors($validator)
            ->withInput($validator->getData());

        }

        $validator = $validator->validated();
        return $validator;
    }
    public function step1($id){
        $rlist = Reservation::findOrFail(decrypt($id));
        $dateInfo = [
            'at' =>    $rlist->accommodation_type,
            'cin' =>   $rlist->check_in,
            'px' =>  $rlist->pax,
            'cout' =>  $rlist->check_out,
        ];
        if(session()->has('erinfo')){
            $dateInfo = [
                'at' => isset(session('erinfo')['at']) ? decrypt(session('erinfo')['at']) : old('accommodation_type'),
                'cin' => isset(session('erinfo')['cin']) ? decrypt(session('erinfo')['cin']) : old('check_in') ?? Carbon::now()->format('Y-m-d'),
                'px' =>   isset(session('erinfo')['px']) ? decrypt(session('erinfo')['px']) : old('pax'),
                'cout' => isset(session('erinfo')['cout']) ? decrypt(session('erinfo')['cout']) : old('check_out'),
            ];
        }
        return view('users.reservation.edit.step1', ['dateInfo' => $dateInfo, 'id' => $id]);
    }
    public function storeStep1(Request $request, $id){
        $rlist = Reservation::findOrFail(decrypt($id));
        $validated = $this->reservationValidation($request, false, false, $rlist->id);
        if(!is_array($validated)) return $validated;
        $dates = [
            "cin" =>  encrypt($validated['check_in']),
            "cout" =>  encrypt($validated['check_out']),
            "at" =>  encrypt($validated['accommodation_type']),
            "px" =>   encrypt($validated['pax']),
        ];
        // $this->replaceRInfo($paramsDates);
        if(session()->has('erinfo')){
            $erinfo = session('erinfo');
            if(isset($erinfo['oat']) && $validated['accommodation_type'] != decrypt($erinfo['oat'])) $erinfo['oat'] = $erinfo['at'];
            $erinfo['cin'] =  $dates['cin'];
            $erinfo['cout'] =  $dates['cout'];
            $erinfo['at'] =  $dates['at'];
            $erinfo['px'] =  $dates['px'];


        }
        else{
            $erinfo['cin'] =  $dates['cin'];
            $erinfo['cout'] =  $dates['cout'];
            $erinfo['at'] =  $dates['at'];
            $erinfo['px'] =  $dates['px'];
            if($validated['accommodation_type'] != $rlist->accommodation_type) $erinfo['oat'] = encrypt($rlist->accommodation_type);
        }
        // dd($erinfo);
        session(['erinfo' => $erinfo]);
        unset($validated);
        return redirect()->route('user.reservation.edit.step2', $id);

    }
    public function step2(Request $request, $id){
        $rlist = Reservation::findOrFail(decrypt($id));
        if(!session()->has('erinfo')) return route('user.reservation.edit.step1', encrypt($rlist->id));
        $noOfday = 100;
        $erinfo = decryptedArray(session('erinfo'));
        $TourInfo = [
            "cin" => $erinfo['cin'],
            "cout" =>  $erinfo['cout'],
            "px" =>  $erinfo['px'],
            "at" =>  $erinfo['at'],
            "tpx" =>  $erinfo['tpx'] ?? $rlist->tour_pax,
            "py" =>  $erinfo['py'] ?? $rlist->payment_method,
            "otpx" => $erinfo['otpx'] ?? 0,
            "oat" => $erinfo['oat'] ?? $erinfo['at'],
            "tamount" => 0,
        ];
        $tourListCart = [];
        // dd($TourInfo);
        if(isset($decrypted['tm'])) $tours = $decrypted['tm'];
        else {
            $count = 0;
            foreach($rlist->transaction ?? [] as $key => $item){
                if(strpos($key, 'tm') !== false){
                    $tour = TourMenu::withTrashed()->find($item['id']);
                    if($tour){
                        $tours[$count]['id'] = $tour->id;
                        $tours[$count]['price'] = $tour->price;
                        $tours[$count]['title'] = $tour->tourMenu->title;
                        $tours[$count]['type'] = $tour->type;
                        $tours[$count]['pax'] = $tour->pax;
                    }
                }
                $count++;
            }
        }
        foreach($tours ?? [] as $key => $item){
            $tourListCart[$key]['id'] = $item['id'];
            $tourListCart[$key]['price'] = $item['price'];
            $tourListCart[$key]['title'] = $item['title'];
            $tourListCart[$key]['type'] = $item['type'];
            $tourListCart[$key]['pax'] = $item['pax'];
            $TourInfo['tamount'] += $item['price'] * (int)$TourInfo['tpx'];
        }
        $noOfday = getNoDays($TourInfo['cin'], $TourInfo['cout']);
        // if($TourInfo['tpx'] != $rlist->tour_pax) $tourListCart = [];
        if($TourInfo['at'] == 'Room Only' || $TourInfo['at'] != $TourInfo['oat'] || $TourInfo['tpx'] != $TourInfo['otpx']) $tourListCart = [];

        return view('users.reservation.edit.step2', [
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
            'TourInfo' => $TourInfo ,
            'tourListCart' => $tourListCart ,
            'TourInfoEncrypted' => $TourInfoEncrypted ?? [],
            "user_days" => $noOfday,
            "id" => $id,
        ]); 


    }
    public function storeStep21(Request $request, $id){
        $rlist = Reservation::findOrFail(decrypt($id));
        if(!session()->has('erinfo')) return route('user.reservation.edit.step1', encrypt($r_list->id));
        $validated = $this->reservationValidation($request, true, true, $rlist->id);
        if(!is_array($validated)) return $validated;
        $step2 = [
            "cin" =>  encrypt($validated['check_in']),
            "cout" =>  encrypt($validated['check_out']),
            "at" =>  encrypt($validated['accommodation_type']),
            "px" =>   encrypt($validated['pax']),
            "py" => encrypt($validated['payment_method']),
        ];
        if($validated['accommodation_type'] != 'Room Only') $step2["tpx"] = encrypt($validated['tour_pax']);
        $erinfo = session('erinfo');
        if($validated['accommodation_type'] == 'Room Only') unset($erinfo['tm']);
        if($validated['accommodation_type'] != decrypt($erinfo['at'])) $erinfo['oat'] = $erinfo['at'];
        $erinfo['cin'] =  $step2['cin'];
        $erinfo['cout'] =  $step2['cout'];
        $erinfo['at'] =  $step2['at'];
        $erinfo['px'] =  $step2['px'];
        if($validated['accommodation_type'] != 'Room Only'){
            $erinfo['otpx'] =  $erinfo['tpx'] ?? $step2['tpx'];
            $erinfo['tpx'] = $step2['tpx'];
        }
        $erinfo['py'] =  $step2['py'];
        session(['erinfo' => $erinfo]);

        unset($validated);
        if(decrypt($erinfo['at']) == 'Room Only') return redirect()->route('user.reservation.edit.step3', $id);
        else return redirect()->route('user.reservation.edit.step2', ['id' =>  $id, Arr::query(['tpx' => $step2['tpx'], 'py' => $step2['py']])]);

    }
    public function storeStep22(Request $request, $id){
        $rlist = Reservation::findOrFail(decrypt($id));
        if(!session()->has('erinfo')) return route('user.reservation.edit.step1', encrypt($rlist->id));
        $validated = Validator::make($request->all('tour_menu'), [
            'tour_menu.*' => 'required',
        ]);
        $erinfo = decryptedArray(session('erinfo'));
        if(empty($request['tour_menu']) || $validated->fails()){
            return back()->with('error', 'You have not selected anything in the cart yet. Please make a selection first.');
        }
        $validated = $validated->validate();
        $erinfo = session('erinfo');
        $erinfo['tm'] = encrypt($validated['tour_menu']);

        session(['erinfo' => $erinfo]);
        unset($validated);
        
        return redirect()->route('user.reservation.edit.step3', $id);

    }
    public function step3($id){
        $rlist = Reservation::findOrFail(decrypt($id));
        if(!session()->has('erinfo')) return route('user.reservation.edit.step1', encrypt($rlist->id));
        $uinfo = decryptedArray(session('erinfo')) ?? '';
        $rlist = Reservation::findOrFail(decrypt($id));

        if(!session()->has('erinfo')) return route('user.reservation.edit.step1', encrypt($rlist->id));
        $uinfo = decryptedArray(session('erinfo'));
        if(empty($uinfo['tm']) && $uinfo['at'] !== 'Room Only') return redirect()->route('user.reservation.edit.step2', ['id' => $id, Arr::query(['tpx' => encrypt($uinfo['tpx']), 'py' => $uinfo['py']]), '#tourMenu'])->with('info', 'Your Tour Menu was empty');
        $user_menu = [];
        if(isset($uinfo['tm'])){
            foreach($uinfo['tm'] as $key => $item){
                $tour = TourMenu::withTrashed()->findOrFail($item);
                if($tour){
                    $user_menu[$key]['id'] = $tour->id;
                    $user_menu[$key]['price'] = $tour->price;
                    $user_menu[$key]['amount'] = (int)$uinfo['tpx'] * $tour->price;
    
                    $user_menu[$key]['title'] = $tour->tourMenu->title;
                    $user_menu[$key]['type'] = $tour->type;
                    $user_menu[$key]['pax'] = $tour->pax . ' guest';
                    $user_menu[$key]['tour_pax'] = $uinfo['tpx'] . ' guest';
                }
            }
        }
        return view('users.reservation.edit.step4', [
            'user_menu' => $user_menu,
            'uinfo' => $uinfo,
            'id' => $id,
        ]);
    }
    public function update($id){
        if(!session()->has('erinfo')) return route('user.reservation.edit.step1', encrypt($rlist->id));
        $rlist = Reservation::findOrFail(decrypt($id));
        $erinfo = decryptedArray(session('erinfo'));
        $transaction = $rlist->transaction;
        foreach($transaction ?? [] as $key => $item) {
            if(str($key, 'tm') !== false) unset($transaction[$key]);
        }
        foreach($erinfo['tm'] ?? [] as $item){
            $tour = TourMenu::withTrashed()->find($item);
            if($tour){
                $transaction['tm'.$item] = [
                'id' => $tour->id,
                'title' => $tour->tourMenu->title,
                'type' => $tour->type,
                'pax' => $tour->pax,
                'price' => (double)$tour->price,
                'created' => now('Asia/Manila')->format('YmdHis'),
                'tpx' => $erinfo['tpx'],
                'used' => false,
                ];
            }
        }
        $updated = $rlist->update([
            'check_in' => $erinfo['cin'],
            'check_out' => $erinfo['cout'],
            'accommodation_type' => $erinfo['at'],
            'pax' => $erinfo['px'],
            'tour_pax' => $erinfo['tpx'] ?? null,
            'payment_method' => $erinfo['py'],
            'transaction' => $transaction,
        ]);
        if($updated) return redirect()->route('user.reservation.show', $id)->with('success', 'Reservation changed successfully');
    }
}
