@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Payment
Options']) @section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}"
	rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}"
	rel="stylesheet" type="text/css" />
<style>
#payment_48 {
	display: block;
}
</style>
@endsection @section('content')

<!-- Start Content-->
<div class="container-fluid alpaymentOptionPage">

	<!-- start page title -->
	<div class="row">
		<!-- <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Payment Options</h4>
            </div>
        </div> -->
		<div class="col-12">
			<div class="text-sm-left">
				@if (\Session::has('success'))
				<div class="alert mt-2 mb-0 alert-success">
					<span>{!! \Session::get('success') !!}</span>
				</div>
				@endif @if ( ($errors) && (count($errors) > 0) )
				<div class="alert mt-2 mb-0 alert-danger">
					<ul class="m-0">
						@foreach ($errors->all() as $error)
						<li>{{ $error }}</li> @endforeach
					</ul>
				</div>
				@endif
			</div>
		</div>
	</div>

    <form method="POST" id="payment_option_form" action="{{route('payoption.updateAll')}}"  enctype="multipart/form-data">
        @csrf
        @method('POST')
        <div class="row align-items-center">
            <div class="col-sm-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title">{{ __("Payment Options") }}</h4>
                    <button class="btn btn-info waves-effect waves-light save_btn" type="submit"> {{ __("Save") }}</button>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($payOption as $key => $opt)
            <div class="col-6 col-md-3 col-xl-2 mb-3" id="payment_{{$opt->id}}">

				<input type="hidden" name="method_id[]" id="{{$opt->id}}"
					value="{{$opt->id}}"> <input type="hidden" name="method_name[]"
					id="{{$opt->code}}" value="{{$opt->code}}">

                <?php
                $creds = json_decode($opt->credentials);
                $id = (isset($creds->id)) ? $creds->id : '';
                $easypaisa_store_id = (isset($creds->easypaisa_store_id)) ? $creds->easypaisa_store_id : '';
                $token = (isset($creds->token)) ? $creds->token : '';
                $cod_min_amount = (isset($creds->cod_min_amount)) ? $creds->cod_min_amount : '';
                $username = (isset($creds->username)) ? $creds->username : '';
                $password = (isset($creds->password)) ? $creds->password : '';
                $signature = (isset($creds->signature)) ? $creds->signature : '';
                $app_id = (isset($creds->app_id)) ? $creds->app_id : '';
                $api_key = (isset($creds->api_key)) ? $creds->api_key : '';
                $location_id = (isset($creds->location_id)) ? $creds->location_id : '';
                $application_id = (isset($creds->application_id)) ? $creds->application_id : '';
                $api_access_token = (isset($creds->api_access_token)) ? $creds->api_access_token : '';
                $api_secret_key = (isset($creds->api_secret_key)) ? $creds->api_secret_key : '';
                $publishable_key = (isset($creds->publishable_key)) ? $creds->publishable_key : '';
				$webhook_signature = (isset($creds->webhook_signature)) ? $creds->webhook_signature : '';
                $secret_key = (isset($creds->secret_key)) ? $creds->secret_key : '';
                $public_key = (isset($creds->public_key)) ? $creds->public_key : '';
                $private_key = (isset($creds->private_key)) ? $creds->private_key : '';
                $site_code = (isset($creds->site_code)) ? $creds->site_code : '';
                $merchant_id = (isset($creds->merchant_id)) ? $creds->merchant_id : '';
                $merchant_key = (isset($creds->merchant_key)) ? $creds->merchant_key : '';
                $passphrase = (isset($creds->passphrase)) ? $creds->passphrase : '';
                $merchant_account = (isset($creds->merchant_account)) ? $creds->merchant_account : '';
                $merchant_salt_v1 = (isset($creds->merchant_salt_v1)) ? $creds->merchant_salt_v1 : '';
                $merchant_salt_v2 = (isset($creds->merchant_salt_v2)) ? $creds->merchant_salt_v2 : '';
                $multiplier = (isset($creds->multiplier)) ? $creds->multiplier : '';
                $login_id = (isset($creds->login_id)) ? $creds->login_id : '';
                $profile_id = (isset($creds->profile_id)) ? $creds->profile_id : '';
                $transaction_key = (isset($creds->transaction_key)) ? $creds->transaction_key : '';
                $client_id = (isset($creds->client_id)) ? $creds->client_id : '';
                $client_key = (isset($creds->client_key)) ? $creds->client_key : '';
                $server_key = (isset($creds->server_key)) ? $creds->server_key : '';
                $mobile_client_key = (isset($creds->mobile_client_key)) ? $creds->mobile_client_key : '';
                $mobile_server_key = (isset($creds->mobile_server_key)) ? $creds->mobile_server_key : '';
                $access_code = (isset($creds->access_code)) ? $creds->access_code : '';
                $enc_key = (isset($creds->enc_key)) ? $creds->enc_key : '';
                $custom_url = (isset($creds->custom_url)) ? $creds->custom_url : '';
                $easypaisa_store_id = (isset($creds->easypaisa_store_id)) ? $creds->easypaisa_store_id : '';
                $toyyibpay_api_key = (isset($creds->toyyibpay_api_key)) ? $creds->toyyibpay_api_key : '';
                $toyyibpay_redirect_uri = (isset($creds->toyyibpay_redirect_uri)) ? $creds->toyyibpay_redirect_uri : '';
                $easebuzz_merchant_key = (isset($creds->easebuzz_merchant_key)) ? $creds->easebuzz_merchant_key : '';
                $easebuzz_salt = (isset($creds->easebuzz_salt)) ? $creds->easebuzz_salt : '';
                $easebuzz_Sub_merchant = (isset($creds->easebuzz_Sub_merchant)) ? $creds->easebuzz_Sub_merchant : '';
                $vnpay_website_id = (isset($creds->vnpay_website_id)) ? $creds->vnpay_website_id : '';
                $vnpay_server_key = (isset($creds->vnpay_server_key)) ? $creds->vnpay_server_key : '';
                $merchant_phone = (isset($creds->merchant_phone)) ? $creds->merchant_phone : '';
                $manule_payment_title = (isset($creds->manule_payment_title)) ? $creds->manule_payment_title : '';
                $userede_Rede_PV = (isset($creds->userede_Rede_PV)) ? $creds->userede_Rede_PV : '';
                $userede_Rede_token = (isset($creds->userede_Rede_token)) ? $creds->userede_Rede_token : '';
                $openpay_merchant_id = (isset($creds->openpay_merchant_id)) ? $creds->openpay_merchant_id : '';
                $openpay_private_key = (isset($creds->openpay_private_key)) ? $creds->openpay_private_key : '';
                $openpay_public_key = (isset($creds->openpay_public_key)) ? $creds->openpay_public_key : '';
                $openpay_verification_key = (isset($creds->openpay_verification_key)) ? $creds->openpay_verification_key : '';

                // azulpay
                $azul_main_url = (isset($creds->azul_main_url)) ? $creds->azul_main_url : '';
                $azul_alternate_url = (isset($creds->azul_alternate_url)) ? $creds->azul_alternate_url : '';
                $azul_test_url = (isset($creds->azul_test_url)) ? $creds->azul_test_url : '';
                $azul_ecommerce_url = (isset($creds->azul_ecommerce_url)) ? $creds->azul_ecommerce_url : '';
                $azul_merchant_id = (isset($creds->azul_merchant_id)) ? $creds->azul_merchant_id : '';
                $azul_auth_header_one = (isset($creds->azul_auth_header_one)) ? $creds->azul_auth_header_one : '';
                $azul_auth_header_two = (isset($creds->azul_auth_header_two)) ? $creds->azul_auth_header_two : '';
                $azul_ssl_certificate = (isset($creds->azul_ssl_certificate)) ? $creds->azul_ssl_certificate : '';
                $azul_ssl_key = (isset($creds->azul_ssl_key)) ? $creds->azul_ssl_key : '';

                // payway
                $payway_main_url = (isset($creds->payway_main_url)) ? $creds->payway_main_url : '';
                $payway_test_url = (isset($creds->payway_test_url)) ? $creds->payway_test_url : '';
                $payway_merchant_id = (isset($creds->payway_merchant_id)) ? $creds->payway_merchant_id : '';
                $payway_api_key = (isset($creds->payway_api_key)) ? $creds->payway_api_key : '';

                $company_token = (isset($creds->company_token)) ? $creds->company_token : '';
                $service_type = (isset($creds->service_type)) ? $creds->service_type : '';
                $aes_key = (isset($creds->aes_key)) ? $creds->aes_key : '';
                $uuid_key = (isset($creds->uuid_key)) ? $creds->uuid_key : '';
                $subscription_key = (isset($creds->subscription_key)) ? $creds->subscription_key : '';
                $reference_id = (isset($creds->reference_id)) ? $creds->reference_id : '';
                $mtn_api_key = (isset($creds->api_key)) ? $creds->api_key : '';
                $plugnpay_publisher_name = (isset($creds->plugnpay_publisher_name)) ? $creds->plugnpay_publisher_name : '';

				//skip cash
				$skip_cash_client_id = (isset($creds->skip_cash_client_id)) ? $creds->skip_cash_client_id : '';
				$skip_cash_api_secret = (isset($creds->skip_cash_api_secret)) ? $creds->skip_cash_api_secret : '';
				$skip_cash_key_id = (isset($creds->skip_cash_key_id)) ? $creds->skip_cash_key_id : '';
				$skip_cash_testing_url = (isset($creds->skip_cash_testing_url)) ? $creds->skip_cash_testing_url : '';
				$skip_cash_live_url = (isset($creds->skip_cash_live_url)) ? $creds->skip_cash_live_url : '';

				//Nmi
				$nmi_client_id = (isset($creds->nmi_client_id)) ? $creds->nmi_client_id : '';
				$nmi_key_id = (isset($creds->nmi_key_id)) ? $creds->nmi_key_id : '';

                // OBO Pay
                $obo_business_name     = (isset($creds->obo_business_name)) ? $creds->obo_business_name : '';
                $obo_client_id         = (isset($creds->obo_client_id)) ? $creds->obo_client_id : '';
                $obo_key_id            = (isset($creds->obo_key_id)) ? $creds->obo_key_id : ''  ;
                $obo_market_place_id   = (isset($creds->obo_market_place_id)) ? $creds->obo_market_place_id : '';
                $obo_company_reference = (isset($creds->obo_company_reference)) ? $creds->obo_company_reference : '';

                // Powertrans
				$powertrans_id = (isset($creds->powertrans_id)) ? $creds->powertrans_id : '';
				$powertrans_password = (isset($creds->powertrans_password)) ? $creds->powertrans_password : '';

                // Pesapal
				$pesapal_consumer_key = (isset($creds->pesapal_consumer_key)) ? $creds->pesapal_consumer_key : '';
				$pesapal_consumer_secret = (isset($creds->pesapal_consumer_secret)) ? $creds->pesapal_consumer_secret : '';

				// mpesa-safari
				$mpesasafari_consumer_key = (isset($creds->mpesasafari_consumer_key)) ? $creds->mpesasafari_consumer_key : '';
				$mpesasafari_consumer_secret = (isset($creds->mpesasafari_consumer_secret)) ? $creds->mpesasafari_consumer_secret : '';
				$mpesasafari_shortcode = (isset($creds->mpesasafari_shortcode)) ? $creds->mpesasafari_shortcode : '';
				$mpesasafari_webhook = (isset($creds->mpesasafari_webhook)) ? $creds->mpesasafari_webhook : '';

                $livee_merchant_key=(isset($creds->livee_merchant_key))?$creds->livee_merchant_key: '';
                  $livee_resource_key=(isset($creds->livee_resource_key))?$creds->livee_resource_key: '';
                //Totalpay
                $totalpay_MerchantId = (isset($creds->totalpay_MerchantId)) ? $creds->totalpay_MerchantId:'';
				$totalpay_password = (isset($creds->totalpay_password)) ? $creds->totalpay_password:'';
				//thawani payment Gateway
				$thawani_Apikey=(isset($creds->thawani_Apikey))?$creds->thawani_Apikey: '';
				$thawani_publishKey=(isset($creds->thawani_publishKey))?$creds->thawani_publishKey: '';


				//icici payment Gateway
				$icici_merchant_id = (isset($creds->icici_merchant_id)) ? $creds->icici_merchant_id : '';
				$icici_sub_merchant_id = (isset($creds->icici_sub_merchant_id)) ? $creds->icici_sub_merchant_id : '';
                $icici_merchant_name = (isset($creds->icici_merchant_name)) ? $creds->icici_merchant_name : '';
				$icici_sub_merchant_name = (isset($creds->icici_sub_merchant_name)) ? $creds->icici_sub_merchant_name : '';
				$icici_merchant_encryption_file = (isset($creds->icici_merchant_encryption_file)) ? $creds->icici_merchant_encryption_file : '';
				$icici_merchant_key_file = (isset($creds->icici_merchant_key_file)) ? $creds->icici_merchant_key_file : '';

                //mastercard payment gateway
                $mastercard_merchant_id = (isset($creds->mastercard_merchant_id)) ? $creds->mastercard_merchant_id : '';
                $mastercard_merchant_key = (isset($creds->mastercard_merchant_key)) ? $creds->mastercard_merchant_key : '';
                $mastercard_operator_id = (isset($creds->mastercard_operator_id)) ? $creds->mastercard_operator_id : '';
                $mastercard_gateway = (isset($creds->mastercard_gateway)) ? $creds->mastercard_gateway : '';
                //hitpay payment Gateway
                $hitpay_business_key =(isset($creds->hitpay_business_key))?$creds->hitpay_business_key:'';
                $hitpay_salt_key=(isset($creds->hitpay_salt_key))?$creds->hitpay_salt_key:'';
                //Orange pay payment Gateway
				$orangepay_MerchantKey= (isset($creds->orangepay_MerchantKey)) ? $creds->orangepay_MerchantKey:'';
				$orangepay_MerchantToken= (isset($creds->orangepay_MerchantToken)) ? $creds->orangepay_MerchantToken:'';
                //Cyber source payment Gateway
				$cyber_source_merchant_id= (isset($creds->cyber_source_merchant_id)) ? $creds->cyber_source_merchant_id:'';
				$cyber_source_profile_id= (isset($creds->cyber_source_profile_id)) ? $creds->cyber_source_profile_id:'';
				$cyber_source_access_key= (isset($creds->cyber_source_access_key)) ? $creds->cyber_source_access_key:'';
				$cyber_source_secret_key= (isset($creds->cyber_source_secret_key)) ? $creds->cyber_source_secret_key:'';
				$bill_to_address_line1= (isset($creds->bill_to_address_line1)) ? $creds->bill_to_address_line1:'';
				$bill_to_address_city= (isset($creds->bill_to_address_city)) ? $creds->bill_to_address_city:'';
				$bill_to_address_country= (isset($creds->bill_to_address_country)) ? $creds->bill_to_address_country:'';
				$bill_to_address_state= (isset($creds->bill_to_address_state)) ? $creds->bill_to_address_state:'';
				$bill_to_address_postal_code= (isset($creds->bill_to_address_postal_code)) ? $creds->bill_to_address_postal_code:'';

				if(strtolower($opt->code) == 'obo') {
					$opt->title = "O'Pay";
				}
                ?>

                <div class="card-box h-100 mb-0">
					<div class="d-flex align-items-center justify-content-between mb-2">
						<h4 class="header-title mb-0">
							<span class="alPaymentImage"
								style="height: 24px; width: 24px; display: inline-block;"> <img
								style="width: 100%;"
								src="{{asset('paymentsLogo/'.$opt->code.'.png')}}" alt=""></span>
							{{$opt->title}}
						</h4>
					</div>
					<div class="row">
						<div class="col-6">
							<div class="form-group mb-0 switchery-demo">
								<label for="" class="mr-0 d-block">{{ __("Enable") }}</label> <input
									type="checkbox" data-id="{{$opt->id}}"
									data-title="{{$opt->code}}" data-plugin="switchery"
									name="active[{{$opt->id}}]" class="chk_box all_select"
									data-color="#43bee1" @if($opt->status == 1) checked @endif>
							</div>
						</div>
                        @if ( (strtolower($opt->code) != 'dpo') && (strtolower($opt->code)
						!= 'cod') && (strtolower($opt->code) != 'razorpay') &&
						(strtolower($opt->code) != 'simplify') &&
						(strtolower($opt->code)!= 'kongapay') && (strtolower($opt->code)!=
						'windcave') && (strtolower($opt->code)!= 'payphone') &&
						(strtolower($opt->code)!= 'offline_manual') &&
						(strtolower($opt->code) != 'khalti'))
						<div class="col-6">
							<div class="form-group mb-0 switchery-demo">
								<label for="" class="mr-0 d-block">{{ __('Sandbox') }}</label> <input
									type="checkbox" data-id="{{$opt->id}}"
									data-title="{{$opt->code}}" data-plugin="switchery"
									name="sandbox[{{$opt->id}}]" class="chk_box sandbox-btn-{{$opt->code}}"
									data-color="#43bee1" @if($opt->test_mode == 1) checked @endif>
							</div>
						</div>
						@endif
					</div>

                    @if ((strtolower($opt->code) == 'mastercard'))
                    <div class="mt-2" id="mastercard_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="mastercard_merchant_id" class="mr-3">
                                        {{ __('Merchant ID') }}
                                    </label>
                                    <input type="text" name="mastercard_merchant_id" id="mastercard_merchant_id" class="form-control"
                                        value="{{ $mastercard_merchant_id }}" <?php if ($opt->status == 1) echo 'required' ?>>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="mastercard_merchant_key" class="mr-3">
                                        {{ __('Merchant Key') }}
                                    </label>
                                    <input type="password" name="mastercard_merchant_key" id="mastercard_merchant_key" class="form-control"
                                        value="{{ $mastercard_merchant_key }}" <?php if ($opt->status == 1) echo 'required' ?>>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="mastercard_operator_id" class="mr-3">
                                        {{ __('Operator ID') }}
                                    </label>

                                    <input type="text" name="mastercard_operator_id" id="mastercard_operator_id" class="form-control"
                                        value="{{ $mastercard_operator_id }}" <?php if ($opt->status == 1 && $opt->test_mode == 1) echo 'required' ?>>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="mastercard_gateway" class="mr-3">
                                        {{ __('Mastercard Gateway') }}
                                    </label>

                                    <input type="text" name="mastercard_gateway" id="mastercard_gateway" class="form-control"
                                        value="{{ $mastercard_gateway }}" <?php if ($opt->status == 1) echo 'required' ?>>
                                </div>
							</div>
						</div>
                    </div>
                    @endif @if ( (strtolower($opt->code) == 'cod') )
					<div class="mt-2" id="cod_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="cod_min_amount" class="mr-3">{{ __("Minimum Amount
										For Cod") }}</label> <input type="text" name="cod_min_amount"
										id="cod_min_amount" class="form-control"
										value="{{@$cod_min_amount}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>

						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'easypaisa') )
					<div class="mt-2" id="easypaisa_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="easypaisa_merchant" class="mr-3">{{ __("Store Id")
										}}</label> <input type="text" name="easypaisa_store_id"
										id="easypaisa_store_id" class="form-control"
										value="{{$easypaisa_store_id}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>

						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'viva_wallet') )
					<div class="mt-2" id="viva_wallet_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="viva_wallet_merchant" class="mr-3">{{ __("Merchant
										Id") }}</label> <input type="text"
										name="viva_wallet_merchant_id" id="viva_wallet_merchant_id"
										class="form-control" value="{{$merchant_id}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="viva_wallet_merchant" class="mr-3">{{
										__("Encryption Key") }}</label> <input type="text"
										name="viva_wallet_merchant_key" class="form-control"
										value="{{$merchant_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<h6 class="ml-2">
								<u>{{__('Smart Checkout Credentials')}}</u>
							</h6>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="viva_wallet_merchant" class="mr-3">{{ __("Client
										Id") }}</label> <input type="text"
										name="viva_wallet_client_id" class="form-control"
										value="{{$client_id}}" @if($opt->status == 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="viva_wallet_merchant" class="mr-3">{{ __("Client
										Key") }}</label> <input type="text"
										name="viva_wallet_client_key" class="form-control"
										value="{{$client_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>

						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'ccavenue') )
					<div class="mt-2" id="ccavenue_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="ccavenue_merchant" class="mr-3">{{ __("Merchant
										Id") }}</label> <input type="text" name="ccavenue_merchant_id"
										id="ccavenue_merchant_id" class="form-control"
										value="{{$merchant_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="ccavenue_access_code" class="mr-3">{{ __("Access
										Code") }}</label> <input type="text"
										name="ccavenue_access_code" id="ccavenue_access_code"
										class="form-control" value="{{$access_code}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="ccavenue_merchant" class="mr-3">{{ __("Encryption
										Key") }}</label> <input type="text" name="ccavenue_enc_key"
										id="ccavenue_enc_key" class="form-control"
										value="{{$enc_key}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<label>{{__('Custom Url')}}</label> <select class="form-control"
									name="custom_url" id="url">
									<option value="com">.com</option>

									<option value="ae" @if($custom_url== "ae")selected @endif>.ae</option>
								</select>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'kongapay') )
					<div class="mt-2" id="kongapay_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="kongapay_api_key" class="mr-3">{{ __("API Key") }}</label>
									<input type="text" name="kongapay_api_key"
										id="kongapay_api_key" class="form-control"
										value="{{$api_key}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="kongapay_merchant" class="mr-3">{{ __("Merchant
										Id") }}</label> <input type="text" name="kongapay_merchant_id"
										id="kongapay_merchant_id" class="form-control"
										value="{{$merchant_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'stripe') )
					<div class="mt-2" id="stripe_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_api_key" class="mr-3">{{ __("Secret Key") }}</label>
									<input type="password" name="stripe_api_key"
										id="stripe_api_key" class="form-control" value="{{$api_key}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_publishable_key" class="mr-3">{{
										__("Publishable Key") }}</label> <input type="password"
										name="stripe_publishable_key" id="stripe_publishable_key"
										class="form-control" value="{{$publishable_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_webhook_signature" class="mr-3">{{
										__("Webhook_signature") }}</label> <input type="password"
										name="stripe_webhook_signature" id="stripe_webhook_signature"
										class="form-control" value="{{$webhook_signature}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'stripe_fpx') )
					<div class="mt-2" id="stripe_fpx_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_fpx_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="stripe_fpx_secret_key" id="stripe_fpx_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_fpx_publishable_key" class="mr-3">{{
										__("Publishable Key") }}</label> <input type="password"
										name="stripe_fpx_publishable_key"
										id="stripe_fpx_publishable_key" class="form-control"
										value="{{$publishable_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'paypal') )
					<div class="mt-2" id="paypal_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paypal_username" class="mr-3">{{ __("Username") }}</label>
									<input type="textbox" name="paypal_username"
										id="paypal_username" class="form-control"
										value="{{$username}}" @if($opt->status == 1) value="" required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paypal_password" class="mr-3">{{ __("Password") }}</label>
									<input type="password" name="paypal_password"
										id="paypal_password" class="form-control"
										value="{{$password}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paypal_signature" class="mr-3">{{ __("Signature")
										}}</label> <input type="password" name="paypal_signature"
										id="paypal_signature" class="form-control"
										value="{{$signature}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'paystack') )
					<div class="mt-2" id="paystack_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paystack_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="paystack_secret_key" id="paystack_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paystack_public_key" class="mr-3">{{
										__("Publishable Key") }}</label> <input type="password"
										name="paystack_public_key" id="paystack_public_key"
										class="form-control" value="{{$public_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'payfast') )
					<div class="mt-2" id="payfast_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payfast_merchant_id" class="mr-3">{{ __("Merchant
										ID") }}</label> <input type="text" name="payfast_merchant_id"
										id="payfast_merchant_id" class="form-control"
										value="{{$merchant_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payfast_merchant_key" class="mr-3">{{ __("Merchant
										Key") }}</label> <input type="password"
										name="payfast_merchant_key" id="payfast_merchant_key"
										class="form-control" value="{{$merchant_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payfast_passphrase" class="mr-3">{{
										__("Passphrase") }}</label> <input type="text"
										name="payfast_passphrase" id="payfast_passphrase"
										class="form-control" value="{{$passphrase}}">
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'mobbex') )
					<div class="mt-2" id="mobbex_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mobbex_api_key" class="mr-3">{{ __("API Key") }}</label>
									<input type="text" name="mobbex_api_key" id="mobbex_api_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mobbex_api_access_token" class="mr-3">{{ __("API
										Access Token") }}</label> <input type="password"
										name="mobbex_api_access_token" id="mobbex_api_access_token"
										class="form-control" value="{{$api_access_token}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'yoco') )
					<div class="mt-2" id="yoco_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="yoco_secret_key" class="mr-3">{{ __("Secret Key")
										}}</label> <input type="password" name="yoco_secret_key"
										id="yoco_secret_key" class="form-control"
										value="{{$secret_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="yoco_public_key" class="mr-3">{{ __("Public Key")
										}}</label> <input type="password" name="yoco_public_key"
										id="yoco_public_key" class="form-control"
										value="{{$public_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'paylink') )
					<div class="mt-2" id="paylink_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paylink_api_key" class="mr-3">{{ __("API Key") }}</label>
									<input type="password" name="paylink_api_key"
										id="paylink_api_key" class="form-control" value="{{$api_key}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paylink_api_secret_key" class="mr-3">{{ __("API
										Secret Key") }}</label> <input type="password"
										name="paylink_api_secret_key" id="paylink_api_secret_key"
										class="form-control" value="{{$api_secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'razorpay') )
					<div class="2" id="razorpay_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="razorpay_api_key" class="mr-3">{{ __("API Key") }}</label>
									<input type="text" name="razorpay_api_key"
										id="razorpay_api_key" class="form-control"
										value="{{$api_key}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="razorpay_api_secret_key" class="mr-3">{{ __("API
										Secret Key") }}</label> <input type="text"
										name="razorpay_api_secret_key" id="razorpay_api_secret_key"
										class="form-control" value="{{$api_secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'gcash') )
					<div class="mt-2" id="gcash_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="gcash_public_key" class="mr-3">{{ __("Public Key")
										}}</label> <input type="text" name="gcash_public_key"
										id="gcash_public_key" class="form-control"
										value="{{$public_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'simplify') )
					<div class="mt-2" id="simplify_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="simplify_public_key" class="mr-3">{{ __("Public
										Key") }}</label> <input type="text" name="simplify_public_key"
										id="simplify_public_key" class="form-control"
										value="{{$public_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="simplify_private_key" class="mr-3">{{ __("Private
										Key") }}</label> <input type="password"
										name="simplify_private_key" id="simplify_private_key"
										class="form-control" value="{{$private_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'square') )
					<div class="mt-2" id="square_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="square_application_id" class="mr-3">{{
										__("Application ID") }}</label> <input type="text"
										name="square_application_id" id="square_application_id"
										class="form-control" value="{{$application_id}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="square_access_token" class="mr-3">{{ __("Access
										Token") }}</label> <input type="password"
										name="square_access_token" id="square_access_token"
										class="form-control" value="{{$api_access_token}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="square_location_id" class="mr-3">{{ __("Location
										ID") }}</label> <input type="text" name="square_location_id"
										id="square_location_id" class="form-control"
										value="{{$location_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'ozow') )
					<div class="mt-2" id="ozow_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="ozow_site_code" class="mr-3">{{ __("Site Code") }}</label>
									<input type="text" name="ozow_site_code" id="ozow_site_code"
										class="form-control" value="{{$site_code}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="ozow_private_key" class="mr-3">{{ __("Private Key")
										}}</label> <input type="password" name="ozow_private_key"
										id="ozow_private_key" class="form-control"
										value="{{$private_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="ozow_api_key" class="mr-3">{{ __("API Key") }}</label>
									<input type="text" name="ozow_api_key" id="ozow_api_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'pagarme') )
					<div class="mt-2" id="pagarme_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="pagarme_api_key" class="mr-3">{{ __("API Key") }}</label>
									<input type="text" name="pagarme_api_key" id="pagarme_api_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="pagarme_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="pagarme_secret_key" id="pagarme_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="pagarme_multiplier" class="mr-3">{{
										__("Multiplier") }}</label> <input type="number"
										name="pagarme_multiplier" id="pagarme_multiplier"
										class="form-control" value="{{$multiplier}}" step="0.01"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'checkout') )
					<div class="mt-2" id="checkout_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="checkout_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="checkout_secret_key" id="checkout_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="checkout_public_key" class="mr-3">{{ __("Public
										Key") }}</label> <input type="password"
										name="checkout_public_key" id="checkout_public_key"
										class="form-control" value="{{$public_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'authorize_net') )
					<div class="mt-2" id="authorize_net_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="authorize_net_login_id" class="mr-3">{{ __("Login
										ID") }}</label> <input type="text"
										name="authorize_net_login_id" id="authorize_net_login_id"
										class="form-control" value="{{$login_id}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="authorize_net_transaction_key" class="mr-3">{{
										__("Transaction Key") }}</label> <input type="password"
										name="authorize_net_transaction_key"
										id="authorize_net_transaction_key" class="form-control"
										value="{{$transaction_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="authorize_net_client_key" class="mr-3">{{
										__("Public Client Key") }}</label> <input type="text"
										name="authorize_net_client_key" id="authorize_net_client_key"
										class="form-control" value="{{$client_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'cashfree') )
					<div class="mt-2" id="cashfree_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="cashfree_app_id" class="mr-3">{{ __("App ID") }}</label>
									<input type="password" name="cashfree_app_id"
										id="cashfree_app_id" class="form-control" value="{{$app_id}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="cashfree_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="cashfree_secret_key" id="cashfree_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'toyyibpay') )
					<div class="mt-2" id="toyyibpay_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="toyyibpay_api_key" class="mr-3">{{ __("Secret Key")
										}}</label> <input type="password" name="toyyibpay_api_key"
										id="toyyibpay_api_key" class="form-control"
										value="{{$toyyibpay_api_key??''}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="toyyibpay_redirect_uri" class="mr-3">{{
										__("Redirect URL") }}</label> <input type="password"
										name="toyyibpay_redirect_uri" id="toyyibpay_redirect_uri"
										class="form-control" value="{{$toyyibpay_redirect_uri??''}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'easebuzz') )
					<div class="mt-2" id="easebuzz_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<h6 class="mt-3">
							<span>{{ __('Webhook Url') }} : </span> <a href="javascript:;"
								class="webhook_url"><span id="pwd_spn" class="password-span">{{route('payment.easebuzz.easybuzzNotify')}}</span></a>
						</h6>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="easebuzz_merchant_key" class="mr-3">{{ __("Sub
										Merchant") }}</label> <input type="checkbox"
										data-plugin="switchery" name="easebuzz_Sub_merchant"
										class="chk_box" data-color="#43bee1"
										@if($easebuzz_Sub_merchant== 1) checked @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="easebuzz_merchant_key" class="mr-3">{{ __("Merchant
										Key") }}</label> <input type="text"
										name="easebuzz_merchant_key" id="easebuzz_merchant_key"
										class="form-control" value="{{$easebuzz_merchant_key}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="easebuzz_salt" class="mr-3">{{ __("Salt") }}</label>
									<input type="text" name="easebuzz_salt" id="easebuzz_salt"
										class="form-control" value="{{$easebuzz_salt}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'paytab') )
					<div class="mt-2" id="paytab_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paytab_profile_id" class="mr-3">{{ __("Profile ID")
										}}</label> <input type="text" name="paytab_profile_id"
										id="paytab_profile_id" class="form-control"
										value="{{$profile_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paytab_server_key" class="mr-3">{{ __("Standard
										Server Key") }}</label> <input type="text"
										name="paytab_server_key" id="paytab_server_key"
										class="form-control" value="{{$server_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paytab_client_key" class="mr-3">{{ __("Standard
										Client Key") }}</label> <input type="text"
										name="paytab_client_key" id="paytab_client_key"
										class="form-control" value="{{$client_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paytab_mobile_server_key" class="mr-3">{{
										__("Mobile Server Key") }}</label> <input type="text"
										name="paytab_mobile_server_key" id="paytab_mobile_server_key"
										class="form-control" value="{{$mobile_server_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paytab_mobile_client_key" class="mr-3">{{
										__("Mobile Client Key") }}</label> <input type="text"
										name="paytab_mobile_client_key" id="paytab_mobile_client_key"
										class="form-control" value="{{$mobile_client_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'vnpay') )
					<div class="mt-2" id="vnpay_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="vnpay_website_id" class="mr-3">{{ __("Website ID")
										}}</label> <input type="text" name="vnpay_website_id"
										id="vnpay_website_id" class="form-control"
										value="{{$vnpay_website_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="vnpay_server_key" class="mr-3">{{ __("Server Key")
										}}</label> <input type="text" name="vnpay_server_key"
										id="vnpay_server_key" class="form-control"
										value="{{$vnpay_server_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>

						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'mvodafone') )
					<div class="mt-2" id="mvodafone_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mvodafone_client_id" class="mr-3">{{ __("Client
										ID") }}</label> <input type="text" name="mvodafone_client_id"
										id="mvodafone_client_id" class="form-control"
										value="{{$client_id}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mvodafone_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="mvodafone_secret_key" id="mvodafone_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'flutterwave') )
					<div class="mt-2" id="flutterwave_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="flutterwave_client_id" class="mr-3">{{ __("Public
										Key") }}</label> <input type="text"
										name="flutterwave_client_id" id="flutterwave_client_id"
										class="form-control" value="{{$client_id}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="flutterwave_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="flutterwave_secret_key" id="flutterwave_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="flutterwave_enc_key" class="mr-3">{{ __("Encryption
										Key") }}</label> <input type="password"
										name="flutterwave_enc_key" id="flutterwave_enc_key"
										class="form-control" value="{{$enc_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>

						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'payu') )
					<div class="mt-2" id="payu_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payu_merchant_key" class="mr-3">{{ __("Merchant
										Key") }}</label> <input type="text" name="payu_merchant_key"
										id="payu_merchant_key" class="form-control"
										value="{{$merchant_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payu_merchant_salt_v1" class="mr-3">{{ __("Merchant
										Salt V1") }}</label> <input type="password"
										name="payu_merchant_salt_v1" id="payu_merchant_salt_v1"
										class="form-control" value="{{$merchant_salt_v1}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="pay_merchant_salt_v2" class="mr-3">{{ __("Merchant
										Salt V2") }}</label> <input type="password"
										name="payu_merchant_salt_v2" id="payu_merchant_salt_v2"
										class="form-control" value="{{$merchant_salt_v2}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'braintree') )
					<div class="mt-2" id="braintree_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="braintree_merchant_id" class="mr-3">{{ __("Merchant
										ID") }}</label> <input type="text"
										name="braintree_merchant_id" id="braintree_merchant_id"
										class="form-control" value="{{$merchant_id}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="braintree_public_key" class="mr-3">{{ __("Public
										Key") }}</label> <input type="text"
										name="braintree_public_key" id="braintree_public_key"
										class="form-control" value="{{$public_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="braintree_private_key" class="mr-3">{{ __("Private
										Key") }}</label> <input type="text"
										name="braintree_private_key" id="braintree_private_key"
										class="form-control" value="{{$private_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'payphone'))
					<div class="mt-2" id="payphone_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payphone_id" class="mr-3">{{ __("ID") }}</label> <input
										type="text" name="payphone_id" id="payphone_id"
										class="form-control" value="{{$id}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payphone_client_id" class="mr-3">{{ __("Client ID")
										}}</label> <input type="text" name="payphone_client_id"
										id="payphone_client_id" class="form-control"
										value="{{$client_id}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payphone_token" class="mr-3">{{ __("Token") }}</label>
									<input type="text" name="payphone_token" id="payphone_token"
										class="form-control" value="{{$token}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'windcave'))
					<div class="mt-2" id="windcave_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="windcave_id" class="mr-3">{{ __("User ID") }}</label>
									<input type="text" name="windcave_id" id="windcave_id"
										class="form-control" value="{{$app_id}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="windcave_key" class="mr-3">{{ __("User Key") }}</label>
									<input type="text" name="windcave_key" id="windcave_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'paytech'))
					<div class="mt-2" id="paytech_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paytech_key" class="mr-3">{{ __("Api Key") }}</label>
									<input type="text" name="paytech_key" id="windcave_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="paytech_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="text" name="paytech_secret_key"
										id="paytech_secret_key" class="form-control"
										value="{{$secret_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'mycash'))
					<div class="mt-2" id="mycash_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mycash_api_key" class="mr-3">{{ __("Api Key") }}</label>
									<input type="text" name="mycash_api_key" id="mycash_api_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mycash_username" class="mr-3">{{ __("Username") }}</label>
									<input type="text" name="mycash_username" id="mycash_username"
										class="form-control" value="{{$username}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mycash_password" class="mr-3">{{ __("Password") }}</label>
									<input type="password" name="mycash_password"
										id="mycash_password" class="form-control"
										value="{{$password}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mycash_merchant_phone" class="mr-3">{{ __("Mobile
										Number") }}</label> <input type="text"
										name="mycash_merchant_phone" id="mycash_merchant_phone"
										class="form-control" value="{{$merchant_phone}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'stripe_oxxo') )
					<div class="mt-2" id="stripe_oxxo_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_oxxo_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="stripe_oxxo_secret_key" id="stripe_oxxo_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_oxxo_publishable_key" class="mr-3">{{
										__("Publishable Key") }}</label> <input type="password"
										name="stripe_oxxo_publishable_key"
										id="stripe_oxxo_publishable_key" class="form-control"
										value="{{$publishable_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'stripe_ideal') )
					<div class="mt-2" id="stripe_ideal_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_ideal_secret_key" class="mr-3">{{ __("Secret
										Key") }}</label> <input type="password"
										name="stripe_ideal_secret_key" id="stripe_ideal_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_ideal_publishable_key" class="mr-3">{{
										__("Publishable Key") }}</label> <input type="password"
										name="stripe_ideal_publishable_key"
										id="stripe_ideal_publishable_key" class="form-control"
										value="{{$publishable_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'offline_manual') )
					<div class="mt-2" id="offline_manual_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="manule_payment_title" class="mr-3">{{ __("Manual
										payment title") }}</label> <input type="text"
										name="manule_payment_title" id="manule_payment_title"
										class="form-control" value="{{$manule_payment_title}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'userede') )
					<div class="mt-2" id="userede_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="userede_Rede_PV" class="mr-3">{{ __("Rede PV") }}</label>
									<input type="text" name="userede_Rede_PV" id="userede_Rede_PV"
										class="form-control" value="{{$userede_Rede_PV}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="userede_Rede_token" class="mr-3">{{ __("Rede
										token") }}</label> <input type="text"
										name="userede_Rede_token" id="userede_Rede_token"
										class="form-control" value="{{$userede_Rede_token}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'openpay') )
					<div class="mt-2" id="openpay_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<h6 class="mt-3">
							<span>{{ __('Webhook Url') }} : </span> <a href="javascript:;"
								class="webhook_url"><span id="pwd_spn" class="password-span">{{route('payment.webhook.opnepay')}}</span></a>
						</h6>
						<div class="row">
							<div class="col-12">
								<div class="col-12">
									<div class="form-group mb-2">
										<label for="openpay_verification_key" class="mr-3">{{
											__("Webhook Verification") }}</label> <input type="text"
											name="openpay_verification_key" id="openpay_verification_key"
											class="form-control" value="{{$openpay_verification_key}}">
									</div>
								</div>

								<div class="form-group mb-2">
									<label for="openpay_merchant_id" class="mr-3">{{ __("Merchant
										Id") }}</label> <input type="text" name="openpay_merchant_id"
										id="openpay_merchant_id" class="form-control"
										value="{{$openpay_merchant_id}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="openpay_private_key" class="mr-3">{{ __("Private
										Key") }}</label> <input type="text" name="openpay_private_key"
										id="openpay_private_key" class="form-control"
										value="{{$openpay_private_key}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="openpay_public_key" class="mr-3">{{ __("Public
										Key") }}</label> <input type="text" name="openpay_public_key"
										id="openpay_public_key" class="form-control"
										value="{{$openpay_public_key}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>

						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'dpo') )
					<div class="mt-2" id="dpo_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="company_token" class="mr-3">{{ __("Company Token")
										}}</label> <input type="text" name="company_token"
										id="company_token" class="form-control"
										value="{{$company_token}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="service_type" class="mr-3">{{ __("Service Type") }}</label>
									<input type="text" name="service_type" id="service_type"
										class="form-control" value="{{$service_type}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>


						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'upay') )
					<div class="mt-2" id="upay_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="company_token" class="mr-3">{{ __("UIDD") }}</label>
									<input type="text" name="uuid_key" id="uuid_key"
										class="form-control" value="{{$uuid_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="service_type" class="mr-3">{{ __("AES Key") }}</label>
									<input type="text" name="aes_key" id="aes_key"
										class="form-control" value="{{$aes_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'conekta') )
					<div class="mt-2" id="conekta_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="company_token" class="mr-3">{{ __("Public Key") }}</label>
									<input type="text" name="conekta_public_key"
										id="conekta_public_key" class="form-control"
										value="{{$public_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="service_type" class="mr-3">{{ __("Private Key") }}</label>
									<input type="password" name="conekta_private_key"
										id="conekta_private_key" class="form-control"
										value="{{$private_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'telr') )
					<div class="mt-2" id="telr_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="telr_merchant_id" class="mr-3">{{ __("Store ID") }}</label>
									<input type="text" name="telr_merchant_id"
										id="telr_merchant_id" class="form-control"
										value="{{$merchant_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="telr_api_key" class="mr-3">{{ __("Api Key") }}</label>
									<input type="text" name="telr_api_key" id="telr_api_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'khalti') )
					<div class="mt-2" id="khalti_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="khalti_public_key" class="mr-3">{{ __("Public Key")
										}}</label> <input type="text" name="khalti_public_key"
										id="khalti_public_key" class="form-control"
										value="{{$api_key}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="khalti_secret_key" class="mr-3">{{ __("Secret Key")
										}}</label> <input type="password" name="khalti_secret_key"
										id="khalti_secret_key" class="form-control"
										value="{{$api_secret_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'mtn_momo') )
					<div class="mt-2" id="mtn_momo_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="company_token" class="mr-3">{{ __("Subscription
										Key") }}</label> <input type="text" name="subscription_key"
										id="subscription_key" class="form-control"
										value="{{$subscription_key}}" @if($opt->status == 1) required
									@endif>
									<p id="subscription_key_error"></p>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="reference_id" class="mr-3">{{ __("Reference Id") }}</label>
									<input type="text" name="reference_id" id="reference_id"
										class="form-control" value="{{$reference_id}}" @if($opt->status
									== 1) required @endif>
									<p id="reference_id_error"></p>
								</div>
							</div>

							<div
								class="col-12 @if(empty($mtn_api_key) && $opt->test_mode == 1) d-none @else d-block @endif"
								id="api_key_frm">
								<div class="form-group mb-2">
									<label for="reference_id" class="mr-3">{{ __("Api Key") }}</label>
									<input type="text" name="api_key" id="api_key"
										class="form-control" readonly value="{{$mtn_api_key}}"
										@if($opt->status == 1) required @endif>
									<p id="api_key_error"></p>
								</div>
							</div>

							<div
								class="form-group mb-2 mx-auto @if($opt->test_mode == 1) d-block @else d-none @endif">
								<a class="btn btn-primary" id="generate_mtn_momo_api_key"
									href="javascript:void(0)">Generate Api Key</a>
							</div>
							<div class="form-group mb-2 mx-auto" id="msg_status"></div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'plugnpay') )
					<div class="mt-2" id="plugnpay_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="plugnpay_publisher_name" class="mr-3">{{
										__("plugnpay Publisher Name") }}</label> <input type="text"
										name="plugnpay_publisher_name" id="plugnpay_publisher_name"
										class="form-control" value="{{$plugnpay_publisher_name}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'azul') )
					<div class="mt-2" id="azul_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_main_url" class="mr-3">{{ __("Main Url") }}</label>
									<input type="text" name="azul_main_url" id="azul_main_url"
										class="form-control" value="{{$azul_main_url}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_alternate_url" class="mr-3">{{ __("Alternate
										Url") }}</label> <input type="text" name="azul_alternate_url"
										id="azul_alternate_url" class="form-control"
										value="{{$azul_alternate_url}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_test_url" class="mr-3">{{ __("Test Url") }}</label>
									<input type="text" name="azul_test_url" id="azul_test_url"
										class="form-control" value="{{$azul_test_url}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_ecommerce_url" class="mr-3">{{ __("E-Commerce
										Url") }}</label> <input type="text" name="azul_ecommerce_url"
										id="azul_ecommerce_url" class="form-control"
										value="{{$azul_ecommerce_url}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_merchant_id" class="mr-3">{{ __("Merchant ID")
										}}</label> <input type="text" name="azul_merchant_id"
										id="azul_merchant_id" class="form-control"
										value="{{$azul_merchant_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_auth_header_one" class="mr-3">{{ __("Auth
										Header 1") }}</label> <input type="text"
										name="azul_auth_header_one" id="azul_auth_header_one"
										class="form-control" value="{{$azul_auth_header_one}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_auth_header_two" class="mr-3">{{ __("Auth
										Header 2") }}</label> <input type="text"
										name="azul_auth_header_two" id="azul_auth_header_two"
										class="form-control" value="{{$azul_auth_header_two}}"
										@if($opt->status == 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_ssl_certificate" class="mr-3">{{ __("SSL
										Certificate") }}</label> <input type="file"
										name="azul_ssl_certificate" id="azul_ssl_certificate"
										class="form-control" @if($azul_ssl_certificate== '' && ($opt->status
									== 1)) required @endif>
									<p class="font-weight-bold">{{@$azul_ssl_certificate}}</p>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="azul_ssl_key" class="mr-3">{{ __("SSL Key") }}</label>
									<input type="file" name="azul_ssl_key" id="azul_ssl_key"
										class="form-control" @if($azul_ssl_key== '' && ($opt->status
									== 1)) required @endif>
									<p class="font-weight-bold">{{@$azul_ssl_key}}</p>
								</div>
							</div>

						</div>
					</div>
					@endif
					@if ( (strtolower($opt->code) == 'payway') )
					<div class="mt-2" id="payway_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payway_main_url" class="mr-3">{{ __("Main Url") }}</label>
									<input type="text" name="payway_main_url" id="payway_main_url"
										class="form-control" value="{{$payway_main_url}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payway_test_url" class="mr-3">{{ __("Test Url") }}</label>
									<input type="text" name="payway_test_url" id="payway_test_url"
										class="form-control" value="{{$payway_test_url}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payway_merchant_id" class="mr-3">{{ __("Merchant
										ID") }}</label> <input type="text" name="payway_merchant_id"
										id="payway_merchant_id" class="form-control"
										value="{{$payway_merchant_id}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="payway_api_key" class="mr-3">{{ __("Api Key") }}</label>
									<input type="text" name="payway_api_key" id="payway_api_key"
										class="form-control" value="{{$payway_api_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

						</div>
					</div>
					@endif
					 @if ( (strtolower($opt->code) == 'skip_cash') )
                    <div class="mt-2" id="skip_cash_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="skip_cash_client_id" class="mr-3">{{ __("SKIPCASH CLIENT ID") }}</label>
                                    <input type="password" name="skip_cash_client_id" id="skip_cash_client_id" class="form-control" value="{{$skip_cash_client_id}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
							 <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="skip_cash_key_id" class="mr-3">{{ __("SKIPCASH KEY ID") }}</label>
                                    <input type="password" name="skip_cash_key_id" id="skip_cash_key_id" class="form-control" value="{{$skip_cash_key_id}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="skip_cash_api_secret" class="mr-3">{{ __("SKIPCASH_API_SECRET") }}</label>
                                    <input type="password" name="skip_cash_api_secret" id="skip_cash_api_secret" class="form-control" value="{{$skip_cash_api_secret}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
							 <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="skip_cash_test_url" class="mr-3">{{ __("Testing URL") }}</label>
                                    <input type="text" name="skip_cash_testing_url" id="skip_cash_testing_url" class="form-control" value="{{$skip_cash_testing_url}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
							 <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="skip_cash_live_url" class="mr-3">{{ __("SKIPCASH LIVE URL") }}</label>
                                    <input type="text" name="skip_cash_live_url" id="skip_cash_live_url" class="form-control" value="{{$skip_cash_live_url}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
					@if ( (strtolower($opt->code) == 'data_trans') )
                    <div class="mt-2" id="data_trans_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="data_trans_merchant_id" class="mr-3">{{ __("Merchant ID") }}</label>
                                    <input type="text" name="data_trans_merchant_id" id="data_trans_merchant_id" class="form-control" value="{{$merchant_id}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="data_trans_password" class="mr-3">{{ __("Password") }}</label>
                                    <input type="password" name="data_trans_password" id="data_trans_password" class="form-control" value="{{$password}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

					@if ( (strtolower($opt->code) == 'nmi') )
                    <div class="mt-2" id="nmi_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="nmi_client_id" class="mr-3">{{ __("NMI CLIENT ID") }}</label>
                                    <input type="password" name="nmi_client_id" id="nmi_client_id" class="form-control" value="{{$nmi_client_id}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
							 <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="nmi_key_id" class="mr-3">{{ __("NMI CLIENT KEY") }}</label>
                                    <input type="password" name="nmi_key_id" id="nmi_key_id" class="form-control" value="{{$nmi_key_id}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

{{-- Name changes --}}
					@if ( (strtolower($opt->code) == 'obo') )
						@php
							$gateway="O'Pay";
						@endphp
					@endif
                    @if ( (strtolower($opt->code) == 'obo') )
					@php
						$gateway="O'Pay";
					@endphp
                    <div class="mt-2" id="obo_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="obo_business_name" class="mr-3">{{$gateway}}{{ __(" BUSINESS NAME") }}</label>
                                    <input type="text" name="obo_business_name" id="obo_business_name" class="form-control" value="{{$obo_business_name}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="obo_client_id" class="mr-3">{{$gateway}}{{ __(" CLIENT ID") }}</label>
                                    <input type="number" name="obo_client_id" id="obo_client_id" class="form-control" value="{{$obo_client_id}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
							 <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="obo_key_id" class="mr-3">{{$gateway}}{{ __(" CLIENT KEY") }}</label>
                                    <input type="text" name="obo_key_id" id="obo_key_id" class="form-control" value="{{$obo_key_id}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="obo_market_place_id." class="mr-3">{{$gateway}}{{ __(" MARKET PLACE ID") }}</label>
                                    <input type="text" name="obo_market_place_id" id="obo_market_place_id" class="form-control" value="{{$obo_market_place_id}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="obo_company_reference" class="mr-3">{{ $gateway }}{{ __(" COMPANY REFERENCE ID") }}</label>
                                    <input type="text" name="obo_company_reference" id="obo_company_reference" class="form-control" value="{{ $obo_company_reference }}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
			    @if ( (strtolower($opt->code) == 'mpesasafari') )
					<div class="mt-2" id="mpesasafari_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mpesasafari_consumer_key" class="mr-3">{{ __("Consumer Key") }}</label>
									<input type="text" name="mpesasafari_consumer_key" id="mpesasafari_consumer_key"
										class="form-control" value="{{$mpesasafari_consumer_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mpesasafari_consumer_secret" class="mr-3">{{ __("Consumer Secret") }}</label>
									<input type="text" name="mpesasafari_consumer_secret" id="mpesasafari_consumer_secret"
										class="form-control" value="{{$mpesasafari_consumer_secret}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>

							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mpesasafari_shortcode" class="mr-3">{{ __("Shortcode") }}</label> <input type="text" name="mpesasafari_shortcode"
										id="mpesasafari_shortcode" class="form-control"
										value="{{$mpesasafari_shortcode}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="mpesasafari_webhook" class="mr-3">{{ __("Webhook Url") }}</label> <input type="text" name="mpesasafari_webhook"
										id="mpesasafari_webhook" class="form-control"
										value="{{$mpesasafari_webhook}}" @if($opt->status == 1)
									required @endif>
								</div>
							</div>

						</div>
					</div>
					@endif

					@if ( (strtolower($opt->code) == 'powertrans') )
                    <div class="mt-2" id="powertrans_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="powertrans_id" class="mr-3">{{ __("POWERTRANS ID") }}</label>
                                    <input type="password" name="powertrans_id" id="powertrans_id" class="form-control" value="{{$powertrans_id}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="powertrans_password" class="mr-3">{{ __("POWERTRANS PASSWORD") }}</label>
									<input type="password" name="powertrans_password" id="powertrans_password" class="form-control" value="{{$powertrans_password}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif

					@if ( (strtolower($opt->code) == 'pesapal') )
                    <div class="mt-2" id="pesapal_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="pesapal_consumer_key" class="mr-3">{{ __("PESAPAL CONSUMER KEY") }}</label>
                                    <input type="password" name="pesapal_consumer_key" id="pesapal_consumer_key" class="form-control" value="{{$pesapal_consumer_key}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
							 <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="pesapal_consumer_secret" class="mr-3">{{ __("PESAPAL CONSUMER SECRET") }}</label>
                                    <input type="password" name="pesapal_consumer_secret" id="pesapal_consumer_secret" class="form-control" value="{{$pesapal_consumer_secret}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif


					@if ( (strtolower($opt->code) == 'livee') )
                    <div class="mt-2" id="livee_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="livee_consumer_key" class="mr-3">{{ __("LIVEES MERCHANT KEY") }}</label>
                                    <input type="password" name="livee_merchant_key" id="livee_merchant_key" class="form-control" value="{{$livee_merchant_key}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
							 <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="livee_consumer_secret" class="mr-3">{{ __("LIVEES RESOURCE KEY") }}</label>
                                    <input type="password" name="livee_resource_key" id="livee_resource_key" class="form-control" value="{{$livee_resource_key}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if ( (strtolower($opt->code) == 'hitpay') )
                    <div class="mt-2" id="hitpay_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="hitpay_business_key" class="mr-3">{{ __("HITPAY BUSINESS KEY") }}</label>
                                    <input type="password" name="hitpay_business_key" id="hitpay_business_key" class="form-control" value="{{$hitpay_business_key}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
							 <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="hitpay_salt_key" class="mr-3">{{ __("HITPAY SALT KEY") }}</label>
                                    <input type="password" name="hitpay_salt_key" id="hitpay_salt_key" class="form-control" value="{{$hitpay_salt_key}}" @if($opt->status == 1) required @endif>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

					@if((strtolower($opt->code) == 'orange_pay'))
						<div class="mt-2" id="orange_pay_fields_wrapper" @if($opt->
							status != 1) style="display:none" @endif>
							<div class="row">
							<div class="col-12">
									<div class="form-group mb-2">
										<label for="orange_pay_MerchantKey" class="mr-3">{{ __("Merchant Key") }}</label>
										<input type="text" name="orangepay_MerchantKey" id="orange_pay_MerchantKey"
											class="form-control" value="{{$orangepay_MerchantKey}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="orange_pay_MerchantToken" class="mr-3">{{ __("Bearer Token") }}</label>
										<input type="text" name="orangepay_MerchantToken" id="orange_pay_MerchantToken"
											class="form-control" value="{{$orangepay_MerchantToken}}" @if($opt->status
										== 1) required @endif>
									</div>
								</div>
							</div>
						</div>
					@endif

					@if((strtolower($opt->code) == 'cyber_source'))
						<div class="mt-2" id="cyber_source_fields_wrapper" @if($opt->
							status != 1) style="display:none" @endif>
							<div class="row">
							<div class="col-12">
									<div class="form-group mb-2">
										<label for="cyber_source_merchant_id" class="mr-3">{{ __("Merchant Id") }}</label>
										<input type="text" name="cyber_source_merchant_id" id="cyber_source_merchant_id"
											class="form-control" value="{{$cyber_source_merchant_id}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="cyber_source_profile_id" class="mr-3">{{ __("Profile Id") }}</label>
										<input type="text" name="cyber_source_profile_id" id="cyber_source_profile_id"
											class="form-control" value="{{$cyber_source_profile_id}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="cyber_source_access_key" class="mr-3">{{ __("Access Key") }}</label>
										<input type="text" name="cyber_source_access_key" id="cyber_source_access_key"
											class="form-control" value="{{$cyber_source_access_key}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="cyber_source_secret_key" class="mr-3">{{ __("Secret Key") }}</label>
										<input type="password" name="cyber_source_secret_key" id="cyber_source_secret_key"
											class="form-control" value="{{$cyber_source_secret_key}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="bill_to_address_line1" class="mr-3">{{ __("Bill address") }}</label>
										<input type="text" name="bill_to_address_line1" id="bill_to_address_line1"
											class="form-control" value="{{$bill_to_address_line1}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="bill_to_address_city" class="mr-3">{{ __("Bill city") }}</label>
										<input type="text" name="bill_to_address_city" id="bill_to_address_city"
											class="form-control" value="{{$bill_to_address_city}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="bill_to_address_country" class="mr-3">{{ __("Bill country") }}</label>
										<input type="text" name="bill_to_address_country" id="bill_to_address_country"
											class="form-control" value="{{$bill_to_address_country}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="bill_to_address_state" class="mr-3">{{ __("Bill state") }}</label>
										<input type="text" name="bill_to_address_state" id="bill_to_address_state"
											class="form-control" value="{{$bill_to_address_state}}" @if($opt->status
										== 1) required @endif>
									</div>
									<div class="form-group mb-2">
										<label for="bill_to_address_postal_code" class="mr-3">{{ __("Bill zip code") }}</label>
										<input type="text" name="bill_to_address_postal_code" id="bill_to_address_postal_code"
											class="form-control" value="{{$bill_to_address_postal_code}}" @if($opt->status
										== 1) required @endif>
									</div>
								</div>
							</div>
						</div>
					@endif

                    @if ( (strtolower($opt->code) == 'totalpay') )
						<div class="mt-2" id="totalpay_fields_wrapper" @if($opt->
							status != 1) style="display:none" @endif>
							<div class="row">
							<div class="col-12">
									<div class="form-group mb-2">
										<label for="totalpay_MerchantId" class="mr-3">{{ __("Merchant ID") }}</label>
										<input type="text" name="totalpay_MerchantId" id="totalpay_MerchantId"
											class="form-control" value="{{$totalpay_MerchantId}}" @if($opt->status
										== 1) required @endif>
									</div>
								</div>

								<div class="col-12">
									<div class="form-group mb-2">
										<label for="totalpay_password" class="mr-3">{{ __("Password") }}</label>
										<input type="text" name="totalpay_password" id="totalpay_password"
											class="form-control" value="{{$totalpay_password}}" @if($opt->status
										== 1) required @endif>
									</div>
								</div>
							</div>
						</div>
                    @endif
					@if ((strtolower($opt->code) == 'thawani') )
							<div class="mt-2" id="thawani_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
								<div class="row">
									<div class="col-12">
										<div class="form-group mb-2">
											<label for="thawani_Apikey" class="mr-3">{{ __("Secret Key") }}</label>
												<input type="password" name="thawani_Apikey" id="thawani_Apikey" class="form-control" value="{{$thawani_Apikey}}" @if($opt->status == 1) required @endif>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-12">
										<div class="form-group mb-2">
											<label for="thawani_publishKey" class="mr-3">{{ __("Publishable Key") }}</label>
												<input type="password" name="thawani_publishKey" id="thawani_publishKey" class="form-control" value="{{$thawani_publishKey}}" @if($opt->status == 1) required @endif>
										</div>
									</div>
								</div>

							</div>
					@endif

					@if ((strtolower($opt->code) == 'icici') )
					<div class="mt-2" id="icici_fields_wrapper" @if($opt->status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="icici_merchant_id" class="mr-3">{{ __("Merchant Id")
										}}</label> <input type="text" name="icici_merchant_id"
										id="icici_merchant_id" class="form-control"
										value="{{$icici_merchant_id}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="icici_sub_merchant_id" class="mr-3">{{ __("Sub Merchant Id")
										}}</label> <input type="text" name="icici_sub_merchant_id"
										id="icici_sub_merchant_id" class="form-control"
										value="{{$icici_sub_merchant_id}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
                        <div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="icici_merchant_name" class="mr-3">{{ __("Merchant Name")
										}}</label> <input type="text" name="icici_merchant_name"
										id="icici_merchant_name" class="form-control"
										value="{{$icici_merchant_name}}" @if($opt->status == 1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="icici_sub_merchant_name" class="mr-3">{{ __("Sub Merchant Name")
										}}</label> <input type="text" name="icici_sub_merchant_name"
										id="icici_sub_merchant_name" class="form-control"
										value="{{$icici_sub_merchant_name}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>

                        <div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="icici_merchant_encryption_file" class="mr-3">{{ __("Merchant Encryption File")
										}}</label> <input type="text" name="icici_merchant_encryption_file"
										id="icici_merchant_encryption_file" class="form-control"
										value="{{$icici_merchant_encryption_file}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>

                        <div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="icici_merchant_key_file" class="mr-3">{{ __("Sub Merchant Key File")
										}}</label> <input type="text" name="icici_merchant_key_file"
										id="icici_merchant_key_file" class="form-control"
										value="{{$icici_merchant_key_file}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
					</div>
					@endif




				</div>
			</div>
			@endforeach
		</div>
	</form>
	@if(count($payoutOption) > 0)
	<form method="POST" id="payout_option_form"
		action="{{route('payoutOption.payoutUpdateAll')}}">
		@csrf @method('POST')
		<div class="row align-items-center">
			<div class="col-sm-12">
				<div
					class="page-title-box  d-flex align-items-center justify-content-between">
					<h4 class="page-title">{{ __("Payout Options") }}</h4>
					<button class="btn btn-info waves-effect waves-light save_btn"
						type="submit">{{ __("Save") }}</button>
				</div>
			</div>
		</div>
		<div class="row">
			@foreach($payoutOption as $key => $opt)

			<div class="col-6 col-md-3 col-xl-2 mb-3">

				<input type="hidden" name="method_id[]" id="{{$opt->id}}"
					value="{{$opt->id}}"> <input type="hidden" name="method_name[]"
					id="{{$opt->code}}" value="{{$opt->code}}">

                <?php
                $creds = json_decode($opt->credentials);
                $api_key = (isset($creds->api_key)) ? $creds->api_key : '';
                $secret_key = (isset($creds->secret_key)) ? $creds->secret_key : '';
                $multiplier = (isset($creds->multiplier)) ? $creds->multiplier : '';
                $publishable_key = (isset($creds->publishable_key)) ? $creds->publishable_key : '';
                $client_id = (isset($creds->client_id)) ? $creds->client_id : '';
				if(strtolower($opt->code) == 'obo') {
					$opt->title = "O'Pay";
				}
                ?>

                <div class="card-box h-100">
					<div class="d-flex align-items-center justify-content-between mb-2">
						<h4 class="header-title mb-0">
							<span class="alPaymentImage"
								style="height: 24px; width: 24px; display: inline-block;"> <img
								style="width: 100%;"
								src="{{asset('paymentsLogo/'.$opt->code.'.png')}}" alt=""></span>
							{{$opt->title}}
						</h4>
					</div>
					<div class="row">
						<div class="col-6">
							<div class="form-group mb-0 switchery-demo">
								<label for="" class="mr-0 d-block">{{ __("Enable") }}</label> <input
									type="checkbox" data-id="{{$opt->id}}"
									data-title="{{$opt->code}}" data-plugin="switchery"
									name="active[{{$opt->id}}]" class="chk_box payout_all_select"
									data-color="#43bee1" @if($opt->status == 1) checked @endif>
							</div>
						</div>
						@if ( (strtolower($opt->code) != 'cash') )
						<div class="col-6">
							<div class="form-group mb-0 switchery-demo">
								<label for="" class="mr-0 d-block">{{ __('Sandbox') }}</label> <input
									type="checkbox" data-id="{{$opt->id}}"
									data-title="{{$opt->code}}" data-plugin="switchery"
									name="sandbox[{{$opt->id}}]" class="chk_box"
									data-color="#43bee1" @if($opt->test_mode == 1) checked @endif>
							</div>
						</div>
						@endif
					</div>

					@if ( (strtolower($opt->code) == 'stripe') )
					<div class="mt-2" id="stripe_payout_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_payout_secret_key" class="mr-3">{{
										__("Secret Key") }}</label> <input type="password"
										name="stripe_payout_secret_key" id="stripe_payout_secret_key"
										class="form-control" value="{{$secret_key}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_payout_publishable_key" class="mr-3">{{
										__("Publishable Key") }}</label> <input type="password"
										name="stripe_payout_publishable_key"
										id="stripe_payout_publishable_key" class="form-control"
										value="{{$publishable_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="stripe_payout_client_id" class="mr-3">{{ __("Client
										ID") }}</label> <input type="password"
										name="stripe_payout_client_id" id="stripe_payout_client_id"
										class="form-control" value="{{$client_id}}" @if($opt->status
									== 1) required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'pagarme') )
					<div class="mt-2" id="pagarme_payout_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="pagarme_payout_api_key" class="mr-3">{{ __("API
										Key") }}</label> <input type="text"
										name="pagarme_payout_api_key" id="pagarme_payout_api_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="pagarme_payout_secret_key" class="mr-3">{{
										__("Secret Key") }}</label> <input type="password"
										name="pagarme_payout_secret_key"
										id="pagarme_payout_secret_key" class="form-control"
										value="{{$secret_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="pagarme_payout_multiplier" class="mr-3">{{
										__("Multiplier") }}</label> <input type="number"
										name="pagarme_payout_multiplier"
										id="pagarme_payout_multiplier" class="form-control"
										value="{{$multiplier}}" step="0.01" @if($opt->status == 1)
									required @endif>
								</div>
							</div>
						</div>
					</div>
					@endif @if ( (strtolower($opt->code) == 'razorpay') )

					<div class="2" id="razorpay_payout_fields_wrapper" @if($opt->
						status != 1) style="display:none" @endif>
						<div class="row">
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="razorpay_payout_api_key" class="mr-3">{{ __("API
										Key") }}</label> <input type="text"
										name="razorpay_payout_api_key" id="razorpay_payout_api_key"
										class="form-control" value="{{$api_key}}" @if($opt->status ==
									1) required @endif>
								</div>
							</div>
							<div class="col-12">
								<div class="form-group mb-2">
									<label for="razorpay_payout_secret_key" class="mr-3">{{ __("API
										Secret Key") }}</label> <input type="text"
										name="razorpay_payout_secret_key"
										id="razorpay_payout_secret_key" class="form-control"
										value="{{$secret_key}}" @if($opt->status == 1) required
									@endif>
								</div>
							</div>

							<h6 class="mt-3">
								<span>{{ __('Webhook Url') }} : </span> <a href="javascript:;"
									class="webhook_url"><span id="pwd_spn" class="password-span">{{route('payment.razorpay.payout.notify')}}</span></a>
							</h6>
						</div>
					</div>
					@endif





				</div>
			</div>
			@endforeach
		</div>
	</form>
	@endif

</div>

@endsection @section('script')
<script type="text/javascript">
    $('.applyVendor').click(function() {
        $('#applyVendorModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    $('.all_select').change(function() {
        var id = $(this).data('id');
        // console.log(id);
        var title = $(this).data('title');
        var code = title.toLowerCase();
        if ($(this).is(":checked")) {
            $("#" + code + "_fields_wrapper").show();
            $("#" + code + "_fields_wrapper").find('input').attr('required', true);
        } else {
            $("#" + code + "_fields_wrapper").hide();
            $("#" + code + "_fields_wrapper").find('input').removeAttr('required');
        }

        // if( title.toLowerCase() == 'stripe' ){
        //     if($(this).is(":checked")){
        //         $("#stripe_fields_wrapper").show();
        //         $("#stripe_fields_wrapper").find('input').attr('required', true);
        //     }
        //     else{
        //         $("#stripe_fields_wrapper").hide();
        //         $("#stripe_fields_wrapper").find('input').removeAttr('required');
        //     }
        // }
        // if( title.toLowerCase() == 'paypal' ){
        //     if($(this).is(":checked")){
        //         $("#paypal_fields_wrapper").show();
        //         $("#paypal_fields_wrapper").find('input').attr('required', true);
        //     }
        //     else{
        //         $("#paypal_fields_wrapper").hide();
        //         $("#paypal_fields_wrapper").find('input').removeAttr('required');
        //     }
        // }
        // if( title.toLowerCase() == 'paystack' ){
        //     if($(this).is(":checked")){
        //         $("#paystack_fields_wrapper").show();
        //         $("#paystack_fields_wrapper").find('input').attr('required', true);
        //     }
        //     else{
        //         $("#paystack_fields_wrapper").hide();
        //         $("#paystack_fields_wrapper").find('input').removeAttr('required');
        //     }
        // }
        // if( title.toLowerCase() == 'payfast' ){
        //     if($(this).is(":checked")){
        //         $("#payfast_fields_wrapper").show();
        //         $("#payfast_fields_wrapper").find('input').attr('required', true);
        //     }
        //     else{
        //         $("#payfast_fields_wrapper").hide();
        //         $("#payfast_fields_wrapper").find('input').removeAttr('required');
        //     }
        // }

        // $('#form_'+id).submit();

        //$('.vendorRow').toggle();
    });

    $('.payout_all_select').change(function() {
        var id = $(this).data('id');
        // console.log(id);
        var title = $(this).data('title');
        var code = title.toLowerCase();
        if ($(this).is(":checked")) {
            $("#" + code + "_payout_fields_wrapper").show();
            $("#" + code + "_payout_fields_wrapper").find('input').attr('required', true);
        } else {
            $("#" + code + "_payout_fields_wrapper").hide();
            $("#" + code + "_payout_fields_wrapper").find('input').removeAttr('required');
        }
    });



    $( "#mtn_momo_fields_wrapper" ).delegate( "#generate_mtn_momo_api_key", "click", function() {
    	if($("#api_key").val() != ''){
    		if(!confirm('Are you sure, you want to generate new API key? If yes, you might need to generate reference ID first. It is recommended to do this is in sandbox environment only.')){
    			return false;
    		}
    	}

    	let sandboxCheckbox = $(this).parents('#mtn_momo_fields_wrapper').siblings('.row').children().eq(1).find('input[type="checkbox"]').is(":checked");
        var subscription_key    = $("#subscription_key").val();
        var reference_id        = $("#reference_id").val();
        if(subscription_key == ''){
            $("#subscription_key_error").empty();
            $("#reference_id_error").empty();
            $("#subscription_key_error").html('<p>Please enter subscription key</p>');
        }else if(reference_id == ''){
            $("#subscription_key_error").empty();
            $("#reference_id_error").empty();
            $("#reference_id_error").html('<p>Please enter reference id key</p>');
        }else{
            $("#subscription_key_error").empty();
            $("#reference_id_error").empty();
            //console.log('OK');

            $.ajax({
               type:'POST',
               url:"{{ route('payoption.mtn_momo_api_key') }}",
               data: {'_token': "{{ csrf_token() }}",'subscription_key':subscription_key,'reference_id':reference_id,'sandboxCheckbox':sandboxCheckbox},
               success:function(respones) {
                  var obj = jQuery.parseJSON(respones);
                  //console.log(obj.status);
                  if(obj.status == 201){
                    $("#api_key_frm").removeClass('d-none').addClass('d-block');
                    $("#api_key").val(obj.api_key);
                    $("#msg_status").empty();
                    $("#msg_status").html('<p class="text-success">'+obj.message+'</p>');
                  }else if(obj.status == 409){
                    $("#api_key_frm").removeClass('d-block').addClass('d-none');
                    $("#api_key").val('');
                    $("#msg_status").empty();
                    $("#msg_status").html('<p class="text-danger">'+obj.message+'</p>');
                  }else if(obj.status == 400){
                    $("#api_key_frm").removeClass('d-block').addClass('d-none');
                    $("#api_key").val('');
                    $("#msg_status").empty();
                    $("#msg_status").html('<p class="text-danger">'+obj.message+'</p>');
                  }else if(obj.status == 500){
                    $("#api_key_frm").removeClass('d-block').addClass('d-none');
                    $("#api_key").val('');
                    $("#msg_status").empty();
                    $("#msg_status").html('<p class="text-danger">'+obj.message+'</p>');
                  }
                  else if(obj.status == 404){
                    $("#api_key_frm").removeClass('d-block').addClass('d-none');
                    $("#api_key").val('');
                    $("#msg_status").empty();
                    $("#msg_status").html('<p class="text-danger">'+obj.message+'</p>');
                  }
               }
            });
        }
    });

    //For MTN Momo
    $(document).on('change', '.sandbox-btn-mtn_momo', function(e){
    	let subscriptionKey = $("#subscription_key").val();
    	let referenceId = $("#reference_id").val();
    	let apiKey = $("#api_key").val();
		$('#payment_48').find('input[type="text"]').val('')
		if(!$(this).is(":checked")){
			$('#payment_48 #api_key').attr('readonly',false)
			$("#generate_mtn_momo_api_key").parent().removeClass('d-block').addClass('d-none');
    	}else{
			$('#payment_48 #api_key').attr('readonly',true)
    		$("#generate_mtn_momo_api_key").parent().removeClass('d-none').addClass('d-block');
    	}
    });

</script>
@endsection
