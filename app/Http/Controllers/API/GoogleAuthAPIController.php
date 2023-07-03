<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthAPIController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        try {
            $google_user = Socialite::driver('google')->user();// get google account informations
            $user = User::where('google_id', $google_user->getId())->first(); //verify if exist in database

            if (!$user) {
                $new_user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId()
                ]);
                $settings = Setting::create([
                    'user_id' => $user->id,
                ]);
                Auth::login($new_user);
                return response()->json([$new_user]);
            } else {
                Auth::login($user);
                return Response::json([$user]);
            }
        } catch (\Throwable $th) {
            dd('Something went wrong..', $th->getMessage());
        }
    }
}
