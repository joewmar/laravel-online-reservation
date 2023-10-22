<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Archive;
use App\Models\Feedback;
use App\Models\WebContent;
use App\Models\Reservation;
use Illuminate\Http\Request;

class SystemHomeController extends Controller
{
    public function index(){
        $customers = Archive::all()?? [];
        $feedbacks = Feedback::all() ?? [];
        $reservations = Reservation::all() ?? [];
        $rooms = Room::all();
        $count = $feedbacks->count() ?? 0;
        $total = $feedbacks->sum('rating') ?? 0;
        $ratingAverage = 0;
        $ratingText = 'None';

        if(!($count === 0 && $total == 0)){
            $ratingAverage =  (int)round($total / $count);
            if($ratingAverage  === 1) $ratingText = 'Very Dissatisfied';
            if($ratingAverage  === 2) $ratingText = 'Dissatisfied';
            if($ratingAverage  === 3) $ratingText = 'Neutral';
            if($ratingAverage  === 4) $ratingText = 'Satisfied';
            if($ratingAverage  === 5) $ratingText = 'Very Satisfied';
        } 

        
        $today = now()->format('Y-m-d');
        // $totalSales = Archive::whereDate('created_at', $today)->sum('total');
        $totalSales = Archive::whereDate('created_at', $today)->sum('total') ?? 00.0;
        
        // Fetch additional data for chart
        $hourlySales = Archive::selectRaw('HOUR(created_at) as hour, SUM(total) as sales')
        // $hourlySales = Archive::selectRaw('HOUR(created_at) as hour, SUM(total) as sales')
            // ->whereDate('created_at', $today)
            ->whereDate('created_at', $today)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $nationalities = Archive::groupBy('nationality')->selectRaw('nationality, count(*) as count')->get();
        $roomReserved = 0;
        $avail = 0;
        $today = Carbon::now('Asia/Manila')->format('Y-m-d');
        $r_lists = Reservation::where('status', 2)->pluck('id');

        foreach($rooms as $key => $room){
            $count_paxes = 0;
            foreach($r_lists as $r_list){
                $rs= Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->where('id', $room->id)->get();
                foreach($rs as $room) $count_paxes += $room->customer[$r_list];
            }
            if($count_paxes >= $room->room->max_occupancy) $roomReserved++;
            
            else $avail++;
            

        }

        return view('system.dashboard.index',  [
            'activeSb' => 'Home', 
            'customers' => $customers, 
            'nationalities' => $nationalities, 
            'totalSales' => $totalSales, 
            'today' => $today, 
            'hourlySales' => $hourlySales, 
            'rooms' => $rooms, 
            'avail' => $avail, 
            'reserved' => $roomReserved, 
            'reservations' => $reservations, 
            'feedbacks' => $feedbacks, 
            'ratingAverage' => $ratingAverage,
            'ratingText' => $ratingText,
        ]);
    }
}
