$(function(){
   var vendorCityTable = '' ;
    $(document).on('click', '.edit_cities_page', function(event) {
        $('#vendor_city').modal('show');
        event.preventDefault();
        initDataTable();
        loadMap();
    });

    $(document).on('click', '#vedorCityForm .showMap', function(event) {
        Swal.fire({
            title: 'Add Manual Time',
            html: `<div class="row">
                        <div class="col-md-12">
                            <div id="address-map-container" class="w-100" style="height: 300px; min-width: 300px;">
                                <div id="pick-address-map" class="h-100"></div>
                            </div>
                            <input type="hidden" id="map_lat">
                            <input type="hidden" id="map_long">
                            <input type="hidden" id="map_address">
                            <input type="hidden" id="place_id">
                            <div class="pick_address p-2 mb-2 position-relative">
                                <div class="text-center">
                                    <button type="button" class="btn btn-solid ml-auto pick_address_confirm w-100" data-dismiss="modal"></button>
                                </div>
                            </div>
                        </div>
                    </div>`,
            confirmButtonText: 'Submit',
            focusConfirm: false,
            preConfirm: () => {
                // const memo = Swal.getPopup().querySelector('#memo').value
                // const blocktime = Swal.getPopup().querySelector('#blocktime').value
                // if (!memo || !blocktime) {
                //     Swal.showValidationMessage(`All feilds are required!!`)
                // }
                // return { blocktime: blocktime, memo: memo }
            },onOpen: function() {
                $(function() {
                    var lats = document.getElementById('city_latitude').value;
                    var lngs = document.getElementById('city_longitude').value;
                    if(lats==''){
                        lats=Default_latitude;
                    }
                    if(lngs==''){
                        lngs=Default_longitude;
                    }
                    document.getElementById('map_lat').value =lats ;
                    document.getElementById('map_long').value = lngs;

                    var myLatlng = new google.maps.LatLng(lats, lngs);

                    var infowindow = new google.maps.InfoWindow();
                    var geocoder = new google.maps.Geocoder();
                    var mapProp = {
                        center:myLatlng,
                        zoom:13,
                        mapTypeId:google.maps.MapTypeId.ROADMAP

                    };
                    var map=new google.maps.Map(document.getElementById("pick-address-map"), mapProp);
                    var marker = new google.maps.Marker({
                        position: myLatlng,
                        map: map,
                        draggable:true
                    });
                    // marker drag event
                    google.maps.event.addListener(marker, 'dragend', function() {
                        geocoder.geocode({
                            'latLng': marker.getPosition()
                        }, function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                if (results[0]) {
                                   
                                    document.getElementById('map_lat').value = marker.getPosition().lat();
                                    document.getElementById('map_long').value = marker.getPosition().lng();
                                    document.getElementById('map_address').value = results[0].formatted_address;
                                    document.getElementById('place_id').value = results[0].place_id;

                                    infowindow.setContent(results[0].formatted_address);

                                    infowindow.open(map, marker);
                                }
                            }
                        });
                    });
                });
            }
        }).then(async (result) => {
            console.log(result);
            document.getElementById('city_latitude').value  = document.getElementById('map_lat').value;
            document.getElementById('city_longitude').value = document.getElementById('map_long').value;
            document.getElementById('city-address').value = document.getElementById('map_address').value;
            document.getElementById('city-place_id').value = document.getElementById('place_id').value;
            // var formData = {
            // blocktime:result.value.blocktime,
            // memo:result.value.memo,
            // variant_id:variant_id,
            // product_id:product_id,
            // booking_slot:$('#blocktime').val()
            // }
            // await add_blocked_time(formData)
            // Swal.fire(`
            // blocktime: ${result.value.blocktime}
            //   memo: ${result.value.memo}
            // `.trim())
        });
    });

    function initDataTable(){
        $("#vendorCity").dataTable().fnDestroy()
        vendorCityTable =  $('#vendorCity').DataTable({
            processing: true,
            scrollY: '600px',
            scrollX: '200px',
            scrollCollapse: true,   
            responsive: true,
            ordering: false,
            lengthChange: false,

            ajax: `/client/vendor_city/index`,
            columns: [  
                { data: 'id' },
                { data: 'city_image' },
                { data: 'city_title' },
                { data: 'edit_action', class:'text-center', name: 'edit_action', orderable: false, searchable: false, "mRender":function(data, type, row, meta ){
                 
                  
                        return `<div class='form-ul'><div class='inner-div d-inline-block'><a class='action-icon add_cities'  data-id='${row.id}' href='javascript:void(0);'><i class='mdi mdi-eye'></i></a></div><div class='inner-div d-inline-block'><form method='POST' ><div class='form-group action-icon mb-0'><button type='button' class='btn btn-primary-outline action-icon delete-vendor_city' data-destroy_id='${row.id}' data-destroy_id='${row.id}'><i class='mdi mdi-delete'></i></button></div></form></div></div>`
                    }
                },
                // { data: 'hr.salary' },
            ],
            buttons: [
                {
                    text: 'Add City',
                    attr: {id: 'add_city' },
                    action: function ( e, dt, node, config ) {
                        //alert( 'Button activated' );
                        
                    }
                }
            ]
        });
    }
    $(document).on('click', '.submitSaveVendorCity', function(e) {
        $('.submitSaveVendorCity').attr("disabled", true); 
        var vendor_city_id = $("#vendor_city input[name=vendor_city_id]").val();
        if (vendor_city_id) {
            var post_url = `/client/vendor_city/update`;
            var msg_text = `Update successfully`;
        } else {
            var post_url = `/client/vendor_city/store`;
            var msg_text = `Added successfully`;
        }
        // console.log(vendor_city_id);
        // return false;
        var formData = new FormData(document.getElementById("vedorCityForm"));
        axios.post(post_url, formData)
        .then(async response => {
         console.log(response.status);
                if (response.data.status == 'Success') {
                    
                    $('#vedorCityForm')[0].reset();
                    document.getElementById('vendor_city_id').value = ''
                    var html = `<input type="file" id="vendor_city_image" name="vendor_city_image" class="dropify form-control" data-default-file="" required />`;
                    $('.vendor_city_image').html(html);
                    $('.dropify').dropify();
                    vendorCityTable.ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.data.message,
                    })
                    $('.submitSaveVendorCity').attr("disabled", false); 
                  //$('#vendor_city').modal('hide');
            
               } else {
                    $('.submitSaveVendorCity').attr("disabled", false); 
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.data.message,
                    })
               }
           
        })
        .catch(e => {
            console.log(e);
            $('.submitSaveVendorCity').attr("disabled", false); 
                let errors = e.response.data.errors;
                Object.keys(errors).forEach(function(key) {
                    var data = key.replace('.','_');
                    $("." + data ).addClass("is-invalid");
                });
            })    
    });

    $(document).on('click','.delete-vendor_city',function(){
        var city_id = $(this).data('destroy_id');
        Swal.fire({
            title: 'Warning!',
            text: 'Are you sure?',
            // input: 'text',
            // inputPlaceholder: 'Delete',
          }).then(({value}) => {
           // if (value === "Yes") {
                deleteVendorCity(city_id);
               
            // }  else {
            //     Swal.fire({
            //         icon: 'error',
            //         title: 'Oops...',
            //         text: 'Entered wrong text!',
            //         //footer: '<a href="">Why do I have this issue?</a>'
            //     })
            // }
          });
    });

    function deleteVendorCity(city_id){
        
        axios.get(`/client/vendor_city/destroy/${city_id}`)
        .then(async response => {
         
             console.log(response);
             if(response.data.success) {
                // Swal.fire(
                //     'Deleted successfully!',                                    
                //     'success'
                // )
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Deleted successfully!',
                    //footer: '<a href="">Why do I have this issue?</a>'
                })
             }
             
        })
        .catch(e => {
            Swal.fire(
                'Something went wrong, try again later!',                                    
                'error'
            )
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong, try again later!',
                //footer: '<a href="">Why do I have this issue?</a>'
            })
        })    
    }

    function loadMap() {
        var input = document.getElementById('city-address');
        var autocomplete = new google.maps.places.Autocomplete(input);
        // autocomplete.bindTo('bounds', bindMap);
        if(is_map_search_perticular_country){
            autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
        }
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            
             console.log(place.place_id);
            document.getElementById('city_longitude').value = place.geometry.location.lng();
            document.getElementById('city_latitude').value = place.geometry.location.lat();
            document.getElementById('place_id').value = place.place_id;
        });

        setTimeout(function(){
            $(".pac-container").appendTo("#add_new_address_form .address-input-group");
        }, 300);

    }
    $(document).on("click", ".add_cities", function() {
        let tag_id = $(this).data('id');
        $('#vendor_city input[name=vendor_city_id]').val(tag_id);
        if(tag_id) {
            showCities(tag_id);
        } 
        
        
    });

    function showCities(tag_id){
        $.ajax({
            method: 'GET',
            
            url: `/client/vendor_city/show/${tag_id}`,
            success: function(response) {
                console.log(response);
               if (response.status = 'Success') {
                    var data = response.data;
                    if(data!= undefined && data!=null){
                        document.getElementById('city_latitude').value = data.latitude;
                        document.getElementById('city_longitude').value = data.longitude;
                        document.getElementById('city-address').value = data.address;
                        document.getElementById('place_id').value = data.place_id;
                        var image = data.image.proxy_url+'100/100'+data.image.image_path;
                        var html = `<input type="file" id="vendor_city_image" name="vendor_city_image" class="dropify form-control" data-default-file="${image}" required />`;
                        $('.vendor_city_image').html(html);
                        $('.dropify').dropify();
                          $.each(response.data.translations, function( index, value ) {
                            $('#vendor_city #city_name_'+value.language_id).val(value.name);
                        });
                        
                        // $(".dropify").attr("data-default-file", "https://dummyimage.com/600x400/000/fff");
                        // $('.dropify').dropify();
                    }
                    
                //   $("#add_product_tag_modal input[name=tag_id]").val(response.data.id);
                //   $('#add_product_tag_modal #standard-modalLabel').html('Update Product Tag');
                //   $('#add_product_tag_modal').modal('show');
                //   $.each(response.data.translations, function( index, value ) {
                //     $('#add_product_tag_modal #product_tag_name_'+value.language_id).val(value.name);
                //   });
               }
            },
            error: function() {

            }
        });
    }
    
});