<?php

namespace App\Http\Controllers;

use App\Models\System;
use Illuminate\Http\Request;
use Laravel\Ui\Presets\React;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private $system_user;
    public function __construct()
    {
        $this->system_user = auth('system');
    }
    public function edit(){
        $system_user = System::findOrFail($this->system_user->user()->id);
        return view('system.profile.edit',  ['activeSb' => 'Edit', 'systemUser' => $system_user]);
    }
    public function update(Request $request, $id){
        $system_user = System::findOrFail(decrypt($id));
        $validated = $request->validate([
            'avatar' =>  ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5024'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'contact' => ['required', 'numeric', 'min:7'],
            'email' => ['required', 'email', Rule::when($request['email'] !== $system_user->email, [Rule::unique('systems', 'email')])],
            'username' => ['required', Rule::when($request['username'] !== $system_user->username, [Rule::unique('systems', 'username')])],
            'telegram_username' => ['nullable', Rule::when($request['telegram_username'] !== $system_user->telegram_username, [Rule::unique('systems', 'telegram_username')])],
        ], [
            'required' => 'Required to fill up this form'
        ]);
        if($validated['telegram_username'] != null && $validated['telegram_username'] !== $system_user->telegram_username){
            $chat_id = getChatIdByUsername($validated['telegram_username'], 'bot1') ?? getChatIdByUsername($validated['telegram_username'], 'bot2') ;
            if($chat_id == null) return back()->withErrors(['telegram_username' => 'Invalid Username or did not do it when typing in chat bots'])->withInput($validated);
            else $validated['telegram_chatID'] = $chat_id;
        }


        if($request->hasFile('avatar')){                        
            $validated['avatar'] =  saveImageWithJPG($request, 'avatar', 'employee', 'private');
        }

        $updated = $system_user->update($validated);
        if($system_user->telegram_chatID && $updated) telegramSendMessage($system_user->telegram_chatID, "Hello there, " . $system_user->name() . " Your username was updated", null, 'bot2');
        if($updated) return redirect()->route('system.profile.edit')->with('success', 'Your Profile was updated');
    }

    public function password(){
        $system_user = System::findOrFail($this->system_user->user()->id);
        return view('system.profile.password',  ['activeSb' => 'Password', 'systemUser' => $system_user]);
    }
    public function updatePassword(Request $request, $id){
        $system_user = System::findOrFail(decrypt($id));
        $validated = $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required','string','min:8','confirmed', 'different:current_password'],
        ], ['required' => 'Required Input']);
        if (Hash::check($validated['current_password'], $system_user->password)) {
            $system_user->update([
                'password' => Hash::make($validated['new_password']),
            ]);
            return redirect()->route('system.profile.password')
                             ->with('success', 'Password changed successfully.');
        } 
        else {
            return redirect()->route('system.profile.password')
                             ->with('error', 'Current password is incorrect.');
        }
    }
    public function updatePasscode(Request $request, $id){
        $system_user = System::findOrFail(decrypt($id));
        $validated = $request->validate([
            'current_passcode' => ['required'],
            'new_passcode' => ['required','numeric','digits:4', 'different:current_passcode'],
            'confirm_new_passcode' => ['required','numeric','digits:4', 'same:new_passcode'],
        ], ['required' => 'Required Input']);
        if (Hash::check($validated['current_passcode'], $system_user->passcode)) {
            $system_user->update([
                'passcode' => Hash::make($validated['confirm_new_passcode']),
            ]);
            return redirect()->route('system.profile.password')
                             ->with('success', 'Passcode changed successfully.');
        } 
        else {
            return redirect()->route('system.profile.password')
                             ->with('error', 'Current passcode is incorrect.');
        }
    }
}
