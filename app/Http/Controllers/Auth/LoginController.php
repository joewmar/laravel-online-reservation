<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    // Google Login
    public function redirectToGoogle(){
        session()->forget('ginfo');
        return Socialite::driver('google')->redirect();

    }
    public function handleGoogleCallback(){
        try {
            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->orWhere('email', $user->email)->first();
            if($finduser){
                Auth::login($finduser);
                return redirect()->intended(route('home'))->with('success', 'Welcome back ' . auth('web')->user()->name());
            }
            else{
                $users = [
                    'google_id' => $user->id,
                    'avatar' => $user->avatar,
                    'first_name' => $user['given_name'],
                    'last_name' => $user['family_name'],
                    'email'=> $user->email,
                ];
                session(['ginfo' => $users]);
                return redirect()->route('google.fillup');
            }
        } 
        catch (Exception $e) {
            return redirect()->route('google.redirect');
        }
    }

    // Facebook Login
    public function redirectToFacebook(){
        return Socialite::driver('facebook')->redirect();
    }
    public function handleFacebookCallback(){
        try {
            $user = Socialite::driver('facebook')->user();
            $finduser = User::where('facebook_id', $user->id)->orWhere('email', $user->email)->first();
            if($finduser){
                Auth::login($finduser);
                return redirect()->intended(route('home'))->with('success', 'Welcome back ' . auth('web')->user()->name());
            }
            else{
                $users = [
                    'facebook_id' => $user->id,
                    'avatar' => $user->avatar,
                    'first_name' => explode(' ', $user->name)[0],
                    'last_name' => explode(' ', $user->name)[1],
                    'email'=> $user->email,
                ];
                session(['fbubser' => $users]);
                return redirect()->route('facebook.fillup');
            }
        } 
        catch (Exception $e) {
            return redirect()->route('facebook.redirect');
        }
    }
}
