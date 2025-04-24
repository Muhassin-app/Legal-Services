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
        <div class="col-6">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Database Audit Log For ".$auditable_type) }}</h4>
            </div>
        </div>
        <div class="col-6">
            <div class="page-title-box text-right">
                <a href="{{route('databaseAuditingLogs')}}" target="_self" rel="noopener noreferrer">
                    <h4 class="page-title text-capitalize" style="color:#43bee1;">Return Back </h4>
                </a>
            </div>
        </div>
        <h3>Total Login : {{@$logsignIn}}</h3>
                    <h3>Total SignUp : {{@$logsignUp}}</h3>
                    <h3>Total paymentCall : {{@$paymentCall}}</h3>
                    <h3>Total orderCreated : {{@$orderCreated}}</h3>
    </div>
    <div class="row cms-cols al_custom_cms_page">
        <div class="col-md-12 col-xl-12 mb-2 cms-content">
            <div class="card">
                <div class="card-body p-3" id="edit_page_content">
                    <table class="table table-responsive" id="logTable">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">S.No.</th>
                            <th scope="col">User Name</th>
                            <th scope="col">Event</th>
                            <th scope="col">Old Values</th>
                            <th scope="col">New Values</th>
                            <th scope="col">URL</th>
                            <th scope="col">IP Address</th>
                            <th scope="col">User Agent</th>
                            <th scope="col">Created On</th>
                          </tr>
                        </thead>
                        <tbody>
                            @php
                                $logCount = 1;
                            @endphp
                            @foreach ($audits as $audit)
                                <tr>
                                    <th scope="row">{{$logCount}}</th>
                                    <td>
                                        @if ($audit->user)
                                            {{$audit->user->name}}
                                        @else
                                             N/A
                                        @endif
                                    </td>
                                    <td>{{$audit->event}}</td>
                                   <td>
                                       {{ json_encode($audit->old_values,JSON_PRETTY_PRINT) }}
                                    </td>
                                     <td> {{ json_encode($audit->new_values,JSON_PRETTY_PRINT) }} </td>
                                    <td> {{$audit->url}} </td>
                                    <td> {{$audit->ip_address}} </td>
                                    <td> {{$audit->user_agent}} </td>
                                    <td> {{$audit->created_at->diffForHumans()}} </td>
                                </tr>
                            @php
                                $logCount++;
                            @endphp
                          @endforeach
                        </tbody>
                      </table>
                      <div class="row">
                          <div class="col-12 mt-3">
                                {{$audits->links()}}
                          </div>
                      </div>
                       
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script>
          $('#logTable').DataTable();
    </script>
@endsection
