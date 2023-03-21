 @extends('layouts.dashboard')

 @section('content')
 <!-- Main content -->
 <section class="content">
     <div class="container-fluid">
         <div class="row">
             <div class="col-md-3"></div>
             <!-- right column -->
             <div class="col-md-6">
                 <!-- Form Element sizes -->
                 <div class="card card-primary">
                     <div class="card-header">
                         <h3 class="card-title">Edit Plan's price</h3>
                     </div>
                     <div class="card-body">
                         @if ($errors->any())
                         <div class="alert alert-danger">
                             <strong>Whoops!</strong> There were some problems with your input.<br><br>
                             <ul>
                                 @foreach ($errors->all() as $error)
                                 <li>{{ $error }}</li>
                                 @endforeach
                             </ul>
                         </div>
                         @endif
                         <form action="{{ route('plans.update', $plan->id) }}" method="POST">
                             @csrf
                             @method('PUT')
                             <label for="plan_title">Title</label>
                             <input class="form-control form-control-lg" id="plan_title" name="plan_title" disabled type="text" value="{{ $plan->plan_title }}">
                             <br>
                             <label for="duration_time">Duration time (Months)</label>
                             <input class="form-control" id="duration_time" name="duration_time" type="text" disabled value="{{ $plan->duration_time }}">
                             <br>
                             <label for="plan_type">Type</label>
                             <input class="form-control form-control-sm" name="plan_type" id="plan_type" disabled type="text" value="{{ $plan->plan_type }}">
                             <br>
                             <label for="storage_capacity">Storage Capacity</label>
                             <input class="form-control form-control-sm" name="storage_capacity" id="storage_capacity" disabled type="text" value="{{ $plan->storage_capacity }}">
                             <br>
                             <label for="plan_type">Price ($)</label>
                             <input class="form-control form-control-sm" name="price" step="0.01" required id="price" type="number" min="0" value="{{ $plan->price }}">
                             <br>
                             <div class="pull-right">
                                 <input type="submit" class="btn btn-info " value="Update Plan">
                             </div>
                         </form>
                     </div>
                     <!-- /.card-body -->
                 </div>
                 <!-- /.card -->
             </div>
             <div class="col-md-3"></div>

         </div>
     </div>
 </section>
 <!-- /.content -->
 @endsection