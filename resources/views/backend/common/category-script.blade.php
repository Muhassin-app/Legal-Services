<script type="text/javascript">
    summernoteInit();
    var regexp = /^[a-z0-9-_-]+$/;

    function alphaNumeric(evt) {
        var charCode = String.fromCharCode(event.which || event.keyCode);

        if (!regexp.test(charCode)) {
            return false;
        }
        var n2 = document.getElementById('slug');
        // n2.value = n2.value+charCode;
        return true;
    }

    function summernoteInit() {
        $('#warning_page_design').summernote({
            placeholder: 'Warning Page',
            tabsize: 2,
            height: 120,
        });
    }
    $(".openCategoryModal").click(function(e) {
        localStorage.removeItem('edit_cat');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var uri = "{{route('category.create')}}";
        var id = $(this).attr('dataid');
        var is_vendor = $(this).attr('is_vendor');
        if (id > 0) {
            uri = "<?php echo url('client/category'); ?>" + '/' + id + '/edit';
        }
        localStorage.setItem('edit_cat',id);
        $.ajax({
            type: "get",
            url: uri,
            data: {
                'is_vendor': is_vendor
            },
            dataType: 'json',
            success: function(data) {
                $("#p-error").empty();
                if (id > 0) {
                    $('#edit-category-form').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#edit-category-form #editCategoryBox').html(data.html);
                    if(is_vendor==0)
                    {
                        $('.editcatmodal').css('display','none');
                    }else{
                        $('.editcatmodal').css('display','');
                    }
                    setTimeout(function() {
                        $('input[name="type_id"]:checked').trigger('change');
                        $('input[name="warning_page_id"]:checked').trigger('change');
                        $('input[name="template_type_id"]:checked').trigger('change');
                    }, 1000);
                    element1 = document.getElementsByClassName('edit-switch_menu');
                    element2 = document.getElementsByClassName('edit-wishlist_switch');
                    element3 = document.getElementsByClassName('edit-add_product_switch');
                    var switchery = new Switchery(element1[0]);
                    var switchery = new Switchery(element2[0]);
                    var switchery = new Switchery(element3[0]);
                    makeTag();
                    summernoteInit();
                } else {
                    $('#add-category-form').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#add-category-form #AddCategoryBox').html(data.html);
                    element1 = document.getElementsByClassName('switch_menu');
                    element2 = document.getElementsByClassName('wishlist_switch');
                    element3 = document.getElementsByClassName('add_product_switch');
                    var switchery = new Switchery(element1[0]);
                    var switchery = new Switchery(element2[0]);
                    var switchery = new Switchery(element3[0]);
                    makeTag();
                    summernoteInit();
                }
                $('.dropify').dropify();
                $('.selectize-select').selectize();

            },
            error: function(data) {
                $("#p-error").empty();
                console.log('data2');
            }
        });
    });
    $(document).on('click', '.addCategorySubmit', function(e) {
        e.preventDefault();
        var form = document.getElementById('addCategoryForm');
        var formData = new FormData(form);
        var url = "{{route('category.store')}}";
        // console.log('url', url);return false;
        saveCategory(formData, '', url);

    });
    $(document).on('change', '.type-select', function() {
        var id = $(this).val();
        var for1 = $(this).attr('for');
        $('#warning_page_main_div').hide();
        $('#template_type_main_div').hide();
        $('#warning_page_design_main_div').hide();
        $("#additional-fields-dv").css("display", "none");
        $(".cat-banners").css("display", "none");
        $("#" + for1 + "-category-form #" + for1 + "ProductHide").hide();
        $("#" + for1 + "-category-form #" + for1 + "DispatcherHide").hide();
        if (id == '1') {
            $("#additional-fields-dv").css("display", "block");
            $("#" + for1 + "-category-form #" + for1 + "ProductHide").show();
            $("#" + for1 + "-category-form #" + for1 + "DispatcherHide").hide();
        } else if (id == '2') {
            $("#" + for1 + "-category-form #" + for1 + "ProductHide").hide();
            $("#" + for1 + "-category-form #" + for1 + "DispatcherHide").show();
        } else if (id == '3') {
            $("#additional-fields-dv").css("display", "block");
            $("#" + for1 + "-category-form #" + for1 + "ProductHide").show();
            $("#" + for1 + "-category-form #" + for1 + "DispatcherHide").hide();
        } else if (id == '7') {
            $('#warning_page_main_div').show();
            $('#template_type_main_div').show();
            $('#warning_page_design_main_div').show();
            $("#" + for1 + "-category-form #" + for1 + "DispatcherHide").hide();
        } if (id == '11') {
            $("#additional-fields-dv").css("display", "block");
            $(".cat-banners").css("display", "block");
            $("#" + for1 + "-category-form #" + for1 + "ProductHide").hide();
            $("#" + for1 + "-category-form #" + for1 + "DispatcherHide").hide();
        }
    });
    $(document).on('change', '#client-cat-language', function() {
        var languageId = $(this).val();
        //var categoryId = $('#category_id').val();
        var categoryId = localStorage.edit_cat;
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
            url: "{{url('client/category/translation')}}",
            data: {"languageId":languageId, "categoryId":categoryId},
            success: function(response) {
                if(response.status == "success"){
                    var data = response.data
                    $('#cat-lang-name').val('');
                    $('#cat-lang-meta-description').val('');
                    $('#cat-lang-language-id').val('');
                    $('#cat-lang-trans-id').val('');
                    $('#cat-lang-meta-title').val('');
                    $('#cat-lang-meta-keywords').val('');
                    if(data != null){
                        $('#cat-lang-name').val(data.name);
                        $('#cat-lang-meta-description').val(data.meta_description);
                        $('#cat-lang-language-id').val(data.language_id);
                        $('#cat-lang-trans-id').val(data.id);
                        $('#cat-lang-meta-title').val(data.meta_title);
                        $('#cat-lang-meta-keywords').val(data.meta_keywords);
                    }
                }
            },
            error: function(response) {

            }
        });
    });
    $(document).on('change', '#warningPageSelectBox', function() {
        if ($('input[name="type_id"]:checked').val() == '7') {
            $('#warning_page_design_main_div').show();
        }
    });
    $(document).on('click', '.editCategorySubmit', function(e) {
        e.preventDefault();
        var form = document.getElementById('editCategoryForm');
        var formData = new FormData(form);
        var url = document.getElementById('cateId').getAttribute('url');
        saveCategory(formData, 'Edit', url);
    });

    function saveCategory(formData, type, base_uri) {
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
            url: base_uri,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status == 'success') {
                    $(".modal .close").click();
                    location.reload();
                } else if (response.status == 'error1') {
                    $("#p-error1").empty();
                    $("#p-error").empty();
                    $("#p-error1").append("*!Cannot create a sub-category of product type of category.");
                    $("#p-error").append("*!Cannot create a sub-category of product type of category.");
                    $(".show_all_error.invalid-feedback").show();
                } else if (response.status == 'error2') {
                    $("#p-error1").empty();
                    $("#p-error1").append("*!Either delete the sub categories of this category or do  not change type to product.");
                    $(".show_all_error.invalid-feedback").show();
                } else {
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            error: function(response) {
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;
                    console.log('errors', errors);
                    Object.keys(errors).forEach(function(key) {
                        if (key == 'name.0') {
                            var valiField = 'nameInput' + type;
                            $("#" + valiField + " input").addClass("is-invalid");
                            $("#nameInput" + type + " span.invalid-feedback").children("strong").text('The default language name field is required.');
                            $("#nameInput" + type + " span.invalid-feedback").show();
                        } else {
                            var valiField = key + 'Input' + type + 'Edit';
                            $("#" + valiField + " input").addClass("is-invalid");
                            $("#" + valiField + " span.invalid-feedback").children("strong").text(errors[key][0]);
                            $("#" + valiField + " span.invalid-feedback").show();
                        }
                    });
                } else {
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text('Something went wrong, Please try Again.');
                }

                return response;

            }
        });
    }

    $(document).on("click",".delete-category",function() {
            var destroy_url = $(this).data('destroy_url');
            var id = $(this).data('rel');
            Swal.fire({
                title: "Are you sure?",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if(result.value)
                {
                    $.ajax({
                        type: "GET",
                        url: destroy_url,
                        success: function(response) {
                            $.NotificationApp.send("Success", "Category deleted successfully!", "top-right", "#5ba035", "success");
                            window.location.reload();
                        }
                    });
                }
            });
        });
</script>
