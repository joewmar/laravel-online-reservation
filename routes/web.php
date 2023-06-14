<?php

use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// 
// Route::get('/', function () {
//     return view('index', ['activeNav' => 'Home']);
// });
// Route::get('/aboutus', function () {
//     return view('landing.about_us', ['activeNav' => 'About Us']);
// });
// Route::get('/login', function () {
//     return view('users.login', ['activeNav' => 'About Us']);
// });
// Route::get('/register', function () {
//     return view('users.register', ['activeNav' => 'About Us']);
// });


Route::get('/', function () {
    return view('index', ['activeNav' => 'Home']);
})->name('home');

Route::get('/about', function () {
    return view('landing.about_us', ['activeNav' => 'About Us']);
})->name('about.us');

Auth::routes();

// Route::middleware(['guest:web'])->group(function(){
Route::middleware(['guest'])->group(function(){
    Route::view('/login', 'users.login')->name('login');
    Route::view('/register', 'users.register')->name('register');
    Route::post('/create', [UserController::class, 'create'])->name('create');
    Route::post('/check', [UserController::class, 'check'])->name('check');
});

// Route::middleware(['auth:web', 'prevent.back.history'])->group(function(){
Route::middleware(['auth', 'prevent.back.history'])->group(function(){
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::view('/profile', 'home')->name('profile');
});

//For System Users Auth (System Panel)
Route::prefix('system')->name('system.')->group(function(){
    Route::middleware(['guest:system'])->group(function(){
       Route::view('/login', 'system.login')->name('login');
       Route::post('/check', [SystemController::class, 'check'])->name('check');
    });
    Route::middleware(['auth:system', 'prevent.back.history'])->group(function(){
        Route::view('/', 'system.dashboard.index',  ['activeSb' => 'Home'])->name('home');
        Route::view('/reservation', 'system.reservation.index',  ['activeSb' => 'Reservation'])->name('reservation');
        Route::view('/rooms', 'system.rooms.index',  ['activeSb' => 'Rooms'])->name('rooms');
        Route::view('/tour', 'system.tour.index',  ['activeSb' => 'Tour'])->name('tour');
        Route::view('/analytics', 'system.analytics.index',  ['activeSb' => 'Analytics'])->name('analytics');
        Route::view('/news', 'system.news.index',  ['activeSb' => 'News'])->name('news');
        Route::view('/feedback', 'system.feedback.index',  ['activeSb' => 'Feedback'])->name('feedback');
        Route::view('/webcontent', 'system.webcontent.index',  ['activeSb' => 'Web Content'])->name('webcontent');

        // System Settings
        Route::prefix('setting')->name('setting.')->group(function(){
            Route::view('/', 'system.setting.index',  ['activeSb' => 'Setting'])->name('home');
            // Route::view('/accounts', 'system.setting.accounts',  ['activeSb' => 'Accounts'])->name('accounts');
            Route::view('/rooms/create', 'system.setting.rooms.create',  ['activeSb' => 'Rooms'])->name('rooms.create');
            Route::post('/rooms/store', [RoomController::class, 'store'])->name('rooms.store');
            Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');

            Route::get('/rooms/{id}' , [RoomController::class, 'show'])->name('rooms.show');
            Route::delete('/rooms/{id}' , [RoomController::class, 'destroy'])->name('rooms.destroy');
            Route::get('/rooms/{id}/edit' , [RoomController::class, 'edit'])->name('rooms.edit');
            Route::put('/rooms/{id}/edit' , [RoomController::class, 'update'])->name('rooms.update');


            // Route::view('/rides', 'system.setting.rides.index',  ['activeSb' => 'Rides'])->name('rides');
        });

        // System Profile
        Route::prefix('profile')->name('system.profile.')->group(function(){
            Route::view('/', 'system.profile.index',  ['activeSb' => 'Profile'])->name('home');
            Route::view('/edit', 'system.profile.edit',  ['activeSb' => 'Edit'])->name('edit');
            Route::view('/link', 'system.profile.link',  ['activeSb' => 'Link'])->name('link');
            Route::view('/password', 'system.profile.password',  ['activeSb' => 'Password'])->name('password');
        });


        Route::post('/logout', [SystemController::class, 'logout'])->name('logout');
    });  
});