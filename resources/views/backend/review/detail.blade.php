@extends('layouts.vertical', ['demo' => 'creative', 'title' => __(getNomenclatureName('Ratings & Reviews', True))])
@section('css')
    <link href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/dropify/dropify.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/mohithg-switchery/mohithg-switchery.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/multiselect/multiselect.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/selectize/selectize.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/bootstrap-selectroyoorders/bootstrap-select.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/bootstrap-touchspin/bootstrap-touchspin.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/nestable2/nestable2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .error {
            color: red;
        }

        i.fa.fa-star.checked {
            color: gold;
        }

    </style>

@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ __(getNomenclatureName('Ratings & Reviews', true)) }}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card widget-inline">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="review-product-img">
                                @php
                                    $image = $product->media ? @$product->media->first()->image['path']['proxy_url'] . '74/100' . @$product->media->first()->image['path']['image_path'] : @$product->image['proxy_url'] . '74/100' . @$product->image['image_path'];
                                @endphp
                                <img src="{{ $image }}" class="img-fluid blur-up lazyloaded">
                            </div>
                            <div class="review-product-decsription">
                                <h5><b>{{ __('Product name') }}:</b>
                                    <span>{{ $product->translation_one->title ?? $product->sku }}</span>
                                </h5>
                                <h5><b>{{ __(getNomenclatureName('Vendor name',true)) }}:</b> <span>{{ $product->vendor->name }}</span>
                                </h5>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            @if($message = Session::get('success'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $message }}</strong>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card review-table-responsive">
                        <div class="card-body position-relative">

                            <div class="table-responsive ">
                                <table id="review_table" class="table table-centered table-nowrap table-striped"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Customer Name') }}</th>
                                            <th>{{ __('Review') }}</th>
                                            <th>{{ __('Rating') }}</th>
                                            <th>{{ __('Images') }}</th>
                                            <th>{{ __('Admin Approval') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="review_table_tbody_list">
                                        @foreach ($product->reviews as $key => $reviwe)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $reviwe->user->name }}</td>
                                                <td>{{ $reviwe->review }}</td>
                                                <td>
                                                    <i
                                                        class="fa fa-star {{ $reviwe->rating >= 1 ? 'checked' : '' }}"></i>
                                                    <i
                                                        class="fa fa-star {{ $reviwe->rating >= 2 ? 'checked' : '' }}"></i>
                                                    <i
                                                        class="fa fa-star {{ $reviwe->rating >= 3 ? 'checked' : '' }}"></i>
                                                    <i
                                                        class="fa fa-star {{ $reviwe->rating >= 4 ? 'checked' : '' }}"></i>
                                                    <i
                                                        class="fa fa-star {{ $reviwe->rating >= 5 ? 'checked' : '' }}"></i>
                                                </td>
                                                <td>
                                                    <div class="file-outer">
                                                        @foreach ($reviwe->reviewFiles as $k => $image)
                                                            <div class="review-images-file">
                                                                <img
                                                                    src="{{ $image['file']['image_fit'] . '74/100' . $image['file']['image_path'] }}" />
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class='form-ul'>
                                                        <div class='inner-div'>
                                                            <input type='checkbox' data-id='' id='ss' data-plugin='switchery' name='userAccountStatus' class='chk_box' data-color='#43bee1' checked>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class='form-ul'>
                                                        <div class='inner-div'>
                                                            <a href='{{ route('review.delete',[$reviwe->id]) }}' class='action-icon'>
                                                                <i class='mdi mdi-delete' title='Delete review'></i>
                                                             </a>
                                                           
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="row">
            @foreach ($product->reviews as $reviwe)
                <div class="col-xl- col-md-6 mt-3">
                    <div class="outer-box d-flex align-items-center justify-content-between px-0">
                        <div class="address-type w-100">
                            <div class="default_address border-bottom mb-1 px-2">
                                <h6 class="mt-0 mb-2">{{ $reviwe->user->name }}</h6>
                            </div>
                            <div class="px-2">
                                <p class="mb-1">{{ __('Product Reviwe') }}: {{ $reviwe->review }}</p>
                                <p class="mb-1">{{ $reviwe->user->name }}</p>
                                <h5><b>{{ __('Rating Stars') }}:</b>
                                    <span>
                                        <i class="fa fa-star {{ $reviwe->rating >= 1 ? 'checked' : '' }}"></i>
                                        <i class="fa fa-star {{ $reviwe->rating >= 2 ? 'checked' : '' }}"></i>
                                        <i class="fa fa-star {{ $reviwe->rating >= 3 ? 'checked' : '' }}"></i>
                                        <i class="fa fa-star {{ $reviwe->rating >= 4 ? 'checked' : '' }}"></i>
                                        <i class="fa fa-star {{ $reviwe->rating >= 5 ? 'checked' : '' }}"></i>
                                    </span>
                                </h5>
                                <h6 class="mt-0 mb-2">{{ __('Images uploaded by customers:') }}</h6>

                                @foreach ($reviwe->reviewFiles as $k => $image)
                                    <img src="{{ $image['file']['image_fit'] . '74/100' . $image['file']['image_path'] }}" />
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div> --}}

    </div>


@endsection
@section('script')
    {{-- <script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script> --}}
@endsection
