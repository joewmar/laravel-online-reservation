<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Models\Feedback;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\OnlinePayment;
use App\Jobs\SendTelegramMessage;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class ReservationTwoController extends Controller
{
    private function systemNotification($text, $link = null){
        $systems = System::whereBetween('type', [0, 1])->get();
        $keyboard = null;
        if(isset($link)){
            $keyboard = [
                [
                    ['text' => 'View', 'url' => $link],
                ],
            ];
        }
        foreach($systems as $system){
            if(isset($system->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $system->telegram_chatID), $text, $keyboard, 'bot2'));
        }
        Notification::send($systems, new SystemNotification(Str::limit($text, 10), $text, route('system.notifications')));
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
                return back()->with('error', $validator);
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
                    // Send Notification 
                    $this->systemNotification($text, route('system.reservation.show.online.payment', encrypt($reservation->id)));
                    $text = null;
                    return redirect()->route('reservation.payment.done', encrypt($sended->id));
                }
            }
        }
        else{
            abort(404);
        }
        

    }
    public function donePayment($id){
        $online_payment = OnlinePayment::findOrFail(decrypt($id));
        if(!($online_payment->reserve->status() === 'Confirmed')) abort(404);
        $contacts = WebContent::all()->first()->contact ?? [];
        if($online_payment) return view('reservation.payment-done', ['contacts' => $contacts]);
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
    public function bankTransfer($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $reference = WebContent::all()->first()->payment['bankTransfer'] ?? [];
        foreach($reference as $key => $item){
            if($reference[$key]['priority'] === true){
                $reference = $reference[$key];
                break;
            }
        }
        if(!($reservation->status() === 'Confirmed' && $reservation->payment_method === 'Bank Transfer'))  abort(404);
        return view('reservation.BT.index', ['reservation' => $reservation, 'reference' => $reference]);
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
            'name' => $reservation->userReservation->name(),
            'rating' => (int)$validated['rating'],
            'message' => $validated['message'],
        ]);
        if($created) return redirect()->route('home')->with('success', 'Thank you for your feedback. Come Again');
    }
}
