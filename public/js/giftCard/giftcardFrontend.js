
$(document).on("click",".giftCardBuy_btn",function(e) {
    e.preventDefault();
    var GiftCard_id = $(this).attr('data-id');
        $.ajax({
            type: "get",
            dataType: "json",
            url: `giftCard/payment/${GiftCard_id}`,
            success: function (response) {
                if (response.status == "Success") {
                    $("#GiftCard_payment #subscription_title").html(response.GiftCard.title);
                    $("#GiftCard_payment #giftCard_price").html(response.currencySymbol + response.GiftCard.amount);
                    $("#GiftCard_payment #giftCard__title").html(response.GiftCard.title);
                    $("#GiftCard_payment #giftCard_disc").html(response.GiftCard.short_desc);
                    var image =`<img style="border-radius: 20px;" class="w-100" src="`+response.GiftCard.image.proxy_url+'100/50'+response.GiftCard.image.image_path+`"></img>`;
                    $("#GiftCard_payment #gift_card_image").html(image);
                    $("#GiftCard_payment #giftCard_id").val(GiftCard_id);
                    $("#GiftCard_payment #giftCard_amount").val(response.GiftCard.amount);
                    $("#GiftCard_payment #giftCard_payment_methods").html('');
                    let payment_method_template = _.template($('#payment_method_template').html());
                    $("#GiftCard_payment #giftCard_payment_methods").html(payment_method_template({ payment_options: response.payment_options }));
                    if (response.payment_options == '') {
                        $("#GiftCard_payment .subscription_confirm_btn").hide();
                    }
                    $("#GiftCard_payment").modal("show");
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
            },
            error: function (error) {
                var response = $.parseJSON(error.responseText);
                let error_messages = response.message;
                $("#error_response .message_body").html(error_messages);
                $("#error_response").modal("show");
            }
        });
   
});
$(document).on('change', '#giftCard_payment_methods input[name="GiftCard_payment_method"]', function() {
    var method = $(this).val();
    var code = method.replace('radio-', '');

    if (code != '') {
        $("#giftCard_payment_methods .option-wrapper").addClass('d-none');
        $("#giftCard_payment_methods ."+code+"_element_wrapper").removeClass('d-none');
    } else {
        $("#giftCard_payment_methods .option-wrapper").addClass('d-none');
    }

    if (code == 'yoco') {
     
        var yoco_amount_payable = $("input[name='subscription_amount']").val();
        inline = sdk.inline({
            layout: 'field',
            amountInCents:  yoco_amount_payable * 100,
            currency: 'ZAR'
        });
        // this ID matches the id of the element we created earlier.
        inline.mount('#yoco-card-frame');
    }
   

    if (code == 'checkout') {
        Frames.init(checkout_public_key);
    }
   
});

$(document).on("click",".giftCard_confirm_btn",function(e) {
        e.preventDefault();
    var _this = $(".giftCard_confirm_btn");
    _this.attr("disabled", true);
    var selected_option = $("input[name='GiftCard_payment_method']:checked");
    // var subscription_id = $('#subscription_payment_form #subscription_id').val();
    var payment_option_id = selected_option.data("payment_option_id");
    if ((selected_option.length > 0) && (payment_option_id > 0)) {
        GiftCardPaymentOPtions(payment_option_id);
    } else {
        _this.attr("disabled", false);
        success_error_alert('error', 'Please select any payment option', "#subscription_payment .payment_response");
    }
});

function GiftCardPaymentOPtions(payment_option_id)
{
    switch (payment_option_id) {

        case 3:
            paymentViaPaypal('', payment_option_id);
        break;

        case 4:
            stripe.createSource(card).then(function(result) {
                if (result.error) {
                    $('#stripe_card_error').html(result.error.message);
                    $("#subscription_confirm_btn").attr("disabled", false);
                } else {
                    paymentAjaxData.payment_option_id = payment_option_id;
                    var three_d_secure = result.source.card.three_d_secure;
                    // if(three_d_secure == 'required'){
                        stripe.createPaymentMethod({
                            type: 'card',
                            card: card,
                        }).then(stripePaymentMethodHandler);
                  
                }
            });

        break;

        case 8:

                inline.createToken().then(function (result) {
                    if (result.error) {
                        $('#yoco_card_error').html(result.error.message);
                        _this.attr("disabled", false);
                    } else {
                        const token = result;
                        paymentViaYoco(token.id, '', '');
                    }
                }).catch(function (error) {
                    // Re-enable button now that request is complete
                    _this.attr("disabled", false);
                    //alert("error occured: " + error);
                    Swal.fire({
                        // title: "Warning!",
                        text: "error occured: " + error,
                        icon: "error",
                        button: "OK",
                    });
                });

        break;

        case 9:
                paymentViaPaylink('', '');
        break;

        case 10:
                paymentViaRazorpay_wallet('', payment_option_id);
        break;

        case 12:
                paymentViaSimplify('', '');
        break;

        case 13:
                paymentViaSquare('', '');
        break;

        case 14:
                paymentViaOzow('', '');
        break;

        case 15:
                paymentViaPagarme('', '');
        break;

        case 17:
                paymentViaCheckout('', '');
        break;

        case 18:
                paymentViaAuthorize('', '');
        break;

        case 19:
                paymentViaStripeFPX('', 19, '');
        break;

        case 20:
                payWithKPG('');
        break;

        case 21:
                payWithVivaWallet('');
        break;

        case 22:
                payWithCcAvenue('');
        break;

        case 23:
                paymentViaEasyPaisaPay('');
        break;

        case 24:
                paymentViaCashfree('');
        break;

        case 25:
                payWithEasebuss('');
        break;

        case 26:
                paymentViaToyyibPay('');
        break;

        case 27:
                paymentViaPaytab('','');
        break;

        case 29:
                payWithMvodafone('','');
        break;

        case 30:
                payWithFlutterWave('','');
        break;

        case 32:
                payphoneButton('','');
        break;

        case 33:
                paymentViaBraintree('');
        break;

        case 34:
                payWithWindcave('','');
        break;

        case 35:
            payWithPaytech('','');
        break;

        case 36:
                paymentViaMyCash('', payment_option_id, '');
        break;

        case 37:
            stripeOXXOInitialize();
            paymentViaStripeOXXO('', payment_option_id, '');
        break;

        case 39:
            paymentViaStripeIdeal('', payment_option_id, '');
        break;
        case 40:
            paymentViaUseRede('', payment_option_id, '');
        break;
        case 41:
            payWithOpenPay('', payment_option_id, '');
        break;
        case 42:
            paymentViaDpoSubscription('', payment_option_id, '');
        break;
        case 43:
            paymentViaUPay('', payment_option_id, '');
        break;
        case 44:
            paymentViaConekta('', payment_option_id, '');
        break;
        case 45:
            paymentViaTelr('', payment_option_id, '');
        break;
        case 47:
            paymentViaKhalti('', '');
        break;
    }

}