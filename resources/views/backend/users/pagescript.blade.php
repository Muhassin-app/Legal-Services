<script>
    var CSRF_TOKEN = $("input[name=_token]").val();
    $('.addUserModal').click(function(){
        $('#user-modal').modal({
            //backdrop: 'static',
            keyboard: false
        });
        $('.dropify').dropify();
        $('.selectize-select').selectize();
    });


    $('.importUserModal').click(function(){
        $('#import-form').modal('show');
         $('.dropify').dropify();
    });

    $('.exportUserModal').click(function(){
       var url = $(this).attr('data-url');
       window.location.href = url;
    });


    function submitImportUserForm() {
        var form = document.getElementById('save_imported_customer');
        var formData = new FormData(form);
        var data_uri = "{{route('customer.import')}}";
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
            url: data_uri,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                location.reload();
                if (response.status == 'success') {
                    // $("#import-form").modal('hide');
                    $('#p-message').empty();
                    $('#p-message').append('Document uploaded Successfully!');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);

                } else {
                    $('#p-message').empty();
                    $('#p-message').append('Document uploading Failed!');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);

                }
                return response;
            },
            beforeSend: function() {
                $('#p-message').empty();
                $('#p-message').append('Document uploading!');

                setTimeout(function() {
                    location.reload();
                }, 2000);

                $(".loader_box").show();
            },
            complete: function() {
                $('#p-message').empty();
                $('#p-message').append('Document uploading!');
                setTimeout(function() {
                    location.reload();
                }, 2000);


                $(".loader_box").hide();
            }
        });
    }



    var userActive = $('.chk_box');

    // $(userActive).on("change" , function() {
    $(document).delegate('input[name="userAccountStatus"]', 'change', function() {
        var user_id = $(this).data('id');
        var chk = $('#cur_' + user_id + ':checked').length;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('customer.changeStatus') }}",
            data: {
                _token: CSRF_TOKEN,
                value: chk,
                userId: user_id
            },
            success: function(response) {

                if (response.status == 'success') {
                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                }
            }
        });
    });

    $(".editVendor").click(function (e) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();

        var uid = $(this).attr('userId');

        $.ajax({
            type: "get",
            url: "<?php echo url('vendor'); ?>" + '/' + uid + '/edit',
            data: '',
            dataType: 'json',
            success: function (data) {


                $('#edit-user-modal #editCardBox').html(data.html);
                $('#edit-user-modal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

            },
            error: function (data) {
                console.log('data2');
            }
        });
    });

    $("#user-modal #add_user").submit(function(e) {
            e.preventDefault();
    });

    $(document).on('click', '.submitCustomerForm', function() {
        var form =  document.getElementById('add_user');
        var formData = new FormData(form);
        var urls = "{{URL::route('customer.store')}}";
        saveCustomer(urls, formData, inp = '', modal = 'user-modal');
    });

    $("#edit-user-modal #edit_user").submit(function(e) {
            e.preventDefault();
    });

    $(document).on('click', '.editVendorForm', function(e) {
        e.preventDefault();
        var form =  document.getElementById('edit_customer');
        var formData = new FormData(form);
        var urls =  document.getElementById('customer_id').getAttribute('url');
        saveCustomer(urls, formData, inp = 'Edit', modal = 'edit-user-modal');
        console.log(urls);
    });

    function saveCustomer(urls, formData, inp = '', modal = ''){

         $.ajax({
            method: 'post',
            headers: {
                Accept: "application/json"
            },
            url: urls,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if (response.status == 'success') {
                    $("#" + modal + " .close").click();
                    location.reload();
                } else {
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            error: function(response) {
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        $("#" + key + "Input" + inp + " input").addClass("is-invalid");
                        $("#" + key + "Input" + inp + " span.invalid-feedback").children("strong").text(errors[key][0]);
                        $("#" + key + "Input span.invalid-feedback").show();

                        // $("#" + key + "Input input").addClass("is-invalid");
                        // $("#" + key + "Input span.invalid-feedback").children("strong").text(errors[key][0]);
                        // $("#" + key + "Input span.invalid-feedback").show();
                    });
                } else {
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text('Something went wrong, Please try Again.');
                }
                return response;
            }
        });

    }

    $("#customer_search").autocomplete({
        source: function(request, response) {
            // Fetch data
            $.ajax({
                url: "{{ route('customer.customSearch') }}",
                type: 'post',
                dataType: "json",
                data: {
                    _token: jQuery('meta[name="csrf-token"]').attr('content'),
                    search: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        appendTo: "#cus_search_wrapper",
        select: function(event, ui) {
            // Set selection
            $('#customer_search').val(ui.item.label); // display the selected text
            $('#cusid').val(ui.item.value); // save selected id to input
            $(".oldhide").hide();
            return false;
        }
    });

    $(document).on("submit", "#submitpayreceive", function(e) {
        e.preventDefault();
        var formdata = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: "{{ route('customer.pay.receive') }}",
            data: formdata,
            success: function(response) {
                if (response.status == 'Success') {
                    $("#pay-receive-modal .close").click();
                    location.reload();
                } else {
                    $("#pay-receive-modal .show_all_error.invalid-feedback").show();
                    $("#pay-receive-modal .show_all_error.invalid-feedback").text(response.message);
                }
            },
            error: function(response){
                let errors = response.responseJSON;
                $("#pay-receive-modal .show_all_error.invalid-feedback").show();
                $("#pay-receive-modal .show_all_error.invalid-feedback").text(errors.message);
            }
        });
    });

</script>
