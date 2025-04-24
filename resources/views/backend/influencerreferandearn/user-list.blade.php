@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Influencer'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<style>
    div#kycmodal div#kycData .col-sm-6 img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    margin-bottom: 20px;
}
div#kycmodal div#kycData  span {
    font-size: 16px;
    font-weight: 400;
    margin-bottom:15px;
    color:#222;
}
div#kycmodal div#kycData span:last-child {
    float: right;
    font-weight: bold;
    color: #000;
}
</style>
@endsection

@section('content')

<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Influencer</h4>
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
                                @if (\Session::has('error_delete'))
                                <div class="alert alert-danger">
                                    <span>{!! \Session::get('error_delete') !!}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="products-datatable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Tier</th>
                                    <th>Refferal Code</th>
                                    <th>Commision Type</th>
                                    <th>Commision</th>
                                    <th>Kyc</th>
                                    <th>Approval</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($influencer_users as $influencer_user)
                                <tr>
                                    <td> {{ $influencer_user->user->name ?? '' }} </td>
                                    <td> {{ $influencer_user->tier->name ?? '' }} </td>
                                    <td> {{ $influencer_user->reffered_code ?? '' }} </td>
                                    <td>
                                        {{(!empty($influencer_user->commision_type) && $influencer_user->commision_type==1)?'Percentage':((!empty($influencer_user->commision_type) && $influencer_user->commision_type==2)?'Fixed':'-')}}
                                    </td>
                                    <td>
                                        {{$influencer_user->commision??'-'}}
                                    </td>
                                    <td>
                                        @if(@$influencer_user->kyc)
                                        <a class="action-icon KycBtn" dataid="{{$influencer_user->id}}" href="javascript:void(0);">
                                            <i class="mdi mdi-square-edit-outline"></i>
                                        </a>
                                        @endif
                                    </td>
                                    <td> @if($influencer_user->is_approved == 1)
                                        <!--  Approved --->
                                        {{_('Approved')}}
                                        @elseif($influencer_user->is_approved == 2)
                                        <!--  Rejected --->
                                        {{_('Rejected')}}
                                        @else
                                        <!--  Pending --->
                                        {{--_('Pending')--}}
                                        <a class="action-icon approveRejectTierBtn" dataid="{{$influencer_user->id}}" href="javascript:void(0);">
                                            <i class="mdi mdi-square-edit-outline"></i>
                                        </a>
                                        @endif
                                    </td>
                                    <td> {{(!empty($influencer_user->is_approved) && $influencer_user->is_approved==0)?'Inactive':((!empty($influencer_user->is_approved) && $influencer_user->is_approved==1)?'Active':'-')}} </td>
                                    <td>
                                        {{-- {{ route('influencer-refer-earn.edit', ['id' => $influencer_user->id]) }} --}}
                                        <a href="javascript:void(0);" data-id="{{$influencer_user->id}}" class="influencer_user_edit"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{$influencer_users->links()}}
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
</div>
<div id="approveRejectmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Edit ".getNomenclatureName('Attribute')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="outter-loader d-none">
                <div class="css-loader"></div>
            </div>
            <form id="approveRejectForm" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" id="approveRejectBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" name="approveRejectSubmit" value="1" class="btn btn-info waves-effect waves-light approveRejectSubmit">{{ __("Approve") }}</button>
                    <button type="button" name="approveRejectSubmit" value="2" class="btn btn-info waves-effect waves-light approveRejectSubmit rejectBtn">{{ __("Reject") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="kycmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __(getNomenclatureName('KYC')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="outter-loader d-none">
                <div class="css-loader"></div>
            </div>
                <div class="modal-body" id="kycData">

                </div>
        </div>
    </div>
</div>

<div id="editInfluencerUser" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Edit ".getNomenclatureName('Influencer')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form action="{{route('influencer-refer-earn.update-user-commision')}}" method="post">
                @csrf
                <div class="modal-body" id="editInfluencerUserBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" name="editInfluencerUser_btn" value="" class="btn btn-info waves-effect waves-light editInfluencerUserBtn">{{ __("Update") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')

<script type="text/javascript">
    $('.rejectBtn').on('click', function(e) {
        $('#tierdata').removeAttr('required');

        $('#approveRejectForm').submit();

    });
    $('.KycBtn').on('click', function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{url('client/influencer-user/getkycData')}}",
            data: {
                'did': did
            },
            dataType: 'json',
            beforeSend: function() {
                $(".loader_box").show();
            },
            success: function(data) {
                $('#kycmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $('#kycData').html(data.html);

                $('.selectize-select').selectize();
            },
            error: function(data) {
                console.log('data2');
            },
            complete: function() {
                $('.loader_box').hide();
            }
        });
    });
    // Edit Influencer Attribute
    $('.approveRejectTierBtn').on('click', function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{url('client/influencer-user/getUploadedData')}}",
            data: {
                'did': did
            },
            dataType: 'json',
            beforeSend: function() {
                $(".loader_box").show();
            },
            success: function(data) {
                $('#approveRejectmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $('#approveRejectForm #approveRejectBox').html(data.html);

                $('.selectize-select').selectize();


                document.getElementById('approveRejectForm').action = data.submitUrl;
            },
            error: function(data) {
                console.log('data2');
            },
            complete: function() {
                $('.loader_box').hide();
            }
        });
    });

    $(document).on('click', '.influencer_user_edit', function() {
        var influencer_user_id = $(this).data('id');
        $.ajax({
            headers: {
                'X-CSRF-Token': $('meta[name=_token]').attr('content')
            },
            url: "{{ route('influencer-refer-earn.editInfluencerUser') }}",
            type: 'GET',
            cache: false,
            data: {
                'influencer_user_id': influencer_user_id
            }, //see the $_token
            datatype: 'html',
            beforeSend: function() {
                //something before send
            },
            success: function(data) {
                if (data.success == true) {
                    $('#editInfluencerUser').modal();
                    $('#editInfluencerUserBox').html(data.html);
                } else {
                    $('#editInfluencerUserBox').text('Something went wrong');
                }
            },
            error: function(xhr, textStatus, thrownError) {
                alert(xhr + "\n" + textStatus + "\n" + thrownError);
            }
        });
    });
</script>
@endsection