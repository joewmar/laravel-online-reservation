<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(){
        $feedbacks = Feedback::latest()->paginate(10);
        return view('system.feedback.index',  ['activeSb' => 'Feedback', 'feedbacks' => $feedbacks]);
    }
    public function search(Request $request){
        $search = $request->input('query');
        $names = [];
        if(!empty($search)){
            $results = Feedback::whereRaw("name LIKE ?", ["%$search%"])->get();
            foreach($results as $list){
                $names[] = [
                    'title' => $list->name(),
                    'link' => route('system.setting.accounts.show', encrypt($list->id)),
                ];
            }
        } 
        return response()->json($names);
    }
}
