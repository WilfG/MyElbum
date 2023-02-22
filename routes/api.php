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

Route::middleware('auth:sanctum', 'verified')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthAPIController::class, 'login']);
Route::post('register', [AuthAPIController::class, 'register']);

// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');

Route::post('email/verification-notification', [VerificationController::class, 'sendVerificationEmail']);
Route::get('verify-email/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');


Route::post('/login/callback', [SocialiteController::class, 'handleProviderCallback']);

Route::resources([
    'plans' => plansAPIController::class,
    'frames' => framesAPIController::class,
    'frame_contents' => frameContentsAPIController::class,
]);
