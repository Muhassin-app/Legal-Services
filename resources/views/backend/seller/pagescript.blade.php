<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script>
        var section_id = 0;
        function addvendorSectionTemplate(section_id){
            section_id                = parseInt(section_id);
            section_id                = section_id +1;
            var data                  = '';
            //console.log(section_id);
            var price_section_temp    = $('#vendor_section_template').html();
            var modified_temp         = _.template(price_section_temp);

            // console.log(languages);
            // $.each(languages, function( index, value ) {
            //     var result_html           = modified_temp({id:section_id,data:data,language:value});
            //     $("#vendor_section_options").append(result_html);
            // });

            var result_html           = modified_temp({id:section_id,data:data});
            $("#vendor_section_options").append(result_html);
            $('.add_more_button').hide();
            $('#add_button_'+section_id).show();
        }
        $(document).on('click','.add_more_button',function(){
            var main_id = $(this).data('id');
            addvendorSectionTemplate(main_id);
            console.log($('.add_more_button').length);
        });
        $(document).on('click','.remove_more_button',function(){
            var main_id =$(this).data('id');
            removeFaqSectionTemplate(main_id);
            $('.add_more_button').each(function(key,value){
                if(key == ($('.add_more_button').length-1)){
                    $('#add_button_'+$(this).data('id')).show();
                }
            });
        });
        function removeFaqSectionTemplate(div_id){
            $('#option_section_'+div_id).remove();
        }
        $(document).on('click', '#add_vendor_section_form', function() {
            submitVendorSectionForm();
        });


    function submitVendorSectionForm() {
        var form = document.getElementById('save_vendor_section_form');
        var formData = new FormData(form);
        let section_id = $("#save_vendor_section_form input[name='section_id']").val();
        console.log(section_id);
        var data_uri = "{{route('vsection.store')}}";
        if(section_id != undefined && section_id!= ''){
             data_uri = "{{route('vsection.update')}}";
        }
        console.log(data_uri);
      // return false;
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
                // location.reload();
                if (response.status == 'success') {
                    $('.section_msg').text('Updated successfully');

                    //setTimeout(function() {
                        $(".modal .close").click();
                         location.reload();
                    //}, 2000);
                    //location.reload();
                } else {
                    $("#add_vendor_section_form").attr("disabled", false);
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            beforeSend: function() {

                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
                setTimeout(function() {
                   location.reload();
                }, 2000);

            },error: function(response) {
                $("#add_vendor_section_form").attr("disabled", false);
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
    $(document).on("change","#client_language",function() {
        let language_id = $(this).val();
        let section_id = $("#save_vendor_section_form input[name='section_id']").val();
      console.log(section_id);
      if(section_id != undefined && section_id != ''){
        getVendorSection(section_id, language_id);
      }
    });

    $(document).on("click",".deleteMultiBanner",function() {
        //alert('asd');
        var banner_id =  $(this).data('banner_id');
       console.log(banner_id);
        Swal.fire({
            icon: 'warning',
            title: "{{ __('Are you sure? You want to delete this Banner.')}}",
            confirmButtonText: 'Yes',
            focusConfirm: false,
            preConfirm: () => {

            },onOpen: function() {
            }
          }).then(async (result) => {

            await  deleteMultiBanner(banner_id)
          })



    });
    function deleteMultiBanner(banner_id){
        axios.get(`/client/vendor_banner/destroy/${banner_id}`)
        .then(async response => {
            console.log(response);
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.data.message,
                })
                setTimeout(async ()=>{
                    location.reload();
                },2000)

        })
        .catch(e => {
            Swal.fire(
                'Something went wrong, try again later!',
                'error'
            )
        })

    }
    $(document).on("click",".submitMultibannerForm",function() {
        //alert('asd');
        var form = document.getElementById('save_multi_banner_form');
        var formData = new FormData(form);
        if(document.getElementsByName("banner_image")[0].value == "") {
            sweetAlert.error('Oops...','Please select an Image!')
        }
        axios.post(`/client/vendor_banner/store`, formData)
        .then(async response => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: response.data.message,
            })
            setTimeout(async ()=>{
                location.reload();
            },2000)


        })
        .catch(e => {
            Swal.fire(
               "{{__('Something went wrong, try again later!')}}",
                'error'
            )
        })

    });

    $(document).on('click','.editSectionBtn',function(){
        var section_id = $(this).data('id');
        var language_id = $(this).data('language_id');
        if(section_id != undefined && section_id != ''){
            getVendorSection(section_id, language_id);
        }

    });
    $('.openVendorSectionModal').click(function() {
        $('#add_section').modal();
        $("#save_vendor_section_form input[name='section_id']").val('');
        document.getElementById("save_vendor_section_form").reset();

        $('#add_section #header_title').html(`{{ __("Add Section") }}`);
        $("#vendor_section_options").html('');
        addvendorSectionTemplate(0);

    });

    function getVendorSection(section_id, language_id){
        var get_section_url = "{{ route('vsection.edit', ':id') }}";
        var url = get_section_url.replace(":id", section_id);
        $.get(url, {language_id:language_id},function(response) {
              if(response.status == 'Success'){
                    if(response.data){

                        $("#vendor_section_options").html('');
                        $("#save_vendor_section_form input[name='section_id']").val(response.data.id);
                        $("#save_vendor_section_form input[name='heading']").val((response.data.heading_translation[0]!= undefined)? response.data.heading_translation[0].heading : '');
                        var section_translation = response.data.section_translation;
                        var vendor_section_temp    = $('#vendor_section_template').html();
                        var modified_temp         = _.template(vendor_section_temp);
                        var section_id = 0
                        $(section_translation).each(function(index, value) {
                            section_id                = parseInt(section_id);
                            section_id                = section_id +1;
                            $('#vendor_section_options').append(modified_temp({ id:section_id,data:value}));
                            $('.add_more_button').hide();
                            $('#add_button_'+section_id).show();
                        });
                        addvendorSectionTemplate(section_id);
                        $('#add_section').modal();
                        $('#add_section #header_title').html(`{{ __("Edit Section") }}`);
                    }
              }
            });
    }
    $(document).on('click', ' .iti__country', function() {
        var code = $(this).attr('data-country-code');
        $('#editCardBox #vendorCountryCode').val(code);
        var dial_code = $(this).attr('data-dial-code');
        $('#editCardBox #vendorDialCode').val(dial_code);
    });



    $('.openAddModal').click(function() {
        $('#add-form').modal({
            //backdrop: 'static',
            keyboard: false

        });
       // runPicker();
        $('.dropify').dropify();
        $('.selectize-select').selectize();
        $('.selectized').selectize();
        autocompletesWraps.push('add');
        loadMap(autocompletesWraps);

    });

    $('.openImportModal').click(function() {
        $('#import-form').modal({
            //backdrop: 'static',
            keyboard: false
        });
        //runPicker();
        $('.dropify').dropify();
    });

    function runPicker() {
        $('.datetime-datepicker').flatpickr({
            enableTime: true,
            dateFormat: "Y-m-d H:i"
        });

        $('.selectpicker').selectpicker();
    }

    var autocomplete = {};
    var autocompletesWraps = [];
    var count = 1;
    editCount = 0;
    $(document).ready(function() {

        autocompletesWraps.push('def');
        loadMap(autocompletesWraps);
    });

    function loadMap(autocompletesWraps) {

        // console.log(autocompletesWraps);
        $.each(autocompletesWraps, function(index, name) {
            const geocoder = new google.maps.Geocoder;

            if ($('#' + name).length == 0) {
                return;
            }
            //autocomplete[name] = new google.maps.places.Autocomplete(('.form-control')[0], { types: ['geocode'] }); console.log('hello');
            autocomplete[name] = new google.maps.places.Autocomplete(document.getElementById(name + "-address"), {
                types: ['geocode']
            });
            if(is_map_search_perticular_country){
                autocomplete[name].setComponentRestrictions({'country': [is_map_search_perticular_country]});
            }
            google.maps.event.addListener(autocomplete[name], 'place_changed', function() {
                var place = autocomplete[name].getPlace();
                if (!place.geometry) {
                    window.alert("Autocomplete's returned place contains no geometry");
                    return;
                }
                geocoder.geocode({
                    'placeId': place.place_id
                }, function(results, status) {

                    if (status === google.maps.GeocoderStatus.OK) {
                        const lat = results[0].geometry.location.lat();
                        const lng = results[0].geometry.location.lng();
                        document.getElementById(name + '_latitude').value = lat;
                        document.getElementById(name + '_longitude').value = lng;
                    }
                });

                for (let i = 1; i < place.address_components.length; i++) {
                    let mapAddress = place.address_components[i];
                    if (mapAddress.long_name != '') {
                        let streetAddress = '';
                        if (mapAddress.types[0] == "street_number") {
                            streetAddress += mapAddress.long_name;
                        }
                        if (mapAddress.types[0] == "route") {
                            streetAddress += mapAddress.short_name;
                        }
                        if ($('#street').length > 0) {
                            document.getElementById('street').value = streetAddress;
                        }
                        if (mapAddress.types[0] == "locality") {
                            document.getElementById('city').value = mapAddress.long_name;
                        }
                        if (mapAddress.types[0] == "administrative_area_level_1") {
                            document.getElementById('state').value = mapAddress.long_name;
                        }
                        if (mapAddress.types[0] == "postal_code") {
                            document.getElementById('pincode').value = mapAddress.long_name;
                        } else {
                            document.getElementById('pincode').value = '';
                        }
                        if (mapAddress.types[0] == "country") {
                            document.getElementById('country').value = mapAddress.long_name.toUpperCase();

                        }
                    }
                }

            });

        });
    }
    function checkAddressString(obj,name)
    {
        if($(obj).val() == "")
        {
            document.getElementById(name + '_latitude').value = '';
            document.getElementById(name + '_longitude').value = '';
        }
    }
    $('#show-map-modal').on('hide.bs.modal', function() {
        $('#add-customer-modal').removeClass('fadeIn');

    });

    $(document).on('click', '.showMap', function() {
        var no = $(this).attr('num');
        console.log(no);

        var lats = document.getElementById(no + '_latitude').value;
        var lngs = document.getElementById(no + '_longitude').value;
        var address = document.getElementById(no+'-address').value;
        console.log(lats + '--' + lngs);

        document.getElementById('map_for').value = no;

        if (lats == null || lats == '0' || lats =='') {
            lats = Default_latitude;
        }
        if (lngs == null || lngs == '0'  || lngs == '') {
            lngs = Default_longitude ;
        }
        if(address==null){
            address= '';
        }

        var myLatlng = new google.maps.LatLng(lats, lngs);
        var mapProp = {
            center: myLatlng,
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.ROADMAP

        };
        document.getElementById('lat_map').value= lats;
        document.getElementById('lng_map').value= lngs ;
        document.getElementById('address_map').value= address ;
        var infowindow = new google.maps.InfoWindow();
        var geocoder = new google.maps.Geocoder();
        var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            title: 'Hello World!',
            draggable: true
        });
        document.getElementById('lat_map').value = lats;
        document.getElementById('lng_map').value = lngs;

        google.maps.event.addListener(marker, 'dragend', function() {
            geocoder.geocode({
            'latLng': marker.getPosition()
            }, function(results, status) {

            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                        document.getElementById('lat_map').value = marker.getPosition().lat();
                        document.getElementById('lng_map').value = marker.getPosition().lng();
                        document.getElementById('address_map').value= results[0].formatted_address;

                    infowindow.setContent(results[0].formatted_address);

                    infowindow.open(map, marker);
                }
            }
            });
        });
        // // marker drag event
        // google.maps.event.addListener(marker, 'drag', function(event) {
        //     document.getElementById('lat_map').value = event.latLng.lat();
        //     document.getElementById('lng_map').value = event.latLng.lng();
        // });

        //marker drag event end
        // google.maps.event.addListener(marker, 'dragend', function(event) {
        //     var zx = JSON.stringify(event);
        //     console.log(zx);


        //     document.getElementById('lat_map').value = event.latLng.lat();
        //     document.getElementById('lng_map').value = event.latLng.lng();
        //     //alert("lat=>"+event.latLng.lat());
        //     //alert("long=>"+event.latLng.lng());
        // });
        $('#add-customer-modal').addClass('fadeIn');
        $('#show-map-modal').modal({
            //backdrop: 'static',
            keyboard: false
        });

    });

    $(document).on('click', '.selectMapLocation', function() {

        var mapLat = document.getElementById('lat_map').value;
        var mapLlng = document.getElementById('lng_map').value;
        var mapFor = document.getElementById('map_for').value;
        var address = document.getElementById('address_map').value;

        document.getElementById(mapFor + '_latitude').value = mapLat;
        document.getElementById(mapFor + '_longitude').value = mapLlng;
        document.getElementById(mapFor + '-address').value = address;

        $('#show-map-modal').modal('hide');
    });

    $(".openEditModal").click(function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var uri = "{{ isset($vendor) ? route('vendor.edit', $vendor->id) : '' }}";
        $.ajax({
            type: "get",
            url: uri,
            data: '',
            dataType: 'json',
            success: function(data) {
                $('#edit-form').modal('show');
                $('#edit-form #editCardBox').html(data.html);
                $('.selectize-select').selectize();
                $('.dropify').dropify();
                // dine = document.getElementsByClassName('dine_in');
                // var switchery = new Switchery(dine[0]);
                // take = document.getElementsByClassName('takeaway');
                // var switchery = new Switchery(take[0]);
                // delivery = document.getElementsByClassName('delivery');
                // var switchery = new Switchery(delivery[0]);
                var elems = document.querySelectorAll('.editSwitchery');
                elems.forEach(function(html) {
                    var switchery = new Switchery(html);
                });
                autocompletesWraps.push('edit');
                loadMap(autocompletesWraps);
                // },
                // error: function (data) {
                //     console.log('data2');
                // },
                // beforeSend: function(){
                //     $(".loader_box").show();
                // },
                // complete: function(){
                //     $(".loader_box").hide();
                var input = document.querySelector("#editCardBox #vendor_phone_number");
                console.log(input);
                if(input){
                    window.intlTelInput(input, {
                        separateDialCode: true,
                        hiddenInput: "contact",
                        utilsScript: "{{asset('assets/js/utils.js')}}",
                        initialCountry: "{{ Session::get('default_country_code','US') }}",
                    });
                }
            }
        });
    });

    function submitProductImportForm() {
        var form = document.getElementById('save_imported_products');
        var formData = new FormData(form);
        var data_uri = "{{route('product.import')}}";
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
                // location.reload();
                if (response.status == 'success') {
                    $(".modal .close").click();
                    location.reload();
                } else {

                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            beforeSend: function() {

                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
                setTimeout(function() {
                    location.reload();
                }, 2000);

            }
        });
    }


    function submitImportForm() {
        var form = document.getElementById('save_imported_vendors');
        var formData = new FormData(form);
        var data_uri = "{{route('vendor.import')}}";
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
                     $("#import-form").modal('hide');
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

    $(document).on('click', '.submitAddForm', function(e) {
        e.preventDefault();
        var form = document.getElementById('save_banner_form');
        var formData = new FormData(form);
        var url = "{{route('vendor.store')}}";
        saveData(formData, 'add', url);

    });

    $(document).on('click', '.submitEditForm', function(e) {
        e.preventDefault();
        var form = document.getElementById('save_edit_banner_form');
        var formData = new FormData(form);
        var url = "{{ isset($vendor) ? route('vendor.update', $vendor->id) : ''}}";

        saveData(formData, 'edit', url);

    });

    function saveData(formData, type, data_uri) {
      //  console.log(data_uri);
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

                if (response.status == 'success') {
                    $(".modal .close").click();
                    location.reload();
                } else {
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
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
    $(document).on('click', '#add_vendor_form', function(e) {
        e.preventDefault();
        var input='';
        $(".activeCategory:checkbox:checked").each(function(){
            var category_id = $(this).data('category_id');
                 input+= "  <input type='hideen' name='category_ids[]' value='"+category_id+"' >";
        });
        $('#nestable_list_1').append(input);
        var form = document.getElementById('save_banner_form');
        var formData = new FormData(form);
        var data_uri = "{{route('vendor.store')}}";
        // console.log(formData);
        // return false;
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
                if (response.status == 'success') {
                    $(".modal .close").click();
                    location.reload();
                } else {
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
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

    });



    $(".openAddonModal").click(function(e) {
        $('#addAddonmodal').modal({
            backdrop: 'static',
            keyboard: false
        });
        var slider = $("#slider-range").data("ionRangeSlider");
        var from = slider.result.from;
        var to = $('#banner-datatable >tbody >tr.input_tr').length;
        slider.update({
            grid: false,
        });
    });
    $(document).on('click', '.addOptionRow-Add', function(e) {
        var $tr = $('.optionTableAdd tbody>tr:first').next('tr');
        var $clone = $tr.clone();
        $clone.find(':text').val('');
        $clone.find('.lasttd').html('<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>');
        $('.optionTableAdd').append($clone);
        var slider = $("#slider-range").data("ionRangeSlider");
        var from = slider.result.from;
        var to = $('#banner-datatable >tbody >tr.input_tr').length;
        slider.update({
            min: from,
            max: to,
        });
    });

    $(document).on('click', '.addOptionRow-edit', function(e) {
        var $tr = $('.optionTableEdit tbody>tr:first').next('tr');
        var $clone = $tr.clone();
        $clone.find(':text').val('');
        $clone.find(':hidden').val('');
        $clone.find('.lasttd').html('<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>');
        $('.optionTableEdit').append($clone);
        var slider = $("#slider-range1").data("ionRangeSlider");
        var from = slider.result.from;
        var to = $('#edit_addon-datatable >tbody >tr.input_tr').length;
        slider.update({
            min: from,
            max: to,
        });
    });
    $("#addAddonmodal").on('click', '.deleteCurRow', function() {
        var slider = $("#addAddonmodal #slider-range").data("ionRangeSlider");
        var from = slider.result.from;
        var to = $('#addAddonmodal #banner-datatable >tbody >tr.input_tr').length - 1;
        slider.update({
            min: from,
            max: to,
        });
        $(this).closest('tr').remove();
        var slider = $("#slider-range").data("ionRangeSlider");
        var from = slider.result.from;
        var to = $('#banner-datatable >tbody >tr.input_tr').length;
        slider.update({
            min: from,
            max: to,
        });
    });

    $("#editdAddonmodal").on('click', '.deleteCurRow', function() {
        var slider = $("#editdAddonmodal #slider-range").data("ionRangeSlider");
        var from = slider.result.from;
        var to = $('#editdAddonmodal #edit_addon-datatable >tbody >tr.input_tr').length - 1;
        if (to == 1) {
            from = 0;
        }
        slider.update({
            min: from,
            max: to,
        });
        $(this).closest('tr').remove();
        var slider = $("#slider-range1").data("ionRangeSlider");
        var from = slider.result.from;
        var to = $('#edit_addon-datatable >tbody >tr.input_tr').length;
        slider.update({
            min: from,
            max: to,
        });
    });

    $(document).on('click', '.deleteAddon', function() {

        var did = $(this).attr('dataid');
        if (confirm("Are you sure? You want to delete this addon set.")) {
            $('#addonDeleteForm' + did).submit();
        }
        return false;
    });

    $('.editAddonBtn').on('click', function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "<?php echo url('client/addon'); ?>" + '/' + did + '/edit',
            data: '',
            dataType: 'json',
            success: function(data) {
                $('#editdAddonmodal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $('#editAddonForm #editAddonBox').html(data.html);
                $('#editdAddonmodal .modal-title').html('Edit AddOn Set');
                $('#editdAddonmodal .editAddonSubmit').html('Update');
                document.getElementById('editAddonForm').action = data.submitUrl;
                setTimeout(function() {
                    var max = $('#edit_addon-datatable >tbody >tr.input_tr').length;
                    var $d4 = $("#editAddonForm #slider-range1");
                    $d4.ionRangeSlider({
                        type: "double",
                        grid: false,
                        min: 0,
                        max: max,
                        from: data.min_select,
                        to: data.max_select
                    });
                    $d4.on("change", function() {
                        var $inp = $(this);
                        $("#editAddonForm #max_select").val($inp.data("to"));
                        $("#editAddonForm #min_select").val($inp.data("from"));
                    });
                }, 1000);
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            },
            error: function(data) {
                console.log('data2');
            }
        });
    });
    $(document).on('click', '.delete_addon_set', function(e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        console.log('sdf');
        var option_id = $(this).attr('data_addon_id');

        $.ajax({
            type: "POST",
            url: "{{route('addonoption_delete')}}",
            data: {'option_id':option_id},
            dataType: 'json',
            success: function(data) {
                Swal.fire({
                    text: "{{__('Option deleted successfully!') }}",
                    icon: "success",
                    button: "OK",
                }).then((result) => {
                    location.reload();
                });
                setTimeout(function () {
            $(element).addClass('d-none');
            $(element).find(".alert").hide();
        }, 8000);
            },
            beforeSend: function() {
                $(".loader_box").show();
            },
            complete: function() {
                $(".loader_box").hide();
            },
            error: function(data) {
                console.log('data2');
            }
        });

    });
    // search users for set permission
    $('#search_user_for_vendor_permission').keyup(function(){
        var query = $(this).val();
        var vendor_id = 1;
        if(query != '')
        {
            var _token = $('input[name="_token"]').val();
            $.ajax({
            url:"{{ route('searchUserForPermission') }}",
            method:"POST",
            data:{query:query, _token:_token, vendor_id:vendor_id},
            success:function(data){
            $('#userList').fadeIn();
            $('#userList').html(data);
            }
            });
        }
    });
    $(document).on('click', '#userList li', function(){
        $('#search_user_for_vendor_permission').val($(this).text());
        $('#userId').val($(this).attr('data-id'));
        $('#userList').fadeOut();
    });

    // search users for set permission
    $('#search_user_for_permission').keyup(function(){
        var query = $(this).val();
        var vendor_id = 0;
        var userid =[];
        $("#selected_user .user_hidden_ids").each(function(){
            var id = $(this).val();
            userid.push(id)

        });
        // if($("input[name='userIDs[]']").val()){
        //   userid = $("input[name='userIDs[]']").val();
        // }

        if(query != '')
        {
            var _token = $('input[name="_token"]').val();
            $.ajax({
            url:"{{ route('searchUserForPermission') }}",
            method:"POST",
            data:{query:query, _token:_token, vendor_id:vendor_id,user_ids:userid},
            success:function(data){
            $('#userList_model').fadeIn();
            $('#userList_model').html(data);
            }
            });
        }
    });
    $(document).on('click', '#userList_model li', function(){
        $("#selected_user_ul").removeClass('active');
        $("#selected_user_ul").addClass('active');
        $('#search_user_for_permission').val('');
       var user_id = $(this).attr('data-id');
       var name = $(this).attr('data-name');
       var image = $(this).attr('data-image');
       var email = $(this).attr('data-email');

       $('#userList_model').fadeOut();
        var user_id_section    = $('#user_id_section').html();
        var modified_temp         = _.template(user_id_section);
        $('#selected_user').append(modified_temp({ id:section_id,user_id:user_id,name:name,image:image,email:email}));

      // $('#userList_model').fadeOut();
    });
    $(document).on('click', '#addUserAddForm', function(e){
        e.preventDefault();
        var url=$(this).attr('data-url');

        console.log(url);
        var name       = $("#new_user_name").val();
        var token       = $("input[name=_token]").val();
        var email      = $("#new_user_email").val();
        var phone_number = $("#new_user_phone_number").val();
        var countryCode = $("#countryCode").val();
        var dial_code   = $("#dialCode").val();
        var password    = $("#new_user_password").val();

        console.log(name,phone_number,countryCode,dial_code);
        if(name =='' || email =='' || phone_number =='' || password ==''  ){

         var alert = '<div class="alert alert-danger"><span>All fields required</span></div>'
            $('#adduesr_error').html(alert);
            return false;
        }

        // var contact=dial_code+phone_number;

        $.ajax({
            method: 'post',
            url: url,
            data: { _token:token,name: name,contact:phone_number,phone_number:phone_number,dial_code:dial_code,email:email,password:password},
            success: function(response) {
                $('#adduesr_error').html('');
                console.log(response);
                var user =response.Userdata;
                console.log(user);
                var user_id =user.id;
                var name = user.name;
                var email =user.email;

                var image = '';
                if( user.image){
                    image=user.image.image_fit+'100/100'+user.image.image_path;
                }

                var user_id_section    = $('#user_id_section').html();
                var modified_temp         = _.template(user_id_section);
                $('#selected_user').append(modified_temp({ id:section_id,user_id:user_id,name:name,image:image,email:email}));

            },
            error: function(response) {
                var alert = '<div class="alert alert-danger">';
                if (response.status === 422) {
                    let errors = response.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        alert+='<span>'+errors[key][0]+'</span><br>';

                    });
                } else {
                    alert+='<span>Something went wrong, Please try Again.</span>';
                }
                alert+='</div>';

                $('#adduesr_error').html(alert);
                return response;
            }
        });
    });


    ///// **************** 1.1  check vendor exists in dispatcher or not for pickup********** //////////

    $(".openConfirmDispatcher").click(function(e) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        e.preventDefault();


        var uri = "{{route('update.Create.Vendor.In.Dispatch')}}";
        var id = $(this).data('id');

        $.ajax({
            type: "post",
            url: uri,
            data: {
                id: id
            },
            dataType: 'json',
            success: function(data) {
                var url = data.url;
                window.open(url, '_blank');
            },
            error: function(data) {
                Swal.fire({
                    // title: "Warning!",
                    text: data.message,
                    icon : "error",
                    button: "{{__('ok')}}",
                });
                //alert(data.message);
            },
            beforeSend: function() {
                $(".loader_box").show();
                var token = $('meta[name="csrf_token"]').attr('content');
                if (token) {
                    return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
    });
    /////////////// **************   end 1.1 *****************************///////////////

    ///// **************** 1.2  check vendor exists in dispatcher or not for on demand********** //////////

    $(".openConfirmDispatcherOnDemand").click(function(e) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        e.preventDefault();


        var uri = "{{route('update.Create.Vendor.In.Dispatch.OnDemand')}}";
        var id = $(this).data('id');

        $.ajax({
            type: "post",
            url: uri,
            data: {
                id: id
            },
            dataType: 'json',
            success: function(data) {
                var url = data.url;
                window.open(url, '_blank');
            },
            error: function(data) {
                Swal.fire({
                    // title: "Warning!",
                    text: data.message,
                    icon : "error",
                    button: "{{__('ok')}}",
                });
                //alert(data.message);
            },
            beforeSend: function() {
                $(".loader_box").show();
                var token = $('meta[name="csrf_token"]').attr('content');
                if (token) {
                    return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
    });
    /////////////// **************   end 1.2 *****************************///////////////
    ///// **************** 1.3  check vendor exists in dispatcher or not for laundry********** //////////

    $(".openConfirmDispatcherLaundry").click(function(e) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        e.preventDefault();


        var uri = "{{route('update.Create.Vendor.In.Dispatch.Laundry')}}";
        var id = $(this).data('id');

        $.ajax({
            type: "post",
            url: uri,
            data: {
                id: id
            },
            dataType: 'json',
            success: function(data) {
                var url = data.url;
                window.open(url, '_blank');
            },
            error: function(data) {
                alert(data.message);
            },
            beforeSend: function() {
                $(".loader_box").show();
                var token = $('meta[name="csrf_token"]').attr('content');
                if (token) {
                    return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
    });
/////////////// **************   end 1.3 *****************************///////////////
 ///// **************** 1.4  check vendor exists in dispatcher or not for appointment********** //////////

$(".openConfirmAppointmentDispatcher").click(function(e) {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        e.preventDefault();


        var uri = "{{route('update.Create.Vendor.In.Dispatch.Appointment')}}";
        var id = $(this).data('id');

        $.ajax({
            type: "post",
            url: uri,
            data: {
                id: id
            },
            dataType: 'json',
            success: function(data) {
                var url = data.url;
                if(url != undefined && url !=''){
                    window.open(url, '_blank');
                }else{
                    alert(data.message);
                }

            },
            error: function(data) {
                alert(data.message);
            },
            beforeSend: function() {
                $(".loader_box").show();
                var token = $('meta[name="csrf_token"]').attr('content');
                if (token) {
                    return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }
            },
            complete: function() {
                $(".loader_box").hide();
            }
        });
});
/////////////// **************   end 1.4 *****************************///////////////
</script>
