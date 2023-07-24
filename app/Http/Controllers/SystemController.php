<?php

namespace App\Http\Controllers;

use App\Models\System;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Telegram\Bot\Laravel\Facades\Telegram;

class SystemController extends Controller
{
    public function index(Request $request){
        if($request['type'] != 'all'){
            $employees  = System::where('id', 'like', '%' . $request['search'] . '%')
            ->orWhere('first_name', 'like', '%' . $request['search'] . '%')
            ->orWhere('last_name', 'like', '%' . $request['search'] . '%')
            ->orWhere('type', '=', $request['type'])
            ->paginate(5);
        }
        else{
            $employees  = System::where('id', 'like', '%' . $request['search'] . '%')
            ->orWhere('first_name', 'like', '%' . $request['search'] . '%')
            ->orWhere('last_name', 'like', '%' . $request['search'] . '%')
            ->paginate(5);  
        }
        
        return view ('system.setting.accounts.index',  ['activeSb' => 'Accounts', 'employees' => $employees]);
    }
    public function show($id){
        $id = decrypt($id);
        // return view ('system.setting.accounts.index',  ['activeSb' => 'Accounts', 'employees' => System::all()]);

    }
    public function search(Request $request){
        if($request['type'] == null){
            $request['type'] = "all";
        }
        return redirect()->route('system.setting.accounts', Arr::query(['search' => $request['search'], 'type' => $request['type']]));
    }
    public function store(Request $request){
        // dd($request->all());

        $validated = $request->validate([
                'type' => ['required'],
                'avatar' =>  ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'first_name' => ['required'],
                'last_name' => ['required'],
                'contact' => ['required', 'numeric', 'min:7'],
                'email' => ['required', 'email', Rule::unique('systems', 'email')],
                'username' => ['required', Rule::unique('systems', 'username')],
                'password' => ['required', 'min:6'],
                'passcode' => ['required', 'numeric', 'digits:4'],
                'telegram_username' => ['nullable'],
        ], [
            'required' => 'Required to fill up this form'
        ]);
        if($validated['telegram_username'] != null){
            $chat_id = getChatIdByUsername($validated['telegram_username']) ;
            if($chat_id == null){
                return back()->withErrors(['telegram_username' => 'Invalid Username or did not do it when typing in chat bots'])->withInput($validated);
            }
            else{
                $validated['telegram_chatID'] = $chat_id;
            }
        }
        $validated['password'] = bcrypt($validated['password']);
        $validated['passcode'] = bcrypt($validated['passcode']);

        if($request->hasFile('avatar')){                          // storage/app/logos
            $validated['avatar'] = $request->file('avatar')->store('employee', 'public');
        }
        if(auth('system')->user()->type === 0){
            $systemUser = System::create($validated);
            return redirect()->route('system.setting.accounts')->with('success', $systemUser->first_name . ' ' . $systemUser->last_name . ' was Created');
        }
        else{
            abort(401, 'Unauthorized User');
        }

    }
    // Check if Valid System Credentials
    public function check(Request $request){
        // Validate Inputs
        $validated = $request->validate([
            'username' => ['required'],
            'password' => ['required', 'min:5'],
        ]);

        // guard('your_guard_created') Attempt to log the user in (If user credentials are correct)
        if(auth('system')->attempt($validated)){
            $request->session()->regenerate(); //Regenerate Session ID
            return redirect()->intended(route('system.home'));
        }
        else{
            return back()->withErrors(['username' => 'Invalid Credentials'])->onlyInput('username');
        }
    }
    
    public function logout(Request $request){
        Auth::guard('system')->logout();
        // Recommend to invalidate the users session and regenerate the toke from @crfs
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('system.login');
    }
    public function create(Request $request){
        $validated = $request->validate([
            'username' => ['required'],
            'password' => ['required', 'min:5'],
        ]);
    }
}
