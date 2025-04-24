
//jQuery time
var current_fs, next_fs, previous_fs; //fieldsets
var left, opacity, scale; //fieldset properties which we will animate
var animating; //flag to prevent quick multi-click glitches

$(document).on('click', '.next', function() {



    var div_id = $(this).attr('id');
	if(animating) return false;

    if(div_id == "select_vendor")
    { 

    var rentalTime = $('#datetime-picker').val();
    if (rentalTime == "") {
        sweetAlert.error('Please select a valid date and time for your rental.',"");
        return;
    }
    }
    if(div_id == "choose_rental")
    { 

    var selected_rental_product = $('#selected_rental_product').val();
    if (!selected_rental_product) {
        sweetAlert.error('Please select a cab before proceeding to the next step.',"");
        return;
    }
    }
	animating = true;
	
	current_fs = $(this).parent();
	next_fs = $(this).parent().next();
    let selected_product = $('#selected_rental_product').val();
	//activate next step on progressbar using the index of next_fs
	
    
	//show the next fieldset
	next_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale current_fs down to 80%
			scale = 1 - (1 - now) * 0.2;
			//2. bring next_fs from the right(50%)
			left = (now * 50)+"%";
			//3. increase opacity of next_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({
        'transform': 'scale('+scale+')',
        'position': 'absolute'
      });
			next_fs.css({'left': left, 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeInOutBack'
	});
    
    if(div_id == "select_vendor")
    { 

        var rental_time = $('#datetime-picker').val();


        var rental_hours = $('#rental_hours').val();
        var rental_price = $('#rental_price').val();
        var vendorId = $("#default_cab_vendor_id").val($(this).data('vendor'));
        fetch(get_rental_vehicle_list, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded' // Set the appropriate content type
            },
            body: new URLSearchParams({
                schedule_date_delivery: rental_time,
                category_id: $("#selected_category_id").val(),
                vendor_id: vendorId,
                "_token": csrf_token,
                rental_hours :rental_hours
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.status === 'Success') {
                $('#search_rental_product_main_div').html('');
                $('#search_product_rider_main_div').html('');
        
                if (data.data.length !== 0) {
                    var productData = _.extend({ Helper: NumberFormatHelper }, { results: data.data.products });
                    let products_template = _.template($('#products_template').html());
                    let products_rider_template = _.template($('#products_rider_template').html());
        
                    $("#search_rental_product_main_div").append(products_template(productData));
                    $("#search_product_rider_main_div").append(products_rider_template(productData));
        
                } else {
                    $("#search_rental_product_main_div ").html('<p class="text-center my-3">'+ no_result_message +'</p>').show();
                    $("#search_product_rider_main_div ").html('<p class="text-center my-3">'+ no_result_message +'</p>');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
        
    }
});

$(document).on('click', '.previous', function() {

	if(animating) return false;
	animating = true;
	
	current_fs = $(this).parent();
	previous_fs = $(this).parent().prev();
	
	//de-activate current step on progressbar
	$("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
	
	//show the previous fieldset
	previous_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale previous_fs from 80% to 100%
			scale = 0.8 + (1 - now) * 0.2;
			//2. take current_fs to the right(50%) - from 0%
			left = ((1-now) * 50)+"%";
			//3. increase opacity of previous_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({'left': left});
			previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeInOutBack'
	});
});



        const plusButton = document.getElementById('plusButton');
        const minusButton = document.getElementById('minusButton');
        const boxes = document.querySelectorAll('.custom-box');
        const buttonText = document.getElementById('buttonText');
        let currentIndex = 1;

        plusButton.addEventListener('click', () => {
            if (currentIndex < boxes.length) {
                boxes[currentIndex].classList.add('filled-box');
                currentIndex++;
                updateRentalPrice();
                $('#rental_hours').val(currentIndex);

                
            }
        });

        minusButton.addEventListener('click', () => {
            if (currentIndex > 1) {
                currentIndex--;
                boxes[currentIndex].classList.remove('filled-box');
                updateRentalPrice();
                $('#rental_hours').val(currentIndex);
            }
        });

        function updateRentalPrice() {
            var product_price =$('.hourly_price').val();
            var rental_hours = currentIndex.toString();
              
            var new_product_price = product_price * rental_hours;
            const buttonTextElement = $('.hourly_price');
            if (buttonTextElement) {
                $('.hourly_price').text("$"+new_product_price+"/hr");
                $('#rental_price').val(new_product_price);
                $('#buttonText').text(currentIndex.toString());
                
            }
        }
		
		$("#datetime-picker").flatpickr({
        enableTime: true,
        disableMobile: true,
        dateFormat: "Y-m-d H:i:S",
        minDate: "today",
        time_24hr: true,
        locale: "en"
        });

        document.getElementById('leave-now').addEventListener('click', function (e) {
            e.preventDefault();  
            const currentDateTime = new Date();
            const formattedDateTime = formatDateTime(currentDateTime);
            document.getElementById('datetime-picker').value = formattedDateTime;
        });
        
        function formatDateTime(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Month starts from 0
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
        
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }




        // order create js


        $(document).on("click","#book_hourly_rental",function(e) {
 
             e.preventDefault();

             var pickupLocation = $('#pickup_hourly_location').val().trim();
             if (pickupLocation === '') {
                 sweetAlert.error('Please select a pick-up location.',"");
                 return;
             }
             var pickup_lat = $('#pickup_location_latitude').val().trim();
             if (pickup_lat === '') {
                 sweetAlert.error('Please select a valid pick-up location.',"");
                 return;
             }
          
            let rentalHr = $('#rental_hours').val() ?? 0;

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
            var booking_time = document.getElementById('datetime-picker').value;
          
           // Assuming you have a valid value for schedule_datetimeset (if not, handle this validation separately)
            if (booking_time !== undefined && booking_time !== 0) {
                // Convert schedule_datetimeset to a Date object
                var schedule_datetime = new Date(booking_time);
                var current_datetime = new Date();

                // Compare schedule_datetime with current_datetime
                if (schedule_datetime > current_datetime) {
                    // schedule_datetimeset is in the future, set task_type to 'schedule'
                    var task_type = 'schedule';
                } else {
                    // schedule_datetimeset is in the past or equal to the current time, set task_type to 'now'
                    var task_type = 'now';
                }
            } else {
                // Handle the case where schedule_datetimeset is undefined or 0
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
                // tasks2.push(sample_array);
            });
    
           
            let amount                      = $(this).attr('data-amount');
            let tollamount                  = $(this).attr('data-tollamount');
            let servicechargeamount         = $(this).attr('data-servicechargeamount');
            let totalamount                 = $(this).attr('data-totalamount');
            let subscription_payable_amount = $(this).attr('data-subscriptionPayableAmount');
            let product_image               = $(this).attr('data-image');
            let vendor_id                   = $('#selected_vendor_id').val();
            let coupon_id                   = $(this).attr('data-coupon_id');
            let product_id                  = $('#selected_rental_product').val();
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
                data: { user_product_order_form:product_order_form_element_data,time_zone:time_zone,payment_option_id: payment_option_id, vendor_id: vendor_id, product_id: product_id, coupon_id: coupon_id, amount: amount, tollamount:tollamount, servicechargeamount:servicechargeamount, totalamount:totalamount, subscription_payable_amount:subscription_payable_amount, tasks: tasks, task_type:task_type, schedule_datetime:booking_time, type:type, friendName:friendName, friendPhoneNumber:friendPhoneNumber, no_seats_for_pooling:no_seats_for_pooling, is_cab_pooling:is_cab_pooling,driver_id,unique_id,recurringformPost,share_ride_users,total_other_taxes_string,total_other_taxes,bid_task_type,send_to_all,rental_hours:rentalHr},
                success: function(response) {
    
                    $('#book_hourly_rental').attr('disabled', false);
                    $('#pickup_later').attr('disabled', false);
                    if(response.status == '200'){
                
                        
    
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
                        }
                        else if(payment_option_id == 4){
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
                        }else if(payment_option_id == 30){
                            payWithFlutterWave(response.data);
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
                },
                error:function (error)  
                {
                    return false;
                }
            });
        });
    
        
           $(document).on("click",".rental_payment_method_selection", function(){
            var type = $(this).attr('type');
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: get_payment_options,
                    success: function(response) {
                        if(response.status == 'Success'){
                            $("#payment_modal .modal-body").html('');
                            let payment_methods_template = _.template($('#payment_methods_template').html());
                            var selected = $('#book_hourly_rental').attr("data-payment_method");
                            $("#payment_modal .modal-body").append(payment_methods_template({payment_options: response.data, type:type}));
                            $("#payment_modal .select_cab_payment_method[value='"+selected+"']").prop("checked", true);

                            
                        }
                    }
                });
                // clearInterval(driverInterval);
           });

           function isValidDateTime(dateTime) {
            // You can use a date/time validation library or write custom code here.
            // This is a basic example, and you may need to adjust it to match your date/time format.
            var regex = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})$/;
            if (!regex.test(dateTime)) {
                return false;
            }
        
            // Additional checks can be added to validate specific date and time requirements.
        
            // Example: Check if the date is not in the past
            var selectedDate = new Date(dateTime);
            var currentDate = new Date();
            if (selectedDate < currentDate) {
                return false;
            }
        
            return true;
        }


        $('#label_for_me1').click(function() {
            $('#label_for_me').addClass('active');
            $('#label_for_friend').removeClass('active');
            $('.address-form').removeClass('d-none');
            $(".hourly-rental-container").addClass('d-none');
            $('.hourly-rental-container').empty();
        $(".hourly-rental-container").removeClass('d-none');
        $(".hourly-rental-container").removeClass('active');
        $('.location-containerNew').removeClass('d-none');

        });
        $('#label_for_friend1').click(function() {
            $('#label_for_me').removeClass('active');
            $('#label_for_friend').addClass('active');
            $('.address-form').removeClass('d-none');
            $(".hourly-rental-container").addClass('d-none');
            $(".hourly-rental-container").removeClass('active');
            $('.hourly-rental-container').empty();
            $('.location-containerNew').removeClass('d-none');
            $('.check-dropoff-secpond').removeClass('d-none');
        });
       
        $('#label_for_hourly_rental1').click(function() {

        $('.address-form').addClass('d-none');
        $('.for_friend').removeClass('active');
        $(".hourly-rental-container").removeClass('d-none');
        $('#label_for_friend').removeClass('active');
        $('.location-containerNew').addClass('d-none');
        $('.hourly-rental-container').empty();
      
        // Make an AJAX request to load the view.
        $.ajax({
            url: "{{route('get-rental-view')}}",
            method: 'POST',
            success: function(response) {
                // Append the retrieved view to the desired element.
                $('.hourly-rental-container').html(response.view);
                
            },
            error: function(xhr, status, error) {
                // Handle errors if necessary.
            }
        });
        });
