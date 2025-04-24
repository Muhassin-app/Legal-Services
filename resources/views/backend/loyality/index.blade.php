@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('Loyalty Cards', True)])
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
<style>.error{color: red;}</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{getNomenclatureName( __('Loyalty Cards'), true)}}</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-12 d-flex justify-content-between">
                            <label class="mr-2 mb-0">{{ __('Enable') }}</label>
                            <input type="checkbox" id="activeCheck" {{$status == 0 ? 'checked' : ''}} data-plugin="switchery" name="validity_index" class="chk_box1 " data-color="#43bee1">
                        </div>
                        <div class="col-12">
                            <form id="setRedeem">
                            @csrf
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group mb-0">
                                            <h4 class="header-title">{{ __('Redemption Value') }}</h4>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text primaryKey" id="basic-addon1">
                                                        @if(isset($client_cur) && !empty($client_cur->currency))
                                                            {{$client_cur->currency->symbol ?? ''}}
                                                        @endif
                                                    </span>
                                                </div>
                                                <input type="text" onkeypress="return isNumberKey(event);" class="form-control" name="redeem_points_per_primary_currency" id="redeem_points_per_primary_currency" placeholder="Value" aria-label="Username" aria-describedby="basic-addon1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <button type="button" class="btn btn-primary setredeempoints w-100">{{ __('Save changes') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9 mb-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-12 text-right">
                            <button class="btn btn-info waves-effect waves-light text-sm-right"
                              data-toggle="modal" data-target=".addModal"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <form name="saveOrder" id="saveOrder"> @csrf </form>
                        <table class="table table-centered table-nowrap table-striped" id="banner-datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Minimum Points') }}</th>
                                    <th>{{ __('Earnings Per Order') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="post_list">
                                @foreach($loyaltycards as $ban)
                                <tr data-row-id="{{$ban->id}}">
                                    <td class="draggableTd">
                                        <span class="dragula-handle"></span>
                                    </td>
                                    <td>
                                        <img src="{{$ban->image['proxy_url'].'40/40'.$ban->image['image_path']}}" class="rounded-circle" alt="{{$ban->name}}" >
                                    </td>
                                    <td><a class="openEditModal" loyaltyID="{{$ban->id}}" href="#"> {{ $ban->name }} </a></td>
                                    <td> {{ Str::limit($ban->description, 50, ' ...') }} </td>
                                    <td> {{ $ban->minimum_points }} </td>
                                    <td> {{ $ban->per_order_points }} </td>
                                    <td>
                                        <input type="checkbox" bid="{{$ban->id}}" id="activeCheck" data-plugin="switchery" name="validity_index" class="chk_box" data-color="#43bee1" {{($ban->status == '0') ? 'checked' : ''}} >
                                     </td>
                                    <td>
                                        <div class="form-ul" style="width: 60px;">
                                            <div class="inner-div" style="float: left;">
                                                <a class="action-icon openEditModal" loyaltyID="{{$ban->id}}" href="#"><i class="mdi mdi-square-edit-outline"></i></a>
                                            </div>
                                            <div class="inner-div">
                                                <form method="POST" action="{{ route('loyalty.destroy', $ban->id) }}" id="deleteLoyality{{$ban->id}}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="form-group">
                                                       <button type="button" class="btn btn-primary-outline action-icon" id="deleteLoyalityButton" onclick="deleteloyality('{{$ban->id}}')"><i class="mdi mdi-delete"></i></button>
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
                        {{-- $loyaltycards->links() --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('backend.loyality.modals')
@endsection
@section('script')
<script type="text/javascript">
    function deleteloyality(id)
    {
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"You want to delete the {{getNomenclatureName('Loyalty Cards', false)}}.",
                // icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                $("#deleteLoyality"+id).off("submit").submit();
            }else{
                return false;
            }
        });
    }
</script>
@include('backend.loyality.pagescript')
@endsection
