@if (isset($vendor->products[0]->recurringService) && count($product->recurringService) > 0)
<div class="outer_div col-12 mb-2">
<div class="outer_divLongTermBox w-100">
 <a href="javascript:;" class="mt-1 recurring-btn btn btn-primary btn-sm rounded" data-id="{{$order->id}}" >{{ __('Recurring Schedule Service') }}</a>
    <div class="col-12 recurringClass-{{$order->id}} mt-2" style="display:none">
        <table class="wp-table w-100">
            <tr>
                <th>#</th>
                <th>{{ __('Scheduled date time') }}
                </th>
                <th>{{ __('Status') }}</th>
            </tr>
            @foreach ($product->recurringService as $key => $schedule)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td><a
                            href="javascript:void(0)">{{ date('d M Y h:i A', strtotime(dateTimeInUserTimeZone($schedule->schedule_date, $timezone))) }}</a>
                    </td>
                    <td> <span
                            class="badge {{ $schedule->status == 0 ? 'badge-info' : 'badge-success' }}  mr-2">{{ $schedule->status == 0 ? __('Pending') : __('Complete') }}</span>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
</div>
@endif
