<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\Addons;
use App\Models\System;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use App\Mail\ReservationMail;
use App\Models\OnlinePayment;
use Illuminate\Validation\Rule;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Notifications\SystemNotification;
use App\Notifications\UserNotif;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class SystemReservationTwoController extends Controller
{
    private $system_user;
    public function __construct(){
        $this->system_user = auth('system');
        $this->middleware(function ($request, $next){
            if(!($this->system_user->user()->type === 0 || $this->system_user->user()->type === 1 )) abort(404);
            return $next($request);

        })->except(['updateCheckin', 'updateCheckout', 'show', 'index', 'search', 'event']);
    }
    private function employeeLogNotif($action, $link = null){
        if(auth()->guard('system')->user()->role() !== "Admin"){
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
                if(isset($admin->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $admin->telegram_chatID), $text, $keyboard, 'bot2'));
            }
            Notification::send($admins, new SystemNotification('Employee Action from '.auth()->guard('system')->user()->name().': ' . Str::limit($action, 10, '...'), $text, route('system.notifications')));
        }
    }
    private function roomAssign(array $rooms, Reservation $reservation, $validated, bool $forceAssign = false, bool $changeAssign = false){
        $roomCustomer = [];
        $reservationPax = 0;

        if($forceAssign){
            foreach($rooms as $room_id => $newPax){
                $reservationPax += (int)$newPax;
                $room = Room::find($room_id);
                $roomCustomer[$room_id] = $newPax;
            }
        }
        else{
            if(Room::checkAllAvailable()){
                foreach($rooms as $room_id => $newPax){
                    $reservationPax += (int)$newPax;
                    $room = Room::find($room_id);
                    if($newPax > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$newPax.' pax) and Room Capacity ('.$room->room->max_occupancy.' capacity)')->withInput($validated);
                    if($newPax > $room->getVacantPax() && $reservationPax <= $room->getVacantPax()) return back()->with('error', 'Room No. ' . $room->room_no. ' are only '.$room->getVacantPax().' pax to reserved and your guest ('.$reservationPax.' pax)')->withInput($validated);
                    $roomCustomer[$room_id] = $newPax;
                }
            }
            else{
                $r_lists = Reservation::where(function ($query) use ($reservation) {
                    $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                          ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                          ->orWhere(function ($query) use ($reservation) {
                              $query->where('check_in', '<=', $reservation->check_in)
                                    ->where('check_out', '>=', $reservation->check_out);
                          });
                })->whereBetween('status', [1, 2])->where('id', '!=', $reservation->id)->pluck('id');
    
                foreach($rooms as $room_id => $newPax){
                    $reservationPax += (int)$newPax;
                    $count_paxes = 0;
                    foreach($r_lists as $r_list){
                        $rooms = Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->where('id', $room_id)->get();
                        foreach($rooms as $room) $count_paxes += $room->customer[$r_list];
                    }
                    // dd($count_paxes);
                    $room = Room::find($room_id);
    
                    if($count_paxes > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot proceed due not Available based on guest ('.$newPax.' pax) on '.Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'))->withInput($validated);
            
                    if($count_paxes > $reservationPax && $reservationPax < $count_paxes)  return back()->with('error', 'Room No. ' . $room->room_no. ' cannot proceed due invalid guest between customer ('.$newPax.' pax) and vacant guest ('.$count_paxes.' pax) on '.Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'))->withInput($validated);
    
                    $roomCustomer[$room_id] = $newPax;
                }
            }
            if($reservationPax > $reservation->pax || $reservationPax < $reservation->pax) return back()->with('error', 'Guest you choose ('.$reservationPax.' pax) does not match on Customer Guest ('.$reservation->pax.' pax)')->withInput($validated);
        }

        return $roomCustomer; 
    }
    public function updateDisaproveReschedule(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status >= 2 && $reservation->status <= 3 || $reservation->status == 0) abort(404);
        $validator = Validator::make($request->all('reason'), [
            'reason' => ['required'],
        ]);
        if($validator->fails()) return back ()->with('error', $validator->errors()->all())->withInput($validator->getData());
        $validator = $validator->validate();
        $message = $reservation->message;
        $reservation->status = $message['reschedule']['prev_status'];
        $reservation->save();
        $message['reschedule']['prev_status'] = 7;
        if(isset($message['reschedule']['message'])) unset($message['reschedule']['message']);
        $updated = $reservation->update(['message' => $message]);
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Cancellation',
                'body' => 'Sorry, Your Reservation Cancel Request are now disapproved due to ' . $validator['reason'] . '. If you want concern. Please contact the owner'
            ];
            $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'reshedule'])) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Cancel Request of '.$reservation->userReservation->name().'was successful disapproved');
        }
       
        
    }
    public function showRooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        if($reservation->status >= 1) abort(404);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $roomReserved = [];
        $r_lists = Reservation::where(function ($query) use ($reservation) {
            $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                  ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                  ->orWhere(function ($query) use ($reservation) {
                      $query->where('check_in', '<=', $reservation->check_in)
                            ->where('check_out', '>=', $reservation->check_out);
                  });
        })->whereBetween('status', [1, 2])->where('id', '!=', $reservation->id)->pluck('id');
        
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
        return view('system.reservation.show-room',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'reserved' => $roomReserved]);
    }
    public function updateReservation(Request $request){
        try{
            $roomCustomer = [];
            if($request->has('room_rate')) $request['room_rate'] = decrypt($request['room_rate']);
            $validated = $request->validate([
                'room_rate' => ['required', Rule::when($request->has('room_rate'), ['numeric'])],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);
            
            $system_user = $this->system_user->user();
            $reservation = Reservation::findOrFail(decrypt($request->id));
            if($reservation->status >= 1) abort(404);
            if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');
            if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($request['room_pax']);
            else $validated['room_pax'] = $request['room_pax'];
            $rate = RoomRate::find($validated['room_rate']);

            if(isset($request['force'])) $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated, true);
            else $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated);

            if(!is_array($roomCustomer)){
                return $roomCustomer;
            }

            $transaction = $reservation->transaction;
            $transaction['rid'.$rate->id]['title'] = $rate->name;
            $transaction['rid'.$rate->id]['price'] = $rate->price;
            $transaction['rid'.$rate->id]['amount'] = $rate->price * $reservation->getNoDays();
            // Update Reservation
            $reserved = $reservation->update([
                'roomid' => array_keys($roomCustomer),
                'roomrateid' => $rate->id,
                'transaction' => $transaction,
                'status' => 1,
            ]);
            $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
            $reservation->save();

            // Update Room Availability
            if($reserved){
                foreach($roomCustomer as $key => $pax){
                    $room = Room::find($key);
                    $room->addCustomer($reservation->id, $pax);
                }
                $tour_menu = [];
                // Get Tour Menu for Mail
                $count = 0;
                foreach($reservation->transaction ?? [] as $key => $item){
                    if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                        $tour_menuID = (int)str_replace('tm','', $key);
                        $tour_menu[$count]['title'] = $reservation->transaction['tm'.$tour_menuID]['title'];
                        $tour_menu[$count]['price'] = (double) $reservation->transaction['tm'.$tour_menuID]['price'];
                        $tour_menu[$count]['amount'] = (double) $reservation->transaction['tm'.$tour_menuID]['amount'] * (int)$reservation->tour_pax;
                    }
                    if (strpos($key, 'TA') !== false && $reservation->accommodation_type != 'Room Only') {
                        $tour_menuID = (int)str_replace('TA','', $key);
                        foreach($item as $TA){
                            $tour_menu[$count]['title'] = $item['title'];
                            $tour_menu[$count]['price'] = (double)$item['price'];
                            $tour_menu[$count]['amount'] = (double)$item['amount'] * (int)$reservation->tour_pax;
                        }

                    }
                    $count++;
                }
            
                $roomDetails = [];
                foreach($reservation->roomid as $item) {
                    $room = Room::find($item);
                    if($room) $roomDetails[] = 'Room No ' . $room->room_no . ' ('.$room->room->name.')';
                    else $roomDetails[] = 'Room Data Missing';
                }

                $text = null;
                $url = null;
                if($reservation->payment_method == "Gcash"){
                    $gc = WebContent::all()->first()->payment ?? [];
                    $sp = "";
                    if(isset($gc['gcash'])){
                        foreach($gc['gcash'] ?? [] as $key => $item){
                            if($item['priority'] === true){
                                $sp = " or Mobile Number".(isset($item['name']) ? "of ".$item['name']."" : "")." ".(isset($item['number']) ? "(".$item['number'].")" : "");
                            }
                        }
                    }
                    $steps = [
                        "Step 1: Pay via QR Scanner" . $sp,
                        "Step 2: Send Screenshot of Receipt of Gcash",
                        "Step 3: Fill up the information for verify your payment",
                    ];
                    $url = route('reservation.gcash', encrypt($reservation->id));
                }
                if($reservation->payment_method == "PayPal"){
                    $ppl = WebContent::all()->first()->payment ?? [];
                    $s = "Enter your recipient's name, PayPal username, email, or mobile number";
                    if(isset($ppl['paypal'])){
                        foreach($ppl['paypal'] ?? [] as $key => $item){
                            if($item['priority'] === true){
                                $s = "Enter your recipient's name " . (isset($item['name']) ? "(".$item['name'].")" : "") ." , PayPal username ". (isset($item['username']) ? "(".$item['username'].")" : "") ." , email ".(isset($item['email'] ) ? "(".$item['email'].")" : "") .", or mobile number  ".(isset($item['number']) ? "(".$item['number'].")" : "");
                            }
                        }
                    }
                    $steps = [
                        "Step 1: " . $s ,
                        "Step 2:Enter the amount you want to send and choose a currency. You can even add a personalized note.",
                        'Step 3: Choose "Send". Your payment is on its way.',
                        "Step 4: Send Your Screenshot of your Receipt",
                        "Step 5: Fill up the information for verify your payment",
                    ];
                    $url = route('reservation.paypal', encrypt($reservation->id));
                }
                if($reservation->payment_method == "Bank Transfer"){
                    $bt = WebContent::all()->first()->payment ?? [];
                    $sbt = "Make your payment at the nearest or preferred bank location. Don't forget to keep your receipt.";
                    if(isset($bt['bankTransfer'])){
                        foreach($bt['bankTransfer'] ?? [] as $key => $item){
                            if($item['priority'] === true){
                                $sbt = "Make your payment at the nearest or preferred bank company location". (isset($item['name']) ? " and Send to ".$item['name']."" : "") . "". (isset($item['acc_no'] ) ? " with Account No. ".$item['acc_no']."." : "") ." Don't forget to keep your receipt.";
                            }
                        }
                    }
                    $steps = [
                        "Step 1: " . $sbt,
                        "Step 2: Send Your Screenshot of your Receipt",
                        "Step 3: Fill up the information for verify your payment",
                    ];
                    $url = route('reservation.bnktr', encrypt($reservation->id));
                }

                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => 'Reservation has Confirmed',
                    'body' => 'Your Reservation has Confirmed, Be on time at ' . Carbon::createFromFormat('Y-m-d', $reservation->check_in, Carbon::now()->timezone->getName())->format('F j, Y') . ' to ' . Carbon::createFromFormat('Y-m-d', $reservation->check_in, Carbon::now()->timezone)->addDays(3)->format('F j, Y'),
                    "age" => $reservation->age,  
                    "nationality" =>  $reservation->userReservation->nationality , 
                    "country" =>  $reservation->userReservation->country, 
                    "check_in" =>  Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'), 
                    "check_out" =>  Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y'), 
                    "accommodation_type" =>  $reservation->accommodation_type,
                    "payment_method" =>  $reservation->payment_method,
                    "pax" =>  $reservation->pax,
                    "tour_pax" =>  $reservation->tour_pax,
                    'menu' => $tour_menu,
                    "room_no" =>  implode(',', $roomDetails),
                    "room_type" => $rate->name,
                    'room_rate' => (double)$rate->price,
                    'total' => (double) $reservation->getTotal(),
                    'payment_link' => $url,
                    'payment_steps' => $steps,
                    'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('F j Y \a\t g:iA'),
                ];
                $this->employeeLogNotif('Chose to Approve of ' . $reservation->userReservation->name() . ' Reservation with Room Assign '.implode(',', $roomDetails), route('system.reservation.show', encrypt($reservation->id)));
                unset($roomDetails);
                $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'confirmed'])) ,$details['body'], $details, 'reservation.confirm-mail'))->onQueue(null));
                unset($details, $text, $url);
                return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Confirmed');
            }
        }
        catch(Exception $e){
            return redirect()->route('system.reservation.home');
        }
    }
    public function updateCheckin(Request $request){
        try{
            $system_user = $this->system_user->user();
            $admins = System::all()->where('type', 0);
            $reservation = Reservation::findOrFail(decrypt($request->id));
            if($reservation->status >= 2) abort(404);

            $transaction = $reservation->transaction;
            $discounted = 0;
            if($request->has('senior_count')){
                $validate = Validator::make(['senior_count' => $request['senior_count']], [
                    'senior_count' => ['required', 'numeric', 'min:1', 'max:'.$reservation->pax]
                ], [
                    'senior_count.required' => 'Required Input (Senior Guest Discount)',
                    'senior_count.max' => 'Input based on Room Guest of Customer',
                ]);
                if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());

                $discounted = $validate->validate();
                $transaction['payment']['discountPerson'] = $discounted['senior_count'];
                foreach($transaction ?? [] as $key => $item){
                    if (strpos($key, 'rid') !== false) {
                        $rateID = (int)str_replace('rid','', $key);
                        $discounted = (20 / 100) * $discounted['senior_count'];
                        $discounted = (double)($transaction['rid'.$rateID]['amount'] * $discounted);
                        $discounted = (double)($transaction['rid'.$rateID]['amount'] - $discounted);
                        $transaction['rid'.$rateID]['orig_amount'] = $transaction['rid'.$rateID]['amount'];
                        $transaction['rid'.$rateID]['amount'] = $discounted;
                        break;
                    }
                }

            }
            $downpayment = $transaction['payment']['downpayment'] ?? 0;
            $balance = abs((abs($reservation->getTotal() - $discounted)) - $downpayment);
            if($request['payments'] == 'partial'){
                $validate = Validator::make($request->all(['payments', 'another_payment']), [
                    'payments' => ['required'],
                    'another_payment' =>['required', 'numeric', 'max:'.(int)$balance],
                ], [
                    'required' => 'Required to choose',
                    'max' => 'Fill the amount up to ₱' . number_format($balance, 2),
                ]);
                if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
                $validate = $validate->validate();
                
                $transaction['payment']['cinpay'] = (double)$validate['another_payment'];
                $message = 'Partial Payment (₱ '.number_format($validate ['another_payment'], 2).')';

            }
            else if($request['payments'] == 'fullpayment'){
                $validate = Validator::make($request->all(['payments', 'another_payment']), [
                    'payments' => ['required'],
                ], [
                    'required' => 'Required to choose',
                ]);
                if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
                $transaction['payment']['cinpay'] = $balance ;
                $message = 'Full Payment (₱ '.number_format($balance, 2).')';
            }
            else{
                return back();
            }
            $updated = $reservation->update([
                'transaction' => $transaction,
                'status' => 2,
            ]);
            unset($transaction, $discounted);
            $text = 
            "Employee Action: Check-in !\n" .
            "Name: ". $reservation->userReservation->name() ."\n" . 
            "Age: " . $reservation->age ."\n" .  
            "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
            "Payment: " . $message  ."\n" . 
            "Who Approve: " . $system_user->name() ;
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Check-in',
                'body' => 'You now checked in at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A'),
            ];
            if($updated){
                $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'checkin'])) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
                unset($text, $details);
                $this->employeeLogNotif('Checked in' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
                return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked in');
            }
        }
        catch(Exception $e){
            return redirect()->route('system.reservation.home');
        }
    }
    public function updateCheckout(Request $request){
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status >= 3) abort(404);

        $validated = $request->validate([
            'fullpay' => ['accepted'],
        ],[
            'accepted' => 'Before proceeding, full payment must be made first.'
        ]);
        $transaction = $reservation->transaction;
        $transaction['payment']['coutpay'] = $reservation->balance();
        if($validated) {
            $reservation->update(['status' => 3, 'transaction' => $transaction]);
            $reservation->checkedOut();
        }
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Check-out',
            'body' => 'You now checked out at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A'),
            'receipt_link' => route('reservation.receipt', encrypt($reservation->id)),
            'feedback_link' => route('reservation.feedback', encrypt($reservation->id)),
        ];   

        $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'checkin'])) ,$details['body'], $details, 'reservation.checkout-mail'))->onQueue(null));
        $this->employeeLogNotif('Check-out of ' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
        unset($text, $details, $transaction);
        return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked out');
        
    }
    public function disaprove($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $admins = System::all()->where('type', 0);
        if($reservation->status >= 1) abort(404);

        $tour_menu = [];
        $count = 0;
        foreach($reservation->transaction as $key => $item){
            if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menuID = (int)str_replace('tm','', $key);
                $tour_menu[$count]['title'] = TourMenu::find($tour_menuID)->tourMenu->title;
                $tour_menu[$count]['type'] = TourMenu::find($tour_menuID)->type;
                $tour_menu[$count]['pax'] = TourMenu::find($tour_menuID)->pax;
                $tour_menu[$count]['price'] = $reservation->transaction['tm'.$tour_menuID];
                $tour_menu[$count]['amount'] = $reservation->transaction['tm'.$tour_menuID]['amount'];
            }
            if (strpos($key, 'rid') !== false) {
                $rateID = (int)str_replace('rid','', $key);
                $rates['name'] = RoomRate::find($rateID)->name;
                $rates['amount'] = RoomRate::find($rateID)->price;
                $rates['price'] = $reservation->transaction['rid'.$rateID];
            }
            $count++;
        }
        unset($count);
        return view('system.reservation.disaprove',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'menu' => $tour_menu]);
    }
    public function disaproveStore(Request $request, $id){
        try{
            $reservation = Reservation::findOrFail(decrypt($id));
            if($reservation->status >= 1) abort(404);

            $system_user = $this->system_user->user();

            if($request['reason'] === 'Other'){
                $validated = $request->validate([
                    'reason' => ['required'],
                    'message' => ['required'],
                    'passcode' => ['required', 'digits:4', 'numeric'],
                ], [
                    'required' => 'Need to fill up this form'
                ]);

            }
            else{
                $validated = $request->validate([
                    'reason' => ['required'],
                    'passcode' => ['required', 'digits:4', 'numeric'],
                ], [
                    'required' => 'Need to fill up this form'
                ]);
                $validated['message'] =  $validated['reason'];
            }
            if(!Hash::check($validated['passcode'], $system_user->passcode))  return back()->with('error', 'Invalid Passcode, Try Again')->withInput($validated);
            $messages = $reservation->message;
            $messages['disaprove'] = $validated['message'];
            $updated = $reservation->update(['status' => 5]); // delete on reservation
            if($updated){
                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => 'Reservation Disapprove',
                    'body' => 'Your Reservation are disapproved due of ' . $messages['disaprove']. 'Sorry for waiting. Please try again to make reservation in another dates',
                ];
                $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'canceled'])) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));

                unset($details, $text);
                $this->employeeLogNotif('Chose to Disapprove of ' . $reservation->userReservation->name() . ' Reservation', route('system.reservation.show', encrypt($reservation->id)));
                return redirect()->route('system.reservation.home')->with('success', 'Disapprove of ' . $reservation->userReservation->name() . ' was Successful');
            }
            else{
                return back()->withInput($request->all());
            }
        }
        catch(Exception $e){
            return redirect()->route('system.reservation.home');
        }
    }
    public function showOnlinePayment($id){
        $reservation = Reservation::findOrFail(decrypt($id));

        return view('system.reservation.onlinepayment.index', ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function storeOnlinePayment(Request $request, $id){
       try{
            $validated = $request->validate([
                'amount' => ['required', 'numeric'],
                'type' => ['required'],
            ]);
            $online_payment = OnlinePayment::findOrFail(decrypt($id));
            $reservation = Reservation::findOrFail($online_payment->reservation_id);
            if($reservation->status >= 2) abort(404);
            $downpayment = $reservation->transaction;
            if(isset($downpayment['payment']['downpayment'])) $downpayment['payment']['downpayment'] += (double)$validated['amount'];
            else $downpayment['payment']['downpayment'] = (double)$validated['amount'];
            $reservation->update(['transaction' => $downpayment]);
            $online_payment->approval = true;
            $online_payment->save();

            if($validated['type'] == "full"){
                $reservation->payment_cutoff = null;
                $reservation->save();
                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => 'Your online payment was approved',
                    'body' => 'Downpayment: ₱' .  number_format($downpayment['payment']['downpayment'], 2),
                ];
                $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['title'] . ' ' . $details['body'], $details, 'reservation.mail'))->onQueue(null));
            }
            else{
                $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s'); 
                $reservation->save(); 

                if($reservation->payment_method == "Gcash") $url = route('reservation.gcash', encrypt($reservation->id));
                if($reservation->payment_method == "PayPal") $url = route('reservation.paypal', encrypt($reservation->id));
                if($reservation->payment_method == "Bank Transfer") $url = route('reservation.bnktr', encrypt($reservation->id));
                
                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => 'Your online payment was approved, but the amount you paid was insufficient. There is a chance for you to make another payment',
                    'body' => 'Amount: ₱' .  number_format($downpayment['payment']['downpayment'], 2) .' but minimuim payment is 1000 philippine pesos',            
                    'link' => $url,
                    'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y'),
                ];
                $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['title'] . '. ' . $details['body'], $details, 'reservation.online-payment-mail'))->onQueue(null));
            }
            $this->employeeLogNotif('Approve Downpayment of ' . $reservation->userReservation->name() . ' with Paid ₱' . number_format($downpayment['payment']['downpayment'], 2), route('system.reservation.show.online.payment', encrypt($reservation->id)));
            unset($downpayment);
            return redirect()->route('system.reservation.show.online.payment', encrypt($reservation->id))->with('success', 'Approved payment successful');
       }
       catch(Exception $e){
            return redirect()->route('system.reservation.home');
        }
    }
    public function disaproveOnlinePayment(Request $request, $id){
        $validated = $request->validate(['reason' => ['required']]);
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        $reservation = Reservation::findOrFail($online_payment->reservation_id);
        $countDisapprove = OnlinePayment::where('reservation_id', $reservation->id)->where('approval', 0)->count();
        if($countDisapprove >= 3){
            $reservation->payment_cutoff = null;
            $reservation->update(['status' => 5]);
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation was Canceled',
                'body' => 'Your reservation has been canceled because it has been disapproved 3 attempts, where the opportunity to pay properly. If you have any concerns, you can contact the owner or personnel'
            ];
            $text = 
                "Cancel Reservation!\n" .
                "Name: ". $reservation->userReservation->name() ."\n" . 
                "Age: " . $reservation->age ."\n" .  
                "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
                "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y') ."\n" . 
                "Check-out: " . Carbon::createFromFormat('Y-m-d', $reservation->check_out)->format('F j, Y') ."\n" . 
                "Type: " . $reservation->accommodation_type ;
                // Send Notification to 
            $keyboard = [
                [
                    ['text' => 'View', 'url' => route('system.reservation.show', encrypt($reservation->id))],
                ],
            ];
            foreach(System::whereBetween('type', [0, 1]) as $user){
                if(!empty($user->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID') ?? $user->telegram_chatID, $text, $keyboard));
            }
            $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.online-payment-mail'))->onQueue(null));
            $this->employeeLogNotif('Choose Disapprove on Downpayment of ' . $reservation->userReservation->name(), route('system.reservation.show.online.payment', encrypt($reservation->id)));
            return redirect()->route('system.reservation.show.online.payment', encrypt($reservation->id))->with('success', 'Disapprove payment successful with Cancal Reservation');
        }
        $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        $reservation->save();
        $online_payment->approval = false;
        $online_payment->save();

        if($reservation->payment_method == "Gcash"){
            $url = route('reservation.gcash', encrypt($reservation->id));
        }
        if($reservation->payment_method == "PayPal"){
            $url = route('reservation.paypal', encrypt($reservation->id));
        }
        if($reservation->payment_method == "Bank Transfer"){
            $url = route('reservation.bnktr', encrypt($reservation->id));
        }
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Your online payment was disapproved',
            'body' => 'Reason:  ' .  $validated['reason'] . '. I will give you a chance to make payment with ('.(3 - $online_payment->attempt).' only) ',
            'link' => $url,
            'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y'),
        ];
        $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.online-payment-mail'))->onQueue(null));

        $this->employeeLogNotif('Choose Disapprove on Downpayment of ' . $reservation->userReservation->name(), route('system.reservation.show.online.payment', encrypt($reservation->id)));
        return redirect()->route('system.reservation.show.online.payment', encrypt($reservation->id))->with('success', 'Disapprove payment successful');

    }
    public function storeForcePayment(Request $request, $id){
        try{
            $system_user = $this->system_user->user();
            $reservation = Reservation::findOrFail(decrypt($id));
            if($reservation->status >= 2) abort(404);
            $admins = System::all()->where('type', 0);
            $validated = Validator::make($request->all('amount', 'passcode'), [
                'amount' => ['required', 'numeric', 'min:1000', 'max:'.$reservation->getTotal()],
                'passcode' => ['required', 'numeric', 'digits:4'],
            ], [
                'passcode.required' => 'Required to fill up (Passcode)', 
                'passcode.digits' => 'Required to 4 Digits Number', 
                'amount.required' => 'Required to fill up (Amount)', 
                'amount.numeric' => 'Number only',
                'amount.min' => 'The amount must be ₱ 1,000 above',
                'amount.max' => 'The amount must exact ₱ '.number_format($reservation->getTotal(), 2).' below',
            ]);
            if($validated->fails()){
                return back()->with('error', $validated->errors()->all());
            }
            $validated = $validated->validate();
            if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');
            $downpayment = $reservation->transaction;
            if(isset($downpayment['payment']['downpayment'] )) $downpayment['payment']['downpayment'] += (double)$validated['amount'];
            else $downpayment['payment']['downpayment'] = (double)$validated['amount'];
            $updated = $reservation->update(['transaction' => $downpayment]);
            $reservation->payment_cutoff = null;
            $reservation->save();

            if($updated){
                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => "Force Payment",
                    'body' => "Your Downpayment was paid by " . $system_user->name() . " with  ₱" . number_format($validated['amount'], 2),
                ];
                $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));

                $this->employeeLogNotif('Fill the Payment of Force Payment for ' . $reservation->userReservation->name() . ' in ' . $validated['amount'] . ' pesos', route('system.reservation.show', $id));
                return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . 'was now paid on ₱ ' . number_format($validated['amount'], 2));
            } 
        }
        catch(Exception $e){
            return redirect()->route('system.reservation.home');
        }
    }
    public function showAddons(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if($request->has('tab') && $request['tab'] === 'TA'){
            $tour_list = TourMenuList::all();
            $tour_menu = TourMenuList::all();
            $tour_category = TourMenuList::distinct()->get('category');
            if(old('tour_menu')){
                $temp = [];
                foreach(old('tour_menu') as $key => $item){
                    $temp[$key]['id'] = TourMenu::find($item)->id;
                    $temp[$key]['title'] = TourMenu::find($item)->tourMenu->title;
                    $temp[$key]['type'] =  TourMenu::find($item)->type . '('.TourMenu::find($item)->pax .' guest)';
                    $temp[$key]['price'] = number_format(TourMenu::find($item)->price, 2);
                }
            }
        }

        return view('system.reservation.addons.index',  [
            'activeSb' => 'Reservation', 
            'r_list' => $reservation,
            'tour_lists' => $tour_list ?? [], 
            'tour_category' =>  $tour_category ?? [], 
            'tour_menus' => $tour_menu ?? [],
            'temp_tour' => $temp ?? [],
            'addons_list' => Addons::all(),
            'user_days' => $reservation->getNoDaysInToday(),
        ]);
    }
    public function updateAddons(Request $request, $id){
        // dd($request->all());
       try{
        $system_user = $this->system_user->user();
        $reservation = Reservation::findOrFail(decrypt($id));

        if(!($reservation->status == 2)) abort(404);

        $transaction = $reservation->transaction;
        $type = "Other Addons";
        if($request->has('tab') && $request['tab'] == 'TA'){
            $validate = Validator::make($request->all(), [
                'tour_menu' => ['required'],
                'new_pax' => ['required', 'numeric', 'min:1' , 'max:'.$reservation->pax],
                'passcode' => ['required', 'numeric', 'digits:4'],
            ], [
                'tour_menu.required' => 'Your Cart is empty',
                'new_pax.required' => 'Required to fill up number of guest ',
                'new_pax.numeric' => 'Number of guest should be number only',
                'new_pax.min' => 'Number of guest should be 1 and above',
                'new_pax.max' => 'INumber of guest should be '.$reservation->pax.' guest below',
            ]);     
            if($validate->fails()){
                return back()->with('error', $validate->errors()->all())->withInput();
            }
            $validated = $validate->validate();
            if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid passcode')->withInput($validated);
            foreach($validated['tour_menu'] as $item){
                $transaction['TA'.$item][] = [
                    'title' => TourMenu::find($item)->tourMenu->title . ' ' . TourMenu::find($item)->type . '('.TourMenu::find($item)->pax.' pax)',
                    'price' => TourMenu::find($item)->price ?? 0,
                    'tpx' => $validated['new_pax'],
                    'amount' => ((double)TourMenu::find($item)->price ?? 0) * (int)$validated['new_pax'],
                ];
            }
            $type = "Tour Addons";
        }
        else{
            $validated = $request->validate([
                'addons' => ['required'],
                'pcs' => ['required', 'numeric'],
                'passcode' => ['required', 'numeric', 'digits:4'],
            ]);
            if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid passcode')->withInput($validated);
            $id = decrypt($validated['addons']);
            $adddon = Addons::find($id);
            $transaction = $reservation->transaction;
            $transaction['OA'.$id][] = [
                'title' => $adddon->title,
                'amount' => $adddon->price * (int)$validated['pcs'],
                'pcs' => $validated['pcs'],
                'price' => $adddon->price ,
            ];  

        }
        $updated = $reservation->update([
            'transaction' => $transaction,
        ]);
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => "Add Addons Package",
            'body' => "Your Reservation Addon was added (".$type.") by" . $system_user->name(),
        ];
        $reservation->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
        $this->employeeLogNotif('Add Addons Package of ' . $reservation->userReservation->name() . '(' . $type . ')');
        if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Other Add-ons for '.$reservation->userReservation->name().' was successful');

       }
       catch(Exception $e){
            return redirect()->route('system.reservation.home');
       }
    }
    public function showExtend($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        if(!($reservation->status == 2)) abort(404);

        return view('system.reservation.extend.index',  ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function updateExtend(Request $request, $id){
        // try{
            $reservation = Reservation::findOrFail(decrypt($id));
            if(!($reservation->status == 2)) abort(404);
            $validated = $request->validate([
                'no_days' => ['required', 'numeric', 'min:1'],
            ]);
            $extended = Carbon::now('Asian/Manila')->addDays((int)$validated['no_days'])->format('Y-m-d');
            $transaction = $reservation->transaction;
            $reservation->check_out = $extended;
            $reservation->save();

            foreach($transaction ?? [] as $key => $item){
                if (strpos($key, 'rid') !== false) {
                    $rateID = (int)str_replace('rid','', $key);
                    $transaction['rid'.$rateID]['amount'] = $transaction['rid'.$rateID]['price'] * $reservation->getNoDays();
                    if($transaction['payment']['discountPerson']){
                        $discounted = (20 / 100) * (int)$transaction['payment']['discountPerson'];
                        $discounted = (double)($transaction['rid'.$rateID]['amount'] * $discounted);
                        $discounted = (double)($transaction['rid'.$rateID]['amount'] - $discounted);
                        $transaction['rid'.$rateID]['orig_amount'] = $transaction['rid'.$rateID]['amount'];
                        $transaction['rid'.$rateID]['amount'] = $discounted;
                    }
                    break;
                }
            }
   
            // May kulang pa dito
            $updated = $reservation->update([ 
                'status' => 2, 
                'transaction' => $transaction
            ]);
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => "Extend Days Request",
                'body' => "Extend Days was updated in  just ".($validated['no_days'] > 1 ? $validated['no_days'] . "days" : $validated['no_days'] . "day") . "(Check-in: ".Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y')." and Check-out: ".Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y').")",
            ];
            $reservation->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
            $this->employeeLogNotif('Add days of Extend Day for ' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
            if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->useReservation()->name() . ' was extend in ' . ($validated['no_days'] > 1 ? $validated['no_days'] . ' days' : $validated['no_days'] . ' day'));
        // }
        // catch(Exception $e){
        //     return redirect()->route('system.reservation.home');
        // }
    }
}
