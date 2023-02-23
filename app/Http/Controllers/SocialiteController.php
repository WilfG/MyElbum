<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class SocialiteController extends Controller
{
    public function handleProviderCallback(Request $request)
    {
        try {

            $validator = Validator::make($request->only('provider', 'access_provider_token'), [
                'provider' => ['required', 'string'],
                'access_provider_token' => ['required', 'string']
            ]);
            if ($validator->fails())
                return response()->json($validator->errors(), 400);
            $provider = $request->provider;
            $validated = $this->validateProvider($provider);
            if (!is_null($validated))
                return $validated;
            $providerUser = Socialite::driver($provider)->userFromToken($request->access_provider_token);
            $user = User::firstOrCreate(
                [
                    'email' => $providerUser->getEmail()
                ],
                [
                    'name' => $providerUser->getName(),
                ]
            );
            Auth::login($user);
            $data =  [
                'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                'user' => $user,
                'access_provider_token' => $request->access_provider_token,
                'status' => Auth::check(),
                'message' => 'Your account is successfully created and you are logged in',
            ];
            return response()->json($data, 200);
            
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage()]);
        }
    }

    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['google'])) {
            return response()->json(["message" => 'You can only login via google account'], 400);
        }
    }
}
