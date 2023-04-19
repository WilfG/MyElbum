@extends('layouts.auth')

@section('main')
<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="#" class="h1"><b>My</b>Elbum</a>
        </div>
        <div class="card-body">
            <div class="mb-4 font-medium text-sm text-green-600 alert alert-danger">
                <p class="login-box-msg">Email address not yet verified.. try later</p>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>
<!-- /.login-box -->
@endsection