<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        foreach($reservations as $item){
            // Verify the deadline of payment then auto canceled
            if((string)$item->payment_cutoff === Carbon::now()->format('Y-m-d H:i:s')){
                
            }
            // Verify the the not present on guesthouse then auto canceled
            if((string)$item->check_in === Carbon::now()->addDays(3)->format('Y-m-d H:i:s')){
                
            }
        }
    }
}
