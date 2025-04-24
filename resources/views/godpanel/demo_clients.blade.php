@extends('layouts.god-vertical', ['title' => 'Clients'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Clients') }}</h4>
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
                                @if(\Session::has('success'))
                                <div class="alert alert-success">
                                    <span>{!! \Session::get('success') !!}</span>
                                </div>
                                @endif

                                @if(\Session::has('error'))
                                <div class="alert alert-error">
                                    <span>{!! \Session::get('error') !!}</span>
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="products-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __("Name") }}</th>
                                    <th>{{ __("Type") }}</th>
                                    <th>{{ __("Email") }}</th>
                                    <th>{{ __("Password") }}</th>
                                    <th>{{ __("Phone") }}</th>
                                    <th>{{ __("DB Name") }}</th>
                                    <th>{{ __("SUB Domain") }}</th>
                                    <th>{{ __("Client Code") }}</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clients as $client)
                                <tr id="tr_{{ $client->id }}">
                                    <td class="table-user">
                                        <a href="javascript:void(0);" class="text-body font-weight-semibold">{{$client->name}}</a>
                                    </td>
                                    <td> @if($client->client_type == 1) <span class="badge bg-success" style="color:#fff;">Live</span> @else <span class="badge bg-warning" style="color:#fff;">Demo</span> @endif </td>
                                    <td> {{$client->email}} </td>
                                    <td style="width:100px;max-width:100px;"> </td>
                                    <td> {{$client->phone_number}} </td>
                                    <td> {{$client->database_name}} </td>
                                    <td><a target="_blank" href="{{$client->sub_domain_url}}">{{$client->sub_domain }}{{env('SUBMAINDOMAIN') }}</a> </td>
                                    <td> {{$client->code}} </td>
                                    
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        
    }); 
</script>
@endsection
