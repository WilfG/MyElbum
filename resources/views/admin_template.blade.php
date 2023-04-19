@extends('layouts.dashboard')

@section('content')
<div class="row">
  <div class="col-md-1 col-lg-1"></div>
  <div class="col-md-3 col-lg-3">
    <label for="country">Subscribed Users by country</label>
    <select id="country" name="country" class="form-control">
      @foreach($countries as $country)
      <option value="{{ $country->country }}">{{ $country->country }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3 col-lg-3">
    <label for="region">Online user by region</label>
    <select id="region" name="region" class="form-control">
      @foreach($regions as $region)
      <option value="{{ $region->region }}">{{ $region->region }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-5 col-lg-5">
    <form action="#" class="period_form" method="POST" enctype="multipart/form-data">
      @csrf
      <label for="">Subscribed users in specific period</label><br>
      <input type="date" name="start_date" id="start_date">
      <input type="date" name="end_date" id="end_date">
      <button class="btn btn-success btn-md">Filter</button>
    </form>

  </div>
</div><br>
<section class="content">
  <div class="container-fluid">

    <!-- <section class="content">
      <div class="container-fluid"> -->
    <!-- Info boxes -->
    <div class="row">
      <div class="col-12 col-sm-6 col-md-4" id="results-stats-country">
      </div>

      <div class="col-12 col-sm-6 col-md-4" id="results-stats-region">
      </div>
      <div class="col-12 col-sm-6 col-md-4" id="results-stats-period">
      </div>
    </div>

    <!-- </div>
    </section> -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number">
                  {{ $total_nbr_user }}
                  <!-- <small>%</small> -->
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-">$</i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total souscriptions</span>
                <span class="info-box-number">{{ $total_nbr_souscription }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-4">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total online users</span>
                <span class="info-box-number">{{ $total_nbr_online_users }}</span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <!-- <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
          <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">New Members</span>
            <span class="info-box-number">2,000</span>
          </div> -->
          <!-- /.info-box-content -->
          <!-- </div> -->
          <!-- /.info-box -->
          <!-- </div> -->
          <!-- /.col -->
        </div>
        <!-- /.row -->


        <!-- /.row -->
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->

    @endsection