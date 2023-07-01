<?php

namespace App\Http\Controllers;

use App\Models\TourMenu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class TourMenuController extends Controller
{
    //
    public function index(Request $request){
        return view('system.service.index', ['activeSb' => 'Tour Menu', 'service_menus' => TourMenu::all()]);
    }
    public function create(Request $request){
        return view('system.service.create', ['activeSb' => 'Tour Menu', 'service_menus' => TourMenu::all()->toArray()]);
    }
    public function store(Request $request){
        $system_user = auth()->guard('system')->user();
        if($system_user->type == '0'){
            $validated = $request->validate([
                'title' => ['required'],
                'category' =>  ['required'],
                'type' => ['nullable'],
                'inclusion' => ['nullable'],
                'price' =>  ['required', 'numeric', 'decimal:2'],
                'no_day' =>  ['required', 'numeric', 'min:1'],
                'hrs' =>  ['required', 'numeric', 'decimal:1'],
                'pax' =>  ['required', 'numeric', 'min:1'],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if (Hash::check($validated['passcode'], $system_user->passcode)) {

                $tour = TourMenu::create($validated);

                return redirect()->route('system.menu.home')->with('success', $tour->title .' was Created');
            } 
            else{
                return back()->with('error', 'Passcode Invalid');
            }
        }
        else{
            abort(404);
        }
        // dd($request->all());

    }

    public function show (Request $request){
        $id = decrypt($request->id);
        return view('system.service.show', ['activeSb' => 'Tour Menu', 'service_menu' => TourMenu::findOrFail($id)]);

    }

    public function edit (Request $request){
        $id = decrypt($request->id);
        return view('system.service.edit', ['activeSb' => 'Tour Menu', 'service_menu' => TourMenu::findOrFail($id)]);

    }
    public function update (Request $request){
        $system_user = auth()->guard('system')->user();
        if($system_user->type == '0'){
            $id = decrypt($request->id);
            $validated = $request->validate([
                'title' => ['required'],
                'category' =>  ['required'],
                'type' => ['nullable'],
                'inclusion' => ['nullable'],
                'price' =>  ['required', 'numeric', 'decimal:2'],
                'no_day' =>  ['required', 'numeric', 'min:1'],
                'hrs' =>  ['required', 'numeric', 'decimal:1'],
                'pax' =>  ['required', 'numeric', 'min:1'],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if (Hash::check($validated['passcode'], $system_user->passcode)) {

                $tour = TourMenu::findOrFail($id);
                $tour->update($validated);

                return redirect()->route('system.menu.home')->with('success', $tour->title .' was Updated');
            } 
            else{
                return back()->with('error', 'Passcode Invalid');
            }
        }
        else{
            abort(404);
        }

       

    }
    public function destroy (Request $request){
        $system_user = auth()->guard('system')->user();
        if($system_user->type == '0'){
            $id = decrypt($request->id);
            $validated = $request->validate([
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if (Hash::check($validated['passcode'], $system_user->passcode)) {

                $tour = TourMenu::findOrFail($id);
                $tour->delete($validated);

                return redirect()->route('system.menu.home')->with('success', $tour->title .' was Delete');
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
