<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showImage($folder, $filename)
    {    
        $path = $folder . '/' . $filename;
        
        // Check if the file exists in the private storage
        if (!Storage::disk('private')->exists($path)) {
            abort(404);
        }
    
        // Get the file contents and set appropriate headers
        $file = Storage::disk('private')->get($path);
        $mime = Storage::disk('private')->mimeType($path);
    
        return response($file, 200)->header('Content-Type', $mime);
    }
}
