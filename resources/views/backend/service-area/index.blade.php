@extends('layouts.vertical', ['title' => 'Service Area'])

@section('css')
    <link href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/dropify/dropify.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Service Area</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-sm-8">
                                <div class="text-sm-left">
                                    @if (\Session::has('success'))
                                        <div class="alert alert-success">
                                            <span>{!! \Session::get('success') !!}</span>
                                        </div>
                                    @endif
                                    @if (\Session::has('error_delete'))
                                        <div class="alert alert-danger">
                                            <span>{!! \Session::get('error_delete') !!}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-4 text-right">
                                <button class="btn btn-info openServiceModal"> {{ __('Add Service Area') }}</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-striped" id="products-datatable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($areas as $geo)
                                        <tr>
                                            <td class="table-user">
                                                <a href="javascript:void(0);" class="text-body">{{ $geo->name }}</a>
                                            </td>

                                            <td>
                                                @if ($client_preference_detail->slots_with_service_area == 1 && isset($vendor) && $vendor->show_slot == 0)
                                                    <input type="checkbox" data-plugin="switchery"
                                                        name="is_active_for_vendor_slot"
                                                        class="form-control is_active_for_vendor_slot" data-color="#43bee1"
                                                        data-aid="{{ $geo->id }}"
                                                        @if ($geo->is_active_for_vendor_slot == 1) checked @endif
                                                        {{ $vendor->cron_for_service_area == 1 ? 'disabled' : '' }}>
                                                @endif

                                                <button type="button"
                                                    class="btn btn-primary-outline action-icon editAreaBtn"
                                                    area_id="{{ $geo->id }}"><i
                                                        class="mdi mdi-square-edit-outline"></i></button>

                                                <form action="{{ route('admin.serviceArea.delete') }}" method="POST"
                                                    class="action-icon">
                                                    @csrf
                                                    <input type="hidden" value="{{ $geo->id }}" name="area_id">
                                                    <button type="submit"
                                                        onclick="return confirm('Are you sure? You want to delete the service area.')"
                                                        class="btn btn-primary-outline action-icon"><i
                                                            class="mdi mdi-delete"></i></button>

                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No areas found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination pagination-rounded justify-content-end mb-0">
                            {{-- $banners->links() --}}
                        </div>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
    </div>
    <div id="service-area-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add Service Area') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="geo_form" action="{{ route('admin.serviceArea') }}" method="POST">
                    @csrf
                    <div class="modal-body mt-0" id="editCardBox">
                        <input type="hidden" name="latlongs" value="" id="latlongs" />
                        <input type="hidden" name="zoom_level" value="13" id="zoom_level" />
                        <input type="hidden" name="country_code" value="" id="country_code" />
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                {!! Form::label('title', __('Area Name'), ['class' => 'control-label']) !!}
                                {!! Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Area Name', 'required' => 'required']) !!}
                            </div>
                            <div class="col-lg-12 mb-2">
                                {!! Form::label('title', __('Area Description'), ['class' => 'control-label']) !!}
                                {!! Form::textarea('description', '', [
                                    'class' => 'form-control',
                                    'rows' => '3',
                                    'placeholder' => 'Area Description',
                                ]) !!}
                            </div>
                            <div class="col-lg-12">
                                <div class="input-group mb-3">
                                    <input type="text" id="pac-input" class="form-control" placeholder="Search by name"
                                        aria-label="Recipient's username" aria-describedby="button-addon2" name="loc_name">
                                    <div class="input-group-append">
                                        <button class="btn btn-info" type="button"
                                            id="refresh">{{ __('Edit Mode') }}</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <select class="form-control al_box_height" id="primary_language" name="primary_language" required>
                                        <option value=""> Primary Language </option>
                                        @foreach($languages as $language)
                                            <option value="{{$language->id}}">{{$language->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <select class="form-control al_box_height" id="primary_currency" name="primary_currency" required>
                                        <option value=""> Primary Currency </option>
                                        @foreach($currencies as $currencie)
                                            <option value="{{$currencie->id}}"> {{$currencie->iso_code.' '.$currencie->symbol}} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="" style="height:96%;">
                                    <div id="map-canvas" style="min-width: 300px; width:100%; height: 600px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit"
                                    class="btn btn-block btn-blue waves-effect waves-light w-100">{{ __('Save') }}</button>
                            </div>
                            <div class="col-md-6 p-0">
                                <input id="remove-line" class="btn btn-block btn-blue waves-effect waves-light w-100"
                                    type="button" value="Remove" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="edit-area-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title">{{ __('Add Service Area') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form id="edit-area-form" action="" method="POST">
                    @csrf
                    <div class="modal-body" id="editAreaBox">
                        
                    </div>
                    <div class="modal-footer">
                        <div class="row mt-1">
                            <div class="col-12">
                                <button type="submit"
                                    class="btn btn-block btn-blue waves-effect waves-light">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
        var all_coordinates = @json($all_coordinates);
        var areajson_json = all_coordinates; //{all_coordinates};
        function initialize_show() {
            var latitude = all_coordinates[0].coordinates['0']['lat'];
            var longitude = all_coordinates[0].coordinates['0']['lng'];
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

                    // Retrieve ISO 2 country code
                    const countryCode = place.address_components.find((component) =>
                    component.types.includes("country")
                    )?.short_name;
                    $('#country_code').val(countryCode);

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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "post",
                dataType: "json",
                url: "{{ route('admin.serviceArea.edit') }}",
                data: {
                    _token: CSRF_TOKEN,
                    data: aid
                },
                success: function(data) {
                    document.getElementById("edit-area-form").action =
                        "{{ url('client/admin/updateArea') }}" + '/' + aid;
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
        
        google.maps.event.addDomListener(window, 'load', initialize);
        google.maps.event.addDomListener(window, 'load', initialize_show);
        google.maps.event.addDomListener(window, 'load', initialize_edit);
        google.maps.event.addDomListener(document.getElementById('refresh'), 'click', deleteSelectedShape);
        
    </script>
    <script type="text/javascript">
        $('.openServiceModal').click(function() {
            $('#service-area-form').modal({
                keyboard: false
            });
        });
    </script>
@endsection
