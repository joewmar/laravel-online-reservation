<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;
use Mockery\CountValidator\AtMost;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:web'])->except('check');
    }
    public function create(Request $request){
        // Validate input
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'min:3'],
            'last_name' => ['required', 'min:3'],
            'birthday' => ['required'],
            'country' => ['required', 'min:3'],
            'nationality' => ['required'],
            'contact' => ['required', 'numeric', 'min:7'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->symbols()]
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
            session(['uinfo' => $validated]);

            // // Create User

            
            return redirect()->route('register.verify');

        }
    }
    public function verify(Request $request){
        if(! session()->has('uinfo')) return redirect()->route('register');
        $otp = mt_rand(1111,9999);
        $details = [
            'title' => "Let's Verify your Email",
            'body' => 'Verification Code: ' . $otp,
        ];
        Mail::to(env('SAMPLE_EMAIL', session('uinfo')['email']))->queue(new ReservationMail($details, 'reservation.mail', 'Email Verification'));
        $user_info = session('uinfo');
        $user_info['otp'] = $otp;
        session(['uinfo' => $user_info]);
        return view('users.register.verify', ['email' => session('uinfo')['email']]);
    }
    public function verifyStore(Request $request){
        $validated = $request->validate([
            'code' => ['required', 'digits:4', 'numeric'],
        ]);
        if(session()->exists(['uinfo']) && $validated['code'] == session('uinfo')['otp']){
            unset(session('uinfo')['otp']);
            $user = User::create(session('uinfo'));
            $user = Auth::guard('web')->login($user);

            if($user){
                session()->forget('uinfo');
                return redirect()->intended(route('home'))->with('success', 'Welcome First timer' . auth()->user()->name());
            }
        }
        return redirect()->intended(route('home'));

    }
    // Verify login
    public function check(Request $request){
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required',
        ]);

        // Attempt to log the user in (If user credentials are correct)
        if(Auth::guard('web')->attempt($validated, $request['remember'] ?? 0)){
            $request->session()->regenerate(); //Regenerate Session ID
            return redirect()->intended(route('home'))->with('success', 'Welcome back ' . auth('web')->user()->first_name . ' ' . auth()->user()->last_name);
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
        return redirect()->route('home')->with('success', 'Thank you for services');
    }
    public function fillupGoogle(){
        if(session()->has('ginfo')) return view('users.google.fillup');
        else redirect()->route('google.redirect');
    }
    public function fillupGoogleUpdate(Request $request){
        if(session()->has('ginfo')){
            // dd($request->all());
            $validated = $request->validate([
                'birthday' => ['required', ' date'],
                'country' => ['required'],
                'nationality' => ['required'],
                'contact' => ['required', 'numeric', 'min:7'],
            ], [
                'contact.min' => 'Contact number must be valid',
                'required' => 'Need to fill up your :attribute',
            ]);
            $user = session('ginfo'); 
            $validated['google_id'] = $user['google_id'];
            $validated['avatar'] = $user['avatar'];
            $validated['first_name'] = $user['first_name'];
            $validated['last_name'] = $user['last_name'];
            $validated['email'] = $user['email'];
            $validated['password'] = Str::password();
            $newUser = User::create($validated);
            if($newUser){
                session()->forget('ginfo');
                Auth::guard('web')->login($newUser);
                return redirect()->intended(route('home'))->with('success', 'Welcome back ' . auth('web')->user()->name());
            }
            else{
                return back()->with('error', 'Something Wrong')->withInput( $validated);
            }
           
        }
        else redirect()->route('google.redirect');


    }
}
