<?php

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
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SystemHomeController;
use App\Http\Controllers\WebContentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomSettingController;
use App\Http\Controllers\TourSettingController;
use App\Http\Controllers\MyReservationController;
use App\Http\Controllers\CreateReservationController;
use App\Http\Controllers\EditReservationController;
use App\Http\Controllers\EditUserReserveController;
use App\Http\Controllers\ReservationTwoController;
use App\Http\Controllers\SystemReservationController;
use App\Http\Controllers\SystemReservationTwoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!

Cancel and Reschedule disapprove are need to check
|
*/

Auth::routes();

Route::middleware(['auth.image'])->group(function () {
    Route::get('/private/{folder}/{filename}', [HomeController::class,'showImage'])->name('private.image');
});
// Route::middleware(['auth.dbBackup'])->group(function () {
//     Route::get('/private/backup/{filename}', [HomeController::class,'accessBackup'])->name('private.image');
// });
// Route::get('/bot/getUpdates',[LandingController::class, 'teleUpdates'])->name('home');
Route::controller(LandingController::class)->middleware(['session.delete'])->group(function (){
    Route::get('/', 'index')->name('home');
    // Route::get('/testing', 'testing')->name('home');
    Route::get('/tour', 'services')->name('services');
    Route::get('/aboutus', 'aboutus')->name('about.us');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/reservation/demo', 'demo')->name('reservation.demo');
    Route::get('/term-and-conditions', 'termConditions')->name('term-conditions');

});

Route::prefix('reservation')->name('reservation.')->middleware(['session.delete'])->group(function (){
    Route::get('/date', [ReservationController::class, 'date'])->name('date');
    Route::post('/date', [ReservationController::class, 'dateCheck'])->name('date.check');
    Route::post('/date/check', [ReservationController::class, 'dateStore'])->name('date.check.store');
});


// Route::middleware(['guest:web'])->group(function(){
Route::middleware(['guest:web'])->middleware(['session.delete'])->group(function(){
    Route::get('/login', [UserController::class, 'login'])->name('login');
    Route::get('/register', [UserController::class, 'register'])->name('register');
    Route::post('/register', [UserController::class, 'create'])->name('create');
    Route::get('/register/verify', [UserController::class, 'verify'])->name('register.verify');
    Route::get('/register/verify/resend', [UserController::class, 'resend'])->name('register.verify.resend');
    Route::post('/register/verify/store', [UserController::class, 'verifyStore'])->name('register.verify.store');
    Route::post('/check', [UserController::class, 'check'])->name('check');
    Route::get('/forgot-password', [UserController::class, 'forgotPass'])->name('forgot.password');
    
    Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('google.callback');

    Route::get('/auth/facebook', [LoginController::class, 'redirectToFacebook'])->name('facebook.redirect');
    Route::get('/auth/facebook/callback', [LoginController::class, 'handleFacebookCallback'])->name('facebook.callback');

    Route::get('/auth/google/fillup', [UserController::class, 'fillupGoogle'])->name('google.fillup');
    Route::post('/auth/google/fillup/update', [UserController::class, 'fillupGoogleUpdate'])->name('google.fillup.store');

    Route::get('/auth/facebook/fillup', [UserController::class, 'fillupFacebook'])->name('facebook.fillup');
    Route::post('/auth/facebook/fillup/update', [UserController::class, 'fillupFacebookUpdate'])->name('facebook.fillup.store');
    Route::get('/auth/facebook/fillup/verify', [UserController::class, 'fillupFacebookVerify'])->name('facebook.verify');
    Route::post('/auth/facebook/fillup/verify/update', [UserController::class, 'fillupFacebookStore'])->name('facebook.verify.store');
    

});

// Route::middleware(['auth:web', 'preventBackhHistory'])->group(function(){
Route::middleware(['auth:web', 'preventBackhHistory', 'session.delete'])->controller(UserController::class)->group(function(){
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/notifications', [UserController::class, 'notifications'])->name('user.notifications');
    Route::get('/notifications/mark-as-read', [UserController::class, 'userMarkReads'])->name('user.notifications.mark-as-read');
    Route::delete('/notifications/{id}/delete', [UserController::class, 'deleteOneNotif'])->name('user.notifications.destroy');

    Route::prefix('profile')->name('profile.')->group(function (){
        Route::get('/', 'index')->name('home');
        Route::put('/{id}/update/avatar', 'updateAvatar')->name('update.avatar');
        Route::put('/{id}/update/user/info', 'updateUserInfo')->name('update.user.info');
        Route::get('/{id}/update/user/info/email/verify', 'emailVerify')->name('update.user.info.email.verify');
        Route::get('/{id}/update/user/info/email/resend', 'resendUpdateEmail')->name('update.user.info.email.resend');
        Route::put('/{id}/update/user/info/email/verified', 'emailVerified')->name('update.user.info.email.verified');
        Route::put('/{id}/update/password', 'updatePassword')->name('update.password');
        Route::put('/{id}/update/valid-id', 'updateValidID')->name('update.validid');
        Route::delete('/{id}/delete/account', 'destroyAccount')->name('destroy.account');
        Route::get('/{id}/delete/code', 'sendCode')->name('destroy.send.code');
        Route::delete('/{id}/delete/code', 'destroyAccCode')->name('destroy.account.code');
    });

    Route::prefix('my-reservation')->name('user.reservation.')->controller(MyReservationController::class)->group(function (){
        Route::get('/','index')->name('home');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}/cancel', 'cancel')->name('cancel');
        Route::get('/{id}/show', 'show')->name('show');
        Route::put('/{id}/reschedule', 'reschedule')->name('reschedule');
        Route::put('/{id}/details/update', 'updateDetails')->name('update.details');
        Route::get('/{id}/tour/edit', 'editTour')->name('edit.tour');
        Route::put('/{id}/tour/update', 'updateTour')->name('update.tour');
        Route::put('/{id}/request/update', 'updateRequest')->name('update.request');
        Route::put('/{id}/cance/update', 'updateCancel')->name('update.cancel');
        Route::put('/{id}/reschedule/update', 'updateReschedule')->name('update.reschedule');
        Route::get('/{id}/show/online-payment', 'showOnlinePayment')->name('show.online.payment');
        Route::delete('/{id}/reservation/delete', 'destroyReservation')->name('destroy');
        
    });
    Route::prefix('reservation/{id}/edit')->name('user.reservation.edit.')->controller(EditUserReserveController::class)->group(function (){
        Route::get('/step1', 'step1')->name('step1');
        Route::post('/step1/store', 'storeStep1')->name('step1.store');
        Route::get('/step2', 'step2')->name('step2');
        Route::post('/step2-1/store', 'storeStep21')->name('step21.store');
        Route::post('/step2-2/store', 'storeStep22')->name('step22.store');
        Route::get('/step3', 'step3')->name('step3');
        Route::put('/update', 'update')->name('update');
    });
    // Reservation Information
    Route::prefix('reservation')->name('reservation.')->group(function (){
        Route::controller(ReservationController::class)->group(function (){
            Route::get('/choose', 'choose')->name('choose');
            Route::post('/choose/check', 'chooseCheckAll')->name('choose.check.all');

            Route::post('/choose/check/one', 'chooseCheck1')->name('choose.check.one');
            Route::get('/details', 'details')->name('details');
            Route::post('/details', 'detailsStore')->name('details.store');
            Route::put('/details/user/{id}/update', 'detailsUpdate')->name('details.update');

            Route::get('/confimation', 'confirmation')->name('confirmation');
            Route::get('/confimation/tour/{id}/destroy', 'destroyTour')->name('tour.destroy');
            Route::post('/store', 'storeReservation')->name('store');
            Route::get('{id}/done', 'done')->name('done');
            Route::post('/done/{id}/message/store', 'storeMessage')->name('done.message.store');
        });

        Route::controller(ReservationTwoController::class)->group(function (){
            Route::get('/{id}/gcash', 'gcash')->name('gcash');
            Route::get('/{id}/paypal', 'paypal')->name('paypal');
            Route::get('/{id}/bank-transfer', 'bankTransfer')->name('bnktr');
            Route::post('/{id}/payment', 'paymentStore')->name('payment.store');
            Route::get('/{id}/payment/done', 'donePayment')->name('payment.done');
            
            Route::get('/{id}/feedback', 'feedback')->name('feedback');
            Route::post('/{id}/feedback', 'storeFeedback')->name('feedback.store');
        });
    });
});

//For System Users Auth (System Panel)
Route::prefix('system')->name('system.')->middleware(['session.delete'])->group(function(){
    Route::middleware(['guest:system'])->group(function(){
       Route::get('/login', [SystemController::class, 'login'])->name('login');
       Route::post('/check', [SystemController::class, 'check'])->name('check');
    });
    Route::middleware(['auth:system','preventBackhHistory'])->group(function(){
        Route::post('/logout', [SystemController::class, 'logout'])->name('logout');
        Route::get('/notifications', [SystemController::class, 'notifications'])->name('notifications');
        Route::get('/notifications/mark-as-read', [SystemController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::delete('/notifications/{id}/delete', [SystemController::class, 'deleteOneNotif'])->name('notifications.delete');

        Route::get('/', [SystemHomeController::class, 'index'])->name('home');

        Route::prefix('reservation')->name('reservation.')->group(function(){

            Route::controller(CreateReservationController::class)->group(function (){
                Route::get('/create/step1', 'create')->name('create');
                Route::get('/create/step2', 'step1')->name('create.step.one');
                Route::post('/create/step1', 'storeStep0')->name('store.step.zero');
                Route::post('/create/step2', 'storeStep1')->name('store.step.one');
                Route::get('/create/step3', 'step2')->name('create.step.two');
                Route::post('/create/step3-1', 'storeStep21')->name('store.step.two-one');
                Route::post('/create/step3-2', 'storeStep22')->name('store.step.two-two');
                Route::get('/create/step4', 'step3')->name('create.step.three');
                Route::post('/create/step4', 'storeStep3')->name('store.step.three');
                Route::get('/create/step5', 'step4')->name('create.step.four');
                Route::get('/create/step5/search', 'step4Search')->name('create.step.three.search');
                Route::post('/create/step5', 'storeStep4')->name('store.step.four');
                Route::get('/create/step6', 'step5')->name('create.step.five');
                Route::post('/create/step6', 'storeStep5')->name('store.step.five');
            });
            Route::controller(EditReservationController::class)->group(function (){

                Route::get('/{id}/edit/step1', 'step1')->name('edit.step1');
                Route::post('/{id}/edit/step1', 'storeStep1')->name('edit.step1.store');
                Route::get('/{id}/edit/step2', 'step2')->name('edit.step2');
                Route::post('/{id}/edit/step2', 'storeStep2')->name('edit.step2.store');
                Route::get('/{id}/edit/step3', 'step3')->name('edit.step3');

                Route::post('/{id}/edit/step3', 'storeStep3')->name('edit.step3.store');
                Route::get('/{id}/edit/step4', 'step4')->name('edit.step4');

                Route::put('/{id}/edit/step4', 'storeStep4')->name('edit.step4.update');


                Route::get('/{id}/edit/customer', 'customer')->name('edit.customer');
                Route::put('/{id}/edit/customer', 'updateCustomer')->name('edit.customer.update');

                Route::get('/{id}/edit/rooms', 'rooms')->name('edit.rooms');
                Route::put('/{id}/edit/rooms', 'updateRooms')->name('edit.rooms.update');

                Route::get('/{id}/edit/services', 'services')->name('edit.tour');
                Route::put('/{id}/edit/services', 'updateServices')->name('edit.addons.update');

                Route::get('/{id}/edit/payment', 'payment')->name('edit.payment');
                Route::put('/{id}/edit/payment/downpayment', 'updateDY')->name('update.downpayment');
                Route::put('/{id}/edit/payment/cinpayment', 'updateCINP')->name('update.cinpayment');

            });
            Route::controller(SystemReservationController::class)->group(function (){
                Route::get('/', 'index')->name('home');
                Route::get('/search', 'search')->name('search');
                Route::get('/calendar', 'event')->name('event');
                Route::get('/{id}/show', 'show')->name('show');
                Route::get('/{id}/show/cancel', 'showCancel')->name('show.cancel');
                Route::get('/{id}/show/reschedule', 'showReschedule')->name('show.reschedule');
                Route::put('/{id}/show/cancel/approve', 'updateCancel')->name('update.cancel');
                Route::put('/{id}/show/cancel/disaprove', 'updateDisaproveCancel')->name('update.cancel.disaprove');
                Route::get('/{id}/show/cancel/force', 'forceCancel')->name('force.cancel');
                Route::get('/{id}/show/reschedule/force', 'forceReschedule')->name('force.reschedule');
                Route::put('/{id}/show/cancel/force', 'updateForceCancel')->name('force.cancel.update');
                Route::put('/{id}/show/reschedule/force', 'updateForceReschedule')->name('force.reschedule.update');
                Route::put('/{id}/show/reschedule/approve', 'updateReschedule')->name('reschedule.update');
            });
            Route::controller(SystemReservationTwoController::class)->group(function (){
                Route::put('/{id}/show/reschedule/disaprove', 'updateDisaproveReschedule')->name('update.reschedule.disaprove');
                Route::get('/{id}/show/room', 'showRooms')->name('show.rooms');
                Route::put('/{id}/show/room', 'updateReservation')->name('show.rooms.update');
                Route::put('/{id}/show/checkin', 'updateCheckin')->name('show.checkin');
                Route::put('/{id}/show/checkout', 'updateCheckout')->name('show.checkout');
                Route::get('/{id}/disaprove', 'disaprove')->name('disaprove');
                Route::post('/{id}/disaprove', 'disaproveStore')->name('disaprove.store');
                Route::get('/{id}/show/online-payment', 'showOnlinePayment')->name('show.online.payment');
                Route::post('/{id}/online-payment/approve', 'storeOnlinePayment')->name('online.payment.store');
                Route::post('/{id}/online-payment/disapprove', 'disaproveOnlinePayment')->name('online.payment.disaprove');
                Route::put('/{id}/online-payment/force-payment', 'storeForcePayment')->name('online.payment.forcepayment.update');
                Route::get('/{id}/show/addons', 'showAddons')->name('show.addons');
                Route::put('/{id}/show/addons/update', 'updateAddons')->name('addons.update');
                Route::get('/{id}/show/extend', 'showExtend')->name('show.extend');
                Route::put('/{id}/show/extend/update', 'updateExtend')->name('extend.update');

                Route::put('/{id}/{key}/used/update', 'updateUsed')->name('update.used');
                Route::put('/{id}/{key}/{created}/used/update', 'updateUsed2')->name('update.used.twop');


            });

        });
        Route::prefix('analytics')->name('analytics.')->group(function (){
            Route::get('/', [AnalyticsController::class, 'index'])->name('home');
        });
        
        Route::prefix('rooms')->name('rooms.')->controller(RoomController::class)->group(function (){
            Route::get('/', 'index')->name('home');
            Route::post('/', 'search')->name('search');
        });

        Route::prefix('menu')->name('menu.')->controller(TourMenuController::class)->group(function (){
            Route::get('/', 'index')->name('home');

            Route::prefix('addons')->name('addons.')->group(function (){
                Route::get('/create', 'createAddons')->name('create');
                Route::get('/search', 'searchAddons')->name('search');
                Route::post('/create', 'storeAddons')->name('store');
                Route::get('/{id}/edit', 'editAddons')->name('edit');
                Route::put('/{id}/update', 'updateAddons')->name('update');
                Route::delete('/{id}/delete', 'destroyAddons')->name('destroy');
            });
            Route::get('/create', 'create')->name('create');
            Route::post('/create', 'store')->name('store');
            Route::get('/create/price-details', 'priceDetails')->name('price.details');

            Route::post('/create/next', 'createNext')->name('next');
            Route::post('/create/replace', 'replace')->name('replace');

            Route::get('/{id}', 'show')->name('show');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}/update', 'update')->name('update');

            Route::get('/{id}/price/{priceid}', 'editPrice')->name('edit.price');
            Route::put('/{id}/price/{priceid}', 'updatePrice')->name('update.price');
            Route::delete('/{id}/price/{priceid}', 'destroyPrice')->name('destroy.price');
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
        Route::prefix('feedback')->name('feedback.')->controller(FeedbackController::class)->group(function (){
            Route::get('/', 'index')->name('home');
            Route::get('/search', 'search')->name('search');
        });
        Route::prefix('webcontent')->name('webcontent.')->controller(WebContentController::class)->group(function (){
            Route::get('/', 'index')->name('home');

            Route::post('/image/hero', 'storeHero')->name('image.hero');
            Route::put('/image/hero/update', 'updateHero')->name('image.hero.update');
            Route::delete('/image/hero/delete', 'destroyHero')->name('image.hero.destroy.all');

            Route::post('/image/gallery', 'storeGallery')->name('image.gallery');
            Route::delete('/image/gallery/delete', 'destroyGallery')->name('image.gallery.destroy.all');

            Route::post('/tour', 'storeTour')->name('image.tour');
            // Route::put('/image/tour/update', 'updateTour')->name('image.tour.update');
            // Route::delete('/image/tour/delete', 'destroyTour')->name('image.tour.destroy.all');

            Route::put('/reservation/operation', 'storeOperations')->name('reservation.operation');



            Route::get('/image/gallery/{key}/show', 'showGallery')->name('image.gallery.show');
            Route::put('/image/gallery/{key}/update', 'updateGallery')->name('image.gallery.update');
            Route::delete('/image/gallery/{key}/delete', 'destroyGalleryOne')->name('image.gallery.destroy.one');

            Route::get('/image/hero/{key}/show', 'showHero')->name('image.hero.show');
            Route::put('/image/hero/{key}/update', 'updateHero')->name('image.hero.update');
            Route::delete('/image/hero/{key}/delete', 'destroyHeroOne')->name('image.hero.destroy.one');

            Route::get('/tour/{type}/{key}/show', 'showTour')->name('image.tour.show');
            Route::get('/tour/{type}/{key}/update', 'updateTour')->name('image.tour.update');
            Route::delete('/tour/{type}/{key}/delete', 'destroyTourOne')->name('image.tour.destroy.one');

            Route::delete('/image/tour/main/delete', 'destroyTourMain')->name('image.tour.destroy.main.all');
            Route::delete('/image/tour/side/delete', 'destroyTourSide')->name('image.tour.destroy.side.all');


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
            Route::put('/payment/bank-transfer/priority', 'priorityPaymentBT')->name('priority.payment.bnktr');

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

            Route::get('/payment/bank-transfer', 'createPaymentBT')->name('create.payment.bnktr');
            Route::post('/payment/bank-transfer/store', 'storePaymentBT')->name('store.payment.bnktr');
            Route::get('/payment/bank-transfer/{key}', 'showPaymentBT')->name('show.payment.bnktr');
            Route::get('/payment/bank-transfer/{key}/edit', 'editPaymentBT')->name('edit.payment.bnktr');
            Route::put('/payment/bank-transfer/{key}/update', 'updatePaymentBT')->name('update.payment.bnktr');
            Route::delete('/payment/bank-transfer/{key}/delete', 'destroyPaymentBT')->name('destroy.payment.bnktr');




        });
        Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function(){
            Route::get('/', 'index')->name('home');
            Route::get('/edit', 'edit')->name('edit');
            Route::put('/{id}/update/avatar', 'updateAvatar')->name('update.avatar');
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
                Route::get('/search', 'search')->name('search');
                Route::get('/create', 'create')->name('create');
                Route::post('/create/store', 'store')->name('create.store');
    
                Route::get('/{id}', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::get('/{id}/access-control', 'accessControl')->name('access.control');
                Route::put('/{id}/update', 'update')->name('update');
                Route::delete('/{id}/delete', 'destroy')->name('destroy');
            });

            Route::prefix('activity-log')->name('audit.')->controller(AuditLogController::class)->middleware('can:admin')->group(function (){
                Route::get('/', 'index')->name('home');
                Route::post('/search', 'search')->name('search');
                Route::get('/report', 'report')->name('report');
                // Route::get('/create', 'create')->name('create');
                // Route::post('/create/store', 'store')->name('create.store');
    
                // Route::get('/{id}', 'show')->name('show');
                // Route::get('/{id}/edit', 'edit')->name('edit');
                // Route::put('/{id}/update', 'update')->name('update');
                // Route::delete('/{id}/delete', 'destroy')->name('destroy');
            });

            Route::prefix('rooms')->name('rooms.')->controller(RoomSettingController::class)->middleware('can:admin')->group(function(){
                Route::get('/','index')->name('home');

                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                
                // Room Rate Setting
                Route::get('/rate/create', 'createRate')->name('rate.create');
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

        });

    });  

});
Route::get('reservation/{id}/receipt', [MyReservationController::class, 'receipt'])->middleware(['session.delete'])->name('reservation.receipt');

