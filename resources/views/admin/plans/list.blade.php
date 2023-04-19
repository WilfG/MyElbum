@extends('layouts.dashboard')

@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-3">
                <h1 class="card-title">Plans list</h1>
            </div>
            <div class="col-lg-6 col-md-6"></div>
            <div class="col-lg-3 col-md-3">
                <a href="{{ route('plans.create') }}" class="btn btn-success btn-sm pull-right"><i class="fa fa-plus"> Create a new plan</i></a>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Tiltle</th>
                                    <th>Duration Time (Months)</th>
                                    <th>Type</th>
                                    <th>Storage Capacity (contents)</th>
                                    <th>Price ($)</th>
                                    <th>Stats</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($plans as $key => $plan)
                                <tr>
                                    <td>{{ $plan->plan_title }}</td>
                                    <td>{{ $plan->duration_time }}</td>
                                    <td>{{ $plan->plan_type }}</td>
                                    <td>{{ $plan->storage_capacity }}</td>
                                    <td>{{ $plan->price }}</td>
                                    <td>{{ $souscriptions[$plan->id] }}</td>

                                    <td>
                                        <a href="{{ route('plans.edit', $plan->id) }}"><i class="fa fa-pen"></i></a>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Tiltle</th>
                                    <th>Duration Time (Months)</th>
                                    <th>Type</th>
                                    <th>Storage Capacity (contents)</th>
                                    <th>Price ($)</th>
                                    <th>Stats</th>
                                    <th>Actions</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
@endsection