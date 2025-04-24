
@if((isset($client_preferences['subscription_mode'])) && ($client_preferences['subscription_mode'] == 1))
<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript" src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script type="text/javascript">
    var subscription_payment_options_url = "{{route('vendor.subscription.plan.select', ':id')}}";
    var user_subscription_purchase_url = "{{route('vendor.subscription.plan.purchase', [$vendor->id, ':id'])}}";
    var user_subscription_cancel_url = "{{route('vendor.subscription.plan.cancel', [$vendor->id, ':id'])}}";
    var payment_stripe_url = "{{route('subscription.payment.stripe')}}";
    var flutterwave_payment_url = "{{route('vendor.subscription.payment')}}";
    // Payment Gateway Key Detail
    var razorpay_api_key = "{{getRazorPayApiKey()??''}}";
    // Client Detail
    var client_company_name = "{{getClientDetail()->company_name}}";
    var client_logo_url = "{{getClientDetail()->logo_image_url}}";
    var digit_count = "{{$client_preference_detail->digit_after_decimal}}";

    // Logged In User Detail
    var logged_in_user_name = "{{Auth::user()->name??''}}";
    var logged_in_user_email = "{{Auth::user()->email??''}}";
    var logged_in_user_phone = "{{Auth::user()->phone_number??''}}";
    var logged_in_user_dial_code = "{{Auth::user()->dial_code??'91'}}";

    // Client Perference  Detail
    var client_preference_web_color = "{{getClientPreferenceDetail()->web_color}}";
    var client_preference_web_rgb_color = "{{getClientPreferenceDetail()->wb_color_rgb}}";
    var stop_accepting_orders = "{{getClientPreferenceDetail()->stop_order_acceptance_for_users ?? 0}}";

    var check_active_subscription_url = "{{route('vendor.subscription.plan.checkActive', [$vendor->id, ':id'])}}";
    var payment_razorpay_url = "{{route('payment.razorpayPurchase')}}";
    var pyment_totalpay_url= "{{ route('make.payment') }}";
    var payment_thawani_url= "{{ route('pay-by-thawanipg') }}";

    var card = '';
    var stripe = '';

    // Razor Pay script
    var razorpay_options = {
        "key": razorpay_api_key, // Enter the Key ID generated from the Dashboard
        "amount": "50000", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
        "currency": "INR",
        "name": client_company_name,
        "description": "Test Transaction",
        "image": client_logo_url,
        "order_id": "order_9A33XWu170gUtm", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
        "handler": function (response){
            alert(response.razorpay_payment_id);
            alert(response.razorpay_order_id);
            alert(response.razorpay_signature);
        },
        "prefill": {
            "name": logged_in_user_name,
            "email": logged_in_user_email,
            "contact": "+"+logged_in_user_dial_code+""+logged_in_user_phone
        },
        "notes": {
            "address": "Razorpay Corporate Office"
        },
        "theme": {
            "color": client_preference_web_color
        }
    };

    function stripeInitialize() {
        stripe = Stripe(stripe_publishable_key);
        var elements = stripe.elements();
        var style = {
            base: {
                fontSize: '16px',
                color: '#32325d',
                borderColor: '#ced4da'
            },
        };
        card = elements.create('card', {
            hidePostalCode: true,
            style: style
        });
        card.mount('#stripe-card-element');
    }

    if ($("#stripe-card-element").length > 0) {
        stripeInitialize();
    }

    $(document).on('change', '#subscription_payment_methods input[name="subscription_payment_method"]', function() {
        var method = $(this).data("payment_option_id");
        if (method == 4) {
            $("#subscription_payment_methods .stripe_element_wrapper").removeClass('d-none');
        } else {
            $("#subscription_payment_methods .stripe_element_wrapper").addClass('d-none');
        }
    });

    $(document).on('click', '.cancel-subscription-link', function() {
        var id = $(this).attr('data-id');
        $('#cancel-subscription-form').attr('action', user_subscription_cancel_url.replace(":id", id));
    });

    $(document).delegate(".subscribe_btn", "click", function() {
        var sub_id = $(this).attr('data-id');
        $.ajax({
            type: "get",
            dataType: "json",
            url: check_active_subscription_url.replace(":id", sub_id),
            success: function(response) {
                if (response.status == "Success") {
                    $.ajax({
                        type: "get",
                        dataType: "json",
                        url: subscription_payment_options_url.replace(":id", sub_id),
                        success: function(response) {
                            if (response.status == "Success") {
                                $("#subscription_payment #subscription_title").html(response.sub_plan.title);
                                $("#subscription_payment #subscription_price").html('$' + response.sub_plan.price);
                                $("#subscription_payment #subscription_frequency").html(response.sub_plan.frequency);
                                $("#subscription_payment #features_list").html(response.sub_plan.features);
                                $("#subscription_payment #subscription_id").val(sub_id);
                                $("#subscription_payment #subscription_amount").val(response.sub_plan.price);
                                $("#subscription_payment #subscription_payment_methods").html('');
                                let payment_method_template = _.template($('#payment_method_template').html());
                                $("#subscription_payment #subscription_payment_methods").append(payment_method_template({
                                    payment_options: response.payment_options
                                }));
                                if (response.payment_options == '') {
                                    $("#subscription_payment .subscription_confirm_btn").hide();
                                }
                                $("#subscription_payment").modal("show");
                                stripeInitialize();
                            }
                        },
                        error: function(error) {
                            var response = $.parseJSON(error.responseText);
                            let error_messages = response.message;
                            $("#error_response .message_body").html(error_messages);
                            $("#error_response").modal("show");
                        }
                    });
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                let error_messages = response.message;
                $("#error_response .message_body").html(error_messages);
                $("#error_response").modal("show");
            }
        });
    });
    $(document).delegate(".subscription_confirm_btn", "click", function() {
        var _this = $(".subscription_confirm_btn");
        _this.attr("disabled", true);
        var selected_option = $("input[name='subscription_payment_method']:checked");
        var payment_option_id = selected_option.data("payment_option_id");
        if ((selected_option.length > 0) && (payment_option_id > 0)) {
            if (payment_option_id == 4) {
                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        $('#stripe_card_error').html(result.error.message);
                        _this.attr("disabled", false);
                    } else {
                        $("#card_last_four_digit").val(result.token.card.last4);
                        $("#card_expiry_month").val(result.token.card.exp_month);
                        $("#card_expiry_year").val(result.token.card.exp_year);
                        paymentViaStripe(result.token.id, '', payment_option_id);
                    }
                });
            }if (payment_option_id == 10) {
                paymentViaRazorpay();
            } else {
                paymentViaPaypal('', payment_option_id);
            }
        } else {
            _this.attr("disabled", false);
            success_error_alert('error', 'Please select any payment option', "#subscription_payment .payment_response");
        }
    });


  function paymentViaRazorpay() {
        let total_amount = 0;
        let tip = 0;
        let path = window.location.pathname;
        let ajaxData = [];
        let subscriptionElement = $("input[name='subscription_amount']");
        total_amount = subscriptionElement.val();
        ajaxData = $("#subscription_payment_form").serializeArray();
        ajaxData.push({
            name: 'returnUrl',
            value: path
        }, {
            name: 'amount',
            value: total_amount
        }, {
            name: 'cancelUrl',
            value: path
        }, {
            name: 'payment_option_id',
            value: 30
        },{
            name: 'payment_from',
            value: 'vendor_subscription'
        });

        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: payment_razorpay_url,
            data: ajaxData,
            success: function(response) {
                if (response.status == "Success") {
                    razorpay_options.amount = response.data.amount;
                    razorpay_options.order_id = response.data.order_id;
                    razorpay_options.currency = response.data.currency;
                    $('#proceed_to_pay_modal').modal('hide');
                    razourPayView(response.data);
                } else {
                    if (cartElement.length > 0) {
                        success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    } else if (walletElement.length > 0) {
                        success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    }
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                if (cartElement.length > 0) {
                    success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (walletElement.length > 0) {
                    success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                }
            }
        });
    }

    window.razorPayCompletePayment = function razorPayCompletePayment(data, response, order='') {
        data.razorpay_payment_id = response.razorpay_payment_id;
        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: "{{ route('payment.razorpayCompletePurchase') }}",
            data: data,
            success: function(response) {
                 location.reload();
            },
            error: function(error) {

            }
        });
    }
     // RazourPay payment gateway
    window.razourPayView = function razourPayView(data, order='') {
        razorpay_options.handler = function (response){
            // startLoader('body','We are processing your transaction...');
            razorPayCompletePayment(data,response, order);
        }
        var rzp1 = new Razorpay(razorpay_options);
        rzp1.on('payment.failed', function (response){
        });
        rzp1.open();
    }



    function paymentViaStripe(stripe_token, address_id, payment_option_id) {
        let total_amount = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let ajaxData = [];
        if (cartElement.length > 0) {
            total_amount = cartElement.val();
        } else if (walletElement.length > 0) {
            total_amount = walletElement.val();
        } else if (subscriptionElement.length > 0) {
            total_amount = subscriptionElement.val();
            ajaxData = $("#subscription_payment_form").serializeArray();
        }
        ajaxData.push({
            name: 'stripe_token',
            value: stripe_token
        }, {
            name: 'amount',
            value: total_amount
        }, {
            name: 'payment_option_id',
            value: payment_option_id
        });
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_stripe_url,
            data: ajaxData,
            success: function(resp) {
                if (resp.status == 'Success') {
                    userSubscriptionPurchase(total_amount, payment_option_id, resp.data.id);
                } else {
                    success_error_alert('error', resp.message, "#subscription_payment_form .payment_response");
                    $(".subscription_confirm_btn").removeAttr("disabled");
                }
            },
            error: function(error) {
                var response = $.parseJSON(error.responseText);
                success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                $(".subscription_confirm_btn").removeAttr("disabled");
            }
        });
    }

    function userSubscriptionPurchase(amount, payment_option_id, transaction_id) {
        var id = $("#subscription_payment_form #subscription_id").val();
        if (id != '') {
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: user_subscription_purchase_url.replace(":id", id),
                data: {
                    amount: amount,
                    payment_option_id: payment_option_id,
                    transaction_id: transaction_id
                },
                success: function(response) {
                    if (response.status == "Success") {
                        location.reload();
                    } else {
                        success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                        $(".subscription_confirm_btn").attr("disabled", false);
                    }
                },
                error: function(error) {
                    var response = $.parseJSON(error.responseText);
                    success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                    $(".subscription_confirm_btn").removeAttr("disabled");
                }
            });
        } else {
            success_error_alert('error', 'Invalid data', "#wallet_topup_form .payment_response");
            $(".topup_wallet_confirm").removeAttr("disabled");
        }
    }
</script>
@endif
