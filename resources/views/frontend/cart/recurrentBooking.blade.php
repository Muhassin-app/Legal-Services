@php
if($vendor_product->recurring_booking_type == 1){
    $booking_type = 'Daily';
}else if($vendor_product->recurring_booking_type == 2){
    $booking_type = 'Weekly';
}else if($vendor_product->recurring_booking_type == 3){
    $booking_type = 'Monthly';
}else if($vendor_product->recurring_booking_type == 4){
    $booking_type = 'Custom';
}

$firstday = explode(",",$vendor_product->recurring_day_data);
$r_schedule_datetime = '';
if(isset($firstday[0])){
    $r_schedule_datetime = $firstday[0];
    $r_schedule_datetime = date('Y-m-d', strtotime($firstday[0]));
    $time_s              = Carbon\Carbon::parse($vendor_product->recurring_booking_time)->format('g:i A' );
    $r_schedule_datetime = $r_schedule_datetime.'T'.$vendor_product->recurring_booking_time;
}
@endphp
<div class="row">
<div class="col-lg-12 left_box new_cart mt-4 p-3">
    <h5>Recurring Booking </h5>
    <table class="table table-bordered">
        <thead>
            <th>Type</th>
            <th>Days</th>
            @if($vendor_product->recurring_booking_type == 2)
            <th>Week Day</th>
            @endif
            <th>Time</th>
        </thead>
        <tbody>
            <tr>
            <td>{{ $booking_type }}</td>
            <td>{{ $vendor_product->recurring_day_data }}</td>
            @if($vendor_product->recurring_booking_type == 2)
                <td>{{ weekDaysArray($vendor_product->recurring_week_day) }} </td>
            @endif
            <td>
                {{ Carbon\Carbon::parse($vendor_product->recurring_booking_time)->format('g:i A' ) }}
            </td>
            <tr>
        </tbody>
    </table>
</div>
</div>