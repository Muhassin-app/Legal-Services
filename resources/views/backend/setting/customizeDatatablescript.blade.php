<script>
$(document).ready(function(){
    StaticLocationDatatable();
    var Default_latitude = "30.7333";
    var Default_longitude = "76.7794";
    @if(!empty($client_preference_detail->Default_latitude))
        Default_latitude = "{{$client_preference_detail->Default_latitude}}";
    @endif
    @if(!empty($client_preference_detail->Default_longitude))
        Default_longitude = "{{$client_preference_detail->Default_longitude}}";
    @endif
    var bindLatlng, bindmapProp, bindMap = '';
    function bindLatestCoords(userLatitude, userLongitude){
        bindLatlng = new google.maps.LatLng(userLatitude, userLongitude);
        bindmapProp = {
            center:bindLatlng,
            zoom:13,
            mapTypeId:google.maps.MapTypeId.ROADMAP
        };
        bindMap=new google.maps.Map(document.getElementById("nearmap"), bindmapProp);
    }
    bindLatestCoords(Default_latitude, Default_longitude);

    function StaticLocationDatatable(){
        $('#static_dropoff_datatable').DataTable({
            "responsive": true,
            "scrollX": true,
            "destroy": true,
            "processing": true,
            "serverSide": true,
            "iDisplayLength": 5,
            "lengthChange" : false,
            "searching": false,
            "ordering": false,
            
            language: {
                        search: "",
                        info:'{{__("Showing _START_ to _END_  of _TOTAL_ entries")}}',
                        paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                        searchPlaceholder: "{{__('Search Product')}}",
                        // 'loadingRecords': '&nbsp;',
                        'processing': '<div class="spinner"></div>'
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
        
            ajax: {
                url: "{{route('static-dropoff.index')}}",
                data: function (d) {
                    d.search = $('input[type="search"]').val();
                }
            },
            columns: [
                    {data: 'title', name: 'title', orderable: false, searchable: false},
                    {data: 'address', name: 'address', class: 'text-wrap', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
        
        });
    }
     //user document model
     $('#add_static_dropoff_modal_btn').click(function(e) {
        document.getElementById("staticDropoffForm").reset();
        $('#add_static_dropoff_modal input[name=static_address_id]').val("");
        initialize();
        $('#add_static_dropoff_modal').modal('show');
        $('#add_static_dropoff_modal #standard-modalLabel').html('Add Static Dropoff Location');
    });

    $(document).on("click", ".edit_static_dropoff_btn", function() {
        let static_dropoff_id = $(this).data('static_dropoff_id');
        $('#add_static_dropoff_modal input[name=static_dropoff_id]').val(static_dropoff_id);
        $.ajax({
            method: 'GET',
            data: {
                static_dropoff_id: static_dropoff_id
            },
            url: "{{ route('static-dropoff.edit') }}",
            success: function(response) {
                if (response.status = 'Success') {
                    $('#add_static_dropoff_modal').modal('show');
                    $("#location_title").val(response.data.title);
                    initialize();
                    $("#static-address").val(response.data.address);
                    $("#static_latitude").val(response.data.latitude);
                    $("#static_longitude").val(response.data.longitude);
                    $("#static_place_id").val(response.data.place_id);
                    $("#static_address_id").val(response.data.id);
                    $('#add_static_dropoff_modal #standard-modalLabel').html('Update Static Location');
                }
            },
            error: function() {

            }
        });

    });

    $(document).on('click', '.submitSaveStaticDropoff', function(e) {
        // alert('af');
        // return false;
       // var static_dropoff_id = $("#add_static_dropoff_modal input[name=static_dropoff_id]").val();
        
        // if (static_dropoff_id) {
        //     var post_url = "{{ route('user.registration.document.update') }}";
        // } else {
            var post_url = "{{ route('static-dropoff.create') }}";
        ///}
        var form_data = new FormData(document.getElementById("staticDropoffForm"));
        $.ajax({
            url: post_url,
            method: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
               if (response.status == 'Success') {
                  $('#add_static_dropoff_modal').modal('hide');
                  $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                  setTimeout(function() {
                    StaticLocationDatatable()
                  }, 2000);
               } else {
                  $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
               }
            },
            error: function(response) {
               $('#add_static_dropoff_modal_btn .social_media_url_err').html('The default language name field is required.');
            }
        });
    });
    function initialize() {
        var input = document.getElementById('static-address');
        var autocomplete = new google.maps.places.Autocomplete(input);
        if(is_map_search_perticular_country){
                autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
            }
        autocomplete.bindTo('bounds', bindMap);

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            // console.log(place);
            document.getElementById('static_longitude').value = place.geometry.location.lng();
            document.getElementById('static_latitude').value = place.geometry.location.lat();
            document.getElementById('static_place_id').value = place.place_id;
            // for(let i=1; i < place.address_components.length; i++){
            //     let mapAddress = place.address_components[i];
            //     if(mapAddress.long_name !=''){
            //         let streetAddress = '';
            //         if (mapAddress.types[0] =="street_number") {
            //             streetAddress += mapAddress.long_name;
            //         }
            //         if (mapAddress.types[0] =="route") {
            //             streetAddress += mapAddress.short_name;
            //         }
            //         if($('#street').length > 0){
            //             document.getElementById('street').value = streetAddress;
            //         }
            //         if (mapAddress.types[0] =="locality") {
            //             document.getElementById('city').value = mapAddress.long_name;
            //         }
            //         if(mapAddress.types[0] =="administrative_area_level_1"){
            //             document.getElementById('state').value = mapAddress.long_name;
            //         }
            //         if(mapAddress.types[0] =="postal_code"){
            //             document.getElementById('pincode').value = mapAddress.long_name;
            //         }else{
            //             document.getElementById('pincode').value = '';
            //         }
            //         if(mapAddress.types[0] == "country"){
            //             var country = document.getElementById('country');
            //             for (let i = 0; i < country.options.length; i++) {
            //                 if (country.options[i].text.toUpperCase() == mapAddress.long_name.toUpperCase()) {
            //                     country.value = country.options[i].value;
            //                     break;
            //                 }
            //             }
            //         }
            //     }
            // }
        });

        // setTimeout(function(){
        //     $(".pac-container").appendTo("#add_new_address_form .address-input-group");
        // }, 300);

    }
    $(document).on('click', '.showMap', function() {
        var no = $(this).attr('num');
        var lats = document.getElementById('static_latitude').value;
        var lngs = document.getElementById('static_longitude').value;
        var address = document.getElementById('static-address').value;
        console.log(lats + '--' + lngs);


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
                        document.getElementById('place_id').value= results[0].place_id;
                    infowindow.setContent(results[0].formatted_address);

                    infowindow.open(map, marker);
                }
            }
            });
        });
        $('#add-customer-modal').addClass('fadeIn');
        $('#show-map-modal').modal({
            //backdrop: 'static',
            keyboard: false
        });

    });
    $(document).on('click', '.selectMapLocation', function() {

        var mapLat = document.getElementById('lat_map').value;
        var mapLlng = document.getElementById('lng_map').value;
        var mapFor = 'static';
        var address = document.getElementById('address_map').value;
        var place_id = document.getElementById('place_id').value;

        document.getElementById(mapFor + '_latitude').value = mapLat;
        document.getElementById(mapFor + '_longitude').value = mapLlng;
        document.getElementById(mapFor + '-address').value = address;
        document.getElementById(mapFor + '_place_id').value = place_id;

        $('#show-map-modal').modal('hide');
    });
    $(document).on("click",".delete-static_location",function() {
            var destroy_url = $(this).data('destroy_url');
            var id = $(this).data('rel');
            Swal.fire({
                title: "{{__('Are you sure?')}}",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if(result.value)
                {
                    $('form#deleteStaticLocation_'+id).submit();
                }
            });
        });
    
});
</script>