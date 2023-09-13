<?php

use App\Models\System;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
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
use App\Http\Controllers\MyReservationController;
use App\Http\Controllers\CreateReservationController;
use App\Http\Controllers\SystemReservationController;
use App\Notifications\SystemNotification;

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

Auth::routes();

Route::middleware(['auth.image'])->group(function () {
    Route::get('/private/{folder}/{filename}', [HomeController::class,'showImage'])->name('private.image');
});
// Route::get('/bot/getUpdates',[LandingController::class, 'teleUpdates'])->name('home');
Route::controller(LandingController::class)->group(function (){
    Route::get('/', 'index')->name('home');
    Route::get('/tour', 'services')->name('services');
    Route::get('/aboutus', 'aboutus')->name('about.us');
    Route::get('/contact', 'contact')->name('contact');
    Route::view('/reservation/demo', ['landing.demo'])->name('reservation.demo');
});

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

    Route::get('/auth/facebook/fillup', [UserController::class, 'fillupFacebook'])->name('facebook.fillup');
    Route::post('/auth/facebook/fillup/update', [UserController::class, 'fillupFacebookUpdate'])->name('facebook.fillup.store');
    

});

// Route::middleware(['auth:web', 'preventBackhHistory'])->group(function(){
Route::middleware(['auth:web', 'preventBackhHistory'])->controller(UserController::class)->group(function(){
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::prefix('profile')->name('profile.')->group(function (){
        Route::get('/', 'index')->name('home');
        Route::put('/{id}/update/avatar', 'updateAvatar')->name('update.avatar');
        Route::put('/{id}/update/user/info', 'updateUserInfo')->name('update.user.info');
        Route::put('/{id}/update/password', 'updatePassword')->name('update.password');
        Route::put('/{id}/update/valid-id', 'updateValidID')->name('update.validid');
    });

    Route::prefix('my-reservation')->name('user.reservation.')->controller(MyReservationController::class)->group(function (){
        Route::get('/','index')->name('home');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}/cancel', 'cancel')->name('cancel');
        Route::get('/{id}/show', 'show')->name('show');
        Route::put('/{id}/reschedule', 'reschedule')->name('reschedule');
    });
    // Reservation Information
    Route::prefix('reservation')->name('reservation.')->controller(ReservationController::class)->group(function (){
        Route::get('/choose', 'choose')->name('choose');
        Route::post('/choose/check', 'chooseCheckAll')->name('choose.check.all');

        Route::post('/choose/check/one', 'chooseCheck1')->name('choose.check.one');
        Route::get('/details', 'details')->name('details');
        Route::post('/details', 'detailsStore')->name('details.store');
        Route::put('/details/user/{id}/update', 'detailsUpdate')->name('details.update');

        Route::get('/confimation', 'confirmation')->name('confirmation');
        Route::post('/confimation/convert', 'convert')->name('convert');
        Route::get('/confimation/tour/{id}/destroy', 'destroyTour')->name('tour.destroy');
        Route::post('/store', 'storeReservation')->name('store');
        Route::get('{id}/done', 'done')->name('done');
        Route::post('/done/{id}/message/store', 'storeMessage')->name('done.message.store');

        Route::get('/{id}/gcash', 'gcash')->name('gcash');
        Route::get('/{id}/gcash/done', 'doneGcash')->name('gcash.done');
        Route::get('/{id}/paypal', 'paypal')->name('paypal');
        Route::get('/{id}/paypal/done', 'donePayPal')->name('paypal.done');
        Route::post('/{id}/payment', 'paymentStore')->name('payment.store');
        
        Route::get('/{id}/feedback', 'feedback')->name('feedback');
        Route::post('/{id}/feedback', 'storeFeedback')->name('feedback.store');
    });
});

//For System Users Auth (System Panel)
Route::prefix('system')->name('system.')->group(function(){
    Route::middleware(['guest:system'])->group(function(){
       Route::view('/login', 'system.login')->name('login');
       Route::post('/check', [SystemController::class, 'check'])->name('check');
    });
    Route::middleware(['auth:system','preventBackhHistory'])->group(function(){
        Route::post('/logout', [SystemController::class, 'logout'])->name('logout');
        Route::get('/notifications', [SystemController::class, 'notifications'])->name('notifications');
        Route::get('/notifications/mark-as-read', [SystemController::class, 'markAsRead'])->name('notifications.mark-as-read');

        Route::get('/', [SystemHomeController::class, 'index'])->name('home');
        // Route::get('/', function(){
        //     System::find(Auth::user()->id)->notify(new SystemNotification('NOtification successful;l'));
        //     dd('Good sa notification');
        // })->name('home');
        Route::prefix('reservation')->name('reservation.')->group(function(){

            Route::controller(CreateReservationController::class)->group(function (){
                Route::get('/create/step1', 'create')->name('create');
                Route::post('/create/step1', 'storeStep1')->name('store.step.one');
                Route::get('/create/step2', 'step2')->name('create.step.two');
                Route::post('/create/step2-1', 'storeStep21')->name('store.step.two-one');
                Route::post('/create/step2-2', 'storeStep22')->name('store.step.two-two');
                Route::get('/create/step3', 'step3')->name('create.step.three');
                Route::post('/create/step3', 'storeStep3')->name('store.step.three');
                Route::get('/create/step4', 'step4')->name('create.step.four');
                Route::post('/create/step4', 'storeStep4')->name('store.step.four');
            });

            Route::controller(SystemReservationController::class)->group(function (){
                Route::get('/', 'index')->name('home');
                Route::post('/search', 'search')->name('search');
                Route::get('/calendar', 'event')->name('event');
                Route::get('/{id}/show', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}/update', 'updateRInfo')->name('update');
                Route::get('/{id}/show/extend', 'showExtend')->name('show.extend');
                Route::get('/{id}/show/addons', 'showAddons')->name('show.addons');
                Route::put('/{id}/show/addons/update', 'updateAddons')->name('addons.update');
                Route::put('/{id}/show/extend/update', 'updateExtend')->name('extend.update');
                Route::get('/{id}/show/online-payment', 'showOnlinePayment')->name('show.online.payment');
                Route::post('/{id}/online-payment/create', 'storeOnlinePayment')->name('online.payment.store');
                Route::post('/{id}/online-payment/disaprove', 'disaproveOnlinePayment')->name('online.payment.disaprove');
                Route::put('/{id}/online-payment/force-payment', 'storeForcePayment')->name('online.payment.forcepayment.update');
                Route::get('/{id}/show/room', 'showRooms')->name('show.rooms');
                Route::get('/{id}/disaprove', 'disaprove')->name('disaprove');
                Route::post('/{id}/disaprove', 'disaproveStore')->name('disaprove.store');
                Route::put('/{id}/show/room', 'updateReservation')->name('show.rooms.update');
                Route::put('/{id}/show/checkin', 'updateCheckin')->name('show.checkin');
                Route::put('/{id}/show/checkout', 'updateCheckout')->name('show.checkout');
                Route::get('/{id}/show/cancel', 'showCancel')->name('show.cancel');
                Route::get('/{id}/show/reschedule', 'showReschedule')->name('show.reschedule');
                Route::put('/{id}/show/cancel/approve', 'updateCancel')->name('update.cancel');
                Route::put('/{id}/show/cancel/disaprove', 'updateDisaproveCancel')->name('update.cancel.disaprove');
                Route::put('/{id}/show/reschedule/disaprove', 'updateDisaproveReschedule')->name('update.reschedule.disaprove');
                Route::get('/{id}/show/reschedule/approve', 'approveReschedule')->name('reschedule.approve');
                Route::put('/{id}/show/reschedule/approve', 'updateReschedule')->name('reschedule.update');
            });

        });
        Route::prefix('analytics')->name('analytics.')->group(function (){
            Route::get('/', [AnalyticsController::class, 'index'])->name('home');
        });
        
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.home');

        Route::prefix('menu')->name('menu.')->middleware('can:admin')->group(function (){
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

        Route::prefix('news')->name('news.')->controller(NewsController::class)->middleware('can:admin')->group(function (){
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
        Route::prefix('webcontent')->name('webcontent.')->controller(WebContentController::class)->middleware('can:admin')->group(function (){
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
            Route::post('/contact/main/create', 'storeMainContact')->name('main.contact.store');
            Route::put('/contact', 'updateContact')->name('contact.update');
            Route::put('/contact/main', 'updateMainContact')->name('main.contact.update');
            Route::delete('/contact', 'destroyContact')->name('contact.destroy.all');

            Route::get('/contact/{key}/', 'showContact')->name('contact.show');
            Route::put('/contact/{key}/update', 'updateContact')->name('contact.update');
            Route::delete('/contact/{key}/delete', 'destroyContactOne')->name('contact.destroy.one');

            Route::get('/payment/gcash', 'createPaymentGcash')->name('create.payment.gcash');
            Route::post('/payment/gcash', 'storePaymentGcash')->name('store.payment.gcash');
            Route::put('/payment/gcash/priority', 'priorityPaymentGcash')->name('priority.payment.gcash');
            Route::put('/payment/paypal/priority', 'priorityPaymentPayPal')->name('priority.payment.paypal');

            Route::get('/payment/gcash/{key}', 'showPaymentGcash')->name('show.payment.gcash');
            Route::get('/payment/gcash/{key}/edit', 'editPaymentGcash')->name('edit.payment.gcash');
            Route::put('/payment/gcash/{key}/update', 'updatePaymentGcash')->name('update.payment.gcash');
            Route::delete('/payment/gcash/{key}/delete', 'destroyPaymentGcash')->name('destroy.payment.gcash');

            Route::get('/payment/paypal', 'createPaymentPayPal')->name('create.payment.paypal');
            Route::post('/payment/paypal', 'storePaymentPayPal')->name('store.payment.paypal');

            Route::get('/payment/paypal/{key}', 'showPaymentPayPal')->name('show.payment.paypal');
            Route::get('/payment/paypal/{key}/edit', 'editPaymentPayPal')->name('edit.payment.paypal');
            Route::put('/payment/paypal/{key}/update', 'updatePaymentPayPal')->name('update.payment.paypal');
            Route::delete('/payment/paypal/{key}/delete', 'destroyPaymentPayPal')->name('destroy.payment.paypal');

        });
        Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function(){
            Route::get('/', 'index')->name('home');
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
            Route::view('/', 'system.setting.index',  ['activeSb' => 'Setting'])->middleware('can:admin')->name('home');
            Route::prefix('accounts')->name('accounts.')->controller(SystemController::class)->middleware('can:admin')->group(function (){
                Route::get('/', 'index')->name('home');
                Route::post('/search', 'search')->name('search');
                Route::get('/create', 'create')->name('create');
                Route::post('/create/store', 'store')->name('create.store');
    
                Route::get('/{id}', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}/update', 'update')->name('update');
                Route::delete('/{id}/delete', 'destroy')->name('destroy');
            });


        Route::prefix('rooms')->name('rooms.')->controller(RoomSettingController::class)->middleware('can:admin')->group(function(){
                Route::get('/','index')->name('home');

                Route::get('/create', 'create')->name('create');
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

});

Route::get('reservation/{id}/receipt', [MyReservationController::class, 'receipt'])->name('reservation.receipt');
