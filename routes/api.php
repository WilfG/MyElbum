<?php

use App\Http\Controllers\API\frameContentsAPIController;
use App\Http\Controllers\API\framesAPIController;
use App\Http\Controllers\API\plansAPIController;
use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\API\VerificationController;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::resources([
    'plans' => plansAPIController::class,
    'frames' => framesAPIController::class,
    'frame_contents' => frameContentsAPIController::class,
]);


Route::middleware('auth:sanctum', 'verified')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Login and Register(includes email verification sent but email validation not yet working)
 */
Route::controller(AuthAPIController::class)->group(function () {
    Route::post('register', 'register'); // done but email verify not yet ready
    Route::post('login', 'login');
});

Route::post('email/verification-notification', [VerificationController::class, 'sendVerificationEmail'])->middleware('auth:sanctum');
Route::get('verify-email/{id}/{hash}',[VerificationController::class, 'verify'])->name('verification.verify')->middleware('auth:sanctum');

/**
 * Google Sign up and automatically sign in
 */
Route::post('/signup-socialite', [SocialiteController::class, 'handleProviderCallback']);

Route::post('validatePhoneNumber', [AuthAPIController::class, 'validatePhoneNumber']);

