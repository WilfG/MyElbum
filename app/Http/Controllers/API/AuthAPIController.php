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
use Illuminate\Support\Facades\Session;
use Twilio\Rest\Client;
use Stevebauman\Location\Facades\Location;


class AuthAPIController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password'), [
                'lastname' => ['required', 'min:2', 'max:50', 'string'],
                'firstname' => ['required', 'min:2', 'max:50', 'string'],
                'country' => ['required', 'string'],
                'phoneNumber' => ['required', 'min:8', 'max:15', 'string'],
                'birthDate' => ['required', 'string'],
                'username' => ['required', 'min:2', 'max:50', 'string'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:6', 'max:255', 'string'],
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $input = $request->only('lastname', 'firstname', 'country', 'phoneNumber', 'birthDate', 'username', 'email', 'password');
            $input['password'] = Hash::make($request['password']);
            $input['isVerified'] = 0;
            // var_dump($input); die;
            $user = User::create($input);
            event(new Registered($user));

            $contact = Contact::firstOrNew(['id' => $user->id]);
            $contact->contact_firstname = $user->firstname;
            $contact->contact_lastname = $user->lastname;
            $contact->save();
            Auth::login($user);
            $data =  [
                'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
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
                $validator = Validator::make($request->only('email', 'password'), [
                    'email' => ['required', 'email', 'exists:users,email'],
                    'password' => ['required', 'min:6', 'max:255', 'string'],
                ]);
                if ($validator->fails())
                    return response()->json($validator->errors(), 400);

                        
                    if ($position = Location::get()) {
                        var_dump($position->countryName);die;
                    }

                if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                    // die;
                    $user = $request->user();
                    Auth::login($user);
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

    public function logout()
    {
        // die;
        Session::flush();
        Auth::logout();
        return response()->json(['status' => Auth::check()]);
    }

    protected function validatePhoneNumber(Request $request)
    {
        // $user = Auth::;
        // die($user);
        $data = $request->validate([
            'phone_number' => ['required', 'numeric', 'unique:users'],
        ]);
        /* Get credentials from .env */
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_sid = getenv("TWILIO_SID");
        $twilio_verify_sid = getenv("TWILIO_VERIFY_SID");
        $twilio = new Client($twilio_sid, $token);
        $twilio->verify->v2->services($twilio_verify_sid)
            ->verifications
            ->create($data['phone_number'], "sms");
        // $user->phone_number = $data['phone_number'];
        // $user->save();
        return redirect()->with(['phone_number' => $data['phone_number']]);
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
}
