@extends('layouts.vertical', ['title' => 'Database Audit Logs'])
@section('content')
@section('css')
    <style>
        .table .thead-dark th{
            color:white !important;
        }
        #logTable_info{
            display:none !important;
        }
        #logTable_paginate{
            display: none !important;
        }
        #logTable_length{
            display: none !important;
        }
    </style>
@endsection
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Database Audit Logs") }}</h4>
            </div>
        </div>
    </div>
    <div class="row cms-cols al_custom_cms_page">
        <div class="col-md-3 col-xl-3 mb-2">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4>{{ __("Log Tables") }}</h4>
                           
                    </div>
                    <h3>{{ __('Total Login') }} : {{@$logsignIn}}</h3>
                    <h3>{{ __('Total SignUp') }} : {{@$logsignUp}}</h3>
                    <h3>{{ __('Total paymentCall')}} : {{@$paymentCall}}</h3>
                    <h3>{{ __('Total orderCreated')}} : {{@$orderCreated}}</h3>

                   <div class="custom-dd-empty dd home-options-list" id="pickup_page_datatable">
                        <a href="#" target="_self" style="color:#095cd2;">
                            <nav class="navbar navbar-light bg-light mt-2" >
                                <span>
                                    1. Authentication
                                </span>
                                <span>
                                        <svg style="height:14px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14.34 12"><defs><style>.cls-1{fill:#6e768e;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M2.69,10.8c-.29,0-.58,0-.86,0a.6.6,0,0,1-.63-.64V9.31a.63.63,0,0,0-.42-.58.61.61,0,0,0-.65.2A2.53,2.53,0,0,0,0,9.15v1.29l0,.08a1.67,1.67,0,0,0,1.34,1.41,5.84,5.84,0,0,0,1.45,0,.57.57,0,0,0,.34-.21.54.54,0,0,0,.07-.61A.58.58,0,0,0,2.69,10.8Z"/><path class="cls-1" d="M0,2.9a.6.6,0,0,0,.69.41.61.61,0,0,0,.48-.65V1.88a.59.59,0,0,1,.64-.65h.77a1.09,1.09,0,0,0,.26,0A.59.59,0,0,0,3.28.56.55.55,0,0,0,2.75,0,9.55,9.55,0,0,0,1.68,0,1.7,1.7,0,0,0,.22,1,4.44,4.44,0,0,0,0,1.58V2.87Z"/><path class="cls-1" d="M11.91,5.84a4.17,4.17,0,0,0-1.54-2.36A5.09,5.09,0,0,0,4.75,3,4.32,4.32,0,0,0,2.41,5.89a.57.57,0,0,0,0,.26,4.09,4.09,0,0,0,.78,1.61A4.87,4.87,0,0,0,7,9.65a5.43,5.43,0,0,0,2.15-.39,4.55,4.55,0,0,0,2.76-3A.77.77,0,0,0,11.91,5.84Zm-4.74,2A1.8,1.8,0,1,1,9,6,1.82,1.82,0,0,1,7.17,7.81Z"/><path class="cls-1" d="M11.67,1.23c.28,0,.57,0,.85,0a.58.58,0,0,1,.61.63c0,.29,0,.58,0,.87a.6.6,0,1,0,1.19,0v-.9A1.76,1.76,0,0,0,13,.1a5.54,5.54,0,0,0-.83-.1V0h-.61a.49.49,0,0,0-.41.23.58.58,0,0,0-.06.62A.6.6,0,0,0,11.67,1.23Z"/><path class="cls-1" d="M14.32,9.18a0,0,0,0,0,0,0,.59.59,0,0,0-1.17.15c0,.28,0,.57,0,.85a.59.59,0,0,1-.64.65h-.77a1.09,1.09,0,0,0-.26,0,.57.57,0,0,0-.4.64.56.56,0,0,0,.53.52h.94a1.8,1.8,0,0,0,1.73-1.28A5.69,5.69,0,0,0,14.32,9.18Z"/></g></g></svg>
                                </span>
                            </nav>
                        </a>

                       @php
                           $count = 2;
                       @endphp
                       @foreach ($audits as $audit)
                       @php
                           $name = substr($audit->auditable_type, strrpos($audit->auditable_type, "\\") + 1);
                       @endphp
                        <a href="{{route("singleDatabaseAuditingLogs",['table_name'=>strtolower($name)])}}" target="_self">
                                <nav class="navbar navbar-light bg-light mt-2" >
                                    <span>
                                        {{$count.'.'.$name}}
                                    </span>
                                    <span>
                                            <svg style="height:14px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14.34 12"><defs><style>.cls-1{fill:#6e768e;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M2.69,10.8c-.29,0-.58,0-.86,0a.6.6,0,0,1-.63-.64V9.31a.63.63,0,0,0-.42-.58.61.61,0,0,0-.65.2A2.53,2.53,0,0,0,0,9.15v1.29l0,.08a1.67,1.67,0,0,0,1.34,1.41,5.84,5.84,0,0,0,1.45,0,.57.57,0,0,0,.34-.21.54.54,0,0,0,.07-.61A.58.58,0,0,0,2.69,10.8Z"/><path class="cls-1" d="M0,2.9a.6.6,0,0,0,.69.41.61.61,0,0,0,.48-.65V1.88a.59.59,0,0,1,.64-.65h.77a1.09,1.09,0,0,0,.26,0A.59.59,0,0,0,3.28.56.55.55,0,0,0,2.75,0,9.55,9.55,0,0,0,1.68,0,1.7,1.7,0,0,0,.22,1,4.44,4.44,0,0,0,0,1.58V2.87Z"/><path class="cls-1" d="M11.91,5.84a4.17,4.17,0,0,0-1.54-2.36A5.09,5.09,0,0,0,4.75,3,4.32,4.32,0,0,0,2.41,5.89a.57.57,0,0,0,0,.26,4.09,4.09,0,0,0,.78,1.61A4.87,4.87,0,0,0,7,9.65a5.43,5.43,0,0,0,2.15-.39,4.55,4.55,0,0,0,2.76-3A.77.77,0,0,0,11.91,5.84Zm-4.74,2A1.8,1.8,0,1,1,9,6,1.82,1.82,0,0,1,7.17,7.81Z"/><path class="cls-1" d="M11.67,1.23c.28,0,.57,0,.85,0a.58.58,0,0,1,.61.63c0,.29,0,.58,0,.87a.6.6,0,1,0,1.19,0v-.9A1.76,1.76,0,0,0,13,.1a5.54,5.54,0,0,0-.83-.1V0h-.61a.49.49,0,0,0-.41.23.58.58,0,0,0-.06.62A.6.6,0,0,0,11.67,1.23Z"/><path class="cls-1" d="M14.32,9.18a0,0,0,0,0,0,0,.59.59,0,0,0-1.17.15c0,.28,0,.57,0,.85a.59.59,0,0,1-.64.65h-.77a1.09,1.09,0,0,0-.26,0,.57.57,0,0,0-.4.64.56.56,0,0,0,.53.52h.94a1.8,1.8,0,0,0,1.73-1.28A5.69,5.69,0,0,0,14.32,9.18Z"/></g></g></svg>
                                    </span>
                                </nav>
                            </a>
                            @php
                                $count++;
                            @endphp
                        @endforeach
                   </div>
                </div>
            </div>
        </div>
        <div class="col-md-9 col-xl-9 mb-2 cms-content">
            <div class="card">
                <div class="card-body p-3" id="edit_page_content">
                    <table class="table table-responsive" id="logTable">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{ __("User Name")}}</th>
                            <th scope="col">{{ __("IP Address")}}</th>
                            <th scope="col">{{ __("User Agent")}}</th>
                            <th scope="col">{{ __("Login At")}}</th>
                            <th scope="col">{{ __("Logout At")}}</th>
                          </tr>
                        </thead>
                        <tbody>
                            @php
                                $logCount = 1;
                            @endphp
                            @foreach ($authenticationLogs as $authenticationLog)
                                <tr>
                                    <th scope="row">{{$logCount}}</th>
                                    <td>
                                        @if($authenticationLog->user)
                                             {{$authenticationLog->user->name}}
                                        @else
                                             N/A
                                        @endif
                                    </td>
                                    <td>{{$authenticationLog->ip_address}}</td>
                                    <td>{!! $authenticationLog->user_agent !!}</td>
                                    <td>
                                        @if ($authenticationLog->login_at == "")
                                            N/A
                                        @else
                                            {{$authenticationLog->login_at}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($authenticationLog->logout_at == "")
                                            Still logged in
                                        @else
                                            {{$authenticationLog->logout_at}}
                                        @endif
                                    </td>
                                </tr>
                            @php
                                $logCount++;
                            @endphp
                          @endforeach
                        </tbody>
                      </table>
                      {{$authenticationLogs->links()}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $('#logTable').DataTable({
        "pageLength": 25
    });
</script>
@endsection
