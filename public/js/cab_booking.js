   ////////   **************  cab details page  *****************  ////////
   $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')}
    });
    recurringformPost =  {};
   $(document).on("click",".cab_payment_method_selection", function(){
    var type = $(this).attr('type');
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: get_payment_options,
            success: function(response) {
                if(response.status == 'Success'){
                    $("#payment_modal .modal-body").html('');
                    $("#payment_modal_bid .modal-body").html('');
                    let payment_methods_template = _.template($('#payment_methods_template').html());
                    if(type == 'bid'){
                        var selected = $('#pickup_now_bid').attr("data-payment_method");
                        $("#payment_modal_bid .modal-body").append(payment_methods_template({payment_options: response.data, type:type}));
                        $("#payment_modal_bid .select_cab_payment_method[value='"+selected+"']").prop("checked", true);
                    }
                    else{
                        var selected = $('#pickup_now').attr("data-payment_method");
                        $("#payment_modal .modal-body").append(payment_methods_template({payment_options: response.data, type:type}));
                        $("#payment_modal .select_cab_payment_method[value='"+selected+"']").prop("checked", true);
                    }
                }
            }
        });
        // clearInterval(driverInterval);
   });
   var card = '';
   var stripe = '';
   function stripeInitialize() {
        stripe = Stripe(stripe_publishable_key);
        var elements = stripe.elements();
        var style = {
            base: { fontSize: '16px', color: '#32325d', borderColor: '#ced4da' },
        };
        card = elements.create('card', { hidePostalCode: true, style: style });
        card.mount('#stripe-card-element');
    }
   $(document).on("click", ".select_cab_payment_method",function() {
       var payment_method = $(this).attr('data-payment_method');
       console.log({payment_method});
            if (payment_method == 4) {
                stripeInitialize();
                $("#cab_payment_method_form .stripe_element_wrapper").removeClass('d-none');
            }else if (payment_method == 49) {
                 $("#cab_payment_method_form .plugnpay_element_wrapper").removeClass('d-none');
            }
            else if (payment_method == 58) {
                $("#cab_payment_method_form .powertrans_element_wrapper").removeClass('d-none');
                $("#cab_payment_method_form .stripe_element_wrapper").addClass('d-none');
            }
            else {
                $("#cab_payment_method_form .stripe_element_wrapper").addClass('d-none');
            }
    //    if(payment_method == 2)
    //    $('#payment_type').html('<i class="fa fa-money" aria-hidden="true"></i> Wallet');
    //    else
    //    $('#payment_type').html('<i class="fa fa-money" aria-hidden="true"></i> Cash');
        var label = $(this).closest('label').find('span:first-child').text();
       $('#payment_type').html('<i class="fa fa-money" aria-hidden="true"></i> '+label);
       $('#payment_type_bid').html('<i class="fa fa-money" aria-hidden="true"></i> '+label);

       $('#pickup_now').attr("data-payment_method",payment_method);
       $('#pickup_now_bid').attr("data-payment_method",payment_method);
       $('#pickup_later').attr("data-payment_method",payment_method);
       $('#payment-method-for-bid').val(payment_method);
       $('#book_hourly_rental').attr("data-payment_method",payment_method);
        //$("#payment_modal").modal('toggle');
   });
    $(document).on("click", ".select_payment_option_done",function() {
        $("#cab_payment_method_form, .select_payment_option_done").attr("disabled", true);
        var type = $(this).attr('data-type');
        let payment_option_id = $("#cab_payment_method_form input[name='select_cab_payment_method']:checked").val();

        if (payment_option_id == 4) {
            stripe.createToken(card).then(function(result) {
                //card error case
                if (result.error) {
                    $('#stripe_card_error').html(result.error.message);
                    $("#cab_payment_method_form .select_payment_option_done").attr("disabled", false);
                } else {
                    //
                    $('#stripe_card_error').html('');
                     //card token in input case
                   $('#stripe_token').val(result.token.id);
                     //hide model
                    if (type == 'bid') {
                        $("#payment_modal_bid").modal('toggle');
                    }else{
                        $("#payment_modal").modal('toggle');
                    }
                }
            });
        }
        else if(payment_option_id == 58){
            var expData = $('#date-element-powertrans').val();
            var [expMonth, expYear] = [expData.slice(2), expData.slice(0, 2)]
            var newDate = expMonth+'/'+expYear;
            cardJson = {
                'cno': $('#card-element-powertrans').val(),
                'dt': newDate,
                'cv': $('#cvv-element-powertrans').val(),
                'name':'powertrans',
            }
            if(cardValidation(cardJson)){
                console.log('Credit card information is valid.');
            }
            else {
                success_error_alert('error', 'Invalid credit card information', "#powertrans_card_error");
                return false;
            }
        }
        else{
            //hide model
             $('#stripe_card_error').html('');
             if (type == 'bid') {
                $("#payment_modal_bid").modal('toggle');
             }else{
                $("#payment_modal").modal('toggle');
             }

        }
    });

   function setOrderDetailsPage() {
    $('.address-form').addClass('d-none');
    $('.cab-detail-box').removeClass('d-none');
     $.ajax({
        type: "POST",
        dataType: 'json',
        url: order_place_driver_details_url,
        success: function(response) {
            $('#pickup_now').attr('disabled', false);
            $('#pickup_later').attr('disabled', false);
            if(response.status == '200'){
                $('#cab_detail_box').html('');

                // var Helper = { formatPrice: function(x){   //x=x.toFixed(2)
                //     return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                //      } };

                var orderSuccessData = _.extend({ Helper: NumberFormatHelper },{result: response.data, product_image: response.data.product_image});

                let order_success_template = _.template($('#order_success_template').html());
                $("#cab_detail_box").append(order_success_template(orderSuccessData)).show();
                setInterval(function(){
                    getOrderDriverDetails(response.data.dispatch_traking_url,response.data.id)
                },3000);
            }
        }
    });
}




function getOrderDriverDetails(dispatch_traking_url,order_id) {
    var new_dispatch_traking_url = dispatch_traking_url.replace('/order/','/order-details/');
    $.ajax({
        type:"POST",
        dataType: "json",
        url: order_tracking_details_url,
        data:{new_dispatch_traking_url:new_dispatch_traking_url,order_id:order_id},
        success: function( response ) {
            if(response.data.agent_location != null){
                $('#searching_main_div').remove();
                $('#driver_details_main_div').show();
                $('#driver_name').html(response.data.order.name).show();
                $('#driver_image').attr('src', response.data.agent_image).show();
                $('#driver_phone_number').html(response.data.order.phone_number).show();
                $("#dispatcher_status_show").html(response.data.order_details.dispatcher_status);
            }
        }
    });
}

// get driver details



$(document).ready(function () {

    $('.cab-booking-main-loader').hide();
    var selected_address = '';
    // const styles = [{"stylers":[{"visibility":"on"},{"saturation":-100},{"gamma":0.54}]},{"featureType":"road","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"water","stylers":[{"color":"#4d4946"}]},{"featureType":"poi","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels.text","stylers":[{"visibility":"simplified"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"simplified"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"gamma":0.48}]},{"featureType":"transit.station","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"gamma":7.18}]}];
    const styles = [];
    $(document).on("click","#show_dir",function() {
        initMap2();
    });

    // PlugPay payment

    $(document).on("click", "#paywithplugpay",function() {

        cno = $('#plugnpay-card-element').val();
        dt  = $('#plugnpay-date-element').val();
        cv  = $('#plugnpay-cvv-element').val();
        if((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == ''))
        {
            success_error_alert('error', 'Please Fill Details', "#plugnpay_card_error");
            return false;
        }else{
            $("#pickup_now, #pickup_later").trigger('click');
            $('#paywithplugpay').prop('disabled',true);
        }

    });

     // PlugPay payment

    $(document).on("click", "#paywithazulpay",function() {

        cno = $('#azul-card-element').val();
        dt  = $('#azul-date-element').val();
        cv  = $('#azul-cvv-element').val();
        if((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == '') || creditCardValidation() == false)
        {
           // success_error_alert('error', 'Please Fill Details', "#azul_card_error");
             $('#paywithazulpay').prop('disabled',false);
            return false;
        }else{
            $("#pickup_now, #pickup_later").trigger('click');
            $('#paywithazulpay').prop('disabled',true);
        }

    });

    $(document).on("click", ".right-top",function() {
        var payment_option_id = $(".select_cab_payment_method:checked").val();
        if(payment_option_id == 49){
           $("#plugnpay_card_error").empty();
           $("#proceed_to_pay_loader").hide();
           $('#paywithplugpay').prop('disabled',false);
           $('#plugnpay-card-element').val('');
           $('#plugnpay-date-element').val('');
           $('#plugnpay-cvv-element').val('');
        }

         if(payment_option_id == 50){
           $("#azul_card_error").empty();
           $("#proceed_to_azulpay_loader").hide();
           $('#paywithazulpay').prop('disabled',false);
           $('#azul-card-element').val('');
           $('#azul-date-element').val('');
           $('#azul-cvv-element').val('');
        }
    });


    $(document).on('keypress','#driver_unique_id', function(e){
        if(e.which === 32){
            return false;
        }
        $('#driver_request_error').hide();
    });

    // please order dispatcher
    $(document).on("click", "#pickup_now, #pickup_now_bid, #pickup_later",function() {
        var bookingType = $(this).attr('booking-type');
        var bid_task_type = '';
        if(bookingType == 'bid')
        {
			bid_task_type = 'bid_task_type';
            var payid = $('#payment-method-for-bid').val();
        }else if(bookingType == 'driver_request'){
            var unique_id = $('#driver_unique_id').val();
            if(unique_id == '' || unique_id == undefined){
                $('#driver_request_error').text('Driver Unique id is required');
                return false;
            }
            var isBase64 = /^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{4})$/;
            var decode = isBase64.test(unique_id);

            if(!decode){
                $('#driver_request_error').text('Invalid Unique Id');
                $('#driver_request_error').show();
                return false;
            }

        }else{
            var payid = $(this).attr('data-payment_method');
        }

        var share_ride_users = $("input[name='share_ride_users[]']:checked").map(function () {
            return this.value;
        }).get();



        if(payid == 49){
            cno = $('#plugnpay-card-element').val();
            dt  = $('#plugnpay-date-element').val();
            cv  = $('#plugnpay-cvv-element').val();
            if((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == ''))
            {
                $('#plugpaymethod').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#proceed_to_pay_loader").hide();
                $('#paywithplugpay').prop('disabled',false);
                return false;
            }else{
                $("#proceed_to_pay_loader").show();
            }
        }
         if(payid == 50){
            cno = $('#azul-card-element').val();
            dt  = $('#azul-date-element').val();
            cv  = $('#azul-cvv-element').val();
            if((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == '') || creditCardValidation() == false)
            {
                $('#azulpaymethod').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#proceed_to_azulpay_loader").hide();
                $('#paywithazulpay').prop('disabled',false);
                return false;
            }else{
				$("#proceed_to_azulpay_loader").show();
			}
        }
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

        if(bookingType == 'driver_request'){
            var schedule_datetimeset = $('#schedule_date_for_driver').val();
        }else{
            var schedule_datetimeset = $('#schedule_date').val();
        }

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
        var addons = $('.addon-opt').map(function() {return this.getAttribute('data-id')}).get();

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

        let amount                      = $(this).attr('data-amount');
        let tollamount                  = $(this).attr('data-tollamount');
        let servicechargeamount         = $(this).attr('data-servicechargeamount');
        let totalamount                 = $(this).attr('data-totalamount');
        let subscription_payable_amount = $(this).attr('data-subscriptionPayableAmount');
        let product_image               = $(this).attr('data-image');
        let vendor_id                   = $(this).attr('data-vendor_id');
        let coupon_id                   = $(this).attr('data-coupon_id');
        let product_id                  = $(this).attr('data-product_id');
        let payment_option_id           = $(this).attr('data-payment_method');
        let type                        = parseFloat($('input[name=is_for_friend]:checked').val());
        let driver_id                   = $(this).attr('data-driver_id');
		let send_to_all					= $("#send_to_all").is(":checked")?1:0;
        let seats                       = $('#seats').val();
        let duration_time                       = $('#duration_time').val();

        // return false;
        let friendName=$('input[name=friendName]').val();
        let friendPhoneNumber= $('input[name=friendPhoneNumber]').val();

        let total_other_taxes = $('input[name=total_other_taxes]').val();
        let total_other_taxes_string= $('input[name=total_other_taxes_string]').val();


        var no_seats_for_pooling = $('input[name="no_seats_for_pooling"]').val();
        var is_cab_pooling = $('input[name="is_cab_pooling_radio"]:checked').val();

        if(is_cab_pooling == 4 && (recurringformPost.action == '' || recurringformPost.action == undefined))
        {
            alert('Select recurring details');
            return false;
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: cab_booking_create_order,
            data: { user_product_order_form:product_order_form_element_data,time_zone:time_zone,payment_option_id: payment_option_id, vendor_id: vendor_id, product_id: product_id, coupon_id: coupon_id, amount: amount, tollamount:tollamount, servicechargeamount:servicechargeamount, totalamount:totalamount, subscription_payable_amount:subscription_payable_amount, tasks: tasks, task_type:task_type, schedule_datetime:schedule_datetime, type:type, friendName:friendName, friendPhoneNumber:friendPhoneNumber, no_seats_for_pooling:no_seats_for_pooling, is_cab_pooling:is_cab_pooling,driver_id,unique_id,recurringformPost,share_ride_users,total_other_taxes_string,total_other_taxes,bid_task_type,send_to_all},
            success: function(response) {
                $('#pickup_now').attr('disabled', false);
                $('#pickup_later').attr('disabled', false);
                if(response.status == '200'){
                    if(is_cab_pooling == 4 )
                    {
                        alert(response.message);
                        window.location.href = response.redirect;
                    }
                    let order_id = response.data.id;
                    let order_number = response.data.order_number;
                    let reload_route = response.data.route;

                    if((payment_option_id == 1) || (payment_option_id == 2)){
                        // placeOrderBeforePayment('',payment_option_id,0,order_number);
                        window.location.replace(response.data.route);

                        // $('#cab_detail_box').html('');
                        // var orderSuccessData = _.extend({ Helper: NumberFormatHelper },{result: response.data, product_image: product_image});
                        // let order_success_template = _.template($('#order_success_template').html());
                        // $("#cab_detail_box").append(order_success_template(orderSuccessData)).show();
                        // setInterval(function(){
                        //     getDriverDetails(response.data.dispatch_traking_url)
                        // },3000);
                    }else if(payment_option_id == 3){
                       let payment_form = "pickup_delivery";
                        $.ajax({
                            type: "POST",
                            dataType: 'json',
                            url: payment_paypal_url,
                            data: { user_product_order_form:product_order_form_element_data,time_zone:time_zone,payment_option_id: payment_option_id, vendor_id: vendor_id, product_id: product_id,coupon_id: coupon_id, amount: totalamount, tasks: tasks, task_type:task_type, schedule_datetime:schedule_datetime,stripe_token: stripe_token , payment_form : payment_form,reload_route: reload_route,ordernumber:order_number,order_id:order_id },
                            success: function(resp) {
                                if (resp.status == 'Success') {
                                    window.location.replace(resp.data);
                                }
                            },
                            error: function(error) {
                                $('#show_error_of_booking').html(response.message);

                                var response = $.parseJSON(error.responseText);
                            }
                    });
                } else if(payment_option_id == 4){
                        var stripe_token = $('#stripe_token').val();
                        let payment_form = "pickup_delivery";

                          //  console.log(ajaxData);
                        $.ajax({
                            type: "POST",
                            dataType: 'json',
                            url: payment_stripe_url,
                            data: { user_product_order_form:product_order_form_element_data,time_zone:time_zone,payment_option_id: payment_option_id, vendor_id: vendor_id, product_id: product_id,coupon_id: coupon_id, amount: totalamount, tasks: tasks, task_type:task_type, schedule_datetime:schedule_datetime,stripe_token: stripe_token , payment_form : payment_form,reload_route: reload_route,order_number:order_number },
                            success: function(resp) {
                                console.log({resp});
                                if (resp.status == 'Success') {
                                    window.location.replace(resp.data);
                                } else {
                                    alert(resp.message);
                                }
                            },
                            error: function(error) {
                                console.log(error);
                                // $('#show_error_of_booking').html(response.message);

                                //var response = $.parseJSON(error.responseText);

                            }
                        });
                        //var stripe_token = $('#stripe_token').val();
                       // paymentViaStripe(stripe_token,order_id, payment_option_id,vendor_id);
                    }
                    else if (payment_option_id == 69) {
                        let payment_from = "pickup_delivery";
                        $.ajax({
                            type: "POST",
                            dataType: 'json',
                            url: payment_hitpay_url,
                            data: { user_product_order_form: product_order_form_element_data, time_zone: time_zone, payment_option_id: payment_option_id, vendor_id: vendor_id, product_id: product_id, coupon_id: coupon_id, amount: totalamount, tasks: tasks, task_type: task_type, schedule_datetime: schedule_datetime, stripe_token: stripe_token, payment_from: payment_from, reload_route: reload_route, order_number: order_number, order_id: order_id },
                            success: function (resp) {
                                if (resp.status == 'Success') {


                                    window.location.replace(resp.payment_url);
                                }
                            },
                            error: function (error) {
                                $('#show_error_of_booking').html(response.message);

                                var response = $.parseJSON(error.responseText);
                            }
                        });

                    }
                    else if(payment_option_id == 5){
                        $res = paymentViaPaystack('',response.data);
                    }
                    else if(payment_option_id == 10){
                        paymentViaRazorpay('', response.data, 'pickup_delivery');
                    }else if(payment_option_id == 22){
                        payWithCcAvenue(response.data);
                    }else if(payment_option_id == 32){
                        payphoneButton(response.data);
                    }else if(payment_option_id == 42){
                        payWithDpo(response.data);
                    } else if (payment_option_id == 30) {
                        payWithFlutterWave(response.data);
                    } else if (payment_option_id == 46) {
                        paymentViaMastercard('pickup_delivery', response.data)
                    }else if(payment_option_id == 49){

                        paymentViaplugnpay(reload_route,'',response.data);
                    }
                    else if(payment_option_id == 50){

                        paymentViazulpay(reload_route,'',response.data);
                    }
                    else if(payment_option_id == 52){
                        paymentViaSkipCash('',response.data);
                    }
                    else if(payment_option_id == 56){
                       paymentViaOboPay(reload_route,'',response.data);
                    }
                    else if(payment_option_id == 22){
                        payWithCcAvenue(response.data);
                    }else if(payment_option_id == 48){
                        paymentViaMtnMomo('',response.data,'pickup_delivery',reload_route)
                    }
                    else if(payment_option_id == 57){
                        payWithPesapal(payment_option_id,response.data);
                    }
                    else if(payment_option_id == 58){
                        payWithPowerTrans(payment_option_id,response.data);
                    }else if(payment_option_id == 59){
                        payWithLivees(payment_option_id,'',response.data);
                    }
                    else if(payment_option_id == 60){
                        payWithCompany('',payment_option_id,response.data);
                        window.location.replace(response.data.route);
                    }else if(payment_option_id == 62){
                        payWithLivees(reload_route,'',response.data);
                    }
                    else if(payment_option_id == 70){
                        paymentViaCyberSourcePay(payment_option_id,response.data);
                    }
                    else if(payment_option_id == 71){
                        paymentViaOrangePay(payment_option_id,response.data);
                    }
                    cabBookingPaymentOptions(payment_option_id, response.data);
                }
                else if(response.status == 201){
                    $('#driver_request_error').text(response.message);
                    $('#driver_request_error').show();
                    return false;
                }
                else{
                    $('#show_error_of_booking').html(response.message);
                }
            }
        });
    });

    window.placeOrderBeforePayment = function placeOrderBeforePayment(address_id = 0, payment_option_id, tip = 0,pick_drop_order_number) {
        var task_type = $("input[name='task_type']").val();
        var schedule_dt = $("#schedule_datetime").val();
        var slot = $("#slot").val();
        var is_gift = $('#is_gift:checked').val() ?? 0;
        // place_order_url=domain+/user/
        if ((task_type == 'schedule') && (schedule_dt == '')) {
            $("#proceed_to_pay_modal").modal('hide');
            $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            success_error_alert('error', 'Schedule date time is required', ".cart_response");
            return false;
        }

        var orderResponse = '';

        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: place_order_url,
            data: { address_id: address_id, payment_option_id: payment_option_id, tip: tip, task_type: task_type, schedule_dt: schedule_dt, is_gift: is_gift, slot: slot,order_number:pick_drop_order_number },
            success: function (response) {
                if (response.status == "Success") {
                    orderResponse = response.data;
                    // return orderResponse;
                } else {
                    if ($(".payment_response").length > 0) {
                        $(".payment_response").removeClass("d-none");
                        success_error_alert("error", response.message, ".payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    }
                }
            },
            error: function (error) {

                var response = $.parseJSON(error.responseText);
                // success_error_alert('error', response.message, ".payment_response");
                if ($('.payment_response').length > 0) {
                    $(".payment_response").removeClass('d-none');
                    success_error_alert('error', response.message, ".payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                }
            },
            complete: function (data) {
                $('.spinner-overlay').hide();
            }
        });
        return orderResponse;
    }

    function paymentViaStripe(stripe_token, order_id, payment_option_id,) {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';

        let ajaxData = [];
        // cab booking only
        ajaxData.push(
            { name: 'payment_form', value: payment_form },
            { name: 'stripe_token', value: stripe_token },
            { name: 'amount', value: total_amount },
            { name: 'order_id', value: order_id },
            { name: 'vendor_id', value: vendor_id },
            { name: 'payment_option_id', value: payment_option_id }
        );
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_stripe_url,
            data: ajaxData,
            success: function(resp) {
                if (resp.status == 'Success') {
                    if (path.indexOf("cart") !== -1) {
                        // placeOrder(address_id, payment_option_id, resp.data.id, tip,delivery_type);
                    } else if (path.indexOf("wallet") !== -1) {
                        // creditWallet(total_amount, payment_option_id, resp.data.id);
                    } else if (path.indexOf("subscription") !== -1) {
                        // userSubscriptionPurchase(total_amount, payment_option_id, resp.data.id);
                    } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {

                        // let order_number = $("#order_number").val();
                        // if (order_number.length > 0) {
                        //     order_number = order_number;
                        // }
                        // creditTipAfterOrder(total_amount, payment_option_id, resp.data.id, order_number);
                    } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                        // creditWallet(total_amount, payment_option_id, resp.data.id);
                    }
                    window.location.href = resp.data;
                } else {
                    if (path.indexOf("cart") !== -1) {
                        success_error_alert('error', resp.message, ".payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    } else if (path.indexOf("wallet") !== -1) {
                        success_error_alert('error', resp.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    } else if (path.indexOf("subscription") !== -1) {
                        success_error_alert('error', resp.message, "#subscription_payment_form .payment_response");
                        $(".subscription_confirm_btn").removeAttr("disabled");
                    } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
                        success_error_alert('error', resp.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                        success_error_alert('error', resp.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    }
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                if (path.indexOf("cart") !== -1) {
                    success_error_alert('error', response.message, ".payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (path.indexOf("wallet") !== -1) {
                    success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                } else if (path.indexOf("subscription") !== -1) {
                    success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                    $(".subscription_confirm_btn").removeAttr("disabled");
                }
            }
        });
    }



    window.getDriverDetails = function getDriverDetails(dispatch_traking_url) {
        var new_dispatch_traking_url = dispatch_traking_url.replace('/order/','/order-details/')
        $.ajax({
            type:"POST",
            dataType: "json",
            url: order_tracking_details_url,
            data:{new_dispatch_traking_url:new_dispatch_traking_url},
            success: function( response ) {
                if(response.data.agent_location != null){
                    $('#searching_main_div').remove();
                    $('#driver_details_main_div').show();
                    $('#driver_name').html(response.data.order.name).show();
                    $('#driver_image').attr('src', response.data.agent_image).show();
                    $('#driver_phone_number').html(response.data.order.phone_number).show();
                }
            }
        });
    }

    $(document).on("click", ".add-more-location",function() {
        let random_id = Date.now();

        var destination_location_names = $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').map(function(){
            $(this).hide();
            if(this.value == ''){
                return "empty";
            }
        }).get();

        if(destination_location_names[0] == 'empty'){
            return false;
        }

        var destination_location_add_temp = $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').length;
        if(destination_location_add_temp == 4){
            $('.add-more-location').hide();
        }
        $('#search_product_main_div').attr("style", "display: none !important");
        $('.location-list').attr("style", "display: block !important");
        // check-dropoff
        let destination_location_template = _.template($('#destination_location_template').html());
        $("#destination_location_add_temp").append(destination_location_template({random_id:random_id})).show();
        initializeNew(random_id);
    });
    // $(document).on("click", ".location-inputs .apremove",function() {
    //     if($('#dots_'+$(this).data('rel')).length != 0){
    //         $('#dots_'+$(this).data('rel')).remove();
    //         var destination_location_names = $('input[name="destination_location_name[]"]').map(function(){
    //            return this.value;
    //         }).get();
    //         if(destination_location_names.length < 5){
    //             $('.add-more-location').show();
    //         }else{
    //             $('.add-more-location').hide();
    //         }
    //         initMap2();
    //     }
    // });
    function initializeNew(random_id) {
      var input2 = document.getElementById('destination_location_'+random_id);
      if(input2){
        var autocomplete = new google.maps.places.Autocomplete(input2);
        if(is_map_search_perticular_country){
            autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
        }
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place2 = autocomplete.getPlace();
            $('#destination_location_latitude_'+random_id).val(place2.geometry.location.lat());
            $('#destination_location_longitude_'+random_id).val(place2.geometry.location.lng());
            initMap2(random_id);

            if(random_id != ''){
                $('#search_product_main_div').attr("style", "display: block !important");
                $('.location-list').attr("style", "display: none !important");
                $('#destination_location_'+random_id).attr("style", "display: none !important");

                var currentUrl                   = window.location.href;
                var queryString                  = removeURLParameter(currentUrl, 'destination_location_'+random_id);
                var destination_location         = $("#destination_location_"+random_id).val();
                var destinationLocationLatitude  = $("#destination_location_latitude_"+random_id).val();
                var destinationLocationLongitude = $("#destination_location_longitude_"+random_id).val();
                var perm = "?" + (queryString != '' ? queryString : '') + "&destination_location_"+random_id+"=" + destination_location  + "&destination_location_latitude_"+random_id+"=" + destinationLocationLatitude + "&destination_location_longitude_"+random_id+"=" + destinationLocationLongitude;
                window.history.replaceState(null, null, perm);
            }

        });
        setTimeout(function(){
            //$(".pac-container").appendTo(".booking-experience #destination_location_add_temp");
        }, 300);
      }
    }
    $(document).on("click",".search-location-result",function() {
        var latitude             = $(this).data('latitude');
        var longitude            = $(this).data('longitude');
        var pickup_location      = $('#pickup_location_latitude').val();
        var destination_location = $('#destination_location_latitude').val();
        var pickupAddress        = $(this).data('address');
        if(pickup_location == ''){
            $('#pickup_location').val($(this).data('address'));
            $('#pickup_location_latitude').val(latitude);
            $('#pickup_location_longitude').val(longitude);

            var pickupLocationLatitude  = latitude;
            var pickupLocationLongitude = longitude;
            var currentUrl              = window.location.href;
            var queryString             = removeURLParameter(currentUrl, 'pickup_location');
            var perm                    = "?pickup_location=" + $(this).data('address') + "&pickup_location_latitude=" + pickupLocationLatitude +"&pickup_location_longitude=" + pickupLocationLongitude + (queryString != '' ? "&" + queryString : '');
            window.history.replaceState(null, null, perm);

            $(".check-pick-first").css("display", "none");
            $("#pickup-where-from").html(" "+$(this).data('address'));
            $(".check-dropoff-secpond").css("display", "block");
            $('.check-pickup').attr("style", "display: none !important");
            $(".check-dropoff").css("display", "block");

        }else if(destination_location == ''){
            $('#destination_location').val($(this).data('address'));
            $('#destination_location_latitude').val(latitude);
            $('#destination_location_longitude').val(longitude);

            var currentUrl  = window.location.href;
            var queryString = removeURLParameter(currentUrl, 'destination_location');
            var perm        = "?" + (queryString != '' ? queryString : '') + "&destination_location=" + $(this).data('address')  + "&destination_location_latitude=" + latitude +"&destination_location_longitude=" + longitude;
            window.history.replaceState(null, null, perm);

            $("#dropoff-where-to").html(" "+$(this).data('address'));
            $('.where-to-first').attr("style", "display: none !important");
            $('.check-dropoff').attr("style", "display: none !important");
            $(".where-to-second").css("display", "block");
            $('.add-more-location').attr("style", "display: block !important");

            $('#search_product_main_div').attr("style", "display: block !important");
            $('.location-list').attr("style", "display: none !important");
        }else{
            $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').map(function(){
                if(this.value == ''){
                    var inputId = this.id;
                    $('#'+inputId).val(pickupAddress);
                    var random_id = $(this).data( "rel" );
                    $('#destination_location_latitude_'+random_id).val(latitude);
                    $('#destination_location_longitude_'+random_id).val(longitude);

                    $('#search_product_main_div').attr("style", "display: block !important");
                    $('.location-list').attr("style", "display: none !important");

                    let destination_location_template_li = _.template($('#destination_location_template_li').html());
                    var destination_location = $('#destination_location_'+random_id).val();
                    $('#location_input_main_div li:last-child').after(destination_location_template_li({random_id:random_id}));
                    $("#dropoff-where-to-"+random_id).html(" "+ pickupAddress);

                    var currentUrl                   = window.location.href;
                    var queryString                  = removeURLParameter(currentUrl, 'destination_location_'+random_id);
                    var perm = "?" + (queryString != '' ? queryString : '') + "&destination_location_"+random_id+"=" + pickupAddress  + "&destination_location_latitude_"+random_id+"=" + latitude + "&destination_location_longitude_"+random_id+"=" + longitude;
                    window.history.replaceState(null, null, perm);

                    $('#destination_location_'+random_id).attr("style", "display: none !important");

                }
            }).get();
        }

        displayLocationCab(latitude, longitude);initMap2();
        getVendorList();
    });
    function getVendorList(){
        if(wallet_balance < 0)
        return false;
        var locations = [];
        let vendor_id = $(this).data('vendor');
        var pickup_location_latitude = $('input[name="pickup_location_latitude[]"]').map(function(){return this.value;}).get();
        var pickup_location_longitude = $('input[name="pickup_location_longitude[]"]').map(function(){return this.value;}).get();
        var destination_location_latitudes = $('input[name="destination_location_latitude[]"]').map(function(){return this.value;}).get();
        var destination_location_longitudes = $('input[name="destination_location_longitude[]"]').map(function(){return this.value;}).get();
        $(pickup_location_latitude).each(function(index, latitude) {
            var data = {};
            data.latitude = latitude;
            data.longitude = pickup_location_longitude[index];
            locations.push(data);
        });
        $(destination_location_latitudes).each(function(index, destination_location_latitude) {
            var data = {};
            data.latitude = destination_location_latitude;
            data.longitude = destination_location_longitudes[index];
            locations.push(data);
        });

        let schedule_datetime = '';
        let schedule_datetimeset = $('#schedule_date').val();
        if(schedule_datetimeset != undefined && schedule_datetimeset != 0){
            schedule_datetime = moment(schedule_datetimeset).format('YYYY-MM-DD HH:mm');
        }

        var post_data = JSON.stringify(locations);
        let pickup_location = $('#pickup_location').val();
        let destination_location = $('#destination_location').val();
        if(pickup_location && destination_location){
            $('.location-list').hide();
            $.ajax({
                data: {locations: post_data, schedule_date_delivery:schedule_datetime},
                type: "POST",
                dataType: 'json',
                url: autocomplete_urls,
                beforeSend: function(){
                    add_spinner('.cab-booking-loader');
                },
                success: function(response) {
                    if(response.status == 'Success'){
                        remove_spinner('.cab-booking-loader');
                        $('#vendor_main_div').html('');
                        if(response.data.length != 0){
                            let vendors_template = _.template($('#vendors_template').html());
                            $("#vendor_main_div").append(vendors_template({results: response.data})).show();
                            if(response.data.length == 1){
                                $('.vendor-list').trigger('click');
                                $('.table-responsive').hide();
                            }else{
                                $('.vendor-list').first().trigger('click');
                            }
                        }else{
                            $("#vendor_main_div").html('<p class="text-center my-3">'+ no_result_message +'</p>').show();
                        }
                    }
                }
            });
        }
    }

    // Start Rider SOurce COde
    $(document).on("change",".is_for_friend",function() {
        if($(this).val() == 1)
        {
            $('#search_product_main_div').hide();
            $('#search_product_rider_main_div').show();
            $(".alAddRiderSecOuter").show();
            $('#product_rider_div').show();
        }else{
            $('#search_product_rider_main_div').hide();
            $('#search_product_main_div').show();
            $(".alAddRiderSecOuter").hide();
            $('#product_rider_div').hide();
        }
    });

    $(document).on("change",".is_cab_pooling_radio",function() {

        var is_cab_pooling = $('input[name="is_cab_pooling_radio"]:checked').val();
        if(is_cab_pooling == 4)
        {
            $(".TypeBookingRec").show();
            $(".TypeBookingNow").hide();
        }else{
            $(".TypeBookingRec").hide();
            $(".TypeBookingNow").show();
        }

        // getListOfCabs();
    });

    $(document).on("change","#no_seats_for_pooling",function() {
        var product_id = $("#pickup_now").attr('data-product_id');
        getVehicleDetail(product_id);
    });

    $(document).on("click",".btn-number-up-down",function(){
        var product_id = $("#pickup_now").attr('data-product_id');
        type      = $(this).attr('data-type');
        var input = $("input[name='no_seats_for_pooling']");
        var currentVal = parseInt(input.val());
        if (!isNaN(currentVal)) {
            if(type == 'minus') {

                if(currentVal > input.attr('min')) {
                    input.val(currentVal - 1).trigger('change');
                }
                if(parseInt(input.val()) == input.attr('min')) {
                }

            } else if(type == 'plus') {

                if(currentVal < input.attr('max')) {
                    input.val(currentVal + 1).trigger('change');
                }
                if(parseInt(input.val()) == input.attr('max')) {
                }
            }
        } else {
            input.val(1);
        }
    });

    $(document).on("click","#submit_product_rider_button",function(){
        let product_id = $('input[name="rider_product_id"]:checked').val();
        let rider_id = 0;
        if(product_id === undefined){
            alert("Please choose one "+category_name+" to process next");
        }else{

            let rider_type = $('input[name="is_for_friend"]:checked').val();
            if(rider_type == 1 || rider_type == "1")
            {
                rider_id = $('input[name="rider_id"]:checked').val();
            }
        }
        getVehicleDetail(product_id,rider_id);

    });
    $(document).on('click','.add_rider_button',function(){
        var input = document.querySelector("#phone");
        window.intlTelInput(input, {
            separateDialCode: true,
            utilsScript: utilsScript_path,
            initialCountry: initial_country_code,
        });
    });

    $(document).on('click','.add_share_rider_button',function(){
        var input = document.querySelector("#phone");
        window.intlTelInput(input, {
            separateDialCode: true,
            utilsScript: utilsScript_path,
            initialCountry: initial_country_code,
        });
    });

    $(document).delegate('.iti__country','click', function() {
        var code = $(this).attr('data-country-code');
        $('#countryData').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#dialCode').val(dial_code);
    });


    $(document).on('click','.add_share_rider_submit_button',function(){
        var form = document.getElementById('add_rider_form');
        var formData = new FormData(form);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: add_rider_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                    let share_rider_template = _.template($('#share_rider_template').html());
                    $("#rider_section_user").html(share_rider_template({riders: response.riders})).show();
                    $("#rider_section_user").show();
                    $("#rider_section").hide();

                $('#alAddRiderSecModal').modal('hide');
                form.reset();
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
    });

    $(document).on('click','.add_rider_submit_button',function(){
        var form = document.getElementById('add_rider_form');
        var formData = new FormData(form);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: add_rider_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                let rider_template = _.template($('#rider_template').html());

                var is_cab_pooling = $('input[name="is_cab_pooling_radio"]:checked').val();
                $("#rider_section").html(rider_template({riders: response.riders})).show();


                $('#alAddRiderSecModal').modal('hide');
                form.reset();
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
    });
    $(document).on('click','.deleteRider',function(){
        var parent = $(this).parent()[0];
        var rider_id = $(this).data('id');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "get",
            headers: {
                Accept: "application/json"
            },
            url: remove_rider_url+'?rider_id='+rider_id,
            contentType: false,
            processData: false,
            success: function(response) {
                parent.remove();
                $('#rider_count').html(response.rider_count);
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
    });

    $(document).on('click','.vendor-list',function(){
        $("#default_cab_vendor_id").val($(this).data('vendor'));
        getListOfCabs();
    });
    // End Rider SOurce COde
    window.getListOfCabs = function getListOfCabs()
    {
        $('a[data-vendor="'+$("#default_cab_vendor_id").val()+'"]').show();
        let vendor_id = $("#default_cab_vendor_id").val();

        if(vendor_id =='' || vendor_id == undefined){
            $('.location-list').attr("style", "display: blank !important");
            return false;
        }

        var locations = [];
        var pickup_location_latitude = $('input[name="pickup_location_latitude[]"]').map(function(){return this.value;}).get();
        var pickup_location_longitude = $('input[name="pickup_location_longitude[]"]').map(function(){return this.value;}).get();
        var destination_location_latitudes = $('input[name="destination_location_latitude[]"]').map(function(){return this.value;}).get();
        var destination_location_longitudes = $('input[name="destination_location_longitude[]"]').map(function(){return this.value;}).get();

        let schedule_datetime = '';
        let schedule_datetimeset = $('#schedule_date').val();
        if(schedule_datetimeset != undefined && schedule_datetimeset != 0){
            schedule_datetime = moment(schedule_datetimeset).format('YYYY-MM-DD HH:mm');
        }

        $(pickup_location_latitude).each(function(index, latitude) {
            var data = {};
            data.latitude = latitude;
            data.longitude = pickup_location_longitude[index];
            locations.push(data);
        });
        $(destination_location_latitudes).each(function(index, destination_location_latitude) {
            var data = {};
            data.latitude = destination_location_latitude;
            data.longitude = destination_location_longitudes[index];
            locations.push(data);
        });
        var is_cab_pooling = $('input[name="is_cab_pooling_radio"]:checked').val();
        $.ajax({
            type: "POST",
            dataType: 'json',
            data: {locations:locations, schedule_date_delivery:schedule_datetime, is_cab_pooling:is_cab_pooling},
            url: get_vehicle_list+'/'+vendor_id+'/'+category_id,
            beforeSend: function(){
                add_spinner('.cab-booking-loader');
            },
            success: function(response) {
                if(response.status == 'Success'){
                    remove_spinner('.cab-booking-loader');
                    $('#search_product_main_div').html('');
                    $('#search_product_rider_main_div').html('');
                    if(response.data.length != 0){
                        var productData = _.extend({ Helper: NumberFormatHelper }, {results: response.data.products});

                        let products_template = _.template($('#products_template').html());

                        let products_rider_template = _.template($('#products_rider_template').html());
                        $("#search_product_main_div").append(products_template(productData));
                        $("#search_product_rider_main_div").append(products_rider_template(productData));

                        if($('input[name="is_cab_pooling_radio"]:checked').val() == 0 || $('input[name="is_cab_pooling_radio"]:checked').val() === undefined)
                        {
                            $("#search_product_main_div .double_price_p").hide();
                            $("#search_product_main_div .single_price_p").show();

                            $(".TypeBookingRec").hide();
                            $(".TypeBookingNow").show();

                        }else if(is_cab_pooling == 4)
                        {
                            $("#search_product_main_div .double_price_p").show();
                            $("#search_product_main_div .single_price_p").hide();

                            $(".TypeBookingRec").show();
                            $(".TypeBookingNow").hide();
                        }else{

                            $("#search_product_main_div .double_price_p").show();
                            $("#search_product_main_div .single_price_p").hide();

                            $(".TypeBookingRec").hide();
                            $(".TypeBookingNow").show();
                        }

                        let is_friend = $('input[name="is_for_friend"]:checked').val();
                        if(is_friend == undefined || is_friend == '0')
                        {
                            $("#search_product_main_div").show();
                            $("#search_product_rider_main_div").hide();
                        }else{
                            $("#search_product_main_div").hide();
                            $("#search_product_rider_main_div").show();
                        }
                    }else{
                        $("#search_product_main_div ").html('<p class="text-center my-3">'+ no_result_message +'</p>').show();
                        $("#search_product_rider_main_div ").html('<p class="text-center my-3">'+ no_result_message +'</p>');
                    }

                }
            },
            complete:function(data){
                remove_spinner('.cab-booking-loader');
            }
        });
    }

    $(document).on("click","#promo_code_list_btn_cab_booking",function() {
        let amount = $(this).data('amount');
        let vendor_id = $(this).data('vendor_id');
        let product_id = $(this).data('product_id');
         let cart_product_ids = $("input[name='cart_product_ids[]']").map(function(){return $(this).val();}).get();
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: promo_code_list_url,
            data: {amount:amount, vendor_id:vendor_id,cart_product_ids:cart_product_ids},
            success: function(response) {
                if(response.status == 'Success'){
                    $('#cab_booking_promo_code_list_main_div').html('');
                    if(response.data.length != 0){
                        $('.promo-box').removeClass('d-none');
                        $('.cab-detail-box').addClass('d-none');
                        let cab_booking_promo_code_template = _.template($('#cab_booking_promo_code_template').html());
                        $("#cab_booking_promo_code_list_main_div").append(cab_booking_promo_code_template({promo_codes: response.data, vendor_id:vendor_id, product_id:product_id, amount:amount})).show();
                    }else{
                        $('.promo-box').removeClass('d-none');
                        $('.cab-detail-box').addClass('d-none');
                        let cab_booking_promo_code_template = _.template($('#cab_booking_promo_code_template').html());
                        $("#cab_booking_promo_code_list_main_div").html(no_coupon_available_message).show();
                    }
                }
            }
        });
    });
    $(document).on("click","#remove_promo_code_cab_booking_btn",function() {
        let amount = $(this).data('amount');
        let vendor_id = $(this).data('vendor_id');
        let product_id = $(this).data('product_id');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: cab_booking_promo_code_remove_url,
            data: {amount:amount, vendor_id:vendor_id},
            success: function(response) {
                if(response.status == 'Success'){
                    $('#promo_code_list_btn_cab_booking').show();
                    $('#remove_promo_code_cab_booking_btn').hide();
                    $('.cab-detail-box #discount_amount').text('').hide();
                    $('.cab-detail-box .code-text').text("Select A Promo Code").show();
                    $('.cab-detail-box #real_amount').text(response.data.currency_symbol+' '+amount);
                    let subscriptionAmout = $('#subscription-amout-h').val();
                    if(subscriptionAmout != undefined){
                        $('#subscription-amout').text(response.data.currency_symbol+''+subscriptionAmout);
                        $('#pickup_now').attr("data-subscriptionPayableAmount",subscriptionAmout);
                    }
                    let elementsToHide = document.getElementsByClassName("cab_payment_method_selection");
                    if(amount <= 0){
                        amount = 0.00;
                        // Loop through the selected elements and set an inline style with !important
                        for (let i = 0; i < elementsToHide.length; i++) {
                            elementsToHide[i].style.setProperty('display', 'none', 'important');
                        }
                    }else{
                        for (let i = 0; i < elementsToHide.length; i++) {
                            elementsToHide[i].style.removeProperty('display');
                        }
                    }
                    $('#pickup_now').attr("data-coupon_id",'');
                    $('#pickup_later').attr("data-coupon_id",'');
                }
            }
        });
    });
    $(document).on("click",".cab_booking_apply_promo_code_btn",function() {
        let amount = $(this).data('amount');
        let vendor_id = $(this).data('vendor_id');
        let coupon_id = $(this).data('coupon_id');
        let product_id = $(this).data('product_id');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url:  apply_cab_booking_promocode_coupon_url,
            data: {amount:amount, vendor_id:vendor_id, product_id:product_id, coupon_id:coupon_id},
            success: function(response) {
                if(response.status == 'Success'){
                    $('.promo-box').addClass('d-none');
                    $('.cab-detail-box').removeClass('d-none');
                    $('#promo_code_list_btn_cab_booking').hide();
                    $('#remove_promo_code_cab_booking_btn').show();
                    let real_amount = $('.cab-detail-box #real_amount').text();
                    $('.cab-detail-box #discount_amount').text(real_amount).show();
                    $('.cab-detail-box .code-text').text('Code '+response.data.name+' applied').show();
                    $('#pickup_now').attr("data-coupon_id",coupon_id);
                    $('#pickup_later').attr("data-coupon_id",coupon_id);
                    var current_amount = amount - response.data.new_amount;
                    let subscriptionAmout = $('#subscription-amout-h').val();
                    if(subscriptionAmout != undefined && subscriptionAmout > 0){
                        //console.log('response.data.new_amount', response.data.new_amount);
                        var subscriptionPercent = $('#subscription-percent-h').val();
                        let newPayableAmount = current_amount - (subscriptionPercent * current_amount / 100);
                        $('#subscription-amout').text(response.data.currency_symbol+''+newPayableAmount);
                        $('#pickup_now').attr("data-subscriptionPayableAmount",newPayableAmount);
                    }
                    let elementsToHide = document.getElementsByClassName("cab_payment_method_selection");
                    if(current_amount <= 0){
                        current_amount = 0.00;
                        // Loop through the selected elements and set an inline style with !important
                        for (let i = 0; i < elementsToHide.length; i++) {
                            elementsToHide[i].style.setProperty('display', 'none', 'important');
                        }
                    }else{
                        for (let i = 0; i < elementsToHide.length; i++) {
                            elementsToHide[i].style.removeProperty('display');
                        }
                    }
                    $('.cab-detail-box #real_amount').text(response.data.currency_symbol+''+current_amount);
                }
            },
            error: function (reject) {
                if (reject.status === 422) {
                    var message = $.parseJSON(reject.responseText);
                    sweetAlert.error(message.message,"");
                  //  $(".invalid-feedback.manual_promocode").html("<strong>" + message.message + "</strong>");
                }
            }
        });
    });
    $(document).on("click",".close-promo-code-detail-box",function() {
        $('.promo-box').addClass('d-none');
        $('.cab-detail-box').removeClass('d-none');
    });

    $(document).on("click",".close-cab-detail-box",function() {
        $('.cab-detail-box').addClass('d-none');
        $('.address-form').removeClass('d-none');
    });
    $(document).on("click",".skip-clear",function() {
        $('.cab-detail-box').attr("style", "display: block !important");
        $('.scheduled-ride-list').attr("style", "display: none !important");
    });
    $(document).on("click",".vehical-view-box",function() {
        let product_id = $(this).data('product_id');
        getVehicleDetail(product_id);
    });
    $(document).on("click",".category-view-box",function() {
        let category_id = $(this).data('category_id');
        getProductDetail(category_id);
        initialize();

    });

    function getProductDetail(category_id)
    {

        $("#selected_category_id").val(category_id);
        $.ajax({
            type: "POST",
            dataType: 'json',
            data: {category_id:category_id},
            url: get_rental_view,
            success: function(response) {
               $('#starting_rental_price').val(response.product.price);
               $('#starting_price').text("$"+response.product.price);
               $('.hourly_price').val(response.product.price);
             }
        });
    }
    function getVehicleDetail(product_id, rider_id=0,$recurringformPost = {})
    {
        add_spinner('.cab-booking-loader');
        var locations = [];
        var pickup_location_latitude = $('input[name="pickup_location_latitude[]"]').map(function(){return this.value;}).get();
        var pickup_location_longitude = $('input[name="pickup_location_longitude[]"]').map(function(){return this.value;}).get();
        var destination_location_latitudes = $('input[name="destination_location_latitude[]"]').map(function(){return this.value;}).get();
        var destination_location_longitudes = $('input[name="destination_location_longitude[]"]').map(function(){return this.value;}).get();
        $(pickup_location_latitude).each(function(index, latitude) {
            var data = {};
            data.latitude = latitude;
            data.longitude = pickup_location_longitude[index];
            locations.push(data);
        });
        $(destination_location_latitudes).each(function(index, destination_location_latitude) {
            var data = {};
            data.latitude = destination_location_latitude;
            data.longitude = destination_location_longitudes[index];
            locations.push(data);
        });

        let schedule_datetime = '';
        let rental_price = $('#rental_price').val() ?? 0;
        let rentalHr = $('#rental_hours').val() ?? 0;
        let schedule_datetimeset = $('#schedule_date').val();
        if(schedule_datetimeset != undefined && schedule_datetimeset != 0){
            schedule_datetime = moment(schedule_datetimeset).format('YYYY-MM-DD HH:mm');
        }
        var no_seats_for_pooling = $('input[name="no_seats_for_pooling"]').val();
        var is_cab_pooling = $('input[name="is_cab_pooling_radio"]:checked').val();

        const urlParams = new URLSearchParams(window.location.search);
        const yacht_id = urlParams.get('yacht_id');
        $.ajax({
            type: "POST",
            dataType: 'json',
            data: {locations:locations,rider_id:rider_id, schedule_date_delivery:schedule_datetime, is_cab_pooling:is_cab_pooling, no_seats_for_pooling:no_seats_for_pooling,recurringformPost,rental_hour:parseInt(rentalHr)},
            url: get_product_detail+'/'+product_id,
            success: function(response) {
                console.log({response});
                remove_spinner('.cab-booking-loader');
                if(response.status == 'Success'){
                    $('#cab_detail_box').html('');
                    if(response.data.length != 0){

                        var schedule_date_time = ''
                        if(schedule_datetime !='' && schedule_datetime != undefined){
                            schedule_date_time = moment(schedule_datetime).format('MMM Do YY, h:mm:ss a')
                        }

                        var cabData = _.extend({ Helper: NumberFormatHelper },{result: response.data,schedule_datetime:schedule_date_time});

                        $('.address-form').addClass('d-none');
                        $('.cab-detail-box').removeClass('d-none');
                        if(response.data.faqlist > 0){

                        }
                        var isBid = $('#bid_radio').prop("checked");
                        var isParticularDriver = $('#particular_driver_radio').prop("checked");
                        if(isBid){
                            let vehicle_bid_template = _.template($('#vehicle_bid_template').html());
                            $("#cab_detail_box").append(vehicle_bid_template(cabData)).show();
                        }else if(isParticularDriver){
                            let particular_driver_template = _.template($('#particular_driver_template').html());
                            $("#cab_detail_box").append(particular_driver_template(cabData)).show();
                        }else{

                            let cab_detail_box_template = _.template($('#cab_detail_box_template').html());
                            $("#cab_detail_box").append(cab_detail_box_template(cabData)).show();
                        }

                        if($('input[name="is_cab_pooling_radio"]:checked').val() == 0 || $('input[name="is_cab_pooling_radio"]:checked').val() === undefined)
                        {
                            $(".show_no_of_seats_if_pooling").hide();
                        }else{
                            $(".show_no_of_seats_if_pooling").show();
                        }
                        if(response.data.distance == 0 || response.data.duration == 0)
                        {

                            if($('#selected_category_id').val() == "")
                            {

                                getDistance();
                            }
                        }
                        if($('input[name=is_for_friend]:checked').val()==1){
                            $('.for_friend_fields_div').removeClass('d-none');


                        }else{
                            $('.for_friend_fields_div').addClass('d-none');
                        }

                         $('#selected_rental_product').val(response.data.id);
                         $('#selected_vendor_id').val(response.data.vendor_id);
                         $('.cab-detail-box #real_amount').text(response.data.original_tags_price);

                    }else{
                        $("#cab_detail_box ").html('<p class="text-center my-3">'+ no_result_message +'</p>').show();
                    }
                }
            }
        });
    }

    $(document).on("click",".edit-pickup",function() {
        $(".check-pick-first").css("display", "block");
        $(".check-dropoff-secpond").css("display", "none");
        $('.check-pickup').attr("style", "display: block !important");
        $('.check-dropoff').attr("style", "display: none !important");
        $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').map(function(){
            $(this).hide();
        }).get();
    });

    $(document).on("click","#get-current-location",function() {
        getLocation();

        var latitude             = $('#address-latitude').val();
        var longitude            = $('#address-longitude').val();

        var pickup_location      = $('#pickup_location_latitude').val();
        var destination_location = $('#destination_location_latitude').val();
        var pickupAddress        = $('#address-input').val();
        if(pickup_location == ''){
            $('#pickup_location').val($('#address-input').val());
            $('#pickup_location_latitude').val(latitude);
            $('#pickup_location_longitude').val(longitude);


            var pickupLocationLatitude  = latitude;
            var pickupLocationLongitude = longitude;
            var currentUrl              = window.location.href;
            var queryString             = removeURLParameter(currentUrl, 'pickup_location');
            var perm                    = "?pickup_location=" + $('#address-input').val() + "&pickup_location_latitude=" + pickupLocationLatitude +"&pickup_location_longitude=" + pickupLocationLongitude + (queryString != '' ? "&" + queryString : '');
            window.history.replaceState(null, null, perm);

            $(".check-pick-first").css("display", "none");
            $("#pickup-where-from").html(" "+$('#address-input').val());
            $(".check-dropoff-secpond").css("display", "block");
            $('.check-pickup').attr("style", "display: none !important");
            $(".check-dropoff").css("display", "block");

        }else if(destination_location == ''){
            $('#destination_location').val($('#address-input').val());
            $('#destination_location_latitude').val(latitude);
            $('#destination_location_longitude').val(longitude);

            var currentUrl  = window.location.href;
            var queryString = removeURLParameter(currentUrl, 'destination_location');
            var perm        = "?" + (queryString != '' ? queryString : '') + "&destination_location=" + $('#address-input').val()  + "&destination_location_latitude=" + latitude +"&destination_location_longitude=" + longitude;
            window.history.replaceState(null, null, perm);

            $("#dropoff-where-to").html(" "+$('#address-input').val());
            $('.where-to-first').attr("style", "display: none !important");
            $('.check-dropoff').attr("style", "display: none !important");
            $(".where-to-second").css("display", "block");
            $('.add-more-location').attr("style", "display: block !important");

            $('#search_product_main_div').attr("style", "display: block !important");
            $('.location-list').attr("style", "display: none !important");

        }else{
            $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').map(function(){
                if(this.value == ''){
                    var inputId = this.id;
                    $('#'+inputId).val(currentLocation);
                    var random_id = $(this).data( "rel" );
                    $('#destination_location_latitude_'+random_id).val(currentLocationLatitude);
                    $('#destination_location_longitude_'+random_id).val(currentLocationLongitude);

                    $('#search_product_main_div').attr("style", "display: block !important");
                    $('.location-list').attr("style", "display: none !important");

                    let destination_location_template_li = _.template($('#destination_location_template_li').html());
                    var destination_location = $('#destination_location_'+random_id).val();
                    $('#location_input_main_div li:last-child').after(destination_location_template_li({random_id:random_id}));
                    $("#dropoff-where-to-"+random_id).html(" "+ currentLocation);

                    var currentUrl                   = window.location.href;
                    var queryString                  = removeURLParameter(currentUrl, 'destination_location_'+random_id);
                    var perm = "?" + (queryString != '' ? queryString : '') + "&destination_location_"+random_id+"=" + currentLocation  + "&destination_location_latitude_"+random_id+"=" + currentLocationLatitude + "&destination_location_longitude_"+random_id+"=" + currentLocationLongitude;
                    window.history.replaceState(null, null, perm);
                }
            }).get();
        }

        displayLocationCab(latitude, longitude);
        getVendorList();

    });
    $(document).on("click",".edit-dropoff",function() {
        $(".check-dropoff-secpond").css("display", "block");
        $('.add-more-location').attr("style", "display: none !important");
        $('.where-to-second').attr("style", "display: none !important");
        $('.where-to-first').attr("style", "display: block !important");
        $('.check-dropoff').attr("style", "display: block !important");
        $('#destination_location_add_temp').attr("style", "display: none !important");

        $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').map(function(){
            $(this).hide();
        }).get();

    });
    $(document).on("click",".delete-drop-off",function() {
        $(".check-dropoff-secpond").css("display", "block");
        $('.add-more-location').attr("style", "display: none !important");
        $('.where-to-second').attr("style", "display: none !important");
        $('.where-to-first').attr("style", "display: block !important");
        $('.check-dropoff').attr("style", "display: block !important");
        $('#destination_location_add_temp').attr("style", "display: none !important");

        $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').map(function(){
            $(this).hide();
        }).get();
    });

    $(document).on("click",".scheduled-ride",function() {
        $('.location-list').attr("style", "display: none !important");
        $('.scheduled-ride-list').attr("style", "display: block !important");

        $('.recurring-option').attr("style", "display: none !important");
        $('.now-option').attr("style", "display: block !important");

        $(".scheduled-footer").html('<button class="btn btn-solid w-100" id="check-schedule-date-time">Select</button>');
    });

    $(document).on("click",".scheduled-ride-rec",function() {
        $('.location-list').attr("style", "display: none !important");
        $('.scheduled-ride-list').attr("style", "display: block !important");

        $('.now-option').attr("style", "display: none !important");
        $('.recurring-option').attr("style", "display: block !important");

        $(".scheduled-footer").html('<button class="btn btn-solid w-100" id="check-schedule-date-time-rec">Select</button>');
        // var fromDate = moment();
        // var toDate   = moment().add(31, 'days');
       // enumerateDaysBetweenDates(fromDate, toDate);
    });

    var enumerateDaysBetweenDates = function(startDate, endDate) {
        var now = startDate, dates = [];
        var i = 1;
        while (now.isSameOrBefore(endDate)) {
            i++;
            if(now.format('MMDDYYYY') == moment().format('MMDDYYYY')){
                var lableText = 'Today';
            }else{
                var lableText = now.format('ddd, D MMM');
            }
            var scheduledDate = '<div class="form-check radio check-active" id="schedule-date-'+now.format('MMDDYYYY')+'" ><input class="form-check-input" id="'+now.format('MMDDYYYY')+'" type="radio"'+ ((now.format('MMDDYYYY') == moment().format('MMDDYYYY')) ? 'checked': '') +' onclick="appendScheduleTime(this)" name="scheduledDate" data-mdi="schedule-date-'+now.format('MMDDYYYY')+'" value="'+now.format('MM-DD-YYYY')+'"><label class="radio-label" id="lable-schedule-date-'+now.format('MMDDYYYY')+'" for="'+now.format('MMDDYYYY')+'">'+lableText+'</label></div>';
            $(".date-radio-list").append(scheduledDate);
            if(now.format('MMDDYYYY') == moment().format('MMDDYYYY')){
                $('.scheduled-ride-list').find('.scheduleTime').remove();
                let scheduleTime_template = _.template($('#scheduleTime_template').html());
                var mainDivId = 'schedule-date-'+now.format('MMDDYYYY');
                $("#"+mainDivId).append(scheduleTime_template).show();
            }
            now.add(1, 'days');
        }

        $(".scheduled-footer").html('<button id="check-schedule-date-time" onclick="getScheduleDateTime(this)" disabled>Select</button>');
    };

    $(document).on("click","#check-schedule-date-time-rec",function() {

        $('.cab-detail-box').attr("style", "display: block !important");
        $('.scheduled-ride-list').attr("style", "display: none !important");

        var currentUrl  = window.location.href;
        var queryString = removeURLParameterNew(currentUrl, 'schedule_date');
        var perm = "?" + (queryString != '' ? queryString : '') + "&schedule_date=";
        window.history.replaceState(null, null, perm);

        // getListOfCabs();

    });

    $(document).on("click","#check-schedule-date-time",function() {
        var scheduleDateTimeSet = $('#schedule_pickup_date').val();

        if(scheduleDateTimeSet != '' || scheduleDateTimeSet != undefined ){
            //if(moment(scheduleDateTimeSet).format('MMDDYYYY') != moment().format('MMDDYYYY') ){
                var currentUrl  = window.location.href;
                var queryString = removeURLParameterNew(currentUrl, 'schedule_date');
                var perm = "?" + (queryString != '' ? queryString : '') + "&schedule_date=" + scheduleDateTimeSet;
                window.history.replaceState(null, null, perm);

                $('#schedule_date').val(scheduleDateTimeSet);
                $('.scheduleDateTimeApnd').text( moment(scheduleDateTimeSet).format('MMM Do YY, h:mm:ss a'));
                $('#schedule_datetime').val(scheduleDateTimeSet);


            $('.cab-detail-box').attr("style", "display: block !important");
            $('.scheduled-ride-list').attr("style", "display: none !important");
            getListOfCabs();
        }else{

        }

    });


    $(document).on("click",".apremove",function() {
        var destination_location_add_temp = $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').length;

        if(destination_location_add_temp == 0){
            return false;
        }else if(destination_location_add_temp == 1){
            var destination_location_names = $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').map(function(){
                if(this.value == ''){
                    return "empty";
                }
            }).get();

            if(destination_location_names[0] == 'empty'){
                return false;
            }

            var destination_location = $("#destination_location").val();
            if(destination_location == undefined){
                return false;
            }

        }else if(destination_location_add_temp == 2){
            var destination_location_names = $('#destination_location_add_temp').find('input[name="destination_location_name[]"]').map(function(){
                if(this.value == ''){
                    return "empty";
                }
            }).get();

            var destination_location = $("#destination_location").val();
            if(destination_location_names[0] == 'empty' && destination_location == undefined){
                return false;
            }

        }

        var random_id = $(this).data('rel');

        if(random_id == ''){
            $(".where-to-second").remove();
            $("#destination_location").remove();
            $("#destination_location_latitude").remove();
            $("#destination_location_longitude").remove();

            var currentUrl = window.location.href;
            query1 = removeURLParameter(currentUrl, 'destination_location');
            var perm        = (query1 != '' ? "?" + query1 : '');
            window.history.replaceState(null, null, perm);

            var currentUrl2 = window.location.href;
            query2 = removeURLParameter(currentUrl2, 'destination_location_latitude');
            var perm        = (query2 != '' ? "?" + query2 : '');
            window.history.replaceState(null, null, perm);

            var currentUrl3 = window.location.href;
            var query3 = removeURLParameter(currentUrl3, 'destination_location_longitude');
            var perm        = (query3 != '' ? "?" + query3 : '');
            window.history.replaceState(null, null, perm);
        }else{

            $("#dots_"+random_id).remove();
            $("#destination_location_"+random_id).remove();
            $("#destination_location_latitude_"+random_id).remove();
            $("#destination_location_longitude_"+random_id).remove();

            var currentUrl = window.location.href;
            query1 = removeURLParameter(currentUrl, 'destination_location_'+random_id);
            var perm        = (query1 != '' ? "?" + query1 : '');
            window.history.replaceState(null, null, perm);

            var currentUrl2 = window.location.href;
            query2 = removeURLParameter(currentUrl2, 'destination_location_latitude_'+random_id);
            var perm        = (query2 != '' ? "?" + query2 : '');
            window.history.replaceState(null, null, perm);

            var currentUrl3 = window.location.href;
            var query3 = removeURLParameter(currentUrl3, 'destination_location_longitude_'+random_id);
            var perm        = (query3 != '' ? "?" + query3 : '');
            window.history.replaceState(null, null, perm);
        }

        const waypts = [];
        var destination_location_names = $('input[name="destination_location_name[]"]').map(function(){
            return this.value;
         }).get();

         $(destination_location_names).each(function(index, destination_location_name) {
             waypts.push({
                 location: destination_location_name,
                 stopover: true,
               });
         });

        var random_id = $("#destination_location_add_temp input:last").data("rel");

        let origin = $('#pickup_location').val();
        let destination = (random_id != undefined) ? $('#destination_location_'+random_id).val() : $('#destination_location').val();
        if(origin && destination){
            directionsService.route({
                origin: origin,
                waypoints:waypts,
                optimizeWaypoints:true,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING,
            }, function(response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                var point = response.routes[0].legs[0];
                directionsDisplay.setDirections(response);
                getVendorList();
                } else {
                window.alert('Directions request failed due to ' + status);
                return false;
                }
            });
        }


    });

    $(document).on("click",".edit-other-stop",function() {
        var random_id = $(this).attr("id");
        $("#destination_location_add_temp").attr("style", "display: block !important");
        $("#destination_location_"+random_id).attr("style", "display: block !important");
        initializeNew(random_id);
    });

    var query = window.location.search.substring(1);
   // var query = str.replace(query.replace('+', ' ');
  //  var uri_enc = encodeURIComponent(uri);
  //  var uri_dec = decodeURIComponent(uri_enc);
    if(query != ''){
        var vars = query.split('&');
        for(i = 0; i<vars.length; i++){
            vars[i] = decodeURIComponent(vars[i].replace(/\+/g, ' '));
            var perm = vars[i].split('=');
            if(perm[0] == 'pickup_location'){
                var pickup_location = window.unescape(perm[1]);
                $('#pickup_location').val(pickup_location);
            }else if(perm[0] == 'pickup_location_latitude'){
                $('#pickup_location_latitude').val(perm[1]);
            }else if(perm[0] == 'pickup_location_longitude'){
                $('#pickup_location_longitude').val(perm[1]);
            }else if(perm[0] == 'destination_location'){
                var destination_location = window.unescape(perm[1]);
                $('#destination_location').val(destination_location);
            }else if(perm[0] == 'destination_location_latitude'){
                $('#destination_location_latitude').val(perm[1]);
            }else if(perm[0] == 'destination_location_longitude'){
                $('#destination_location_longitude').val(perm[1]);
            }else if(perm[0] == 'schedule_date'){
                $('#schedule_date').val(perm[1]);
                $('#schedule_date_time').val(perm[1]);
                $('.scheduleDateTimeApnd').html(moment(perm[1]).format('MMM Do YY, h:mm:ss a'));
            }else if(perm[0] == 'schedule_date_set'){ //schedule_date_set
                $('.scheduleDateTimeApnd').html(perm[1]);
            }
        }

        var pickup_location      = $("#pickup_location").val();
        var destination_location = $("#destination_location").val();
        if(pickup_location != '' && destination_location != ''){
            $(".check-pick-first").css("display", "none");
            $("#pickup-where-from").html(" "+pickup_location);
            $(".check-dropoff-secpond").css("display", "block");
            $('.check-pickup').attr("style", "display: none !important");
            $(".check-dropoff").css("display", "block");

            $("#dropoff-where-to").html(" "+destination_location);
            $('.where-to-first').attr("style", "display: none !important");
            $('.check-dropoff').attr("style", "display: none !important");
            $(".where-to-second").css("display", "block");
            $('.add-more-location').attr("style", "display: block !important");
            getVendorList();
            setTimeout(function(){
                initMap2();
            }, 1000);
        }else if(pickup_location != ''){
            $(".check-pick-first").css("display", "none");
            $("#pickup-where-from").html(" "+pickup_location);
            $(".check-dropoff-secpond").css("display", "block");
            $('.check-pickup').attr("style", "display: none !important");
            $(".check-dropoff").css("display", "block");
        }
    }


    function initMap2(random_id = '') {
        var locations = [];
        let pickup_location_latitude  = $('#pickup_location_latitude').val();
        let pickup_location_longitude = $('#pickup_location_longitude').val();

        var pointA = new google.maps.LatLng(pickup_location_latitude, pickup_location_longitude);
        map = new google.maps.Map(document.getElementById('booking-map'), {zoom: 7,center: pointA});
        map.setOptions({ styles:  styles});
        directionsService = new google.maps.DirectionsService;
        directionsDisplay = new google.maps.DirectionsRenderer({map: map});
        calculateAndDisplayRoute(directionsService, directionsDisplay, random_id);
    }
    function calculateAndDisplayRoute(directionsService, directionsDisplay, random_id = '') {
        const waypts = [];
        if(random_id != ''){
            if($("#dots_" + random_id).length == 1){
                var destination_location = $('#destination_location_'+random_id).val();
                $("#dropoff-where-to-"+random_id).html(" "+destination_location);
            }else{
                let destination_location_template_li = _.template($('#destination_location_template_li').html());
                var destination_location = $('#destination_location_'+random_id).val();
                $('#location_input_main_div li:last-child').after(destination_location_template_li({random_id:random_id}));
                $("#dropoff-where-to-"+random_id).html(" "+destination_location);
            }

        }
        var destination_location_names = $('input[name="destination_location_name[]"]').map(function(){
           return this.value;
        }).get();

        $(destination_location_names).each(function(index, destination_location_name) {
            waypts.push({
                location: destination_location_name,
                stopover: true,
              });
        });

        let origin ='';
        if($('#pickup_location').val()=="Your Location"){
            var latmy = parseFloat($("#address-latitude").val());
            var longmy = parseFloat($("#address-longitude").val());
            origin = {lat: latmy, lng: longmy};
        }else{
            origin = $('#pickup_location').val();
        }

        let destination = (random_id != '') ? $('#destination_location_'+random_id).val() : $('#destination_location').val();
        if(origin && destination){
            directionsService.route({
                origin: origin,
                waypoints:waypts,
                optimizeWaypoints:true,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING,
            }, function(response, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                  var point = response.routes[0].legs[0];
                  directionsDisplay.setDirections(response);
                  getVendorList();
                } else {
                  window.alert('Directions request failed due to ' + status);
                  return false;
                }
            });
        }
    }
    initialize();
    function initialize() {
      var input = document.getElementById('pickup_location');
      var input2 = document.getElementById('destination_location');
      var input3 = document.getElementById('pickup_hourly_location');

      if(input){
        var autocomplete = new google.maps.places.Autocomplete(input);
        var autocomplete2 = new google.maps.places.Autocomplete(input2);
        if(is_map_search_perticular_country){
            autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
            autocomplete2.setComponentRestrictions({'country': [is_map_search_perticular_country]});
        }
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            $('#pickup_location_latitude').val(place.geometry.location.lat());
            $('#pickup_location_longitude').val(place.geometry.location.lng());
            initMap2();

            var pickup_location = $("#pickup_location").val();
            if(pickup_location != ""){
                var pickupLocationLatitude  = place.geometry.location.lat();
                var pickupLocationLongitude = place.geometry.location.lng();
                var currentUrl              = window.location.href;
                var queryString             = removeURLParameter(currentUrl, 'pickup_location');
                var perm                    = "?pickup_location=" + pickup_location + "&pickup_location_latitude=" + pickupLocationLatitude +"&pickup_location_longitude=" + pickupLocationLongitude + (queryString != '' ? "&" + queryString : '');
                window.history.replaceState(null, null, perm);

                $(".check-pick-first").css("display", "none");
                $("#pickup-where-from").html(" "+pickup_location);
                $(".check-dropoff-secpond").css("display", "block");
                $('.check-pickup').attr("style", "display: none !important");
                $(".check-dropoff").css("display", "block");
            }

        });
        google.maps.event.addListener(autocomplete2, 'place_changed', function () {
            var place2 = autocomplete2.getPlace();
            $('#destination_location_latitude').val(place2.geometry.location.lat());
            $('#destination_location_longitude').val(place2.geometry.location.lng());
            initMap2();

            var pickup_location      = $("#pickup_location").val();
            var destination_location = $("#destination_location").val();
            if(pickup_location != '' && destination_location != ''){

                var currentUrl  = window.location.href;
                var queryString = removeURLParameter(currentUrl, 'destination_location');
                var perm        = "?" + (queryString != '' ? queryString : '') + "&destination_location=" + destination_location  + "&destination_location_latitude=" + place2.geometry.location.lat() +"&destination_location_longitude=" + place2.geometry.location.lng();
                window.history.replaceState(null, null, perm);

                $("#dropoff-where-to").html(" "+destination_location);
                $('.where-to-first').attr("style", "display: none !important");
                $('.check-dropoff').attr("style", "display: none !important");
                $(".where-to-second").css("display", "block");
                $('.add-more-location').attr("style", "display: block !important");
            }

        });
      }
      if(input3){
        var hourly_autocomplete = new google.maps.places.Autocomplete(input3);
        if(is_map_search_perticular_country){
            hourly_autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
        }
        google.maps.event.addListener(hourly_autocomplete, 'place_changed', function () {
            var place = hourly_autocomplete.getPlace();
            $('#pickup_location_latitude').val(place.geometry.location.lat());
            $('#pickup_location_longitude').val(place.geometry.location.lng());
            initMap2();

            var pickup_location = $("#pickup_location").val();
            if(pickup_location != ""){
                var pickupLocationLatitude  = place.geometry.location.lat();
                var pickupLocationLongitude = place.geometry.location.lng();
                var currentUrl              = window.location.href;
                var queryString             = removeURLParameter(currentUrl, 'pickup_location');
                var perm                    = "?pickup_location=" + pickup_location + "&pickup_location_latitude=" + pickupLocationLatitude +"&pickup_location_longitude=" + pickupLocationLongitude + (queryString != '' ? "&" + queryString : '');
                window.history.replaceState(null, null, perm);

                $(".check-pick-first").css("display", "none");
                $("#pickup-where-from").html(" "+pickup_location);
                $(".check-dropoff-secpond").css("display", "block");
                $('.check-pickup').attr("style", "display: none !important");
                $(".check-dropoff").css("display", "block");
            }

        });

      }
    }



    function removeURLParameter(url, parameter) {
        //prefer to use l.search if you have a location/link object
        var urlparts = url.split('?');
        if (urlparts.length >= 2) {

            var prefix = encodeURIComponent(parameter) + '=';
            var pars = urlparts[1].split(/[&;]/g);

            //reverse iteration as may be destructive
            for (var i = pars.length; i-- > 0;) {
                //idiom for string.startsWith
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }
            return (pars.length > 0 ? pars.join('&') : '');
        }
        return false;
    }

    function getQueryStringVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split('&');
        for (var i=0; i<vars.length; i++) {
            var pair = vars[i].split('=');
            if (pair[0] == variable) {
                return pair[1];
            }
        }

        return false;
   }

    function getDistance(){
            //Find the distance
            var distanceService = new google.maps.DistanceMatrixService();
            if(distance_unit == "IMPERIAL"){
                var unitSystem = google.maps.UnitSystem.IMPERIAL;
            }else{
                var unitSystem = google.maps.UnitSystem.METRIC;
            }
            distanceService.getDistanceMatrix({
            origins: [$("#pickup_location").val()],
            destinations: [$("#destination_location").val()],
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: unitSystem,
            durationInTraffic: true,
            avoidHighways: false,
            avoidTolls: false
        },
        function (response, status) {
            if (status !== google.maps.DistanceMatrixStatus.OK) {
            } else {
                $("#distance").text(response.rows[0].elements[0].distance.text).show();
                $("#duration").text(response.rows[0].elements[0].duration.text).show();
            }
        });
    }

    function getLocation() {
        if (navigator.geolocation) {
            var options = {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
              };
            navigator.geolocation.getCurrentPosition(showPosition, errorcallback, options);
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }

    function errorcallback(positionerror) {
        if (window.console) {
            //console.log(positionerror);
            $('#address-latitude').val('30.7120453');
            $('#address-longitude').val('76.8144185');
            $('#address-input').val('PR67+RQ Chandigarh, India');
          }
    }

    function showPosition(position) {
        let lat = position.coords.latitude;
        let long = position.coords.longitude;

        var is_chrome = /chrom(e|ium)/.test( navigator.userAgent.toLowerCase() );
        var is_ssl    = 'https:' == document.location.protocol;
        if( is_chrome && ! is_ssl ){
            return false;
        }

        var google_map_pos = new google.maps.LatLng( lat, long );


        var google_maps_geocoder = new google.maps.Geocoder();
        google_maps_geocoder.geocode(
            { 'latLng': google_map_pos },
            function( results, status ) {
                if ( status == google.maps.GeocoderStatus.OK && results[0] ) {
                    //console.log( results[0].formatted_address ); committed by surednder
                    //mylocationAdd = results[0].formatted_address; committed by surednder
                    $('#address-input').val(results[0].formatted_address);
                }else{
                    $('#address-input').val('Your Location');
                }
            }
        );

        $('#address-latitude').val(lat);
        $('#address-longitude').val(long);
        displayLocationCab(lat, long);
    }
    if (!selected_address) {
        getLocation();
    }
    let lat = $("#booking-latitude").val();
    let long = $("#booking-longitude").val();
    displayLocationCab(lat, long);
    function displayLocationCab(latitude, longitude) {
        var geocoder;
        geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(latitude, longitude);
        const map = new google.maps.Map(document.getElementById('booking-map'), {
            center: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
            zoom: 13
        });
        map.setOptions({ styles:  styles});
        var icon_set = {
            url: live_location, // url
            scaledSize: new google.maps.Size(30, 30), // scaled size
            origin: new google.maps.Point(0,0), // origin
            anchor: new google.maps.Point(0, 0) // anchor
        };
        const marker = new google.maps.Marker({
            map: map,
            position: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
            icon : icon_set,
        });
        geocoder.geocode(
            { 'latLng': latlng },
            function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        var add = results[0].formatted_address;
                        var value = add.split(",");
                        count = value.length;
                        country = value[count - 1];
                        state = value[count - 2];
                        city = value[count - 3];
                        $("#addHeader1-input").val(add);
                        $("#location_search_wrapper .homepage-address span").text(value).attr({ "title": value, "data-original-title": value });
                    }else {
                    }
                }else {
                   // $("#address-input").val("Geocoder failed due to: " + status);
                }
            }
        );
    }


    function cabBookingPaymentOptions(payment_option_id, order='')
    {
        var action =  payment_option_id;
        switch (action) {

            case '6':
                paymentViaPayfast('', order);
                break;
            case '18':
                paymentViaAuthorize('', order);
                break;

            case '47':
                paymentViaKhalti('', order);
                break;

            case '55':
                paymentViaDataTrans('',payment_option_id,order);
            break;

        }

    }

    function stripePaymentMethodHandler(result) {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        // let payment_option_id = paymentAjaxData.payment_option_id;

        paymentAjaxData.payment_method_id = result.paymentMethod.id;
        // paymentAjaxData.payment_option_id = payment_option_id;

        if (path.indexOf("cart") !== -1) {
            payment_form = 'cart';
            total_amount = cartElement.val();
        } else if ((path.indexOf("wallet") !== -1) || ((typeof cabbookingwallet !== 'undefined') && (cabbookingwallet == 1))) {
            payment_form = 'wallet';
            total_amount = walletElement.val();
        } else if (path.indexOf("subscription") !== -1) {
            payment_form = 'subscription';
            total_amount = subscriptionElement.val();
            // paymentAjaxData = $("#subscription_payment_form").serializeArray();
            paymentAjaxData.subscription_id = $("#subscription_payment_form #subscription_id").val();
        } else if ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            paymentAjaxData.order_number = $("#order_number").val();
        }
        paymentAjaxData.payment_form = payment_form;
        paymentAjaxData.total_amount = total_amount;

        if (result.error) {
            swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something Went Wrong',
            }).then(function() {
                window.location.reload();
            });
        } else {
            fetch('/payment/payment_init', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-Token": $('input[name="_token"]').val()
                  },
                  credentials: "same-origin",
                body: JSON.stringify(paymentAjaxData)
            }).then(function(result) {
                // Handle server response (see Step 4)
                result.json().then(function(json) {
                    handleServerResponse(json);
                })
            });
        }
    }

    function handleServerResponse(response) {
        if (response.error) {
            swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: response.error,
            }).then(function() {
                window.location.reload();
            });
            // Show error from server on payment form
        } else if (response.requires_action) {
            // Use Stripe.js to handle required card action
            stripe.handleCardAction(
                response.payment_intent_client_secret
            ).then(handleStripeJsResult);
        } else {
            // console.log(response);
            // Show success message
            setTimeout(() => {
                window.location.href = response.result;
            }, 1500);
        }
    }

    function handleStripeJsResult(result) {
        if (result.error) {
            swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: result.error.message,
            }).then(function() {
                window.location.reload();
            });
            // Show error in payment form
        } else {
            paymentAjaxData.payment_intent_id = result.paymentIntent.id;

            // The card action has been handled
            // The PaymentIntent can be confirmed again on the server
            fetch('/payment/payment_init', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-Token": $('input[name="_token"]').val()
                  },
                body: JSON.stringify(paymentAjaxData)
            }).then((response) => response.json())
            .then((responseJSON) => {
                $('#proceed_to_pay_loader').hide();
                window.location.href = responseJSON.result;
            });
        }

    }


});




function appendScheduleTime(thisObj){
    $('.scheduled-ride-list').find('.scheduleTime').remove();
    let scheduleTime_template = _.template($('#scheduleTime_template').html());
    var mainDivId = $(thisObj).data('mdi');
    $("#"+mainDivId).append(scheduleTime_template).show();
}

function getScheduleDateTime(thisObj){
    var mainDivId      = $('input[name=scheduledDate]:checked').data('mdi');
    var scheduledDate  = $("#lable-"+mainDivId).text();
    var scheduleHour   = $('.scheduleHour').find(":selected").val();
    var scheduleMinute = $('.scheduleMinute').find(":selected").val();
    var scheduleAmPm   = $('.scheduleAmPm').find(":selected").val();
    var waitTime       = parseFloat(scheduleMinute) + Number(10);
    var scheduleDateTime = scheduledDate + ", " + scheduleHour + ":" + scheduleMinute + " " + scheduleAmPm + " - " + scheduleHour + ":" + waitTime + " " + scheduleAmPm;
    var scheduleDateRadio =  $('input[name=scheduledDate]:checked').val();
    var scheduleHourHtml   = $('.scheduleHour').find(":selected").html();
    var scheduleMinuteHtml = $('.scheduleMinute').find(":selected").html();
    var scheduleDateTimeSet = scheduleDateRadio +" "+ scheduleHourHtml + ":" + scheduleMinuteHtml + " " + scheduleAmPm;
    var currentUrl  = window.location.href;
    var queryString = removeURLParameterNew(currentUrl, 'schedule_date');
    var perm = "?" + (queryString != '' ? queryString : '') + "&schedule_date=" + scheduleDateTimeSet;
    window.history.replaceState(null, null, perm);


    var currentUrl  = window.location.href;
    var queryString = removeURLParameterNew(currentUrl, 'schedule_date_set');
    var perm = "?" + (queryString != '' ? queryString : '') +"&schedule_date_set=" + scheduleDateTime;
    window.history.replaceState(null, null, perm);

    $('#schedule_date').val(scheduleDateTimeSet);
    $('.scheduleDateTimeApnd').text(scheduleDateTime);
    $('#schedule_datetime').val(scheduleDateTimeSet);


    $('.cab-detail-box').attr("style", "display: block !important");
    $('.scheduled-ride-list').attr("style", "display: none !important");

    getListOfCabs();
}

function removeURLParameterNew(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts = url.split('?');
    if (urlparts.length >= 2) {

        var prefix = encodeURIComponent(parameter) + '=';
        var pars = urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i = pars.length; i-- > 0;) {
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }
        return (pars.length > 0 ? pars.join('&') : '');
    }
    return false;
}

function checkScheduleDateTime(thisObj){
    var scheduledDate = $('input[name=scheduledDate]:checked').val();
    var scheduleHour   = $('.scheduleHour').find(":selected").val();
    var scheduleMinute = $('.scheduleMinute').find(":selected").val();
    var scheduleAmPm   = $('.scheduleAmPm').find(":selected").val();
    if(scheduledDate != '' && scheduleHour != '' && scheduleMinute != '' && scheduleAmPm != ''){
        $('#check-schedule-date-time').prop('disabled', false);
    }else{
        $('#check-schedule-date-time').prop('disabled', true);
    }

}

function addressInputDisplay(locationWrapper, inputWrapper, input) {
    $(inputWrapper).removeClass("d-none").addClass("d-flex");
    $(locationWrapper).removeClass("d-flex").addClass("d-none");
    var val = $(input).val();
    $(input).focus().val('').val(val);
}

function addressInputHide(locationWrapper, inputWrapper, input) {
    $(inputWrapper).addClass("d-none").removeClass("d-flex");
    $(locationWrapper).addClass("d-flex").removeClass("d-none");
}

function initMap() {
    const autocompletes = [];
    const locationInputs = document.getElementsByClassName("map-input");
    const geocoder = new google.maps.Geocoder;
    for (let i = 0; i < locationInputs.length; i++) {
        const input = locationInputs[i];
        const fieldKey = input.id.replace("-input", "");
        const isEdit = document.getElementById(fieldKey + "-latitude").value != '' && document.getElementById(fieldKey + "-longitude").value != '';
        const latitude = parseFloat(document.getElementById(fieldKey + "-latitude").value) || -33.8688;
        const longitude = parseFloat(document.getElementById(fieldKey + "-longitude").value) || 151.2195;
        const map = new google.maps.Map(document.getElementById(fieldKey + '-map'), {
            center: { lat: latitude, lng: longitude },
            zoom: 13
        });
        const marker = new google.maps.Marker({
            map: map,
            position: { lat: latitude, lng: longitude }
        });
        marker.setVisible(isEdit);
        const autocomplete = new google.maps.places.Autocomplete(input);
        if(is_map_search_perticular_country){
            autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
        }
        autocomplete.key = fieldKey;
        autocompletes.push({ input: input, map: map, marker: marker, autocomplete: autocomplete });
    }
    for (let i = 0; i < autocompletes.length; i++) {
        const input = autocompletes[i].input;
        const autocomplete = autocompletes[i].autocomplete;
        const map = autocompletes[i].map;
        const marker = autocompletes[i].marker;
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            marker.setVisible(false);
            const place = autocomplete.getPlace();
            geocoder.geocode({ 'placeId': place.place_id }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    const lat = results[0].geometry.location.lat();
                    const lng = results[0].geometry.location.lng();
                    $(".homepage-address span").text(place.formatted_address);
                    setLocationCoordinates(autocomplete.key, lat, lng);
                }
            });
            if (!place.geometry) {
                window.alert("No details available for input: '" + place.name + "'");
                input.value = "";
                return;
            }
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(13);
            }
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);
        });
    }
}
function setLocationCoordinates(key, lat, lng) {
    const latitudeField = document.getElementById(key + "-" + "latitude");
    const longitudeField = document.getElementById(key + "-" + "longitude");
    latitudeField.value = lat;
    longitudeField.value = lng;
}
google.maps.event.addDomListener(window, 'load', initMap);


$(document).on("click",".btn-price-up-down",function(){
    var product_id = $("#bid_ride_now").attr('data-product_id');
    type      = $(this).attr('data-type');
    var input = $("input[name='cab_bid_price']");
    var currentVal = parseInt(input.val());
    if (!isNaN(currentVal)) {
        if(type == 'minus') {

            if(currentVal > input.attr('min')) {
                input.val(currentVal - 10).trigger('change');
            }
            if(parseInt(input.val()) == input.attr('min')) {
            }

        } else if(type == 'plus') {

            if(currentVal < input.attr('max')) {
                input.val(currentVal + 10).trigger('change');
            }
            if(parseInt(input.val()) == input.attr('max')) {
            }
        }
    } else {
        input.val(input.attr('min'));
    }
});

$(document).on("click",".share_ride_btn",function(){
    $('#rider_section_user').toggle();
});


