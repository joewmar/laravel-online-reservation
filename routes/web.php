<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RideController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\TourMenuController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SystemHomeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomSettingController;
use App\Http\Controllers\TourSettingController;
use App\Http\Controllers\SystemReservationController;

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


// Route::get('/bot/getUpdates',[LandingController::class, 'teleUpdates'])->name('home');
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
Route::middleware(['guest:web'])->group(function(){
    Route::view('/login', 'users.login')->name('login');
    Route::view('/register', 'users.register')->name('register');
    Route::get('/register/verify', [UserController::class, 'verify'])->name('register.verify');
    Route::post('/register/verify', [UserController::class, 'verifyStore'])->name('register.verify.store');
    Route::post('/create', [UserController::class, 'create'])->name('create');
    Route::post('/check', [UserController::class, 'check'])->name('check');
    Route::view('/forgot-password','auth.passwords.email')->name('forgot.password');
    
    Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('google.callback');

    Route::get('/auth/facebook', [LoginController::class, 'redirectToFacebook'])->name('facebook.redirect');
    Route::get('/auth/facebook/callback', [LoginController::class, 'handleFacebookCallback'])->name('facebook.callback');

    Route::get('/auth/google/fillup', [UserController::class, 'fillupGoogle'])->name('google.fillup');
    Route::post('/auth/google/fillup/update', [UserController::class, 'fillupGoogleUpdate'])->name('google.fillup.store');
    

});

// Route::middleware(['auth:web', 'preventBackhHistory'])->group(function(){
Route::middleware(['auth:web', 'preventBackhHistory'])->group(function(){
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::view('/profile', 'home')->name('profile');

    Route::prefix('my-reservation')->name('user.reservation.')->group(function (){
        Route::get('/', [ReservationController::class, 'index'])->name('home');
    });
    // Reservation Information
    Route::prefix('reservation')->name('reservation.')->group(function (){
        Route::get('/choose', [ReservationController::class, 'choose'])->name('choose');
        Route::post('/choose/check', [ReservationController::class, 'chooseCheckAll'])->name('choose.check.all');

        Route::post('/choose/check/one', [ReservationController::class, 'chooseCheck1'])->name('choose.check.one');
        Route::get('/details', [ReservationController::class, 'details'])->name('details');
        Route::post('/details', [ReservationController::class, 'detailsStore'])->name('details.store');
        Route::put('/details/user/{id}/update', [ReservationController::class, 'detailsUpdate'])->name('details.update');

        Route::get('/confimation', [ReservationController::class, 'confirmation'])->name('confirmation');
        Route::post('/confimation/convert', [ReservationController::class, 'convert'])->name('convert');
        Route::post('/store', [ReservationController::class, 'storeReservation'])->name('store');
        Route::get('{id}/done', [ReservationController::class, 'done'])->name('done');
        Route::post('/done/{id}/message/store', [ReservationController::class, 'storeMessage'])->name('done.message.store');

        Route::get('/{id}/gcash', [ReservationController::class, 'gcash'])->name('gcash');
        Route::get('/{id}/paypal', [ReservationController::class, 'paypal'])->name('paypal');
        Route::post('/{id}/payment', [ReservationController::class, 'paymentStore'])->name('payment.store');

    });
});

//For System Users Auth (System Panel)
Route::prefix('system')->name('system.')->group(function(){

    Route::middleware(['guest:system'])->group(function(){
       Route::view('/login', 'system.login')->name('login');
       Route::post('/check', [SystemController::class, 'check'])->name('check');
    });
    Route::middleware(['auth:system', 'preventBackhHistory'])->group(function(){
        Route::post('/logout', [SystemController::class, 'logout'])->name('logout');
        Route::get('/', [SystemHomeController::class, 'index'])->name('home');
        Route::prefix('reservation')->name('reservation.')->group(function(){
            Route::get('/', [SystemReservationController::class, 'index'])->name('home');
            Route::post('/search', [SystemReservationController::class, 'search'])->name('search');
            Route::get('/calendar', [SystemReservationController::class, 'event'])->name('event');
            Route::get('/create', [SystemReservationController::class, 'create'])->name('create');
            Route::post('/create', [SystemReservationController::class, 'storeStep1'])->name('store.step.one');

            
            Route::get('/{id}/show', [SystemReservationController::class, 'show'])->name('show');
            Route::get('/{id}/show/online-payment', [SystemReservationController::class, 'showOnlinePayment'])->name('show.online.payment');
            Route::get('/{id}/show/room', [SystemReservationController::class, 'showRooms'])->name('show.rooms');
            Route::get('/{id}/disaprove', [SystemReservationController::class, 'disaprove'])->name('disaprove');
            Route::post('/{id}/disaprove', [SystemReservationController::class, 'disaproveStore'])->name('disaprove.store');
            Route::put('/{id}/show/room', [SystemReservationController::class, 'updateReservation'])->name('show.rooms.update');
            Route::put('/{id}/show/checkin', [SystemReservationController::class, 'updateCheckin'])->name('show.checkin');
        });
        
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');

        Route::prefix('menu')->name('menu.')->group(function (){
            Route::get('/', [TourMenuController::class, 'index'])->name('home');
            Route::get('/create', [TourMenuController::class, 'create'])->name('create');
            Route::post('/create', [TourMenuController::class, 'store'])->name('store');
            Route::get('/create/price-details', [TourMenuController::class, 'priceDetails'])->name('price.details');

            Route::post('/create/next', [TourMenuController::class, 'createNext'])->name('next');
            Route::post('/create/replace', [TourMenuController::class, 'replace'])->name('replace');

            Route::get('/{id}', [TourMenuController::class, 'show'])->name('show');
            Route::delete('/{id}', [TourMenuController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/edit', [TourMenuController::class, 'edit'])->name('edit');
            Route::put('/{id}/update', [TourMenuController::class, 'update'])->name('update');

            Route::get('/{id}/price/{priceid}', [TourMenuController::class, 'editPrice'])->name('edit.price');
            Route::put('/{id}/price/{priceid}', [TourMenuController::class, 'updatePrice'])->name('update.price');
            Route::delete('/{id}/price/{priceid}', [TourMenuController::class, 'destroyPrice'])->name('destroy.price');
        });

        Route::view('/analytics', 'system.analytics.index',  ['activeSb' => 'Analytics'])->name('analytics');
        Route::view('/news', 'system.news.index',  ['activeSb' => 'News'])->name('news');
        Route::view('/feedback', 'system.feedback.index',  ['activeSb' => 'Feedback'])->name('feedback');
        Route::view('/webcontent', 'system.webcontent.index',  ['activeSb' => 'Web Content'])->name('webcontent');

        // System Settings Moudle
        Route::prefix('settings')->name('setting.')->group(function(){
            Route::view('/', 'system.setting.index',  ['activeSb' => 'Setting'])->name('home');
            Route::get('/accounts', [SystemController::class, 'index'])->name('accounts');
            Route::post('/accounts/search', [SystemController::class, 'search'])->name('accounts.search');
            Route::view('/accounts/create', 'system.setting.accounts.create',  ['activeSb' => 'Setting'])->name('accounts.create');
            Route::post('/accounts/create', [SystemController::class, 'store'])->name('accounts.create.store');


            Route::get('/accounts/{id}', [SystemController::class, 'show'])->name('accounts.show');
            Route::get('/accounts/{id}/edit', [SystemController::class, 'edit'])->name('accounts.edit');
            Route::put('/accounts/{id}/edit', [SystemController::class, 'update'])->name('accounts.update');
            Route::delete('/accounts/{id}', [SystemController::class, 'destroy'])->name('accounts.destroy');


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


    });  
});

Route::get('reservation/{id}/receipt', [SystemReservationController::class, 'receipt'])->name('reservation.receipt');

Route::middleware(['auth.image'])->group(function () {
    Route::get('/private/{folder}/{filename}', [HomeController::class,'showImage'])->name('private.image');
});