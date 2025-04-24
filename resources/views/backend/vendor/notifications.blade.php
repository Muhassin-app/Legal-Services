@extends('layouts.vertical', ['demo' => 'Notifications', 'title' => 'Notifications'])

@section('content')
<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Notifications</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="" class="display" style="width:100%">
                            <thead>
                            <tr>
                               <th>Order Number</th>
                               <th>Message</th>
                               <th>Created Date</th>
                            </tr>
                            </thead>
                            <tbody>
                              @forelse ($notifications as $key => $noti)
                              <tr>
                                  <td><a href="/client/order/{{ $noti->order_id.'/'.$noti->vendor_id }}">#{{ $noti->order_number }}</a></td>
                                  <td>{!! $noti->message !!}</td>
                                  <td>{{ $noti->created_at }}</td>
                              </tr>
                              @empty

                              <tr>
                                <td colspan="10" class="text-center">No Record found.</td>
                              </tr>
                              @endforelse
                            </tbody>
                          </table>
                        
                    </div>
                  
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
</div>
@endsection