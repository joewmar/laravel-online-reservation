<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\System;
use Illuminate\Support\Facades\Auth;

class SystemController extends Controller
{
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
}
