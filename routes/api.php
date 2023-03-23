<?php

use App\Http\Controllers\API\FrameContentsAPIController;
use App\Http\Controllers\API\FramesAPIController;
use App\Http\Controllers\API\PlansAPIController;
use App\Http\Controllers\API\AuthAPIController;
use App\Http\Controllers\API\CommentsAPIController;
use App\Http\Controllers\API\ContactsAPIController;
use App\Http\Controllers\API\FrameContentCommentsAPIController;
use App\Http\Controllers\API\FrameContentTagsAPIController;
use App\Http\Controllers\API\NotificationsAPIController;
use App\Http\Controllers\API\ReactionsAPIController;
use App\Http\Controllers\API\TagsAPIController;
use App\Http\Controllers\API\UserContactAPIController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\API\VerificationController;
use App\Models\Comment;
use App\Models\FrameContentComment;
use Illuminate\Http\Client\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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
    'plans' => PlansAPIController::class,
    'frames' => FramesAPIController::class,
    'frame_contents' => frameContentsAPIController::class,
    'users_contacts' => UserContactAPIController::class,
    'comments' => CommentsAPIController::class,
    'tags' => TagsAPIController::class,
    'content_comments' => FrameContentCommentsAPIController::class,
    'content_tags' => FrameContentTagsAPIController::class,
    'contacts' => ContactsAPIController::class,
    'reactions' => ReactionsAPIController::class,
    'notifications' => NotificationsAPIController::class,
]);

Route::get('plans/user_plan/{id}', [PlansAPIController::class, 'user_plan']);
Route::get('logout', [AuthAPIController::class, 'logout']);
Route::get('user_frame/{id}', [FramesAPIController::class, 'userFrame']);
Route::get('user_contacts/{id}', [ContactsAPIController::class, 'userContacts']);
Route::get('frame_contents/frame/{id}', [FrameContentsAPIController::class, 'frame_contents']);
Route::post('frame_contents/updateframecontent/{id}', [FrameContentsAPIController::class, 'updateFrameContent']);
Route::post('frame_transfert_verif', [FramesAPIController::class, 'frame_transfert_verif']);
Route::post('transfer_frame', [FramesAPIController::class, 'transfer_frame']);
Route::post('frame_reset', [FramesAPIController::class, 'frame_reset']);
Route::delete('restore_frame/{id}', [FrameContentsAPIController::class, 'restore_frame']);
Route::get('frameComments/{id}', [CommentsAPIController::class, 'frameComments']);
Route::get('frameContentComments/{id}', [FrameContentCommentsAPIController::class, 'frameContentComments']);
Route::get('reactionByFrame/{id}', [ReactionsAPIController::class, 'reactionByFrame']);
Route::get('reactionByFrameContent/{id}', [ReactionsAPIController::class, 'reactionByFrameContent']);
Route::get('reactionBycomment/{id}', [ReactionsAPIController::class, 'reactionBycomment']);
Route::get('reactionByFrameContentComment/{id}', [ReactionsAPIController::class, 'reactionByFrameContentComment']);
Route::get('friend_requests/{user_id}', [UserContactAPIController::class, 'friend_requests']);
Route::post('add_thumbnail_to_frame', [FramesAPIController::class, 'add_thumbnail_to_frame']);

Route::middleware('auth:sanctum', 'verified')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Login and Register(includes email verification sent but email validation not yet working)
 */
Route::controller(AuthAPIController::class)->group(function () {
    Route::post('register', 'register'); // done but email verify not yet ready
    Route::post('login', 'login');
    Route::post('validatePhoneNumber', 'validatePhoneNumber');
    Route::post('updateUser/{user}', 'updateUser');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

 
Route::post('email/verification-notification', [VerificationController::class, 'sendVerificationEmail'])->middleware('auth:sanctum');
Route::get('verify-email/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify')->middleware('auth:sanctum');

/**
 * Google Sign up and automatically sign in
 */
Route::post('/signup-socialite', [SocialiteController::class, 'googleSignup']);
Route::post('/signin-socialite', [SocialiteController::class, 'googleSignin']);

