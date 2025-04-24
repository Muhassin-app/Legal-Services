@extends('layouts.vertical', ['demo' => 'creative', 'title' => getNomenclatureName('Slots', True)])
@section('css')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <link href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/dropify/dropify.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/mohithg-switchery/mohithg-switchery.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/multiselect/multiselect.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/selectize/selectize.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/bootstrap-selectroyoorders/bootstrap-select.min.css') }}" rel="stylesheet" type="text/css" />
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
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h3 class="page-title">{{ getNomenclatureName(__('Same Day Delivery Slots'), true) }}</h3>
                    </div>
                    <div class="col-md-6">
                        <div class="al_new_export_table royo_customber_btn table_customber_add">
                            <div class="position-absolute mb-2">
                                <button type="button" class="btn btn-info waves-effect waves-light text-sm-right addSlotBtn" data-pincode="" data-id=""><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add Delivery Slot') }}</button>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body position-relative">
                        <div class="table-responsive">
                            <table id="slot_table" class="table table-centered table-nowrap table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Start Time') }}</th>
                                        <th>{{ __('End Time') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Duration In Minute') }}</th>
                                        <th>{{ __('CutOff Time') }}</th>
                                        <th>{{ __('Status') }}</th>
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
    <div id="add-edit-slot" class="modal fade add_reason" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add Slot') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="save_slot_form" method="post" enctype="multipart/form-data" action="{{ route('delivery-slot.store') }}">
                    @csrf
                    <input type="hidden" name="slot_id" id="slot_id" value="" />
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" class="form-control" name="slot_title" id="slot_title" placeholder="Enter Slot Title" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <input type="number" class="form-control" name="price" id="price" placeholder="Enter Slot Price" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time">Start Time</label>
                                    <input type="time" class="form-control" name="start_time" id="start_time" placeholder="Enter Start Time" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">End Time</label>
                                    <input type="time" class="form-control" name="end_time" id="end_time" placeholder="Enter End Time" value="" required>
                                </div>
                            </div>
                            <div class="col-md-6" style="z-index: 9999999;">
                                <div class="form-group" id="cutOff_timeInput">
                                    {!! Form::label('title', __('Cut Off Time'),['class' => 'control-label']) !!}
                                    <input class="form-control timepicker" name="cutoff_time" type="text" placeholder="Cut off time" value="" min="0" required/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group w-100">
                                {!! Form::label('title', __('Slot Duration (In minutes)'),['class' => 'control-label']) !!}
                                    <select class="form-control" name="slot_minutes" id="slot_minutes" required>
                                        <option value="">{{__('Slot Duration')}}</option>
                                        <option value="15">15 {{__(' Minutes')}}</option>
                                        <option value="30">30 {{__(' Minutes')}}</option>
                                        {{-- <option value="45">45 {{__(' Minutes')}}</option> --}}
                                        @for($i=1;$i<=3;$i++)
                                            <option value="{{$i*60}}">{{ $i. __(' Hour')}}</option>
                                        @endfor
                                        {{--  {{$vendor->slot_minutes == ($i*60)? 'selected':''}} --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info waves-effect waves-light submitSlot">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });
            initDataTable();
            
            function initDataTable() {
                $('#slot_table').DataTable({
                    "lengthChange": false,
                    "searching": false,
                    "destroy": true,
                    "scrollX": true,
                    "processing": true,
                    "serverSide": true,
                    "iDisplayLength": 10,
                    "ajax": "{{ url('client/delivery-slot') }}",
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
                        searchPlaceholder: '{{__("Search Here")}}'
                    },

                    columns: [
                        {data: 'id',name: 'id',orderable: false,searchable: false},
                        {data: 'title',name: 'title',orderable: false,searchable: false},
                        {data: 'start_time',name: 'start_time',orderable: false,searchable: false},
                        {data: 'end_time',name: 'end_time',orderable: false,searchable: false},
                        {data: 'price',name: 'price',orderable: false,searchable: false},
                        {data: 'slot_interval',name: 'slot_interval',orderable: false,searchable: false},
                        {data: 'cutOff_time',name: 'cutOff_time',orderable: false,searchable: false},
                        {data: 'status',name: 'status',orderable: false,searchable: false},
                        {data: 'action',name: 'action',orderable: false,searchable: false}
                    ]
                });
            }
            $(document).on('click', '.addSlotBtn', function(){
                var slot_id = $(this).data('id');
                if(slot_id != ''){
                    $('#slot_id').val(slot_id);
                    $('#slot_title').val($(this).data('title'));
                    $('#start_time').val($(this).data('start-time'));
                    $('#end_time').val($(this).data('end-time'));
                    $('#price').val($(this).data('price'));
                    $("select#slot_minutes").val($(this).data('slot-duration')).attr('selected','selected');
                    $('#add-edit-slot .modal-title').text("Edit Slot");
                    $('.timepicker').val($(this).data('cut-off-time'));
                }else{
                    $('#slot_id').val('');
                    $('#slot_title').val('');
                    $('#start_time').val('');
                    $('#end_time').val('');
                    $('#price').val('');
                    $("select#slot_minutes").val('');
                    $('#add-edit-slot .modal-title').text("Add Slot");
                    $('.timepicker').val('');
                }
                $('#add-edit-slot').modal();
            });
            $('.timepicker').timepicker({                
                timeFormat: 'HH:mm',
                interval: 60,
                inline: true,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
        });
    </script>
@endsection
@section('script')
    <script src="{{ asset('assets/libs/datatables/datatables.min.js') }}"></script>
@endsection
