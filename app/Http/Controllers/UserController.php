<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ReservationMail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Notifications\DatabaseNotification;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:web'])->except(['check', 'create', 'verify','resend', 'verifyStore', 'fillupGoogle', 'fillupGoogleUpdate', 'fillupFacebook', 'fillupFacebookUpdate', 'fillupFacebookVerify', 'fillupFacebookStore', 'login', 'register', 'forgotPass']);
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
    public function login(){
        return view('users.login');
    }
    public function register(){
        return view('users.register');
    }
    public function forgotPass(){
        return view('auth.passwords.email');
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
        $validated = $request->validate([
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
        if($validated){
            // Hash password
            $phone = new PhoneNumber($validated['contact'], Str::upper($validated['contact_code']));

            $validated['contact'] = $phone->formatInternational(); 
            if($user->email === $request['email']) $updated = $user->update($validated);
            else{
                $otp = mt_rand(1111,9999);
                $details = [
                    'name' => $user->name(),
                    'title' => "New Email Verify ",
                    'body' => 'Verification Code: ' . $otp,
                ];
                Mail::to($validated['email'])->queue(new ReservationMail($details, 'reservation.mail', 'Email Verification'));
                $validated['otp'] = $otp;
                session(['upuinfo' => encryptedArray($validated)]);
                return redirect()->route('profile.update.user.info.email.verify', encrypt($user->id));
            }
            // // Create User
            if($updated) return redirect()->route('profile.home')->with('success', 'Your Information was updated');

        }
    }
    public function emailVerify($id){
        $user = User::findOrFail(decrypt($id));
        if(!session()->has('upuinfo')) return redirect()->route('profile.home');
        return view('users.verify-update', ['email' => decrypt(session('upuinfo')['email']), 'user' => $user]);
    }
    public function resendUpdateEmail($id){
        $user = User::findOrFail(decrypt($id));
        if(!session()->has('upuinfo')) return redirect()->route('profile.home');
        $otp = mt_rand(1111,9999);
        $otp = encrypt($otp);
        $user_info = session('upuinfo');
        $details = [
            'name' => decrypt($user_info['first_name']) . ' ' . decrypt( $user_info['last_name']),
            'title' => "Let's Verify your Email",
            'body' => 'Verification Code: ' . decrypt($otp),
        ];
        Mail::to(decrypt(session('upuinfo')['email']))->queue(new ReservationMail($details, 'reservation.mail', 'Email Verification'));
        $user_info['otp'] = $otp;
        session(['upuinfo' => $user_info]);
        return redirect()->route('profile.update.user.info.email.verify', encrypt($user->id));
    }
    public function emailVerified(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validated = $request->validate([
            'code' => ['required', 'digits:4', 'numeric'],
        ]);
        if(!session()->has('upuinfo')){
            session()->forget('upuinfo');
            return redirect()->route('profile.home');
        }
        $user_info = decryptedArray(session('upuinfo'));
        if(!((int)$validated['code'] === (int)$user_info['otp'])) return back()->with('error', 'Invalid Code')->withInputs($validated);
        
        unset($user_info['otp']);

        $updated = $user->update($user_info);        

        session()->forget('upuinfo');
        if($updated) return redirect()->route('profile.home')->with('success', 'Your Information was updated');

    }
    public function updatePassword(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validator = Validator::make($request->all(), [
            'current_password' => Rule::when(isset($user->password), ['required']),
            'new_password' => ['required', Password::min(8)->symbols()],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ], [
            'required' => 'Need to fill up your :attribute',
        ]);
        if($validator->fails()) return back()->withErrors($validator);
        
        $validator = $validator->validated();
        if (isset($user->password) && !Hash::check($validator['current_password'], $user->password)) return back()->withErrors(['new_password' => 'The Current Password does not match']);
        $validator['new_password'] = bcrypt($validator['new_password']);
        $updated = $user->update(['password' => $validator['new_password']]);

        if($updated) return redirect()->route('profile.home')->with('success', 'Your Password was changed');
        
    }
    public function updateValidID(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validated = $request->validate([
            'valid_id' => ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);
        if(isset($user->valid_id)) deleteFile($user->valid_id, 'private');
        $validated['valid_id'] = saveImageWithJPG($request, 'valid_id', 'valid_id', 'private');
        $user->update($validated);
        return redirect()->route('profile.home')->with('success', 'Your Valid ID was changed');
    }
    public function create(Request $request){
        // Validate input
        // dd($request->all());
        $validated = $request->validate([
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
        $accecpted = Validator::make($request->all(), [
            'acptm' => ['accepted'],
        ], [
            'acptm.accepted' => 'Required to accepted Term & Conditiions',
        ]);
        if($accecpted->fails()) return back()->with('error', $accecpted->errors()->all())->withInput($request->all());
        if($validated){
            $phone = new PhoneNumber($validated['contact'], Str::upper($validated['contact_code']));
            $validated['contact'] = $phone->formatInternational();    

            $validated['password'] = bcrypt($validated['password']);
            $otp = mt_rand(1111,9999);
            $details = [
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'title' => "Let's Verify your Email",
                'body' => 'Verification Code: ' . $otp,
            ];
            Mail::to($validated['email'])->queue(new ReservationMail($details, 'reservation.mail', 'Email Verification'));
            $validated['otp'] = $otp;
            session(['uinfo' => encryptedArray($validated)]);

            return redirect()->route('register.verify');

        }
    }
    public function verify(){
        if(!session()->has('uinfo')) return back();
        return view('users.register.verify', ['email' => decrypt(session('uinfo')['email'])]);
    }
    public function resend(){
        if(!session()->has('uinfo')){
            session()->forget('uinfo');
            return redirect()->route('register');
        }
        $otp = mt_rand(1111,9999);
        $otp = encrypt($otp);
        $user_info = session('uinfo');
        $details = [
            'name' => decrypt($user_info['first_name']) . ' ' . decrypt( $user_info['last_name']),
            'title' => "Let's Verify your Email",
            'body' => 'Verification Code: ' . decrypt($otp),
        ];
        Mail::to(decrypt(session('uinfo')['email']))->queue(new ReservationMail($details, 'reservation.mail', 'Email Verification'));
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
        if(!((int)$validated['code'] === (int)decrypt(session('uinfo')['otp']))) return back()->with('error', 'Invalid Code')->withInputs($validated);
        
        unset(session('uinfo')['otp']);
        $user = User::create(decryptedArray(session('uinfo')));
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
        if(Auth::guard('web')->attempt($validated, (isset($request['remember']) ? true : false) )){
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
            $guser = decryptedArray(session('ginfo'));
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


            $user = decryptedArray(session('ginfo')); 
            $validated['google_id'] = $user['google_id'];
            $validated['avatar'] = $user['avatar'];
            $validated['first_name'] = $user['first_name'];
            $validated['last_name'] = $user['last_name'];
            $validated['email'] = $user['email'];
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
    public function destroyAccount(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validated = Validator::make($request->all(), ['dltpass' => 'required'], ['dltpass.required' => 'Required to Enter Password to Delete your Account']);
        if($validated->fails()) return back()->with('error', $validated->errors()->all());

        $validated = $validated->validate();

        if(!Hash::check($validated['dltpass'], $user->password)) return back()->with('error', 'Invalid Credential');
        if(isset($user->valid_id)) deleteFile($user->valid_id, 'private');
        if(isset($user->avatar)) deleteFile($user->avatar);
        foreach (Reservation::where('user_id', $user->id)->get() ?? [] as $list) $list->update([
            'otherinfo' => [
                "birthday" =>  $user->birthday,
                "first_name" => $user->first_name,
                "last_name" =>  $user->last_name,
                'nationality' => $user->nationality,
                'country' => $user->country,
                'email' => $user->email,
                'contact' => $user->contact,
            ],
        ]);
        if($user->delete()){
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->with('success', 'Your Account was permanent delete');
        }
    }
    public function sendCode($id){
        $user = User::findOrFail(decrypt($id));
        $otp = mt_rand(1111,9999);
        $otp = encrypt($otp);
        $details = [
            'name' => $user->name(),
            'title' => "Account Deletion Confirmation",
            'body' => 'Code: ' . decrypt($otp),
        ];
        Mail::to($user->email)->queue(new ReservationMail($details, 'reservation.mail', 'Delete Account Verification'));
        session(['code' => $otp]);
        return response()->json(['status' => 'success']);
    }
    public function destroyAccCode(Request $request, $id){
        $user = User::findOrFail(decrypt($id));
        $validated = Validator::make($request->all(), ['code' => 'required|numeric|digits:4']);
        if($validated->fails()) return back()->with('error', $validated->errors()->all());

        $validated = $validated->validate();

        if(session()->has('code') && $validated['code'] != decrypt(session('code'))) return back()->with('error', 'Invalid Code');
        if(isset($user->valid_id)) deleteFile($user->valid_id, 'private');
        if(isset($user->avatar)) deleteFile($user->avatar);
        foreach (Reservation::where('user_id', $user->id)->get() ?? [] as $list) $list->update([
            'otherinfo' => [
                'name' => $user->name(),
                'age' => $user->age(),
                'nationality' => $user->nationality,
                'country' => $user->country,
                'email' => $user->email,
                'contact' => $user->contact,
            ],
        ]);
        if($user->delete()){
            session()->has('code');
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
