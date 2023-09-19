<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\News;
use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\TourMenuList;
use App\Models\WebContent;

class LandingController extends Controller
{
    public function index(){
        $news = News::where('type', 0)->get() ?? [];
        $announcements = News::where('type', 1)->get() ?? [];
        $web_contents = WebContent::all()->first() ?? [];
        $feedbacks = Feedback::whereBetween('rating', [3, 4, 5])->latest()->get() ?? [];
        return view('index', ['activeNav' => 'Home', 'news' => $news, 'web_contents' => $web_contents, 'announcements' => $announcements, 'feedbacks' => $feedbacks]);
    }
    public function aboutus(){
        return view('landing.about_us', ['activeNav' => 'About Us']);
    }
    public function services(){
        $rooms = RoomList::all() ?? [];
        $rates = RoomRate::all() ?? [];
        $tour_menu = TourMenu::all() ?? [];
        $categories = TourMenuList::distinct()->get('category');
        $tours = WebContent::all()->first()->tour ?? [];
        return view('landing.accomodations', ['activeNav' => 'Tour', 'rooms' => $rooms, 'rates' => $rates, 'tour_menu' =>$tour_menu, 'categories' => $categories, 'tours' => $tours]);
    }
    public function contact(){
        $contacts = WebContent::all()->first()->contact ?? [];
        return view('landing.contact_us', ['activeNav' => 'Contact Us', 'contacts' => $contacts]);
    }
    public function demo(){
        return view('landing.demo', [
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
        ]);
    }

}
