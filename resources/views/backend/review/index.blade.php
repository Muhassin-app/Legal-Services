@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('Product Reviews', True)])
@section('css')
    <link href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/dropify/dropify.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/mohithg-switchery/mohithg-switchery.min.css') }}" rel="stylesheet" type="text/css" />
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

    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">{{ getNomenclatureName(__('Product Reviews'), true) }}</h4>
                </div>
            </div>
        </div>

    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body position-relative">

                        <div class="table-responsive">
                            <table id="review_table" class="table table-centered table-nowrap table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Product Name') }}</th>
                                        <th>{{ __('Average Rating') }}</th>
                                        <th>{{ __('Total Reviews') }}</th>
                                        <th>{{ __('View Reviews')}}</th>
                                        <th>{{ __('Edit Product') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="review_table_tbody_list">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });

            initDataTable();



            function initDataTable() {
                console.log('dasd');
                $('#review_table').DataTable({
                    "lengthChange": false,
                    "searching": false,
                    "destroy": true,
                    "scrollX": true,
                    "processing": true,
                    "serverSide": true,
                    "iDisplayLength": 10,
                    "ajax": "{{ url('client/review') }}",
                    drawCallback: function() {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    },
                    language: {
                        search: "",
                        info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        },
                        searchPlaceholder: '{{__("Search By Name")}}'
                    },


                    columns: [{
                            data: 'id',
                            name: 'id',
                            //orderable: false,
                            searchable: false
                        },
                        {
                            data: 'product_name',
                            name: 'product_name',
                            orderable: false,
                            searchable: false,

                        },
                        {
                            data: 'averageRating',
                            name: 'averageRating',
                            orderable: false,
                            searchable: false,

                        },
                        {
                            data: 'reviews_count',
                            name: 'reviews_count',
                            orderable: false,
                            searchable: false,
                        },
                        {
                            data: 'view_rating',
                            name: 'view_rating',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ]
                });

            }
        });
    </script>
@endsection
@section('script')
    <script src="{{ asset('assets/libs/datatables/datatables.min.js') }}"></script>
@endsection
