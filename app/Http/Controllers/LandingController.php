<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\TourMenuList;
use App\Models\WebContent;

class LandingController extends Controller
{
    public function index(){
        $news = News::all() ?? [];
        $web_contents = WebContent::all()->first() ?? [];
        return view('index', ['activeNav' => 'Home', 'news' => $news, 'web_contents' => $web_contents]);
    }
    public function aboutus(){
        return view('landing.about_us', ['activeNav' => 'About Us']);
    }
    public function services(){
        $rooms = RoomList::all() ?? [];
        $rates = RoomRate::all() ?? [];
        $tour_menu = TourMenu::all() ?? [];
        $categories = TourMenuList::distinct()->get('category');
        return view('accomodations', ['activeNav' => 'Services', 'rooms' => $rooms, 'rates' => $rates, 'tour_menu' =>$tour_menu, 'categories' => $categories]);
    }
    public function contact(){
        $web_contents = WebContent::all()->first() ?? [];
        // return view('landing.accomodations', ['activeNav' => 'Services', 'rooms' => $rooms, 'rates' => $rates]);
    }
}
