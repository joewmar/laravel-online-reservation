<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use Carbon\Carbon;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class RoomController extends Controller
{
    public function index(){
        $accommodations = Accommodation::all();
        return view('system.setting.rooms.index',  ['activeSb' => 'Rooms', 'accommodations' => $accommodations]);
    }

    // Add New Room
    public function store(Request $request){
        $validated = $request->validate([
            'name' => ['required', Rule::unique('accommodations', 'name')],
            'type'=> ['required'],
            'amenities'=> ['max:100'],
            'description'=> ['max:255'],
            'occupancy' =>  ['required', 'numeric'],
            'location' =>  ['required'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'size:5024'],
            'many_room' =>  ['required', 'numeric', 'min:1'],
        ]);

        if($request->hasFile('image')){                          // storage/app/logos
            $validated['image'] = $request->file('image')->store('accommodations', 'public');
        }

        // Save to database and get value
        $accommodations = Accommodation::create($validated);  

        // Get Count of Room
        $room_count = (int)$accommodations->many_room; 
        
        // Check if null and get last of room number
        $room_no = Room::where('accommodation_id', $accommodations->id)->orderBy('id', 'desc')->value('room_no');
        if($room_no === null){
            $room_no = 1;
        }
        
        // Add Room One by one
        for($count = 0; $count < $room_count; $count++){
            $room = new Room();
            $room->accommodation_id = $accommodations->id; // Set the appropriate accommodation_id value
            $room->room_no = $room_no; 
            $room->availability = false; 
            $room->save(); 
            $room_no++;
        }
        return redirect()->route('system.setting.rooms')->with('success', 'Room Created');
    }
}
