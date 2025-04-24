<div id="add-role-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Add Role') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="add_role" method="post" action="{{ route('save.roles') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input name="id" class="role-id" type="hidden" />
                        <div class="col-md-12">
                            <div class="form-group" id="nameInput">
                                {!! Form::label('title', __('Role'), ['class' => 'control-label']) !!}
                                {!! Form::text('role_name', null, ['class' => 'form-control role-name', 'required' => 'required']) !!}
                                <span class="invalid-feedback" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info waves-effect waves-light submitAddSubscriptionForm">{{ __('Submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
