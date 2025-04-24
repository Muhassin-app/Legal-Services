@if($payout_option == 'pagarme')
<div class="modal-header py-3 px-3 border-bottom-0 d-block">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h5 class="modal-title" id="modal-title">{{ __('Create Pagarme Account') }}</h5>
</div>
<div class="modal-body px-3 pb-3 pt-0">
    <form id="pagarme_account_form" method="post">
        @csrf
        <div>
            <input type="hidden" name="vendor" value="{{$vendor}}">
        </div>
        <div class="row mb-2">
            {{-- <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Select Bank)") }}</label>
                    <select class="form-control" name="pagarme_bank" id="pagarme_bank" required >
                        @foreach($banks_list as $bank)
                            <option value="{{$bank->bank_code}}">{{$bank->legal_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div> --}}
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Bank Code") }} *</label>
                    <input class="form-control" maxlength="3" placeholder="{{ __("Bank Code") }}" type="text" name="bank_code" value="" id="bank_code" required />
                    <span class="invalid-feedback" role="alert" id="bank_code_err"></span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Agency") }} *</label>
                    <input class="form-control" maxlength="5" placeholder="{{ __("Agency") }}" type="text" name="agencia" value="" id="agencia" required />
                    <span class="invalid-feedback" role="alert" id="agencia_err"></span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Agency Check Digit") }}</label>
                    <input class="form-control" maxlength="1" placeholder="{{ __("Agency Check Digit") }}" type="text" name="agencia_dv" id="agencia_dv" />
                    <span class="invalid-feedback" role="alert" id="agencia_dv_err"></span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Account Number") }} *</label>
                    <input class="form-control" maxlength="13" placeholder="{{ __("Account Number") }}" type="text" name="conta" value="" id="conta" required />
                    <span class="invalid-feedback" role="alert" id="conta_err"></span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Account Verification Digit") }} *</label>
                    <input class="form-control" maxlength="2" placeholder="{{ __("Account Verification Digit") }}" type="text" name="conta_dv" value="" id="conta_dv" required />
                    <span class="invalid-feedback" role="alert" id="conta_dv_err"></span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Account CPF or CNPJ") }} *</label>
                    <input class="form-control" placeholder="{{ __("Account CPF or CNPJ") }}" type="text" name="document_number" value="" id="document_number" required />
                    <span class="invalid-feedback" role="alert" id="document_number_err"></span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Full Name or Business Name") }} *</label>
                    <input class="form-control" maxlength="30" placeholder="{{ __("Full Name or Business Name") }}" type="text" name="legal_name" value="" id="legal_name" required />
                    <span class="invalid-feedback" role="alert" id="legal_name_err"></span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ __("Account Type") }} *</label>
                    <select class="form-control" name="type" id="type" required >
                        <option value="conta_corrente" selected>{{ __('Current Account') }}</option>
                        <option value="conta_poupanca">{{ __('Savings Account') }}</option>
                        <option value="conta_corrente_conjunta">{{ __('Joint Current Account') }}</option>
                        <option value="conta_poupanca_conjunta">{{ __('Joint Savings Account') }}</option>
                    </select>
                    <span class="invalid-feedback" role="alert" id="type_err"></span>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12 d-sm-flex justify-content-between">
                <button type="button" class="btn btn-info" id="pagarme_create_account">{{ __('Create') }}</button>
                <button type="button" class="btn btn-info" data-dismiss="modal">{{ __('Cancel') }}</button>
            </div>
        </div>
    </form>
</div>
@endif
