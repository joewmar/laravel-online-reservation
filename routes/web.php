<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/', function () {
    return view('index', ['activeNav' => 'Home']);
});
Route::get('/aboutus', function () {
    return view('landing.about_us', ['activeNav' => 'About Us']);
});
Route::get('/login', function () {
    return view('users.login', ['activeNav' => 'About Us']);
});
Route::get('/register', function () {
    return view('users.register', ['activeNav' => 'About Us']);
});


// System
Route::get('/system/', function () {
    return view('system.dashboard.index', ['activeSb' => 'Home']);
});
Route::get('/system/reservation', function () {
    return view('system.reservation.index', ['activeSb' => 'Reservation']);
});
Route::get('/system/rooms', function () {
    return view('system.rooms.index', ['activeSb' => 'Rooms']);
});
Route::get('/system/tour', function () {
    return view('system.tour.index', ['activeSb' => 'Tour']);
});
Route::get('/system/analytics', function () {
    return view('system.analytics.index', ['activeSb' => 'Analytics']);
});
Route::get('/system/news', function () {
    return view('system.news.index', ['activeSb' => 'News']);
});
Route::get('/system/feedback', function () {
    return view('system.feedback.index', ['activeSb' => 'Feedback']);
});
Route::get('/system/webcontent', function () {
    return view('system.webcontent.index', ['activeSb' => 'Website Content']);
});
Route::get('/setting', function () {
    return view('system.setting.index', ['activeSb' => 'None']);
});
Route::get('/setting/accounts', function () {
    return view('system.setting.accounts.index', ['activeSb' => 'None']);
});
Route::get('/setting/rooms', function () {
    return view('system.setting.rooms.index', ['activeSb' => 'None']);
});
Route::get('/setting/rides', function () {
    return view('system.setting.rides.index', ['activeSb' => 'None']);
});
Route::get('/profile', function () {
    return view('system.profile.index', ['activeSb' => 'None']);
});
Route::get('/profile/edit', function () {
    return view('system.profile.edit', ['activeSb' => 'None']);
});
Route::get('/profile/link', function () {
    return view('system.profile.link', ['activeSb' => 'None']);
});
Route::get('/profile/password', function () {
    return view('system.profile.password', ['activeSb' => 'None']);
});
