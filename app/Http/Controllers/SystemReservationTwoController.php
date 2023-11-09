<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\Addons;
use App\Models\System;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\AuditTrail;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use App\Models\OnlinePayment;
use Illuminate\Validation\Rule;
use App\Notifications\UserNotif;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Notifications\SystemNotification;
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

        })->except(['updateCheckin', 'updateCheckout', 'show', 'index', 'search', 'event', 'showAddons', 'updateAddons', 'showExtend', 'updateExtend']);
    }
    private function attemptPayment(Reservation $rlist, $attempt = 4){
        $paymentsCount = OnlinePayment::where('reservation_id', $rlist->id)->whereNotNull('approval')->count();
        $haveAttempt = true;
        if($paymentsCount >= $attempt){
            $rlist->update(['status' => 5]);
            $details = [
                'name' => $rlist->userReservation->name(),
                'title' => 'Reservation Cancelled',
                'body' => 'Your reservation was canceled when you ran out of attempts for your chances to pay again. If you have concern, please contact us',
            ];
            foreach(Room::all() as $room) $room->removeCustomer($rlist->id);
            if(isset($rlist->user_id)) $rlist->userReservation->notify((new UserNotif(route('user.reservation.home', 'tab=cancel') ,$details['body'], $details, 'reservation.mail')));
            $haveAttempt = false;
        }
        return $haveAttempt;
    }
    private function employeeLogNotif($action, $link = null){
        $user = auth()->guard('system')->user();
        if($user->role() !== "Admin"){
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
        AuditTrail::create([
            'system_id' => $user->id,
            'role' => $user->type ?? '',
            'action' => $action,
            'module' => 'Payment Reservation',
        ]);
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
        unset($message['reschedule']);
        $reservation->message = $message;
        $reservation->save();
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Reschedule Disapproved',
            'body' => 'Sorry, Your Reschedule Request are now disapproved due to ' . $validator['reason'] . '. If you want concern. Please contact the owner'
        ];
        if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'reshedule'])) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
        return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Reschedule Request of '.$reservation->userReservation->name().'was disapproved');
        
       
        
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
        
        foreach($rooms as $room){
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
        $roomCustomer = [];
        if($request->has('room_rate')) $request['room_rate'] = decrypt($request['room_rate']);
        $validated = $request->validate([
            'room_rate' => ['required', Rule::when($request->has('room_rate'), ['numeric'])],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        
        $system_user = $this->system_user->user();
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status >= 1) abort(404);
        if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($request->all());
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($request['room_pax']);
        else $validated['room_pax'] = $request['room_pax'];
        $rate = RoomRate::find($validated['room_rate']);

        if(isset($request['force'])) $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated, true);
        else $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated);

        if(!is_array($roomCustomer)){
            return $roomCustomer;
        }
        $person = (double)$rate->price * $reservation->getNoDays();
        $transaction = $reservation->transaction;
        $transaction['rid'.$rate->id]['title'] = $rate->name;
        $transaction['rid'.$rate->id]['price'] = (double)$rate->price;
        $transaction['rid'.$rate->id]['person'] = $person;
        $transaction['rid'.$rate->id]['amount'] = $person * $reservation->pax;
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
            $tourTotal = 0;
            foreach($reservation->transaction ?? [] as $key => $item){
                if (strpos($key, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                    $tour_menuID = (int)str_replace('tm','', $key);
                    $tour_menu[$count]['title'] = $reservation->transaction['tm'.$tour_menuID]['title'];
                    $tour_menu[$count]['price'] = (double) $reservation->transaction['tm'.$tour_menuID]['price'];
                    $tour_menu[$count]['amount'] = (double) $reservation->transaction['tm'.$tour_menuID]['price'] * (int)$reservation->tour_pax;
                    $tourTotal += (double)$item['price'] * (int)$reservation->tour_pax;
                }
                // if (strpos($key, 'TA') !== false && $reservation->accommodation_type != 'Room Only') {
                //     $tour_menuID = (int)str_replace('TA','', $key);
                //     foreach($item as $TA){
                //         $tour_menu[$count]['title'] = $item['title'];
                //         $tour_menu[$count]['price'] = (double)$item['price'];
                //         $tour_menu[$count]['amount'] = (double)$item['amount'] * (int)$reservation->tour_pax; 
                //     }

                // }
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
            $steps = '';
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
            $person = (double)$rate->price * $reservation->getNoDays();
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation has Confirmed',
                'body' => 'Your Reservation has Confirmed, Be on time at ' . Carbon::createFromFormat('Y-m-d', $reservation->check_in, Carbon::now()->timezone->getName())->format('F j, Y') . ' to ' . Carbon::createFromFormat('Y-m-d', $reservation->check_in, Carbon::now()->timezone)->addDays(3)->format('F j, Y'),
                "nationality" =>  $reservation->userReservation->nationality , 
                "country" =>  $reservation->userReservation->country, 
                "check_in" =>  Carbon::createFromFormat('Y-m-d', $reservation->check_in)->setTimezone('UTC')->format('F j, Y') . '(UTC)', 
                "check_out" =>  Carbon::createFromFormat('Y-m-d', $reservation->check_out)->setTimezone('UTC')->format('F j, Y') . '(UTC)', 
                "accommodation_type" =>  $reservation->accommodation_type,
                "payment_method" =>  $reservation->payment_method,
                "pax" =>  $reservation->pax,
                "tour_pax" =>  $reservation->tour_pax,
                'menu' => $tour_menu,
                "room_no" =>  implode(',', $roomDetails),
                "room_type" => $rate->name,
                'room_rate' => (double)$rate->price,
                'days' =>  $reservation->getNoDays(),
                'rate_person' => $person,
                'rate_amount' => $person * $reservation->pax,
                'total' => ($person * $reservation->pax) + $tourTotal,
                'payment_link' => $url,
                'payment_steps' => $steps,
                'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->setTimezone('UTC')->format('F j Y \a\t g:iA') . ' (UTC)',
            ];
            $this->employeeLogNotif('Chose to Approve of ' . $reservation->userReservation->name() . ' Reservation with Room Assign '.implode(',', $roomDetails), route('system.reservation.show', encrypt($reservation->id)));
            unset($roomDetails);
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'confirmed'])) ,$details['body'], $details, 'reservation.confirm-mail'))->onQueue(null));
            unset($details, $text, $url);
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Confirmed');
        }
    }
    public function updateCheckin(Request $request){
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
                'senior_count.required' => 'Required Input (Senior or PWD Guest Discount)',
                'senior_count.max' => 'Input based on Room Guest of Customer',
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($request->all());

            $validate = $validate->validate();
            $transaction['payment']['discountPerson'] = (int)$validate['senior_count'];
            foreach($transaction ?? [] as $key => $item){
                if (strpos($key, 'rid') !== false) {
                    $rateID = (int)str_replace('rid','', $key);
                    $transaction['rid'.$rateID]['orig_amount'] = $item['amount'];
                    $discounted = $reservation->discounted($item['person']);
                    // dd($discounted);
                    $roomtotal = 0;
                    for($i = 1; $i <= $reservation->pax; $i++){
                        if($i <= (int)$validate['senior_count']) $roomtotal += $discounted;
                        else $roomtotal += (double)$item['person'];
                    }
                    $transaction['rid'.$rateID]['amount'] = $roomtotal;
                    $transaction['rid'.$rateID]['discounted'] = $discounted;
                }
            }

        }
        $downpayment = $transaction['payment']['downpayment'] ?? 0;
        $validate = Validator::make($request->all(['payments', 'another_payment']), [
            'another_payment' =>['required', 'numeric'],
        ], [
            'required' => 'Required to choose',
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
        $validate = $validate->validate();
        $transaction['payment']['cinpay'] = (double)$validate['another_payment'];
        
        $updated = $reservation->update([
            'transaction' => $transaction,
            'status' => 2,
        ]);
        $text = 
        "Employee Action: Check-in !\n" .
        "Name: ". $reservation->userReservation->name() ."\n" . 
        "Age: " . $reservation->age ."\n" .  
        "Nationality: " . $reservation->userReservation->nationality  ."\n" . 
        "Payment: ₱ " . number_format($transaction['payment']['cinpay'], 2)  ."\n" . 
        "Who Approve: " . $system_user->name() ;
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Checked in',
            'body' => 'You now checked in at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A') . '('.Carbon::now()->timezone->getName().')',
        ];
        if($updated){
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'checkin'])) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
            unset($text, $details, $transaction, $discounted, $downpayment, $balance);
            $this->employeeLogNotif('Checked in ' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was Checked in');
        }

    }
    public function updateCheckout(Request $request){
        $sysUser = $this->system_user->user();
        $reservation = Reservation::findOrFail(decrypt($request->id));
        if($reservation->status >= 3) abort(404);
        $transaction = $reservation->transaction;
        if(strpos($request['coutpay'], 'force-') !== false) {
            $passcode = Validator::make($request->all(), [
                'coutpay' => ['required', 'string'],
            ]);
            if($passcode->fails()) return back()->with('error', $passcode->errors()->all());
            $passcode = $passcode->validate();
            $passcode = str_replace('force-', '', $passcode['coutpay']);
            if(!Hash::check($passcode, $sysUser->passcode)) return back()->with('error', 'Invalid Passcode');
            $transaction['payment']['refunded'] = true;
        }
        else{
            $validated = Validator::make($request->all(),[
                'coutpay' => ['required', 'numeric', Rule::in($reservation->balance())],
            ],[
                'lte' => 'Enter amount up to ₱ '. $reservation->balance(),
            ]);
            if($validated->fails()) return back()->with('error', $validated->errors()->all())->withInput($request->all());
            $validated = $validated->validate();
            $transaction['payment']['coutpay'] = $validated['coutpay'];
        }


        $transaction['receipt'] = Carbon::now()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $reservation->update(['status' => 3, 'transaction' => $transaction]);
        $reservation->checkedOut();
        
        $details = [
            'name' => $reservation->userReservation->name(),
            'title' => 'Reservation Checked out',
            'body' => 'You now checked out at ' . Carbon::now(Carbon::now()->timezone->getName())->format('F j, Y, g:i A') . '('.Carbon::now()->timezone->getName().')',
            'receipt_link' => route('reservation.receipt', encrypt($reservation->id)),
            'feedback_link' => route('reservation.feedback', encrypt($reservation->id)),
        ];   

        if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'checkin'])) ,$details['body'], $details, 'reservation.checkout-mail'))->onQueue(null));
        $this->employeeLogNotif('Checked out of ' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
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
                $trs = TourMenu::withTrashed()->find($tour_menuID);
                $tour_menu[$count]['title'] = $trs->tourMenu->title;
                $tour_menu[$count]['type'] = $trs->type;
                $tour_menu[$count]['pax'] = $trs->pax;
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
        $reservation = Reservation::findOrFail(decrypt($id));
        if($reservation->status >= 1) abort(404);

        $system_user = $this->system_user->user();
        $validated = $request->validate([
            'reason' => ['required'],
            'message' => Rule::when($request['reason'] === 'Other', ['required']),
            'passcode' => ['required', 'digits:4', 'numeric'],
        ], [
            'required' => 'Need to fill up this form'
        ]);

        if(!Hash::check($validated['passcode'], $system_user->passcode))  return back()->with('error', 'Invalid Passcode, Try Again')->withInput($validated);
        if(!($request['reason'] === 'Other')) $validated['message'] =  $validated['reason'];
        
        $updated = $reservation->update(['status' => 5]); 
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Disapprove',
                'body' => 'The Reservation you request are disapproved due of ' . $validated['message']. '. Please try again to make reservation in another dates',
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab'=> 'canceled'])) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));

            unset($details, $text);
            $this->employeeLogNotif('Chose to Disapprove of ' . $reservation->userReservation->name() . ' Reservation', route('system.reservation.show', encrypt($reservation->id)));
            return redirect()->route('system.reservation.home')->with('success', 'Disapprove of ' . $reservation->userReservation->name() . ' was Successful');
        }
        else{
            return back()->withInput($request->all());
        }
    }
    public function showOnlinePayment($id){
        $reservation = Reservation::findOrFail(decrypt($id));

        return view('system.reservation.onlinepayment.index', ['activeSb' => 'Reservation', 'r_list' => $reservation]);
    }
    public function storeOnlinePayment(Request $request, $id){
        $validated = Validator::make($request->all(), [
            'amount' => ['required', 'numeric'],
            'type' => ['required'],
        ], [
            'amount.required' => "Required to Enter Legit Amount",
            'type.required' => "Required to Choose Type of Approve",
        ]);
        if($validated->fails()) return back()->with('error', $validated->errors()->all());
        $validated = $validated->validate();
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        $reservation = Reservation::findOrFail($online_payment->reservation_id);
        if($reservation->status >= 2) abort(404);
        $downpayment = $reservation->transaction;
        if(isset($downpayment['payment']['downpayment'])) $downpayment['payment']['downpayment'] += (double)$validated['amount'];
        else $downpayment['payment']['downpayment'] = (double)$validated['amount'];
        $reservation->update(['transaction' => $downpayment]);

        $typeApp = 'Partial';
        $changeAmount = null;
        if($online_payment->amount != (double)$validated['amount']) $changeAmount = $this->system_user->user()->name() . ' made an adjustment to the provided payment information, changing the amount from ₱ '.number_format($online_payment->amount, 2).' to ₱ '.number_format($validated['amount'], 2).'.';
        $online_payment->amount = (double)$validated['amount'];
        if($validated['type'] == "full"){
            $typeApp = 'Full';
            $online_payment->approval = 1;
            $online_payment->save();
            $reservation->payment_cutoff = null;
            $reservation->save();
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Your online payment was approved. If you have concern, please contact us',
                'body' => 'Downpayment: ₱' .  number_format($downpayment['payment']['downpayment'], 2),
            ];
            if($changeAmount != null) $details['note'] = $changeAmount;
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['title'] . ' ' . $details['body'], $details, 'reservation.online-payment-maill'))->onQueue(null));
        }
        else{
            $online_payment->approval = 3;
            $online_payment->save();
            if($this->attemptPayment($reservation)){

                $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s'); 
                $reservation->save(); 
    
                if($reservation->payment_method == "Gcash") $url = route('reservation.gcash', encrypt($reservation->id));
                if($reservation->payment_method == "PayPal") $url = route('reservation.paypal', encrypt($reservation->id));
                if($reservation->payment_method == "Bank Transfer") $url = route('reservation.bnktr', encrypt($reservation->id));
                $count = OnlinePayment::where('reservation_id', $reservation->id)->whereNotNull('approval')->count();
    
                $details = [
                    'name' => $reservation->userReservation->name(),
                    'title' => 'Your online payment was partial approved',
                    'body' => 'Amount: ₱' .  number_format($downpayment['payment']['downpayment'], 2) .' but required pay again in ₱ '.(1000 - $downpayment['payment']['downpayment']).' above to make full approve your reservation. We give chance for you to another payment ('.(4 - $count).' attempt only). If you have concern please contact us',            
                    'link' => $url,
                    'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->format('M j, Y') . ' (UTC)',
                ];
                if($changeAmount != null) $details['note'] = $changeAmount;
                if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['title'] . '. ' . $details['body'], $details, 'reservation.online-payment-mail'))->onQueue(null));
            }
        }
        
        $this->employeeLogNotif($typeApp.' Approve Downpayment of ' . $reservation->userReservation->name() . ' with Paid ₱' . number_format($downpayment['payment']['downpayment'], 2), route('system.reservation.show.online.payment', encrypt($reservation->id)));
        unset($downpayment);
        return redirect()->route('system.reservation.show.online.payment', encrypt($reservation->id))->with('success', 'Approved payment successful');
    }
    public function disaproveOnlinePayment(Request $request, $id){
        // $validated = $request->validate(['reason' => ['required']]);
        $validated = Validator::make($request->all(), [
            'reason' => ['required'],
            'message' => Rule::when($request['reason'] == 'Other', ['required']),
        ], [
            'message.required' => "Required to Enter Other Message",
        ]);
        if($validated->fails()) return back()->with('error', $validated->errors()->all());
        $validated = $validated->validate();
        if($validated['reason'] == "Other") $validated['reason'] = $validated['message'];
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        $reservation = Reservation::findOrFail($online_payment->reservation_id);

        if($this->attemptPayment($reservation)){
            $reservation->payment_cutoff = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
            $reservation->save();
            $online_payment->approval = 0;
            $online_payment->save();
            $count = OnlinePayment::where('reservation_id', $reservation->id)->whereNotNull('approval')->count();
    
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
                'body' => 'Reason:  ' .  $validated['reason'] . '. We will give you a chance to pay again ('.(4 - $count).' attempt only)',
                'link' => $url,
                'payment_cutoff' => Carbon::createFromFormat('Y-m-d H:i:s', $reservation->payment_cutoff)->setTimezone('UTC')->format('M j, Y') . '(UTC)',
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.online-payment-mail'))->onQueue(null));
    
        }
        
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
                'amount' => ['required', 'numeric', Rule::when($reservation->balance() >= 1000, 'min:1000')],
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
                if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show.online.payment', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));

                $this->employeeLogNotif('Fill the Payment of Force Payment for ' . $reservation->userReservation->name() . ' in ' . $validated['amount'] . ' pesos', route('system.reservation.show', $id));
                return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' was now paid on ₱ ' . number_format($validated['amount'], 2));
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
                    $tour = TourMenu::withTrashed()->find($item);
                    $temp[$key]['id'] = $tour->id;
                    $temp[$key]['title'] = $tour->tourMenu->title;
                    $temp[$key]['type'] =  $tour->type . '('.$tour->pax .' guest)';
                    $temp[$key]['price'] = number_format($tour->price, 2);
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
                $tour_menu = TourMenu::find($item);
                $transaction['TA'.$item][now('Asia/Manila')->format('YmdHis')] = [
                    'id' => $tour_menu->id,
                    'price' => $tour_menu->price,
                    'title' => $tour_menu->tourMenu->title,
                    'type' => $tour_menu->type,
                    'pax' => $tour_menu->pax,
                    'tpx' => $validated['new_pax'],
                    'used' => false,
                    'amount' => ((double)$tour_menu->price ?? 0) * (int)$validated['new_pax'],
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
            // foreach($transaction as $key => $item){
            //     if(array_key_exists('OA'.$adddon->id, $transaction)) {
            //         dd(array_key_exists('OA'.$adddon->id, $transaction));
            //         $qty = $item['pcs'];
            //     }
            // }
            $transaction['OA'.$id][now('Asia/Manila')->format('YmdHis')] = [
                'title' => $adddon->title,
                'amount' => $adddon->price * (int)$validated['pcs'],
                'pcs' => $validated['pcs'],
                'price' => $adddon->price ,
                'used' => false,
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
        if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
        $this->employeeLogNotif('Add Addons Package of ' . $reservation->userReservation->name() . '(' . $type . ')');
        if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Other Add-ons for '.$reservation->userReservation->name().' was successful');
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
            $extended = Carbon::createFromFormat('Y-m-d', $reservation->check_out)->addDays((int)$validated['no_days'])->format('Y-m-d');
            $transaction = $reservation->transaction;
            $reservation->check_out = $extended;
            $reservation->save();

            foreach($transaction ?? [] as $key => $item){
                if (strpos($key, 'rid') !== false) {
                    $rateID = (int)str_replace('rid','', $key);
                    $transaction['rid'.$rateID]['amount'] = $transaction['rid'.$rateID]['price'] * $reservation->getNoDays();
                    if(isset($transaction['payment']['discountPerson'])){
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
                'body' => "Extend Days was updated in  just ".($validated['no_days'] > 1 ? $validated['no_days'] . "days" : $validated['no_days'] . "day") . "(Check-in: ".Carbon::createFromFormat('Y-m-d', $reservation->check_in)->setTimezone('UTC')->format('F j, Y')." and Check-out: ".Carbon::createFromFormat('Y-m-d', $reservation->check_in)->setTimezone('UTC')->format('F j, Y')." UTC)",
            ];
            if(isset($reservation->user_id)) $reservation->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($reservation->id)) ,$details['body'], $details, 'reservation.mail'))->onQueue(null));
            $this->employeeLogNotif('Add days of Extend Day for ' . $reservation->userReservation->name(), route('system.reservation.show', encrypt($reservation->id)));
            if($updated) return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', $reservation->userReservation->name() . ' was extend in ' . ($validated['no_days'] > 1 ? $validated['no_days'] . ' days' : $validated['no_days'] . ' day'));
        // }
        // catch(Exception $e){
        //     return redirect()->route('system.reservation.home');
        // }
    }
    public function updateUsed(Request $request, $id, $key){
        // dd($request->all());
        $validated = Validator::make($request->all(), [
            'used' => ['required'],
        ]);
        if($validated->fails()) return back()->with('error', $validated->erros()->all());
        $rlist = Reservation::findOrFail(decrypt($id));
        $transaction = $rlist->transaction ?? [];
        $validated = $validated->validate();
        if($validated['used'] === 'y') $used = true;
        else $used = false;

        $usedKey = decrypt($key);
        $usedKey = explode("(_)", $usedKey);
        // dd($usedKey);
        $name = 'This Tour ';
        if(count($usedKey) == 2){
            if(array_key_exists($usedKey[0], $transaction) && array_key_exists($usedKey[1], $transaction[$usedKey[0]] ?? [])){
                $transaction[$usedKey[0]][$usedKey[1]]['used'] = $used ?? false;
                $name = $transaction[$usedKey[0]][$usedKey[1]]['title'];
            }
        } 
        else{
            if(array_key_exists($usedKey[0], $transaction)){
                $transaction[$usedKey[0]]['used'] = $used ?? false;
                $name = $transaction[$usedKey[0]]['title'];
            }
        }
        $rlist->transaction = $transaction;
        $rlist->save();
        return back()->with('success', $name . '  ' . ' was changed into ' . ($used ? 'Used' : 'Not Used'));
    }
}
