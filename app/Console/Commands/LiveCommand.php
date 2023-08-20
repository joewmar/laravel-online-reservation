<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Reservation;
use App\Mail\ReservationMail;
use App\Models\System;
use App\Models\WebContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class LiveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Live Running...';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reservations = Reservation::all();
        $webcontent = WebContent::all()->first();
        $system_user = System::all()->where('type','<=', 1)->where('type','>=', 0);
        foreach($reservations as $item){
            // Verify the deadline of payment then auto canceled
            if((string)$item->payment_cutoff === Carbon::now()->format('Y-m-d H:i:s')){
                $details = [
                    'name' => $item->userReservation->name(),
                    'title' => 'Reservation was Canceled',
                    'body' => 'Your reservation has been canceled as you did not take time to arrange the requirements. If you have any concerns, you can contact the owner or personnel.'
                ];
                $text = 
                "Cancel Reservation!\n" .
                "Name: ". $reservations->userReservation->name() ."\n" . 
                "Age: " . $reservations->age ."\n" .  
                "Nationality: " . $reservations->userReservation->nationality  ."\n" . 
                "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservations->check_in)->format('F j, Y') ."\n" . 
                "Check-out: " . Carbon::createFromFormat('Y-m-d', $reservations->check_out)->format('F j, Y') ."\n" . 
                "Type: " . $reservations->accommodation_type ;
                // Send Notification to 
                $keyboard = [
                    [
                        ['text' => 'View Details', 'url' => route('system.reservation.show', encrypt($reservations->id))],
                    ],
                ];
                Mail::to(env('SAMPLE_EMAIL', $item->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
                foreach($system_user as $user){
                    if(!empty($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, $keyboard, 'bot1');
                }
            }
            // Verify the the not present on guesthouse then auto canceled
            $checkInToday = Carbon::now()->format('Y-m-d') . ' 9:15pm' ;
            if(Carbon::createFromFormat('Y-m-d', $item->check_in )->addDays(2)->format('Y-m-d H:i:s') === Carbon::createFromFormat('Y-m-d g:ia', $checkInToday)->format('Y-m-d H:i:s')){
                $details = [
                    'name' => $item->userReservation->name(),
                    'title' => 'Reservation was Canceled',
                    'body' => 'Your reservation has been canceled because you did not show up at the guesthouse. If you have any concerns, you can contact the owner or personnel.'
                ];
                $text = 
                "Did not show up at the Guesthouse!\n" .
                "Name: ". $reservations->userReservation->name() ."\n" . 
                "Age: " . $reservations->age ."\n" .  
                "Nationality: " . $reservations->userReservation->nationality  ."\n" . 
                "Check-in: " . Carbon::createFromFormat('Y-m-d', $reservations->check_in)->format('F j, Y') ."\n" . 
                "Today: " . Carbon::now()->format('F j, Y') ."\n" . 
                // Send Notification to 
                $keyboard = [
                    [
                        ['text' => 'View Details', 'url' => route('system.reservation.show', encrypt($reservations->id))],
                    ],
                ];
                Mail::to(env('SAMPLE_EMAIL', $item->userReservation->email))->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
                foreach($system_user as $user){
                    if(!empty($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, $keyboard, 'bot1');
                }
            }
        }
        // Do Automatic Resevation Operation
        if(isset($webcontent->from) && isset($webcontent->to)){
            if(Carbon::now()->format('Y-m-d') === $webcontent->from){
                $webcontent->operation = false;
            }
            if(Carbon::now()->yesterday()->format('Y-m-d') === $webcontent->to){
                $webcontent->operation = true;
            }
        }
        unset($checkInToday,  $keyboard, $text, $details,  $system_user, $webcontent, $reservations);

    }
}
