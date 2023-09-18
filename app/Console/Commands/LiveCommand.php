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
    protected $description = 'Real-time Event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reservations = Reservation::all();
        $system_user = System::whereBetween('type', [0, 1])->get();
        foreach($reservations as $item){
            // Verify the deadline of payment then auto canceled
            if(isset($item->payment_cutoff)){
                if(Carbon::createFromFormat('Y-m-d H:i:s', $item->payment_cutoff)->timestamp <= Carbon::now('Asia/Manila')->timestamp){
                    $item->update(['status' => 5]);
                    $details = [
                        'name' => $item->userReservation->name(),
                        'title' => 'Reservation was Canceled',
                        'body' => 'Your reservation has been canceled as you did not take time pay the downpayment. If you have any concerns, you can contact the owner or personnel.'
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
                    Mail::to(env('SAMPLE_EMAIL') ?? $item->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
                    foreach($system_user as $user){
                        if(!empty($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $user->telegram_chatID), $text, $keyboard, 'bot1');
                    }
                    $this->info('Cancel due downpayment');
    
                }
            }
            $checkInToday = Carbon::now()->format('Y-m-d') . ' 9:15pm' ;
            // $this->info(Carbon::createFromFormat('Y-m-d', $item->check_in)->addDays(2)->timestamp);
            // $this->info(Carbon::createFromFormat('Y-m-d g:ia', $checkInToday)->timestamp);
            // $this->info(Carbon::createFromFormat('Y-m-d', $item->check_in)->addDays(2)->timestamp === Carbon::createFromFormat('Y-m-d g:ia', $checkInToday)->timestamp ? 'true' : 'false');

            // Verify the the not present on guesthouse then auto canceled
            if(Carbon::createFromFormat('Y-m-d', $item->check_in)->addDays(2)->timestamp <= Carbon::createFromFormat('Y-m-d g:ia', $checkInToday)->timestamp){
                $item->update(['status' => 5]);
                $details = [
                    'name' => $item->userReservation->name(),
                    'title' => 'Reservation was Canceled',
                    'body' => 'Your reservation has been canceled because you did not show up at the guesthouse. If you have any concerns, you can contact the owner or personnel.'
                ];
                $text = 
                "Did not show up at the Guesthouse!\n" .
                "Name: ". $item->userReservation->name() ."\n" . 
                "Age: " . $item->age ."\n" .  
                "Nationality: " . $item->userReservation->nationality  ."\n" . 
                "Check-in: " . Carbon::createFromFormat('Y-m-d', $item->check_in)->format('F j, Y') ."\n" . 
                "Today: " . Carbon::now('Asia/Manila')->format('F j, Y') ."\n" . 
                // Send Notification to 
                // $keyboard = [
                //     [
                //         ['text' => 'View Log', 'url' => route('system.reservation.show', encrypt($item->id))],
                //     ],
                // ];
                Mail::to(env('SAMPLE_EMAIL') ?? $item->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
                foreach($system_user as $user){
                    if(!empty($user->telegram_chatID)) telegramSendMessage(env('SAMPLE_TELEGRAM_CHAT_ID') ?? $user->telegram_chatID, $text);
                }
                $this->info('Show up of customer');
            }
        }
        unset($checkInToday,  $keyboard, $text, $details,  $system_user, $webcontent, $reservations);
        $this->info('Live Application Updated');

    }
}
