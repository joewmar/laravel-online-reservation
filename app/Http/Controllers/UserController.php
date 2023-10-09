<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Laravel\Ui\Presets\React;
use Illuminate\Validation\Rule;
use Mockery\CountValidator\AtMost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Notifications\DatabaseNotification;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:web'])->except(['check', 'create', 'verify','resend', 'verifyStore', 'fillupGoogle', 'fillupGoogleUpdate', 'fillupFacebook', 'fillupFacebookUpdate', 'fillupFacebookVerify', 'fillupFacebookStore']);
    }
    public function index(){
        $user = User::findOrFail(auth('web')->user()->id);
        $isPending = Reservation::whereBetween('status', [1, 2])->where('user_id', $user->id)->get();
        $canDelAcc = Reservation::whereBetween('status', [0, 1, 2])->where('user_id', $user->id)->get();
        if($isPending->count() !== 0) $isPending = true;
        else $isPending = false;

        if($canDelAcc->count() !== 0) $canDelAcc = true;
        else $canDelAcc = false;

        return view('users.show', ['activeNav' => 'Profile','user' => $user, 'isPending' => $isPending, 'canDelAcc' => $canDelAcc]);
    }
    public function updateAvatar(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);
        if(isset($user->avatar)) deleteFile($user->avatar);
        $validated['avatar'] = saveImageWithJPG($request, 'avatar', 'avatar');
        $user->update($validated);
        return back()->with('success', 'Your Profile Picture was updated');
    }
    public function updateUserInfo(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'min:3'],
            'last_name' => ['required', 'min:3'],
            'birthday' => ['required', 'date'],
            'country' => ['required', 'min:3'],
            'nationality' => ['required', 'min:3'],
            'contact_code' => ['required'],
            'contact' => ['required', (new Phone)->international()->country(Str::upper($request['contact_code']))],
            'email' => ['required', 'email', Rule::when($user->email !== $request['email'], [Rule::unique('users', 'email')])],
        ], [
            'contact.min' => 'Contact number must be valid',
            'required' => 'Need to fill up your :attribute',
        ]);
        if($validator->fails()){
            return back()->withErrors($validator);
        }
        $validated = $validator->validated();
        
        if($validated){
            // Hash password
            $phone = new PhoneNumber($validated['contact'], Str::upper($validated['contact_code']));

            $validated['contact'] = $phone->formatInternational(); 

            $updated = $user->update($validated);
            // // Create User
            if($updated) return redirect()->route('profile.home')->with('success', 'Your Information was updated');

        }
    }
    public function updatePassword(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'new_password' => ['required', Password::min(8)->symbols()],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ], [
            'required' => 'Need to fill up your :attribute',
        ]);
        if($validator->fails()) return back()->withErrors($validator);
        
        $validator = $validator->validated();
        if (!Hash::check($validator['current_password'], $user->password)) return back()->withErrors(['new_password' => 'The Current Password does not match']);
        $validator['new_password'] = bcrypt($validator['new_password']);
        $updated = $user->update(['password' => $validator['new_password']]);

        if($updated) return redirect()->route('profile.home')->with('success', 'Your New Password was changed');
        
    }
    public function updateValidID(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validated = $request->validate([
            'valid_id' => ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);
        if(isset($user->valid_id)) deleteFile($user->valid_id);
        $validated['valid_id'] = saveImageWithJPG($request, 'valid_id', 'valid_id', 'private');
        $user->update($validated);
        return redirect()->route('profile.home')->with('success', 'Your Valid ID was changed');
    }
    public function create(Request $request){
        // Validate input
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'min:3'],
            'last_name' => ['required', 'min:3'],
            'birthday' => ['required', 'date'],
            'country' => ['required', 'min:3'],
            'nationality' => ['required'],
            'contact_code' => ['required'],
            'contact' => ['required', (new Phone)->international()->country(Str::upper($request['contact_code']))],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->symbols()],
        ], [
            'contact.min' => 'Contact number must be valid',
            'required' => 'Need to fill up your :attribute',
        ]);
        if($validator->fails()){
            return redirect()->route('register')->withErrors($validator)->withInput($request->all());
        }
        $validated = $validator->validated();
        
        if($validated){
            // Hash password
            $validated['password'] = bcrypt($validated['password']);
            $otp = mt_rand(1111,9999);
            $details = [
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'title' => "Let's Verify your Email",
                'body' => 'Verification Code: ' . $otp,
            ];
            Mail::to($validated['email'])->queue(new ReservationMail($details, 'reservation.mail', 'Email Verification'));
            $validated['otp'] = $otp;
            session(['uinfo' => $validated]);

            return redirect()->route('register.verify');

        }
    }
    public function verify(){
        return view('users.register.verify', ['email' => session('uinfo')['email']]);
    }
    public function resend(){
        if(!session()->has('uinfo')){
            session()->forget('uinfo');
            return redirect()->route('register');
        }
        $otp = mt_rand(1111,9999);
        $user_info = session('uinfo');
        $details = [
            'name' => $user_info['first_name'] . ' ' . $user_info['last_name'],
            'title' => "Let's Verify your Email",
            'body' => 'Verification Code: ' . $otp,
        ];
        Mail::to(session('uinfo')['email'])->queue(new ReservationMail($details, 'reservation.mail', 'Email Verification'));
        $user_info['otp'] = $otp;
        session(['uinfo' => $user_info]);
        return redirect()->route('register.verify');
    }
    public function verifyStore(Request $request){
        $validated = $request->validate([
            'code' => ['required', 'digits:4', 'numeric'],
        ]);
        if(!session()->has('uinfo')){
            session()->forget('uinfo');
            return redirect()->route('register');
        }
        if(!((int)$validated['code'] === (int)session('uinfo')['otp'])) return back()->with('error', 'Invalid Code')->withInputs($validated);
        
        unset(session('uinfo')['otp']);
        $user = User::create(session('uinfo'));
        auth('web')->login($user);
        $request->session()->regenerate(); //Regenerate Session ID

        session()->forget('uinfo');
        return redirect()->intended(route('home'))->with('success', 'Welcome ' . auth('web')->user()->name());
        
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
            if(Carbon::createFromFormat('Y-m-d', auth('web')->user()->birthday)->age === Carbon::now()->age) return redirect()->intended(route('home'))->with('success', 'Welcome back and Happy Birthday! ' . auth('web')->user()->first_name . ' ' . auth()->user()->last_name);
            else return redirect()->intended(route('home'))->with('success', 'Welcome back ' . auth('web')->user()->first_name . ' ' . auth()->user()->last_name);
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
        if(session()->has('ginfo')) {
            $guser = session('ginfo');
            return view('users.google.fillup', ['guser' => $guser]);
        }
        else redirect()->route('google.redirect');
    }
    public function fillupGoogleUpdate(Request $request){
        if(session()->has('ginfo')){
            // dd($request->all());
            $validated = $request->validate([
                'birthday' => ['required', ' date'],
                'country' => ['required'],
                'nationality' => ['required'],
                'contact_code' => ['required'],
                'contact' => ['required', (new Phone)->international()->country(Str::upper($request['contact_code']))],
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
            $phone = new PhoneNumber($validated['contact'], Str::upper($validated['contact_code']));
            $validated['contact'] = $phone->formatInternational(); 
            $newUser = User::create($validated);
            if($newUser){
                session()->forget('ginfo');
                Auth::guard('web')->login($newUser);
                $request->session()->regenerate(); //Regenerate Session ID
                return redirect()->intended(route('home'))->with('success', 'Welcome back ' . auth('web')->user()->name());
            }
            else{
                return back()->with('error', 'Something Wrong')->withInput( $validated);
            }
           
        }
        else redirect()->route('google.redirect');


    }
    public function fillupFacebook(){
        if(session()->has('fbubser')) {
            $fbubser = session('fbubser');
            return view('users.facebook.fillup', ['fbuser' => $fbubser]);
        }
        else redirect()->route('facebook.redirect');
    }
    public function fillupFacebookUpdate(Request $request){
        if(session()->has('fbubser')){
            $validated = $request->validate([
                'first_name' => ['required'],
                'last_name' => ['required'],
                'birthday' => ['required', ' date'],
                'country' => ['required'],
                'nationality' => ['required'],
                'contact_code' => ['required'],
                'contact' => ['required', (new Phone)->international()->country(Str::upper($request['contact_code']))],
                'email' => [Rule::when(isset($request['email']), [Rule::unique('users', 'email')])],
            ], [
                'contact.min' => 'Contact number must be valid',
                'required' => 'Need to fill up your :attribute',
            ]);
            $user = session('fbubser'); 
            $phone = new PhoneNumber($validated['contact'], Str::upper($validated['contact_code']));
            $validated['contact'] = $phone->formatInternational(); 
            if(isset($request['email'])){
                $user['first_name'] = $validated['first_name'];
                $user['last_name'] = $validated['last_name'];
                $user['birthday'] = $validated['birthday'];
                $user['country'] = $validated['country'];
                $user['nationality'] = $validated['nationality'];
                $user['contact'] = $validated['contact'];
                $user['password'] = Str::password();
                session(['fbubser' => $user]);
                return redirect()->route('facebook.verify');

            }
            else{
                $validated['facebook_id'] = $user['facebook_id'];
                $validated['avatar'] = $user['avatar'];
                $validated['email'] = $user['email'];
                $validated['password'] = Str::password();
                $newUser = User::create($validated);
                if($newUser){
                        session()->forget('ginfo');
                        Auth::guard('web')->login($newUser);
                        $request->session()->regenerate(); //Regenerate Session ID
                        return redirect()->intended(route('home'))->with('success', 'Welcome back ' . auth('web')->user()->name());
                    }
                    else{
                        return back()->with('error', 'Something Wrong')->withInput( $validated);
                    }   
            }
            
        }
        else redirect()->route('facebook.redirect');


    }
    public function fillupFacebookVerify(){
        $otp = mt_rand(1111,9999);
        $user_info = session('fbubser');
        // dd($user_info);
        $details = [
            'name' => $user_info['first_name'] . ' ' . $user_info['last_name'],
            'title' => "Let's Verify your Email",
            'body' => 'Verification Code: ' . $otp,
        ];
        Mail::to(session('fbubser')['email'])->queue(new ReservationMail($details, 'reservation.mail', 'Email Verification'));
        $user_info['otp'] = $otp;
        session(['fbubser' => $user_info]);
        return view('users.facebook.verify', ['email' => session('fbubser')['email']]);
    }
    public function fillupFacebookStore(Request $request){
        $validated = $request->validate([
            'code' => ['required', 'digits:4', 'numeric'],
        ]);

        if(session()->has('fbubser')){
            if(!$validated['code'] == session('fbubser')['otp']){
                session()->forget('fbubser');
                return back()->with('error', 'Invalid Code')->withInputs(session('fbubser') ?? []);
            }
            unset(session('fbubser')['otp']);
            $user = User::create(session('fbubser'));
            auth('web')->login($user);
            $request->session()->regenerate(); //Regenerate Session ID
            session()->forget('fbubser');
            return redirect()->intended(route('home'))->with('success', 'Welcome ' . auth('web')->user()->name());
            
        }
        else{
            session()->forget('fbubser');
            return redirect()->route('facebook.redirect');
        }
    }
    public function destroyAccount(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validated = $request->validate(['password' => 'required'], ['password.required' => 'Required to Enter Password']);
        if(!Hash::check($validated['password'], $user->password)) return back()->with('error', 'Invalid Credential');
        if(isset($user->valid_id)) deleteFile($user->valid_id);
        if(isset($user->avatar)) deleteFile($user->avatar);
        if($user->delete()){
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->with('success', 'Your Account was permanent delete');
        }
    }
    public function notifications(){
        return view('users.notifications', ['activeNav' => 'Notifications','myNotif' => auth('web')->user()->unreadNotifications ?? []]);
    }
    public function userMarkReads(){
        Auth::guard('web')->user()->unreadNotifications->markAsRead();
        return back();
    }
    public function deleteOneNotif($id){
        $user = Auth::user();
        // Find the notification you want to delete by its ID
        $notification = DatabaseNotification::find(decrypt($id));
        
        // Check if the notification belongs to the user before deleting it
        if ($notification && $notification->notifiable_id === $user->id) {
            $notification->delete();
            
            // Optionally, you can also mark it as read
            // $notification->markAsRead();
            
            return back();
        } else {
            return back()->with('success', "Notification not found or you don't have permission to delete it");
        }
    }
    
}
