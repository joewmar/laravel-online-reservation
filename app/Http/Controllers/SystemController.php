<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Jobs\TelegramJob;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Telegram\Bot\Laravel\Facades\Telegram;

class SystemController extends Controller
{
    private $system_user;

    public function __construct()
    {
        $this->middleware(function (){
            $this->system_user = auth()->guard('system');
            if(!$this->system_user->user()->type === 0) abort(404);
        })->except('check');
    }
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
        $employee = System::findOrFail(decrypt($id));
        return view ('system.setting.accounts.show',  ['activeSb' => 'Accounts', 'employee' => $employee]);

    }
    public function edit($id){
        $employee = System::findOrFail(decrypt($id));
        return view ('system.setting.accounts.edit',  ['activeSb' => 'Accounts', 'employee' => $employee]);

    }
    public function search(Request $request){
        if($request['type'] == null){
            $request['type'] = "all";
        }
        return redirect()->route('system.setting.accounts', Arr::query(['search' => $request['search'], 'type' => $request['type']]));
    }
    public function store(Request $request){
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
    
        $systemUser = System::create($validated);
        telegramSendMessage($systemUser->telegram_chatID, "Hello there, " . $systemUser->first_name . " Your username was verified", null, 'bot2');
        return redirect()->route('system.setting.accounts')->with('success', $systemUser->name() . ' was Created');

    }
    public function update(Request $request, $id){
        $systemUser = System::findOrFail(decrypt($id));
        $validated = $request->validate([
                'type' => ['required'],
                'avatar' =>  ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'first_name' => ['required'],
                'last_name' => ['required'],
                'contact' => ['required', 'numeric', 'min:7'],
                'email' => ['required', 'email'],
                'username' => ['required'],
                'password' => ['nullable', 'min:6'],
                'passcode' => ['nullable', 'numeric', 'digits:4'],
                'telegram_username' => ['nullable'],
        ], [
            'required' => 'Required to fill up this form'
        ]);
        if($validated['password'] == null) $validated['password'] = $systemUser->password;
        else $validated['password'] = bcrypt($validated['password']);

        if($validated['passcode'] == null) $validated['passcode'] = $systemUser->passcode;
        else $validated['passcode'] = bcrypt($validated['passcode']);

        if($validated['telegram_username'] == null) $validated['telegram_username'] = $systemUser->telegram_username;
        else{
            if($validated['telegram_username'] != null){
                $chat_id = getChatIdByUsername($validated['telegram_username']) ;
                if($chat_id == null) return back()->withErrors(['telegram_username' => 'Invalid Username or did not do it when typing in chat bots'])->withInput($validated);
                else $validated['telegram_chatID'] = $chat_id;
            }
        }

        if($request->hasFile('avatar')){                          // storage/app/logos
            if($systemUser->avatar) 
                deleteFile($systemUser->avatar);
            $validated['avatar'] = $request->file('avatar')->store('employee', 'public');
        }
    
            $updated = $systemUser->update($validated);
            if($updated){
                return redirect()->route('system.setting.accounts')->with('success', $systemUser->name() . ' was Updated');
            }
            else
                return back()->with('error', $systemUser->first_name . ' ' . $systemUser->last_name . ' was Something Error, Try Again');


    }
    public function destroy(Request $request, $id) {
        if(!auth('system')->user()->type === 0) abort(401, 'Unauthorized User');
        $validated = $request->validate([
            'passcode' => ['required', 'digits:4'],
        ]);
        if(Hash::check($validated['passcode'], $this->system_user->user()->passcode)){
            $systemUser = System::findOrFail(decrypt($id));
            $systemUser->delete();
            return redirect()->route('system.setting.accounts')->with('success', $systemUser->name() . ' was Deleted');
        }
        else{
            return back()->with('error', 'Invalid Passcode');
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
        if(Auth::guard('system')->attempt($validated)){
            $request->session()->regenerate(); //Regenerate Session ID
            return redirect()->intended(route('system.home'));
        }
        else{
            return back()->withErrors(['username' => 'Invalid Credentials'])->onlyInput('username');
        }
    }
    
    public function logout(Request $request){
        Auth::logout();
        Auth::guard('system')->logout();
        // Recommend to invalidate the users session and regenerate the toke from @crfs
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('system.login');
    }
}
