<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\System;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use App\Mail\ReservationMail;
use App\Models\OnlinePayment;
use Illuminate\Console\Command;
use App\Notifications\UserNotif;
use App\Jobs\SendTelegramMessage;
use App\Models\News;
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
        $system_user = System::whereIn('type', [0, 1])->get();
        $wb = WebContent::all()->first();
        if(!empty($wb)){
            if(isset($wb->from) && Carbon::createFromFormat('Y-m-d', $wb->from, 'Asia/Manila')->timestamp <= Carbon::createFromFormat('Y-m-d', Carbon::now('Asia/Manila'))->timestamp){
                $wb->update(['operation' => false]);
            }
            if(isset($wb->to) && Carbon::createFromFormat('Y-m-d', $wb->to, 'Asia/Manila')->timestamp <= Carbon::createFromFormat('Y-m-d', Carbon::now('Asia/Manila'))->timestamp){
                $wb->update(['operation' => true]);
            }
        }
        foreach(Reservation::all() as $item){
            // Verify the deadline of payment then auto canceled
            if(isset($item->payment_cutoff)){
                if(Carbon::createFromFormat('Y-m-d H:i:s', $item->payment_cutoff)->timestamp <= Carbon::now('Asia/Manila')->timestamp && !($item->downpayment() >= 1000)){
                    $item->update(['status' => 5]);
                    $details = [
                        'name' => $item->userReservation->name(),
                        'title' => 'Reservation was Canceled',
                        'body' => 'Your reservation has been canceled as you did not take time pay the downpayment. If you have any concerns, you can contact the owner or personnel.'
                    ];
                    $text = 
                    "Cancel Reservation!\n" .
                    "Name: ". $item->userReservation->name() ."\n" . 
                    "Age: " . $item->age ."\n" .  
                    "Nationality: " . $item->userReservation->nationality  ."\n" . 
                    "Check-in: " . Carbon::createFromFormat('Y-m-d', $item->check_in)->format('F j, Y') ."\n" . 
                    "Check-out: " . Carbon::createFromFormat('Y-m-d', $item->check_out)->format('F j, Y') ."\n" . 
                    "Type: " . $item->accommodation_type ;
                    // Send Notification to 
                    $keyboard = [
                        [
                            ['text' => 'View Details', 'url' => route('system.reservation.show', encrypt($item->id))],
                        ],
                    ];
                    $item->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab' => 'canceled'])) ,$details['body'], $details, 'reservation.mail')));
                    foreach($system_user as $user){
                        if(!empty($user->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID') ?? $user->telegram_chatID, $text, $keyboard));
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
                "Today: " . Carbon::now('Asia/Manila')->format('F j, Y');
                // Send Notification to 
                $keyboard = [
                    [
                        ['text' => 'View ', 'url' => route('system.reservation.show', encrypt($item->id))],
                    ],
                ];
                $item->userReservation->notify((new UserNotif(route('user.reservation.home', Arr::query(['tab' => 'canceled'])) ,$details['body'], $details, 'reservation.mail')));
                foreach($system_user as $user){
                    if(!empty($user->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID') ?? $user->telegram_chatID, $text, $keyboard));
                }
                $this->info('Show up of customer');
            }
            // Notify of check in's (System)
            if(Carbon::createFromFormat('Y-m-d g:ia', $item->check_in, 'Asia/Manila')->yesterday()->timestamp <= Carbon::now('Asia/Manila')->timestamp){
                $system_user = System::all();

                $text = 
                "Check-in Alert !\n" .
                "Name: ". $item->userReservation->name() ."\n" . 
                "Age: " . $item->age ."\n" .  
                "Nationality: " . $item->userReservation->nationality  ."\n" . 
                "Check-in: " . Carbon::createFromFormat('Y-m-d', $item->check_in)->format('F j, Y') ."\n" . 
                "Check-out: " . Carbon::createFromFormat('Y-m-d', $item->check_out)->format('F j, Y') ."\n" . 
                "Type: " . $item->accommodation_type ;
                // Send Notification to 
                $keyboard = [
                    [
                        ['text' => 'View Check-in', 'url' => route('system.reservation.show', encrypt($item->id))],
                    ],
                ];
                foreach($system_user as $user){
                    if(!empty($user->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID') ?? $user->telegram_chatID, $text, $keyboard));
                }
                $this->info('Check-in on System Notifcation ');
            }
            // Notify of check out's (System)
            if(Carbon::createFromFormat('Y-m-d g:ia', $item->check_out . ' 7:00am', 'Asia/Manila')->timestamp <= Carbon::now('Asia/Manila')->timestamp && $item->accommodation_type != 'Day Tour'){
                $system_user = System::all();

                $text = 
                "Check-out Alert !\n" .
                "Name: ". $item->userReservation->name() ."\n" . 
                "Age: " . $item->age ."\n" .  
                "Nationality: " . $item->userReservation->nationality  ."\n" . 
                "Check-out: " . Carbon::createFromFormat('Y-m-d', $item->check_out)->format('F j, Y') ."\n" . 
                "Today: " . Carbon::now('Asia/Manila')->format('F j, Y');
                "Type: " . $item->accommodation_type ;

                // Send Notification to 
                $keyboard = [
                    [
                        ['text' => 'View Check-out', 'url' => route('system.reservation.show', encrypt($item->id))],
                    ],
                ];
                foreach($system_user as $user){
                    if(!empty($user->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID') ?? $user->telegram_chatID, $text, $keyboard));
                }
                $this->info('Check-out on System Notifcation ');
            }
            // Notify of check in's (User)
            if(Carbon::createFromFormat('Y-m-d', $item->check_in)->yesterday()->timestamp <= Carbon::now()->timestamp){

                $details = [
                    'name' => $item->userReservation->name(),
                    'title' => 'Check-in Alert',
                    'body' => 'Your scheduled date is coming soon. Please be at the guesthouse. If you have any concerns, you can contact the owner or personnel.'
                ];
                $item->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($item->id)) ,$details['body'], $details, 'reservation.mail')));
                $this->info('Check-in on User Notifcation ');
            }
            // Notify of check out's (User)
            if(Carbon::createFromFormat('Y-m-d', $item->check_out)->timestamp <= Carbon::now()->timestamp && $item->accommodation_type != 'Day Tour'){
                $details = [
                    'name' => $item->userReservation->name(),
                    'title' => 'Check-out Alert',
                    'body' => 'Be prepared due as your room stay time up. If you have any concerns, you can contact the owner or personnel.'
                ];
                $item->userReservation->notify((new UserNotif(route('user.reservation.show', encrypt($item->id)) ,$details['body'], $details, 'reservation.mail')));
                $this->info('Check-out on User Notifcation ');
            }
        }
        // Automatic Delete After One Month
        foreach(OnlinePayment::all() as $item){
            if(Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at)->addMonth(1)->timestamp <= Carbon::now('Asia/Manila')->timestamp){
                $item->delete();
            }
        }
        foreach(News::all() as $new){
            if((isset($new->from) && isset($new->to)) && Carbon::createFromFormat('Y-m-d', $new->to)->timestamp <= Carbon::now()->timestamp){
                $new->delete();
            }
        }
        foreach(User::all() as $user){
            $rlist = Reservation::where('user_id', $user->id);
            if($rlist->count() == 5){
                $delete = $rlist->oldest('created_at')->first();
                $delete->delete();
            }
        }
        unset($checkInToday,  $keyboard, $text, $details,  $system_user, $webcontent, $reservations);
        $this->info('Live Application Updated');

    }
}
