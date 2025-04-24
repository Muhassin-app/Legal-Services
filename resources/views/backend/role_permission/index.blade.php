@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Role'])
<style>
    li.role_name.active a { color: #43bee1; }
</style>
@section('content')
    <!-- Start Content-->
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Roles and Permission</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        {{-- <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-sm-8">
                                <div class="text-sm-left">
                                    @if (\Session::has('success'))
                                        <div class="alert alert-success">
                                            <span>{!! \Session::get('success') !!}</span>
                                        </div>
                                    @endif
                                    @if (\Session::has('error_delete'))
                                        <div class="alert alert-danger">
                                            <span>{!! \Session::get('error_delete') !!}</span>
                                        </div>
                                    @endif
                                </div>
                            
                                @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <div class="col-sm-4 text-right">
                            <a class="btn btn-info waves-effect waves-light text-sm-right add-role" href="javascript:;"><i class="mdi mdi-plus-circle mr-1"></i> Create Role</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="vendor_payouts_datatable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th width="280px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $key => $role)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ @$role->name }}</td>
                                    <td>
                                        <a class="btn btn-primary btn-sm edit-role" data-name="{{@$role->name}}" data-id="{{$role->id}}" href="javascript:;">Edit</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No Record found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div> --}}
   
    <div class="row">
        <div class="col-md-3">
            <div class="role-left">
                <a class="btn btn-info waves-effect waves-light text-sm-center add-role permission-role"
                href="javascript:;"><i class="mdi mdi-plus-circle mr-1"></i> Create Role</a>
                <ul class="m-0 p-0 card-box">
                    @forelse ($roles as $key => $role)
                        <li data-id="{{$role->id}}"  class="role_name @if($key == 0)active @endif"><a href="javascript:void(0)">{{ @$role->name }}</a></li>
                    @empty
                        <tr>
                            <li data-id="{{$role->id}}"  class="role_name"><a href="javascript:void(0)">No Record found.</li></tr>
                        </tr>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-12">
                    <div class="permision_btn">
                        {{-- <h4>Module Permission	</h4> --}}
                        {{-- <a href="#">Click</a> --}}
                    </div>
                </div>
            </div>
            <div id="permission-data"></div>
        </div>
    </div>
</div>

@include('backend.role_permission.modals')

@endsection

@section('script')
    @include('backend.role_permission.pagescript')
@endsection