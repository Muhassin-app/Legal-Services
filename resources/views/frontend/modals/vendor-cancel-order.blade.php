<form id="addRejectForm" method="post" class="text-center" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="reason">Select Reason</label>
        <select class="form-control" id="return_reason_id" name="return_reason_id">
            @foreach ($cancellation_reason as $reason)
            <option value="{{$reason->id}}">{{$reason->title}}</option>
            @endforeach
        </select>
    </div>
    <p id="error-case" style="color:red;"></p>
    <label style="font-size:medium;">Enter reason for cancel the order. <small>(Optional)</small> </label>
    <textarea class="reject_reason w-100" data-name="reject_reason" name="reject_reason" id="reject_reason" cols="50" rows="5"></textarea>
    @if($orderCancellationPercentage > 0)
    <p id="order-cancelletion-message" class="text-danger"><i class="fa fa-info-circle" aria-hidden="true"></i> By cancelling this order, <span id="order-cancelletion-percentages">{{$orderCancellationPercentage}}%</span> amount will be deducted as a refund for the order.</p>
    @endif
    <button type="button" class="btn btn-info waves-effect waves-light addrejectSubmit">{{ __("Submit") }}</button>

</form>

<script>
    $('.addrejectSubmit').on('click', function(e) {
        console.log('clicked');
        e.preventDefault();
        var return_reason_id = $('#return_reason_id').val();
        var reject_reason = $('#reject_reason').val();
        var pickup_cancelling_charges = "{{@$pickup_cancelling_charges}}";
        var pickup_order_date = "{{@$pickup_order_date}}";
        var order_number = "{{@$order_number}}";
        var product_id = "{{@$order_vendor->products[0]->product_id??0}}";
        var order_id = "{{$order_vendor->order_id??0}}";
        var vendor_id = "{{$order_vendor->vendor_id??0}}";
        var order_vendor_id = "{{$order_vendor->id??0}}";
        $.ajax({
            url: "{{ route('order.cancel.customer') }}",
            type: "POST",
            data: {
                vendor_id: vendor_id,
                return_reason_id: return_reason_id,
                order_id: order_id,
                reject_reason: reject_reason,
                "_token": "{{ csrf_token() }}",
                order_vendor_id: order_vendor_id,
                product_id: product_id,
                pickup_cancelling_charges: pickup_cancelling_charges,
                pickup_order_date: pickup_order_date,
                order_number: order_number,
            },

            success: function(response) {
                if (response.status == 'success') {
                    // $(".modal .close").click();
                    location.reload();
                } else if (response.status == 'error') {
                    $('#error-case').empty();
                    $('#error-case').append(response.message);
                }

            },
            error: function(response) {
                if (response.status == 'error') {
                    $('#error-case').empty();
                    $('#error-case').append(response.message);
                }
            }

        });


    });
</script>