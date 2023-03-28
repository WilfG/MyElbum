<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationsAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = DB::table('notifications')->get();
        // ->where('user_id', Auth::id())->get(); // à améliorer
        return response()->json(['notifications' => $notifications]);    }

    /**
     * Store a newly created resource in storage.
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
    public function show($id)
    {
        //
    }
    public function userNotifications($id)
    {
        $notifications = DB::table('notifications')
        ->where('user_id', '=', $id)->get();

        return response()->json(['user_notifications' => $notifications]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notification $notification)
    {
        try {
            //code...
            $validator = Validator::make($request->only('status'), [
                'status' => ['string'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $notification->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'notification' => $notification,
                'message' => 'Notification successfully viewed'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ]);
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
        //
    }
}
