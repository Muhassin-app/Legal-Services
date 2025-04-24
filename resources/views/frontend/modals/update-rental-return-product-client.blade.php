        <div class="d-flex align-items-center">
            <label class="order-items d-flex align-items-center" for="item_one">
                <div class="item-img mr-1">
                    <img src="{{ $return_details->orderProduct->image['proxy_url'] . '74/100' . $return_details->orderProduct->image['image_path'] }}"
                        alt="">
                </div>
                <div class="items-name ml-2">
                    <h4 class="mt-0 mb-1"><b>{{ $return_details->orderProduct->product_name }}</b></h4>
                    <label><b>Quantity</b>: {{ $return_details->orderProduct->quantity }}</label>
                </div>
            </label>
        </div>

        <form id="return-upload-form" class="theme-form" action="javascript:void(0)" method="post"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{$return_details->id}}">
            <input type="hidden" name="order_vendor_product_id" value="{{$return_details->order_vendor_product_id}}">   

            <div class="row form-group">
                <div class="col-md-12">
                    <label>{{__('Update Status')}}</label>
                </div>
                <div class="col-md-12">
                    <select class="form-control" name="status">
                        <option value="Accepted" >{{__('Accepted')}}</option>
                        <option value="Pending" >{{__('Pending')}}</option>
                      
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>{{__('Damage Amount (If Any)')}}:</label>
                <input type="number" name="damage" class="form-control"  value="">
            </div>
           

            <span class="text-danger" id="error-msg"></span>
            <span class="text-success" id="success-msg"></span>
            <button class="btn btn-primary w-100 mt-3" id="return_form_button">{{__('Update')}}</button>
            
        </form>


<script type="text/javascript">
    $(document).ready(function(e) {
    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#return-upload-form').submit(function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: "{{ route('update.order.rental.return.client') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                   $("#return_form_button").html(
                            '<i class="fa fa-spinner fa-spin fa-custom"></i> Loading').prop(
                            'disabled', true);
                },
                success: (data) => {
                    if (data.status == 'Success') {
                       $("#return_form_button").html('Submitted');
                       location.reload();
                    } else {
                        $('#error-msg').text(data.message);
                        $("#return_form_button").html('Update').prop('disabled',
                            false);
                    }
                },
                error: function(data) {
                    $('#error-msg').text(data.message);
                    $("#return_form_button").html('Update').prop('disabled',
                        false);
                }
            });
        });

    });

</script>
