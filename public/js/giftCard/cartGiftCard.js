$(document).on('click', '#open_gift_card', function(e) {
    e.preventDefault();
   
    let cart_total_payable_amount =  $("input[name='cart_total_payable_amount']");
    let cart_id =  $("#cart_id").val();
    $(".invalid-feedback.manual_giftCard").html("");
    $.ajax({
        type: "GET",
        //dataType: 'json',
        url: 'user/giftCard/list',
        data:{cart_id:cart_id },
        success: function (response) {

            if (response.success == true) {
                $('#giftCard-modal').modal('show');
                $("#promo_code_list_main_div").html('');
                $(document).find('.manual_promocode_input').val("");
               // $('#giftCard_code_list_main_div').html(response.html);
                //$('.validate_giftCard_code_btn').attr('data-cart_id', cart_id);
                
            }
        }
    });
   
});

$(document).on("click", ".apply_gifCard_code_btn", function () {
    let cart_id     =  $("#cart_id").val();
    let giftCard_id =  $(this).attr('data-giftcard_id');

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: `verify/giftCard`,
        data: { cart_id: cart_id, giftCard_id: giftCard_id},
        success: function (response) {
            if (response.status == "Success") {
                $('#giftCard-modal').modal('hide');
                cartHeader();
            }
        },
        error: function (reject) {
            if (reject.status === 422) {
                var message = $.parseJSON(reject.responseText);
                //alert(message.message);
                Swal.fire({
                    // title: "Warning!",
                    text: message.message,
                    icon: "error",
                    button: "OK",
                });
            }
        }
    });
});
$(document).on('click', '.validate_giftCard_code_btn', function () {
    let cart_id  =  $("#cart_id").val();
    
    let giftCardCode = $(document).find('.manual_giftCard_input').val();
    if (giftCardCode && giftCardCode != "") {
        // let coupon_id = $(this).data('coupon_id');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: `verify/giftCard`,
            data: { cart_id: cart_id, giftCardCode: giftCardCode},
            success: function (response) {
                if (response.status == "Success") {
                    $('#giftCard-modal').modal('hide');
                    cartHeader();
                }
            },
            error: function (reject) {
                if (reject.status === 422) {
                    var message = $.parseJSON(reject.responseText);
                    $(".invalid-feedback.manual_giftCard").html("<strong>" + message.message + "</strong>");
                }
            }
        });
    } else {
        $(".invalid-feedback.manual_giftCard").html("<strong>Please Enter Gift Card</strong>");
    }
});

$(document).on("click", ".remove_giftCard", function () {
    let cart_id     =  $("#cart_id").val();
    let giftCard_id =  $(this).attr('data-giftcard_id');
    console.log(cart_id);
    console.log(giftCard_id);
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: `remove/giftCard`,
        data: { giftCard_id: giftCard_id, cart_id: cart_id },
        success: function (response) {
            if (response.status == "Success") {
                cartHeader();
            }
        }
    });
});