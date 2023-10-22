<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class NewsController extends Controller
{
    private $system_user;
    public function __construct()
    {
        $this->system_user = auth('system');
        $this->middleware(function ($request, $next){
            if(!($this->system_user->user()->type === 0)) abort(404);
            return $next($request);
        });
    }
    public function index(Request $request){
        $news = News::news()->get();
        if($request->has('tab') && $request['tab'] === 'announcement') $news = News::announcements()->get();
        return view('system.news.index',  ['activeSb' => 'News', 'news' => $news]);
    }
    public function create(){
        return view('system.news.create',  ['activeSb' => 'News']);
    }
    public function store(Request $request){
        $validated = $request->validate([
            'passcode' => ['required', 'numeric', 'digits:4'],
            'title' => ['required'],
            'description' => ['required'],
            'deadline' => ['required'],
            'date_from' => Rule::when($request['deadline'] === "limit", ['required', 'date', 'date_format:Y-m-d']),
            'date_to' => Rule::when($request['deadline'] === "limit", ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['date_from']]),
            'image' => ['nullable' ,'image', 'mimes:jpeg,png,jpg', 'max:5024'], 
        ], [
            'required' => 'Required Input',
            'image' => 'The file must be an image of type: jpeg, png, jpg',
            'mimes' => 'The image must be of type: jpeg, png, jpg',
            'max' => 'The image size must not exceed 5 MB',
        ]);
        if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($validated);
        if($request->hasFile('image')){  
            $validated['image'] = saveImageWithJPG($request, 'image', 'news', 'public');
        }
        if($validated['deadline'] === 'limit'){
            $created = News::create([
                'type' => 0,
                'image' => $validated['image'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'from' => $validated['date_from'],
                'to' => $validated['date_to'],
            ]);
        }
        else{
            $created = News::create([
                'type' => 0,
                'image' => $validated['image'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);
        }
        if($created) return redirect()->route('system.news.home')->with('success', $created->title . ' News was Added');
    }
    public function show($id){
        $new = News::findOrFail(decrypt($id));
        return view('system.news.show',  ['activeSb' => 'News', 'new' => $new]);

    }
    public function edit($id){
        $new = News::findOrFail(decrypt($id));
        return view('system.news.edit',  ['activeSb' => 'News', 'new' => $new]);
    }
    public function update(Request $request, $id){
        $new = News::findOrFail(decrypt($id));
        $validated = $request->validate([
            'passcode' => ['required', 'numeric', 'digits:4'],
            'title' => ['required'],
            'description' => ['required'],
            'deadline' => ['required'],
            'date_from' => Rule::when($request['deadline'] === "limit", ['required', 'date', 'date_format:Y-m-d']),
            'date_to' => Rule::when($request['deadline'] === "limit", ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['date_from']]),
            'image' => ['nullable' ,'image', 'mimes:jpeg,png,jpg', 'max:5024'], 
            'image_clear' => ['required', 'boolean'], 
        ], [
            'required' => 'Required Input',
            'image' => 'The file must be an image of type: jpeg, png, jpg',
            'mimes' => 'The image must be of type: jpeg, png, jpg',
            'max' => 'The image size must not exceed 5 MB',
        ]);
        if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($validated);
        if($request->hasFile('image')){  
            if($new->image) deleteFile($new->image);
            $validated['image'] = saveImageWithJPG($request, 'image', 'news', 'public');
        }
        if((bool)$validated['image_clear'] === true){  
            deleteFile($new->image);
            $new->image = null;
            $new->save();
        }
        if($validated['deadline'] === 'limit'){
            $updated = $new->update([
                'image' => $validated['image'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'from' => $validated['date_from'],
                'to' => $validated['date_to'],
            ]);
        }
        else{
            $updated = $new->update([
                'image' => $validated['image'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'from' => null,
                'to' => null,
            ]);
        }
        if($updated) return redirect()->route('system.news.home')->with('success', $new->title . ' News was Update');
    }
    public function destroy(Request $request, $id){
        $new = News::findOrFail(decrypt($id));
        $validated = $request->validate([
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($validated);
        deleteFile($new->image);
        $deleted = $new->delete();
        if($deleted) return redirect()->route('system.news.home')->with('success', $new->title . ' News was Removed');

    }
    public function createAnnouncement(){
        return view('system.news.announcement.create',  ['activeSb' => 'News']);
    }
    public function storeAnnouncement(Request $request){
        $validated = $request->validate([
            'passcode' => ['required', 'numeric', 'digits:4'],
            'title' => ['required'],
            'deadline' => ['required'],
            'date_from' => Rule::when($request['deadline'] === "limit", ['required', 'date', 'date_format:Y-m-d']),
            'date_to' => Rule::when($request['deadline'] === "limit", ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['date_from']]),
        ], [
            'required' => 'Required Input',

        ]);
        if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($validated);
        if($validated['deadline'] === 'limit'){
            $created = News::create([
                'type' => 1,
                'title' => $validated['title'],
                'from' => $validated['date_from'],
                'to' => $validated['date_to'],
            ]);
        }
        else{
            $created = News::create([
                'type' => 1,
                'title' => $validated['title'],
            ]);
        }
        if($created) return redirect()->route('system.news.home', 'tab=announcement')->with('success', $created->title . ' Announcement was Added');
    }
    public function showAnnouncement($id){
        $new = News::findOrFail(decrypt($id));
        return view('system.news.announcement.show',  ['activeSb' => 'News', 'new' => $new]);

    }
    public function editAnnouncement($id){
        $new = News::findOrFail(decrypt($id));
        return view('system.news.announcement.edit',  ['activeSb' => 'News', 'new' => $new]);
    }
    public function updateAnnouncement(Request $request, $id){
        $new = News::findOrFail(decrypt($id));
        if(str_contains($request['date_from'], 'to')){
            $dateSeperate = explode('to', $request['date_from']);
            $request['date_from'] = trim($dateSeperate[0]);
            $request['date_to'] = trim ($dateSeperate[1]);
        }
        // Check out convertion word to date format
        if(str_contains($request['date_to'], ', ')){
            $date = Carbon::createFromFormat('F j, Y', $request['date_to']);
            $request['date_to'] = $date->format('Y-m-d');
        }
        $validated = $request->validate([
            'passcode' => ['required', 'numeric', 'digits:4'],
            'title' => ['required'],
            'deadline' => ['required'],
            'date_from' => Rule::when($request['deadline'] === "limit", ['required', 'date', 'date_format:Y-m-d']),
            'date_to' => Rule::when($request['deadline'] === "limit", ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:'.$request['date_from']]),
        ], [
            'required' => 'Required Input',
        ]);
        if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($validated);
        if($validated['deadline'] === 'limit'){
            $updated = $new->update([
                'title' => $validated['title'],
                'from' => $validated['date_from'],
                'to' => $validated['date_to'],
            ]);
        }
        else{
            $updated = $new->update([
                'title' => $validated['title'],
                'from' => null,
                'to' => null,
            ]);
        }
        if($updated) return redirect()->route('system.news.home', 'tab=announcement')->with('success', $new->title . ' Announcement was Update');
    }
    public function destroyAnnouncement(Request $request, $id){
        $new = News::findOrFail(decrypt($id));
        $validated = $request->validate([
            'passcode' => ['required', 'numeric', 'digits:4'],
        ]);
        if(!Hash::check($validated['passcode'], $this->system_user->user()->passcode)) return back()->with('error', 'Invalid Passcode')->withInput($validated);
        $deleted = $new->delete();
        if($deleted) return redirect()->route('system.news.home', 'tab=announcement')->with('success', $new->title . ' Announcement was Removed');

    }
    
}
