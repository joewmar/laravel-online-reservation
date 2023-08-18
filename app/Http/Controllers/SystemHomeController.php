<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use Illuminate\Http\Request;

class SystemHomeController extends Controller
{
    public function index(){
        $customers = Archive::all()->count() ?? [];
        return view('system.dashboard.index',  ['activeSb' => 'Home', 'countCus' => $customers]);
    }
}
