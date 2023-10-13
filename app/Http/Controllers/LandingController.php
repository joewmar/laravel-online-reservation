<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Feedback;
use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\TourMenu;
use App\Models\WebContent;
use App\Models\Reservation;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use App\Jobs\SendTelegramMessage;
use App\Models\System;

class LandingController extends Controller
{
    public function index(Request $request){
        $news = News::where('type', 0)->get() ?? [];
        $announcements = News::where('type', 1)->get() ?? [];
        $web_contents = WebContent::all()->first() ?? [];
        $feedbacks = Feedback::whereBetween('rating', [3, 4, 5])->latest()->get() ?? [];
        $request->session()->flash('info', 'This Website are under testing and developing');
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
        // $systemContacts = System::whereIn('status', [0, 1])->get() ?? [];
        return view('landing.contact_us', ['activeNav' => 'Contact Us', 'contacts' => $contacts]);
    }
    public function demo(){
        return view('landing.demo', [
            'tour_lists' => TourMenuList::all(), 
            'tour_category' => TourMenuList::distinct()->get('category'), 
        ]);
    }
    // public function testing(){
    //     $reservation = Reservation::all()->firstOrFail();
    //     $reference = WebContent::all()->firstOrFail()->payment['paypal'] ?? [];
    //     foreach($reference as $key => $item){
    //         if($reference[$key]['priority'] === true){
    //             $reference = $reference[$key];
    //             break;
    //         }
    //     }
    //     // $references = $references->payment['gcash'];
    //     // if(!($reservation->status() === 'Confirmed' && $reservation->payment_method === 'Gcash'))  abort(404);
    //     return view('reservation.paypal.index', ['reservation' => $reservation, 'reference' => $reference]);
    // }
    

}
