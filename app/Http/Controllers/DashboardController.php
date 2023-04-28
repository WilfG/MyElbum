<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $total_nbr_user = DB::table('users')->count();
        $total_nbr_souscription = DB::table('souscriptions')->count();
        $total_nbr_online_users = DB::table('user_sessions')->where('expired', 0)->count();

        $countries = DB::table('users')->select('country')->distinct()->get();
        $regions = DB::table('user_sessions')->select('region')->distinct()->get();

        return view('admin_template', ['regions' => $regions, 'countries' => $countries, 'total_nbr_user' => $total_nbr_user, 'total_nbr_souscription' => $total_nbr_souscription, 'total_nbr_online_users' => $total_nbr_online_users]);
    }

    /**
     * 
     */

    public function results_stats_country(Request $request)
    {
        if ($request->ajax()) {
            $nbr_user_by_country = DB::table('users')->where('country', $request->value)->count();
            echo '<div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
          
                    <div class="info-box-content">
                      <span class="info-box-text">Total Users from ' . $request->value . '</span>
                      <span class="info-box-number">' .
                $nbr_user_by_country .
                ' <!-- <small>%</small> -->
                      </span>
                    </div>
                    <!-- /.info-box-content -->
                  </div>';
        }
    }

    /**
     * 
     */
    public function results_stats_region(Request $request)
    {
        if ($request->ajax()) {
            $nbr_online_user_by_country = DB::table('user_sessions')->where('region', $request->value)->count();
            echo '<div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
          
                    <div class="info-box-content">
                      <span class="info-box-text">Total Users from ' . $request->value . '</span>
                      <span class="info-box-number">' .
                $nbr_online_user_by_country .
                ' <!-- <small>%</small> -->
                      </span>
                    </div>
                    <!-- /.info-box-content -->
                  </div>
                  <!-- /.info-box -->';
        }
    }

    /**
     * 
     */

    public function results_stats_period(Request $request)
    {
        if ($request->ajax()) {
            // die($request->end_period);
            $start_period = $request->start_period;
            $end_period = $request->end_period;
            // print '<h3>'. $request->start_period . '</h3>';
            $nbr_subscribed_user_on_this_period = DB::table('users')->where('created_at','>=', $start_period)->where('created_at', '<=', $end_period)->count();
            echo '<div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>

                    <div class="info-box-content">
                      <span class="info-box-text">Total Users suscribed in this period </span>
                      <span class="info-box-number">' .
                $nbr_subscribed_user_on_this_period .
                ' <!-- <small>%</small> -->
                      </span>
                    </div>
                    <!-- /.info-box-content -->
                  </div>
                  <!-- /.info-box -->';
            // echo '<span>' . $request->start_period . '</span>';
        }
    }

    public function generate_signature(){
        return URL::temporarySignedRoute('connexion', now()->addMinute());
    }
    public function generate_signature_signup(){
        return URL::temporarySignedRoute('inscrire', now()->addMinute(5));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
