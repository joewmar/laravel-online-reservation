<?php

namespace App\Http\Controllers;

use App\Models\System;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;

class SystemController extends Controller
{
    private $system_user;
    public function __construct()
    {
        $this->system_user = auth('system');
    }
    private function systemNotification($text, $link = null){
        if($this->system_user->user()->type != 0){
            $systems = System::whereBetween('type', [0, 1])->get();
            $keyboard = null;
            if(isset($link)){
                $keyboard = [
                    [
                        ['text' => 'View', 'url' => $link],
                    ],
                ];
            }
            foreach($systems as $system){
                if(isset($system->telegram_chatID)) dispatch(new SendTelegramMessage(env('SAMPLE_TELEGRAM_CHAT_ID', $system->telegram_chatID), $text, $keyboard, 'bot2'));
            }
            Notification::send($systems, new SystemNotification(Str::limit($text, 10), $text, route('system.notifications')));
        }
    }
    public function index(Request $request){
        $employees  = System::paginate(5);
        if($request->has('type') && $request['type'] != 'all'){
            $employees  = System::where('type', '=', $request['type'])
            ->paginate(5);
        }
        if(isset($request['search'])){
            $names = explode(' ', $request['search']);

            $firstName = $names[0];
            $lastName = isset($names[1]) ? $names[1] : '';
            $employees  = System::where('id', 'like', '%' .$firstName . '%')
            ->where('last_name', 'like', '%' . $lastName . '%')
            ->orWhere('last_name', 'like', '%' . $firstName . '%')
            ->paginate(5);
        }
        return view ('system.setting.accounts.index',  ['activeSb' => 'Accounts', 'employees' => $employees]);
    }
    public function create(){
        return view ('system.setting.accounts.create',  ['activeSb' => 'Accounts']);
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
        return redirect()->route('system.setting.accounts.home', Arr::query(['search' => $request['search'], 'type' => $request['type']]));
    }
    public function store(Request $request){
        $validated = $request->validate([
                'type' => ['required'],
                'avatar' =>  ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5024'],
                'first_name' => ['required'],
                'last_name' => ['required'],
                'contact' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
                'email' => ['required', 'email', Rule::unique('systems', 'email')],
                'username' => ['required', Rule::unique('systems', 'username')],
                'password' => ['required', 'min:6'],
                'passcode' => ['required', 'numeric', 'digits:4'],
                'telegram_username' => ['nullable', Rule::unique('systems', 'telegram_username')],
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
            $validated['avatar'] = saveImageWithJPG($request, 'avatar', 'employee', 'private');
        }
    
        $systemUser = System::create($validated);
        telegramSendMessage($systemUser->telegram_chatID, "Hello there, " . $systemUser->first_name . " Your username was verified", null, 'bot2');
        return redirect()->route('system.setting.accounts.accounts')->with('success', $systemUser->name() . ' was Created');

    }
    public function update(Request $request, $id){
        $systemUser = System::findOrFail(decrypt($id));
        $validated = $request->validate([
            'type' => ['required'],
            // 'avatar' =>  ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5024'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'contact' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'email' => ['required', 'email', Rule::when($request['email'] !== $systemUser->email, [Rule::unique('systems', 'email')])],
            'username' => ['required', Rule::when($request['username'] !== $systemUser->username, [Rule::unique('systems', 'username')])],
            'password' => ['nullable', 'min:6'],
            'passcode' => ['nullable', 'numeric', 'digits:4'],
            'telegram_username' => ['nullable', Rule::when($request['telegram_username'] !== $systemUser->telegram_username, [Rule::unique('systems', 'telegram_username')])],
        ], [
            'required' => 'Required to fill up this form'
        ]);
        if($validated['password'] == null) $validated['password'] = $systemUser->password;
        else $validated['password'] = bcrypt($validated['password']);

        if($validated['passcode'] == null) $validated['passcode'] = $systemUser->passcode;
        else $validated['passcode'] = bcrypt($validated['passcode']);

        if($validated['telegram_username'] != null && $validated['telegram_username'] !== $systemUser->telegram_username){
            $chat_id = getChatIdByUsername($validated['telegram_username'], 'bot1') ?? getChatIdByUsername($validated['telegram_username'], 'bot2') ;
            if($chat_id == null) return back()->withErrors(['telegram_username' => 'Invalid Username or did not do it when typing in chat bots'])->withInput($validated);
            else $validated['telegram_chatID'] = $chat_id;
        }

        if($request->hasFile('avatar')){                          // storage/app/logos
            if($systemUser->avatar) 
                deleteFile($systemUser->avatar);
            $validated['avatar'] = saveImageWithJPG($request, 'avatar', 'employee', 'private');
        }
        $updated = $systemUser->update($validated);
        if($updated){
            return redirect()->route('system.setting.accounts.home')->with('success', $systemUser->name() . ' was Updated');
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
            return redirect()->route('system.setting.accounts.home')->with('success', $systemUser->name() . ' was Deleted');
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
            $this->systemNotification(Auth::guard('system')->user()->name() . " was logged in");
            return redirect()->intended(route('system.home'));
        }
        else{
            return back()->withErrors(['username' => 'Invalid Credentials'])->onlyInput('username');
        }
    }
    public function notifications(){

        return view('system.notification', ['activeSb' => 'Notification', 'notifs' => auth('system')->user()->unreadNotifications]);
    }
    public function markAsRead(){
        Auth::guard('system')->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }
    public function logout(Request $request){
        $this->systemNotification(Auth::guard('system')->user()->name() . " was logged out");
        Auth::guard('system')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('system.login');
    }
}
