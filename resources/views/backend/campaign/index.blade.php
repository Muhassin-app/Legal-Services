@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Campaigns'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/mohithg-switchery/mohithg-switchery.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/multiselect/multiselect.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/selectize/selectize.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/bootstrap-selectroyoorders/bootstrap-select.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/bootstrap-touchspin/bootstrap-touchspin.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/nestable2/nestable2.min.css')}}" rel="stylesheet" type="text/css" />
<style>.error{color: red;}
.descript {
    max-width: 200px;
    white-space: nowrap !important;
    overflow: hidden;
    text-overflow: ellipsis;
}

</style>
@endsection

@section('content')

<!-- Start Content-->
<div class="container-fluid alCampaignsPage">

    <!-- start page title -->
    <div class="row align-items-center">
        <div class="col-sm-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
                <h4 class="page-title">{{ __('Campaigns') }}</h4>
                <button class="btn btn-info waves-effect waves-light text-sm-right"
                    data-toggle="modal" data-target=".addModal"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                </button>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-12">
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
                        <form name="saveOrder" id="saveOrder"> @csrf </form>
                        <table class="table table-centered table-nowrap table-striped" id="celeb-datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __("Title") }}</th>
                                    <th>{{__("Type")}}</th>
                                    <th>{{ __("URL Option") }}</th>
                                    <th>{{__("Send to")}}</th>
                                    <th>{{__("Schedule Time")}}</th>
                                    <th>{{__("Request User Count")}}</th>
                                    <th>{{__("Request Time Difference")}}</th>
                                    <th>{{__("Total Request Count")}}</th>
                                    <th>{{__("Live Count")}}</th>
                                    <th>{{__("Action")}}</th>
                                </tr>
                            </thead>
                            <tbody id="post_list">
                                @foreach($campaigns as $campaign)

                                <tr data-row-id="{{$campaign->id}}">
                                    <!-- <td class="draggableTd"><span class="dragula-handle"></span></td> -->
                                    <td>
                                    </td>
                                    {{-- <td><a class="openEditModal text-capitalize" loyaltyID="{{$campaign->id}}" href="#">{{ $campaign->title }}</a> </td> --}}
                                    <td class="text-capitalize">{{ $campaign->title }} </td>
                                    <td class="descript">
                                        @if ($campaign->type==1)
                                            {{__('SMS')}}
                                        @elseif($campaign->type==2)
                                            {{__('Email')}}
                                        @else
                                            {{__('Push Notification')}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($campaign->push_url_option==1)
                                        {{__('URL')}}
                                        @elseif($campaign->push_url_option==2)
                                        {{__('Category')}}
                                        @elseif($campaign->push_url_option==3)
                                        {{__('Vendor')}}
                                        @else
                                        @endif
                                    </td>
                                    <td>
                                        @if($campaign->send_to==1)
                                            {{__('All')}}
                                        @else
                                            {{__('Vendors')}}
                                        @endif
                                    </td>
                                    <td>
                                        {{$campaign->schedule_datetime}}
                                    </td>
                                    <td>
                                        {{$campaign->request_user_count}}
                                    </td>
                                    <td>
                                        {{$campaign->request_time_difference}}
                                    </td>
                                    <td>
                                        {{$campaign->total_request_count}}
                                    </td>
                                    <td>
                                        {{$campaign->livecount}}
                                    </td>
                                    <td>
                                        <div class="form-ul" style="width: 60px;">
                                            {{-- <div class="inner-div" style="float: left;">
                                                <a class="action-icon openEditModal" loyaltyID="{{$campaign->id}}" href="#"><i class="mdi mdi-square-edit-outline"></i></a>
                                            </div> --}}
                                            <div class="inner-div">
                                                <form method="POST" action="{{ route('campaign.destroy', $campaign->id) }}" id="deleteCampaign{{ $campaign->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
                                                    <div class="form-group">
                                                       <button type="button" id="deleteCampaignButton" class="btn btn-primary-outline action-icon deleteCampaignButton" data-id="{{ $campaign->id }}"><i class="mdi mdi-delete"></i></button> 
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                               @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{-- $campaigns->links() --}}
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
</div>

@include('backend.campaign.modals')
@endsection

@section('script')
<script src="{{ asset('assets/ck_editor/ckeditor.js')}}"></script>
<script type="text/javascript">
    $('.deleteCampaignButton').click(function(e) {
        var campId=$(this).attr('data-id');
        e.preventDefault();
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('You want to delete the campaign.')}}",
                // icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                $(`#deleteCampaign${campId}`).submit();
            }else{
                return false;
            }
        });
    });

    $('input[type="radio"][name="type"]').click(function(){
    if ($(this).is(':checked'))
    {
      var type = $(this).val();
      if(type==1)
      {
        $('.sms-section').css('display','');
        $('.email-section, .push-section').css('display','none');
        // $('#email_title, #email_subject, #email_body').val('');
        // $('#push_title, #push_message_body, #push_url_option, #push_url_option_value').val('');
      }else if(type==2){
        $('.email-section').css('display','');
        $('.sms-section, .push-section').css('display','none');
        // $('#sms_text, #push_title, #push_message_body, #push_url_option, #push_url_option_value').val('');
      }else if(type==3){
        $('.push-section').css('display','');
        $('.sms-section, .email-section').css('display','none');
        // $('#sms_text, #email_title, #email_subject, #email_body').val('');
      }
    }
  });

  $('#push_url_option').on('change', function() {
    var pushvalue = this.value;
    if(pushvalue==1)
    {
        $('.push_url_option_value').html('');
        var pushvaluehtml = '<input class="form-control" placeholder="" name="push_url_option_value" type="text" id="push_url_option_value">';
        $('.push_url_option_value').html(pushvaluehtml);
    }else{
        $.ajax({
            type: "get",
            url: "{{route('campaign.pushoptions')}}",
            data: {'pushvalue':pushvalue},
            dataType: 'json',
            success: function(data) {
               $('.push_url_option_value').html('');
               $('.push_url_option_value').html(data.html);
            },
            error: function(data) {
                console.log('data2');
            }
        });
    }
  });
</script>

@include('backend.campaign.pagescript')
<script>
    CKEDITOR.replace('email_body');

</script>
@endsection