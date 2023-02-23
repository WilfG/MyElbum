<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;

class AuthAPIController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->only('lastname','firstname','country','phoneNumber', 'birthDate','username','email','password'), [
                'lastname' => ['required', 'min:2', 'max:50', 'string'],
                'firstname' => ['required', 'min:2', 'max:50', 'string'],
                'country' => ['required', 'min:2', 'max:50', 'string'],
                'phoneNumber' => ['required', 'min:2', 'max:50', 'string'],
                'birthDate' => ['required', 'min:2', 'max:50', 'string'],
                'username' => ['required', 'min:2', 'max:50', 'string'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:6', 'max:255', 'string'],
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $input = $request->only('lastname','firstname','country','phoneNumber', 'birthDate','username','email','password');
            $input['password'] = Hash::make($request['password']);
            $input['isVerified'] = 0;
            // var_dump($input); die;
            $user = User::create($input);
            event(new Registered($user));

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
            // die($request);
            $validator = Validator::make($request->only('email', 'password'), [
                'email' => ['required', 'email', 'exists:users,email'],
                'password' => ['required', 'min:6', 'max:255', 'string'],
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                // die;
                $user = $request->user();
                $data =  [
                    'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                    'user' => $user,
                    'status' => Auth::check(),
                    'message' => 'you are successfully logged in'
                ];
                return response()->json($data, 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }
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


}