$(document).on("click", ".order_edit_button", function() {
    var orderid = $(this).data('order_id')
    Swal.fire({
        title: confirm_edit_order_title,
        text: confirm_edit_order_desc,
        showCancelButton: true,
        confirmButtonText: 'Ok',
    }).then((result) => {
        if(result.value)
        {
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: edit_order_by_user_url,
                data: { orderid: orderid},
                success: function(response) {
                    if (response.status == "Success"){
                        sweetAlert.success('Success', response.message);
                    }else{
                        sweetAlert.error('Oops...', response.message);
                    }
                    window.location.href = showcart_redirect;
                },
                error: function(error) {
                    sweetAlert.error('Oops...', response.message);
                }
            });
        }else{
            return false;
        }
    });
});

