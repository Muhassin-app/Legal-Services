@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Promocode'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
@php
$timezone = Auth::user()->timezone ? Auth::user()->timezone : 'UTC';
@endphp
<div class="container-fluid alPromocodePage">
    <div class="row align-items-center">
        <div class="col-sm-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
                <h4 class="page-title">{{ __('Promocode') }}</h4>
                <button class="btn btn-info waves-effect waves-light text-sm-right openPromoModal" userId="0"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }} </button>
            </div>
        </div>
    </div>
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
                                @if (\Session::has('Data_Updated'))
                                <div class="alert alert-success">
                                    <span>{!! \Session::get('Data_Updated') !!}</span>
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
                        <table class="table table-centered table-nowrap table-striped" id="promo-datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Image') }}</th>
                                    <th>{{ __('Promo Code') }}</th>
                                    <th style="width:150px">{{ __('Title') }}</th>
                                    <th style="width:150px">{{ __('Description') }}</th>
                                    <th>{{ __('Promo Types') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Expiry Date') }}</th>
                                    <!-- <th>Min Amount To Spend</th>
                                    <th>Max Amount To Spend</th>
                                    <th>Limit Per User</th>
                                    <th>Total Limit</th>
                                    <th>Restriction On</th>
                                    <th>Restriction Type</th> -->
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="post_list">
                                @foreach($promocodes as $promo)
                                <tr data-row-id="">
                                    <td class="draggableTd"><span class="dragula-handle"></span></td>
                                    <td>
                                        <img class="promo_img" src="{{$promo->image['proxy_url'].'200/100'.$promo->image['image_path']}}" alt="{{$promo->id}}" >
                                    </td>
                                    <td><a class="openPromoModal text-capitalize" userId="{{$promo->id}}" href="#"> {{$promo->name}}</a></td>
                                    <td style="width:100px"><p class="ellips">{{$promo->title}}</p></td>
                                    <td style="width:100px"><p class="ellips">{{$promo->short_desc}}</p></td>
                                    <td>{{$promo->type ? $promo->type->title : ''}}</td>
                                    <td>{{decimal_format($promo->amount)}}</td>
                                    <td>{{$promo->expiry_date}}</td>
                                    <td>
                                        @if($promo->added_by == Auth::id() || Auth::user()->is_superadmin == 1)
                                        <div class="form-ul" style="width: 60px;">
                                            <div class="inner-div" style="float: left;">
                                                <a class="action-icon openPromoModal" userId="{{$promo->id}}" href="#">
                                                    <i class="mdi mdi-square-edit-outline"></i>
                                                </a>
                                            </div>
                                            <div class="inner-div">
                                                <form method="POST" action="{{route('promocode.destroy', $promo->id) }}" id="deletePromoCode">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="form-group">
                                                        <button type="submit" id="deletePromoButton" class="btn btn-primary-outline action-icon">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{-- $promocode->links() --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('backend.promocode.modals')
@endsection
@section('script')
<script type="text/javascript">
    $('#deletePromoButton').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('You want to delete the Promocode.')}}",
                // icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                $("#deletePromoCode").off("submit").submit();
            }else{
                return false;
            }
        });
    });
</script>
@include('backend.promocode.pagescript')
@endsection