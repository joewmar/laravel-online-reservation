<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(){
        return view('system.rooms.index',  ['activeSb' => 'Rooms', 'rooms' =>  Room::all()]);
    }
}
