<?php

namespace App\Http\Controllers;

use App\Models\WebContent;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class WebContentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function($request, $next) {
            if(auth('system')->user()->type === 0) return $next($request);
            else abort(404);
        });
    }
    public function index(){
        $webcontents = WebContent::all()->first();
        return view('system.webcontent.index', ['activeSb' => 'Website Content', 'webcontents' => $webcontents]);
    }
    public function storeHero(Request $request){
        $web_content = WebContent::all()->first();
        // dd($request->all());
        $validated = $request->validate([
            'main_hero' =>  ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);

        $main_hero = $web_content->hero ?? [];
        // dd($web_content->hero);
        if($request->hasFile('main_hero')){
            $main_hero['main_hero'.count($main_hero) + 1] = saveImageWithJPG($request, 'main_hero', 'hero', 'public');
        }
        else{
            return back()->with('error', 'Required to send image (Hero Image)');
        }
        if(empty($web_content)){
            $created = WebContent::create([
                'hero' => $main_hero,
                'operation' => true,
            ]);
        }
        else{
            $created = $web_content->update([
                'hero' => $main_hero,
            ]);
        }
        if($created) return redirect()->route('system.webcontent.home', '#hero')->with('success', 'Hero Images was created');
    }
    public function showHero($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->hero)) abort(404);
        return view('system.webcontent.hero.show', ['activeSb' => 'Website Content', 'key' => $key, 'hero' => $webcontents->hero]);
    }
    public function updateHero(Request $request, $key){
        $web_content = WebContent::all()->first();
        // dd($request->all());
        $validate = Validator::make($request->all('hero_one'), [
            'hero_one' =>  ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);
        if($validate->fails()) return back()->with('error', 'Required change your Image');
        $key = decrypt($key);
        $main_hero = $web_content->hero ?? [];
        // dd($web_content->hero);
        if($request->hasFile('hero_one')){
            if(array_key_exists($key, $main_hero)){
                deleteFile($main_hero[$key]);
                $main_hero[$key] = saveImageWithJPG($request, 'hero_one', 'hero', 'public');
            }
        }
        else{
            return back()->with('error', 'Required change your Image');
        }
        if(empty($web_content)){
            $created = WebContent::create([
                'hero' => $main_hero,
                'operation' => true,
            ]);
        }
        else{
            $created = $web_content->update([
                'hero' => $main_hero,
            ]);
        }
        if($created) return redirect()->route('system.webcontent.home', '#hero')->with('success', 'Hero Images was updated');
    }
    public function destroyHero(Request $request){
        $webcontents = WebContent::all()->first();
        // dd($request->all());
        $validated = $request->validate([
            'remove_hero.*' =>  ['required'],
        ]);
        $main_hero = $webcontents->hero ?? [];
        foreach($validated['remove_hero'] as $key => $item){
            $heroID = decrypt($key);
            if(array_key_exists($heroID , $main_hero)){
                deleteFile($main_hero[$heroID]);
                unset($main_hero[$heroID]);
            }
            else{
                return redirect()->route('system.webcontent.home', '#hero')->with('error', 'Hero Images does not exist');
            }
        }
        $removed = $webcontents->update([
            'hero' => $main_hero,
        ]);
        if($removed) return redirect()->route('system.webcontent.home')->with('success', 'Hero Image was removed');
    }
    public function destroyHeroOne($key){
        $webcontents = WebContent::all()->first();
        // dd($request->all());
        $key = decrypt($key);
        $main_hero = $webcontents->hero ?? [];
        if(array_key_exists($key , $main_hero)){
            deleteFile($main_hero[$key]);
            unset($main_hero[$key]);
        }
        $removed = $webcontents->update([
            'hero' => $main_hero,
        ]);
        if($removed) return redirect()->route('system.webcontent.home', '#hero')->with('success', 'Hero Image was removed');
    }
    public function storeGallery(Request $request){
        $web_content = WebContent::all()->first();
        // dd($request->all());
        $validated = $request->validate([
            'gallery' =>  ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);

        $gallery = $web_content->gallery ?? [];
        // dd($web_content->hero);
        if($request->hasFile('gallery')){
            $gallery['gallery'.count($gallery) + 1] = saveImageWithJPG($request, 'gallery', 'gallery', 'public');
        }
        else{
            return back()->with('error', 'Required to send image (Gallery Photo)');
        }
        if(empty($web_content)){
            $created = WebContent::create([
                'gallery' => $gallery,
                'operation' => true,
            ]);
        }
        else{
            $created = $web_content->update([
                'gallery' => $gallery,
            ]);
        }
        if($created) return redirect()->route('system.webcontent.home', '#gallery')->with('success', 'Gallery Photo was added');
    }
    public function showGallery($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->gallery)) abort(404);
        return view('system.webcontent.gallery.show', ['activeSb' => 'Website Content', 'key' => $key, 'gallery' => $webcontents->gallery]);
    }
    public function updateGallery(Request $request, $key){
        $web_content = WebContent::all()->first();
        // dd($request->all());
        $validate = Validator::make($request->all('gallery_one'), [
            'gallery_one' =>  ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);
        if($validate->fails()) return back()->with('error', 'Required change your Image');
        $key = decrypt($key);
        $gallery = $web_content->gallery ?? [];
        // dd($web_content->hero);
        if($request->hasFile('gallery_one')){
            if(array_key_exists($key, $gallery)){
                deleteFile($gallery[$key]);
                $gallery[$key] = saveImageWithJPG($request, 'gallery_one', 'gallery', 'public');
            }
        }
        else{
            return back()->with('error', 'Required change your Image');
        }
        if(empty($web_content)){
            $created = WebContent::create([
                'gallery' => $gallery,
                'operation' => true,
            ]);
        }
        else{
            $created = $web_content->update([
                'gallery' => $gallery,
            ]);
        }
        if($created) return redirect()->route('system.webcontent.home', '#gallery')->with('success', 'Hero Images was updated');
    }
    public function destroyGalleryOne($key){
        $webcontents = WebContent::all()->first();
        // dd($request->all());
        $key = decrypt($key);
        $gallery = $webcontents->gallery ?? [];
        if(array_key_exists($key , $gallery)){
            deleteFile($gallery[$key]);
            unset($gallery[$key]);
        }
        $removed = $webcontents->update([
            'gallery' => $gallery,
        ]);
        if($removed) return redirect()->route('system.webcontent.home', '#gallery')->with('success', 'Hero Image was removed');
    }
    public function destroyGallery(Request $request){
        $webcontents = WebContent::all()->first();
        // dd($request->all());
        $validated = $request->validate([
            'remove_gallery.*' =>  ['required'],
        ]);
        $gallery = $webcontents->gallery ?? [];
        foreach($validated['remove_gallery'] as $key => $item){
            $galleryID = decrypt($key);
            if(array_key_exists($galleryID , $gallery)){
                deleteFile($gallery[$galleryID]);
                unset($gallery[$galleryID]);
            }
            else{
                return redirect()->route('system.webcontent.home', '#gallery')->with('error', 'Gallery Images does not exist');
            }
        }
        $removed = $webcontents->update([
            'gallery' => $gallery,
        ]);
        if($removed) return redirect()->route('system.webcontent.home')->with('success', 'Gallery Photo selected was removed');
    }
    public function createContact(Request $request){
        $webcontents = WebContent::all()->first();
        return view('system.webcontent.contact.create', ['activeSb' => 'Website Content', 'contacts' => $webcontents->contact ?? []]);
    }
    public function storeContact(Request $request){
        // dd($request->all());
        if($request->input('contactPerson') === 'new'){
            $validated = Validator::make($request->input(), [
                'person' => ['required'],
                'contact_no' => ['required'],
                'email' => ['required', 'email'],
                'facebook_username' => ['required', 'regex:/^[^\s]+$/'],
                'whatsapp' => ['required'],
            ], [
                'person.required' => 'Required (Name of Person)',
                'contact_no.required' => 'Required (Contact No.)',
                'email.required' => 'Required (Email)',
                'facebook_username.required' => 'Required (Facebook)',
                'facebook_username.regex' => 'The facebook Username cannot contain spaces.',
                'whatsapp.required' => 'Required (WhatsApp)',
            ]);
        }
        elseif($request->input('contactPerson') === 'current'){
            $validated = Validator::make($request->input(), [
                'person' => ['required'],
                'contact_no' => ['nullable'],
                'email' => ['email', 'nullable'],
                'facebook_username' => ['nullable', 'regex:/^[^\s]+$/'],
                'whatsapp' => ['required', 'numeric'],
            ], [
                'person.required' => 'Required (Name of Person)',
                'facebook_username.regex' => 'The facebook Username cannot contain spaces.',
            ]);
        }
        else{
            return back()->with('error', 'Required to choose if new or current person');
        }

        if($validated->fails()){
            return back()->with('error', $validated->errors()->all());
        }
        $validated = $validated->validate();
        $webcontents = WebContent::all()->first();
        $contacts = $webcontents->contact ?? [];
        if(isset($contacts) && isset($webcontents)){
            $contacts[Str::camel($validated['person'])]['contactno'][] = $validated['contact_no'];
            $contacts[Str::camel($validated['person'])]['email'][] = $validated['email'];
            $contacts[Str::camel($validated['person'])]['fbuser'][] = $validated['facebook_username'];
            $contacts[Str::camel($validated['person'])]['whatsapp'][] = $validated['whatsapp'];
            $save = $webcontents->update(['contact' => $contacts]);

        }
        else{
            $contacts[Str::camel($validated['person'])]['name'] = $validated['person'];
            $contacts[Str::camel($validated['person'])]['contactno'][] = $validated['contact_no'];
            $contacts[Str::camel($validated['person'])]['email'][] = $validated['email'];
            $contacts[Str::camel($validated['person'])]['fbuser'][] = $validated['facebook_username'];
            $contacts[Str::camel($validated['person'])]['whatsapp'][] = $validated['whatsapp'];
            $save = WebContent::create(['contact' => $contacts,  'operation' => false]);
        }
        if($save) return redirect()->route('system.webcontent.home', '#contact')->with('success', 'Contact of '.$validated['person'].'was added');
    }
    public function showContact($key){
        $key = decrypt($key);
        // dd($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->contact)) abort(404);
        // dd($webcontents->contact[$key]);
        return view('system.webcontent.contact.show', ['activeSb' => 'Website Content', 'key' => $key, 'contact' => $webcontents->contact]);
    }
    public function updateContact(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->contact)) abort(404);
        $contact = $webcontents->contact;
        if($request->has('contact')){
            $validate = Validator::make($request->input(), [
                'contact' => ['numeric', 'required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact[$key]['contactno'][] = $validate['contact'];
            $message =  $contact[$key]['name'] . ' Contact No. was Added';

        }
        if($request->has('email')){
            $validate = Validator::make($request->input(), [
                'email' => ['email', 'required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact[$key]['email'][] = $validate['email'];
            $message =  $contact[$key]['name'] . ' Email Address was Added';
        }
        if($request->has('facebook_username')){
            $validate = Validator::make($request->input(), [
                'facebook_username' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact[$key]['fbuser'][] = $validate['facebook_username'];
            $message =  $contact[$key]['name'] . ' Facebook Username was Added';

        }
        if($request->has('whatsapp')){
            $validate = Validator::make($request->input(), [
                'whatsapp' => ['required', 'numeric'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact[$key]['whatsapp'][] = $validate['whatsapp'];
            $message =  $contact[$key]['name'] . ' WhatsApp Contact No. was Added';
        }
        if($request->has('name')){
            $validate = Validator::make($request->input(), [
                'name' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact[$key]['name'] = $validate['name'];
            $message =  $contact[$key]['name'] . ' was updated the name';
        }
        if($webcontents->update(['contact' => $contact])) return redirect()->route('system.webcontent.contact.show', encrypt($key))->with('success', $message);
    }
    public function destroyContactOne(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->contact)) abort(404);
        $contact = $webcontents->contact;
        if($request->has('rcontact')){
            $validate = Validator::make($request->input(), [
                'rcontact' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($validate['rcontact'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact[$key]['contactno'][$id])) unset($contact[$key]['contactno'][$id]);
            }
            $message =  $contact[$key]['name'] . ' was remove some contact no.';
        }
        if($request->has('remail')){
            $validate = Validator::make($request->input(), [
                'remail' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($validate['remail'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact[$key]['email'][$id])) unset($contact[$key]['email'][$id]);
            }
            $message =  $contact[$key]['name'] . ' was remove some email';
        }
        if($request->has('rfb')){
            $validate = Validator::make($request->input(), [
                'rfb' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($validate['rfb'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact[$key]['fbuser'][$id])) unset($contact[$key]['fbuser'][$id]);
            }
            $message =  $contact[$key]['name'] . ' was remove some item facebook user';
        }
        if($request->has('rwapp')){
            $validate = Validator::make($request->input(), [
                'rwapp' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($validate['rwapp'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact[$key]['whatsapp'][$id])) unset($contact[$key]['whatsapp'][$id]);
            }
            $message =  $contact[$key]['name'] . ' was remove some WhatsApp Contact No';
        }
        if($webcontents->update(['contact' => $contact])) return redirect()->route('system.webcontent.contact.show', encrypt($key))->with('success', $message);
    }
    public function destroyContact(Request $request){
        $webcontents = WebContent::all()->first();
        // dd($request->all());
        $validated = $request->validate([
            'remove_contact.*' =>  ['required'],
        ]);
        $contact = $webcontents->contact ?? [];
        foreach($validated['remove_contact'] as $key => $item){
            $contactID = decrypt($key);
            if(array_key_exists($contactID , $contact)){
                unset($contact[$contactID]);
            }
            else{
                return redirect()->route('system.webcontent.home', '#contact')->with('error', 'Contact Information  does not exist');
            }
        }
        $removed = $webcontents->update([
            'contact' => $contact,
        ]);
        if($removed) return redirect()->route('system.webcontent.home', '#contact')->with('success', 'Contact Information selected was removed');
    }
    public function storeOperations(Request $request){
        // dd( $request->all());
        $webcontents = WebContent::all()->first();
        $passcode = Validator::make($request->all('passcode'), [
            'passcode' => ['required', 'digits:4', 'numeric'],
        ]);
        if($passcode->fails()) return back()->with('error', $passcode->errors()->all());
        if(!Hash::check($passcode->validate()['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        if(!$request->has('operation')){
            $validated = $request->validate([
                'from' => ['required', 'date',  'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->format('Y-m-d')],
                'to' => ['required', 'date',  'date_format:Y-m-d', 'after:'.$request['from']],
                'reason' => ['required'],
            ]);
        }
        else{
            $validated['operation'] = true;
            $validated['from'] = null;
            $validated['to'] = null;
            $validated['reason'] = null;

        }

        if(isset($webcontents)){
            $updated = $webcontents->update($validated);
        }
        else{
            $updated=  WebContent::create($validated);
        }
        if($updated) return redirect()->route('system.webcontent.home', '#reservation')->with('success', 'Reservation Operation was updated');

    }
}
