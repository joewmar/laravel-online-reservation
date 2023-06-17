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
    public function index(){
        $systemType = auth()->guard('system')->user()->type;
        if($systemType == '0'){
            return view('system.setting.rides.index', ['activeSb' => 'Ride', 'rides' =>  Ride::all()]);
        }
        else{
            abort(404);
        }
       
    }
    public function edit(Request $request){
        $systemUser = auth()->guard('system')->user();
        if($systemUser->type == '0'){
            $id = decrypt($request->id);
            return view('system.setting.rides.edit',  ['activeSb' => 'Ride', 'ride' =>  Ride::findOrFail($id)]);
        }
        else{
            abort(404);
        }
       
    }
    // Add New Room
    public function store(Request $request){
        $systemUser = auth()->guard('system')->user();
        if($systemUser->type == '0'){
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
            if (Hash::check($validated['passcode'], $systemUser->passcode)) {
                // Save to database and get value
                Ride::create($validated);  

                return redirect()->route('system.setting.rides.home')->with('success', 'Ride Created');
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
        $systemUser = auth()->guard('system')->user();
        if($systemUser->type == '0'){
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

            if (Hash::check($validated['passcode'], $systemUser->passcode)) {
                // Save to database and get value
                $ride->update($validated);  

                return redirect()->route('system.setting.rides.home')->with('success', $ride->model . ' was Created');
            }
            else{
                return back()->with('error', 'Passcode Invalid');
            }
        }
        else{
            abort(404);
        }
    }

    public function destroy(Request $request){
        $systemUser = auth()->guard('system')->user();
        if($systemUser->type == '0'){
            $id = decrypt($request->id);
            $ride = Ride::find($id);
            $validated = $request->validate([
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);
            if (Hash::check($validated['passcode'], $systemUser->passcode)) {
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
        else{
            abort(404);
        }
    }
}
