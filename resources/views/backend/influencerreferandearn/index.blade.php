@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Influencer'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
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
        <div class="col-6">
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
                        <div class="col-sm-4 text-right">
                            <a class="btn btn-info waves-effect waves-light text-sm-right"
                                href="{{route('influencer-refer-earn.create')}}"><i class="mdi mdi-plus-circle mr-1"></i> Add
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="products-datatable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Kyc</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($influencer_list as $value)
                                <tr>
                                    <td> {{ $value->name ?? '' }} </td>
                                    <td> {{ (@$value->kyc)?'Yes':'No' }} </td>
                                    <td> 
                                        {{-- <a class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? You want to delete the map provider.')" href="#"><i class="fa fa-trash"></i></a> --}}
                                        <a href="{{ route('influencer-refer-earn.edit', ['id' => $value->id]) }}"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                               @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{$influencer_list->links()}}
                    </div>

                    
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->

        <div class="col-6">
            <div class="">
                <div class="influencer-form-list">
                    <div class="">
                        <div class="card-box h-100">
                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="page-title">{{ ("Attribute") }}</h4>
                                        <button class="btn btn-info waves-effect waves-light text-sm-right addAttributbtn" dataid="0"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                                        </button>
                                    </div>
                                    <p class="sub-header">
                                        {{ __("Drag & drop Attribute to change the position") }}
                                    </p>
                                </div>
                            </div>
                            <div class="row variant-row">
                                <div class="col-md-12">
                                    <form name="variant_order" id="variant_order" action="{{route('variant.order')}}" method="post">
                                        @csrf
                                        <input type="hidden" name="orderData" id="orderVariantData" value="" />
                                    </form>
                                    <div class="table-responsive outer-box">
                                        <table class="table table-centered table-nowrap table-striped" id="varient-datatable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Category') }}</th>
                                                    <th>{{ __('Options') }}</th>
                                                    <th>{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                                @if(!empty($attributes))
                                                @foreach($attributes as $key => $variant)

                                                    @if(!empty($variant->translation_one))
                                                        <tr class="variantList" data-row-id="{{$variant->id}}">
                                                            <td>
                                                                <a class="editAttributeBtn" dataid="{{$variant->id}}" href="javascript:void(0);">{{$variant->title}}</a>
                                                            </td>
                                                            <td>
                                                            <a href="{{ route('influencer-refer-earn.edit', ['id' => $variant->influencerCategory[0]->id]) }}">{{$variant->influencerCategory[0]->name ?? ''}}</a>
                                                            </td>
                                                            <td>
                                                                @foreach($variant->option as $key => $value)
                                                                <label style="margin-bottom: 3px;">
                                                                    @if(isset($variant) && !empty($variant->type) && $variant->type == 2)
                                                                    <span style="padding:8px; float: left; border: 1px dotted #ccc; background:{{$value->hexacode}};"> </span>
                                                                    @endif
                                                                    &nbsp;&nbsp; {{$value->title}}</label> <br />
                                                                @endforeach
                                                            </td>
                                                            <td>
                                                                <a class="action-icon editAttributeBtn" dataid="{{$variant->id}}" href="javascript:void(0);">
                                                                    <i class="mdi mdi-square-edit-outline"></i>
                                                                </a>
                                                                @if( auth()->user()->is_superadmin )
                                                                <a class="action-icon deleteAttribute" dataid="{{$variant->id}}" href="javascript:void(0);">
                                                                    <i class="mdi mdi-delete"></i>
                                                                </a>
                                                                <form action="{{route('attribute-influencer-refer-earn.delete', $variant->id)}}" method="POST" style="display: none;" id="attrDeleteForm{{$variant->id}}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="action-icon btn btn-primary-outline" dataid="{{$variant->id}}" onclick="return confirm('Are you sure? You want to delete the attribute.')"> <i class="mdi mdi-delete"></i></button>
                                                                </form>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
    @include('backend.influencerreferandearn.tiers')
    </div>
</div>


{{-- Modal Section --}}
<div id="addAttributemodal" class="modal al fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add ".getNomenclatureName('Influencer Attribute')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="addAttributeForm" method="post" enctype="multipart/form-data" action="{{route('attribute-influencer-refer-earn.store')}}">
                @csrf
                <div class="modal-body" id="AddAttributeBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light addAttributeSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editAttributemodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Edit ".getNomenclatureName('Attribute')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="outter-loader d-none"><div class="css-loader"></div></div>
            <form id="editAttributeForm" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editAttributeBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light editAttributeSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- End of Modal Section --}}

@endsection

@section('script')
@include('backend.influencerreferandearn.pagescript')
<script type="text/javascript">
    
</script>
@endsection