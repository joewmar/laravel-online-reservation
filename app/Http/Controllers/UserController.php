<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function create(Request $request)
    {
        // Validate input
        $formFields = $request->validate([
            'first_name' => ['required', 'min:3'],
            'last_name' => ['required', 'min:3'],
            'nationality' => ['required', 'min:3'],
            'country' => ['required', 'min:3'],
            'contact' => ['required', 'min:3'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => 'required|confirmed|min:6'
        ]);

        // Hash password
        $formFields['password'] = bcrypt($formFields['password']);

        // Create User
        $user = User::create($formFields);

        if($user){
            return redirect()->back()->with('success', 'User created and logged in');
        }
        else{
            return redirect()->back()->with('error', 'User created and logged in');
        }
    }
    // Verify login
    public function check(Request $request){
        $formFields = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required',
        ]);

        // Attempt to log the user in (If user credentials are correct)
        if(auth()->attempt($formFields)){
            $request->session()->regenerate(); //Regenerate Session ID
            // ->with('message', 'You are now logged in!')
            return redirect()->route('home');
        }
        return back()->withErrors(['email' => 'Invalid Credentials'])->onlyInput('email');
        
    }

    // Logout
    public function logout(){
        Auth::logout();
        return redirect('/');
    }

}
