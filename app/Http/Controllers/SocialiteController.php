<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SocialiteController extends Controller
{
    public function googleSignup(Request $request)
    {
        try {

            $validator = Validator::make($request->only('firstname', 'lastname', 'google_id', 'email'), [
                'firstname' => ['required','min:2', 'max:50', 'string'],
                'lastname' => ['required','min:2', 'max:50', 'string'],
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

            $contact = Contact::firstOrNew(['id' => $user->id]);
            $contact->contact_firstname = $user->firstname;
            $contact->contact_lastname = $user->lastname;
            $contact->save();

            Auth::login($user);
            $data =  [
                'token' => $user->createToken('Sanctom+Socialite')->plainTextToken,
                'user' => $user,
                'status' => Auth::check(),
                'message' => 'Your account is successfully created and you are logged in',
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage()]);
        }
    }
}
