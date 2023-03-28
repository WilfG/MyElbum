<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Souscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe;

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
                $validator = Validator::make($request->only('plan_title', 'duration_time', 'plan_type', 'storage_capacity', 'user_id', 'cvc', 'card_number', 'exp_month', 'exp_year', 'cvc'), [
                    'plan_title' => ['required', 'string'],
                    'plan_type' => ['required', 'string'],
                    'storage_capacity' => ['required', 'numeric'],
                    'duration_time' => ['required', 'numeric'],
                    'user_id' => ['required', 'numeric'],
                    'card_number' => ['required', 'numeric'],
                    'exp_month' => ['required', 'numeric'],
                    'exp_year' => ['required', 'numeric'],
                    'cvc' => ['required', 'numeric'],
                ]);
            }

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }


            $user_exist = DB::table('users')->where('id', '=', $request->user_id)->first();

            if (!$user_exist) {
                return response()->json(['error' => 'This user does not exist']);
            }


            $choosen_plan = DB::table('plans')
                ->where('plan_title', '=', $request->plan_title)
                ->where('duration_time', '=', $request->duration_time)
                ->where('storage_capacity', '=', $request->storage_capacity)
                ->where('plan_type', '=', $request->plan_type)->first();

                // var_dump($choosen_plan);die;

            $verify_user_plan = Db::table('souscriptions')
                ->join('plans', 'souscriptions.plan_id', 'plans.id')
                ->where('user_id', '=', $request->user_id)
                ->where('plan_id', '=', $choosen_plan->id)
                ->select('plans.*')->first();


            if ($verify_user_plan) {
                return response()->json(['error' => 'You already suscribed to this plan, add another plan']);
            }
            $input = $request->only('user_id');
            $input['plan_id'] = $choosen_plan->id;

            
            /**
             * Payment method
             */
            if ($choosen_plan->plan_title == 'Premium') {

                if ($choosen_plan->duration_time == 18) {
                    $input['ends_at'] = $ends_at = Carbon::now()->addMonths(18);
                } elseif ($choosen_plan->duration_time == 36) {
                    $input['ends_at'] = $ends_at = Carbon::now()->addMonths(36);
                } else {
                    $input['ends_at'] = $ends_at = Carbon::now()->addMonths(60);
                }

                $stripe = new \Stripe\StripeClient(
                    env('STRIPE_SECRET_KEY')
                );
                $res = $stripe->tokens->create([
                    'card' => [
                        'number' => $request->card_number,
                        'exp_month' => $request->exp_month,
                        'exp_year' => $request->exp_year,
                        'cvc' => $request->cvc,
                    ],
                ]);

                Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
                $response =  $stripe->charges->create([
                    'amount' => $choosen_plan->price,
                    'currency' => 'usd',
                    'source' => $res->id,
                    'description' => 'The user ' . $user_exist->lastname . ' purchased a ' . $choosen_plan->plan_title . ' ' . $choosen_plan->plan_type . ' plan with a storage capacity of ' . $choosen_plan->storage_capacity . ' for a period of ' . $choosen_plan->duration_time . ' months.',
                ]);


                $souscription = Souscription::create($input);
                if ($response) {
                }
                $data = [
                    'plan' => $souscription,
                    'message' => 'Plan successfully created',
                    // 'payment status' => $response->status,
                ];
            } else {
                $input['trial_ends_at'] = $trial_ends_at = Carbon::now()->addDays(30);
                // die($input); die;
                $souscription = Souscription::create([
                    'trial_ends_at' => $trial_ends_at,
                    'ends_at' => $trial_ends_at,
                    'user_id' => intval($request->user_id),
                    'plan_id' => $choosen_plan->id,
                ]);
                $data = [
                    'plan' => $souscription,
                    'message' => 'Trial Plan successfully created',
                ];
            }
            /**
             * End Payment
             */

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
        $plan = DB::table('plans')
        ->join('souscriptions', 'plans.id', 'souscriptions.plan_id')
        ->where('souscriptions.user_id', $id)->get();
        if ($plan) {
            return response()->json(['plan' => $plan]);
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
            $validator = Validator::make($request->only('plan_title', 'duration_time', 'plan_type', 'storage_capacity', 'price'), [
                'plan_title' => ['string'],
                'duration_time' => ['numeric'],
                'plan_type' => ['string'],
                'storage_capacity' => ['numeric'],
                'price' => ['float'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $plan->update([
                'plan_title' => $request->plan_title,
                'duration_time' => $request->duration_time,
                'plan_type' => $request->plan_type,
                'storage_capacity' => $request->storage_capacity,
                'price' => $request->price,
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
