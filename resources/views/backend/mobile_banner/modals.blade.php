<div id="add-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add Mobile Banner") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_banner_form" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" id="AddCardBox">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light submitAddForm">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="edit-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Edit Mobile Banner") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_edit_banner_form" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editCardBox">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light submitEditForm">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="service-area-form" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add Service Area") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="geo_form" action="{{ route('banner.serviceArea') }}" method="POST">
                @csrf
                <div class="modal-body mt-0" id="editCardBox">
                    <input type="hidden" name="type" value="2" />
                    <input type="hidden" name="latlongs" value="" id="latlongs" />
                    <input type="hidden" name="zoom_level" value="13" id="zoom_level" />
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            {!! Form::label('title', __('Area Name'),['class' => 'control-label']) !!}
                            {!! Form::text('name', '',['class' => 'form-control', 'placeholder' => 'Area Name', 'required'=>'required']) !!}
                        </div>
                        <div class="col-lg-12 mb-2">
                            {!! Form::label('title', __('Area Description'),['class' => 'control-label']) !!}
                            {!! Form::textarea('description', '',['class' => 'form-control', 'rows' => '3', 'placeholder' => 'Area Description']) !!}
                        </div>
                        <div class="col-lg-12">
                            <div class="input-group mb-3">
                                <input type="text" id="pac-input" class="form-control" placeholder="Search by name" aria-label="Recipient's username" aria-describedby="button-addon2" name="loc_name">
                                <div class="input-group-append">
                                    <button class="btn btn-info" type="button" id="refresh">{{ __("Edit Mode") }}</button>
                                </div>
                            </div>
                            <div class="" style="height:96%;">
                                <div id="map-canvas" style="min-width: 300px; width:100%; height: 600px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- <div class="col-md-6">
                        <button type="button"
                            class="btn btn-block btn-outline-blue waves-effect waves-light">Cancel</button>
                    </div> -->

                    <div class="row">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-block btn-blue waves-effect waves-light w-100">{{ __("Save") }}</button>
                        </div>
                        <div class="col-md-6 p-0">
                        <input id="remove-line" class="btn btn-block btn-blue waves-effect waves-light w-100" type="button" value="Remove" />
                        </div>
                    </div>


                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-area-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Add Service Area') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>

            <form id="edit-area-form" action="" method="POST">
                @csrf
                <input type="hidden" name="type" value="2" />
                <div class="modal-body" id="editAreaBox">

                </div>

                <div class="modal-footer">
                    <div class="row mt-1">
                        <!-- <div class="col-md-6">
                            <button type="button"
                            class="btn btn-block btn-outline-blue waves-effect waves-light">Cancel</button>
                        </div> -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-block btn-blue waves-effect waves-light">{{ __("Save") }}</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>