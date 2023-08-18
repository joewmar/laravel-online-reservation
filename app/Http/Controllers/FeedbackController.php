<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(){
        $feedbacks = Feedback::all();
        return view('system.feedback.index',  ['activeSb' => 'Feedback', 'feedbacks' => $feedbacks]);
    }
}
