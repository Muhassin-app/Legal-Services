@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('Reasons', True)])
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
            <div class="col-md-2">
                <h4 class="page-title">{{ getNomenclatureName(__('Reasons'), true) }}</h4>
            </div>
            <div class="col-md-10 d-md-flex align-items-center justify-content-end mb-3">
                <button type="button" class="btn btn-info waves-effect waves-light text-sm-right addReasonBtn"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add Reason') }}</button>
            </div>
            <div class="col-12">
                @if(session()->has('success'))
                    <div class="alert alert-success">
                        {{ session()->get('success') }}
                    </div>
                @endif
                @if(session()->has('error'))
                    <div class="alert alert-error">
                        {{ session()->get('error') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-body position-relative">
                        <div class="table-responsive">
                            <table id="reason_table" class="table table-centered table-nowrap table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Action') }}</th>
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
    <div id="add-reason" class="modal fade add_reason" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add Reason') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="save_reason_form" method="post" enctype="multipart/form-data" action="{{ route('reason.store') }}">
                    @csrf
                    <input type="hidden" name="reason_id" value="">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" class="form-control" name="title" id="title" placeholder="Enter Title" required>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="form-group">
                                    <label for="reasonType">{{ __('Type') }}</label>
                                    <select class="form-control" name="type" id="reasonType" required>
                                        <option value="1">{{ __('Return')}}</option>
                                        <option value="2">{{ __('Exchange')}}</option>
                                        <option value="3">{{ __('Cancellation')}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info waves-effect waves-light submitReason">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="edit-reason" class="modal fade edit_reason" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Edit Reason') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="save_reason_form" method="post" enctype="multipart/form-data" action="{{ route('reason.store') }}">
                    @csrf
                    <input type="hidden" name="reason_id" id="reason_id" value="">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" class="form-control" name="title" id="title" placeholder="Enter Title" required>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="form-group">
                                    <label for="reasonType">Type</label>
                                    <select class="form-control" name="type" id="reasonType" required>
                                        <option value="1">Return</option>
                                        <option value="2">Exchange</option>
                                        <option value="3">Cancellation</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info waves-effect waves-light submitReason">{{ __('Submit') }}</button>
                    </div>
                </form>
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
                $('#reason_table').DataTable({
                    "lengthChange": false,
                    "searching": false,
                    "destroy": true,
                    "scrollX": true,
                    "processing": true,
                    "serverSide": true,
                    "iDisplayLength": 10,
                    "ajax": "{{ url('client/reason') }}",
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

                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                            //orderable: false,
                            searchable: false
                        },
                        {
                            data: 'title',
                            name: 'title',
                            orderable: false,
                            searchable: false,
                        },
                        {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false,
                        },
                        {
                            data: 'type',
                            name: 'type',
                            orderable: false,
                            searchable: false,
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
            $(".addReasonBtn").click(function(){
                $('#add-reason').modal();
            });
        });
        $(document).on('click', '.editReasonBtn', function(){
            var id = $(this).data('id');
            var title = $(this).data('title');
            var type = $(this).data('type');
            $('#edit-reason').modal();
            $('#edit-reason #title').val(title);
            $('#edit-reason #reason_id').val(id);
            $('#edit-reason #reasonType').val(type);
        });
    </script>
@endsection
@section('script')
    <script src="{{ asset('assets/libs/datatables/datatables.min.js') }}"></script>
@endsection
