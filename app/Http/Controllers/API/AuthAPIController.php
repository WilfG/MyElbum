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

class AuthAPIController extends Controller
{
    public function register(Request $request)
    {

        try {
            $validator = Validator::make($request->only('email', 'name'), [
                'name' => ['required', 'min:2', 'max:50', 'string'],
                'email' => ['required', 'email', 'unique:users,email'],
                // 'password' => ['required', 'min:6', 'max:255', 'confirmed', 'string'],
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);

            $input = $request->only('name', 'email');
            // $input['password'] = Hash::make($request['password']);
            $user = User::create($input);
            event(new Registered($user));


            $data =  [
                'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                'user' => $user,
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
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:6', 'max:255', 'string'],
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = $request->user();
            $data =  [
                'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                'user' => $user,
            ];
            return response()->json($data, 200);
        }
    }
}
