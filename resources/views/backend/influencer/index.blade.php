@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Influencer'])

@section('content')
<style>
    .dataTables_filter, .dataTables_info { display: none; }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                
                <div class="float-left">
                    <button type="button" onclick="history.go(-1)" class="btn btn-outline-dark waves-effect waves-light mr-2">
                        <i class="bx bx-left-arrow-alt font-size-16 align-middle "></i> Back
                    </button>
                    <h4 class="mb-0 font-size-18 d-inline">Groups</h4>
                </div>
                <div class="page-title-right">
                        
                        <a class="btn btn-primary pull-right" style="margin-top: -10px;margin-bottom: 5px" href="{{ route('admin-type.create') }}">Add New</a>
                </div>

            </div>
        </div>
    </div>
    
   <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body"> 
                    @include('admin::admin_types.table')
            </div>
        </div>
        <div class="text-center">
        
        </div>
    </div>
</div>
</div>


@endsection