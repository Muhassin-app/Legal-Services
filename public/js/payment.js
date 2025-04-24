// Razor Pay script
var razorpay_options = {
    "key": razorpay_api_key, // Enter the Key ID generated from the Dashboard
    "amount": "50000", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
    "currency": "INR",
    "name": client_company_name,
    "description": "Test Transaction",
    "image": client_logo_url,
    "order_id": "order_9A33XWu170gUtm", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
    "handler": function (response) {
        alert(response.razorpay_payment_id);
        alert(response.razorpay_order_id);
        alert(response.razorpay_signature);
    },
    "prefill": {
        "name": logged_in_user_name,
        "email": logged_in_user_email,
        "contact": "+" + logged_in_user_dial_code + "" + logged_in_user_phone
    },
    "notes": {
        "address": "Razorpay Corporate Office"
    },
    "theme": {
        "color": client_preference_web_color
    }
};

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    let queryString = window.location.search;
    let path = window.location.pathname;
    let urlParams = new URLSearchParams(queryString);
    if ((urlParams.has('gateway')) && (urlParams.get('gateway') == 'paystack')) {
        $('.spinner-overlay').show();
        let tipAmount = 0;
        let paymentFrom = '';
        if (urlParams.has('tip')) {
            tipAmount = urlParams.get('tip');
        }
        order_number = 0;
        if (urlParams.has('ordernumber')) {
            order_number = urlParams.get('ordernumber');

        }
        if (urlParams.has('payment_from')) {
            paymentFrom = urlParams.get('payment_from');
            if (paymentFrom == "pickup_delivery") {
                path = "pickup_delivery";
            }

        }
    }


    window.paymentViaPaystack = function paymentViaPaystack(address_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let cabElement = $("#pickup_now");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");

        let ajaxData = {};

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            ajaxData.tip = tip;
            ajaxData.address_id = address_id;
            ajaxData.payment_form = 'cart';
            ajaxData.order_number = order.order_number;

        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('totalamount');
            ajaxData.payment_form = 'pickup_delivery';
            ajaxData.order_number = order.order_number;
            ajaxData.reload_route = order.route;
        }
        else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            ajaxData.payment_form = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            ajaxData.subscription_id = subscription_id.val();
            ajaxData.payment_form = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            ajaxData.payment_form = 'tip';
            ajaxData.order_number = $("#order_number").val();


        }
        ajaxData.amount = total_amount;
        ajaxData.returnUrl = path;
        ajaxData.cancelUrl = path;


        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_paystack_url,
            data: ajaxData,
            success: function (response) {
                if (response.status == "Success") {
                    $('#proceed_to_pay_modal').modal('hide');
                    window.location.href = response.data;
                } else {
                    if (cartElement.length > 0) {
                        success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    } else if (walletElement.length > 0) {
                        success_error_('error', response.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    }
                }
            },
            error: function (error) {
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

    function paymentSuccessViaPaystack(amount, reference, path, tip = 0, order_number = 0) {
        console.log(path);
        let address_id = 0;
        let payment_form = '';
        if (path.indexOf("cart") !== -1) {

            $("#order_placed_btn, .proceed_to_pay").attr("disabled", true);
            address_id = $("input:radio[name='address_id']:checked").val();
            payment_form = "cart"
        } else if (path.indexOf("wallet") !== -1) {

            $("#topup_wallet_btn, .topup_wallet_confirm").attr("disabled", true);
            payment_form = "wallet"
        }
        else if (path.indexOf("wallet") !== -1) {

            payment_form = "wallet"
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_success_paystack_url,
            data: { 'amount': amount, 'reference': reference },
            success: function (response) {
                console.log(response);
                if (response.status == "Success") {
                    if (path.indexOf("cart") !== -1) {
                        placeOrder(address_id, 5, response.data, tip);
                    } else if (path.indexOf("wallet") !== -1) {
                        creditWallet(amount, 5, response.data);
                    } else if (path.indexOf("orders") !== -1) {
                        creditTipAfterOrder(amount, 3, response.data, order_number);
                    }
                    else if (path == 'pickup_delivery') {
                        creditTipAfterOrder(amount, 4, response.data, order_number);
                    }
                } else {
                    $('.spinner-overlay').hide();
                    if (path.indexOf("cart") !== -1) {
                        success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    } else if (path.indexOf("wallet") !== -1) {
                        success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                        $("#topup_wallet_btn, .topup_wallet_confirm").removeAttr("disabled");
                    }
                }
            },
            error: function (error) {
                $('.spinner-overlay').hide();
                var response = $.parseJSON(error.responseText);
                if (path.indexOf("cart") !== -1) {
                    success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (path.indexOf("wallet") !== -1) {
                    success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    $("#topup_wallet_btn, .topup_wallet_confirm").removeAttr("disabled");
                }
            }
        });
    }

    window.paymentViaPayfast = function paymentViaPayfast(address_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let cabElement = $("#pickup_now");
        let ajaxData = {};
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            ajaxData.tip = tip;
            ajaxData.address_id = $("input:radio[name='address_id']:checked").val();
            ajaxData.payment_form = 'cart';
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            ajaxData.payment_form = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            ajaxData.subscription_id = subscription_id.val();
            ajaxData.payment_form = 'subscription';
        } else if ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            ajaxData.payment_form = 'tip';
            ajaxData.order_number = $("#order_number").val();
        } else if (cabElement.length > 0) {
            total_amount = cabElement.attr('data-amount');
            ajaxData.payment_form = 'pickup_delivery';
            ajaxData.order_number = order.order_number;
        }
        ajaxData.amount = total_amount;
        ajaxData.returnUrl = path;
        ajaxData.cancelUrl = path;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_payfast_url,
            data: ajaxData,
            success: function (response) {
                if (response.status == "Success") {
                    var res = response.data;
                    if (res.formData != '') {
                        $("#payfast_offsite_form").remove();
                        var form = '';
                        $.each(res.formData, function (key, value) {
                            form += '<input type="hidden" name="' + key + '" value="' + value + '">';
                        });
                        form = $('<form id="payfast_offsite_form" action="' + res.redirectUrl + '" method="post">' + form + '</form>');
                        if (cartElement.length > 0) {
                            $('#proceed_to_pay_modal .modal-body').append(form);
                        } else if (walletElement.length > 0) {
                            $('#topup_wallet .modal-content').append(form);
                        }
                        form.submit();
                    }
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
            error: function (error) {
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

    window.paymentViaMobbex = function paymentViaMobbex(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = {};
        if (cartElement.length > 0) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            ajaxData.tip = tip;
            ajaxData.address_id = address_id;
            ajaxData.payment_form = 'cart';
            ajaxData.order_number = order.order_number;
        } else if (walletElement.length > 0) {
            total_amount = walletElement.val();
            ajaxData.payment_form = 'wallet';
        }
        ajaxData.amount = total_amount;
        ajaxData.returnUrl = path;
        ajaxData.cancelUrl = path;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_mobbex_url,
            data: ajaxData,
            success: function (response) {
                if (response.status == "Success") {
                    window.location.href = response.data;
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
            error: function (error) {
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

    window.paymentViaYoco = function paymentViaYoco(token, address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let ajaxData = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            ajaxData.push(
                { name: 'tip', value: tip },
                { name: 'address_id', value: address_id },
                { name: 'payment_form', value: 'cart' },
                { name: 'cart_id', value: cart_id },
                { name: 'order_number', value: order.order_number }
            );

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );

        }
        ajaxData.push(
            { name: 'token', value: token },
            { name: 'amount', value: total_amount },
            { name: 'returnUrl', value: path }
        );

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_yoco_url,
            data: ajaxData,
            success: function (response) {
                if (response.status == "Success") {
                    if (path.indexOf("cart") !== -1) {
                        window.location.href = order_success_return_url;
                    } else if (path.indexOf("wallet") !== -1) {
                        creditWallet(total_amount, 8, response.data.id);
                    } else if (path.indexOf("subscription") !== -1) {
                        userSubscriptionPurchase(total_amount, 8, response.data.id);
                    } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
                        let order_number = $("#order_number").val();
                        if (order_number.length > 0) {
                            order_number = order_number;
                        }
                        creditTipAfterOrder(total_amount, 8, response.data.id, order_number);
                    }
                } else {
                    if (path.indexOf("cart") !== -1) {
                        success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    } else if (path.indexOf("wallet") !== -1) {
                        success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    } else if (path.indexOf("subscription") !== -1) {
                        success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                        $(".subscription_confirm_btn").removeAttr("disabled");
                    }
                }
            },
            error: function (error) {
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

    // window.paymentViaYoco_wallet = function paymentViaYoco_wallet(token, address_id, payment_option_id) {
    //     let total_amount = 0;
    //     let ajaxData = [];
    //     total_amount = $("input[name='wallet_amount']").val();

    //     ajaxData.push({ name: 'token', value: token }, { name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
    //     $.ajax({
    //         type: "POST",
    //         dataType: 'json',
    //         url: payment_yoco_url,
    //         data: ajaxData,
    //         success: function(response) {
    //             creditWallet(total_amount, payment_option_id, response.data.id);
    //         },
    //     });
    // }

    window.paymentViaPaylink = function paymentViaPaylink(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            ajaxData.push(
                { name: 'tip', value: tip },
                { name: 'address_id', value: address_id },
                { name: 'payment_form', value: 'cart' },
                { name: 'cart_id', value: cart_id },
                { name: 'order_number', value: order.order_number }
            );
            // ajaxData.tip = tip;
            // ajaxData.address_id = address_id;
            // ajaxData.payment_form = 'cart';
            // ajaxData.cart_id = cart_id;
            // ajaxData.order_number = order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            ajaxData.payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
        }
        ajaxData.push(
            { name: 'amount', value: total_amount },
            { name: 'returnUrl', value: path },
            { name: 'cancelUrl', value: path }
        );
        // ajaxData.amount = total_amount;
        // ajaxData.returnUrl = path;
        // ajaxData.cancelUrl = path;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_paylink_url,
            data: ajaxData,
            success: function (response) {
                if (response.status == "Success") {
                    window.location.href = response.data;
                } else {
                    if (path.indexOf("cart") !== -1) {
                        success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    } else if (path.indexOf("wallet") !== -1) {
                        success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    }
                }
            },
            error: function (error) {
                var response = $.parseJSON(error.responseText);
                if (path.indexOf("cart") !== -1) {
                    success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (path.indexOf("wallet") !== -1) {
                    success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                }
            }
        });
    }

    // function paymentViaPaylink_wallet(address_id, payment_option_id) {
    //     let total_amount = 0;
    //     let ajaxData = [];
    //     total_amount = $("input[name='wallet_amount']").val();
    //     ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
    //     $.ajax({
    //         type: "POST",
    //         dataType: 'json',
    //         url: payment_paylink_url,
    //         data: ajaxData,
    //         success: function(response) {
    //             if (response.status == "Success") {
    //                 //  creditWallet(total_amount, payment_option_id, data.result.id);
    //                 window.location.href = response.data;
    //             }
    //         }
    //     });
    // }

    window.paymentViaRazorpay = function paymentViaRazorpay(address_id, order, payment_from) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let cabElement = $("#pickup_now");

        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = {};
        if (cartElement.length > 0) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            ajaxData.tip = tip;
            ajaxData.address_id = address_id;
            ajaxData.payment_from = 'cart';
            ajaxData.cart_id = cart_id;
            ajaxData.order_number = order.order_number;
        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            ajaxData.payment_from = 'pickup_delivery';
            ajaxData.order_number = order.order_number;
        } else if (walletElement.length > 0) {
            total_amount = walletElement.val();
            ajaxData.payment_form = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            // ajaxData.push({name: 'payment_form', value: 'subscription'});
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            // ajaxData.push(
            //     {name: 'payment_form', value: 'tip'},
            //     {name: 'order_number', value: $("#order_number").val()}
            // );
        }
        ajaxData.amount = total_amount;
        // ajaxData.payment_from = 'cart';
        ajaxData.returnUrl = path;
        ajaxData.cancelUrl = path;
        // console.log(JSON.stringify(ajaxData));
        // return false;
        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: payment_razorpay_url,
            data: ajaxData,
            success: function (response) {
                if (response.status == "Success") {
                    // razorpay_options.key = response.data.api_key;
                    razorpay_options.amount = response.data.amount;
                    razorpay_options.order_id = response.data.order_id;
                    razorpay_options.currency = response.data.currency;
                    $('#proceed_to_pay_modal').modal('hide');
                    razourPayView(response.data, order);
                    // window.location.href = response.data;
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
            error: function (error) {
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

    window.razorPayCompletePayment = function razorPayCompletePayment(data, response, order = '') {
        data.razorpay_payment_id = response.razorpay_payment_id;
        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: razorpay_complete_payment_url,
            data: data,
            success: function (response) {
                // console.log(response);
                if (response.status == "Success") {
                    if (data.payment_from == 'pickup_delivery') {
                        window.location.replace(order.route);
                    } else {
                        window.location.href = response.data;
                    }
                } else {

                }
            },
            error: function (error) {

            }
        });
    }
    // RazourPay payment gateway
    window.razourPayView = function razourPayView(data, order = '') {
        razorpay_options.handler = function (response) {
            startLoader('body', 'We are processing your transaction...');
            razorPayCompletePayment(data, response, order);
        }
        var rzp1 = new Razorpay(razorpay_options);
        rzp1.on('payment.failed', function (response) {
        });
        rzp1.open();
    }
    // RazourPay payment gateway

    /////////////////////////////////////////////GCash payment Gateway Integration/////////////
    window.paymentViaGCash = function paymentViaGCash(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 11;
        data._token = $('input[name=_token]').val();
        $.redirect(gcash_before_payment, data);
    }

    //////////////////////////////////////Simplify Payment Gateway////////////////////////////////////

    window.paymentViaSimplify = function paymentViaSimplify(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 12;
        data._token = $('input[name=_token]').val();
        $.redirect(simplify_before_payment, data);
    }
    /////////////////////////////////////////////Square Pamyent Gateway /////////////////////////////////////////
    window.paymentViaSquare = function paymentViaSquare(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 13;
        data._token = $('input[name=_token]').val();
        $.redirect(square_before_payment, data);
    }

    //////////////////////////Ozow Payment Gateway /////////////////////////////////////////
    window.paymentViaOzow = function paymentViaOzow(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 14;
        data._token = $('input[name=_token]').val();
        $.redirect(ozow_before_payment, data);
    }
    ///////////////////////////Pagarme payment Gateway //////////////////////////////
    window.paymentViaPagarme = function paymentViaPagarme(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 15;
        data._token = $('input[name=_token]').val();
        $.redirect(pagarme_before_payment, data);
    }
    ///////////////////////////Authorize payment Gateway //////////////////////////////
    window.paymentViaAuthorize = function paymentViaAuthorize(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let cabElement = $("#pickup_now");
        let pending_amount = $("input[name='wallet_amount_pending']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        } else if (cabElement.length > 0) {
            total_amount = cabElement.attr('data-amount');
            data.payment_from = 'pickup_delivery';
            data.order_number = order.order_number;
            data.reload_route = order.route;
        } else if ((typeof pending_amount_for_past_order !== 'undefined') && (pending_amount_for_past_order == 1)) {
            total_amount = pending_amount.val();
            payment_form = 'pending_amount_form';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 18;
        data._token = $('input[name=_token]').val();
        $.redirect(authorize_before_payment, data);
    }

    /////////////////////////////////////////////Square Pamyent Gateway /////////////////////////////////////////
    window.paymentViaBraintree = function paymentViaBraintree(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 33;
        data._token = $('input[name=_token]').val();
        $.redirect(braintree_before_payment, data);
    }
    //////////////////////////////////////UPay Payment Gateway////////////////////////////////////

    window.paymentViaUPay = function paymentViaUPay(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 43;
        data._token = $('input[name=_token]').val();
        $.redirect(upay_before_payment, data);
    }
    //////////////////////////////////////UPay Payment Gateway////////////////////////////////////

    window.paymentViaConekta = function paymentViaConekta(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 44;
        data._token = $('input[name=_token]').val();
        $.redirect(conekta_before_payment, data);
    }
    //////////////////////////////////////UPay Payment Gateway////////////////////////////////////

    window.paymentViaTelr = function paymentViaTelr(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 45;
        data._token = $('input[name=_token]').val();
        $.redirect(telr_before_payment, data);
    }

    ///////////////////////////Checkout payment Gateway //////////////////////////////
    window.paymentViaCheckout = function paymentViaCheckout(address_id, order = '') {
        var address_id = address_id;
        var order = order;
        Frames.submitCard()
            .then(function (data) {
                $('#checkout_card_error').html('');
                // Frames.addCardToken(form, data.token);
                var token = data.token;
                let total_amount = 0;
                let tip = 0;
                let tipElement = $("#cart_tip_amount");
                let cartElement = $("input[name='cart_total_payable_amount']");
                let cart_id = $("#cart_total_payable_amount").data("cart_id");
                let walletElement = $("input[name='wallet_amount']");
                let subscriptionElement = $("input[name='subscription_amount']");
                let ajaxData = [];

                if (path.indexOf("cart") !== -1) {
                    total_amount = cartElement.val();
                    tip = tipElement.val();
                    ajaxData.push(
                        { name: 'tip', value: tip },
                        { name: 'address_id', value: address_id },
                        { name: 'payment_form', value: 'cart' },
                        { name: 'cart_id', value: cart_id },
                        { name: 'order_number', value: order.order_number }
                    );
                } else if (path.indexOf("wallet") !== -1) {
                    total_amount = walletElement.val();
                    ajaxData.push({ name: 'payment_form', value: 'wallet' });
                } else if (path.indexOf("subscription") !== -1) {
                    total_amount = subscriptionElement.val();
                    ajaxData = $("#subscription_payment_form").serializeArray();
                    ajaxData.push({ name: 'payment_form', value: 'subscription' });
                } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
                    total_amount = walletElement.val();
                    ajaxData.push(
                        { name: 'payment_form', value: 'tip' },
                        { name: 'order_number', value: $("#order_number").val() }
                    );
                }
                ajaxData.push(
                    { name: 'token', value: token },
                    { name: 'amount', value: total_amount }
                );
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: payment_checkout_url,
                    data: ajaxData,
                    success: function (response) {
                        if (response.status == "Success") {
                            // if (path.indexOf("cart") !== -1) {
                            window.location.href = response.data;
                            // } else if (path.indexOf("wallet") !== -1) {
                            //     creditWallet(total_amount, 8, response.data.id);
                            // } else if (path.indexOf("subscription") !== -1) {
                            //     userSubscriptionPurchase(total_amount, 8, response.data.id);
                            // } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
                            //     let order_number = $("#order_number").val();
                            //     if (order_number.length > 0) {
                            //         order_number = order_number;
                            //     }
                            //     creditTipAfterOrder(total_amount, 8, response.data.id, order_number);
                            // }
                        } else {
                            if (path.indexOf("cart") !== -1) {
                                success_error_alert('error', response.message, "#cart_payment_form .payment_response");
                                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                            } else if (path.indexOf("wallet") !== -1) {
                                success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                                $(".topup_wallet_confirm").removeAttr("disabled");
                            } else if (path.indexOf("subscription") !== -1) {
                                success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                                $(".subscription_confirm_btn").removeAttr("disabled");
                            }
                        }
                    },
                    error: function (error) {
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

            })
            .catch(function (error) {
                $('#checkout_card_error').html(error.message);
                $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
            });
        return false;
    }

    ///////////////////////////Stripe FPX payment Gateway //////////////////////////////

    window.payWithCcAvenue = function payWithCcAvenue(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        //let amt = cartElement.val()*100;
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        let cabElement = $("#pickup_now");
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if (path.indexOf("giftCard") !== -1) {
                payment_form = 'giftCard';
                gift_card_id        = $("#giftCard_id").val();
                send_card_to_name   = $("input[name='send_card_to_name']").val();
                send_card_to_mobile = $("input[name='send_card_to_mobile']").val();
                send_card_to_email  = $("input[name='send_card_to_email']").val();
                send_card_to_address    = $("input[name='send_card_to_address']").val();
                send_card_is_delivery   = $("#send_card_is_delivery").val();
                var rowData = 'gift_card_id=' + total_amount + '&from=' + payment_from + 'send_card_to_name=' + send_card_to_name + '&send_card_to_mobile=' + send_card_to_mobile +'send_card_to_email=' + send_card_to_email + '&send_card_to_address=' + send_card_to_address + '&send_card_is_delivery=' + send_card_is_delivery;
        }else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + order.order_number;
        } 
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        window.location = create_ccavenue_url + '?' + rowData;
    }

    window.payWithWindcave = function payWithWindcave(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_windcave_hash_url,
            data: rowData,
            success: function (resp) {
                // console.log(resp.url);
                window.location.href = resp.href;
            },
            error: function (error) {
                console.log(error);
            }

        });
    }

    window.payWithDpo = function payWithDpo(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let cabElement = $("#pickup_now");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_dpo_tocken_url,
            data: rowData,
            success: function (resp) {
                // console.log('resp',resp);
                window.location.replace(resp);
                // window.location.href = resp.href;
            },
            error: function (error) {
                console.log(error);
            }

        });
    }

    window.paymentViaDpo = function paymentViaDpo(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_dpo_tocken,
            data: rowData,
            success: function (resp) {
                if (resp != '') {
                    window.location.replace(resp);
                } else {
                    alert('Tray Again');
                }
            },
            error: function (error) {
                console.log(error);
            }

        });
    }
    window.payWithPaytech = function payWithPaytech(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_paytech_hash_url,
            data: rowData,
            success: function (resp) {
                if (resp.success == 1) {
                    window.location.href = resp.redirect_url;
                } else {
                    alert('Tray Again');
                }
            },
            error: function (error) {
                console.log(error);
            }

        });
    }

    window.payphoneButton = function payphoneButton(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        //let amt = cartElement.val()*100;
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let cabElement = $("#pickup_now");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_payphone_url,
            data: rowData,
            success: function (resp) {
                if (resp.paymentId) {
                    window.location.href = resp.payWithCard;
                } else {
                    alert(resp.message);
                    if (payment_from == 'cart') {
                        window.location.href = payphone_refund_wallet;
                    }
                    window.location.reload();
                }
            },
            error: function (error) {
                console.log(error);
            }

        });
    }

    window.payWithKPG = function payWithKPG(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        //let amt = cartElement.val()*100;
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_konga_hash_url,
            data: rowData,
            success: function (resp) {

                KPG.setup(resp);
            },
            error: function (error) {
                console.log(error);
            }

        });
    }

    window.paymentViaEasyPaisaPay = function paymentViaEasyPaisaPay(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        //let amt = cartElement.val()*100;
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_easypaisa_hash_url,
            data: rowData,
            success: function (resp) {
                // console.log(resp.url);
                window.location.href = resp.url + easypaisaUrl(resp.data);
            },
            error: function (error) {
                console.log(error);
            }

        });
    }

    function easypaisaUrl(params) {
        var str = jQuery.param(params);
        return str;
    }

    window.payWithFlutterWave = function payWithFlutterWave(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        let cabElement = $("#pickup_now");

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_flutterwave_url,
            data: rowData,
            success: function (resp) {

                FlutterwaveCheckout(resp);
            },
            error: function (error) {
                console.log(error);
            }

        });
    }

    window.payWithMvodafone = function payWithMvodafone(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        //let amt = cartElement.val()*100;
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            url: create_mvodafone_pay_url,
            data: rowData,
            success: function (resp) {
                window.location.href = resp.url;
            },
            error: function (error) {
                console.log(error);
                alert(error);
            }

        });
    }

    window.payWithVivaWallet = function payWithVivaWallet(order = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        //let amt = cartElement.val()*100;
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        $.ajax({
            type: "POST",
            url: create_viva_wallet_pay_url,
            data: rowData,
            success: function (resp) {
                window.location.href = resp;
            },
            error: function (error) {
                console.log(error);
                alert('Tray Again.');
            }

        });
    }


    ///////////////////////////Stripe FPX payment Gateway //////////////////////////////
    window.paymentViaStripeFPX = function paymentViaStripeFPX(address_id = '', payment_option_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'payment_form', value: 'cart' }
            );
            returnParams += 'order=' + order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_form = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
            returnParams += 'order=' + $("#order_number").val();
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
        returnParams += '&amount=' + total_amount + '&payment_form=' + payment_form;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_create_stripe_fpx_url,
            data: ajaxData,
            success: function (resp) {
                if (resp.status == 'Success') {
                    const clientSecret = resp.data;
                    const result = stripe_fpx.confirmFpxPayment(clientSecret, {
                        payment_method: {
                            fpx: fpxBank
                        },
                        // Return URL where the customer should be redirected after the authorization
                        return_url: payment_retrive_stripe_fpx_url + '?' + returnParams,
                    });
                    if (result.error) {
                        // Inform the customer that there was an error.
                        if (path.indexOf("cart") !== -1) {
                            success_error_alert('error', result.error.message, ".payment_response");
                            $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                        } else if (path.indexOf("wallet") !== -1) {
                            success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                            $(".topup_wallet_confirm").removeAttr("disabled");
                        } else if (path.indexOf("subscription") !== -1) {
                            success_error_alert('error', result.error.message, "#subscription_payment_form .payment_response");
                            $(".subscription_confirm_btn").removeAttr("disabled");
                        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
                            success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                            $(".topup_wallet_confirm").removeAttr("disabled");
                        } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                            success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                            $(".topup_wallet_confirm").removeAttr("disabled");
                        }
                    }
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
            error: function (error) {
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


    window.paymentViaStripeIdeal = function paymentViaStripeIdeal(address_id = '', payment_option_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'payment_form', value: 'cart' }
            );
            returnParams += 'order=' + order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_form = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
            returnParams += 'order=' + $("#order_number").val();
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
        returnParams += '&amount=' + total_amount + '&payment_form=' + payment_form;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_create_stripe_ideal_url,
            data: ajaxData,
            success: function (resp) {
                if (resp.status == 'Success') {
                    const clientSecret = resp.data.client_secret;
                    // Redirects away from the client
                    const result = stripe_ideal.confirmIdealPayment(clientSecret,
                        {
                            payment_method: {
                                ideal: idealBank,
                                billing_details: {
                                    name: resp.data.shipping.name,
                                },
                            },
                            return_url: payment_retrive_stripe_ideal_url + '?' + returnParams,
                        }
                    );

                    if (result.error) {
                        // Inform the customer that there was an error.
                        if (path.indexOf("cart") !== -1) {
                            success_error_alert('error', result.error.message, ".payment_response");
                            $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                        } else if (path.indexOf("wallet") !== -1) {
                            success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                            $(".topup_wallet_confirm").removeAttr("disabled");
                        } else if (path.indexOf("subscription") !== -1) {
                            success_error_alert('error', result.error.message, "#subscription_payment_form .payment_response");
                            $(".subscription_confirm_btn").removeAttr("disabled");
                        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
                            success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                            $(".topup_wallet_confirm").removeAttr("disabled");
                        } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                            success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                            $(".topup_wallet_confirm").removeAttr("disabled");
                        }
                    }
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
            error: function (error) {
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


    window.paymentViaStripeOXXO = function paymentViaStripeOXXO(address_id = '', payment_option_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'payment_form', value: 'cart' }
            );
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_form = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_create_stripe_oxxo_url,
            data: ajaxData,
            success: function (resp) {
                if (resp.status == 'Success') {
                    const clientSecret = resp.data.client_secret;
                    stripeOxxo.confirmOxxoPayment(clientSecret, {
                        payment_method: {
                            billing_details: {
                                name: resp.data.shipping.name,
                                email: resp.data.receipt_email,
                            },
                        },
                    }) // Stripe.js will open a modal to display the OXXO voucher to your customer
                        .then(function (result) {
                            // This promise resolves when the customer closes the modal

                            if (result.error) {
                                // Inform the customer that there was an error.
                                if (path.indexOf("cart") !== -1) {
                                    success_error_alert('error', result.error.message, ".payment_response");
                                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                                } else if (path.indexOf("wallet") !== -1) {
                                    success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                                    $(".topup_wallet_confirm").removeAttr("disabled");
                                } else if (path.indexOf("subscription") !== -1) {
                                    success_error_alert('error', result.error.message, "#subscription_payment_form .payment_response");
                                    $(".subscription_confirm_btn").removeAttr("disabled");
                                } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
                                    success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                                    $(".topup_wallet_confirm").removeAttr("disabled");
                                } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                                    success_error_alert('error', result.error.message, "#wallet_topup_form .payment_response");
                                    $(".topup_wallet_confirm").removeAttr("disabled");
                                }
                            } else {

                                if (payment_form == 'cart') {
                                    window.location.href = cart_clear_stripe_oxxo_url + '?no=' + order.order_number;
                                } else {
                                    location.reload();
                                }

                            }
                        });

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
            error: function (error) {
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

    ///////////////////////////Stripe FPX payment Gateway //////////////////////////////
    window.paymentViaCashfree = function paymentViaCashfree(address_id = '', payment_option_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'payment_form', value: 'cart' }
            );
            returnParams += 'order=' + order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_form = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
            returnParams += 'order=' + $("#order_number").val();
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
        returnParams += '&amount=' + total_amount + '&payment_form=' + payment_form;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: post_payment_via_gateway_url.replace(':gateway', 'cashfree'),
            data: ajaxData,
            success: function (resp) {
                if (resp.status == 'Success') {
                    var data = resp.data;
                    // console.log(data);
                    window.location.href = data.payment_link;
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
            error: function (error) {
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


    ///////////////////////////Toyyibpay payment Gateway //////////////////////////////
    window.paymentViaToyyibPay = function paymentViaToyyibPay(address_id = '', payment_option_id = '', order = '') {

        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");

        let product_name = $("#hidden_product_name").val();
        let category_name = $("#category_name").val();


        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'payment_form', value: 'cart' },
                { name: 'product_name', value: 'product_name' },
                { name: 'category_name', value: 'category_name' }
            );
            returnParams += 'order=' + order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_form = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
            returnParams += 'order=' + $("#order_number").val();
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id }, { name: 'category_name', value: category_name }, { name: 'product_name', value: product_name }, { name: 'payment_form', value: payment_form });
        returnParams += '&amount=' + total_amount + '&payment_form=' + payment_form;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: post_toyyibpay_via_gateway_url,
            data: ajaxData,
            success: function (resp) {
                // alert(123);
                //console.log(resp);
                // alert(resp);

                if (resp.status == 'Success') {
                    window.location.href = resp.payment_link;
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
            error: function (error) {
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

    ///////////////////////////Dpo payment Gateway //////////////////////////////
    window.paymentViaDpoSubscription = function paymentViaDpoSubscription(address_id = '', payment_option_id = '', order = '') {

        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");

        let product_name = $("#hidden_product_name").val();
        let category_name = $("#category_name").val();


        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'from', value: 'cart' },
                { name: 'product_name', value: 'product_name' },
                { name: 'category_name', value: 'category_name' }
            );
            returnParams += 'order=' + order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            from = 'wallet';
            ajaxData.push({ name: 'from', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            from = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'from', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            from = 'tip';
            ajaxData.push(
                { name: 'from', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
            returnParams += 'order=' + $("#order_number").val();
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id }, { name: 'category_name', value: category_name }, { name: 'product_name', value: product_name }, { name: 'payment_form', value: payment_form });
        returnParams += '&amount=' + total_amount + '&from=' + payment_form;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_dpo_subscription,
            data: ajaxData,
            success: function (resp) {
                // alert(123);
                //console.log(resp);
                // alert(resp);

                if (resp != '') {
                    window.location.replace(resp);
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
            error: function (error) {
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

    ////////////////////////////////////// skipcash payment gateway////////////////////////////////////

    window.paymentViaSkipCash = function paymentViaSkipCash(address_id = '', order) {
        let total_amount = 0;
        let tip = 0;
        let cabElement = $("#pickup_now");
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if (cabElement.length > 0) {
            total_amount = cabElement.attr('data-amount');
            data.payment_from = 'pickup_delivery';
            data.order_id = order.order_number;
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 52;
        data._token = $('input[name=_token]').val();
        $.redirect(skipcash, data);
    }



    ///////////////////////////EaseBuzz payment Gateway //////////////////////////////
    window.payWithEasebuss = function payWithEasebuss(address_id = '', payment_option_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            var vendor_id = order?.vendors[0]?.vendor_id || 0;
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'vendor_id', value: vendor_id },
                { name: 'payment_form', value: 'cart' }
            );
            returnParams += 'order=' + order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_form = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
            returnParams += 'order=' + $("#order_number").val();
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
        returnParams += '&amount=' + total_amount + '&payment_form=' + payment_form;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: post_payment_via_gateway_url.replace(':gateway', 'easebuzz'),
            data: ajaxData,
            success: function (resp) {
                console.log(resp);
                if (resp.status == 'Success') {

                    var data = resp.data;
                    console.log(data);
                    window.location.href = data.data;
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
            error: function (error) {

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

    ///////////////////////////VNpay payment Gateway //////////////////////////////
    window.payWithVNpay = function payWithVNpay(address_id = '', payment_option_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'payment_form', value: 'cart' }
            );
            returnParams += 'order=' + order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_form = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
            returnParams += 'order=' + $("#order_number").val();
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
        returnParams += '&amount=' + total_amount + '&payment_form=' + payment_form;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: post_payment_via_gateway_url.replace(':gateway', 'vnpay'),
            data: ajaxData,
            success: function (resp) {
                console.log(resp);
                if (resp.status == 'Success') {

                    var data = resp.data;
                    console.log(data);
                    window.location.href = data.data;
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
            error: function (error) {

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


    /////////////////////////////////////////////Paytab Pamyent Gateway /////////////////////////////////////////
    window.paymentViaPaytab = function paymentViaPaytab(address_id, order) {
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 13;
        data._token = $('input[name=_token]').val();
        $.redirect(paytab_before_payment, data);
    }

    ///////////////////////////PayU payment Gateway //////////////////////////////
    window.payWithPayU = function payWithPayU(address_id = '', payment_option_id = '', order = '') {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';
        let returnParams = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_form = 'cart';
            ajaxData.push(
                { name: 'address_id', value: address_id },
                { name: 'order_number', value: order.order_number },
                { name: 'payment_form', value: 'cart' }
            );
            returnParams += 'order=' + order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_form = 'wallet';
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_form = 'subscription';
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'payment_form', value: 'tip' },
                { name: 'order_number', value: $("#order_number").val() }
            );
            returnParams += 'order=' + $("#order_number").val();
        }
        ajaxData.push({ name: 'amount', value: total_amount }, { name: 'payment_option_id', value: payment_option_id });
        returnParams += '&amount=' + total_amount + '&payment_form=' + payment_form;
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: post_payment_via_gateway_url.replace(':gateway', 'payu'),
            data: ajaxData,
            success: function (resp) {
                if (resp.status == 'Success') {
                    var res = resp.data;
                    if (res.formData != '') {
                        $("#payu_offsite_form").remove();
                        var form = '';
                        $.each(res.formData, function (key, value) {
                            form += '<input type="hidden" name="' + key + '" value="' + value + '">';
                        });
                        form = $('<form id="payu_offsite_form" action="' + res.redirectUrl + '" method="post">' + form + '</form>');
                        if (path.indexOf("cart") !== -1) {
                            $('#proceed_to_pay_modal .modal-body').append(form);
                        } else if (path.indexOf("wallet") !== -1) {
                            $('#topup_wallet .modal-content').append(form);
                        }
                        form.submit();
                    }
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
            error: function (error) {

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

    ///////////////////////////MyCash payment Gateway //////////////////////////////
    window.paymentViaMyCash = function paymentViaMyCash(address_id, payment_option_id, order = '') {
        let total_amount = 0;
        let tip = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let tipElement = $("#cart_tip_amount");
        let payment_form = '';

        let ajaxData = [];
        if (path.indexOf("cart") !== -1) {
            payment_form = 'cart';
            total_amount = cartElement.val();
            tip = tipElement.val();
            ajaxData.push(
                { name: 'tip', value: tip },
                { name: 'order_number', value: order.order_number }
            );
        } else if ((path.indexOf("wallet") !== -1) || ((typeof cabbookingwallet !== 'undefined') && (cabbookingwallet == 1))) {
            payment_form = 'wallet';
            total_amount = walletElement.val();
        } else if (path.indexOf("subscription") !== -1) {
            payment_form = 'subscription';
            total_amount = subscriptionElement.val();
            ajaxData = $("#subscription_payment_form").serializeArray();
        } else if ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            payment_form = 'tip';
            ajaxData.push(
                { name: 'order_number', value: $("#order_number").val() }
            );
        }
        ajaxData.push(
            { name: 'payment_form', value: payment_form },
            { name: 'amount', value: total_amount },
            { name: 'payment_option_id', value: payment_option_id }
        );
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: post_payment_via_gateway_url.replace(':gateway', 'mycash'),
            data: ajaxData,
            success: function (resp) {
                if (resp.status == 'Success') {
                    // window.location.href = resp.data;
                    var res = resp.data;
                    if (res.formData != '') {
                        $("#temp_form").remove();
                        var form = '';
                        $.each(res.formData, function (key, value) {
                            form += '<input type="hidden" name="' + key + '" value="' + value + '">';
                        });
                        var token = $('meta[name="_token"]').attr('content');
                        form = $('<form id="temp_form" action="' + res.redirectUrl + '" method="post"><input type="hidden" name="_token" value="' + token + '">' + form + '</form>');
                        if (path.indexOf("cart") !== -1) {
                            $('#proceed_to_pay_modal .modal-body').append(form);
                        } else if ((path.indexOf("wallet") !== -1) || ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1))) {
                            $('#topup_wallet .modal-content').append(form);
                        } else if (path.indexOf("subscription") !== -1) {
                            $('#subscription_payment .modal-content').append(form);
                        }
                        form.submit();
                    }
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
            error: function (error) {
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

    ///////////////////////////UseRede payment Gateway //////////////////////////////
    window.paymentViaUseRede = function paymentViaUseRede(address_id, payment_option_id, order = '') {
        if (client_primary_currency != "BRL") {
            if (path.indexOf("cart") !== -1) {
                success_error_alert('error', 'Something went wrong!Please try again.', ".payment_response");
                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            } else if (path.indexOf("wallet") !== -1) {
                success_error_alert('error', 'Something went wrong!Please try again.', "#wallet_topup_form .payment_response");
                $(".topup_wallet_confirm").removeAttr("disabled");
            } else if (path.indexOf("subscription") !== -1) {
                success_error_alert('error', 'Something went wrong!Please try again.', "#subscription_payment_form .payment_response");
                $(".subscription_confirm_btn").removeAttr("disabled");
            }
            return false;
        }

        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = 40;
        data._token = $('input[name=_token]').val();
        $.redirect(userede_before_payment, data);
    }

    ///////////////////////////Plugnpay payment Gateway //////////////////////////////
    window.paymentViaplugnpay = function paymentViaplugnpay(address_id = '', payment_option_id = '', order = '') {


        cno = $('#plugnpay-card-element').val();
        cname = $('#plugnpay-name-element').val();
        dt = $('#plugnpay-date-element').val();
        cv = $('#plugnpay-cvv-element').val();

        caddr1 = $('#plugnpay-addr1-element').val();
        caddr2 = $('#plugnpay-addr2-element').val();
        czip = $('#plugnpay-zip-element').val();
        city = $('#plugnpay-city-element').val();
        state = $('#plugnpay-state-element').val();
        country = $('#plugnpay-country-element').val();

        let total_amount = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let tipElement = $("#cart_tip_amount");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let data = [];
        let payment_from = '';

        let cabElement = $("#pickup_now");
        if (path.indexOf("cart") !== -1) {

            // if (path.indexOf("cart") !== -1) {
            payment_form = 'cart';
            total_amount = cartElement.val();
            data.push(
                { name: 'order_number', value: order.order_number },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'cname', value: cname }
            );

            // }
            data.push(
                { name: 'from', value: payment_form },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
            );
        }

        else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';

            data.push(
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'cname', value: cname },
                { name: 'caddr1', value: caddr1 },
                { name: 'caddr2', value: caddr2 },
                { name: 'czip', value: czip },
                { name: 'city', value: city },
                { name: 'state', value: state },
                { name: 'country', value: country }
            );
        }

        else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_from = 'subscription';
            data.push(
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'subsid', value: subscription_id.val() },
            );

        }
        else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            data.push(
                { name: 'order_number', value: order.order_number },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'reload_route', value: address_id },
            );
        }
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';

            data.push(
                { name: 'order_number', value: $("#order_number").val() },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
            );
        }






        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: payment_plugnpay_url,
            data: data,
            success: function (response) {

                if (response.status == "Fail") {
                    if (response.payment_from == 'wallet') {
                        $("#wallet_payment_methods_error").html(response.msg);
                        $("#wallet_payment_methods_error").css("color", 'red');
                        $(document).find('.topup_wallet_confirm').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'pickup_delivery') {
                        $("#plugnpay_card_error").html(response.msg);
                        $("#plugnpay_card_error").css("color", 'red');
                        $("#proceed_to_pay_loader").hide();
                        $('#paywithplugpay').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'subscription') {
                        $("#plugnpay_card_error").html(response.msg);
                        $("#plugnpay_card_error").css("color", 'red');
                        $(document).find('.subscription_confirm_btn').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'cart') {
                        $("#plugnpay_card_error").html(response.msg);
                        $("#plugnpay_card_error").css("color", 'red');
                        $("#proceed_to_pay_loader").hide();
                        $(document).find('.proceed_to_pay').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'tip') {
                        $("#wallet_payment_methods_error").html(response.msg);
                        $("#wallet_payment_methods_error").css("color", 'red');
                        $(document).find('.topup_wallet_confirm').prop('disabled', false);
                        return false;
                    }

                }
                else if (response.status == "Success") {
                    window.location.replace(response.route);
                    // console.log(response);
                } else {
                    window.location.replace(response.route);

                }
            },
            error: function (response) {

                window.location.replace(response.route);
                // var error = response.responseJSON;
                // console.log(error, 'Error');
            }
        });
    }

    ///////////////////////////Nmi payment Gateway //////////////////////////////
    window.paymentNmipay = function paymentNmipay(address_id = '', payment_option_id = '', order = '', json) {
        cno = json.cno;
        dt = json.dt;
        cv = json.cv;
        let total_amount = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let tipElement = $("#cart_tip_amount");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let data = [];
        let payment_from = '';

        let cabElement = $("#pickup_now");
        if (path.indexOf("cart") !== -1) {

            // if (path.indexOf("cart") !== -1) {
            payment_form = 'cart';
            total_amount = cartElement.val();
            data.push(
                { name: 'order_number', value: order.order_number },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv }
            );

            // }
            data.push(
                { name: 'from', value: payment_form },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
            );
        }

        else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';

            data.push(
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
            );
        }

        else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_from = 'subscription';
            data.push(
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'subsid', value: subscription_id.val() },
            );

        }
        else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            data.push(
                { name: 'order_number', value: order.order_number },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'reload_route', value: address_id },
            );
        }
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';

            data.push(
                { name: 'order_number', value: $("#order_number").val() },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
            );
        }
        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: payment_nmi_url,
            data: data,
            success: function (response) {

                if (response.status == "Fail") {
                    window.location.replace(response.route);
                    if (response.payment_from == 'wallet') {
                        $("#wallet_payment_methods_error").html(response.msg);
                        $("#wallet_payment_methods_error").css("color", 'red');
                        $(document).find('.topup_wallet_confirm').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'pickup_delivery') {
                        $("#azul_card_error").html(response.msg);
                        $("#azul_card_error").css("color", 'red');
                        $("#proceed_to_pay_loader").hide();
                        $('#paywithplugpay').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'subscription') {
                        $("#azul_card_error").html(response.msg);
                        $("#azul_card_error").css("color", 'red');
                        $(document).find('.subscription_confirm_btn').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'cart') {
                        $("#card_error_nmi").html(response.msg);
                        $("#card_error_nmi").css("color", 'red');
                        $("#proceed_to_pay_loader").hide();
                        $(document).find('.proceed_to_pay').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'tip') {
                        $("#wallet_payment_methods_error").html(response.msg);
                        $("#wallet_payment_methods_error").css("color", 'red');
                        $(document).find('.topup_wallet_confirm').prop('disabled', false);
                        return false;
                    }

                }
                else if (response.status == "Success") {
                    window.location.replace(response.route);
                } else {
                    window.location.replace(response.route);
                }
            }
        });
    }

    ///////////////////////////Obo payment Gateway //////////////////////////////
    window.paymentViaOboPay = function paymentViaOboPay(address_id = '', payment_option_id = '', order = '') {
        let total_amount = 0;
        let orderNumber = order.order_number ?? "";
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let cabElement = $("#pickup_now");
        let ajaxData = {};
        if (path.indexOf("cart") !== -1) {
            payment_from = 'cart';
            total_amount = cartElement.val();
        }
        else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_from = 'subscription';
            ajaxData.subscription_id = subscription_id.val()
        }
        else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            ajaxData.reload_route = address_id;
        }
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            orderNumber = $("#order_number").val();
        }
        ajaxData.amount = total_amount;
        ajaxData.cancelUrl = path;
        ajaxData.order_number = orderNumber;
        ajaxData.payment_from = payment_from;

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_obo_url,
            data: ajaxData,
            success: function (response) {
                if (response.status == "Success") {
                    window.location.href = response.data;
                } else {
                    if (cartElement.length > 0) {
                        success_error_alert('error', response.message, ".payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    } else if (walletElement.length > 0) {
                        success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    }
                }
            },
            error: function (error) {
                var response = $.parseJSON(error.responseText);
                if (cartElement.length > 0) {
                    success_error_alert('error', response.message, ".payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (walletElement.length > 0) {
                    success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                }
            }
        });
    }



    ///////////////////////////PayU payment Gateway //////////////////////////////
    window.payWithOpenPay = function payWithOpenPay(address_id = '', payment_option_id = '', order = '') {
        if (default_country_code != "MX" && default_country_code != "CO" && default_country_code != "PE") {
            console.log('openpay only accpoet default_country_code = MX,CO,PE');
            if (path.indexOf("cart") !== -1) {
                success_error_alert('error', 'Something went wrong!Please try again.', ".payment_response");
                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            } else if (path.indexOf("wallet") !== -1) {
                success_error_alert('error', 'Something went wrong!Please try again.', "#wallet_topup_form .payment_response");
                $(".topup_wallet_confirm").removeAttr("disabled");
            } else if (path.indexOf("subscription") !== -1) {
                success_error_alert('error', 'Something went wrong!Please try again.', "#subscription_payment_form .payment_response");
                $(".subscription_confirm_btn").removeAttr("disabled");
            }
            return false;
        }
        //return false;
        let total_amount = 0;
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let walletElement = $("input[name='wallet_amount']");
        let ajaxData = [];
        let data = [];

        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            // ajaxData = $("#subscription_payment_form").serializeArray();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.amount = total_amount;
        data.payment_option_id = payment_option_id;
        data._token = $('input[name=_token]').val();
        $.redirect(openpay_before_payment, data);
    }

    ///////////////////////////Khalti payment Gateway //////////////////////////////
    window.paymentViaKhalti = function paymentViaKhalti(address_id, order, payment_form) {
        let total_amount = 0;
        let tip = 0;
        let cabElement = $("#pickup_now");
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let cart_id = $("#cart_total_payable_amount").data("cart_id");
        let subscriptionElement = $("input[name='subscription_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let product_id_arr_string = 'WALLET_R9N6O5';
        let product_name_arr_string = 'WALLET PRODUCT';
        let ajaxData = [];
        if (cartElement.length > 0) {
            let product_id_arr = order.products;
            product_id_arr_string = product_id_arr.map(elem => (elem.product_id)).toString();
            product_name_arr_string = product_id_arr.map(elem => (elem.product_name)).toString();
            var order_number = order.order_number;

        }
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            total_amount = order.payable_amount;
            ajaxData.push(
                { name: 'tip', value: tip },
                { name: 'address_id', value: address_id },
                { name: 'payment_form', value: 'cart' },
                { name: 'cart_id', value: cart_id },
                { name: 'order_id', value: order_number },
                { name: 'amount', value: total_amount }
            );
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            ajaxData.push({ name: 'payment_form', value: 'wallet' });
            ajaxData.push({ name: 'amount', value: total_amount });
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            ajaxData = $("#subscription_payment_form").serializeArray();
            ajaxData.push({ name: 'payment_form', value: 'subscription' });
        } else if ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            ajaxData.push({ name: 'payment_form', value: 'tip' });
            ajaxData.push({ name: 'order_id', value: $("#order_number").val() });
        } else if (cabElement.length > 0) {
            total_amount = cabElement.attr('data-amount');
            ajaxData.push({ name: 'payment_form', value: 'pickup_delivery' });
            ajaxData.push({ name: 'order_id', value: order.order_number });
        }

        var khaltipay_options = {
            // replace the publicKey with yours
            "publicKey": khalti_api_key,
            "currency": "NPR",
            "name": client_company_name,
            "productIdentity": product_id_arr_string,
            "productName": product_name_arr_string,
            "productName": product_name_arr_string,
            "productUrl": "https://sales.royoorders.com/",
            "eventHandler": {
                onSuccess(payload) {
                    console.log(payload, 'payload');
                    // hit merchant api for initiating verfication
                    ajaxData.push({ name: 'amount', value: payload.amount });
                    ajaxData.push({ name: 'mobile', value: payload.mobile });
                    ajaxData.push({ name: 'product_identity', value: payload.product_identity });
                    ajaxData.push({ name: 'token', value: payload.token });
                    ajaxData.push({ name: 'payment_id', value: payload.idx });
                    $.ajax({
                        url: payment_khalti_url,
                        type: 'POST',
                        data: ajaxData,
                        success: function (data) {

                            console.log('PAY onSuccess Success');
                            khaltiPayView(data);
                        },
                        error: function (data) {
                            console.log("PAY onSuccess Success error");
                            //redirext to error page
                        }
                    });
                },
                onError(error) {
                    console.log('OnError' + error);
                    //redirect as needed
                },
                onClose() {
                    console.log('widget is closing');
                    //redirect as needed
                }
            }
        };
        var khaltipay = new KhaltiCheckout(khaltipay_options);
        khaltipay.show({ amount: (total_amount * 100).toFixed(0) });
    }

    window.khaltiPayCompletePayment = function khaltiPayCompletePayment(data) {
        // console.log('khaltiPayCompletePayment '+JSON.stringify(data));
        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: payment_khalti_complete_purchase,
            data: data,
            success: function (response) {
                if (response.status == "Success") {
                    if (response.data.payment_from == 'pickup_delivery') {
                        window.location.replace(response.data.route);
                    } else if (response.data.payment_from == 'wallet') {
                        window.location.href = response.data.route;
                    } else if (response.data.payment_from == 'cart') {
                        window.location.href = response.data.route;
                    } else if (response.data.payment_from == 'tip') {
                        window.location.href = response.data.route;
                    } else if (response.data.payment_from == 'subscription') {
                        window.location.href = response.data.route;
                    }
                } else {

                }
            },
            error: function (response) {
                var error = response.responseJSON;
                console.log(error, 'Error');
            }
        });
    }

    window.khaltiPayView = function khaltiPayView(data) {
        console.log('Pay View ' + JSON.stringify(data));
        // khaltipay_options.eventHandler = function (response){
        startLoader('body', 'We are processing your transaction...');
        khaltiPayCompletePayment(data);
        // alert(response.razorpay_payment_id);
        // alert(response.razorpay_order_id);
        // alert(response.razorpay_signature);
        // }
        // var khaltipay = new KhaltiCheckout(khaltipay_options);
        // khaltipay.show({amount: 1000});
    } // Ends

    /***
    * Mtn Momo payment gateway
    */
    window.paymentViaMtnMomo = function paymentViaMtnMomo(address_id, order, payment_form, reload_route = '') {
        let cartElement = $("input[name='cart_total_payable_amount']");
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let cabElement = $("#pickup_now");
        let payment_from = '';
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
            var overlayElement = '#proceed_to_pay_modal';
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
            var overlayElement = '#topup_wallet';
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
            var overlayElement = '#subscription_payment';
        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            var rowData = `amt=${total_amount}&from=${payment_from}&reload_route=${reload_route}&order_number=${order.order_number}`;
            var overlayElement = 'body  ';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
            var overlayElement = '#topup_wallet';
        }

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: create_mtn_momo_token,
            data: rowData,
            beforeSend: function () {
                add_spinner(overlayElement, 'Sending Payment Request...');
            },
            success: function (resp) {
                console.log(resp)
                if (resp.hasOwnProperty('wait')) {
                    var interval = setInterval(function () {
                        $.ajax({
                            type: "GET",
                            dataType: 'json',
                            url: resp.responseUrl,
                            beforeSend: function () {
                                remove_spinner(overlayElement);
                                add_spinner(overlayElement, 'Request Sent. Waiting for Response...');
                            },
                            success: function (response) {
                                if (response.hasOwnProperty('url')) {
                                    clearInterval(interval);
                                    remove_spinner(overlayElement);
                                    window.location.href = response.url;
                                } else {
                                    //  alert(response.message);
                                    if (response.hasOwnProperty('response') && response.response != '' && typeof (response.response) != 'undefined') {
                                        console.error(response.response);
                                    }
                                }
                            },
                            error: function (response) {
                                alert(response.responseJSON.message);
                                location.reload(true);
                            }
                        })
                    }, 5000);
                } else {
                    if (resp.hasOwnProperty('url')) {
                        window.location.href = resp.url;
                    } else {
                        alert(resp.message);
                        if (resp.hasOwnProperty('response') && resp.response != '' && typeof (resp.response) != 'undefined') {
                            console.error(resp.response);
                        }
                    }
                }
            },
            error: function (resp) {
                alert(resp.responseJSON.message);
                location.reload(true);
            }
        });
    }


    ///////////////////////////Plugnpay payment Gateway //////////////////////////////
    window.paymentViaplugnpay = function paymentViaplugnpay(address_id = '', payment_option_id = '', order = '') {
        cname = $('#plugnpay-name-element').val();
        cno = $('#plugnpay-card-element').val();
        dt = $('#plugnpay-date-element').val();
        cv = $('#plugnpay-cvv-element').val();

        caddr1 = $('#plugnpay-addr1-element').val();
        caddr2 = $('#plugnpay-addr2-element').val();
        czip = $('#plugnpay-zip-element').val();
        city = $('#plugnpay-city-element').val();
        state = $('#plugnpay-state-element').val();
        country = $('#plugnpay-country-element').val();

        let total_amount = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let tipElement = $("#cart_tip_amount");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let data = [];
        let payment_from = '';

        let cabElement = $("#pickup_now");
        if (path.indexOf("cart") !== -1) {

            // if (path.indexOf("cart") !== -1) {
            payment_form = 'cart';
            total_amount = cartElement.val();
            data.push(
                { name: 'order_number', value: order.order_number },
                { name: 'cno', value: cno },
                { name: 'cname', value: cname },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv }
            );

            // }
            data.push(
                { name: 'from', value: payment_form },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
            );
        }

        else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';

            data.push(
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'cname', value: cname },
                { name: 'caddr1', value: caddr1 },
                { name: 'caddr2', value: caddr2 },
                { name: 'czip', value: czip },
                { name: 'city', value: city },
                { name: 'state', value: state },
                { name: 'country', value: country }
            );
        }

        else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_from = 'subscription';
            data.push(
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'subsid', value: subscription_id.val() },
            );

        }
        else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            data.push(
                { name: 'order_number', value: order.order_number },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'reload_route', value: address_id },
            );
        }
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';

            data.push(
                { name: 'order_number', value: $("#order_number").val() },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
            );
        }

        $.ajax({
            type: "POST",
            dataType: 'json',
            async: false,
            url: payment_plugnpay_url,
            data: data,
            success: function (response) {

                if (response.status == "Fail") {
                    if (response.payment_from == 'wallet') {
                        $("#wallet_payment_methods_error").html(response.msg);
                        $("#wallet_payment_methods_error").css("color", 'red');
                        $(document).find('.topup_wallet_confirm').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'pickup_delivery') {
                        $("#plugnpay_card_error").html(response.msg);
                        $("#plugnpay_card_error").css("color", 'red');
                        $("#proceed_to_pay_loader").hide();
                        $('#paywithplugpay').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'subscription') {
                        $("#plugnpay_card_error").html(response.msg);
                        $("#plugnpay_card_error").css("color", 'red');
                        $(document).find('.subscription_confirm_btn').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'cart') {
                        $("#plugnpay_card_error").html(response.msg);
                        $("#plugnpay_card_error").css("color", 'red');
                        $("#proceed_to_pay_loader").hide();
                        $(document).find('.proceed_to_pay').prop('disabled', false);
                        return false;
                    }
                    else if (response.payment_from == 'tip') {
                        $("#wallet_payment_methods_error").html(response.msg);
                        $("#wallet_payment_methods_error").css("color", 'red');
                        $(document).find('.topup_wallet_confirm').prop('disabled', false);
                        return false;
                    }

                }
                else if (response.status == "Success") {
                    window.location.replace(response.route);
                } else {
                    window.location.replace(response.route);
                }
            },
            error: function (response) {

                window.location.replace(response.route);
                // var error = response.responseJSON;
                //console.log(error, 'Error');
            }
        });
    }

    ///////////////////////////Azulpay payment Gateway //////////////////////////////
    window.paymentViazulpay = function paymentViazulpay(address_id = '', payment_option_id = '', order = '') {
        cno = $('#azul-card-element').val();
        dt = $('#azul-date-element').val();
        cv = $('#azul-cvv-element').val();
        sc = $('#azul-save_card:checked').val();
        card_id = $("input[type='radio'][name='azul_card_id']:checked").val();
        let total_amount = 0;
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let tipElement = $("#cart_tip_amount");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let data = [];
        let payment_from = '';

        let cabElement = $("#pickup_now");
        if (path.indexOf("cart") !== -1) {

            // if (path.indexOf("cart") !== -1) {
            payment_form = 'cart';
            total_amount = cartElement.val();
            data.push(
                { name: 'order_number', value: order.order_number },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'card_id', value: card_id },
                { name: 'save_card', value: sc }
            );

            // }
            data.push(
                { name: 'from', value: payment_form },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
            );
        }

        else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';

            data.push(
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'card_id', value: card_id },
                { name: 'save_card', value: sc }
            );
        }

        else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            payment_from = 'subscription';
            data.push(
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'subsid', value: subscription_id.val() },
                { name: 'card_id', value: card_id },
                { name: 'save_card', value: sc }
            );

        }
        else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            data.push(
                { name: 'order_number', value: order.order_number },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'reload_route', value: address_id },
                { name: 'card_id', value: card_id },
                { name: 'save_card', value: sc }
            );
        }
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';

            data.push(
                { name: 'order_number', value: $("#order_number").val() },
                { name: 'cno', value: cno },
                { name: 'dt', value: dt },
                { name: 'cv', value: cv },
                { name: 'from', value: payment_from },
                { name: 'amt', value: total_amount },
                { name: 'amount', value: total_amount },
                { name: 'card_id', value: card_id },
                { name: 'save_card', value: sc }
            );
        }
        if (creditCardValidation()) {
            $.ajax({
                type: "POST",
                dataType: 'json',
                async: false,
                url: payment_azulpay_url,
                data: data,


                success: function (response) {
                    if (response.status == "Fail") {
                        if (response.payment_from == 'wallet') {
                            $("#wallet_payment_methods_error").html(response.msg);
                            $("#wallet_payment_methods_error").css("color", 'red');
                            $(document).find('.topup_wallet_confirm').prop('disabled', false);
                            return false;
                        }
                        else if (response.payment_from == 'pickup_delivery') {
                            $("#azul_card_error").html(response.msg);
                            $("#azul_card_error").css("color", 'red');
                            $("#proceed_to_pay_loader").hide();
                            $('#paywithazulpay').prop('disabled', false);
                            return false;
                        }
                        else if (response.payment_from == 'subscription') {
                            $("#azul_card_error").html(response.msg);
                            $("#azul_card_error").css("color", 'red');
                            $(document).find('.subscription_confirm_btn').prop('disabled', false);
                            return false;
                        }
                        else if (response.payment_from == 'cart') {
                            $("#azul_card_error").html(response.msg);
                            $("#azul_card_error").css("color", 'red');
                            $("#proceed_to_pay_loader").hide();
                            $(document).find('.proceed_to_pay').prop('disabled', false);
                            return false;
                        }
                        else if (response.payment_from == 'tip') {
                            $("#wallet_payment_methods_error").html(response.msg);
                            $("#wallet_payment_methods_error").css("color", 'red');
                            $(document).find('.topup_wallet_confirm').prop('disabled', false);
                            return false;
                        }

                    }
                    else if (response.status == "Success") {
                        window.location.replace(response.route);
                    } else {
                        window.location.replace(response.route);
                    }
                }
            });
        }
    }
    
    ///////////////////////////Mpesa Safari payment Gateway //////////////////////////////
    window.paymentViaMpesaSafari = function paymentViaMpesaSafari(address_id='', payment_option_id='', order='') {
        let total_amount = 0;
        let orderNumber         = order.order_number ?? "";
        let orderId             = order.id ?? "";
        let tipElement          = $("#cart_tip_amount");
        let cartElement         = $("input[name='cart_total_payable_amount']");
        let walletElement       = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id     = $("input[name='subscription_id']");
        let cabElement          = $("#pickup_now");
        let ajaxData = {};
        if (path.indexOf("cart") !== -1) {
            payment_from = 'cart';
            total_amount = cartElement.val();
        }
        else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
        }else if (path.indexOf("subscription") !== -1) {
            total_amount            = subscriptionElement.val();
            payment_from            = 'subscription';
            ajaxData.subscription_id = subscription_id.val()
        }
        else if (cabElement.length > 0) {
            total_amount = cabElement.data('amount');
            payment_from            = 'pickup_delivery';
            ajaxData.reload_route   = address_id;
        }
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            orderNumber  = $("#order_number").val();
        }
        ajaxData.action   = payment_from;
        ajaxData.come_from   = 'web';
        ajaxData.order_id       = orderId;
        ajaxData.amount         = total_amount;
        ajaxData.order_number   = orderNumber;

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: payment_mpesa_safari_url,
            data: ajaxData,
            success: function (response) {
                if (response.status == "Success") {
                    window.location.href = response.route;
                } else {
                    if (cartElement.length > 0) {
                        success_error_alert('error', response.message, ".payment_response");
                        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                    } else if (walletElement.length > 0) {
                        success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                        $(".topup_wallet_confirm").removeAttr("disabled");
                    }
                }
            },
            error: function (error) {
                var response = $.parseJSON(error.responseText);
                if (cartElement.length > 0) {
                    success_error_alert('error', response.message, ".payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (walletElement.length > 0) {
                    success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                }
            }
        });
    }
    
    

    window.creditCardValidation = function creditCardValidation() {
        var valid = true;
        $(".demoInputBox").css('background-color', '');
        var message = "";

        var cvvRegex = /^[0-9]{3,3}$/;

        var cardNumber = $("#azul-card-element").val();
        var cvv = $("#azul-cvv-element").val();

        var expiry = $("#azul-date-element").val();
        expiry = expiry.split('/');


        var today, someday;
        var exMonth = expiry[0];
        var exYear = expiry[1];
        today = new Date();
        someday = new Date();
        someday.setFullYear(exYear, exMonth, 1);



        if (cardNumber == "" || cvv == "" || expiry == '') {
            message += "<div>All Fields are Required.</div>";

            if (cardNumber == "") {
                $("#azul-card-element").css('background-color', '#FFFFDF');
            }
            if (cvv == "") {
                $("#azul-cvv-element").css('background-color', '#FFFFDF');
            }
            if (expiry == "") {
                $("#azul-date-element").css('background-color', '#FFFFDF');
            }
            valid = false;
        }

        if (cardNumber != "") {
            $('#azul-card-element').validateCreditCard(function (result) {
                if (!(result.valid)) {
                    message += "<div>Card Number is Invalid</div>";
                    $("#card-number").css('background-color', '#FFFFDF');
                    valid = false;
                }
            });
        }

        if (cvv != "" && !cvvRegex.test(cvv)) {
            message += "<div>CVV is Invalid</div>";
            $("#azul-cvv-element").css('background-color', '#FFFFDF');
            valid = false;
        }


        if (expiry != "") {
            if (someday < today) {
                message += "<div>Expiry date is Invalid</div>";
                $("#azul-date-element").css('background-color', '#FFFFDF');
                valid = false;
            }
        }

        var azul_card_id = $("input[type='radio'][name='azul_card_id']:checked").val();
        if (azul_card_id) {
            message = '';
            valid = true;
        }


        if (message != "") {
            $("#azul_card_error").show();
            $("#azul_card_error").html(message);
            $(document).find('.topup_wallet_confirm').prop('disabled', false);
            $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
            $(".subscription_confirm_btn").attr("disabled", false);
        } else {
            $("#azul_card_error").html('');
            $(document).find('.topup_wallet_confirm').prop('disabled', true);
            $(document).find(".proceed_to_pay").prop('disabled', true);
            $(".subscription_confirm_btn").attr("disabled", true);
        }
        return valid;
    }

    window.cardValidation = function cardValidation(jsonVal) {
        var valid = true;
        $(".demoInputBox").css('background-color', '');
        var message = "";

        var cvvRegex = /^[0-9]{3,3}$/;

        var cardNumber = jsonVal.cno;
        var cvv = jsonVal.cv;

        var expiry = jsonVal.dt;
        var name = jsonVal.name;
        expiry = expiry.split('/');


        var today, someday;
        var exMonth = expiry[0];
        var exYear = expiry[1];

        if (exYear.length == 2) {
            var exYear = "20" + expiry[1];
        }

        today = new Date();
        someday = new Date();
        someday.setFullYear(exYear, exMonth, 1);


        if (cardNumber == "" || cvv == "" || expiry == '') {
            message += "<div>All Fields are Required.</div>";

            if (cardNumber == "") {
                $("#card-element-" + name).css('background-color', '#FFFFDF');
            }
            if (cvv == "") {
                $("#cvv-element-" + name).css('background-color', '#FFFFDF');
            }
            if (expiry == "") {
                $("#date-element-" + name).css('background-color', '#FFFFDF');
            }
            valid = false;
        }

        if (cardNumber != "") {
            $('#card-element-' + name).validateCreditCard(function (result) {
                if (!(result.valid)) {
                    message += "<div>Card Number is Invalid</div>";
                    $("#card-number").css('background-color', '#FFFFDF');
                    valid = false;
                }
            });
        }

        if (cvv != "" && !cvvRegex.test(cvv)) {
            message += "<div>CVV is Invalid</div>";
            $("#cvv-element-" + name).css('background-color', '#FFFFDF');
            valid = false;
        }


        if (expiry != "") {
            if (someday < today || exMonth > 12) {
                message += "<div>Expiry date is Invalid</div>";
                $("#date-element-" + name).css('background-color', '#FFFFDF');
                valid = false;
            }
        }

        if (message != "") {
            $("#card_error_" + name).show();
            $("#card_error_" + name).html(message);
            $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
            $(document).find('.topup_wallet_confirm').prop('disabled', true);
            $(".subscription_confirm_btn").attr("disabled", false);
            $(".select_payment_option_done").attr("disabled", false);
            $(".topup_wallet_confirm").attr("disabled", false);
        } else {
            $("#card_error_" + name).html('');
            // $(document).find('.topup_wallet_confirm').prop('disabled',true);
            $(document).find(".proceed_to_pay").prop('disabled', true);
            // $(".subscription_confirm_btn").attr("disabled", true);
            $("#payment_modal").modal('toggle');
        }

        return valid;
    }

    window.payWithPowerTrans = function payWithPowerTrans(payment_option_id, order) {

        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let cabElement = $("#pickup_now");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");

        var data = {};
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.payment_from = 'cart';
            // data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';

        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('totalamount');
            data.payment_from = 'pickup_delivery';
            data.order_number = order.order_number;

        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            data.order_number = subscription_id.val();
            data.payment_from = 'subscription';

        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }

        data.total_amount = total_amount;
        data.payment_option_id = payment_option_id;
        data._token = $('input[name=_token]').val();

        data.card_number = $('#card-element-powertrans').val();
        data.exp_date = $('#date-element-powertrans').val();
        data.cvv = $('#cvv-element-powertrans').val();

        $.ajax({
            type: "post",
            dataType: "json",
            url: powertrans_payment_url,
            data: data,

            success: function (response) {
                console.log({ response });
                if (response.IsoResponseCode == 00) {
                    window.location.href = response.redirect_url + '?TransactionIdentifier=' + response.TransactionIdentifier;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong in Payment!',
                    });
                    console.log('Something wrong in payment');
                }
                return true;
            }
        });
    };

    window.payWithPesapal = function payWithPesapal(payment_option_id, order) {
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let cabElement = $("#pickup_now");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");

        var data = {};
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.payment_from = 'cart';
            // data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';

        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('totalamount');
            data.payment_from = 'pickup_delivery';
            data.order_number = order.order_number;

        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            data.order_number = subscription_id.val();
            data.payment_from = 'subscription';

        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }

        data.total_amount = total_amount;
        data.payment_option_id = payment_option_id;
        data._token = $('input[name=_token]').val();


        $.ajax({
            type: "post",
            dataType: "json",
            url: pesapal_payment_url,
            data: data,

            success: function (response) {
                if (response.status == 200) {
                    window.location.href = response.redirect_url;
                }
                else if (response.status == 201) {
                    $(".subscription_confirm_btn").attr("disabled", false);
                    $(".select_payment_option_done").attr("disabled", false);
                    $(".topup_wallet_confirm").attr("disabled", false);

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message,
                    });
                }
                else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something Went Wrong in Payment',
                    });
                }
            }
        });
    }

    window.paymentViaOrangePay = function paymentViaOrangePay(payment_option_id, order) {
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let cabElement = $("#pickup_now");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        var data = {};
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.from = 'cart';
            // data.cart_id = cart_id;
            data.order_number = order.order_number;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.from = 'wallet';
        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('totalamount');
            data.from = 'pickup_delivery';
            data.order_number = order.order_number;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            data.order_number = subscription_id.val();
            data.from = 'subscription';
        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.from = 'tip';
            data.order_number = $("#order_number").val();
        }
        data.total_amount = total_amount;
        data.payment_option_id = payment_option_id;
        data._token = $('input[name=_token]').val();
        $.ajax({
            type: "post",
            dataType: "json",
            url: payment_orangepay_url,
            data: data,
            success: function (response) {
                if (response.status == "Success") {
                    window.location.href = response.data;
                }
                else if (response.status == 'Error') {
                    $(".subscription_confirm_btn").attr("disabled", false);
                    $(".select_payment_option_done").attr("disabled", false);
                    $(".topup_wallet_confirm").attr("disabled", false);
                    Swal.fire({
                        icon: 'error',
                        title: 'ohh...',
                        text: response.message,
                    });
                }
                else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something Went Wrong in Payment',
                    });
                }
            }
        });
    }
    
    window.paymentViaCyberSourcePay = function paymentViaCyberSourcePay(payment_option_id, order) {
        let cartElement = $("input[name='cart_total_payable_amount']");
        //let amt = cartElement.val()*100;
        let total_amount = 0;
        let walletElement = $("input[name='wallet_amount']");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscriptionId = $("input[name='subscription_id']");
        let tipElement = $("#cart_tip_amount");
        let payment_from = '';
        let cabElement = $("#pickup_now");
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            payment_from = 'cart';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&from=' + payment_from;
        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            subsId = subscriptionId.val();
            payment_from = 'subscription';
            var rowData = 'subsid=' + subsId + '&from=' + payment_from + '&amt=' + total_amount;
        } else if (path.indexOf("giftCard") !== -1) {
                payment_form = 'giftCard';
                gift_card_id        = $("#giftCard_id").val();
                send_card_to_name   = $("input[name='send_card_to_name']").val();
                send_card_to_mobile = $("input[name='send_card_to_mobile']").val();
                send_card_to_email  = $("input[name='send_card_to_email']").val();
                send_card_to_address    = $("input[name='send_card_to_address']").val();
                send_card_is_delivery   = $("#send_card_is_delivery").val();
                var rowData = 'gift_card_id=' + total_amount + '&from=' + payment_from + 'send_card_to_name=' + send_card_to_name + '&send_card_to_mobile=' + send_card_to_mobile +'send_card_to_email=' + send_card_to_email + '&send_card_to_address=' + send_card_to_address + '&send_card_is_delivery=' + send_card_is_delivery;
        }else if (cabElement.length > 0) {
            total_amount = cabElement.data('totalamount');
            payment_from = 'pickup_delivery';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + order.order_number;
        } 
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val();
            payment_from = 'tip';
            var rowData = 'amt=' + total_amount + '&from=' + payment_from + '&order_number=' + $("#order_number").val();
        }
        window.location = payment_cybersource_url + '?' + rowData;
    }

    $(document).on("keyup", "#azul-card-element", function () {
        if (this.value != this.value.replace(/[^0-9\.]/g, '')) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        }
    });


    function clickHandle(evt, tabName) {
        let i, tabcontent, tablinks;

        // This is to clear the previous clicked content.
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Set the tab to be "active".
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Display the clicked tab and set it to active.
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";

        if (tabName == 'Card-List') {

            ajaxCall = $.ajax({
                type: "post",
                dataType: "json",
                url: user_cards_url,
                beforeSend: function () {
                    if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                        ajaxCall.abort();
                    }
                    $('.spinner-overlay').show();
                },
                success: function (response) {
                    $('#Card-List').html(response.html);
                },
                complete: function () {
                    $('.spinner-overlay').hide();
                },
                error: function (data) {
                    //location.reload();
                },
            });


        }
    }

    window.paymentViaDataTrans = function paymentViaDataTrans(address_id, payment_option_id, order) {
        let tip = 0;
        let tipElement = $("#cart_tip_amount");
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let cabElement = $("#pickup_now");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");

        var data = {};
        if (path.indexOf("cart") !== -1) {
            total_amount = cartElement.val();
            tip = tipElement.val();
            data.tip = tip;
            data.address_id = address_id;
            data.payment_from = 'cart';
            // data.cart_id = cart_id;
            data.order_number = order.order_number;

        } else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            data.payment_from = 'wallet';

        } else if (cabElement.length > 0) {
            total_amount = cabElement.data('totalamount');
            data.payment_from = 'pickup_delivery';
            data.order_number = order.order_number;
            data.reload_route = order.route;
        } else if (path.indexOf("subscription") !== -1) {
            total_amount = subscriptionElement.val();
            data.subscription_id = subscription_id.val();
            data.payment_from = 'subscription';
            data.subscription_id = $("#subscription_payment_form #subscription_id").val();

        } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = walletElement.val();
            data.payment_from = 'tip';
            data.order_number = $("#order_number").val();
        }

        data.total_amount = total_amount;
        data.payment_option_id = payment_option_id;
        data._token = $('input[name=_token]').val();

        $.ajax({
            type: "post",
            dataType: "json",
            url: data_trans_url,
            data: data,

            success: function (res) {

                Datatrans.startPayment({
                    transactionId: res.transactionId,
                    'opened': function () { console.log('payment-form opened'); },
                    'loaded': function () { console.log('payment-form loaded'); },
                    'closed': function () { console.log('payment-page closed'); },
                    'error': function (err) { console.log({ err }); }
                });
                return true;
            }
        });
    }

    // //////////   LIVEE PAYMENT GATEWAY /////////////

    window.payWithLivees = function payWithLivees(address_id = '', payment_option_id = '', order = '') {
        let total_amount;

        console.log(total_amount);
        let orderNumber = order.order_number ?? "";
        let cartElement = $("input[name='cart_total_payable_amount']");
        let walletElement = $("input[name='wallet_amount']");
        let tipElement = $("#cart_tip_amount");
        let subscriptionElement = $("input[name='subscription_amount']");
        let subscription_id = $("input[name='subscription_id']");
        let cabElement = $("#pickup_now");
        let ajaxData = {};

        if (path.indexOf("cart") !== -1) {
            payment_from = 'cart';
            total_amount = cartElement.val();
            console.log("inside catr");
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&payment_from=' + payment_from;
            console.log(rowData);
        }
        else if (path.indexOf("wallet") !== -1) {
            total_amount = walletElement.val();
            payment_from = 'wallet';
            var rowData = 'amt=' + total_amount + '&order_number=' + order.order_number + '&payment_from=' + payment_from;
            console.log(total_amount);
        }
        else if (path.indexOf("subscription") !== -1) {
            console.log("inside subsc");
            total_amount = subscriptionElement.val();
            payment_from = 'subscription';
            var rowData = 'amt='+ total_amount + '&order_number=' + order.order_number + '&payment_from=' + payment_from+'&subscription_id='+subscription_id.val();
        console.log(rowData);

        }
        else if (cabElement.length > 0) {
            console.log("inside cabElement");
            total_amount = cabElement.data('amount');
            payment_from = 'pickup_delivery';
            ajaxData.reload_route = address_id;
            var rowData = 'amt='+ total_amount + '&order_number=' + order.order_number + '&payment_from=' + payment_from + '&reload_route='+address_id;
            console.log(rowData);

        }
        else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
            total_amount = tipElement.val(); console.log(total_amount);
            payment_from = 'tip';
            orderNumber = $("#order_number").val();
            var rowData = 'amt='+ total_amount + '&order_number=' + order_number + '&payment_from=' + payment_from+'&subscription_id=' +subscription_id.val();

        }
        window.location = livee_payment_url + '?' + rowData;
    }



window.payWithCompany = function payWithCompany(address_id,payment_option_id,order) {
    let tip = 0;
    let tipElement = $("#cart_tip_amount");
    let cartElement = $("input[name='cart_total_payable_amount']");
    let walletElement = $("input[name='wallet_amount']");
    let cabElement = $("#pickup_now");
    let subscriptionElement = $("input[name='subscription_amount']");
    let subscription_id = $("input[name='subscription_id']");

    var data = {};
    if (path.indexOf("cart") !== -1) {
        total_amount = cartElement.val();
        tip = tipElement.val();
        data.tip = tip;
        data.address_id = address_id;
        data.payment_from = 'cart';
        // data.cart_id = cart_id;
        data.order_number = order.order_number;

    } else if (path.indexOf("wallet") !== -1) {
        total_amount = walletElement.val();
        data.payment_from ='wallet';

    } else if (cabElement.length > 0) {
        total_amount = cabElement.data('totalamount');
        data.payment_from = 'pickup_delivery';
        data.order_number = order.order_number;
        data.reload_route = order.route;
    } else if (path.indexOf("subscription") !== -1) {
        total_amount = subscriptionElement.val();
        data.subscription_id = subscription_id.val();
        data.payment_from ='subscription';
        data.subscription_id = $("#subscription_payment_form #subscription_id").val();

    } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
        total_amount = walletElement.val();
        data.payment_from ='tip';
        data.order_number = $("#order_number").val();
    }

    data.total_amount = total_amount;
    data.payment_option_id = payment_option_id;
    data._token = $('input[name=_token]').val();

    $.ajax({
        type: "post",
        dataType: "json",
        url: data_company_url,
        data: data,
        
        success: function (res) {
            return true;
        }
    });
}

});
