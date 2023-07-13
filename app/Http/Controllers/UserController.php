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
    public function create(Request $request){
        // Validate input
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'min:3'],
            'last_name' => ['required', 'min:3'],
            'birthday' => ['required'],
            'country' => ['required', 'min:3'],
            'contact' => ['required', 'numeric', 'min:7'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => 'required|confirmed|min:6'
        ], [
            'contact.min' => 'Contact number must be valid',
            'required' => 'Need to fill up your :attribute',
        ]);
        if($validator->fails()){
            return redirect()->route('register')->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();
        
        if($validated){
            // Hash password
            $validated['password'] = bcrypt($validated['password']);

            // Create User
            $user = User::create($validated);

            $user = Auth::guard('web')->login($user);
            if($user){
                return redirect()->route('home')->with('success', 'Welcome First timer' . auth()->user()->first_name);
            }
        }
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

            if (request()->exists('cin', 'cout', 'at', 'px', 'ck')) {
                $paramDates = array(
                    "cin" => $request->get('cin'),
                    "cout" => $request->get('cout'),
                    "px" => $request->get('px'),
                    "at" => $request->get('at'),
                    "ck" => $request->get('ck'),
                );
                return redirect()->route('reservation.choose', Arr::query($paramDates));
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
    public function fillupGoogle(Request $request){
        return view('users.google.fillup', ['user_info' => User::where('google_id', $request->id)->firstOrFail()]);
    }
    public function fillupGoogleUpdate(Request $request){
        $finduser = User::where('google_id', $request->id)->first();
        if($finduser){
            $validated = $request->validate([
                'birthday' => ['required'],
                'country' => ['required'],
                'nationality' => ['required'],
                'contact' => ['required', 'numeric', 'min:7'],
            ], [
                'contact.min' => 'Contact number must be valid',
                'required' => 'Need to fill up your :attribute',
            ]);        
            $finduser->update($validated);
            Auth::guard('web')->login($finduser);
            return redirect()->route('home')->with('success', 'Welcome back ' . auth('web')->user()->first_name . ' ' . auth()->user()->last_name);
        }
        else{
            return redirect()->route('google.redirect');
        }

    }
}
