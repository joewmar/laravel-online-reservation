<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoomController extends Controller
{
    private $system_user;

    public function __construct()
    {
        $this->system_user = auth('system');
        $this->middleware(function ($request, $next){
            if($this->system_user->user()->type == 0 || in_array("Rooms",$this->system_user->user()->modules)) return $next($request);
            else abort(404);
        });
    }
    private function searchAvailability (string $dates, $reservation = false){
        $rooms = Room::all();
        $roomReserved = [];
        $r_lists = Reservation::whereDate('check_in', '<=', $dates)
        ->whereDate('check_out', '>=', $dates)
        ->pluck('id');
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
        if($reservation) return $r_lists->toArray();
        else return $roomReserved;
    }
    public function index(Request $request){
        $reserved = $this->searchAvailability(Carbon::now('Asia/Manila')->format('Y-m-d'));
        $reservedIDs = $this->searchAvailability(Carbon::now('Asia/Manila')->format('Y-m-d'), true);
        if(isset($request['date'])){
            $reserved = $this->searchAvailability($request['date']);
            $reservedIDs = $this->searchAvailability($request['date'], true);
        }
        return view('system.rooms.index',  ['activeSb' => 'Rooms', 'rooms' =>  Room::all(), 'reserved' => $reserved ?? [], 'reservedIDS' => $reservedIDs]);
    }
    public function search(Request $request){
        // dd($request->all());
        if( $request['name']){
            $search = ['search' => $request['name']];
        }
        if( $request['date']){
            $search = ['date' => $request['date']];
        }
        return redirect()->route('system.rooms.home', $search ?? []);
    }
}
