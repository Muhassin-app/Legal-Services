@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Permissions'])

@section('content')
<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Roles & Permissions</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
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
                                @if (\Session::has('error'))
                                <div class="alert alert-danger">
                                    <span>{!! \Session::get('error') !!}</span>
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
                            <a class="btn btn-info waves-effect waves-light text-sm-right"
                            href="{{route('roles')}}"><i class="mdi mdi-plus-circle mr-1"></i> Back</a>

                                <a class="btn btn-info waves-effect waves-light text-sm-right add-permission"
                                href="javascript:;"><i class="mdi mdi-plus-circle mr-1"></i> Create Permission</a>

                                <a class="btn btn-info waves-effect waves-light text-sm-right " href="{{route('assign.permissions')}}"><i class="mdi mdi-plus-circle mr-1"></i> Assign Permissions
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">

                            <tr>
                               <th>No</th>
                               <th>Name</th>
                               <th width="280px">Action</th>
                            </tr>
                              @forelse ($permissions as $key => $role)
                              <tr>
                                  <td>{{ ++$key }}</td>
                                  <td>{{ $role->name }}</td>
                                  <td>
                                    <a class="btn btn-primary btn-sm edit-permission" data-name="{{$role->name}}" data-id="{{$role->id}}" href="javascript:;">Edit</a>
                                      {{-- <a class="btn btn-info" href="{{ route('roles.show',$role->id) }}">Show</a>
                                      @can('role-edit')
                                          <a class="btn btn-primary" href="{{ route('roles.edit',$role->id) }}">Edit</a>
                                      @endcan
                                      @can('role-delete')
                                          {!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $role->id],'style'=>'display:inline']) !!}
                                              {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                          {!! Form::close() !!}
                                      @endcan --}}
                                  </td>
                              </tr>
                              @empty

                              <tr>
                                <td colspan="10" class="text-center">No Record found.</td>
                              </tr>
                              @endforelse
                          </table>
                        
                    </div>
                  
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
</div>


<div id="add-permission-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Add Permission') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="add_permission" method="post" action="{{ route('save.permission') }}">
                @csrf
                <div class="modal-body" >
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group" id="nameInput">
                                        {!! Form::label('title', __('Permission'),['class' => 'control-label']) !!}
                                        {!! Form::text('permission_name', null, ['class'=>'form-control', 'required'=>'required']) !!}
                                        <span class="invalid-feedback" role="alert">
                                            <strong></strong>
                                        </span>
                                    </div>
                                </div>
                              
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light submitAddSubscriptionForm">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')

<script>
    $(document).delegate(".add-permission", "click", function(){
        $("#add-permission-modal").modal("show");
    });

    $(document).on('click', '.edit-permission', function(e) {
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            $("#add-permission-modal").modal("show");
            $('.permission-name').val(name);
            $('.permission-id').val(id);

        });

</script>

@endsection