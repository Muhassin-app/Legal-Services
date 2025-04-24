<script>
    $(".openPromoModal").click(function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var uri = "{{route('promocode.create')}}";
        var uid = $(this).attr('userId');
        if (uid > 0) {
            uri = "<?php echo url('client/promocode'); ?>" + '/' + uid + '/edit';
        }
        $.ajax({
            type: "get",
            url: uri,
            data: '',
            dataType: 'json',
            success: function(data) {
                if (uid > 0) {
                    $('#edit-promo-form #editCardBox').html(data.html);
                    $('#edit-promo-form').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('.dropify').dropify();
                    elems1 = document.getElementsByClassName('switch1Edit');
                    elems2 = document.getElementsByClassName('switch2Edit');
                    var switchery = new Switchery(elems1[0]);
                    var switchery = new Switchery(elems2[0]);
                    $('#edit-promo-form .select2-multiple').select2();
                } else {
                    $('#add-promo-form #addCardBox').html(data.html);
                    $('#add-promo-form').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                     $('.dropify').dropify();
                    elems1 = document.getElementsByClassName('switch1');
                    elems2 = document.getElementsByClassName('switch2');
                    var switchery = new Switchery(elems1[0]);
                    var switchery = new Switchery(elems2[0]);
                    $('#add-promo-form .select2-multiple').select2();
                }
                runPicker();
            },
            error: function(data) {
                console.log('data2');
            }
        });
    });

    function runPicker(){
        $('.datetime-datepicker').flatpickr({
            enableTime: true,
            startDate: new Date(),
            minDate: new Date(),
            dateFormat: "Y-m-d H:i"
        });
        $('.selectpicker').selectpicker();
    }
    $('.openAddModal').click(function() {
        $('#add-promo-form').modal({
            keyboard: false
        });
        runPicker();
    });
    $(document).on('change', '.promoTypeField', function(e) {
        var type = $(this).val();
        document.getElementsByClassName("amountInputField")[0].value = 1;  
        if(type == 1){
            document.getElementsByClassName("amountInputField")[0].setAttribute("max", "100"); 
        } else {
            document.getElementsByClassName("amountInputField")[0].setAttribute("max", ""); 
        }
    });
    var count=0;
    var point=false;
    function check(e,value){
        var type = document.getElementsByClassName("promoTypeField")[0].value;
        if(type == 1){
            if(count==3)return false;
            var unicode=e.charCode? e.charCode : e.keyCode;
            if( unicode == 46 && point==true)
                   return false;
            if( unicode == 46 && point==false){
                    point=true;
            }
            if (unicode!=8)if((unicode<48||unicode>57)&&unicode!=46)return false;
            if(point==true)count++;
        }
    }

    function checkLength(){
        var type = document.getElementsByClassName("promoTypeField")[0].value;
        if(type == 1){
            var fieldVal = document.getElementsByClassName("amountInputField")[0].value;
            if(fieldVal <= 100){
                return true;
            } else {
                var str = document.getElementsByClassName("amountInputField")[0].value;
                str = str.substring(0, str.length - 1);
                document.getElementsByClassName("amountInputField")[0].value = str;
            }
        }
    }

    $(document).on('click', '.submitAddForm', function(e) {
        e.preventDefault();
        var form = document.getElementById('addPromoForm');
        var formData = new FormData(form); 
        var urls = "{{route('promocode.store')}}";
        saveData(formData, 'add', urls);
    });

    $(document).on('click', '.submitEditForm', function(e) {
        e.preventDefault();
        var form = document.getElementById('editPromoForm');
        var formData = new FormData(form);
        var urls = document.getElementById('promocode_id').getAttribute('url');
        saveData(formData, 'edit', urls);

    });

    function saveData(formData, type, banner_uri) {
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
            url: banner_uri,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == 'success') {
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
                        $("#" + key + "Input input").addClass("is-invalid");
                        $("#" + key + "Input span.invalid-feedback").children("strong").text(errors[key][0]);
                        $("#" + key + "Input span.invalid-feedback").show();
                    });
                } else {
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text('Something went wrong, Please try Again.');
                }
                return response;
            }
        });
    }

    // $("#banner-datatable tbody").sortable({
    //     placeholder: "ui-state-highlight",
    //     handle: ".dragula-handle",
    //     update: function(event, ui) {
    //         var post_order_ids = new Array();
    //         $('#post_list tr').each(function() {
    //             post_order_ids.push($(this).data("row-id"));
    //         });
    //         console.log(post_order_ids);
    //         saveOrder(post_order_ids);
    //     }
    // });

    var CSRF_TOKEN = $("input[name=_token]").val();
    function saveOrder(orderVal) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ url('client/banner/saveOrder') }}",
            data: {
                _token: CSRF_TOKEN,
                order: orderVal
            },
            success: function(response) {
                if (response.status == 'success') {}
                return response;
            }
        });
    }

    $("#user-modal #add_user").submit(function(e) {
        e.preventDefault();
    });
    $(document).on('change', '.inlineRadioOptions', function() {
        var val = $(this).val();
        var apply = $(this).attr('for');
        if(val == '0'){
            $('#'+apply+'-promo-form #productsList').show();
            $('#'+apply+'-promo-form #vendorsList').hide();
            $('#'+apply+'-promo-form #categoriesList').hide();
        }else if(val == 1){
            $('#'+apply+'-promo-form #productsList').hide();
            $('#'+apply+'-promo-form #vendorsList').show();
            $('#'+apply+'-promo-form #categoriesList').hide();
        }else if(val == 2){
            $('#'+apply+'-promo-form #productsList').hide();
            $('#'+apply+'-promo-form #vendorsList').hide();
            $('#'+apply+'-promo-form #categoriesList').show();
        }else{
            $('#'+apply+'-promo-form #productsList').hide();
            $('#'+apply+'-promo-form #vendorsList').hide();
            $('#'+apply+'-promo-form #categoriesList').hide();
        }
    });
    function myfunction(id) {
        $("#"+id).css("display", "block");
        $("#vendorsList").css("display", "none");
        $("#productsList").css("display", "none");
        $("#categoriesList").css("display", "none");
    }
</script>