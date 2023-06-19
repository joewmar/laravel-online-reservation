<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TourSettingController extends Controller
{
    public function index(){
        return view('system.setting.tour.index', ['activeSb' => 'HEllo', 'tours' => Tour::all()]);
    }
    public function store(Request $request){
        $system_user = System::find(auth()->guard('system')->id());

        // Check if user is admin
        if($system_user->type == '0'){
            $validated = $request->validate([
                'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'name' => ['required'],
                'description' => ['max:65535'],
                'location'=> ['required'],
                'passcode'=> ['required', 'numeric', 'digits:4'],
            ]);
            if (Hash::check($validated['passcode'], $system_user->passcode)) {
                if($request->hasFile('image')){                          // storage/app/logos
                    $validated['image'] = $request->file('image')->store('tours', 'public');
                }
                Tour::create($validated);
                return redirect()->route('system.setting.tour.home')->with('success', 'Tour Destination was Created');
            }
            else{
                return back()->with('error', 'Passcode Invalid');
            }
        }
        else{
            abort(404);
        }
    }
    public function edit(Request $request){
        $system_user = System::find(auth()->guard('system')->id());
        // Check if user is admin
        if($system_user->type == '0'){
            $id = decrypt($request->id);
            return view('system.setting.tour.edit', ['activeSb' => 'asdasd', 'tour' => Tour::findOrFail($id)]);
        }
        else{
            abort(404);
        }  
    }

    public function update(Request $request){
        $system_user = System::find(auth()->guard('system')->id());

        // Check if user is admin
        if($system_user->type == '0'){
            $validated = $request->validate([
                'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'name' => ['required'],
                'description' => ['max:65535'],
                'location'=> ['required'],
                'passcode'=> ['required', 'numeric', 'digits:4'],
            ]);
            if (Hash::check($validated['passcode'], $system_user->passcode)) {
                $id = decrypt($request->id);
                $tour = Tour::findOrfail($id);
                // Update Image
                if($request->hasFile('image')){  
                    if($tour->image) deleteFile($tour->image);
                    $validated['image'] = $request->file('image')->store('tours', 'public');
                }
            
                $tour->update($validated);
                return redirect()->route('system.setting.tour.home')->with('success', $tour->name . ' was Update');
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
        $system_user = System::find(auth()->guard('system')->id());

        // Check if user is admin
        if($system_user->type == '0'){
            $validated = $request->validate([
                'passcode'=> ['required', 'numeric', 'digits:4'],
            ]);
            if (Hash::check($validated['passcode'], $system_user->passcode)) {
                $id = decrypt($request->id);
                $tour = Tour::findOrfail($id);
                if($tour->image) deleteFile($tour->image); // Delete Image
                $tour_name = $tour->name;
                $tour->delete($validated); // Delete data
                return redirect()->route('system.setting.tour.home')->with('success', $tour_name . ' was deleted');
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
