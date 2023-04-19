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
use App\Models\User_session;

class AuthAPIController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password', 'latitude', 'longitude', 'region'), [
                'lastname' => ['required', 'min:2', 'max:50', 'string'],
                'firstname' => ['required', 'min:2', 'max:50', 'string'],
                'country' => ['required', 'string'],
                'phoneNumber' => ['required', 'min:8', 'max:15', 'string'],
                'birthDate' => ['required', 'string'],
                'username' => ['required', 'min:2', 'max:50', 'string'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:6', 'max:255', 'string'],
                'latitude' => ['required', 'string',],
                'longitude' => ['required', 'string',],
                'region' => ['required', 'string',],
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $input = $request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password', 'latitude', 'longitude', 'region');
            $input['password'] = Hash::make($request['password']);
            $input['isVerified'] = 0;
            // var_dump($input); die;
            $user = User::create($input);

            $verification_code = Random::generate(30);
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

            $contact = Contact::firstOrNew(['id' => $user->id]);
            $contact->contact_firstname = $user->firstname;
            $contact->contact_lastname = $user->lastname;
            $contact->save();
            Auth::login($user);
            //log user session
            $session_id = Session::getId();
            User_session::create([
                'region' => $request->region,
                'country' => $request->country,
                'session_id' => $session_id,
                'user_id' => $user->id,
            ]);

            $data =  [
                'token' => $user->createToken('Sanctumm+Socialite')->plainTextToken,
                'user' => $user,
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

                    // $location  = GeoLocation::lookup('192.168.149.100');die;
                    // $latitude = $location->getLatitude();
                    // $longitude = $location->getLongitude();
                    // dd($latitude);
                    $notifications = DB::table('notifications')->where('user_id', $user->id)->get();
                    $data =  [
                        'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                        'session_id' => $session_id,
                        'user' => $user,
                        'plan' => $plan,
                        'frames' => $frames,
                        'friend_requests' => $friend_requests,
                        'notifications' => $notifications,
                        'status' => Auth::check(),
                        'message' => 'you are successfully logged in'
                    ];
                    return response()->json($data, 200);
                }
            } elseif (isset($request->phoneNumber)) {
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
                    $data =  [
                        'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                        'user' => $user,
                        'plan' => $plan,
                        'frames' => $frames,
                        'friend_requests' => $friend_requests,
                        'notifications' => $notifications,
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
        $validator = Validator::make($request->only('user_id', 'session_id'), [
            'user_id' => ['required', 'string'],
            'session_id' => ['required', 'min:6', 'max:255', 'string'],
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $session_id = $request->session_id;
        $user_id = $request->user_id;
        $user = User_session::where('user_id', $user_id)->where('session_id', $session_id)->first();
        $user->expired = true;
        $user->save();
        Session::flush();
        Auth::logout();
        return response()->json(['status' => Auth::check()]);
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
                'country' => ['required', 'string'],
                'phoneNumber' => ['required', 'min:8', 'max:15', 'string'],
                'birthDate' => ['required', 'string'],
                'username' => ['required', 'min:2', 'max:50', 'string'],
                'email' => ['required', 'email',],
                'password' => ['required', 'min:6', 'max:255', 'string'],
                'picture' => 'required',
                'picture' => 'file|mimes:jpeg,jpg,png,gif,PNG,JPG,JPEG',
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $input = $request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password');
            $input['password'] = Hash::make($request['password']);

            if ($request->hasfile('picture')) {
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

            return redirect()->route('email-verified')->with('status', 'You have successfully verified your email address.');
        }

        return redirect()->route('email-not-verified')->with('status', 'Email address not yet verified.');
    }
}
