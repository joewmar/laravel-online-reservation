<?php

namespace App\Http\Controllers;

use App\Models\TourMenu;
use Illuminate\Support\Arr;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class TourMenuController extends Controller
{
    //
    public function index(Request $request){
        return view('system.service.index', ['activeSb' => 'Tour Menu', 'tour_lists' => TourMenuList::all()]);
    }
    public function create(Request $request){
        return view('system.service.create', ['activeSb' => 'Tour Menu', 'service_menus' => TourMenuList::all()]);
    }
    // Replace the Input 
    public function replace(Request $request){
        $validated = $request->validate([
            'replace' => ['required'],
        ]);
        $tour_menu = TourMenuList::findOrFail($validated['replace']);
        return redirect()->route('system.menu.create')->withInput($tour_menu->toArray());
    }
    public function store(Request $request){
        $system_user = auth()->guard('system')->user();
        if($system_user->type == '0'){
            $rules = [
                'title' => ['required'],
                'category' =>  ['required'],
                'inclusion' => ['nullable'],
                'no_day' =>  ['required', 'numeric', 'min:1'],
                'hrs' =>  ['required', 'numeric', 'decimal:1'],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ];
            $rules = collect($rules);

            $parameters = $request->all();
            $pricePlans = collect([]);
            $pricePlanPrice = getAllArraySpecificKey($parameters, 'price-');
            $pricePlanType = getAllArraySpecificKey($parameters, 'type-');
            $pricePlanPax = getAllArraySpecificKey($parameters, 'pax-');

            if(!empty($pricePlans)){
                foreach($pricePlanPrice as $key => $item){
                    $pricePlans->put($key, $item);
                    $rules->put($key, ['required', 'numeric', 'decimal:2']);
                }
                foreach($pricePlanType as $key => $item){
                    $pricePlans->put($key, $item);
                    $rules->put($key, ['required']);
                }
                foreach($pricePlanPax as $key => $item){
                    $pricePlans->put($key, $item);
                    $rules->put($key, ['required', 'numeric', 'min:1']);
                }
                $pricePlans->toArray();
            }
            else{
                
            }
            $validated = $request->validate($rules->toArray());
            dd($request->all());

            // if (Hash::check($validated['passcode'], $system_user->passcode)) {

            //     $tour = TourMenu::create($validated);

            //     return redirect()->route('system.menu.home')->with('success', $tour->title .' was Created');
            // } 
            // else{
            //     return back()->with('error', 'Passcode Invalid');
            // }
        }
        else{
            abort(404);
        }

    }

    public function show (Request $request){
        $id = decrypt($request->id);
        return view('system.service.show', ['activeSb' => 'Tour Menu', 'tour_list' => TourMenuList::findOrFail($id)]);

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
