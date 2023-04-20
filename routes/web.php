<?php

use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\PlanController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Route::get('test', function () {
//     Artisan::call('migrate:fresh');
// });
// Route::get('migrate', function(){
//      Artisan::call('migrate:fresh');
//     return 'Database Migration Completed';
// });
// Route::get('clear', function(){
//     // Artisan::call('migrate:fresh');
//     Artisan::call('cache:clear');
//     return 'Application cache has been cleared';
// });

Route::view('/', 'auth.login')->name('login')->middleware('guest');
Route::view('login', 'auth.login')->name('login')->middleware('guest');

Route::view('register', 'auth.register')->name('register')->middleware('guest');

Route::middleware('is_admin')->group(function () {

    Route::get('dashboard/home', [DashboardController::class, 'index']);
    Route::resource('plans', PlanController::class);
    Route::get('results-stats-country', [DashboardController::class, 'results_stats_country']);
    Route::get('results-stats-region', [DashboardController::class, 'results_stats_region']);
    Route::get('results-stats-period', [DashboardController::class, 'results_stats_period']);
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::get('dashboard', [DashboardController::class, 'index']);
Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('auth/google/call-back', [GoogleAuthController::class, 'callbackGoogle']);
