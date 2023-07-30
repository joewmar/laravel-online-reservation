<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RideController extends Controller
{
    private $system_user;

    public function __construct()
    {
        $this->middleware(['auth:system']);
        $this->system_user = auth()->guard('system')->user();
        if(!$this->system_user->type === 0) abort(404);
    }
    public function index(){
        return view('system.setting.rides.index', ['activeSb' => 'Ride', 'rides' =>  Ride::all()]);
    
    }
    public function edit(Request $request){
            $id = decrypt($request->id);
            return view('system.setting.rides.edit',  ['activeSb' => 'Ride', 'ride' =>  Ride::findOrFail($id)]); 
    }
    // Add New Room
    public function store(Request $request){
            $validated = $request->validate([
                'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'model' => ['required', Rule::unique('rides', 'model')],
                'max_passenger' =>  ['required', 'numeric', 'min:1'],
                'many' =>  ['required', 'numeric', 'min:1'],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if($request->hasFile('image')){                          // storage/app/logos
                $validated['image'] = $request->file('image')->store('rides', 'public');
            }
            if (Hash::check($validated['passcode'], $this->system_user->passcode)) {
                // Save to database and get value
                Ride::create($validated);  

                return redirect()->route('system.setting.rides.home')->with('success', 'Ride Created');
            }
            else{
                return back()->with('error', 'Passcode Invalid');
            }

    
    }
    // Process update room
    public function update(Request $request){
            $id = decrypt($request->id);
            $ride = Ride::find($id);
            $validated = $request->validate([
                'model' => ['required'],
                'max_passenger' =>  ['required', 'numeric', 'min:1'],
                'many' =>  ['required', 'numeric', 'min:1'],
                'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if($request->hasFile('image')){  
                if($ride->image) 
                    deleteFile($ride->image);
            
                $validated['image'] = $request->file('image')->store('rides', 'public');
            }

            if (Hash::check($validated['passcode'], $this->system_user->passcode)) {
                // Save to database and get value
                $ride->update($validated);  

                return redirect()->route('system.setting.rides.home')->with('success', $ride->model . ' was Created');
            }
            else{
                return back()->with('error', 'Passcode Invalid');
            }

    }

    public function destroy(Request $request){
            $id = decrypt($request->id);
            $ride = Ride::find($id);
            $validated = $request->validate([
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);
            if (Hash::check($validated['passcode'], $this->system_user->passcode)) {
                if($ride->image) 
                    deleteFile($ride->image);

                $model = $ride->model;
                // Delete Ride to database
                $ride->delete();  

                return redirect()->route('system.setting.rides.home')->with('success', $model . ' was Deleted');
            }
            else{
                return back()->with('error', 'Passcode Invalid');
            }

    }
}
