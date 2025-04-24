<script type="text/javascript">
    var all_coordinates = @json($all_coordinates);
    var areajson_json = all_coordinates; //{all_coordinates};

    function initialize_show() {

        // var myLatlng = new google.maps.LatLng("{{ $center['lat'] }}","{{ $center['lng']  }}");
        //console.log(myLatlng);
        var latitude  =  all_coordinates[0].coordinates['0']['lat'];
        var longitude =  all_coordinates[0].coordinates['0']['lng'];
        var myOptions = {
            zoom: parseInt(10),
            center: {
                lat: latitude,
                lng: longitude
            },
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        var map = new google.maps.Map(document.getElementById("show_map-canvas"), myOptions);
        const marker = new google.maps.Marker({
            map: map,
            position: {
                lat: latitude,
                lng: longitude
            },
        });

        var length = areajson_json.length;

        //console.log(length);
        for (var i = 0; i < length; i++) {

            data = areajson_json[i];

            var infowindow = new google.maps.InfoWindow();
            var no_parking_geofences_json_geo_area = new google.maps.Polygon({
                paths: data.coordinates,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#ff0000',
                fillOpacity: 0.35,
                geo_name: data.name,
                geo_pos: data.coordinates[i],
            });

            no_parking_geofences_json_geo_area.setMap(map);

        }
    }

    /*          SERVICE     AREA        */

    var iw = new google.maps.InfoWindow(); // Global declaration of the infowindow
    var lat_longs = new Array();
    var markers = new Array();
    var drawingManager;
    var _myPolygon;
    var no_parking_geofences_json = all_coordinates; //{all_coordinates};
    var newlocation = '<?php echo json_encode($co_ordinates); ?>';
    var first_location = JSON.parse(newlocation);
    var lat = parseFloat(first_location.lat);
    var lng = parseFloat(first_location.lng);

    function deleteSelectedShape() {
        drawingManager.setMap(null);
    }

    function initialize() {

        var myLatlng = new google.maps.LatLng(lat, lng);
        var myOptions = {
            zoom: 13,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        const input = document.getElementById("pac-input");
        const searchBox = new google.maps.places.SearchBox(input);
        //map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        // Bias the SearchBox results towards current map's viewport.

        var map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
        const marker = new google.maps.Marker({
            map: map,
            position: {
                lat: lat,
                lng: lng
            },
        });

        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: [google.maps.drawing.OverlayType.POLYGON]
            },
            polygonOptions: {
                editable: true,
                draggable: true,
                strokeColor: '#bb3733',
                fillColor: '#bb3733',
            }
        });

        drawingManager.setMap(map);

        google.maps.event.addListener(drawingManager, "overlaycomplete", function(event) {
            var newShape = event.overlay;
            newShape.type = event.type;
        });

        google.maps.event.addListener(drawingManager, "overlaycomplete", function(event) {
            overlayClickListener(event.overlay);
            var vertices_val = $('#latlongs').val();
            //var vertices_val = event.overlay.getPath().getArray();
            if (vertices_val == null || vertices_val === '') {
                $('#latlongs').val(event.overlay.getPath().getArray());
                // console.log(map.getZoom());
                $('#zoom_level').val(map.getZoom());
            } else {
                alert('You can draw only one zone at a time');
                event.overlay.setMap(null);
            }
            _myPolygon = event.overlay;
        });

        $('#remove-line').on('click', function() {
            $('#latlongs').val('');
            _myPolygon.setMap(null);

        });

        searchBox.addListener("places_changed", () => {
            const places = searchBox.getPlaces();
            if (places.length == 0) {
                return;
            }
            // Clear out the old markers.
            markers.forEach((marker) => {
                marker.setMap(null);
            });
            markers = [];
            // For each place, get the icon, name and location.
            const bounds = new google.maps.LatLngBounds();
            places.forEach((place) => {
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }
                const icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25),
                };
                // Create a marker for each place.
                markers.push(
                    new google.maps.Marker({
                        map,
                        icon,
                        title: place.name,
                        position: place.geometry.location,
                    })
                );

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            });
            map.fitBounds(bounds);
        });
    }

    function overlayClickListener(overlay) {
        google.maps.event.addListener(overlay, "mouseup", function(event) {
            $('#latlongs').val(overlay.getPath().getArray());
        });
    }

    $("#geo_form").on("submit", function(e) {
        var lat = $('#latlongs').val();
        var trainindIdArray = lat.replace("[", "").replace("]", "").split(',');
        var length = trainindIdArray.length;

        if (length < 6) {
            Swal.fire(
                'Select Location?',
                'Please Draw a Location On Map first',
                'question'
            )
            e.preventDefault();
        }
    });

    /*                  EDIT       AREA        MODAL           */
    var CSRF_TOKEN = $("input[name=_token]").val();
    $(document).on('click', '.editAreaBtn', function() {
        var aid = $(this).attr('area_id');
        var editUrl = "{{route('banner.serviceArea.edit', ':id')}}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            dataType: "json",
            url: editUrl.replace(':id', aid),
            data: {
                _token: CSRF_TOKEN
            },
            success: function(data) {

                document.getElementById("edit-area-form").action = "{{url('client/banner/updateArea')}}" + '/' + aid;
                $('#edit-area-form #editAreaBox').html(data.html);
                initialize_edit(data.zoomLevel, data.coordinate);
                $('#edit-area-modal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            }
        });
    });

    var Editmap; // Global declaration of the map
    function initialize_edit(zoomLevel = 0, coordinates = '') {
        var zoomLevel = zoomLevel;
        var coordinate = coordinates;
        if (coordinate != '') {
            coordinate = coordinate.split('(');
            coordinate = coordinate.join('[');
            coordinate = coordinate.split(')');
            coordinate = coordinate.join(']');
            coordinate = "[" + coordinate;
            coordinate = coordinate + "]";
            coordinate = JSON.parse(coordinate);

            var triangleCoords = [];
            const lat1 = coordinate[0][0];
            const long1 = coordinate[0][1];

            var max_x = lat1;
            var min_x = lat1;
            var max_y = long1;
            var min_y = long1;

            $.each(coordinate, function(key, value) {

                if (value[0] > max_x) {
                    max_x = value[0];
                }
                if (value[0] < min_x) {
                    min_x = value[0];
                }
                if (value[1] > max_y) {
                    max_y = value[1];
                }
                if (value[1] < min_y) {
                    min_y = value[1];
                }

                triangleCoords.push(new google.maps.LatLng(value[0], value[1]));
            });

            var myLatlng = new google.maps.LatLng((min_x + ((max_x - min_x) / 2)), (min_y + ((max_y - min_y) / 2)));
            var myOptions = {
                zoom: parseInt(zoomLevel),
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            Editmap = new google.maps.Map(document.getElementById("edit_map-canvas"), myOptions);
            myPolygon = new google.maps.Polygon({
                paths: triangleCoords,
                draggable: true, // turn off if it gets annoying
                editable: true,
                strokeColor: '#424fsd',
                //strokeOpacity: 0.8,
                //strokeWeight: 2,
                fillColor: '#bb3733',
                //fillOpacity: 0.35
            });

            myPolygon.setMap(Editmap);

            google.maps.event.addListener(myPolygon, "mouseup", function(event) {
                $('#zoom_level_edit').val(Editmap.getZoom());
                document.getElementById("latlongs_edit").value = myPolygon.getPath().getArray();
            });
        }
    }
    if ( is_hyperlocal ) {
        google.maps.event.addDomListener(window, 'load', initialize);
        google.maps.event.addDomListener(window, 'load', initialize_show);
        google.maps.event.addDomListener(window, 'load', initialize_edit);
        google.maps.event.addDomListener(document.getElementById('refresh'), 'click', deleteSelectedShape);
    }

    $('.openServiceModal').click(function() {
        $('#service-area-form').modal({
            keyboard: false
        });
    });
</script>