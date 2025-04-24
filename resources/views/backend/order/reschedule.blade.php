@extends('layouts.vertical', ['title' => 'Orders'])
@section('content')
    @php
    $timezone = Auth::user()->timezone;
    @endphp
    <style type="text/css">
        .ellipsis {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

    </style>
    <div class="container-fluid">

        <!-- Return Page Tabbar start from here -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title">{{ __('Rescheduled Orders') }}</h4>
                </div>
            </div>
        </div>
        <div class="row mb-lg-5">
            <div class="col-sm-12 col-lg-12 tab-product pt-0">
                <div class="tab-content nav-material" id="top-tabContent">
                    <div class="tab-pane fade show active" id="rescheduled" role="tabpanel"
                        aria-labelledby="rescheduled-tab">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="w-100 common-table table-striped rescheduledTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('S. No.') }}</th>
                                                <th>{{ __('Order id') }}</th>
                                                <th>{{ __('Reschedule By') }}</th>
                                                <th>{{ __('Vendor') }}</th>
                                                <th>{{ __('Previous Pickup') }}</th>
                                                <th>{{ __('Previous Pickup Slot') }}</th>
                                                <th>{{ __('Previous Dropoff') }}</th>
                                                <th>{{ __('Previous Dropoff Slot') }}</th>
                                                <th>{{ __('New Pickup') }}</th>
                                                <th>{{ __('New Pickup Slot') }}</th>
                                                <th>{{ __('New Dropoff') }}</th>
                                                <th>{{ __('New Dropoff Slot') }}</th>
                                                <th>{{ __('Modified on') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($rescheduleOrders) && count($rescheduleOrders))
                                                @php
                                                    $count = 1;
                                                @endphp
                                                @foreach ($rescheduleOrders as $rescheduleOrder)
                                                    <tr data-id="{{ $rescheduleOrder->id }}" data-status="rescheduled">
                                                        <td>
                                                            {{ $count }}
                                                        </td>
                                                        <td>
                                                            #{{ $rescheduleOrder->order->order_number }}
                                                        </td>
                                                        <td>
                                                            <a href="#">{{ $rescheduleOrder->user->name }}</a>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('vendor.catalogs', $rescheduleOrder->vendor->id) }}">{{ $rescheduleOrder->vendor->name }}</a>
                                                        </td>
                                                        <td class="product-name">
                                                            {{ $rescheduleOrder->prev_schedule_pickup }}
                                                        </td>
                                                        <td class="">
                                                            {{ $rescheduleOrder->prev_scheduled_slot }}
                                                        </td>
                                                        <td class="">
                                                            {{ $rescheduleOrder->prev_schedule_dropoff }}
                                                        </td>
                                                        <td class="">
                                                            {{ $rescheduleOrder->prev_dropoff_scheduled_slot }}
                                                        </td>

                                                        <td class="product-name">
                                                            {{ $rescheduleOrder->new_schedule_pickup }}
                                                        </td>
                                                        <td class="">
                                                            {{ $rescheduleOrder->new_scheduled_slot }}
                                                        </td>
                                                        <td class="">
                                                            {{ $rescheduleOrder->new_schedule_dropoff }}
                                                        </td>
                                                        <td class="">
                                                            {{ $rescheduleOrder->new_dropoff_scheduled_slot }}
                                                        </td>
                                                        <td>
                                                            {{ dateTimeInUserTimeZone($rescheduleOrder->created_at, $timezone) }}
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $count++;
                                                    @endphp
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="7">{{ __('No Records Found') }}</td>
                                                </tr>
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
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.rescheduledTable').DataTable();
        });
    </script>
@endsection
