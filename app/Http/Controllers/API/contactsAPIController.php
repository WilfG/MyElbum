<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactsAPIController extends Controller
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
     * Store a newly created user's contacts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        // $MyElbumContacts = DB::table('contacts')->get();
        $MyElbumContacts = DB::table('users')
        ->select('users.id','users.firstname','users.lastname','users.email','users.phonenumber')->get();
        if ($MyElbumContacts) {
            
            $data = $MyElbumContacts;
               
            return response()->json(['MyElbumContacts' => $data, ], 200);
            
        } else {
            return response()->json(['message' => 'No contacts on MelBum Yet']);
        }
        
        // $phoneNumbers = explode(',', $request->input('phoneNumbers'));
        // $users = DB::table('users')->whereIn('phoneNumber', $phoneNumbers)->get();
        // return response()->json($users);
        
    }

    public function userContacts($id)
    {
        $userContacts = DB::table('user_contact_friends')
            ->join('contacts', 'user_contact_friends.contact_id', '=', 'contacts.id')
            ->where('user_contact_friends.user_id', '=', $id)
            ->select('contacts.*')->get();

        if ($userContacts) {
            return response()->json(['userContacts' => $userContacts]);
            
        } else {
            return response()->json(['message' => 'Contacts not found']);
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
