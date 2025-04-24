@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'QrCodes'])

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
<div class="container-fluid">

    <!-- start page title -->
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Qr Codes') }}</h4>
            </div>
        </div>
        <div class="col-sm-6 text-right">
            <button class="btn btn-info waves-effect waves-light text-sm-right"
                data-toggle="modal" data-target=".importQrcodeBtn"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Import QrCode') }}
            </button>
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
                                    <th>{{ __("Code") }}</th>
                                     {{-- <th>{{__("Vendor")}}</th> --}}
                                   {{-- <th>{{__("Action")}}</th> --}}
                                </tr>
                            </thead>
                            <tbody id="post_list">
                                @forelse ($codes as $item)
                                           <tr>
                                            <td>
                                                {{$item->code}}
                                            </td>
                                            {{-- <td>
                                                {{@$item->vendorDetail->name}}
                                            </td> --}}
                                           </tr>

                                           @empty
                                           <tr>
                                            <td colspan="5" class="text-center">
                                               No record found.
                                            </td>
                                           </tr>

                                       @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{$codes->links()}}
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
</div>

<div id="import-qrcode" class="modal fade importQrcodeBtn" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Import QR Codes') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>

                <div class="modal-body">
                    <div class="row">


                        <div class="col-md-12 text-center">

                            <div id="import_csv" class="row align-items-center mb-3">
                                
                                <div class="col-md-12">
                                    <form method="post" enctype="multipart/form-data" id="save_imported_qrcode">
                                        @csrf
                                        <a
                                            href="{{ url('file-download' . '/sample_qrcode.csv') }}">{{ __('Download Sample file here!') }}</a>
                               
                                        <input type="file" accept=".csv" onchange="submitQrcodeImportForm()"
                                            data-plugins="dropify" name="qrcode_excel" class="dropify" />
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap table-striped" id="">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('File Name') }}</th>
                                            <th colspan="2">{{ __('Status') }}</th>
                                            <th>{{ __('Link') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody >
                                        @foreach ($files as $csv)

                                        <tr data-row-id="{{ $csv->id }}">
                                            <td> {{ $loop->iteration }}</td>
                                            <td> {{ $csv->name }}</td>
                                            @if ($csv->status == 1)
                                                <td>{{ __('Pending') }}</td>
                                                <td></td>
                                            @elseif($csv->status == 2)
                                                <td>{{ __('Success') }}</td>
                                                <td></td>
                                            @else
                                                <td>{{ __('Errors') }}</td>
                                                <td class="position-relative text-center alTooltipHover">
                                                    <i class="mdi mdi-exclamation-thick"></i>
                                                    <ul class="tooltip_error">
                                                        <?php $error_csv = json_decode($csv->error); ?>
                                                        @foreach ($error_csv as $err)
                                                            <li>
                                                                {{ $err }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                            @endif
                                            <td> <a href="{{ $csv->storage_url }}">{{ __('Download') }}</a> </td>
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
    </div>

@endsection

@section('script')
<script src="{{ asset('assets/ck_editor/ckeditor.js')}}"></script>
<script type="text/javascript">
   
   $('.importQrcodeBtn').click(function() {
            $('#import-qrcode').modal({
                keyboard: false
            });
        });
 
        function submitQrcodeImportForm() {
        var form = document.getElementById('save_imported_qrcode');
        var formData = new FormData(form);
        var data_uri = "{{route('qrcode.import')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: data_uri,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // location.reload();
                if (response.status == 'success') {
                    $(".modal .close").click();
                    location.reload();
                } else {

                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            beforeSend: function() {

                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
                setTimeout(function() {
                    location.reload();
                }, 2000);
               
            }
        });
    }

</script>
@endsection