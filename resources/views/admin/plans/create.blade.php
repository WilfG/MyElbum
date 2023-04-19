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
                         @if (session('status'))
                         <div class="mb-4 font-medium text-sm text-green-600 alert alert-success">
                             {{ session('status') }}
                         </div>
                         @endif
                         <form action="{{ route('plans.store') }}" method="POST">
                             @csrf
                             <label for="plan_title">Title</label>
                             <select class="form-control form-control-lg" id="plan_title" name="plan_title">
                                 <option value="Premium">Premium</option>
                             </select>
                             <br>
                             <label for="plan_type">Type</label>
                             <select class="form-control" id="plan_type" name="plan_type" step="1" required>
                                 <option value="Lite">Lite</option>
                                 <option value="All Go">All Go</option>
                             </select>
                             <br>
                             <label for="duration_time"> Duration time (Months)</label>
                             <select class="form-control form-control-sm" name="duration_time" id="duration_time" required>
                                 <option value="1">1</option>
                                 <option value="18">18</option>
                                 <option value="60">60</option>
                             </select>
                             <br>
                             <label for="storage_capacity">Storage Capacity</label>
                             <select class="form-control form-control-sm" name="storage_capacity" id="storage_capacity" required>
                                 <option value="4">4</option>
                                 <option value="8">8</option>
                                 <option value="12">12</option>
                                 <option value="24">24</option>
                                 <option value="48">48</option>
                             </select>
                             <br>
                             <label for="price">Price ($)</label>
                             <input class="form-control form-control-sm" name="price" step="0.01" required id="price" type="number" min="0">
                             <br>
                             <div class="pull-right">
                                 <input type="submit" class="btn btn-info " value="Create Plan">
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