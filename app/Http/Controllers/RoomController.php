<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\System;
use App\Models\RoomList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Laravel\Ui\Presets\React;

class RoomController extends Controller
{
    // Reboot the number of room
    private function refreshRoomNumber(){
        // Update to blank
        $rooms = Room::all(); // Retrieve all the rooms
        if($rooms){
            $newRoomNo = 1;
            foreach ($rooms as $room) {
                // Perform your logic to update the room_no field here
            
                $room->room_no = $newRoomNo;
                $room->save(); // Save the updated room_no value
                $newRoomNo++;
            }
        }
    }
    // Show All Rooms View
    public function index(){
        return view('system.setting.rooms.index',  ['activeSb' => 'Rooms', 'room_lists' =>  RoomList::all()]);
    }
    // Show Single View
    public function show(Request $request){
        $id = decrypt($request->id);
        return view('system.setting.rooms.show',  ['activeSb' => 'Rooms', 'room_list' =>  RoomList::findOrFail($id)]);
    }
    // Show Edit Form
    public function edit(Request $request){
        $id = decrypt($request->id);
        return view('system.setting.rooms.edit',  ['activeSb' => 'Rooms', 'room_list' =>  RoomList::findOrFail($id)]);
    }
    // Process update room
    public function update(Request $request){
        $id = decrypt($request->id);
        $system_user = System::find(auth()->guard('system')->id());
        $room_list = RoomList::find($id);

        // Make sure logged in user is owner and system user
        if(!$system_user){
            abort(403, 'Unauthorized Action');
        }
        
        // Validations
        $validated = $request->validate([
            'name' => ['required'],
            'min_occupancy' =>  ['required', 'numeric', 'min:1'],
            'max_occupancy' =>  ['required', 'numeric', 'min:1'],
            'location' =>  ['required'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'size:5024'],
            'many_room' =>  ['required', 'numeric', 'min:1'],
            'passcode' =>  ['required', 'numeric', 'min:0000', 'max:9999'],
        ]);

        if($request->hasFile('image')){                          
            $validated['image'] = $request->file('image')->store('room_lists', 'public');
        }

        //Get the code if it match on system
        if (Hash::check($validated['passcode'], $system_user->passcode)) {
            // Check if updated many_room greater than previous many_room the add in room table
            if($validated['many_room'] >= $room_list->many_room){
                $length =  abs((int)$validated['many_room'] - (int)$room_list->many_room);
                for($count = 0; $count < $length; $count++){
                    $room = new Room();
                    $room->room_id = $room_list->id; // Set the appropriate accommodation_id value
                    $room->room_no = 0;
                    $room->save(); 
                }

                $this->refreshRoomNumber(); 
            }
            // Check if updated many_room lesst than previous many_room then delete the last
            else if($validated['many_room'] <= $room_list->many_room){

                $length =  abs((int)$room_list->many_room - (int)$validated['many_room']);
                for($count = 0; $count < $length; $count++){
                    $room = Room::where('room_id', $room_list->id)->orderBy('room_no', 'desc')->first();
                    $room->delete();   
                }
                $this->refreshRoomNumber();    
            }
            $room_list->update($validated);
            return back()->with('success', $room_list->name .' Room was updated');

            
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }

    // Add New Room
    public function store(Request $request){
        $validated = $request->validate([
            'name' => ['required', Rule::unique('room_lists', 'name')],
            'min_occupancy' =>  ['required', 'numeric', 'min:1'],
            'max_occupancy' =>  ['required', 'numeric', 'min:1'],
            'location' =>  ['required'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
            'many_room' =>  ['required', 'numeric', 'min:1'],
        ]);

        if($request->hasFile('image')){                          // storage/app/logos
            $validated['image'] = $request->file('image')->store('room_lists', 'public');
        }

        // Save to database and get value
        $room_list = RoomList::create($validated);  

        // Get Count of Room
        $room_count = (int)$room_list->many_room; 
        
        // Add Room One by one
        for($count = 0; $count < $room_count; $count++){
            $room = new Room();
            $room->room_id = $room_list->id; // Set the appropriate accommodation_id value
            $room->room_no = 0; 
            $room->save(); 
        }
        $this->refreshRoomNumber();

        return redirect()->route('system.setting.rooms')->with('success', 'Room Created');
    }
    public function destroy(Request $request){
        $system_user = System::find(auth()->guard('system')->id());

        $validated = $request->validate([
            'passcode' =>  ['required', 'numeric', 'min:0000', 'max:9999'],
        ]);

        if (Hash::check($validated['passcode'], $system_user->passcode)) {

            if(!$system_user){
                abort(403, 'Unauthorized Action');
            }
            $id = decrypt($request->id);
            $room_list =  RoomList::find($id);
            $room_name = $room_list->name;
            $room_list->delete();

            Room::where('room_id', $id)->delete();
            $this->refreshRoomNumber();    
            return redirect()->route('system.setting.rooms')->with('success', $room_name .' Room was deleted');
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }
}
