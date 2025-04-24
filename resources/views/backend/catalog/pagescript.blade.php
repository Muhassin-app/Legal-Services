<script>
    var options = {
        zIndex: 9999
    };
    $(document).on('change', '.assignToSelect', function() {
        var val = $(this).val();
        if (val == 'category') {
            $('.modal .category_vendor').show();
            $('.modal .category_list').show();
            $('.modal .vendor_list').hide();
        } else if (val == 'vendor') {
            $('.modal .category_vendor').show();
            $('.modal .category_list').hide();
            $('.modal .vendor_list').show();
        } else {
            $('.modal .category_vendor').hide();
            $('.modal .category_list').hide();
            $('.modal .vendor_list').hide();
        }
    });

    $("#banner-datatable tbody").sortable({
        placeholder: "ui-state-highlight",
        update: function(event, ui) {
            var post_order_ids = new Array();
            $('#post_list tr').each(function() {
                post_order_ids.push($(this).data("row-id"));
            });
            console.log(post_order_ids);
            saveOrder(post_order_ids);
        }
    });
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
    $(".addVariantbtn").click(function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{route('variant.create')}}",
            data: '',
            dataType: 'json',
            success: function(data) {
                $('#addVariantmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#addVariantForm #AddVariantBox').html(data.html);
                $('.dropify').dropify();
                $('.selectize-select').selectize();

                var picker = new jscolor('#add-hexa-colorpicker-1', options);
            },
            error: function(data) {
                console.log('data2');
            }
        });

    });
    $('.editVariantBtn').on('click', function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{url('client/variant')}}" + '/' + did + '/edit',
            data: '',
            dataType: 'json',
            beforeSend: function() {
                $(".loader_box").show();
            },
            success: function(data) {
                $('#editVariantmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#editVariantForm #editVariantBox').html(data.html);
                $('.dropify').dropify();
                $('.selectize-select').selectize();
                $("#editVariantForm .hexa-colorpicker").each(function() {
                    var ids = $(this).attr('id');
                    try {
                        var picker = new jscolor('#' + ids, options);
                    } catch (err) {
                        console.log(err.message);
                    }
                });
                var getURI = document.getElementById('submitEditHidden').value;
                document.getElementById('editVariantForm').action = data.submitUrl;
            },
            error: function(data) {
                console.log('data2');
            },
            complete: function() {
                $('.loader_box').hide();
            }
        });
    });
    $(document).on('click', '.addOptionRow-Add', function(e) {
        var d = new Date();
        var n = d.getTime();
        var $tr = $('.optionTableAddVarient tr:eq(1)'); 
        var $clone = $tr.clone();
        $clone.find(':text').val('');
        $clone.find(':hidden').val('');
        $clone.find('.hexa-colorpicker').attr("id", "hexa-colorpicker-" + n);
        $clone.find('.lasttd').html('<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>');
        $('.optionTableAddVarient').append($clone);
        $('.hexa-colorpicker').colorpicker();
        var picker = new jscolor("#hexa-colorpicker-" + n, options);
    });

    $(document).on('click', '.addOptionRow-edit', function(e) {
        var d = new Date();
        var n = d.getTime();
        var $tr = $('.optionTableEdit tbody>tr:first').next('tr');
        var $clone = $tr.clone();
        $clone.find(':text').val('');
        $clone.find(':hidden').val('');
        $clone.find('.hexa-colorpicker').attr("id", "hexa-colorpicker-" + n);
        $clone.find('.lasttd').html('<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>');
        $('.optionTableEdit').append($clone);
        $('.hexa-colorpicker').colorpicker();
        var picker = new jscolor("#hexa-colorpicker-" + n, options);
    });

    $("#addVariantmodal").on('click', '.deleteCurRow', function() {
        $(this).closest('tr').remove();
    });

    $("#editVariantmodal").on('click', '.deleteCurRow', function() {
         
          Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('You want to delete this variant option.')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                               $(this).closest('tr').remove();
            
                     var delete_opt_id = $(this).attr('data-id');
            
                $.ajax({
            type: "POST",
            url : "{{route('variant.delete.option')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                "id": delete_opt_id
            },
            success: function (response) {
                
            }
        });
            }
        });
        return false;  
    });

    $(document).on('click', '.deleteVariant', function() {
        var did = $(this).attr('dataid');
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('You want to delete this variant.')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                $('#varDeleteForm' + did).submit();
            }
        });
        return false;
    });




    $("#varient-datatable tbody").sortable({
        placeholder: "ui-state-highlight",
        handle: ".dragula-handle",
        update: function(event, ui) {}
    });

    $('.saveBrandOrder').on('click', function(e) {
        //alert(1);
        var var_ids = new Array();
        $(".brandList").each(function() {
            var_ids.push($(this).data("row-id"));
        });
    });

    $(document).on('change', '.dropDownType', function() {
        var did = $(this).val();
        var dataFor = $(this).attr('dataFor');
        if (did == 1) {
            $('#' + dataFor + 'Variantmodal .hexacodeClass-' + dataFor).hide();
        } else {
            $('#' + dataFor + 'Variantmodal .hexacodeClass-' + dataFor).show();
        }
    });

    $('.saveVariantOrder').on('click', function(e) {
        var var_ids = new Array();
        $(".variantList").each(function() {
            var_ids.push($(this).data("row-id"));
        });
        document.getElementById('orderVariantData').value = var_ids;
        $('#variant_order').submit();
    });

    $('.addBrandbtn').on('click', function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{route('brand.create')}}",
            data: '',
            dataType: 'json',
            success: function(data) {

                $('#addBrandmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#addBrandForm #AddbrandBox').html(data.html);
                $('.dropify').dropify();
                $('.selectize-select').selectize();

            },
            error: function(data) {
                console.log('data2');
            }
        });
    });
    $(document).ready(function() {

        $('#addVariantmodal .selectize-select').selectize();
        $('#addBrandmodal .selectize-select').selectize();

    });

    $("#brand-datatable tbody").sortable({
        placeholder: "ui-state-highlight",
        handle: ".dragula-handle",
        update: function(event, ui) {}
    });
    $(document).on('click', '.deleteBrand', function() {
        var did = $(this).attr('dataid');
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('You want to delete this brand.')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                $('#brandDeleteForm' + did).submit();
            }
        });
        return false;
    });
    $('.saveBrandOrder').on('click', function(e) {
        var var_ids = new Array();
        $(".brandList").each(function() {
            var_ids.push($(this).data("row-id"));
        });
        document.getElementById('orderBrandData').value = var_ids;
        $('#brand_order').submit();
    });
    $('.editBrandBtn').on('click', function(e) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{url('client/brand')}}" + '/' + did + '/edit',
            data: '',
            dataType: 'json',
            success: function(data) {
                $('#editBrandmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#editBrandForm #editBrandBox').html(data.html);
                $('.dropify').dropify();
                $('.selectize-select').selectize();

               // $("#cateSelectBox")[0].selectize.clear();
                //$('#cateSelectBox option:selected')[0].selectize.clear();

                document.getElementById('editBrandForm').action = data.submitUrl;


            },
            error: function(data) {
                console.log('data2');
            }
        });
    });

    function deleteCategory(catid)
    {
        Swal.fire({  
        title: 'Are you sure? You want to delete category.',    
        showCancelButton: true,  
        confirmButtonText: `Ok`,    
        }).then((result) => {  
            if (result.value) {    
                $.ajax({
                    url: '{{ url("client/category/delete" ) }}/'+catid,
                    type: "GET",
                    data: {},
                    success: function(response) {
                        $('.catid'+catid).remove();
                        $('.deletecategorymsg span').text('Category deleted successfully!');
                        $('.deletecategorymsg').css('display','');
                        setTimeout(function(){
                            location.reload();
                        }, 1500);
                            
                        },
                    });
            } 
        });
    }

            // Product Tag Script
    $('#add_product_tag_modal_btn').click(function(e) {
        document.getElementById("productTagForm").reset();
        $('#add_product_tag_modal input[name=tag_id]').val("");
        $('#add_product_tag_modal').modal('show');
        $('#add_product_tag__modal #standard-modalLabel').html('Add Tag');
    });
    $(document).on('click', '.submitSaveProductTag', function(e) {
        var tag_id = $("#add_product_tag_modal input[name=tag_id]").val();
        if (tag_id) {
            var post_url = "{{ route('tag.update') }}";
        } else {
            var post_url = "{{ route('tag.create') }}";
        }
        var form_data = new FormData(document.getElementById("productTagForm"));
        $.ajax({
            url: post_url,
            method: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
               if (response.status == 'Success') {
                  $('#add_or_edit_social_media_modal').modal('hide');
                  $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                     location.reload()
                  }, 2000);
               } else {
                  $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
               $('#add_product_tag_modal .product_tag_err').html('The default language name field is required.');
            }
        });
    });
    $(document).on("click", ".edit_product_tag_btn", function() {
        let tag_id = $(this).data('tag_id');
        $('#add_product_tag_modal input[name=tag_id]').val(tag_id);
        $.ajax({
            method: 'GET',
            data: {
               tag_id: tag_id
            },
            url: "{{ route('tag.edit') }}",
            success: function(response) {
               if (response.status = 'Success') {
                  $("#add_product_tag_modal input[name=tag_id]").val(response.data.id);
                  $('#add_product_tag_modal #standard-modalLabel').html('Update Product Tag');
                  $('#add_product_tag_modal').modal('show');
                  $.each(response.data.translations, function( index, value ) {
                    $('#add_product_tag_modal #product_tag_name_'+value.language_id).val(value.name);
                  });
               }
            },
            error: function() {

            }
        });
    });
    $(document).on("click", ".delete_product_tag_btn", function() {
         var tag_id = $(this).data('tag_id');
         Swal.fire({
            title: "{{__('Are you Sure?')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
          }).then((result) => {
            if(result.value)
            {
               $.ajax({
                  type: "POST",
                  dataType: 'json',
                  url: "{{ route('tag.delete') }}",
                  data: {
                     _token: "{{ csrf_token() }}",
                     tag_id: tag_id
                  },
                  success: function(response) {
                     if (response.status == "Success") {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                        setTimeout(function() {
                           location.reload()
                        }, 2000);
                     }
                  }
               });
            }
        });
    });
    //End Product Tag Script

    // Attribute script
    $(".addAttributbtn").click(function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{route('attribute.create')}}",
            data: '',
            dataType: 'json',
            success: function(data) {
                $('#addAttributemodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#addAttributeForm #AddAttributeBox').html(data.html);
                $('.dropify').dropify();
                $('.selectize-select').selectize();

                var picker = new jscolor('#add-hexa-colorpicker-1', options);
            },
            error: function(data) {
                console.log('data2');
            }
        });

    });


    $('.editAttributeBtn').on('click', function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{url('client/attribute')}}" + '/' + did + '/edit',
            data: '',
            dataType: 'json',
            beforeSend: function() {
                $(".loader_box").show();
            },
            success: function(data) {
                $('#editAttributemodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                
                $('#editAttributeForm #editAttributeBox').html(data.html);
                $('.dropify').dropify();
                $('.selectize-select').selectize();
                $("#editAttributeForm .hexa-colorpicker").each(function() {
                    var ids = $(this).attr('id');
                    try {
                        var picker = new jscolor('#' + ids, options);
                    } catch (err) {
                        console.log(err.message);
                    }
                });
                var getURI = document.getElementById('submitEditHidden').value;
                document.getElementById('editAttributeForm').action = data.submitUrl;
            },
            error: function(data) {
                console.log('data2');
            },
            complete: function() {
                $('.loader_box').hide();
            }
        });
    });

    $(document).on('click', '.addOptionRow-attribute-edit', function(e) {
        var d = new Date();
        var n = d.getTime();
        var $tr = $('.optionTableEditAttribute tbody>tr:first').next('tr');
        var $clone = $tr.clone();
        $clone.find(':text').val('');
        $clone.find(':hidden').val('');
        $clone.find('.hexa-colorpicker').attr("id", "hexa-colorpicker-" + n);
        $clone.find('.lasttd').html('<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>');
        $('.optionTableEditAttribute').append($clone);
        var picker = new jscolor("#hexa-colorpicker-" + n, options);
    });

    $("#addAttributemodal").on('click', '.deleteCurRow', function() {
        $(this).closest('tr').remove();
    });

    $("#editAttributemodal").on('click', '.deleteCurRow', function() {
        var delete_attr_id = $(this).data('delete_attr_id');
        var closet_tr = $(this).closest('tr');
        if( delete_attr_id != 'undefined' && delete_attr_id != undefined ) {
            $.ajax({
                type: "POST",
                url : "{{route('deleteAttribute')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": delete_attr_id
                },
                beforeSend: function() {
                    $(".editAttributeSubmit").attr("disabled", true);
                },
                success: function (response) {
                    
                    if(response.success) {
                        closet_tr.remove();
                    } else {
                        $('.delete_options').removeClass('d-none');
                    }
                },
                error: function(error) {
                    $('.delete_options').removeClass('d-none');
                },
                complete: function() {
                    $(".editAttributeSubmit").attr("disabled", false);
                }
            });
        }else{
            closet_tr.remove();
        }
    });

    $(document).on('click', '.deleteAttribute', function() {
        var did = $(this).attr('dataid');
        Swal.fire({
            title: "{{__('Are you sure?')}}",
            text:"{{__('You want to delete this attribute.')}}",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
        }).then((result) => {
            if(result.value)
            {
                $('#attrDeleteForm' + did).submit();
            }
        });
        return false;
    });

    $(document).on('change', '.dropDownTypeAttr', function() {
        var did = $(this).val();
        var dataFor = $(this).attr('dataFor');
        
        if (did == 1) {
            $('#' + dataFor + 'Attributemodal .hexacodeClass-' + dataFor).hide();
        } else if(did == 2){
                $('#' + dataFor + 'Attributemodal .hexacodeClass-' + dataFor).show();
        }else if(did == 3) {
            $('#' + dataFor + 'Attributemodal .hexacodeClass-' + dataFor).hide();
            $('.radio-div').removeClass('d-none');
        } else if(did == 4) {
            $('.attr-text-box').removeAttr('required');
        }
    });
    $(document).ready(function(){
        
        // hide alert
        if( $('.alert.alert-success').length ) {
            setTimeout(function(){
                $('.alert.alert-success').hide();
            }, 5000);
        }

        // add attribute from product edit page
        // Add option 
        $(document).on('click', '.add_attr_options', function(e){

            e.preventDefault();
            
            var attr_id = $(this).data('attribute_id');
            if( attr_id != 'undefined' && attr_id != undefined ) {
                // console.log('if paryt here');
                $.ajax({
                    type: "GET",
                    url : "{{url('client/attribute')}}" + '/' + attr_id + '/edit',
                    success: function(data) {
                        $('#editAttributemodal').modal({
                            backdrop: 'static',
                            keyboard: false
                        });
                        $('#editAttributeForm #editAttributeBox').html(data.html);
                        $('#editAttributeForm #editAttributeBox').append('<input type="hidden" name="id" value="'+attr_id+'" />');
                        $('#editAttributeForm .editAttributeSubmit').addClass('save-dynamic-options');
                        $('.dropify').dropify();
                        $('.selectize-select').selectize();
                        $("#editAttributeForm .hexa-colorpicker").each(function() {
                            var ids = $(this).attr('id');
                            try {
                                var picker = new jscolor('#' + ids, options);
                            } catch (err) {
                                console.log(err.message);
                            }
                        });
                    },
                    error: function() {

                    },
                    complete: function() {

                    }
                });
            }
        });
    });

    // when attribute update from product edit page
    $(document).on('click', '.save-dynamic-options', function(e){
        e.preventDefault();
        $('.outter-loader').removeClass('d-none');
        $('.save-dynamic-options').attr("disabled", true);

        // get attribute id
        var seariali_arr = $('#editAttributeForm').serializeArray();
        var last_length = seariali_arr[seariali_arr.length - 1]
        if(last_length['value'] != 'undefined' && last_length['value'] != undefined) {
            
            var product_id = $("input[name=product_id]").val();
            // Serialize form data to save
            var serailaize = $('#editAttributeForm').serializeArray();
            serailaize.push({ name: "product_id", value: product_id });
            
            $.ajax({
                url : "{{ route('updateAttributeOption') }}",
                data: serailaize,
                success: function(response) {
                    
                    if( response.success ) {
                        var attr_value = $('.attribute_option_id_'+last_length['value']).val();
                        var attr_html = response.html;
                        $("#attribute_section").html(attr_html);
                        $('.select2-multiple').select2();
                        $('#editAttributemodal').modal('hide');
                    }
                },
                error: function(error) {
                    console.log(error);
                },
                complete: function() {
                    $('.save-dynamic-options').attr("disabled", false);
                }
            });
        }

        
    });
</script>