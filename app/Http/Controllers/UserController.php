<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function create(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'min:3'],
            'last_name' => ['required', 'min:3'],
            'birthday' => ['required'],
            'country' => ['required', 'min:3'],
            'contact' => ['required', 'min:3'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => 'required|confirmed|min:6'
        ], [
            'required' => 'Need to fill up your :attribute',
        ]);
        if($validator->fails()){
            return redirect()->route('register')->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();
        
        // Hash password
        $validated['password'] = bcrypt($validated['password']);

        // Create User
        $user = User::create($validated);

        // $user = Auth::guard('web')->login($user);
        // if($user){
        //     return redirect()->route('home')->with('success', 'Welcome First timer' . auth()->user()->first_name);
        // }
        // else{
        //     return redirect()->route('login')->with('success', 'Login Hello');
        // }
    }
    // Verify login
    public function check(Request $request){
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required',
        ]);

        // Attempt to log the user in (If user credentials are correct)
        if(auth('web')->attempt($validated)){
            $request->session()->regenerate(); //Regenerate Session ID

            $getParamDates = array(
                "cin" =>  ($request->cin != '' ? decrypt($request->cin) : null),
                "cout" =>  ($request->cout != '' ? decrypt($request->cout) : null),
            );
            if (checkAllArrayValue($getParamDates) === false) {
                return redirect()->route('reservation.choose');
            }
            else{
                return redirect()->route('home')->with('success', 'Welcome back ' . auth('web')->user()->first_name . ' ' . auth()->user()->last_name);
            }
            
        
        }
        else{
            return back()->withErrors(['email' => 'Invalid Credentials'])->onlyInput('email');
        }
        
    }
    // Logout
    public function logout(Request $request){
        Auth::guard('web')->logout();

        // Recommend to invalidate the users session and regenerate the toke from @crfs
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

}
