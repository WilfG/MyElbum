<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccessToken;
use App\Models\Contact;
use App\Models\User;
use App\Models\User_session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Nette\Utils\Random;
use Illuminate\Support\Str;


class SocialiteController extends Controller
{
    public function googleSignup(Request $request)
    {
        try {

            $validator = Validator::make($request->only('firstname', 'lastname', 'google_id', 'email'), [
                'firstname' => ['required', 'min:2', 'max:50', 'string'],
                'lastname' => ['required', 'min:2', 'max:50', 'string'],
                'google_id' => ['required', 'string'],
                'email' => ['required',  Rule::unique('users'),],
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);


            $user = User::firstOrNew(['email' => $request->email]);
            $user->lastname = $request->firstname;
            $user->firstname = $request->lastname;
            $user->email = $request->email;
            $user->google_id = $request->google_id;
            $user->save();

            $verification_code = Random::generate(30);
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

            $contact = Contact::firstOrNew(['id' => $user->id]);
            $contact->contact_firstname = $user->firstname;
            $contact->contact_lastname = $user->lastname;
            $contact->save();

            Auth::login($user);
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

            $data =  [
                'session_id' => $session_id,
                // 'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                'token' => $accessToken,
                'user' => $user,
                'status' => Auth::check(),
                'message' => 'Your account is successfully created and you are logged in',
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage()]);
        }
    }

    public function googleSignin(Request $request)
    {
        try {
            $validator = Validator::make($request->only('google_id', 'email'), [
                'google_id' => ['required', 'string'],
                'email' => ['required', 'email', 'exists:users,email'],
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);


            $user = User::where('email', $request->email)->where('google_id', $request->google_id)->first();
            if ($user) {

                Auth::login($user);

                $accessToken = AccessToken::updateOrCreate(
                    ['user_id' => $user->id],
                    ['access_token' => Str::random(191)]
                );

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
                }

                $friend_requests = DB::table('user_contacts')
                    ->where('user_contacts.user_id', '=', $user->id)
                    ->where('user_contacts.request_status', '=', 'Pending')->get();

                // $location  = GeoLocation::lookup('192.168.149.100');die;
                // $latitude = $location->getLatitude();
                // $longitude = $location->getLongitude();
                // dd($latitude);
                $notifications = DB::table('notifications')->where('user_id', $user->id)->get();
                $data =  [
                    'token' => $accessToken,
                    'session_id' => $session_id,
                    'user' => $user,
                    'plan' => $plan,
                    'frames' => $frames,
                    'friend_requests' => $friend_requests,
                    'notifications' => $notifications,
                    'status' => Auth::check(),
                    'message' => 'You are logged in',
                ];
                return response()->json($data, 200);
            }
            return response()->json(['error' => 'This account does not exist, Sign up with Google first.'], 200);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage()]);
        }
    }
}
