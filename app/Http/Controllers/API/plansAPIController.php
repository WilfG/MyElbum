<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlansAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = DB::table('plans')->get();
        // ->where('user_id', Auth::id())->get();
        return response()->json(['plans' => $plans]);
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
            if ($request->plan_title == 'Free Trial') {
                $validator = Validator::make($request->only('plan_title', 'user_id'), [
                    'plan_title' => ['required', 'string'],
                    'user_id' => ['required', 'numeric'],
                ]);
            } else {
                $validator = Validator::make($request->only('plan_title', 'duration_time', 'plan_type', 'storage_capacity', 'user_id'), [
                    'plan_title' => ['required', 'string'],
                    'duration_time' => ['required', 'numeric'],
                    'plan_type' => ['required', 'string'],
                    'storage_capacity' => ['required', 'numeric'],
                    'user_id' => ['required', 'numeric'],
                ]);
            }

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            // die($request->plan_title);
            if ($request->plan_title == 'Free Trial') {
                $request->merge(['plan_type' => 'zero']);
                $request->merge(['storage_capacity' => '8']);
                $request->merge(['duration_time' => 45]);
            }


            $input = $request->only('plan_title', 'duration_time', 'plan_type', 'storage_capacity', 'user_id');
            $user_exist = DB::table('users')->where('id', '=', $request->user_id)->first();

            if (!$user_exist) {
                return response()->json(['error' => 'This user does not exist']);
            }
            
            $verify_user_plan = Db::table('plans')->where('plans.user_id', '=', $request->user_id)
                ->where('plans.plan_title', '=', $request->plan_title)
                ->where('plans.plan_type', '=', $request->plan_type)
                ->where('plans.storage_capacity', '=', $request->storage_capacity)
                ->where('plans.duration_time', '=', $request->duration_time)->first();

            if ($verify_user_plan) {
                return response()->json(['error' => 'You already suscribed to this plan, add another plan']);
            }

            $plan = Plan::create($input);

            $data = [
                'plan' => $plan,
                'message' => 'Plan successfully created'
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
        $plan = Plan::find($id);
        if ($plan) {
            return response()->json(['plan', $plan]);
        } else {
            return response()->json(['message' => 'Plan not found']);
        }
    }


    /**
     * User Plan
     */

    public function user_plan($id)
    {
        $plan = DB::table('plans')->where('user_id', $id)->get();
        if ($plan) {
            return response()->json(['plan', $plan]);
        } else {
            return response()->json(['message' => 'Plan not found']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Plan $plan)
    {
        try {
            //code...
            $validator = Validator::make($request->only('plan_title', 'duration_time', 'plan_type', 'storage_capacity', 'user_id'), [
                'plan_title' => ['string'],
                'duration_time' => ['numeric'],
                'plan_type' => ['string'],
                'storage_capacity' => ['numeric'],
                'user_id' => ['numeric'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $plan->update([
                'plan_title' => $request->plan_title,
                'duration_time' => $request->duration_time,
                'plan_type' => $request->plan_type,
                'storage_capacity' => $request->storage_capacity,
                'user_id' => $request->user_id,
            ]);

            return response()->json([
                'plan' => $plan,
                'message' => 'Plan successfully updated'
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
        $plan = Plan::find($id);
        if ($plan) {
            $plan->delete();
            return response()->json(['message', 'Plan successfully deleted']);
        } else {
            return response()->json(['message', 'Plan not found']);
        }
    }
}
