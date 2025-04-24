var tip_for_past_order = 1;
var inline='';
var stripe_fpx = '';
var fpxBank = '';
var idealBank = {};
$(document).on('change', '#wallet_payment_methods input[name="wallet_payment_method"]', function() {
    $('#wallet_payment_methods_error').html('');
    var method = $(this).val();
    var code = method.replace('radio-', '');

    if (code != '') {
        $("#wallet_payment_methods .option-wrapper").addClass('d-none');
        $("#wallet_payment_methods ."+code+"_element_wrapper").removeClass('d-none');
    } else {
        $("#wallet_payment_methods .option-wrapper").addClass('d-none');
    }

    if (code == 'yoco') {
        
        // Create a new dropin form instance

        var yoco_amount_payable = $("input[name='wallet_amount']").val();
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
