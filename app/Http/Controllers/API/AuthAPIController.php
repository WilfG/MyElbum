<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Nette\Utils\Random;
use Twilio\Rest\Client;
// use Stevebauman\Location\Facades\Location;
use Adrianorosa\GeoLocation\GeoLocation;
use App\Models\AccessToken;
use App\Models\Comment;
use App\Models\Frame;
use App\Models\FrameContent;
use App\Models\FrameContentComment;
use App\Models\Setting;
use App\Models\User_session;
use App\Models\User_verification;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuthAPIController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password', 'latitude', 'longitude', 'region', 'picture'), [
                'lastname' => ['nullable', 'min:2', 'max:50', 'string'],
                'firstname' => ['nullable', 'min:2', 'max:50', 'string'],
                'country' => ['nullable', 'string'],
                'phoneNumber' => ['nullable', 'min:8', 'max:15', 'string'],
                'birthDate' => ['nullable', 'string'],
                'username' => ['nullable', 'min:2', 'max:50', 'string'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['nullable', 'min:6', 'max:255', 'string'],
                'latitude' => ['nullable', 'string',],
                'longitude' => ['nullable', 'string',],
                'region' => ['nullable', 'string',],
                'picture' => 'nullable',
                'picture' => 'file|mimes:jpeg,jpg,png,gif,PNG,JPG,JPEG',
            ]);

            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $input = $request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password', 'latitude', 'longitude', 'region');
            // $input['password'] = Hash::make($request['password']);
            $input['isVerified'] = 0;
            // var_dump($input); die;
            $user = User::create($input);

            if ($request->hasFile('picture')) {
                $ext = explode('.', $request->picture->getClientOriginalName())[1];
                $filename  = $user->firstname . '_' . $user->lastname . '_avatar_' . date('Ymd') . '_' . time() . '.' . $ext;
                $path = 'Users_pictures/' . $user->firstname . '_' . $user->lastname;

                $request->picture->move(public_path($path), $filename);
                User::where('id', $user->id)->update([
                    'profil_picture' => $path . '/' . $filename
                ]);
            }

            $verification_code = random_int(100000, 999999);
            DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);

            $subject = 'Please verify your email';
            $name = $user->firstname . ' ' . $user->lastname;
            $email = $request->email;
            Mail::send(
                'email.verify',
                ['name' => $name, 'verification_code' => $verification_code],
                function ($mail) use ($email, $name, $subject) {
                    $mail->from(getenv('MAIL_FROM_ADDRESS'), "MyElbum");
                    $mail->to($email, $name);
                    $mail->subject($subject);
                }
            );

            // event(new Registered($user));

            // $contact = Contact::firstOrNew(['id' => $user->id]);
            // $contact->contact_firstname = $user->firstname;
            // $contact->contact_lastname = $user->lastname;
            // $contact->save();
            Auth::login($user);
            //log user session
            $session_id = Session::getId();
            User_session::create([
                'region' => $request->region,
                'country' => $request->country,
                'session_id' => $session_id,
                'user_id' => $user->id,
            ]);

            $accessToken = AccessToken::updateOrCreate(
                ['user_id' => $user->id],
                ['access_token' => Str::random(191)]
            );

            $settings = Setting::create([
                'user_id' => $user->id,
            ]);
            $data =  [
                // 'token' => $user->createToken('Sanctum+Socialite')->plainTextToken,
                'session_id' => $session_id,
                'token' => $accessToken,
                'user' => $user,
                'settings' => $settings,
                'status' => Auth::check(),
                'message' => 'We send you a verification mail..'
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }
    }

    public function login(Request $request)
    {
        try {
            if (isset($request->email)) {
                $validator = Validator::make($request->only('email', 'password', 'region', 'country'), [
                    'email' => ['required', 'email', 'exists:users,email'],
                    'password' => ['required', 'min:6', 'max:255', 'string'],
                    'region' => ['required', 'string',],
                    'country' => ['required', 'string',],
                ]);
                if ($validator->fails())
                    return response()->json($validator->errors(), 400);


                // if ($position = Location::get()) {
                //     var_dump($position->countryName);
                //     die;
                // }

                if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                    // die;
                    $user = $request->user();
                    Auth::login($user);
                    //log user session
                    $session_id = Session::getId();
                    User_session::create([
                        'region' => $request->region,
                        'country' => $request->country,
                        'session_id' => $session_id,
                        'user_id' => $user->id,
                    ]);

                    $accessToken = AccessToken::updateOrCreate(
                        ['user_id' => $user->id],
                        ['access_token' => Str::random(191)]
                    );

                    $plan = DB::table('souscriptions')
                        ->join('plans', 'souscriptions.plan_id', 'plans.id')
                        ->where('souscriptions.user_id', $user->id)
                        ->select('plans.*')
                        ->get();
                    // var_dump($plan->id); die;
                    $frames = DB::table('frames')
                        ->join('souscriptions', 'frames.plan_id', 'souscriptions.plan_id')
                        ->where('souscriptions.user_id', '=', $user->id)
                        ->select('frames.*')->get()->toArray();

                    foreach ($frames as $frame) {
                        $contents = DB::table('frame_contents')->where('frame_id', $frame->id)->get();
                        $comments = DB::table('comments')->where('frame_id', $frame->id)->get();
                        $tags = DB::table('tags')->where('frame_id', $frame->id)->get();
                        $reactions = DB::table('reactions')->where('frame_id', $frame->id)->get();
                        $frame->contents = $contents;
                        $frame->comments = $comments;
                        $frame->tags = $tags;
                        $frame->reactions = $reactions;

                        //
                        foreach ($frame->contents as $content) {
                            $content_comments = DB::table('frame_content_comments')->where('frame_content_id', $content->id)->get();
                            $content_tags = DB::table('frame_content_tags')->where('frame_content_id', $content->id)->get();
                            $content_reactions = DB::table('reactions')->where('frame_content_id', $content->id)->get();
                            $content->content_comments = $content_comments;
                            $content->content_tags = $content_tags;
                            $content->content_reactions = $content_reactions;
                        }
                    }

                    $friend_requests = DB::table('user_contacts')
                        ->where('user_contacts.user_id', '=', $user->id)
                        ->where('user_contacts.request_status', '=', 'Pending')->get();

                    // $location  = GeoLocation::lookup('192.168.149.100');die;
                    // $latitude = $location->getLatitude();
                    // $longitude = $location->getLongitude();
                    // dd($latitude);
                    $notifications = DB::table('notifications')->where('user_id', $user->id)
                    ->join('contacts', 'notifications.contact_id', 'contacts.id')
                    ->get();
                    foreach ($notifications as $key => $value) {
                        $post = explode('_', $value->post_id);
                        if ($post[0] == 'frame') {
                            $post_frame = Frame::where('id', $post[1])->first();
                            $value->frame = $post_frame;
                        }
                        
                        if ($post[0] == 'frameContent') {
                            $post_frame = FrameContent::where('id', $post[1])->first();
                            $value->frame_content = $post_frame;
                        }
            
                        if ($post[0] == 'frameComment') {
                            $post_frame = Comment::where('id', $post[1])->first();
                            $value->frame_comment = $post_frame;
                        }
                        
                        if ($post[0] == 'contentComment') {
                            $post_frame = FrameContentComment::where('id', $post[1])->first();
                            $value->frame_content_comment = $post_frame;
                        }
                    }

                    $notification_settings = Setting::where('user_id', $user->id)->first();
                    $data =  [
                        // 'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                        'token' => $accessToken,
                        'session_id' => $session_id,
                        'user' => $user,
                        'plan' => $plan,
                        'frames' => $frames,
                        'friend_requests' => $friend_requests,
                        'notifications' => $notifications,
                        'notification_settings' => $notification_settings,
                        'status' => Auth::check(),
                        'message' => 'you are successfully logged in'
                    ];
                    return response()->json($data, 200);
                }else{
                    return response()->json(['error' => 'Incorrect username or password']);
                }
            }elseif (isset($request->phoneNumber)) {
                $validator = Validator::make($request->only('phoneNumber', 'password'), [
                    'phoneNumber' => ['required', 'exists:users,phoneNumber'],
                    'password' => ['required', 'min:6', 'max:255', 'string'],
                ]);
                if ($validator->fails())
                    return response()->json($validator->errors(), 400);

                if (Auth::attempt(['phoneNumber' => $request->phoneNumber, 'password' => $request->password])) {
                    // die;
                    $user = $request->user();
                    Auth::login($user);

                    //log user session
                    $session_id = Session::getId();
                    User_session::create([
                        'region' => $request->region,
                        'country' => $request->country,
                        'session_id' => $session_id,
                        'user_id' => $user->id,
                    ]);

                    $accessToken = AccessToken::updateOrCreate(
                        ['user_id' => $user->id],
                        ['access_token' => Str::random(191)]
                    );

                    $plan = DB::table('souscriptions')
                        ->join('plans', 'souscriptions.plan_id', 'plans.id')
                        ->where('souscriptions.user_id', $user->id)
                        ->select('plans.*')
                        ->get();
                    // var_dump($plan->id); die;
                    $frames = DB::table('frames')
                        ->join('souscriptions', 'frames.plan_id', 'souscriptions.plan_id')
                        ->where('souscriptions.user_id', '=', $user->id)
                        ->select('frames.*')->get();

                    $friend_requests = DB::table('user_contacts')
                        ->where('user_contacts.user_id', '=', $user->id)
                        ->where('user_contacts.request_status', '=', 'Pending')->get();

                    $notifications = DB::table('notifications')->where('user_id', $user->id)->get();

                    $notification_settings = Setting::where('user_id', $user->id)->first();
                    $data =  [
                        //                         'session_id' => $session_id,
                        //                         'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                        //                         'user' => $user,
                        //                         'plan' => $plan,
                        //                         'frames' => $frames,
                        //                         'friend_requests' => $friend_requests,
                        //                         'notifications' => $notifications,
                        //                         'status' => Auth::check(),
                        //                         'message' => 'you are successfully logged in'

                        'token' => $accessToken,
                        'session_id' => $session_id,
                        'user' => $user,
                        'plan' => $plan,
                        'frames' => $frames,
                        'friend_requests' => $friend_requests,
                        'notifications' => $notifications,
                        'notification_settings' => $notification_settings,
                        'status' => Auth::check(),
                        'message' => 'you are successfully logged in'
                    ];
                    return response()->json($data, 200);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $validator = Validator::make($request->only('user_id', 'session_id'), [
                'user_id' => ['required', 'string'],
                'session_id' => ['required', 'min:6', 'max:255', 'string'],
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $accessToken = AccessToken::where('access_token', $request->access_token)->first();
            if ($accessToken) {
                $accessToken->delete();
            }

            $session_id = $request->session_id;
            $user_id = $request->user_id;
            $user = User_session::where('user_id', $user_id)->where('session_id', $session_id)->first();
            $user->expired = true;
            $user->save();
            Session::flush();
            Auth::logout();
            return response()->json(['status' => Auth::check()]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'An error occurs...']);
        }
    }

    protected function validatePhoneNumber(Request $request)
    {
        try {

            // $user = Auth::;
            // die($user);
            $data = $request->validate([
                'phoneNumber' => ['required', 'numeric', 'unique:users'],
            ]);
            /* Get credentials from .env */
            $token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_sid = getenv("TWILIO_SID");
            $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
            $twilio = new Client($twilio_sid, $token);
            $send = $twilio->verify->v2->services($twilio_verify_sid)
                ->verifications
                ->create($data['phoneNumber'], "sms");
            // $user->phone_number = $data['phone_number'];
            // if ($send) {

            return response()->json(['send' => $send]);
            // }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    protected function validateOTP(Request $request)
    {
        $data = $request->validate([
            'verification_code' => ['required', 'numeric'],
            'phoneNumber' => ['required', 'string'],
        ]);
        // die($data['verification_code']);
        /* Get credentials from .env */
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_sid = getenv("TWILIO_SID");
        $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
        $twilio = new Client($twilio_sid, $token);
        $verification = $twilio->verify->v2->services($twilio_verify_sid)
            ->verificationChecks
            ->create(['to' => $data['phoneNumber'], 'code' => $data['verification_code']]);
        if ($verification->valid) {
            $user = tap(User::where('phoneNumber', $data['phoneNumber']))->update(['otpVerified' => true]);
            /* Authenticate user */
            // Auth::login($user->first());
            return response()->json(['message' => 'Phone number verified']);
        }
        return response()->json(['error' => 'Invalid verification code entered!']);
    }



    public function updateUser(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password', 'picture'), [
                'lastname' => ['required', 'min:2', 'max:50', 'string'],
                'firstname' => ['required', 'min:2', 'max:50', 'string'],
                'country' => ['nullable', 'string'],
                'phoneNumber' => ['nullable', 'min:8', 'max:15', 'string'],
                'birthDate' => ['nullable', 'string'],
                'username' => ['nullable', 'min:2', 'max:50', 'string'],
                'email' => ['required', 'email',],
                'password' => ['nullable', 'min:6', 'max:255', 'string'],
                'picture' => 'nullable',
                'picture' => 'file|mimes:jpeg,jpg,png,gif,PNG,JPG,JPEG',
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $input = $request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password');
            $input['password'] = Hash::make($request['password']);

            if ($request->hasfile('picture')) {
                if (!is_null($user->profil_picture)) {
                    unlink(public_path($user->profil_picture));
                }
                $ext = explode('.', $request->picture->getClientOriginalName())[1];
                $filename  = $user->firstname . '_' . $user->lastname . '_avatar_' . date('Ymd') . '_' . time() . '.' . $ext;
                $path = 'Users_pictures/' . $user->firstname . '_' . $user->lastname;
                $input['profil_picture'] = $path . '/' . $filename;
                $request->picture->move(public_path($path), $filename);
            }

            $user->update($input);

            return response()->json([
                'user' => $input,
                'message' => 'User informations successfully updated'
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    public function updateUserOnRegister(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password', 'picture'), [
                'lastname' => ['required', 'min:2', 'max:50', 'string'],
                'firstname' => ['required', 'min:2', 'max:50', 'string'],
                'country' => ['nullable', 'string'],
                'phoneNumber' => ['nullable', 'min:8', 'max:15', 'string'],
                'birthDate' => ['nullable', 'string'],
                'username' => ['required', 'min:2', 'max:50', 'string'],
                'email' => ['required', 'email',],
                'password' => ['required', 'min:6', 'max:255', 'string'],
                'picture' => 'nullable',
                'picture' => 'file|mimes:jpeg,jpg,png,gif,PNG,JPG,JPEG',
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $input = $request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password');
            $input['password'] = Hash::make($request['password']);

            if ($request->hasfile('picture')) {
                if (!is_null($user->profil_picture)) {
                    unlink(public_path($user->profil_picture));
                }
                $ext = explode('.', $request->picture->getClientOriginalName())[1];
                $filename  = $user->firstname . '_' . $user->lastname . '_avatar_' . date('Ymd') . '_' . time() . '.' . $ext;
                $path = 'Users_pictures/' . $user->firstname . '_' . $user->lastname;
                $input['profil_picture'] = $path . '/' . $filename;
                $request->picture->move(public_path($path), $filename);
            }

            $user->update($input);

            $contact = Contact::create([
                'id' => $user->id,
                'contact_firstname' => $user->firstname,
                'contact_lastname' => $user->lastname,
                'phoneNumber' => $user->phoneNumber,
            ]);

            $settings = Setting::create([
                'user_id' => $user->id,
            ]);

            return response()->json([
                'user' => $input,
                'contact' => $contact,
                'settings' => $settings,
                'message' => 'User informations successfully updated'
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * API Verify User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyUser($verification_code)
    {
        $check = DB::table('user_verifications')->where('token', $verification_code)->first();

        if (!is_null($check)) {
            $user = User::find($check->user_id);

            if ($user->isVerified == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account already verified..'
                ]);
            }

            $user->update(['isVerified' => 1]);
            DB::table('user_verifications')->where('token', $verification_code)->delete();

            return response()->json(['status' => 'You have successfully verified your email address.']);
        }

        return response()->json(['status' => 'Email address not yet verified.']);
    }

    /**
     * Resend verification mail
     */

    public function resendVerification(User $user)
    {
        if ($user->isVerified) {
            response()->json(['status' => 'Your account email is already verified']);
        }

        $verification_code = random_int(100000, 999999);
        DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);

        $subject = 'Please verify your email';
        $name = $user->firstname . ' ' . $user->lastname;
        $email = $user->email;
        Mail::send(
            'email.verify',
            ['name' => $name, 'verification_code' => $verification_code],
            function ($mail) use ($email, $name, $subject) {
                $mail->from(getenv('MAIL_FROM_ADDRESS'), "MyElbum");
                $mail->to($email, $name);
                $mail->subject($subject);
            }
        );

        $data =  [
            // 'token' => $user->createToken('Sanctumm+Socialite')->plainTextToken,
            'user' => $user,
            'message' => 'We send you a verification mail..'
        ];
        return response()->json($data, 200);
    }

    /**
     * Empty verification table for expiration
     */

    public function empty_verification()
    {
        $tokens = User_verification::all();

        foreach ($tokens as  $token) {
            $creation = Carbon::create($token->created_at);
            if (Carbon::now()->gt($creation->addMinutes(5))) {
                $token->delete();
            }
        }
        return 'oui';
    }

    public function users($id)
    {
        try {
            //code...
            $user = User::where('id', $id)->first();
            if ($user) {
                return response()->json(['user' => $user]);
            }
            return response()->json(['message' => 'no user with this id']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function updateSettings(Request $request, $user)
    {
        try {
            $validator = Validator::make($request->only('notification_sounds','notification_vibrate','notification_contentComments_reactions','notification_add_content_to_profile_album','notification_new_tag_in_content','notification_content_deleted'
            ), [
                'notification_sounds' => ['nullable', 'numeric'],
                'notification_vibrate' => ['nullable', 'numeric'],
                'notification_contentComments_reactions' => ['nullable', 'numeric'],
                'notification_add_content_to_profile_album' => ['nullable', 'numeric'],
                'notification_new_tag_in_content' => ['nullable', 'numeric'],
                'notification_content_deleted' => ['nullable', 'numeric'],

            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()]);
            }
// var_dump($request->notification_sounds);die;
            $user_notification_settings = Setting::where('user_id', $user)->first();
            $user_notification_settings->notification_sounds = $request->notification_sounds;
            $user_notification_settings->notification_vibrate = $request->notification_vibrate;
            $user_notification_settings->notification_contentComments_reactions = $request->notification_contentComments_reactions;
            $user_notification_settings->notification_add_content_to_profile_album = $request->notification_add_content_to_profile_album;
            $user_notification_settings->notification_new_tag_in_content = $request->notificatnotification_new_tag_in_contention_add_content_to_profile_album;
            $user_notification_settings->notification_content_deleted = $request->notification_content_deleted;
            $user_notification_settings->save();
            if ($user_notification_settings) {
                return response()->json(['message' => 'Setting saved successfully..']);
            }
            return response()->json(['message' => 'settings failed to save']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
}
