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
        if($validated->fails()){
            return back()->with('error', $validated->errors()->all())->withInput($validated->getData());
        }
        $validated = $validated->validate();
        $webcontents = WebContent::all()->first();
        $contacts = $webcontents->contact ?? [];
        $contacts['other'][Str::camel($validated['person'])]['name'] = $validated['person'];
        $contacts['other'][Str::camel($validated['person'])]['contactno'][] = $validated['contact_no'];
        $contacts['other'][Str::camel($validated['person'])]['email'][] = $validated['email'];
        $contacts['other'][Str::camel($validated['person'])]['fbuser'][] = $validated['facebook_username'];
        $contacts['other'][Str::camel($validated['person'])]['whatsapp'][] = $validated['whatsapp'];
        
        if(isset($webcontents)) $save = $webcontents->update(['contact' => $contacts]);
        else $save = WebContent::create(['contact' => $contacts,  'operation' => true]);
        if($save) return redirect()->route('system.webcontent.home', '#contact')->with('success', 'Contact of '.$validated['person'].'was added');
    }
    public function storeMainContact(Request $request){
        $validated = Validator::make($request->input(), [
            'contact' => ['required'],
            'email' => ['required', 'email'],
            'facebook_link' => ['url'],
            'whatsapp_number' => ['required'],
        ], [
            'contact.required' => 'Required (Contact No.)',
            'email.required' => 'Required (Email)',
            'facebook_link.required' => 'Required (Facebook Link)',
            'facebook_link.url' => 'Must be Url (Facebook Link)',
            'whatsapp_number.required' => 'Required (WhatsApp)',
        ]);
        if($validated->fails()){
            return back()->with('error', $validated->errors()->all())->withInput($validated->getData());
        }
        $validated = $validated->validate();
        $webcontents = WebContent::all()->first();
        $contacts = $webcontents->contact ?? [];
        $contacts['main']['contactno'] = $validated['contact'];
        $contacts['main']['email'] = $validated['email'];
        $contacts['main']['fbuser'] = $validated['facebook_link'];
        $contacts['main']['whatsapp'] = $validated['whatsapp_number'];
        
        if(isset($webcontents)) $save = $webcontents->update(['contact' => $contacts]);
        else $save = WebContent::create(['contact' => $contacts,  'operation' => true]);
        if($save) return redirect()->route('system.webcontent.home', '#contact')->with('success', 'Main Contact was added');
    }
    public function updateMainContact(Request $request){
        $webcontents = WebContent::all()->first();
        if(!array_key_exists('main', $webcontents->contact)) abort(404);
        $validated = Validator::make($request->input(), [
            'contact' => ['required'],
            'email' => ['required', 'email'],
            'facebook_link' => ['url'],
            'whatsapp_number' => ['required'],
        ], [
            'contact.required' => 'Required (Contact No.)',
            'email.required' => 'Required (Email)',
            'facebook_link.required' => 'Required (Facebook Link)',
            'facebook_link.url' => 'Must be Url (Facebook Link)',
            'whatsapp_number.required' => 'Required (WhatsApp)',
        ]);
        if($validated->fails()){
            return back()->with('error', $validated->errors()->all())->withInput($validated->getData());
        }
        $validated = $validated->validate();
        $webcontents = WebContent::all()->first();
        $contacts = $webcontents->contact ?? [];
        $contacts['main']['contactno'] = $validated['contact'];
        $contacts['main']['email'] = $validated['email'];
        $contacts['main']['fbuser'] = $validated['facebook_link'];
        $contacts['main']['whatsapp'] = $validated['whatsapp_number'];
        
        if(isset($webcontents)) $save = $webcontents->update(['contact' => $contacts]);
        if($save) return redirect()->route('system.webcontent.home', '#contact')->with('success', 'Main Contact was updated');
    }
    public function showContact($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->contact['other'])) abort(404);
        // dd($webcontents->contact['other'][$key]);
        return view('system.webcontent.contact.show', ['activeSb' => 'Website Content', 'key' => $key, 'contact' => $webcontents->contact['other']]);
    }
    public function updateContact(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->contact['other'])) abort(404);
        $contact = $webcontents->contact;
        if($request->has('contact')){
            $validate = Validator::make($request->input(), [
                'contact' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact['other'][$key]['contactno'][] = $validate['contact'];
            $message =  $contact['other'][$key]['name'] . ' Contact No. was Added';

        }
        if($request->has('email')){
            $validate = Validator::make($request->input(), [
                'email' => ['email', 'required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact['other'][$key]['email'][] = $validate['email'];
            $message =  $contact['other'][$key]['name'] . ' Email Address was Added';
        }
        if($request->has('facebook_username')){
            $validate = Validator::make($request->input(), [
                'facebook_username' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact['other'][$key]['fbuser'][] = $validate['facebook_username'];
            $message =  $contact['other'][$key]['name'] . ' Facebook Username was Added';

        }
        if($request->has('whatsapp')){
            $validate = Validator::make($request->input(), [
                'whatsapp' => ['required', 'numeric'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact['other'][$key]['whatsapp'][] = $validate['whatsapp'];
            $message =  $contact['other'][$key]['name'] . ' WhatsApp Contact No. was Added';
        }
        if($request->has('name')){
            $validate = Validator::make($request->input(), [
                'name' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $contact['other'][$key]['name'] = $validate['name'];
            $message =  $contact['other'][$key]['name'] . ' was updated the name';
        }
        if($webcontents->update(['contact' => $contact])) return redirect()->route('system.webcontent.contact.show', encrypt($key))->with('success', $message);
    }
    public function destroyContactOne(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->contact['other'])) abort(404);
        $contact = $webcontents->contact;
        if($request->has('rcontact')){
            $validate = Validator::make($request->input(), [
                'rcontact' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($validate['rcontact'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact[$key]['contactno'][$id])) unset($contact['other'][$key]['contactno'][$id]);
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
                if(isset($contact['other'][$key]['email'][$id])) unset($contact['other'][$key]['email'][$id]);
            }
            $message =  $contact['other'][$key]['name'] . ' was remove some email';
        }
        if($request->has('rfb')){
            $validate = Validator::make($request->input(), [
                'rfb' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($validate['rfb'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact['other'][$key]['fbuser'][$id])) unset($contact['other'][$key]['fbuser'][$id]);
            }
            $message =  $contact['other'][$key]['name'] . ' was remove some item facebook user';
        }
        if($request->has('rwapp')){
            $validate = Validator::make($request->input(), [
                'rwapp' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($validate['rwapp'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact['other'][$key]['whatsapp'][$id])) unset($contact[$key]['whatsapp'][$id]);
            }
            $message =  $contact['other'][$key]['name'] . ' was remove some WhatsApp Contact No';
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
            if(array_key_exists($contactID , $contact['other'])){
                unset($contact['other'][$contactID]);
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
                'to' => ['required', 'date',  'date_format:Y-m-d', 'after_or_equal:'.$request['from']],
                'reason' => ['required'],
            ]);
            $validated['operation'] = false;
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
    public function createPaymentGcash(){
        return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function storePaymentGcash(Request $request){ 
        $webcontents = WebContent::all()->first();
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $validate = $request->validate([
            'gcash_number' => ['required', 'numeric', 'min:10'],            'name' => ['required'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
        ]);
        $payments = $webcontents->payment ?? [];
        if(count( $payments['gcash']) === 0) $gcp = true;
        else $gcp = false;
        if($request->hasFile('image')){                          // storage/app/logos
            $validate['image'] = saveImageWithJPG($request, 'image', 'ref_gcash', 'private');
            $payments['gcash'][] =  [
                'name' => $validate['name'],
                'number' => $validate['gcash_number'],
                'qrcode' => $validate['image'],
                'priority' => $gcp,
            ];;
        }
        else{
            $payments['gcash'][] =  [
                'name' => $validate['name'],
                'number' => $validate['gcash_number'],
                'priority' => $gcp,
            ];;
        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'Gcash Payment Reference was created');

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function showPaymentGcash($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['gcash'] ?? [])) abort(404);
        return view('system.webcontent.payment.show-gcash', ['activeSb' => 'Website Content', 'key' => $key, 'gcash' =>  $webcontents->payment['gcash']]);
    }
    public function editPaymentGcash($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['gcash'] ?? [])) abort(404);
        return view('system.webcontent.payment.edit-gcash', ['activeSb' => 'Website Content', 'key' => $key, 'gcash' =>  $webcontents->payment['gcash']]);
    }
    public function updatePaymentGcash(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['gcash'] ?? [])) abort(404);
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $validate = $request->validate([
            'gcash_number' => ['required', 'numeric', 'min:10'],            'name' => ['required'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
        ]);
        $payments = $webcontents->payment ?? [];
        if($request->hasFile('image')){       
            if(isset($payments['gcash'][$key]['qrcode'])) deleteFile($payments['gcash'][$key]['qrcode']);
            $validate['image'] = saveImageWithJPG($request, 'image', 'ref_gcash', 'private');
            $payments['gcash'][$key] =  [
                'name' => $validate['name'],
                'number' => $validate['gcash_number'],
                'qrcode' => $validate['image'],
                'priority' => $payments['gcash'][$key]['priority'],
            ];
        }
        else{
            $payments['gcash'][$key] =  [
                'name' => $validate['name'],
                'number' => $validate['gcash_number'],
                'qrcode' => $payments['gcash'][$key]['qrcode'],
                'priority' => $payments['gcash'][$key]['priority'],

            ];
        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'Gcash Payment Reference ('.$validate['name'].') was updated');

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function destroyPaymentGcash(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['gcash'] ?? [])) abort(404);
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $payments = $webcontents->payment ?? [];
        foreach($payments['gcash'] as $gcahsKey => $item){
            if($gcahsKey === $key){
                $name = $payments['gcash'][$key]['name'];
                if(isset($payments['gcash'][$key]['qrcode'])) deleteFile($payments['gcash'][$key]['qrcode']);
                unset($payments['gcash'][$key]);
            }
        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'Gcash Reference of '.$name.' was removed');

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function createPaymentPayPal(){
        return view('system.webcontent.payment.paypal', ['activeSb' => 'Website Content']);
    }
    public function storePaymentPayPal(Request $request){ 
        $webcontents = WebContent::all()->first();
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $validate = $request->validate([
            'paypal_number' => ['required', 'numeric', 'min:10'],
            'name' => ['required'],
            'email' => ['required', 'email'],
            'username' => ['required'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
        ]);
        $payments = $webcontents->payment ?? [];
        if(count( $payments['paypal']) === 0) $ppp = true;
        else $ppp = false;
                
        if($request->hasFile('image')){                          // storage/app/logos
            $validate['image'] = saveImageWithJPG($request, 'image', 'ref_paypal', 'private');
            $payments['paypal'][] =  [
                'name' => $validate['name'],
                'number' => $validate['paypal_number'],
                'email' => $validate['email'],
                'username' => $validate['username'],
                'image' => $validate['image'],
                'priority' => $ppp,
            ];
        }
        else{
            $payments['paypal'][] =  [
                'name' => $validate['name'],
                'number' => $validate['paypal_number'],
                'email' => $validate['email'],
                'username' => $validate['username'],
                'priority' => $ppp,
            ];
        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'PayPal Payment Reference was created');

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function showPaymentPayPal($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['paypal'] ?? [])) abort(404);
        return view('system.webcontent.payment.show-paypal', ['activeSb' => 'Website Content', 'key' => $key, 'paypal' =>  $webcontents->payment['paypal']]);
    }
    public function editPaymentPayPal($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['paypal'] ?? [])) abort(404);
        return view('system.webcontent.payment.edit-paypal', ['activeSb' => 'Website Content', 'key' => $key, 'paypal' =>  $webcontents->payment['paypal']]);
    }
    public function updatePaymentPayPal(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['paypal'] ?? [])) abort(404);
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $validate = $request->validate([
            'paypal_number' => ['required', 'numeric', 'min:10'],
            'name' => ['required'],
            'email' => ['required', 'email'],
            'username' => ['required'],
            'image' =>  ['image', 'mimes:jpeg,png,jpg', 'max:5024'],
        ]);
        $payments = $webcontents->payment ?? [];
        if($request->hasFile('image')){                          // storage/app/logos
            $validate['image'] = saveImageWithJPG($request, 'image', 'ref_paypal', 'private');
            if(isset($payments['paypal'][$key]['image'])) deleteFile($payments['paypal'][$key]['image']);
            $payments['paypal'][$key] =  [
                'name' => $validate['name'],
                'number' => $validate['paypal_number'],
                'email' => $validate['email'],
                'username' => $validate['username'],
                'image' => $validate['image'],
                'priority' => $payments['paypal'][$key]['priority'],

            ];
        }
        else{
            $payments['paypal'][$key] =  [
                'name' => $validate['name'],
                'number' => $validate['paypal_number'],
                'email' => $validate['email'],
                'username' => $validate['username'],
                'image' => $payments['paypal'][$key]['image'],
                'priority' => $payments['paypal'][$key]['priority'],

            ];

        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'PayPal Payment Reference ('.$validate['name'].') was updated');

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function destroyPaymentPayPal(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['paypal'] ?? [])) abort(404);
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $payments = $webcontents->payment ?? [];
        foreach($payments['paypal'] as $paypalKey => $item){
            if($paypalKey === $key){
                $name = $payments['paypal'][$key]['name'];
                if(isset($payments['paypal'][$key]['image'])) deleteFile($payments['paypal'][$key]['image']);
                unset($payments['paypal'][$key]);
            }
        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'PayPal Reference of '.$name.' was removed');

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function priorityPaymentGcash(Request $request){
        // dd(decrypt($request->all('priority')['priority']));
        $validate = Validator::make($request->all(), [
            'priority' => ['required'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate =$validate->validate();
        $key = decrypt($validate['priority']);
        $webcontents = WebContent::all()->first();
        $payments = $webcontents->payment ?? [];
        if(!array_key_exists($key, $webcontents->payment['gcash'] ?? [])) abort(404);
            foreach($payments['gcash'] as $gcashID => $item){
                if($gcashID === $key){
                    $payments['gcash'][$gcashID]['priority'] = true;
                }
                else{
                    $payments['gcash'][$gcashID]['priority'] = false;
                }
            }
        
        if(isset($webcontents)) $updated = $webcontents->update(['payment' => $payments]);
        else $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'Gcash Payment Reference ('.$payments['gcash'][$key]['name'].') was set priority');
    }
    public function priorityPaymentPayPal(Request $request){
        // dd(decrypt($request->all('priority')['priority']));
        $validate = Validator::make($request->all(), [
            'priority' => ['required'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate =$validate->validate();
        $key = decrypt($validate['priority']);
        $webcontents = WebContent::all()->first();
        $payments = $webcontents->payment ?? [];
        if(!array_key_exists($key, $webcontents->payment['paypal'] ?? [])) abort(404);
            foreach($payments['paypal'] as $paypalID => $item){
                if($paypalID === $key){
                    $payments['paypal'][$paypalID]['priority'] = true;
                }
                else{
                    $payments['paypal'][$paypalID]['priority'] = false;
                }
            }
        
        if(isset($webcontents)) $updated = $webcontents->update(['payment' => $payments]);
        else $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'PayPal Payment Reference ('.$payments['paypal'][$key]['name'].') was set priority');
    }
    public function priorityPaymentBT(Request $request){
        $validate = Validator::make($request->all(), [
            'priority' => ['required'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate =$validate->validate();
        $key = decrypt($validate['priority']);
        $webcontents = WebContent::all()->first();
        $payments = $webcontents->payment ?? [];
        if(!array_key_exists($key, $webcontents->payment['bankTransfer'] ?? [])) abort(404);
            foreach($payments['bankTransfer'] as $btID => $item){
                if($btID === $key) $payments['bankTransfer'][$btID]['priority'] = true;
                else $payments['bankTransfer'][$btID]['priority'] = false;
            }
        
        if(isset($webcontents)) $updated = $webcontents->update(['payment' => $payments]);
        else $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'PayPal Payment Reference ('.$payments['bankTransfer'][$key]['name'].') was set priority');
    }
    public function createPaymentBT(){
        return view('system.webcontent.payment.bank-transfer', ['activeSb' => 'Website Content']);
    }
    public function storePaymentBT(Request $request){ 
        $webcontents = WebContent::all()->first();
        // dd($request->all());
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $validate = $request->validate([
            'acc_no' => ['required'],
            'name' => ['required'],
            'contact' => ['required'],
        ]);
        $payments = $webcontents->payment ?? [];
        if(count($payments['bankTransfer']) === 0) $btp = true;
        else $btp = false;
        $payments['bankTransfer'][] =  [
            'acc_no' => $validate['acc_no'],
            'name' => $validate['name'],
            'contact' => $validate['contact'],
            'priority' => $btp,
        ];

        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'Bank Transfer Reference was created');

    }
    public function showPaymentBT($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['bankTransfer'] ?? [])) abort(404);
        return view('system.webcontent.payment.show-bnktr', ['activeSb' => 'Website Content', 'key' => $key, 'bankTransfer' =>  $webcontents->payment['bankTransfer']]);
    }
    public function editPaymentBT($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['bankTransfer'] ?? [])) abort(404);
        return view('system.webcontent.payment.edit-bank-transfer', ['activeSb' => 'Website Content', 'key' => $key, 'bankTransfer' =>  $webcontents->payment['bankTransfer']]);
    }
    public function updatePaymentBT(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['bankTransfer'] ?? [])) abort(404);
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $validate = $request->validate([
            'acc_no' => ['required'],
            'name' => ['required'],
            'contact' => ['required'],
        ]);
        $payments = $webcontents->payment ?? [];
        $payments['bankTransfer'][$key] =  [
            'acc_no' => $validate['acc_no'],
            'name' => $validate['name'],
            'contact' => $validate['contact'],
        ];
        if(isset($webcontents))$updated = $webcontents->update(['payment' => $payments]);
        else $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'Bank Transfer Reference ('.$validate['name'].') was updated');
    }
    public function destroyPaymentBT(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->payment['paypal'] ?? [])) abort(404);
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $payments = $webcontents->payment ?? [];
        foreach($payments['bankTransfer'] as $btKey => $item){
            if($btKey === $key){
                $name = $payments['bankTransfer'][$key]['name'];
                unset($payments['bankTransfer'][$key]);
            }
        }
        if(isset($webcontents)) $updated = $webcontents->update(['payment' => $payments]);
        else $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        
        if($updated) return redirect()->route('system.webcontent.home', '#payment')->with('success', 'Bank Transfer Reference of '.$name.' was removed');

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
}
