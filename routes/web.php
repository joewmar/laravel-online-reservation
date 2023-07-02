<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RideController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TourMenuController;
use App\Http\Controllers\RoomSettingController;
use App\Http\Controllers\TourSettingController;

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
Auth::routes();


Route::get('/', function () {
    return view('index', ['activeNav' => 'Home']);
})->name('home');

Route::get('/about', function () {
    return view('landing.about_us', ['activeNav' => 'About Us']);
})->name('about.us');

Route::prefix('reservation')->name('reservation.')->group(function (){
    Route::get('/date', [ReservationController::class, 'date'])->name('date');
    Route::post('/date', [ReservationController::class, 'dateCheck'])->name('date.check');
    Route::post('/date/check', [ReservationController::class, 'dateStore'])->name('date.check.store');
});

// Route::middleware(['guest:web'])->group(function(){
Route::middleware(['guest'])->group(function(){
    Route::view('/login', 'users.login')->name('login');
    Route::view('/register', 'users.register')->name('register');
    Route::post('/create', [UserController::class, 'create'])->name('create');
    Route::post('/check', [UserController::class, 'check'])->name('check');
});

// Route::middleware(['auth:web', 'prevent-back-history'])->group(function(){
Route::middleware(['auth', 'prevent-back-history'])->group(function(){
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::view('/profile', 'home')->name('profile');

    // Reservation Information
    Route::prefix('reservation')->name('reservation.')->group(function (){
        Route::get('/choose', [ReservationController::class, 'choose'])->name('choose');
        // Route::post('/choose/check', [ReservationController::class, 'chooseCheckAll'])->name('choose.check.all');

        Route::post('/choose/check/one', [ReservationController::class, 'chooseCheck1'])->name('choose.check.one');
    });
});

//For System Users Auth (System Panel)
Route::prefix('system')->name('system.')->group(function(){
    Route::middleware(['guest:system'])->group(function(){
       Route::view('/login', 'system.login')->name('login');
       Route::post('/check', [SystemController::class, 'check'])->name('check');
    });
    Route::middleware(['auth:system', 'prevent-back-history'])->group(function(){
        Route::view('/', 'system.dashboard.index',  ['activeSb' => 'Home'])->name('home');
        Route::view('/reservation', 'system.reservation.index',  ['activeSb' => 'Reservation'])->name('reservation');
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');

        Route::prefix('menu')->name('menu.')->group(function (){
            Route::get('/', [TourMenuController::class, 'index'])->name('home');
            Route::get('/create', [TourMenuController::class, 'create'])->name('create');
            Route::post('/create', [TourMenuController::class, 'store'])->name('store');

            Route::post('/create/replace', [TourMenuController::class, 'replace'])->name('replace');

            Route::get('/{id}', [TourMenuController::class, 'show'])->name('show');
            Route::delete('/{id}', [TourMenuController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/edit', [TourMenuController::class, 'edit'])->name('edit');
            Route::put('/{id}/update', [TourMenuController::class, 'update'])->name('update');
        });

        Route::view('/analytics', 'system.analytics.index',  ['activeSb' => 'Analytics'])->name('analytics');
        Route::view('/news', 'system.news.index',  ['activeSb' => 'News'])->name('news');
        Route::view('/feedback', 'system.feedback.index',  ['activeSb' => 'Feedback'])->name('feedback');
        Route::view('/webcontent', 'system.webcontent.index',  ['activeSb' => 'Web Content'])->name('webcontent');

        // System Settings Moudle
        Route::prefix('setting')->name('setting.')->group(function(){
            Route::view('/', 'system.setting.index',  ['activeSb' => 'Setting'])->name('home');
            // Route::view('/accounts', 'system.setting.accounts',  ['activeSb' => 'Accounts'])->name('accounts');


            Route::prefix('rooms')->name('rooms.')->group(function(){
                Route::get('/', [RoomSettingController::class, 'index'])->name('home');

                Route::view('/create', 'system.setting.rooms.create',  ['activeSb' => 'Rooms'])->name('create');
                Route::post('/store', [RoomSettingController::class, 'store'])->name('store');
                
                // Room Rate Setting
                Route::view('/rate/create', 'system.setting.rooms.rate.create',  ['activeSb' => 'Rooms'])->name('rate.create');
                Route::post('/rate', [RoomSettingController::class, 'storeRate'])->name('rate.store');

                // Room rate Setting ID's
                Route::delete('/rate/{id}' , [RoomSettingController::class, 'destroyRate'])->name('rate.destroy');
                Route::get('/rate/{id}/edit' , [RoomSettingController::class, 'editRate'])->name('rate.edit');
                Route::put('/rate/{id}/edit' , [RoomSettingController::class, 'updateRate'])->name('rate.update');

                
                Route::get('/{id}' , [RoomSettingController::class, 'show'])->name('show');
                Route::delete('/{id}' , [RoomSettingController::class, 'destroy'])->name('destroy');
                Route::get('/{id}/edit' , [RoomSettingController::class, 'edit'])->name('edit');
                Route::put('/{id}/edit' , [RoomSettingController::class, 'update'])->name('update');

            });

            // Ride Setting
            Route::prefix('rides')->name('rides.')->group(function(){
                Route::get('/', [RideController::class, 'index'])->name('home');
                Route::view('/create', 'system.setting.rides.create',  ['activeSb' => 'Rides'])->name('create');
                Route::post('/create', [RideController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [RideController::class, 'edit'])->name('edit');
                Route::put('/{id}/edit', [RideController::class, 'update'])->name('update');
                Route::delete('/{id}/edit', [RideController::class, 'destroy'])->name('destroy');

            });

                    // Tour Module
            Route::prefix('tour')->name('tour.')->group(function(){
                Route::get('/', [TourSettingController::class, 'index'])->name('home');
                Route::view('/create', 'system.setting.tour.create',  ['activeSb' => 'Hello'])->name('create');
                Route::post('/store', [TourSettingController::class, 'store'])->name('store');
                Route::delete('/{id}', [TourSettingController::class, 'destroy'])->name('destroy');
                Route::get('/{id}/edit', [TourSettingController::class, 'edit'])->name('edit');
                Route::put('/{id}/edit', [TourSettingController::class, 'update'])->name('update');
            });

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