<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Room;
use App\Models\System;
use App\Models\Archive;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class MyReservationController extends Controller
{   
    private function systemNotification($text, $link = null){
        $systems = System::whereIn('type', [0, 1])->get();
        $keyboard = null;
        if(isset($link)){
            $keyboard = [
                [
                    ['text' => 'View', 'url' => $link],
                ],
            ];
        }
        foreach($systems as $system){
            if(isset($system->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID') ?? $system->telegram_chatID, $text, $keyboard));
        }
        Notification::send($systems, new SystemNotification(Str::limit($text, 10, '...'), $text, route('system.notifications')));
    }

    public function index(Request $request){
        $reservation = Reservation::where('user_id', auth('web')->user()->id)->where('status', 0)->latest()->paginate(5) ?? [];
        if($request['tab'] == 'confirmed'){
            $reservation = Reservation::where('user_id', auth('web')->user()->id)->where('status', 1)->latest()->paginate(5) ?? [];
        }
        if($request['tab'] == 'cin'){
            $reservation = Reservation::where('user_id', auth('web')->user()->id)->where('status', 2)->latest()->paginate(5) ?? [];
        }
        if($request['tab'] == 'cout'){
            $reservation = Reservation::where('user_id', auth('web')->user()->id)->where('status', 3)->latest()->paginate(5) ?? [];
        }
        if($request['tab'] == 'reshedule'){
            $reservation = Reservation::where(function ($query) {
                $query->where('user_id', auth('web')->user()->id)
                    ->where(function ($subQuery) {
                        $subQuery->where('status', 7)
                            ->orWhere('message->reschedule->prev_status', 4);
                    });
            })->latest()->paginate(5);  

        }
        if($request['tab'] == 'cancel'){
            $reservation = Reservation::where(function ($query) {
                $query->where('user_id', auth('web')->user()->id)
                    ->where(function ($subQuery) {
                        $subQuery->where('status', 5)
                            ->orWhere('status', 8);
                    });
            })->latest()->paginate(5);            

        }

        if($request['tab'] == 'all'){
            $reservation = Reservation::where('user_id', auth('web')->user()->id)->latest()->paginate(5) ?? [];
        }
        return view('users.reservation.index', ['activeNav' => 'My Reservation', 'reservation' => $reservation]);
    }
    public function show($id) {
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = [];
        $tour_menu = [];
        $other_addons = [];
        $rate = [];
        $total = 0;
        if($reservation->roomid){
            foreach($reservation->roomid as $item){
                $room = Room::find($item);
                if($room) $rooms[] = 'Room No.' . $room->room_no . ' ('.$room->room->name.')';
                else $rooms[] = 'Room Data Missing';

            }
        }
        $count = 0;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menu[$count]['title'] = $item['title'] . ' '.$item['type'];
                $tour_menu[$count]['price'] = $item['price'];
                $tour_menu[$count]['tpx'] = $item['tpx'];
                $tour_menu[$count]['used'] = $item['used'];
                $tour_menu[$count]['amount'] = $item['amount'];
                $tour_menu[$count]['key'] = $key;

                // 'id' => $tour_menu->id,
                // 'title' => $tour_menu->tourMenu->title,
                // 'type' => $tour_menu->type,
                // 'pax' => $tour_menu->pax,
                // 'price' => (double)$tour_menu->price,
                // 'created' => now('Asia/Manila')->format('YmdHis'),
                // 'tpx' => $uinfo['tpx'],
            }

            // Rate
            if (strpos($key, 'rid') !== false) {
                // dd($item['amount']);
                $rate['name'] = $item['title'];;
                $rate['price'] = $item['price'];
                $rate['person'] = $item['person'];
                $rate['amount'] = $item['amount'];
                if(isset($item['discounted'])){
                    $rate['discounted'] = $item['discounted'];
                }
                if(isset($item['orig_amount'])){
                    $rate['orig_amount'] = $item['orig_amount'];
                }
            }
            if (strpos($key, 'OA') !== false) {
                foreach($item as $key => $value){
                    $other_addons[$count]['title'] = $value['title'];
                    $other_addons[$count]['pcs'] = $value['pcs'];
                    $other_addons[$count]['price'] = $value['price'];
                    $other_addons[$count]['amount'] = $value['amount'];
                }
            }
            if (strpos($key, 'TA') !== false) {
                foreach($item as $created => $value){
                    $tour_menu[$count]['title'] = $value['title'] . ' '.$value['type'];
                    $tour_menu[$count]['tpx'] = $value['tpx'];
                    $tour_menu[$count]['price'] = $value['price'];
                    $tour_menu[$count]['used'] = $value['used'];
                    $tour_menu[$count]['key'] = $key . '(_)' . $created;
                    // dd($item);
                    $tour_menu[$count]['amount'] = $value['amount'];
                }
            }
            $count++;
        }
        unset($count);
        return view('users.reservation.show',  ['activeNav' => 'My Reservation', 'r_list' => $reservation, 'menu' => $tour_menu, 'rooms' => implode(',', $rooms), 'rate' => $rate, 'total' => $total, 'other_addons' => $other_addons]);
    }
    public function showOnlinePayment($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        return view('users.reservation.show-payment',  ['activeNav' => 'My Reservation', 'r_list' => $reservation]);
    }
    public function cancel(Request $request, $id) {
        $validate = Validator::make($request->all('cancel_message'), [
            'cancel_message' => ['required'],
        ], ['required' => 'Need to input the reason']);
        if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
        $validate = $validate->validate();
        $reservation = Reservation::findOrFail(decrypt($id));
        $systemUser = System::all()->where('type', '>=', 0)->where('type', '<=', 1);
        $messages = $reservation->message;
        $messages['cancel'] = [
            'prev_status' => $reservation->status,
            'message' =>  $validate['cancel_message'],
        ];
        $reservation->message = $messages;
        $reservation->status = 8;
        $reservation->save();
        $text = 
        "Cancel Request Reservation!\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Country: " . $reservation->userReservation->country ."\n" . 
        "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservation->check_in)->setTimezone('Asia/Manila')->format('F j, Y') ."\n" . 
        "Check-out: " . Carbon::createFromFormat('Y-m-d', $reservation->check_out)->setTimezone('Asia/Manila')->format('F j, Y') ."\n" . 
        "Pax: " . $reservation->pax ."\n" . 
        "Why to Cancel: " .$validate['cancel_message']; 
        $this->systemNotification($text , route('system.reservation.show.cancel', $id));
        return redirect()->route('user.reservation.home')->with('success', 'Cancel Request was succesfull to send. Just Wait Send Email or any Contact for Approval Request');
    }
    public function reschedule(Request $request, $id) {
        $reservation = Reservation::findOrFail(decrypt( $id));
        if($reservation->accommodation_type == 'Day Tour') $request['check_out'] = $request['check_in'];
        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');

        if(($request['check_in'] === $reservation->check_in && $request['check_out'] === $reservation->check_out)){
            return back()->with('error', 'Your choose date does not change at all')->withInput($request->all());
        }
        $noDays = getNoDays($request['check_in'], $request['check_out']);
        $validator = null;
        if($reservation->accommodation_type == 'Day Tour'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first (:attribute)',
                'date_equals' => 'Choose only one day (Day Tour)',
            ]);
        }
        elseif($reservation->accommodation_type == 'Overnight'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Select date with 2 to 3 days',
                'required' => 'Need fill up first (:attribute)',
                'check_out.after_or_equal' => 'Select within 2 or 3 days above (Overnight)',
            ]);
        }
        elseif($reservation->accommodation_type == 'Room Only'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'required' => 'Need fill up first',
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
        }

        if ($validator->fails()) return back()->with('error', $validator->errors()->all())->withInput($request->all());
        $validator = $validator->validate();
        
        $messages = $reservation->message;
        $messages['reschedule'] = [
            'message' => $validator['reschedule_message'],
            'check_in' => $validator['check_in'],
            'check_out' => $validator['check_out'],
            'prev_status' => $reservation->status,
        ];
        
        $reservation->message =  $messages;
        $reservation->status =  7;
        $reservation->save();

        $text = 
        "Reschedule Request !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Country: " . $reservation->userReservation->country ."\n" . 
        "Pax: " . $reservation->pax ."\n" . 
        "New Check-in: " . Carbon::createFromFormat('Y-m-d', $validator['check_in'])->setTimezone('Asia/Manila')->format('F j, Y') ."\n" . 
        "New Check-out: " . Carbon::createFromFormat('Y-m-d', $validator['check_out'])->setTimezone('Asia/Manila')->format('F j, Y') ."\n" . 
        "Reason: " . $validator['reschedule_message'];
        $this->systemNotification($text , route('system.reservation.show.cancel', $id));
        return redirect()->route('user.reservation.home')->with('success', 'Reschedule Request was succesfull to send. Just Wait Send Email or any Contact for Approval Request');
        
    }
    public function receipt($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        abort_if($reservation->status != 3, 404); 
        $tour_menu = [];
        $other_addons = [];
        $tour_addons = [];
        $rooms = [];
        $contacts = WebContent::all()->first()->contact['main'] ?? [];
        // Rooms
        foreach($reservation->roomid  ?? [] as $item){
            $rooms[$item]['no'] = Room::findOrFail($item)->room_no;
            $rooms[$item]['name'] = Room::findOrFail($item)->room->name;
        }
        
        $count = 0;
        foreach($reservation->transaction as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only' && $item['used'] == true) {
                // $tour_menuID = (int)str_replace('tm','', $key);
                $tour_menu[$count]['title'] = $item['title'];
                $tour_menu[$count]['tpx'] = $item['tpx'];
                $tour_menu[$count]['price'] = $item['price'];
                $tour_menu[$count]['amount'] = $item['amount'];
            }
            // Rate
            if (strpos($key, 'rid') !== false) {
                $rateID = (int)str_replace('rid','', $key);
                $rate['name'] = RoomRate::find($rateID)->name;
                $rate['price'] = $reservation->transaction['rid'.$rateID]['price'];
                $rate['person'] = $reservation->transaction['rid'.$rateID]['person'];
                if($reservation->countSenior() > 0) $rate['orig_amount'] = $reservation->getRoomAmount(true) ;
                $rate['amount'] = $reservation->transaction['rid'.$rateID]['amount'];
            }
            if (strpos($key, 'OA') !== false) {
                if(is_array($item)){
                    foreach($item as $key => $value){
                        $other_addons[$count.'-'.$key]['title'] = $value['title'];
                        $other_addons[$count.'-'.$key]['pcs'] = $value['pcs'];
                        $other_addons[$count.'-'.$key]['price'] = $value['price'];
                        $other_addons[$count.'-'.$key]['amount'] = $value['amount'];
                    }
                }
                else{
                    $other_addons[$count]['title'] = $item['title'];
                    $other_addons[$count]['pcs'] = $item['pcs'];
                    $other_addons[$count]['price'] = $item['price'];
                    $other_addons[$count]['amount'] = $item['amount'];
                }
                
            }
            if (strpos($key, 'TA') !== false) {
                if(is_array($item)){
                    foreach($item as $key => $value){
                        $tour_addons[$count.'-'.$key]['title'] = $value['title'];
                        $tour_addons[$count.'-'.$key]['tpx'] = $value['tpx'];
                        $tour_addons[$count.'-'.$key]['price'] = $value['price'];
                        $tour_addons[$count.'-'.$key]['amount'] = $value['amount'];
                    }
                }
                else{
                    $tour_addons[$count]['title'] = $item['title'];
                    $tour_addons[$count]['tpx'] = $value['tpx'];
                    $tour_addons[$count]['price'] = $item['price'];
                    $tour_addons[$count]['amount'] = $item['amount'];
                }
            }
            $count++;
        }
        unset($count);
        // instantiate and use the dompdf class
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml(view('reservation.receipt',  ['r_list' => $reservation, 'menu' => $tour_menu, 'tour_addons' => $tour_addons, 'other_addons' => $other_addons, 'rate' => $rate ?? null, 'rooms' => $rooms ?? [], 'contacts' => $contacts]));

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('Legal');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('AA'.str_replace('aar-','',$reservation->id).'.pdf', ['Attachment' => false]);
        // return view('reservation.receipt',  ['r_list' => $reservation, 'menu' => $tour_menu, 'tour_addons' => $tour_addons, 'other_addons' => $other_addons, 'rate' => $rate ?? null, 'rooms' => $rooms ?? [], 'contacts' => $contacts]);
    }
    public function updateRequest(Request $request, $id){
        // dd($request->all());
        $validate = Validator::make($request->all('service_message'), [
            'service_message' => ['required'],
        ], ['required' => 'Required (Request Service)']);
        if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
        $validate = $validate->validate();
        $reservation = Reservation::findOrFail(decrypt($id));
        $messages = $reservation->message;
        $messages['request'] =   $validate['service_message'];
        $reservation->message = $messages;
        $reservation->save();
        return redirect()->route('user.reservation.show', $id)->with('success', 'Service Request Message was updated');
        
    }
    public function updateCancel(Request $request, $id){
        // dd($request->all());
        $validate = Validator::make($request->all('cancel_message'), [
            'cancel_message' => ['required'],
        ], ['required' => 'Need to input the reason']);
        if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
        $validate = $validate->validate();
        $reservation = Reservation::findOrFail(decrypt($id));
        $messages = $reservation->message;
        $messages['cancel'] = [
            'message' =>  $validate['cancel_message'],
        ];
        $reservation->message = $messages;
        $reservation->save();
        return redirect()->route('user.reservation.show', $id)->with('success', 'Cancel Request Message was updated');
    }
    public function updateReschedule(Request $request, $id){
        // dd($request->all());
        $reservation = Reservation::findOrFail(decrypt( $id));
        $messages = $reservation->message;

        $request['check_in'] = Carbon::parse($request['check_in'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');
        $request['check_out'] = Carbon::parse($request['check_out'])->shiftTimezone(now()->timezone)->setTimezone('Asia/Manila')->format('Y-m-d');

        $validator = null;
        if($reservation->accommodation_type == 'Day Tour'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_out'], 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'date_equals:'.$request['check_in']],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'reschedule_message.required' => 'Required to explain reason of reschedule',
                'required' => 'Need fill up first (:attribute)',
                'date_equals' => 'Choose only one day (Day Tour)',
            ]);
        }
        elseif($reservation->accommodation_type == 'Overnight'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::createFromFormat('Y-m-d', $request['check_in'])->addDays(2)],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'reschedule_message.required' => 'Required to explain reason of reschedule',
                'required' => 'Need fill up first (:attribute)',
                'check_out.after_or_equal' => 'Choose within 2 or 3 days (Overnight)',
            ]);
        }
        elseif($reservation->accommodation_type == 'Room Only'){
            $validator = Validator::make($request->all(), [
                'reschedule_message' => ['required'],
                'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after:'.Carbon::now()->addDays(1)],
                'check_out' => ['required', 'date', 'date_format:Y-m-d', 'after:'.$request['check_in']],
            ], [
                'check_in.unique' => 'Sorry, this date is not available',
                'check_in.after' => 'Choose date with 2 to 3 days',
                'reschedule_message.required' => 'Required to explain reason of reschedule',
                'required' => 'Need fill up first (:attribute)',
                'after' => 'The :attribute was already chose from (Check-in)',
    
            ]);
        }
        if ($validator->fails()) {            
            return back()
            ->with('error', $validator->errors()->all())
            ->withInput($validator->getData());
        }
        $validator = $validator->validate();
        if($validator['check_in'] === $reservation->check_in && $validator['check_out'] === $reservation->check_in ){
            return back()->with('error', 'Your choose date does not change at all')->withInput($validator);
        }
        $messages = $reservation->message;
        $messages['reschedule'] = [
            'message' => $validator['reschedule_message'],
            'check_in' => $validator['check_in'],
            'check_out' => $validator['check_out'],
        ];
        if($reservation->update(['message' => $messages])){
             return redirect()->route('user.reservation.show', $id)->with('success', 'Reschedule Request was updated');
        }
        

    }
    public function destroyReservation(Request $request, $id){
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ], [
            'required' => 'Need fill up first (:attribute)',
        ]);
        if ($validator->fails()) {            
            return back()->with('error', $validator->errors()->all());
        }
        $validator = $validator->validate();
        if(!Hash::check($validator['password'], auth('web')->user()->password)){
            return back()->with('error', 'Invalid Password');
        }
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->delete()) return redirect()->route('user.reservation.home')->with('success', 'Your Reservation was removed');

        
    }
}
