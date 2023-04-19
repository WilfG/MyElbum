<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Souscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = Plan::all();
        $souscriptions = [];
        foreach ($plans as $key => $plan) {
            $souscriptions[$plan->id] = DB::table('souscriptions')->where('plan_id', '=', $plan->id)->count();
            // $plan-
        }
       
        return view('admin.plans.list', ['plans' => $plans, 'souscriptions' => $souscriptions]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // var_dump($request->price);die;
        try {
            $validator = Validator::make($request->only('plan_title', 'duration_time', 'plan_type', 'storage_capacity', 'price'), [
                'plan_title' => ['required', 'string'],
                'plan_type' => ['required', 'string'],
                'storage_capacity' => ['required', 'string'],
                'duration_time' => ['required', 'string'],
                'price' => ['required', 'string'],
            ]);


            if ($validator->fails()) {
                return redirect()->route('plans.create')->with('errors', $validator->errors());
            }

            $plan = Plan::create([
                'plan_title' => $request->plan_title,
                'plan_type' => $request->plan_type,
                'storage_capacity' => $request->storage_capacity,
                'duration_time' => $request->duration_time,
                'price' => $request->price,
            ]);
            if ($plan) {
                return redirect()->route('plans.create')->with('status', 'Plan successfully created');
            }
        } catch (\Throwable $th) {
            return redirect()->route('plans.create')->with('status', $th->getMessage());
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', ['plan' => $plan]);
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
        $validator = Validator::make($request->only('price'), [
            'price' => 'string|required',
        ]);


        if ($validator->fails()) {
            return redirect()->route('plans.edit', $plan->id)->with('error', $validator->errors());
        }

        $plan->price = $request->price;
        $plan->save();

        return redirect()->back()->with('status', 'Plan successfully updated');
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
