<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\System;
use App\Models\RoomList;
use App\Models\RoomRate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RoomSettingController extends Controller
{
    // Show All Rooms View
    public function index(Request $request){
        $systemType = auth()->guard('system')->user()->type;
        if($systemType === 0){
            if($request->tab == '2'){
                return view('system.setting.rooms.index', ['activeSb' => 'Rooms', 'room_rates' =>  RoomRate::all(), 'room_lists' => null]);
            }
            else{
                return view('system.setting.rooms.index',  ['activeSb' => 'Rooms', 'room_lists' =>  RoomList::all(), 'room_rates' => null]);
    
            }
        }
        else{
            abort(404);
        }
    }
    // Show Single View
    public function show(Request $request){
        $systemType = auth()->guard('system')->user()->type;
        if($systemType == 0){
            $id = decrypt($request->id);
            return view('system.setting.rooms.show',  ['activeSb' => 'Rooms', 'room_list' =>  RoomList::findOrFail($id)]);
        }
        else{
            abort(404);
        }
       
    }
    // Show Edit Form
    public function edit(Request $request){
        $systemType = auth()->guard('system')->user()->type;
        if($systemType == 0){
            $id = decrypt($request->id);
            return view('system.setting.rooms.edit',  ['activeSb' => 'Rooms', 'room_list' =>  RoomList::findOrFail($id)]);
        }
        else{
            abort(404);
        }
       
    }
    // Add New Room
    public function store(Request $request){
        $system_user = auth('system')->user();
        if($system_user->type == 0){
            $validated = $request->validate([
                'name' => ['required', Rule::unique('room_lists', 'name')],
                'min_occupancy' =>  ['required', 'numeric', 'min:1'],
                'max_occupancy' =>  ['required', 'numeric', 'min:1'],
                'location' =>  ['required'],
                'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'many_room' =>  ['required', 'numeric', 'min:1'],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if (Hash::check($validated['passcode'], $system_user->passcode)) {

                if($validated){

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
                        $saved = $room->save(); 
                        if(!$saved){ // Check if Error Save data the Room
                            $room_list = RoomList::findOrFail($room_list->id); 
                            $room_list->delete(); 
                        }
                        else $saved;
                    }
                    refreshRoomNumber();

                    return redirect()->route('system.setting.rooms.home')->with('success', 'Room Created');
                }
               
            } 
            else{
                return back()->with('error', 'Passcode Invalid');
            }
        }
        else{
            abort(404);
        }

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
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
            'many_room' =>  ['required', 'numeric', 'min:1'],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);

        if($request->hasFile('image')){  
            if($room_list->image) 
                deleteFile($room_list->image);
        
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

                refreshRoomNumber(); 
            }
            // Check if updated many_room lesst than previous many_room then delete the last
            else if($validated['many_room'] <= $room_list->many_room){

                $length =  abs((int)$room_list->many_room - (int)$validated['many_room']);
                for($count = 0; $count < $length; $count++){
                    $room = Room::where('room_id', $room_list->id)->orderBy('room_no', 'desc')->first();
                    $room->delete();   
                }
                refreshRoomNumber();    
            }
            $room_list->update($validated);
            return back()->with('success', $room_list->name .' Room was updated');

            
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }

    public function destroy(Request $request){
        $system_user = System::find(auth()->guard('system')->id());

        $validated = $request->validate([
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);

        if (Hash::check($validated['passcode'], $system_user->passcode)) {

            if(!$system_user){
                abort(403, 'Unauthorized Action');
            }

            $id = decrypt($request->id);
            $room_list =  RoomList::find($id);
            
            if($room_list->image) deleteFile($room_list->image);
    
            $room_name = $room_list->name;
            $room_list->delete();

            Room::where('room_id', $id)->delete();
            refreshRoomNumber();    
            return redirect()->route('system.setting.rooms.home')->with('success', $room_name .' Room was deleted');
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }

    // Room Rates /////////////////////////////////////////////
    public function storeRate(Request $request){
        $system_user = System::find(auth()->guard('system')->id());

        $validated = $request->validate([
            'name' => ['required', Rule::unique('room_rates', 'name')],
            'price' =>  ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/', 'min:0.01'],
            'occupancy' =>  ['required', 'numeric', 'min:1'],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);

        if (Hash::check($validated['passcode'], $system_user->passcode)) {
            if(!$system_user){
                abort(403, 'Unauthorized Action');
            }

            // Save to database and get value
            RoomRate::create($validated);  
        
            return redirect()->route('system.setting.rooms.home', 'tab=2')->with('success', 'Room Type Created');
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }


    }
    public function editRate(Request $request){
        $systemType = auth()->guard('system')->user()->type;
        if($systemType === 0){
            $id = decrypt($request->id);
            return view('system.setting.rooms.rate.edit',  ['activeSb' => 'Rooms', 'room_rate' =>  RoomRate::findOrFail($id)]);
        }
        else{
            abort(404);
        }
        
    }
    public function updateRate(Request $request){
        $id = decrypt($request->id);
        $system_user = System::find(auth()->guard('system')->id());
        $room_rate = RoomRate::find($id);

        $validated = $request->validate([
            'name' => ['required'],
            'price' =>  ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/', 'min:0.01'],
            'occupancy' =>  ['required', 'numeric', 'min:1'],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);

        if (Hash::check($validated['passcode'], $system_user->passcode)) {
            if(!$system_user){
                abort(403, 'Unauthorized Action');
            }
            // Update Data Process
            $room_rate->update($validated) ;
        
            return redirect()->route('system.setting.rooms.home', 'tab=2')->with('success', $room_rate->name . ' was Updated');
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }


    }
    public function destroyRate(Request $request){
        $system_user = System::find(auth()->guard('system')->id());

        $validated = $request->validate([
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        if(!$system_user){
            abort(403, 'Unauthorized Action');
        }
        if (Hash::check($validated['passcode'], $system_user->passcode)) {

            $room_rate = RoomRate::findOrFail(decrypt($request->id));
            $room_rate_name = $room_rate->name;
            $room_rate->delete();
        
            return redirect()->route('system.setting.rooms.home', 'tab=2')->with('success', $room_rate_name .' was deleted');
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }
}
