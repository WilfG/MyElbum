<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\UserContact;
use App\Models\UserContactFriends;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserContactAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->only('user_id', 'contact_id'), [
                'user_id' => ['required', 'string'],
                'contact_id' => ['required', 'numeric'],
            ]);


            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            if ($request->user_id == $request->contact_id) {
                return  response()->json(['error' => 'You cannot invite yourself.']);
            }

            $verify_invitation = DB::table('user_contacts')
                ->where('user_contacts.user_id', '=', $request->user_id)
                ->where('user_contacts.contact_id', '=', $request->contact_id)
                ->orWhere(function ($query) use ($request) {
                    $query->where('user_contacts.user_id', '=', $request->contact_id)
                        ->where('user_contacts.contact_id', '=', $request->user_id);
                })->first();
            if ($verify_invitation) {
                return  response()->json(['error' => 'You are already friends, or your invitation is pending..']);
            }

            $input = $request->only('user_id', 'contact_id');
            // var_dump($input);
            // die;

            $user_contact = UserContact::create($input);

            $data = [
                'user_contact' => $user_contact,
                'message' => 'Invitation sent, pending...'
            ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->only('request_status', 'user_id', 'contact_id'), [
                'request_status' => ['required', 'string'],
                'user_id' => ['required', 'numeric'],
                'contact_id' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            if ($request->request_status == 'Confirm') {

                for ($i = 0; $i < 2; $i++) {
                    if ($i == 0) {
                        $usercontactfriends = UserContactFriends::create([
                            'user_id' => $request->user_id,
                            'contact_id' => $request->contact_id,
                        ]);
                    }
                    if ($i == 1) {
                        $usercontactfriends = UserContactFriends::create([
                            'user_id' => $request->contact_id,
                            'contact_id' => $request->user_id,
                        ]);
                    }
                }
                $invitation = UserContact::where('user_id', $request->user_id)->where('contact_id', $request->contact_id)->first();
                $invitation->request_status = $request->request_status;
                $invitation->request_notification = 'No';
                $invitation->save();
                return response()->json(['message' => 'Invitation accepted', 'status' => true]);
            } elseif ($request->request_status == 'Reject') {
                $invitation = UserContact::where('user_id', $request->user_id)->where('contact_id', $request->contact_id)->first();
                $invitation->delete();
                return response()->json(['message' => 'Invitation rejected', 'status' => false]);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 400]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
