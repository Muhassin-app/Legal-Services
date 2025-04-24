var pending_amount_for_past_order = 1;
var inline='';
var stripe_fpx = '';
var fpxBank = '';
var idealBank = {};
$(document).on('change', '#payment_methods_pending input[name="wallet_payment_method"]', function() {
        $('#payment_methods_error_pending').html('');
        var method = $(this).val();
        var code = method.replace('radio-', '');
        if (code != '') {
            $("#payment_methods_pending .option-wrapper").addClass('d-none');
            $("#payment_methods_pending ."+code+"_element_wrapper").removeClass('d-none');
        } else {
            $("#payment_methods_pending .option-wrapper").addClass('d-none');
        }
    
        if (code == 'yoco') {
            // $("#wallet_payment_methods .yoco_element_wrapper").removeClass('d-none');
            // Create a new dropin form instance
            var yoco_amount_payable = $("input[name='wallet_amount_pending']").val();
            inline = sdk.inline({
                layout: 'field',
                amountInCents:  yoco_amount_payable * 100,
                currency: 'ZAR'
            });
            // this ID matches the id of the element we created earlier.
            inline.mount('#yoco-card-frame');
        }
        // else {
        //     $("#wallet_payment_methods .yoco_element_wrapper").addClass('d-none');
        // }
        if (code == 'checkout') {
            // $("#wallet_payment_methods .checkout_element_wrapper").removeClass('d-none');
            Frames.init(checkout_public_key);
        }
        // else {
        //     $("#wallet_payment_methods .checkout_element_wrapper").addClass('d-none');
        // }
});

//pending payment 
$(document).on("click", ".pending_payment", function () {
    let payment_option_id = $("#cart_payment_form input[name='cart_payment_method']:checked").val();
    if(payment_option_id == undefined){
        success_error_alert('error', 'Please select payment option', ".payment_response");
        return false;
    }

    if(payment_option_id == 49){
        cno = $('#plugnpay-card-element').val();
        dt = $('#plugnpay-date-element').val();
        cv = $('#plugnpay-cvv-element').val();
        if((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == ''))
        {
            success_error_alert('error', 'Please Fill Details', ".payment_response");
            return false;
        }
    }
    // $(".topup_wallet_confirm").attr("disabled", true);
    // $('#topup_wallet').modal('hide');
    walletPaymentOPtions(payment_option_id);

});



$(document).delegate(".btn_for_pending", "click", function () {
    $.ajax({
        data: {},
        type: "POST",
        async: false,
        dataType: 'json',
        url: wallet_payment_options_url,
        success: function (response) {
            if (response.status == "Success") {
                $('#payment_methods_pending').html('');
                let payment_method_template = _.template($('#payment_method_template').html());
                $("#payment_methods_pending").append(payment_method_template({ payment_options: response.data }));
                if (response.data == '') {
                    $("#pending_amount_modal .topup_wallet_confirm_pending").hide();
                } else {
                    if(stripe_publishable_key != ''){
                        stripeInitialize();
                    }
                    if(stripe_fpx_publishable_key != ''){
                        stripeFPXInitialize();
                    }
                    if(stripe_ideal_publishable_key != ''){
                        stripeIdealInitialize();
                    }

                }
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
        }
    });
});


$(document).on("click", ".topup_wallet_confirm_pending", function () {
    var wallet_amount = $('#amount_pending').val();
    let payment_option_id = $('#payment_methods_pending input[name="wallet_payment_method"]:checked').data('payment_option_id');
    if ((wallet_amount == undefined || wallet_amount <= 0) && (amount_required_error_msg != undefined)) {
        $('#amount_error_pending').html(amount_required_error_msg);
        return false;
    } else {
        $('#amount_error_pending').html('');
    }

    if(payment_option_id == 49){
        cno = $('#plugnpay-card-element').val();
        dt = $('#plugnpay-date-element').val();
        cv = $('#plugnpay-cvv-element').val();
        if((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == ''))
        {
            success_error_alert('error', 'Please Fill Details', ".payment_response");
            return false;
        }
    }

    if ((payment_option_id == undefined || payment_option_id <= 0) && (payment_method_required_error_msg != undefined)) {
        $('#payment_methods_error_pending').html(payment_method_required_error_msg);
        return false;
    } else {
        $('#payment_methods_error_pending').html('');
    }
    // $(".topup_wallet_confirm_pending").attr("disabled", true);
    // $('#topup_wallet').modal('hide');
    walletPaymentOPtions(payment_option_id);

});