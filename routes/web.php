<?php

use App\Mail\ReservationMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\TourMenuController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SystemHomeController;
use App\Http\Controllers\WebContentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomSettingController;
use App\Http\Controllers\TourSettingController;
use App\Http\Controllers\CreateReservationController;
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
    Route::post('/register', [UserController::class, 'create'])->name('create');
    Route::get('/register/verify', [UserController::class, 'verify'])->name('register.verify');
    Route::post('/register/verify', [UserController::class, 'verifyStore'])->name('register.verify.store');
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
        Route::get('/confimation/tour/{id}/destroy', [ReservationController::class, 'destroyTour'])->name('tour.destroy');
        Route::post('/store', [ReservationController::class, 'storeReservation'])->name('store');
        Route::get('{id}/done', [ReservationController::class, 'done'])->name('done');
        Route::post('/done/{id}/message/store', [ReservationController::class, 'storeMessage'])->name('done.message.store');

        Route::get('/{id}/gcash', [ReservationController::class, 'gcash'])->name('gcash');
        Route::get('/{id}/gcash/done', [ReservationController::class, 'doneGcash'])->name('gcash.done');
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
    Route::middleware(['auth:system', 'can:admin' ,'preventBackhHistory'])->group(function(){
        Route::post('/logout', [SystemController::class, 'logout'])->name('logout');

        Route::get('/', [SystemHomeController::class, 'index'])->name('home');
        Route::prefix('reservation')->name('reservation.')->group(function(){
            Route::get('/', [SystemReservationController::class, 'index'])->name('home');
            Route::post('/search', [SystemReservationController::class, 'search'])->name('search');
            Route::get('/calendar', [SystemReservationController::class, 'event'])->name('event');
            Route::get('/create/step1', [CreateReservationController::class, 'create'])->name('create');
            Route::post('/create/step1', [CreateReservationController::class, 'storeStep1'])->name('store.step.one');
            Route::get('/create/step2', [CreateReservationController::class, 'step2'])->name('create.step.two');
            Route::post('/create/step2-1', [CreateReservationController::class, 'storeStep21'])->name('store.step.two-one');
            Route::post('/create/step2-2', [CreateReservationController::class, 'storeStep22'])->name('store.step.two-two');
            Route::get('/create/step3', [CreateReservationController::class, 'step3'])->name('create.step.three');
            Route::post('/create/step3', [CreateReservationController::class, 'storeStep3'])->name('store.step.three');
            Route::get('/create/step4', [CreateReservationController::class, 'step4'])->name('create.step.four');
            Route::post('/create/step4', [CreateReservationController::class, 'storeStep4'])->name('store.step.four');

            
            Route::get('/{id}/show', [SystemReservationController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [SystemReservationController::class, 'edit'])->name('edit');
            Route::put('/{id}/update', [SystemReservationController::class, 'updateRInfo'])->name('update');
            Route::get('/{id}/show/extend', [SystemReservationController::class, 'showExtend'])->name('show.extend');
            Route::get('/{id}/show/addons', [SystemReservationController::class, 'showAddons'])->name('show.addons');
            Route::put('/{id}/show/addons/update', [SystemReservationController::class, 'updateAddons'])->name('addons.update');
            Route::put('/{id}/show/extend/update', [SystemReservationController::class, 'updateExtend'])->name('extend.update');
            Route::get('/{id}/show/online-payment', [SystemReservationController::class, 'showOnlinePayment'])->name('show.online.payment');
            Route::post('/{id}/online-payment/create', [SystemReservationController::class, 'storeOnlinePayment'])->name('online.payment.store');
            Route::post('/{id}/online-payment/disaprove', [SystemReservationController::class, 'disaproveOnlinePayment'])->name('online.payment.disaprove');
            Route::put('/{id}/online-payment/force-payment', [SystemReservationController::class, 'storeForcePayment'])->name('online.payment.forcepayment.update');
            Route::get('/{id}/show/room', [SystemReservationController::class, 'showRooms'])->name('show.rooms');
            Route::get('/{id}/disaprove', [SystemReservationController::class, 'disaprove'])->name('disaprove');
            Route::post('/{id}/disaprove', [SystemReservationController::class, 'disaproveStore'])->name('disaprove.store');
            Route::put('/{id}/show/room', [SystemReservationController::class, 'updateReservation'])->name('show.rooms.update');
            Route::put('/{id}/show/checkin', [SystemReservationController::class, 'updateCheckin'])->name('show.checkin');
            Route::put('/{id}/show/checkout', [SystemReservationController::class, 'updateCheckout'])->name('show.checkout');
        });
        Route::prefix('analytics')->name('analytics.')->group(function (){
            Route::get('/', [AnalyticsController::class, 'index'])->name('home');
        });
        
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.home');

        Route::prefix('menu')->name('menu.')->group(function (){
            Route::get('/', [TourMenuController::class, 'index'])->name('home');

            Route::prefix('addons')->name('addons.')->group(function (){
                Route::get('/create', [TourMenuController::class, 'createAddons'])->name('create');
                Route::post('/create', [TourMenuController::class, 'storeAddons'])->name('store');
                Route::get('/{id}/edit', [TourMenuController::class, 'editAddons'])->name('edit');
                Route::put('/{id}/update', [TourMenuController::class, 'updateAddons'])->name('update');
                Route::delete('/{id}/delete', [TourMenuController::class, 'destroyAddons'])->name('destroy');
            });
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

        Route::prefix('news')->name('news.')->controller(NewsController::class)->group(function (){
            Route::get('/', 'index')->name('home');
            Route::get('/create', 'create')->name('create');
            Route::post('/create', 'store')->name('store');

            Route::prefix('announcement')->name('announcement.')->group(function (){
                Route::get('/create', 'createAnnouncement')->name('create');
                Route::post('/create', 'storeAnnouncement')->name('store');

                Route::get('/{id}/edit', 'editAnnouncement')->name('edit');
                Route::get('/{id}/show', 'showAnnouncement')->name('show');
                Route::put('/{id}/update', 'updateAnnouncement')->name('update');
                Route::delete('/{id}/delete', 'destroyAnnouncement')->name('destroy');

            });


            Route::get('/{id}/show', 'show')->name('show');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}/update', 'update')->name('update');
            Route::delete('/{id}/delete', 'destroy')->name('destroy');

        });
        Route::prefix('feedback')->name('feedback.')->group(function (){
            Route::get('/', [FeedbackController::class, 'index'])->name('home');
        });
        Route::prefix('webcontent')->name('webcontent.')->controller(WebContentController::class)->group(function (){
            Route::get('/', 'index')->name('home');

            Route::post('/image/hero', 'storeHero')->name('image.hero');
            Route::put('/image/hero/update', 'updateHero')->name('image.hero.update');
            Route::delete('/image/hero/delete', 'destroyHero')->name('image.hero.destroy.all');

            Route::post('/image/gallery', 'storeGallery')->name('image.gallery');
            Route::delete('/image/gallery/delete', 'destroyGallery')->name('image.gallery.destroy.all');
            Route::put('/reservation/operation', 'storeOperations')->name('reservation.operation');

            Route::get('/image/gallery/{key}/show', 'showGallery')->name('image.gallery.show');
            Route::put('/image/gallery/{key}/update', 'updateGallery')->name('image.gallery.update');
            Route::delete('/image/gallery/{key}/delete', 'destroyGalleryOne')->name('image.gallery.destroy.one');

            Route::get('/image/hero/{key}/show', 'showHero')->name('image.hero.show');
            Route::put('/image/hero/{key}/update', 'updateHero')->name('image.hero.update');
            Route::delete('/image/hero/{key}/delete', 'destroyHeroOne')->name('image.hero.destroy.one');

            Route::get('/contact/create', 'createContact')->name('contact.create');
            Route::post('/contact/create', 'storeContact')->name('contact.store');
            Route::put('/contact', 'updateContact')->name('contact.update');
            Route::delete('/contact', 'destroyContact')->name('contact.destroy.all');

            Route::get('/contact/{key}/', 'showContact')->name('contact.show');
            Route::put('/contact/{key}/update', 'updateContact')->name('contact.update');
            Route::delete('/contact/{key}/delete', 'destroyContactOne')->name('contact.destroy.one');

        });
        Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function(){
            Route::view('/', 'system.profile.index',  ['activeSb' => 'Profile'])->name('home');
            Route::get('/edit', 'edit')->name('edit');
            Route::put('/{id}/update', 'update')->name('update');
            // Route::view('/link', 'system.profile.link',  ['activeSb' => 'Link'])->name('link');
            Route::get('/password', 'password')->name('password');
            Route::put('/password/{id}', 'updatePassword')->name('password.update');
            Route::put('/passcode/{id}', 'updatePasscode')->name('passcode.update');
            // Route::view('/password', 'system.profile.password',  ['activeSb' => 'Password'])->name('password');
        });
        // System Settings Moudle
        Route::prefix('settings')->name('setting.')->group(function(){
            Route::view('/', 'system.setting.index',  ['activeSb' => 'Setting'])->name('home');
            Route::prefix('accounts')->name('accounts.')->controller(SystemController::class)->group(function (){
                Route::get('/', 'index')->name('home');
                Route::post('/search', 'search')->name('search');
                Route::get('/create', 'create')->name('create');
                Route::post('/create', 'store')->name('create.store');
    
                Route::get('/{id}', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}/edit', 'update')->name('update');
                Route::delete('/{id}/delete', 'destroy')->name('destroy');
            });


            Route::prefix('rooms')->name('rooms.')->controller(RoomSettingController::class)->group(function(){
                Route::get('/','index')->name('home');

                Route::view('/create', 'system.setting.rooms.create',  ['activeSb' => 'Rooms'])->name('create');
                Route::post('/store', 'store')->name('store');
                
                // Room Rate Setting
                Route::view('/rate/create', 'system.setting.rooms.rate.create',  ['activeSb' => 'Rooms'])->name('rate.create');
                Route::post('/rate', 'storeRate')->name('rate.store');

                // Room rate Setting ID's
                Route::delete('/rate/{id}' ,  'destroyRate')->name('rate.destroy');
                Route::get('/rate/{id}/edit' , 'editRate')->name('rate.edit');
                Route::put('/rate/{id}/edit' , 'updateRate')->name('rate.update');

                
                Route::get('/{id}' , 'show')->name('show');
                Route::delete('/{id}' , 'destroy')->name('destroy');
                Route::get('/{id}/edit' , 'edit')->name('edit');
                Route::put('/{id}/edit', 'update')->name('update');

            });
            // Route::prefix('tour')->name('tour.')->controller(TourSettingController::class)->group(function(){
            //     Route::get('/', 'index')->name('home');
            //     Route::view('/create', 'system.setting.tour.create',  ['activeSb' => 'Hello'])->name('create');
            //     Route::post('/store', 'store')->name('store');
            //     Route::delete('/{id}', 'destroy')->name('destroy');
            //     Route::get('/{id}/edit', 'edit')->name('edit');
            //     Route::put('/{id}/edit', 'update')->name('update');
            // });

        });

    });  
    // Route::middleware(['auth:system','can:manager', 'preventBackhHistory'])->group(function(){
    //     Route::post('/logout', [SystemController::class, 'logout'])->name('logout');

    //     Route::get('/', [SystemHomeController::class, 'index'])->name('home');
    //     Route::prefix('reservation')->name('reservation.')->group(function(){
    //         Route::get('/', [SystemReservationController::class, 'index'])->name('home');
    //         Route::post('/search', [SystemReservationController::class, 'search'])->name('search');
    //         Route::get('/calendar', [SystemReservationController::class, 'event'])->name('event');
    //         Route::get('/create/step1', [CreateReservationController::class, 'create'])->name('create');
    //         Route::post('/create/step1', [CreateReservationController::class, 'storeStep1'])->name('store.step.one');
    //         Route::get('/create/step2', [CreateReservationController::class, 'step2'])->name('create.step.two');
    //         Route::post('/create/step2-1', [CreateReservationController::class, 'storeStep21'])->name('store.step.two-one');
    //         Route::post('/create/step2-2', [CreateReservationController::class, 'storeStep22'])->name('store.step.two-two');
    //         Route::get('/create/step3', [CreateReservationController::class, 'step3'])->name('create.step.three');
    //         Route::post('/create/step3', [CreateReservationController::class, 'storeStep3'])->name('store.step.three');
    //         Route::get('/create/step4', [CreateReservationController::class, 'step4'])->name('create.step.four');
    //         Route::post('/create/step4', [CreateReservationController::class, 'storeStep4'])->name('store.step.four');

            
    //         Route::get('/{id}/show', [SystemReservationController::class, 'show'])->name('show');
    //         Route::get('/{id}/edit', [SystemReservationController::class, 'edit'])->name('edit');
    //         Route::put('/{id}/update', [SystemReservationController::class, 'updateRInfo'])->name('update');
    //         Route::get('/{id}/show/extend', [SystemReservationController::class, 'showExtend'])->name('show.extend');
    //         Route::get('/{id}/show/addons', [SystemReservationController::class, 'showAddons'])->name('show.addons');
    //         Route::put('/{id}/show/addons/update', [SystemReservationController::class, 'updateAddons'])->name('addons.update');
    //         Route::put('/{id}/show/extend/update', [SystemReservationController::class, 'updateExtend'])->name('extend.update');
    //         Route::get('/{id}/show/online-payment', [SystemReservationController::class, 'showOnlinePayment'])->name('show.online.payment');
    //         Route::post('/{id}/online-payment/create', [SystemReservationController::class, 'storeOnlinePayment'])->name('online.payment.store');
    //         Route::post('/{id}/online-payment/disaprove', [SystemReservationController::class, 'disaproveOnlinePayment'])->name('online.payment.disaprove');
    //         Route::put('/{id}/online-payment/force-payment', [SystemReservationController::class, 'storeForcePayment'])->name('online.payment.forcepayment.update');
    //         Route::get('/{id}/show/room', [SystemReservationController::class, 'showRooms'])->name('show.rooms');
    //         Route::get('/{id}/disaprove', [SystemReservationController::class, 'disaprove'])->name('disaprove');
    //         Route::post('/{id}/disaprove', [SystemReservationController::class, 'disaproveStore'])->name('disaprove.store');
    //         Route::put('/{id}/show/room', [SystemReservationController::class, 'updateReservation'])->name('show.rooms.update');
    //         Route::put('/{id}/show/checkin', [SystemReservationController::class, 'updateCheckin'])->name('show.checkin');
    //         Route::put('/{id}/show/checkout', [SystemReservationController::class, 'updateCheckout'])->name('show.checkout');
    //     });
    //     Route::prefix('analytics')->name('analytics.')->group(function (){
    //         Route::get('/', [AnalyticsController::class, 'index'])->name('home');
    //     });
        
    //     Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.home');


    //     Route::prefix('feedback')->name('feedback.')->group(function (){
    //         Route::get('/', [FeedbackController::class, 'index'])->name('home');
    //     });

    //     Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function(){
    //         Route::view('/', 'system.profile.index',  ['activeSb' => 'Profile'])->name('home');
    //         Route::get('/edit', 'edit')->name('edit');
    //         Route::put('/{id}/update', 'update')->name('update');
    //         // Route::view('/link', 'system.profile.link',  ['activeSb' => 'Link'])->name('link');
    //         Route::get('/password', 'password')->name('password');
    //         Route::put('/password/{id}', 'updatePassword')->name('password.update');
    //         Route::put('/passcode/{id}', 'updatePasscode')->name('passcode.update');
    //         // Route::view('/password', 'system.profile.password',  ['activeSb' => 'Password'])->name('password');
    //     });
    // });
});

Route::get('reservation/{id}/receipt', [SystemReservationController::class, 'receipt'])->name('reservation.receipt');
Route::get('reservation/{id}/feedback', [ReservationController::class, 'feedback'])->name('reservation.feedback');
Route::post('reservation/{id}/feedback', [ReservationController::class, 'storeFeedback'])->name('reservation.feedback.store');

Route::middleware(['auth.image'])->group(function () {
    Route::get('/private/{folder}/{filename}', [HomeController::class,'showImage'])->name('private.image');
});