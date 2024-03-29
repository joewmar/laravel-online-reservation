<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\System;
use App\Models\RoomList;
use App\Models\RoomRate;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RoomSettingController extends Controller
{
    private $system_user;

    public function __construct()
    {
        $this->system_user = auth('system'); 
        $this->middleware(function ($request, $next){
            if(!($this->system_user->user()->type === 0)) abort(404);
            return $next($request);
        });
    }
    private function employeeLogNotif($text){
        AuditTrail::create([
            'system_id' => $this->system_user->user()->id,
            'role' => $this->system_user->user()->type ?? '',
            'action' => $text,
            'module' => 'Employee Account',
        ]);
    }
    // Show All Rooms View
    public function index(Request $request){
        if($request->tab == '2'){
            return view('system.setting.rooms.index', ['activeSb' => 'Rooms', 'room_rates' =>  RoomRate::all(), 'room_lists' => null]);
        }
        else{
            return view('system.setting.rooms.index',  ['activeSb' => 'Rooms', 'room_lists' =>  RoomList::all(), 'room_rates' => null]);
        }
    }
    public function create(){
        return view('system.setting.rooms.create',  ['activeSb' => 'Rooms']);
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
    // Add New Room
    public function store(Request $request){
        $validated = $request->validate([
            'name' => ['required', Rule::unique('room_lists', 'name')],
            'max_occupancy' =>  ['required', 'numeric', 'min:1'],
            'location' =>  ['nullable'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
            'many_room' =>  ['required', 'numeric', 'min:1'],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);

        if (Hash::check($validated['passcode'], $this->system_user->user()->passcode)) {

            if($validated){

                if($request->hasFile('image')){                          // storage/app/logos
                    $validated['image'] = saveImageWithJPG($request, 'image', 'rooms');
                }
        
                // Save to database and get value
                $room_list = RoomList::create($validated);  
        
                // Get Count of Room
                $room_count = (int)$room_list->many_room; 
                
                // Add Room One by one
                for($count = 0; $count < $room_count; $count++){
                    $room = new Room();
                    $room->roomid = $room_list->id; // Set the appropriate accommodation_id value
                    $room->room_no = 0; 
                    $saved = $room->save(); 
                    if(!$saved){ // Check if Error Save data the Room
                        $room_list = RoomList::findOrFail($room_list->id); 
                        $room_list->delete(); 
                    }
                    else $saved;
                }
                refreshRoomNumber();
                $message = $room_list->name . ' Room Created';
                $this->employeeLogNotif($message);
                return redirect()->route('system.setting.rooms.home')->with('success', $message);
            }
            
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }
    // Process update room
    public function update(Request $request){
        $id = decrypt($request->id);
        $room_list = RoomList::find($id);

        // Validations
        $validated = $request->validate([
            'name' => ['required'],
            'max_occupancy' =>  ['required', 'numeric', 'min:1'],
            'location' =>  ['nullable'],
            'image_clear' =>  ['required'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
            'many_room' =>  ['required', 'numeric', 'min:1'],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        if($request->hasFile('image')){  
            if(isset($room_list->image)) deleteFile($room_list->image);
            $validated['image'] = saveImageWithJPG($request, 'image', 'rooms');
        }
        if((bool)$validated['image_clear'] == true) {
            deleteFile($room_list->image);
            $room_list->image = '';
            $room_list->save();
        }

        //Get the code if it match on system
        if (Hash::check($validated['passcode'], $this->system_user->user()->passcode)) {
            // Check if updated many_room greater than previous many_room the add in room table
            if($validated['many_room'] >= $room_list->many_room){
                $length =  abs((int)$validated['many_room'] - (int)$room_list->many_room);
                for($count = 0; $count < $length; $count++){
                    $room = new Room();
                    $room->roomid = $room_list->id; // Set the appropriate accommodation_id value
                    $room->room_no = 0;
                    $room->save(); 
                }
                refreshRoomNumber(); 
            }
            // Check if updated many_room lesst than previous many_room then delete the last
            else if($validated['many_room'] <= $room_list->many_room){

                $length =  abs((int)$room_list->many_room - (int)$validated['many_room']);
                for($count = 0; $count < $length; $count++){
                    $room = Room::where('roomid', $room_list->id)->orderBy('room_no', 'desc')->first();
                    $room->delete();   
                }
                refreshRoomNumber();    
            }
            $room_list->update($validated);
            $message = $room_list->name . ' Room Updated';
            $this->employeeLogNotif($message);
            return back()->with('success', $message);
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }

    public function destroy(Request $request){
        $validated = $request->validate([
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        if (Hash::check($validated['passcode'], $this->system_user->user()->passcode)) {
            $id = decrypt($request->id);
            $room_list =  RoomList::find($id);
            
            if($room_list->image) deleteFile($room_list->image);
    
            $room_name = $room_list->name;
            $room_list->delete();

            Room::where('roomid', $id)->delete();
            refreshRoomNumber(); 
            $message = $room_name .' Room Removed';
            $this->employeeLogNotif($message);   
            return redirect()->route('system.setting.rooms.home')->with('success', );
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }

    // Room Rates /////////////////////////////////////////////
    public function createRate(){
        return view('system.setting.rooms.rate.create',  ['activeSb' => 'Rooms']);
    }
    public function storeRate(Request $request){
        $validated = $request->validate([
            'name' => ['required', Rule::unique('room_rates', 'name')],
            'price' =>  ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/', 'min:0.01'],
            'occupancy' =>  ['required', 'numeric', 'min:1'],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);

        if (Hash::check($validated['passcode'], $this->system_user->user()->passcode)) {

            // Save to database and get value
            $rate = RoomRate::create($validated);  
            $message = $rate->name .' Rate Created';
            $this->employeeLogNotif($message); 
            return redirect()->route('system.setting.rooms.home', 'tab=2')->with('success', $message);
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }


    }
    public function editRate(Request $request){
            $id = decrypt($request->id);
            return view('system.setting.rooms.rate.edit',  ['activeSb' => 'Rooms', 'room_rate' =>  RoomRate::findOrFail($id)]);
    }
    public function updateRate(Request $request){
        $id = decrypt($request->id);        $room_rate = RoomRate::find($id);

        $validated = $request->validate([
            'name' => ['required'],
            'price' =>  ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/', 'min:0.01'],
            'occupancy' =>  ['required', 'numeric', 'min:1'],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);

        if (Hash::check($validated['passcode'], $this->system_user->user()->passcode)) {
            // Update Data Process
            $room_rate->update($validated) ;
            $message = $room_rate->name .' Rate Updated';
            $this->employeeLogNotif($message); 
            return back()->with('success', $room_rate->name . $message);
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }
    public function destroyRate(Request $request){
        $validated = $request->validate([
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        if (Hash::check($validated['passcode'], $this->system_user->user()->passcode)) {

            $room_rate = RoomRate::findOrFail(decrypt($request->id));
            $room_rate_name = $room_rate->name;
            $room_rate->delete();
            $message = $room_rate_name.' Rate Removed';
            $this->employeeLogNotif($message); 
            return redirect()->route('system.setting.rooms.home', 'tab=2')->with('success', $message);
        } 
        else{
            return back()->with('error', 'Passcode Invalid');
        }
    }
}
