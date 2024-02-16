<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\AuditTrail;
use App\Models\WebContent;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class WebContentController extends Controller
{
    private $system_user;

    public function __construct()
    {
        $this->system_user = auth('system');
        $this->middleware(function ($request, $next){
            if($this->system_user->user()->type == 0 || in_array("Website Content",$this->system_user->user()->modules)) return $next($request);
            else abort(404);
        });
    }
    private function employeeLogNotif($action){
        $user = auth()->guard('system')->user();
        AuditTrail::create([
            'system_id' => $user->id,
            'role' => $user->type ?? '',
            'action' => $action,
            'module' => 'Web Content',
        ]);
    }
    public function index(){
        $webcontents = WebContent::all()->first();
        return view('system.webcontent.index', ['activeSb' => 'Website Content', 'webcontents' => $webcontents]);
    }
    public function storeHero(Request $request){
        $web_content = WebContent::all()->first();
        // dd($request->all());
        $validated = Validator::make($request->all(), [
            'main_hero' =>  ['required', 'image', 'mimes:jpeg,png,jpg'],
        ]);
        if($validated->fails()) return redirect()->route('system.webcontent.home', '#hero')->with('error', $validated->errors()->all());
        $validated = $validated->validate();
        $main_hero = $web_content->hero ?? [];
        // dd($web_content->hero);
        if($request->hasFile('main_hero')){
            $count = count($main_hero)+1;
            for ($i=1; $i <= count($main_hero); $i++) {
                if(!in_array('main_hero'.$i, array_keys($main_hero))) {
                    $count = $i;
                    break;
                }
            }
            $main_hero['main_hero'.$count] = saveImageWithJPG($request, 'main_hero', 'hero', 'public');
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
        if($created) {
            $message =  'Hero Photo was created';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#hero')->with('success', $message);
        }
    }
    public function showHero($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->hero)) abort(404);
        return view('system.webcontent.hero.show', ['activeSb' => 'Website Content', 'key' => $key, 'hero' => $webcontents->hero]);
    }
    public function updateHero(Request $request, $key){
        $web_content = WebContent::all()->firstOrFail();
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
            return back()->with('error', 'Required change your Photo');
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
        if($created) {
            $message =  'Hero Photo was updated';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#hero')->with('success', $message);
        }
    }
    public function destroyHero(Request $request){
        $webcontents = WebContent::all()->firstOrFail();
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
        if($removed) {
            $message = count($validated['remove_hero']). ' Hero Photo selected was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home')->with('success', $message);
        }
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
        if($removed) {
            $message = 'Hero Photo was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#hero')->with('success', $message);
        }
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
            $count = count($gallery)+1;
            for ($i=1; $i <= count($gallery); $i++) {
                if(!in_array('gallery'.$i, array_keys($gallery))){
                     $count = $i;
                     break;
                }
            }
            $gallery['gallery'.$count] = saveImageWithJPG($request, 'gallery', 'gallery', 'public');
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
        if($created) {
            $message = 'Gallery Photo was added';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#gallery')->with('success', $message);
        }
    }
    public function showGallery($key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->gallery)) abort(404);
        return view('system.webcontent.gallery.show', ['activeSb' => 'Website Content', 'key' => $key, 'gallery' => $webcontents->gallery]);
    }
    public function updateGallery(Request $request, $key){
        $web_content = WebContent::all()->firstOrFail();
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
        if($created) {
            $message = 'Gallery Photo was updated';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#gallery')->with('success', $message);
        }
    }
    public function destroyGalleryOne($key){
        $webcontents = WebContent::all()->firstOrFail();
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
        if($removed) {
            $message = 'Gallery Photo was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#gallery')->with('success', $message);
        }
    }
    public function destroyGallery(Request $request){
        $webcontents = WebContent::all()->firstOrFail();
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
        if($removed) {
            $message = count($validated['remove_gallery']) . ' Gallery Photo selected was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', "#gallery")->with('success', $message);
        }
    }
    public function storeTour(Request $request){
        $web_content = WebContent::all()->first();
        $validated = Validator::make($request->all(), [
            'tour_type' =>  ['required'],
            'tour' =>  ['required'],
            'location' =>  ['nullable'],
            'image' =>  ['required', 'image', 'mimes:jpeg,png,jpg'],
        ], [
            'tour_type.required' => 'Required Choose Type of Tour',
            'tour.required' => 'Required Enter Tour Destination',
            'image.required' => 'Required Upload Image of Tour',
            'image.image' => 'Upload Image Only',
        ]);
        if($validated->fails()) return redirect()->route('system.webcontent.home', '#tour')->with('error', $validated->errors()->all());
        $validated = $validated->validate();
        $tour = $web_content->tour ?? []; 
        $key = null; 
        $path = null;
        if($validated['tour_type'] == 'Main Tour') {
            $key = 'mt';
            $path = '/main_tour';
            $type = 'mainTour';
        }
        if($validated['tour_type'] == 'Side Tour') {
            $key = 'st';
            $path = '/side_tour';
            $type = 'sideTour';
        }
        // dd($web_content->hero);
        $count = count($tour[$type])+1;
        for ($i=1; $i <= count($tour[$type]); $i++) {
            if(!in_array($key.$i, array_keys($tour[$type]))) {
                $count = $i;
                break;
            }
        }
        $tour[$type][$key.$count] = [
            'title' => $validated['tour'],
            'location' => $validated['location'],
            'image' => saveImageWithJPG($request, 'image', 'tour'.$path, 'public'),
        ];

        if(empty($web_content)){
            $created = WebContent::create([
                'tour' => $tour,
            ]);
        }
        else{
            $created = $web_content->update([
                'tour' => $tour,
            ]);
        }
        if($created) {
            $message = $validated['tour'] .' was created';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#tour')->with('success', $message);
        }
    }
    public function showTour($type, $key){
        $key = decrypt($key);
        $type = decrypt($type);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->tour[$type])) abort(404);
        return view('system.webcontent.tour.show', ['activeSb' => 'Website Content', 'type' => $type ,'key' => $key, 'tour' => $webcontents->tour[$type][$key]]);
    }
    public function updateTour(Request $request, $type, $key){
        $key = decrypt($key);
        $type = decrypt($type);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->tour[$type])) abort(404);
        $validated = Validator::make($request->all(), [
            'tour' =>  ['required'],
            'location' =>  ['nullable'],
            'image' =>  ['nullable', 'image', 'mimes:jpeg,png,jpg'],
        ], [
            'tour.required' => 'Required Enter Tour Destination',
            'image.required' => 'Required Upload Image of Tour',
            'image.image' => 'Upload Image Only',
        ]);
        if($validated->fails()) return redirect()->route('system.webcontent.home', '#tour')->with('error', $validated->errors()->all());
        $validated = $validated->validate();
        $tour = $webcontents->tour ?? []; 
        $tour[$type][$key]['title'] = $validated['tour'];
        $tour[$type][$key]['location'] = $validated['location'];
        if($request->hasFile('image')){
            if(strpos($key, 'mt') !== false) $path = '/main_tour';
            if(strpos($key, 'st') !== false) $path = '/side_tour';
            $tour[$type][$key]['image'] = saveImageWithJPG($request, 'image', 'tour'.$path, 'public');
        }
        if(empty($webcontents)){
            $updated = WebContent::create([
                'tour' => $tour,
            ]);
        }
        else{
            $updated = $webcontents->update([
                'tour' => $tour,
            ]);
        }
        if($updated) {
            $message = $validated['tour'] .' was updated';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#tour')->with('success', $message);
        }
    }
    public function destroyTourOne($type, $key){
        $webcontents = WebContent::all()->first();
        // dd($request->all());
        $key = decrypt($key);
        $type = decrypt($type);
        $tour = $webcontents->tour ?? [];
        if(!array_key_exists($key, $tour[$type])) abort(404);
        $tour = $tour[$type][$key]['title'];
        deleteFile($tour[$type][$key]['image']);
        unset($tour[$type][$key]);
        
        $removed = $webcontents->update([
            'tour' => $tour,
        ]);
        if($removed) {
            $message = $tour .' was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#tour')->with('success', $message);
        }
    }
    public function destroyTourMain(Request $request){
        $webcontents = WebContent::all()->firstOrFail();
        // dd($request->all());
        $validated = $request->validate([
            'rtr.*' =>  ['required'],
        ]);
        $tour = $webcontents->tour ?? [];
        foreach($validated['rtr'] as $key => $item){
            $tourMain = decrypt($key);
            if(array_key_exists($tourMain , $tour['mainTour'])){
                deleteFile($tour['mainTour'][$tourMain]['image']);
                unset($tour['mainTour'][$tourMain]);
            }
            else{
                return redirect()->route('system.webcontent.home', '#hero')->with('error', 'Main Tour Images does not exist');
            }
        }
        $removed = $webcontents->update([
            'tour' => $tour,
        ]);
        if($removed) {
            $message = count($validated['rtr']) . ' Main Tour you selected was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#tour')->with('success', $message);
        }
    }
    public function destroyTourSide(Request $request){
        $webcontents = WebContent::all()->firstOrFail();
        $validated = $request->validate([
            'rts.*' =>  ['required'],
        ]);
        $tour = $webcontents->tour ?? [];
        foreach($validated['rts'] as $key => $item){
            $tourKey = decrypt($key);
            if(array_key_exists($tourKey , $tour['sideTour'])){
                deleteFile($tour['sideTour'][$tourKey]['image']);
                unset($tour['sideTour'][$tourKey]);
            }
            else{
                return redirect()->route('system.webcontent.home', '#tour')->with('error', 'Side Tour Pictures does not exist');
            }
        }
        $removed = $webcontents->update([
            'tour' => $tour,
        ]);
        if($removed) {
            $message = count($validated['rts']) . ' Side Tour selected was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#tour')->with('success', $message);
        }
    }
    public function createContact(Request $request){
        $webcontents = WebContent::all()->first();
        return view('system.webcontent.contact.create', ['activeSb' => 'Website Content', 'contacts' => $webcontents->contact ?? []]);
    }
    public function storeContact(Request $request){
        // dd($request->all());
        $validated = $request->validate([
            'person' => ['required'],
            'contact_no' => ['required'],
            'email' => ['required', 'email'],
            'facebook_link' => ['required', 'url'],
            'whatsapp' => ['required'],
        ], [
            'person.required' => 'Required (Name of Person)',
            'contact_no.required' => 'Required (Contact No.)',
            'email.required' => 'Required (Email)',
            'facebook_link.required' => 'Required (Facebook)',
            'facebook_link.url' => 'This information does not URL.',
            'whatsapp.required' => 'Required (WhatsApp)',
        ]);
        $webcontents = WebContent::all()->firstOrFail();
        $contacts = $webcontents->contact ?? [];
        $contacts['other'][Str::camel($validated['person'])]['name'] = $validated['person'];
        $contacts['other'][Str::camel($validated['person'])]['contactno'][] = $validated['contact_no'];
        $contacts['other'][Str::camel($validated['person'])]['email'][] = $validated['email'];
        $contacts['other'][Str::camel($validated['person'])]['fbuser'][] = $validated['facebook_link'];
        $contacts['other'][Str::camel($validated['person'])]['whatsapp'][] = $validated['whatsapp'];
        
        if(isset($webcontents)) $save = $webcontents->update(['contact' => $contacts]);
        else $save = WebContent::create(['contact' => $contacts,  'operation' => true]);
        if($save) {
            $message = 'Contact of '.$validated['person'].'was added';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#contact')->with('success', $message);
        }
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
        if($save) {
            $message = 'Main Contact was added';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#contact')->with('success', $message);
        }
    }
    public function updateMainContact(Request $request){
        $webcontents = WebContent::all()->firstOrFail();
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
        if($save) {
            $message = 'Main Contact was updated';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#contact')->with('success', $message);
        }
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
        $message = $contact['other'][$key]['name'] . ' was updated';
        if($request->has('contact')){
            $validate = Validator::make($request->input(), [
                'contact' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            ], ['contact.required' => 'Contact Required']);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($contact['other'][$key]['contactno'] as $contactno){
                if($validate['contact'] == $contactno) return back()->with('error', 'The Contact No. already Same ('.$validate['contact'].')')->withInput($validate);
            }
            $contact['other'][$key]['contactno'][] = $validate['contact'];
            $message =  $contact['other'][$key]['name'] . ' Contact No. was Added';

        }
        if($request->has('email')){
            $validate = Validator::make($request->input(), [
                'email' => ['email', 'required'],
            ], ['email.required' => 'Email Required']);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($contact['other'][$key]['email'] as $email){
                if($validate['email'] === $email) return back()->with('error', 'The Email already Same ('.$validate['email'].')')->withInput($validate);
            }
            $contact['other'][$key]['email'][] = $validate['email'];
            $message =  $contact['other'][$key]['name'] . ' Email Address was Added';
        }
        if($request->has('facebook_link')){
            $validate = Validator::make($request->input(), [
                'facebook_link' => ['required', 'url'],
            ], ['facebook_link.required' => 'Link Required', 'facebook_link.url' => 'Facebook Link does not URL']);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($contact['other'][$key]['fbuser'] as $fbuser){
                if($validate['facebook_link'] === $fbuser) return back()->with('error', 'The Facebook Link already Same')->withInput($validate);
            }
            $contact['other'][$key]['fbuser'][] = $validate['facebook_link'];
            $message =  $contact['other'][$key]['name'] . ' Facebook Link was Added';

        }
        if($request->has('whatsapp')){
            $validate = Validator::make($request->input(), [
                'whatsapp' => ['required', 'numeric'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            foreach($contact['other'][$key]['whatsapp'] as $whatsapp){
                if($validate['whatsapp'] === $whatsapp) return back()->with('error', 'The WhatsApp Number already Same ('.$validate['whatsapp'].')')->withInput($validate);
            }
            $contact['other'][$key]['whatsapp'][] = $validate['whatsapp'];
            $message =  $contact['other'][$key]['name'] . ' WhatsApp Contact No. was Added';
        }
        if($request->has('name')){
            $validate = Validator::make($request->input(), [
                'name' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            if($contact['other'][$key]['name'] === $validate['name']) return back()->with('error', 'The Name already Same ('.$validate['name'].')')->withInput($validate);
            $contact['other'][$key]['name'] = $validate['name'];
            $message =  $contact['other'][$key]['name'] . ' was updated the name';
        }
        if($webcontents->update(['contact' => $contact])) {
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.contact.show', encrypt($key))->with('success', $message);
        }
    }
    public function destroyContactOne(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->first();
        if(!array_key_exists($key, $webcontents->contact['other'])) abort(404);
        $contact = $webcontents->contact ?? [];
        $message = $contact['other'][$key]['name'] . ' was remove some information';

        if($request->has('rcontact')){
            $validate = Validator::make($request->input(), [
                'rcontact' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $message =  $contact['other'][$key]['name'] . ' was remove some contact no.';
            foreach($validate['rcontact'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact['other'][$key]['contactno'][$id])) unset($contact['other'][$key]['contactno'][$id]);
            }
        }
        if($request->has('remail')){
            $validate = Validator::make($request->input(), [
                'remail' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $message =  $contact['other'][$key]['name'] . ' was remove some email';
            foreach($validate['remail'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact['other'][$key]['email'][$id])) unset($contact['other'][$key]['email'][$id]);
            }
        }
        if($request->has('rfb')){
            $validate = Validator::make($request->input(), [
                'rfb' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $message =  $contact['other'][$key]['name'] . ' was remove some item facebook user';
            foreach($validate['rfb'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact['other'][$key]['fbuser'][$id])) unset($contact['other'][$key]['fbuser'][$id]);
            }
        }
        if($request->has('rwapp')){
            $validate = Validator::make($request->input(), [
                'rwapp' => ['required'],
            ]);
            if($validate->fails()) return back()->with('error', $validate->errors()->all())->withInput($validate->getData());
            $validate = $validate->validate();
            $message =  $contact['other'][$key]['name'] . ' was remove some WhatsApp Contact No';
            foreach($validate['rwapp'] as $id => $item){
                $id = decrypt($id);
                if(isset($contact['other'][$key]['whatsapp'][$id])) unset($contact[$key]['whatsapp'][$id]);
            }
        }
        if($webcontents->update(['contact' => $contact])) {
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.contact.show', encrypt($key))->with('success', $message);
        }
    }
    public function destroyContact(Request $request){
        $webcontents = WebContent::all()->firstOrFail();
        dd($request->all());
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
        if($removed) {
            $message = count($validated['remove_contact']) . ' Contact Information selected was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#contact')->with('success', $message);
        }
    }
    public function storeOperations(Request $request){
        // dd( $request->all());
        $webcontents = WebContent::all()->first();
        if(!$request->has('operation')){
            $validated = Validator::make($request->all(), [
                'from' => ['required', 'date',  'date_format:Y-m-d', 'after_or_equal:'.Carbon::now()->format('Y-m-d')],
                'to' => ['required', 'date',  'date_format:Y-m-d', 'after_or_equal:'.$request['from']],
                'reason' => ['required'],
            ], [
                'date' => 'Requires Date Only',
                'from.required' => 'The Start Date are Required',
                'to.required' => 'The End Date are Required',
            ]);
            if($validated->fails()) return redirect()->route('system.webcontent.home', '#reservation')->withErrors($validated->errors());
            $validated = $validated->validate();
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
        if($updated) {
            $message = 'Reservation Operation was updated';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#reservation')->with('success', $message);
        }

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
            'image' =>  ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5024'],
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
        if($updated) {
            $message = 'Gcash Payment Reference was created';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }

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
        $webcontents = WebContent::all()->firstOrFail();
        if(!array_key_exists($key, $webcontents->payment['gcash'] ?? [])) abort(404);
        $validate = Validator::make($request->all(), [
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();
        if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
        $validate = $request->validate([
            'gcash_number' => ['required', 'numeric', 'min:10'],            
            'name' => ['required'],
            'image_clear' =>  ['required'],
            'image' =>  Rule::when($request->has('image_clear') && (bool)$request['image_clear'] == 1, ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5024']),
        ]);
        $payments = $webcontents->payment ?? [];
        $payments['gcash'][$key] =  [
            'name' => $validate['name'],
            'number' => $validate['gcash_number'],
            'qrcode' => $payments['gcash'][$key]['qrcode'],
            'priority' => $payments['gcash'][$key]['priority'],
        ];
        if($request->hasFile('image')){       
            if(isset($payments['gcash'][$key]['qrcode'])) deleteFile($payments['gcash'][$key]['qrcode'], 'private');
            $validate['image'] = saveImageWithJPG($request, 'image', 'ref_gcash', 'private');
            $payments['gcash'][$key]['qrcode'] = $validate['image'];
        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) {
            $message = 'Gcash Payment Reference ('.$validate['name'].') was updated';
            $this->employeeLogNotif($message);
            return redirect()->back()->with('success', $message);
        }

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function destroyPaymentGcash(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->firstOrFail();
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
                if(isset($payments['gcash'][$key]['qrcode'])) deleteFile($payments['gcash'][$key]['qrcode'], 'private');
                unset($payments['gcash'][$key]);
            }
        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) {
            $message = 'Gcash Reference of '.$name.' was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }

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
            'image' =>  ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5024'],
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
        if($updated) {
            $message = 'PayPal Reference was created';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }

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
        $webcontents = WebContent::all()->firstOrFail();
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
            'image_clear' =>  ['required'],
            'image' => Rule::when($request->has('image_clear') && (bool)$request['image_clear'] == 1, ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5024']),
        ]);
        $payments = $webcontents->payment ?? [];
        $payments['paypal'][$key] =  [
            'name' => $validate['name'],
            'number' => $validate['paypal_number'],
            'email' => $validate['email'],
            'username' => $validate['username'],
            'image' => $payments['paypal'][$key]['image'],
            'priority' => $payments['paypal'][$key]['priority'],

        ];
        if($request->hasFile('image')){                          // storage/app/logos
            $validate['image'] = saveImageWithJPG($request, 'image', 'ref_paypal', 'private');
            if(isset($payments['paypal'][$key]['image'])) deleteFile($payments['paypal'][$key]['image'], 'private');
            $payments['paypal'][$key]['image'] = $validate['image'];
        }

        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) {
            $message = 'PayPal Reference ('.$validate['name'].') was updated';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
    public function destroyPaymentPayPal(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->firstOrFail();
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
                if(isset($payments['paypal'][$key]['image'])) deleteFile($payments['paypal'][$key]['image'], 'private');
                unset($payments['paypal'][$key]);
            }
        }
        if(isset($webcontents)){
            $updated = $webcontents->update(['payment' => $payments]);
        }
        else{
            $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        }
        if($updated) {
            $message = 'PayPal Reference of '.$name.' was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }

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
        $webcontents = WebContent::all()->firstOrFail();
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
        if($updated) {
            $message = 'Gcash Reference ('.$payments['gcash'][$key]['name'].') was set priority';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }
    }
    public function priorityPaymentPayPal(Request $request){
        // dd(decrypt($request->all('priority')['priority']));
        $validate = Validator::make($request->all(), [
            'priority' => ['required'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate =$validate->validate();
        $key = decrypt($validate['priority']);
        $webcontents = WebContent::all()->firstOrFail();
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
        if($updated) {
            $message = 'PayPal Reference ('.$payments['paypal'][$key]['name'].') was set priority';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }
    }
    public function priorityPaymentBT(Request $request){
        $validate = Validator::make($request->all(), [
            'priority' => ['required'],
        ]);
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate =$validate->validate();
        $key = decrypt($validate['priority']);
        $webcontents = WebContent::all()->firstOrFail();
        $payments = $webcontents->payment ?? [];
        if(!array_key_exists($key, $webcontents->payment['bankTransfer'] ?? [])) abort(404);
            foreach($payments['bankTransfer'] as $btID => $item){
                if($btID === $key) $payments['bankTransfer'][$btID]['priority'] = true;
                else $payments['bankTransfer'][$btID]['priority'] = false;
            }
        
        if(isset($webcontents)) $updated = $webcontents->update(['payment' => $payments]);
        else $updated = WebContent::create(['payment' => $payments, 'operation' => true]);
        if($updated) {
            $message = 'PayPal Payment Reference ('.$payments['bankTransfer'][$key]['name'].') was set priority';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }
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
        if($updated) {
            $message = 'Bank Transfer Reference was created';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }

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
        $webcontents = WebContent::all()->firstOrFail();
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
        
        if($updated) {
            $message = 'Bank Transfer Reference ('.$validate['name'].') was updated';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }
    }
    public function destroyPaymentBT(Request $request, $key){
        $key = decrypt($key);
        $webcontents = WebContent::all()->firstOrFail();
        if(!array_key_exists($key, $webcontents->payment['bankTransfer'] ?? [])) abort(404);
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
        
        if($updated) {
            $message = 'Bank Transfer Reference of '.$name.' was removed';
            $this->employeeLogNotif($message);
            return redirect()->route('system.webcontent.home', '#payment')->with('success', $message);
        }

        // return view('system.webcontent.payment.gcash', ['activeSb' => 'Website Content']);
    }
}
