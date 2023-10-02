<?php

namespace App\Http\Controllers;

use App\Models\Addons;
use App\Models\TourMenu;
use Illuminate\Support\Arr;
use App\Models\TourMenuList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Laravel\Ui\Presets\React;

class TourMenuController extends Controller
{

    public function index(Request $request){
        if($request->has('tab') && $request['tab'] === 'addons'){
            return view('system.service.index', ['activeSb' => 'Tour Menu', 'addons_list' => Addons::all()]);
        }
        return view('system.service.index', ['activeSb' => 'Tour Menu', 'tour_lists' => TourMenuList::all()]);
    }
    public function create(Request $request){
        $category = TourMenuList::distinct()->get('category')->pluck('category');
        if($request->has('tl')){
            return view('system.service.create', ['activeSb' => 'Tour Menu', 'service_menus' => TourMenuList::all(), 'tl' =>  TourMenuList::findOrFail(decrypt($request['tl'])), 'category' => $category]);
        }
        return view('system.service.create', ['activeSb' => 'Tour Menu', 'service_menus' => TourMenuList::all(), 'category' => $category]);
    }
    public function createAddons(){
        return view('system.service.addons.create', ['activeSb' => 'Tour Menu']);
    }
    public function storeAddons(Request $request){
        $validated = $request->validate([
            'title' => ['required'],
            'price' => ['required', 'numeric', 'min:1'],
            'passcode' => ['required', 'digits:4'],
        ]);
        if(!Hash::check($validated['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($request->except('passcode'));
        $addons = Addons::create($validated);
        if($addons) return redirect()->route('system.menu.home', Arr::query(['tab' => 'addons']))->with('success', $addons->title . ' was Added');
    }
    public function editAddons($id){
        
        return view('system.service.addons.edit', ['activeSb' => 'Tour Menu', 'addon' => Addons::findOrFail(decrypt($id))]);
    }
    public function updateAddons(Request $request, $id){
        $addon = Addons::findOrFail(decrypt($id));
        $validated = $request->validate([
            'title' => ['required'],
            'price' => ['required', 'numeric', 'min:1'],
            'passcode' => ['required', 'digits:4'],
        ]);
        if(!Hash::check($validated['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($request->except('passcode'));
        $addon->update($validated);
        return redirect()->route('system.menu.home', Arr::query(['tab' => 'addons']))->with('success', $addon->title . ' was Updated');
    }
    public function destroyAddons(Request $request, $id){
        $addon = Addons::findOrFail(decrypt($id));
        $addon->delete();
        return redirect()->route('system.menu.home', Arr::query(['tab' => 'addons']))->with('success', $addon->title . ' was Removed');
    }

    // Replace the Input 
    public function replace(Request $request){
        if($request->has('rpid')) {
            return redirect()->route('system.menu.create', Arr::query(['tl' => $request['rpid'], 'rpl' => encrypt(true)]))->with('success', TourMenuList::findOrFail(decrypt($request['rpid']))->title . ' was replaced');
        }
        else{
            $validated = $request->validate([
                'replace' => ['required'],
            ]);
            return redirect()->route('system.menu.create', Arr::query(['tl' => encrypt($validated['replace']), 'rpl' => encrypt(true)]));
        }
    }
    public function store(Request $request){
        $system_user = auth('system')->user();
        if($system_user->type === 0){
            if($request->has(['tlid'])){
                $validated = $request->validate([
                    'menu_id' => ['required', Rule::exists('tour_menu_lists', 'id')],
                    'type' =>  ['required'],
                    'pax' => ['required'],
                    'price' =>  ['required', 'numeric', 'decimal:0,2'],
                    'passcode' =>  ['required', 'numeric', 'digits:4'],

                ]);
                if($validated){
                    if(Hash::check($validated['passcode'], $system_user->passcode)){
                        $tour_menu = TourMenu::create($validated);
                        return redirect()->route('system.menu.price.details', Arr::query(['rpid' => encrypt($tour_menu->menu_id)]))->with('success', TourMenuList::findOrFail($validated['menu_id'])->title . ' ' . $tour_menu->type . ' price'. ' was Added');
                    }
                    else{
                        return back()->with('error', 'Passcode Invalid')->withInput();
                    }
                }
            }
            else{
                $validated = $request->validate([
                    'title' => ['required', Rule::unique('tour_menu_lists', 'title')],
                    'category' =>  ['required'],
                    'inclusion' => ['nullable'],
                    'no_day' =>  ['required', 'numeric', 'min:1', Rule::when(($request['tour_type'] === 'Day Tour'), ['min:1', 'max:1']), Rule::when(($request['tour_type'] === 'Overnight'), ['min:2'])],
                    'pax' => ['required', 'numeric'],
                    'type' => ['required'],
                    'price' =>  ['required', 'numeric', 'decimal:0,2'],
                    'passcode' =>  ['required', 'numeric', 'digits:4'],
                ]);
                if($validated){
                    if(Hash::check($validated['passcode'], $system_user->passcode)){
                        $tour_list = TourMenuList::create([
                            'title' => $validated['title'],
                            'category' => $validated['category'],
                            'inclusion' => implode("(..)",$validated['inclusion']) ?? null,
                            'no_day' =>  $validated['no_day'],


                        ]);
                        TourMenu::create([
                            'menu_id' => $tour_list->id,
                            'type' => $validated['type'],
                            'pax' => $validated['pax'],
                            'price' =>$validated['price'],
                        ]);
                        return redirect()->route('system.menu.price.details', Arr::query(['rpid' => encrypt($tour_list->id)]))->with('success', $tour_list->title . ' was Added');
                    }
                    else{
                        return back()->with('error', 'Passcode Invalid')->withInput();
                    }
                }
            }
        }
        else{
            abort(404);
        }

    }
    public function priceDetails(Request $request){
        if($request->has('rpid')) return view('system.service.show-pricedetails', ['activeSb' => 'Tour Menu']);
        return redirect()->route('system.menu.home');
    }

    public function show (Request $request){
        if(auth('system')->user()->type !== 0) abort(404);
        $id = decrypt($request->id);
        return view('system.service.show', ['activeSb' => 'Tour Menu', 'tour_list' => TourMenuList::findOrFail($id), 'tour_menu' => TourMenu::where('menu_id', '=' ,$id)]);

    }

    public function edit (Request $request){
        if(auth('system')->user()->type !== 0) abort(404);
        $id = decrypt($request->id);
        $category = TourMenuList::distinct()->get('category')->pluck('category');
        return view('system.service.edit', ['activeSb' => 'Tour Menu', 'service_menu' => TourMenuList::findOrFail($id), 'category' => $category]);

    }
    public function update (Request $request){
        $system_user = auth('system')->user();
        if($system_user->type === 0){
            $id = decrypt($request->id);
            $validated = $request->validate([
                'title' => ['required'],
                'category' =>  ['required'],
                'inclusion' => ['nullable'],
                'no_day' =>  ['required', 'numeric', 'min:1', Rule::when(($request['tour_type'] === 'Day Tour'), ['min:1', 'max:1']), Rule::when(($request['tour_type'] === 'Overnight'), ['min:2'])],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if (Hash::check($validated['passcode'], $system_user->passcode)) {
                $validated['inclusion'] = implode("(..)",$validated['inclusion']) ?? null;

                $tour = TourMenuList::findOrFail($id);
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
    public function editPrice(Request $request){
        $priceid = decrypt($request->priceid);
        $id = decrypt($request->id);
        return view('system.service.show-price', ['activeSb' => 'Tour Menu', 'tour_menu' => TourMenu::findOrFail($priceid)]);
    }
    public function updatePrice(Request $request){
        $system_user = auth()->guard('system')->user();
        if($system_user->type === 0){
            $priceid = decrypt($request->priceid);
            $list_id = decrypt($request->id);
            $validated = $request->validate([
                'type' => ['required'],
                'pax' =>  ['required', 'numeric'],
                'price' =>  ['required', 'numeric', 'decimal:0,2'],
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if (Hash::check($validated['passcode'], $system_user->passcode)) {

                $tour_menu = TourMenu::findOrFail($priceid);
                $tour_menu->update($validated);
                return redirect()->route('system.menu.show', encrypt($list_id))->with('success', $tour_menu->type .' was Updated');
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
        if($system_user->type === 0){
            $id = decrypt($request->id);
            $validated = $request->validate([
                'passcode' =>  ['required', 'numeric', 'digits:4'],
            ]);

            if (Hash::check($validated['passcode'], $system_user->passcode)) {

                $tour_list = TourMenuList::findOrFail($id);
                $tour_menu = TourMenu::where('menu_id', '=', $tour_list->id);
                $deleted_list = $tour_list->delete();
                if($deleted_list) $tour_menu->delete();

                return redirect()->route('system.menu.home')->with('success', $tour_list->title .' was Deleted');
            } 
            else{
                return back()->with('error', 'Passcode Invalid');
            }
        }
        else{
            abort(404);
        }

       

    }
    public function destroyPrice (Request $request){
        $this->authorize('admin');
        $priceid = decrypt($request->priceid);
        $list_id = decrypt($request->id);
        $tour_menu = TourMenu::findOrFail($priceid);
        if($tour_menu) {
            if($tour_menu->delete()) return redirect()->route('system.menu.show', encrypt($list_id))->with('success', $tour_menu->tourMenu->title . ' - ' . $tour_menu->type .' was removed');
            else return back();
        }
        else{
            return back();
        }
        
    }
}
