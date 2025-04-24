
$(document).on("click",".btn-price-up-down",function(){    
    var product_id = $("#bid_ride_now").attr('data-product_id');
    type      = $(this).attr('data-type');
    var input = $("input[name='cab_bid_price']");
    var currentVal = parseInt(input.val());
    if (!isNaN(currentVal)) {
        if(type == 'minus') {
            
            if(currentVal > input.attr('min')) {
                input.val((currentVal - 10).toFixed(2)).trigger('change');
            }else{
                Swal.fire('You cannot dicrease amount')
                $(this).attr('disabled',true);
            } 
            if(parseInt(input.val()) == input.attr('min')) {
            }

        } else if(type == 'plus') {
            $('.btn-price-up-down').attr('disabled',false);

            // if(currentVal < input.attr('max')) {
                input.val((currentVal + 10).toFixed(2)).trigger('change');
            // }
            // if(parseInt(input.val()) == input.attr('max')) {
            // }
        }
    } else {
        input.val(input.attr('min'));
    }
});

var driverInterval = null;
$(document).on("click","#create_bid", async function(){    
    var product_id = $(this).attr('data-product_id');
    var taskType = $(this).attr('data-task_type');
    var vendor_id = $(this).attr('data-vendor_id');
    var requested_price = $('input[name="cab_bid_price"]').val();
    var min_requested_price = 0;
    var max_requested_price = 0;
    var tags = $(this).attr('data-tags');
    let OrderId = null;
    var $i = 0;

    var time_zone = (Intl.DateTimeFormat().resolvedOptions().timeZone);
        var schedule_datetime = '';
        if($(this).data('rel') =='pickup_later'){
            let temp_schedule_datetime = $('#schedule_datetime').val();
            if(!temp_schedule_datetime){
                $('#schedule_datetime_main_div').show();
                return false;
            }
            schedule_datetime = moment(temp_schedule_datetime).format('YYYY-MM-DD HH:mm');
        }
        
        var tasks = [];
        var tasks2 = [];

        let schedule_datetimeset = $('#schedule_date').val();
        if(schedule_datetimeset != undefined && schedule_datetimeset != 0){
            schedule_datetime = moment(schedule_datetimeset).format('YYYY-MM-DD HH:mm');
            var task_type = 'schedule';
        }
        else{
            var task_type = 'now';
        }
        if(time_zone != undefined || time_zone.length != 0){
            time_zone = time_zone;
        }

        var pickup_location_names = $('input[name="pickup_location_name[]"]').map(function(){return this.value;}).get();
        var destination_location_names = $('input[name="destination_location_name[]"]').map(function(){return this.value;}).get();
        var pickup_location_latitudes = $('input[name="pickup_location_latitude[]"]').map(function(){return this.value;}).get();
        var pickup_location_longitudes = $('input[name="pickup_location_longitude[]"]').map(function(){return this.value;}).get();
        var destination_location_latitudes = $('input[name="destination_location_latitude[]"]').map(function(){return this.value;}).get();
        var destination_location_longitudes = $('input[name="destination_location_longitude[]"]').map(function(){return this.value;}).get();

        $(pickup_location_latitudes).each(function(index, latitude) {
            var sample_array = {};
            sample_array.barcode = null;
            sample_array.task_type_id = 1;
            sample_array.post_code = null;
            sample_array.short_name = null;
            sample_array.latitude = latitude;
            sample_array.appointment_duration = null;
            sample_array.address = pickup_location_names[index];
            sample_array.longitude = pickup_location_longitudes[index];
            tasks.push(sample_array);
            tasks2.push(sample_array);
        });

        $(destination_location_latitudes).each(function(index, latitude) {
            var sample_array = {};
            sample_array.barcode = null;
            sample_array.task_type_id = 2;
            sample_array.post_code = null;
            sample_array.short_name = null;
            sample_array.latitude = latitude;
            sample_array.appointment_duration = null;
            sample_array.address = destination_location_names[index];
            sample_array.longitude = destination_location_longitudes[index];
            tasks.push(sample_array);
        });

    await $.ajax({
        type: "POST",
        dataType: 'json',
        url: create_bid_url,
        data : {product_id, vendor_id, requested_price,min_requested_price,max_requested_price,tags,tasks},
        success: function(response) {
            if(response.status == 200){
                $("#create_bid_btns").hide();
                $("#driver_acceptance_list").removeClass('d-none');
                OrderId = response.data.id;
            }
        },
        error: function(response) {
            $('#show_error_of_bid').text(response.responseJSON.message);
        } 
    });

    driverInterval = setInterval(driverBidingList, 5000, OrderId,product_id,vendor_id,taskType);

    function driverBidingList(id,product_id,vendor_id,taskType) {
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: driver_biding_list_url,
            data : { order_id: id, task_type: taskType },
            success: function(response) {
                console.log({response});
                if(response.status == 'Success'){
                    let driver_biding_list = _.template($('#driver_biding_list').html());
                    if((response.data.biddata).length != 0)
                    {
                        if($i == 0){
                            $('#paymentMethods').removeClass('d-none');
                        }
                        $i++;
                        $("#driver_acceptance_list").html(driver_biding_list({results: response.data.biddata, product_id, vendor_id}));
                    }
                }
            },
            error: function(response) {
            } 
        });   
    }
});

$(document).on("click","#pickup_now_bid", function(){    
    var bid_id = $(this).attr('data-bid_id');

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: accept_bid_by_customer,
        data : {bid_id},
        success: function(response) {
            if(response.status == "Success"){
                $("#driver_acceptance_list").html(`<div class="text-loader"> ${response.message} </div>`);
                clearInterval(driverInterval);
            }
        },
        error: function(response) {
        } 
    });
});