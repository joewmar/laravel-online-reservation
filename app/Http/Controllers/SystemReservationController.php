<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class SystemReservationController extends Controller
{
    public function index(){
        return view('system.reservation.index',  ['activeSb' => 'Reservation', 'r_list' => Reservation::all()]);
    }
    public function show($id){
        $id = decrypt($id);
        return view('system.reservation.show',  ['activeSb' => 'Reservation', 'r_list' => Reservation::findOrFail($id)]);
    }
}
