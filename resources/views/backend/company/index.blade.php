@extends('layouts.vertical', ['title' => 'Companies'])

@section('css')
    <link href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/dropify/dropify.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Companies</h4>
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
                               @include('alert')
                            </div>
                            <div class="col-sm-4 text-right">
                                <button class="btn btn-info openServiceModal"> {{ __('Add New Company') }}</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-striped" id="products-datatable">
                                <thead>
                                    <tr>
                                        <th>Logo</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                        <th>Address</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($company as $geo)
                                        <tr>
                                            <td class="table-user">
                                               <a href="javascript:void(0);" class="text-body"><img src="{{get_file_path($geo->logo,'FILL_URL','40','40')}}"></a>
                                            </td>
                                            <td class="table-user">
                                                <a href="javascript:void(0);" class="text-body">{{ $geo->name }}</a>

                                                <sup class="position-relative">
                                                    <a class="copy-icon ml-2" id="copy_icon" data-url="{{url('company').'/'.base64_encode($geo->id)}}" style="cursor:pointer;">
                                                        <i class="fa fa-copy"></i>
                                                    </a>
                                                    <h6 id="copy_message" class="copy-message mt-2"></h6>
                                                </sup>
                
                                            </td>
                                            <td class="table-user">
                                                <a href="javascript:void(0);" class="text-body">{{ $geo->email }}</a>
                                            </td>
                                            <td class="table-user">
                                                <a href="javascript:void(0);" class="text-body">{{ $geo->phone_number }}</a>
                                            </td>
                                            <td class="table-user">
                                                <a href="javascript:void(0);" class="text-body">{{ $geo->address }}</a>
                                            </td>

                                            <td>
                                                

                                                <button type="button"
                                                    class="btn btn-primary-outline action-icon editAreaBtn"
                                                    area_id="{{ $geo->id }}"><i
                                                        class="mdi mdi-square-edit-outline"></i></button>

                                                <form action="{{ route('company.delete') }}" method="POST"
                                                    class="action-icon">
                                                    @csrf
                                                    <input type="hidden" value="{{ $geo->id }}" name="area_id">
                                                    <button type="submit"
                                                        onclick="return confirm('Are you sure? You want to delete.')"
                                                        class="btn btn-primary-outline action-icon"><i
                                                            class="mdi mdi-delete"></i></button>

                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No areas found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination pagination-rounded justify-content-end mb-0">
                            {{-- $banners->links() --}}
                        </div>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
    </div>
    <div id="service-area-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add New Company') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form enctype="multipart/form-data" action="{{ route('company.add') }}" method="POST">
                    @csrf
                    <div class="modal-body mt-0" id="editCardBox">
                        <div class="row">

                            <div class="col-lg-12 mb-2">
                                {!! Form::label('title', __('Logo'), ['class' => 'control-label']) !!}
                                <input type="file" name="logo">
                            </div>
                        
                            <div class="col-lg-12 mb-2">
                                {!! Form::label('title', __('Name'), ['class' => 'control-label']) !!}
                                {!! Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Name', 'required' => 'required']) !!}
                            </div>
                            <div class="col-lg-12 mb-2">
                                {!! Form::label('title', __('Phone Number'), ['class' => 'control-label']) !!}
                                {!! Form::number('phone_number', '', ['class' => 'form-control', 'placeholder' => 'Number', 'required' => 'required']) !!}
                            </div>
                            <div class="col-lg-12 mb-2">
                                {!! Form::label('title', __('Email'), ['class' => 'control-label']) !!}
                                {!! Form::email('email', '', ['class' => 'form-control', 'placeholder' => 'Email', 'required' => 'required']) !!}
                            </div>
                        
                            <div class="col-lg-12 mb-2">
                                {!! Form::label('title', __('Address'), ['class' => 'control-label']) !!}
                                {!! Form::textarea('address', '', [
                                    'class' => 'form-control',
                                    'rows' => '3',
                                    'placeholder' => 'Address',
                                ]) !!}
                            </div>
                           
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit"
                                    class="btn btn-block btn-blue waves-effect waves-light w-100">{{ __('Save') }}</button>
                            </div>
                          
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="edit-area-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add Service Area') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="edit-area-form" enctype="multipart/form-data" action="" method="POST">
                    @csrf
                    <div class="modal-body" id="editAreaBox">
                        
                    </div>
                    <div class="modal-footer">
                        <div class="row mt-1">
                            <div class="col-12">
                                <button type="submit"
                                    class="btn btn-block btn-blue waves-effect waves-light">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
       
        function overlayClickListener(overlay) {
            google.maps.event.addListener(overlay, "mouseup", function(event) {
                $('#latlongs').val(overlay.getPath().getArray());
            });
        }
  
        /*                  EDIT       AREA        MODAL           */
        var CSRF_TOKEN = $("input[name=_token]").val();
        $(document).on('click', '.editAreaBtn', function() {
            var aid = $(this).attr('area_id');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "post",
                dataType: "json",
                url: "{{ route('company.edit') }}",
                data: {
                    _token: CSRF_TOKEN,
                    data: aid
                },
                success: function(data) {
                    document.getElementById("edit-area-form").action =
                        "{{ url('client/admin/updateCompany') }}" + '/' + aid;
                    $('#edit-area-form #editAreaBox').html(data.html);
                    $('#edit-area-modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }
            });
        });
        
        $('.openServiceModal').click(function() {
            $('#service-area-form').modal({
                keyboard: false
            });
        });

        $(".copy-icon").click(function(){
            var url = $(this).attr('data-url');
            var temp = $("<input>");
            $("body").append(temp);
            temp.val(url).select();
            document.execCommand("copy");
            temp.remove();
            sweetAlert.success('URL Copied!')
        });

    </script>
@endsection
