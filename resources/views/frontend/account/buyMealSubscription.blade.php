@extends('layouts.store', ['title' => 'Subscription']) @section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />

@endsection @section('content') @php $timezone = Auth::user()->timezone;
$now = \Carbon\Carbon::now()->toDateString(); $after7days =
\Carbon\Carbon::now()->addDays(7)->toDateString(); @endphp

<style type="text/css">
	.invalid-feedback {
		display: block;
	}

	ul li {
		margin: 0 0 10px;
		color: #6c757d;
	}

	.main-menu .brand-logo {
		display: inline-block;
		padding-top: 20px;
		padding-bottom: 20px;
	}

	i {
		margin-right: 10px;
	}

	/*------------------------*/
	input:focus,
	button:focus,
	.form-control:focus {
		outline: none;
		box-shadow: none;
	}

	.form-control:disabled,
	.form-control[readonly] {
		background-color: #fff;
	}

	/*----------step-wizard------------*/
	.d-flex {
		display: flex;
	}

	.justify-content-center {
		justify-content: center;
	}

	.align-items-center {
		align-items: center;
	}

	/*---------signup-step-------------*/
	.bg-color {
		background-color: #333;
	}

	.signup-step-container {
		padding: 150px 0px;
		padding-bottom: 60px;
	}

	.wizard .nav-tabs {
		position: relative;
		margin-bottom: 0;
		border-bottom-color: transparent;
	}

	.wizard>div.wizard-inner {
		position: relative;
		margin-bottom: 50px;
		text-align: center;
	}

	.connecting-line {
		height: 2px;
		background: #e0e0e0;
		position: absolute;
		width: 75%;
		margin: 0 auto;
		left: 0;
		right: 0;
		top: 15px;
		z-index: 1;
	}

	.wizard .nav-tabs>li.active>a,
	.wizard .nav-tabs>li.active>a:hover,
	.wizard .nav-tabs>li.active>a:focus {
		color: #555555;
		cursor: default;
		border: 0;
		border-bottom-color: transparent;
	}

	span.round-tab {
		width: 30px;
		height: 30px;
		line-height: 30px;
		display: inline-block;
		border-radius: 50%;
		background: #fff;
		z-index: 2;
		position: absolute;
		left: 0;
		text-align: center;
		font-size: 16px;
		color: #0e214b;
		font-weight: 500;
		border: 1px solid #ddd;
	}

	span.round-tab i {
		color: #555555;
	}

	.wizard li.active span.round-tab {
		background: #bd3414;
		color: #fff;
		border-color: #bd3414;
	}

	.wizard li.active span.round-tab i {
		color: #5bc0de;
	}

	.wizard .nav-tabs>li.active>a i {
		color: #bd3414;
	}

	.wizard .nav-tabs>li {
		width: 25%;
	}

	.wizard li:after {
		content: " ";
		position: absolute;
		left: 46%;
		opacity: 0;
		margin: 0 auto;
		bottom: 0px;
		border: 5px solid transparent;
		border-bottom-color: red;
		transition: 0.1s ease-in-out;
	}

	.wizard .nav-tabs>li a {
		width: 30px;
		height: 30px;
		margin: 20px auto;
		border-radius: 100%;
		padding: 0;
		background-color: transparent;
		position: relative;
		top: 0;
	}

	.subscription-detail-box {
		width: 85%;
		margin: 0 auto;
		padding-top: 10px;
	}

	.wizard .nav-tabs>li a i {
		position: absolute;
		top: -15px;
		font-style: normal;
		font-weight: 400;
		white-space: nowrap;
		left: 50%;
		transform: translate(-50%, -50%);
		font-size: 12px;
		font-weight: 700;
		color: #000;
	}

	.wizard .nav-tabs>li a:hover {
		background: transparent;
	}

	.wizard .tab-pane {
		position: relative;
		padding-top: 20px;
	}

	.wizard h3 {
		margin-top: 0;
	}

	.prev-step,
	.next-step {
		font-size: 13px;
		padding: 8px 24px;
		border: none;
		border-radius: 4px;
		margin-top: 30px;
	}

	.next-step {
		background-color: #bd3414;
	}

	.skip-btn {
		background-color: #cec12d;
	}

	.step-head {
		font-size: 20px;
		text-align: center;
		font-weight: 500;
		margin-bottom: 20px;
	}

	.term-check {
		font-size: 14px;
		font-weight: 400;
	}

	.custom-file {
		position: relative;
		display: inline-block;
		width: 100%;
		height: 40px;
		margin-bottom: 0;
	}

	.custom-file-input {
		position: relative;
		z-index: 2;
		width: 100%;
		height: 40px;
		margin: 0;
		opacity: 0;
	}

	.custom-file-label {
		position: absolute;
		top: 0;
		right: 0;
		left: 0;
		z-index: 1;
		height: 40px;
		padding: .375rem .75rem;
		font-weight: 400;
		line-height: 2;
		color: #495057;
		background-color: #fff;
		border: 1px solid #ced4da;
		border-radius: .25rem;
	}

	.custom-file-label::after {
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		z-index: 3;
		display: block;
		height: 38px;
		padding: .375rem .75rem;
		line-height: 2;
		color: #495057;
		content: "Browse";
		background-color: #e9ecef;
		border-left: inherit;
		border-radius: 0 .25rem .25rem 0;
	}

	.order-btn .btn-plce {
		width: 100%;
		text-align: right;
	}

	.order-btn .btn-plce button.btn-solid {
		padding: 12px 20px !important;
		font-size: 12px !important;
		letter-spacing: 0px;
	}

	.footer-link {
		margin-top: 30px;
	}

	.all-info-container {}

	.list-content {
		margin-bottom: 10px;
	}

	.list-content a {
		padding: 10px 15px;
		width: 100%;
		display: inline-block;
		background-color: #f5f5f5;
		position: relative;
		color: #565656;
		font-weight: 400;
		border-radius: 4px;
	}

	.list-content a[aria-expanded="true"] i {
		transform: rotate(180deg);
	}

	.list-content a i {
		text-align: right;
		position: absolute;
		top: 15px;
		right: 10px;
		transition: 0.5s;
	}

	.form-control[disabled],
	.form-control[readonly],
	fieldset[disabled] .form-control {
		background-color: #fdfdfd;
	}

	.list-box {
		padding: 10px;
	}

	.signup-logo-header .logo_area {
		width: 200px;
	}

	.signup-logo-header .nav>li {
		padding: 0;
	}

	.signup-logo-header .header-flex {
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.list-inline li {
		display: inline-block;
	}

	.pull-right {
		float: right;
	}

	/*-----------custom-checkbox-----------*/
	/*----------Custom-Checkbox---------*/
	input[type="checkbox"] {
		position: relative;
		display: inline-block;
		margin-right: 5px;
	}

	input[type="checkbox"]::before,
	input[type="checkbox"]::after {
		position: absolute;
		content: "";
		display: inline-block;
	}

	input[type="checkbox"]::before {
		height: 14px;
		width: 14px;
		border: 1px solid #999;
		left: 0px;
		top: 0px;
		background-color: #fff;
		border-radius: 2px;
	}

	input[type="checkbox"]::after {
		height: 5px;
		width: 9px;
		left: 4px;
		top: 4px;
	}

	input[type="checkbox"]:checked::after {
		content:unset;
		border-left: 1px solid #fff;
		border-bottom: 1px solid #fff;
		transform: rotate(-45deg);
	}

	input[type="checkbox"]:checked::before {
		background-color: var(--theme-default);
	}

	.wizard h1 {
		font-size: 24px;
		margin-bottom: 10px;
	}

	.wizard h5 {
		font-size: 14px;
		margin: 0px;
		padding: 6px 0px;
		letter-spacing: 0px;
		font-weight: 500;
		width: 200px;
	}

	.right-heading h4 {
		font-size: 22px;
		font-weight: 500;
		position: relative;
		letter-spacing: 0px;
	}

	.wizard {
		background-color: #ffffff;
		padding: 20px 20px !important;
	}

	.wizard-inner-item {
		border: 1px solid #f9f9f9;
		box-shadow: 6px 13px 30px #1615152b;
	}
	
.al_body_template_one .btn-solid{
	font-size: 14px !important;
	letter-spacing: 0.2px;
	padding: 6px 8px !important;
}
	.form-check-label,
	.meal-sub-field label {
		margin-bottom: 0px;
		font-size: 14px;
		letter-spacing: 0px;
	}
	.wizard-inner-item table.table tr td strong{
		font-size: 14px;
    margin: 0px;
    padding: 6px 0px;
    letter-spacing: 0px;
    font-weight: 500;
    width: 200px;
    color: #222;
    line-height: 24px;
	}

	.product-list-box .product-image h6 {
		display: inline-block;
		font-size: 14px;
	}

	.product-list-box .product-image span.img_active img {
		width: 20%;
		height: auto;
	}

	.product-list-box .product-image {
		margin: 10px;
	}

	.product-list-box .product-image {
		margin: 10px;
		border-radius: 4px;
		border: 1px solid #999;
	}

	.product-list-box {
		background-color: #fff;
		box-shadow: 6px 13px 30px #1615152b;
		padding: 20px 20px !important;
		max-height: 637px;
		height: 100%;
		min-height: 637px;
		overflow: auto;
	}

	.discount-row input {
		width: 262px;
		margin: 0px 10px;
	}

	.select2-selection__rendered li {
		margin-bottom: 0px;
	}


	select, option {
    width: 250px;
}

option {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}


	select {
    width: 200px; /* Set the desired width */
    height: 40px; /* Set the desired height */
    padding: 8px; /* Adjust padding as needed */
    font-size: 16px; /* Adjust font size as needed */
    /* Add any additional styles here */
  }
  .summary .order-detail p strong {
    font-size: 14px;
    margin: 0px;
    padding: 6px 0px;
    letter-spacing: 0px;
    font-weight: 500;
    width: 200px;
	color: #222;
    line-height: 24px;
}
.summary .order-detail p{
	font-size: 14px;
    letter-spacing: 0px;
}
.wizard-inner-item h4{
	font-size: 22px;
    font-weight: 500;
    position: relative;
    letter-spacing: 0px;
}
.summary .order-detail p span {
    width: 620px;
    display: inline-block;
}
.order-detail h4 {
    font-size: 22px;
    font-weight: 500;
    position: relative;
    letter-spacing: 0px;
}
.list-inline .prev-step, .next-step {
		font-size: 14px !important;
    letter-spacing: 0.2px !important;
}
	@media (max-width : 767px) {
		.sign-content h3 {
			font-size: 40px;
		}

		.wizard .nav-tabs>li a i {
			display: none;
		}

		.signup-logo-header .navbar-toggle {
			margin: 0;
			margin-top: 8px;
		}

		.signup-logo-header .logo_area {
			margin-top: 0;
		}

		.signup-logo-header .header-flex {
			display: block;
		}
	}
</style>

<section class="signup-step-container py-4">
	<div class="container">
		<div class="wizard mb-3">
			<div class="wizard-inner d-none">
				<div class="connecting-line"></div>
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="active"><a href="#step1" data-toggle="tab" aria-controls="step1" role="tab" aria-expanded="true"><span class="round-tab">1 </span> <i>Step 1</i></a></li>
					<li role="presentation" class="disabled"><a href="#step2" data-toggle="tab" aria-controls="step2" role="tab" aria-expanded="false"><span class="round-tab">2</span> <i>Step 2</i></a></li>
				</ul>
			</div>
			<form action="{{route('user.subscription.plan.purchase', $subscription_plan->slug)}}" method="POST" id="meal-subscription-form">
				@csrf
				<div class="tab-content pt-0 mt-0" id="main_form">
					<div class="tab-pane active pt-0" role="tabpanel" id="step1">
						<div class="row">
							<div class="col-lg-8">
								<div class="right-heading">
									<h4 class="pb-3">Subscribe to {{ucfirst($subscription_plan->title)}}</h4>
								</div>
								<div class="wizard-inner-item p-2">
									<div class="subscription-detail-box">
										<div class="form-group d-flex">
											<h5>Meal Plan :</h5>
											<div class="form-check form-check-inline ml-3">
												<input class="form-check-input" type="radio" name="meal_plan" value="basic" disabled checked> <label class="form-check-label">{{ucfirst($subscription_plan->title)}}</label>
												
											</div>
										</div>

										<div class="form-group d-flex">
											<h5>Order Limit :</h5>
											<div class="form-check form-check-inline ml-3">
												<label class="form-check-label">{{$subscription_plan->order_limit}}</label>
												<input type="hidden" id="sub-amount" name="amount" value="{{$subscription_plan->price}}" />
											</div>
										</div>
										<div class="form-group d-flex">
											<h5>Subscription Validity :</h5>
											<div class="form-check form-check-inline ml-3">
												<label class="form-check-label">{{ucfirst($subscription_plan->frequency)}}</label>
											</div>
										</div>


										<div class="form-group d-flex">
											<h5>Select Delivery Method :</h5>
											<div class="form-check form-check-inline ml-3">
												<input class="form-check-input" type="radio" name="delivery_method" value="pickup" id="pickup-type" required> <label for="pickup-type" class="form-check-label">Pickup</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input" type="radio" name="delivery_method" value="delivery" id="delivery-type">
												<label for="delivery-type" class="form-check-label">Delivery</label>
											</div>
										</div>


										<div class="form-group">
											<div class="col-md-12 meal-sub-field pl-0">
												@foreach($additionalAttributes as $attribute)
												@if(isset($attribute->primary) &&
												!empty($attribute->primary))
												<div class="form-group d-flex" id="{{$attribute->primary->slug??''}}Input">
													<h5>{{$attribute->title}} :</h5>
													@foreach($attribute->option as $options) @php $translation =
													$options->trans; @endphp
													<div class="form-check form-check-inline ml-3">
														<input type="radio" name="{{$attribute->primary->slug}}" class="intpCheck form-check-input" id="opt_ven_{{$translation->id}}" value="{{$translation->title}}" required> <label for="opt_ven_{{$translation->id}}">{{$translation->title}}</label>
													</div>
													@endforeach
												</div>
												@endif @endforeach

											</div>
										</div>

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<h5>Subscription Start Date :</h5>
													<input type="datetime-local" class="form-control" name="subscription_start_date" min="{{date('Y-m-d H:i', strtotime('+1 day'))}}" required>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<h5>Select Number of Days :</h5>
													<select class="form-control select2-multiple category_features" name="week_days[]" data-toggle="select2" multiple="multiple" data-placeholder="Select days...." id="week-days" required>
														@foreach($subscription_plan->days() as $k => $day)
														<option value="{{$k}}"> {{$day}}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-md-12 address-box" style="display:none;">
												<div class="form-group">
													<h5>Select Address :</h5>
													<select class="form-control" name="address_id" id="address-id" required>
														<option value="">Select Address</option>
														@foreach($addresses as $k => $address)
														<option value="{{$address->id}}">{{ ($address->house_number
																?? false) ? $address->house_number."," : '' }}
															{{$address->address}}, {{$address->state}}
															{{$address->pincode}}
														</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<h5>Delivery Instructions :</h5>
													<textarea class="form-control" name="delivery_instructions" rows="4"></textarea>
												</div>
											</div>
										</div>
										<div class="col-md-6 d-none">
											<div class="form-group ">
												<h5>Discount and Offer Code :</h5>
												<div class="discount-row d-flex">
													<input type="text" class="form-control" name="discount_code">
													<button type="button" class="btn-solid" name="apply_discount">Apply</button>
												</div>
											</div>
										</div>
										<div class="row order-btn">
											<div class="col btn-plce">
												<button type="button" class="btn-solid next-step place-order-btn">Next</button>
											</div>
										</div>
									</div>

								</div>
							</div>
							<div class="col-lg-4">
								<div class="right-heading">
									<h4 class="pb-3">Package Products List</h4>
								</div>
								<div class="product-list mb-4">
									<div class="product-list-box">
										@foreach($subPlanCategory->get() as $category)
										@if(!empty($category->products->all()))
										@foreach($category->products->all() as $product)
										<div class="product-image">

											@if(!empty($product->media) && count($product->media) > 0)
											@php $image = $product->media()->first();
											if(isset($image->pimage)){ $img = $image->pimage->image;
											}else{ $img = $image->image; } @endphp @if(!is_null($img)) <span class="img_active"> <img class="blur-up lazyloaded pro_imgs myimage1" data-src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}" width="60" height="60" src="{{@$img->path['image_fit'].'1000/1000'.@$img->path['image_path']}}">
											</span> @endif @else <span class="img_active"> <img class="blur-up lazyloaded pro_imgs myimage1" data-src="{{loadDefaultImage()}}" width="60" height="60" src="{{loadDefaultImage()}}">
											</span> @endif
											<h6>{{$product->title}}</h6>
										</div>
										@endforeach @endif @endforeach
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane" role="tabpanel" id="step2">
						<div class="subscription-summary ">
							<div class="row">
								<div class="col-md-8">
									<div class="summary wizard-inner-item p-3">
										<p class="sub-start-box">
											<span id="sub-start"></span>
										</p>
										<div class="order-detail">
											<h4>Subscription Detail</h4>
											<hr />
											<p class="sub-plan">
												<strong class="d-inline-block mr-1">Subscription Plan : </strong>
												{{ucfirst($subscription_plan->title)}}
											</p>
											<p class="days">
												<strong class="d-inline-block mr-1">Subscription Start : </strong>
												<span id="sub-start-date"></span>
											</p>
											<p class="days">
												<strong class="d-inline-block mr-1">Subscription Package : </strong>
												<span id="sub-package"></span>
											</p>
											<p class="days">
												<strong class="d-inline-block mr-1">Delivery Method : </strong>
												<span id="sub-delivery"></span>
											</p>
											<p class="days">
												<strong class="d-inline-block mr-1">Days : </strong> <span id="sub-days"></span>
											</p>
											<p class="Validity">
												<strong class="d-inline-block mr-1">Validity : </strong> <span id="sub-validity">{{ucfirst($subscription_plan->frequency)}}</span>
											</p>
											<p class="address">
												<strong class="d-inline-block mr-1">Delivery Address : </strong> <span id="sub-address"></span>
											</p>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="summary wizard-inner-item p-3">
										<h4>Payment Summary</h4>
										<hr>
										<table class="table">
											<tbody>
												<tr>
													<td class="border-0"><strong>Amount:</strong></td>
													<td class="text-right border-0" id="sub_total">{{$clientCurrency->currency->symbol}} 0</td>
												</tr>
												<tr>
													<td><strong>Package&nbsp;Discount:</strong></td>
													<td class="text-right"><span id="discount">{{$clientCurrency->currency->symbol}}
															0</span> <span id="special_discount" style="color: green; font-weight: 600;"></span></td>
												</tr>
												<tr id="cpdsc" style="display: none;">
													<td><strong>Coupon&nbsp;Discount:</strong></td>
													<td class="text-right" id="coupon_discount">
														{{$clientCurrency->currency->symbol}} 0.00
													</td>
												</tr>
												<tr>
													<td><strong>Total Amount:</strong></td>
													<td class="text-right" id="total">{{$clientCurrency->currency->symbol}}
														0.00</td>
												</tr>

												<tr id="is_credit_card" style="display:;">
													<td><strong>Auto Renew:</strong></td>
													<td class="text-right"><label class="label"> <input type="checkbox" name="autorenew" id="is_auto_renew" checked="">

														</label></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
						<ul class="list-inline pull-right">
							<li><button type="button" class="default-btn prev-step">Back</button></li>
							<li><button type="button" class="default-btn btn-solid subscribe_btn" data-id="{{$subscription_plan->slug}}">Subscribe </button></li>
						</ul>
					</div>

				</div>
			</form>
		</div>
	</div>
</section>
<div class="modal fade" id="cancel-subscription" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="cancel_subscriptionLabel">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header pb-0">
				<h5 class="modal-title" id="cancel_subscriptionLabel">{{
					__('Unsubscribe') }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">Ãƒâ€”</span>
				</button>
			</div>
			<form id="cancel-subscription-form" method="POST" action="">
				@csrf
				<div class="modal-body">
					<h6 class="m-0">{{ __('Do you really want to cancel this
						subscription ?') }}</h6>
				</div>
				<div class="modal-footer flex-nowrap justify-content-center align-items-center">
					<button type="submit" class="btn btn-solid">
						{{ __('Continue') }}</a>
						<button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{ __('Cancel') }}</button>

				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="error_response" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="error_responseLabel">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header pb-0">
				<h5 class="modal-title" id="error_responseLabel">{{ __('Error') }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">Ãƒâ€”</span>
				</button>
			</div>
			<div class="modal-body">
				<h6 class="message_body">{{ __('Unknown error occurs') }}</h6>
			</div>
			<div class="modal-footer flex-nowrap justify-content-center align-items-center">
				<button type="button" class="btn btn-solid" data-dismiss="modal">{{
					__('Ok') }}</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="subscription_payment" tabindex="-1" aria-labelledby="subscription_paymentLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header border-bottom">
				<h5 class="modal-title text-17 mb-0 mt-0" id="subscription_paymentLabel">{{ __('Subscription') }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form action="" id="subscription_payment_form">
				@csrf @method('POST')
				<div>
					<input type="hidden" name="email" id="email" value="{{ Auth::user()->email }}"> <input type="hidden" name="subscription_id" id="subscription_id" value=""> <input type="hidden" name="subscription_amount" id="subscription_amount" value=""> <input type="hidden" name="card_last_four_digit" id="card_last_four_digit" value=""> <input type="hidden" name="card_expiry_month" id="card_expiry_month" value=""> <input type="hidden" name="card_expiry_year" id="card_expiry_year" value=""> <input type="hidden" name="type_id" id="type_id" value="">
				</div>
				<div class="modal-body pb-0">
					<div class="form-group">
						<h5 class="text-17 mb-2" id="subscription_title"></h5>
						<div class="mb-2">
							<span id="subscription_price"></span> / <span id="subscription_frequency"></span>
						</div>
					</div>
					<div class="form-group">
						<div class="text-17 mt-2">
							{{ __('Features Included') }}:
							<div class="mt-2" id="features_list"></div>
						</div>
					</div>
					<hr class="mb-1" />
					<div class="payment_response">
						<div class="alert p-0 m-0" role="alert"></div>
					</div>
					<h5 class="text-17 mb-2">{{ __('Debit From') }}</h5>
					<div class="form-group" id="subscription_payment_methods"></div>
				</div>
				<div class="modal-footer d-block text-center">
					<div class="row">
						<div class="col-sm-12 p-0 d-flex justify-space-around">
							<button type="button" class="btn btn-block btn-solid mr-1 mt-2 subscription_confirm_btn">{{
								__('Pay') }}</button>
							<button type="button" class="btn btn-block btn-solid ml-1 mt-2" data-dismiss="modal">{{ __('Cancel') }}</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>



<script type="text/template" id="payment_method_template">

	<% if(payment_options == '') { %>
        <h6>{{ __('Payment Options Not Avaialable') }}</h6>
    <% }else{ %>
        <% _.each(payment_options, function(payment_option, k){%>
            <% if( (payment_option.slug != 'cash_on_delivery') && (payment_option.slug != 'loyalty_points') ) { %>
                <label class="radio mt-2">
                    <%= payment_option.title %>
                    <input type="radio" name="subscription_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.slug %>" data-payment_option_id="<%= payment_option.id %>">
                    <span class="checkround"></span>
                </label>
                <% if(payment_option.slug == 'stripe') { %>
                    <div class="col-md-12 mt-3 mb-3 stripe_element_wrapper option-wrapper d-none">
                        <div class="form-control">
                            <label class="pb-1 mb-0">
                                <div id="stripe-card-element"></div>
                            </label>
                        </div>
                        <span class="error text-danger" id="stripe_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'stripe_fpx') { %>
                    <div class="col-md-12 mt-3 mb-3 stripe_fpx_element_wrapper option-wrapper d-none">
                        <label for="fpx-bank-element">
                            FPX Bank
                        </label>
                        <div class="form-control">
                            <div id="fpx-bank-element">
                              <!-- A Stripe Element will be inserted here. -->
                            </div>
                        </div>
                        <span class="error text-danger" id="stripe_fpx_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'stripe_ideal' ) { %>
                    <div class="col-md-12 mt-3 mb-3 stripe_ideal_element_wrapper option-wrapper d-none">
                        <label for="ideal-bank-element">
                            iDEAL Bank
                        </label>
                        <div class="form-control">
                            <div id="ideal-bank-element">
                              <!-- A Stripe Element will be inserted here. -->
                            </div>
                        </div>

                        <span class="error text-danger"id="error-message"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'yoco') { %>
                    <div class="col-md-12 mt-3 mb-3 yoco_element_wrapper option-wrapper d-none">
                        <div class="form-control">
                            <div id="yoco-card-frame">
                            <!-- Yoco Inline form will be added here -->
                            </div>
                        </div>
                        <span class="error text-danger" id="yoco_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'checkout') { %>
                    <div class="col-md-12 mt-3 mb-3 checkout_element_wrapper option-wrapper d-none">
                        <div class="form-control card-frame">
                            <!-- form will be added here -->
                        </div>
                        <span class="error text-danger" id="checkout_card_error"></span>
                    </div>
                <% } %>
                <% if(payment_option.slug == 'payphone') { %>
                    <div id="pp-button"></div>
                <% } %>

                <% if(payment_option.slug == 'plugnpay') { %>
                    <div class="col-md-12 mt-3 mb-3 plugnpay_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="plugnpay-card-element" placeholder="Enter card Number"  />
                            </div>
                            <div class="col-3">
                                <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="5"  id="plugnpay-date-element" placeholder="MM/YY"  />
                            </div>
                            <div class="col-3">
                                <input type="password" max="3" style=" border-left: none;"  class="form-control" id="plugnpay-cvv-element" placeholder="CVV"  />
                            </div>
                        </div>

                        <span class="error text-danger" id="plugnpay_card_error"></span>
                    </div>
                <% } %>


            <% } %>
        <% }); %>
    <% } %>
</script>

@endsection @section('script')
<script src="{{asset('assets/js/custom.js')}}"></script>
<script src="{{asset('assets/js/payment.js')}}"></script>
@if(in_array('razorpay',$client_payment_options))
<script type="text/javascript" src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif @if(in_array('stripe',$client_payment_options) ||
in_array('stripe_fpx',$client_payment_options) ||
in_array('stripe_oxxo',$client_payment_options) ||
in_array('stripe_ideal',$client_payment_options))
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endif @if(in_array('stripe_oxxo',$client_payment_options))
<script>
	var stripe_oxxo_publishable_key = '{{ $stripe_oxxo_publishable_key }}';
</script>
@endif @if(in_array('stripe_ideal',$client_payment_options))
<script>
	var stripe_ideal_publishable_key = '{{ $stripe_ideal_publishable_key }}';
</script>
@endif @if(in_array('yoco',$client_payment_options))
<script src="https://js.yoco.com/sdk/v1/yoco-sdk-web.js"></script>
<script type="text/javascript">
	var sdk = new window.YocoSDK({
		publicKey: yoco_public_key
	});
	var inline = '';
</script>
@endif @if(in_array('checkout',$client_payment_options))
<script src="https://cdn.checkout.com/js/framesv2.min.js"></script>
@endif @if(in_array('payphone',$client_payment_options))
<script src="https://pay.payphonetodoesposible.com/api/button/js?appId={{$payphone_id}}"></script>
@endif @if(in_array('khalti',$client_payment_options))
<script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
@endif
<script type="text/javascript">
	var stripe_fpx = '';
	var fpxBank = '';
	var idealBank = {};
	var create_viva_wallet_pay_url = "{{route('vivawallet.pay')}}";
	var create_mvodafone_pay_url = "{{route('mvodafone.pay')}}";
	var create_konga_hash_url = "{{route('kongapay.createHash')}}";
	var create_dpo_subscription = "{{route('dpo.subscription')}}";
	var create_payphone_url = "{{route('payphone.createHash')}}";
	var create_easypaisa_hash_url = "{{route('easypaisa.createHash')}}";
	var create_windcave_hash_url = "{{route('windcave.createHash')}}";
	var create_paytech_hash_url = "{{route('paytech.createHash')}}";
	var create_flutterwave_url = "{{route('flutterwave.createHash')}}";
	var create_ccavenue_url = "{{route('ccavenue.pay')}}";
	var post_payment_via_gateway_url = "{{route('payment.gateway.postPayment', ':gateway')}}";
	var subscription_payment_options_url = "{{route('user.subscription.plan.select', ':id')}}";
	var user_subscription_purchase_url = "{{route('user.subscription.plan.purchase', ':id')}}";
	var user_subscription_cancel_url = "{{route('user.subscription.plan.cancel', ':id')}}";
	var payment_stripe_url = "{{route('payment.stripe')}}";
	var payment_retrive_stripe_fpx_url = "{{url('payment/retrieve/stripe_fpx')}}";
	var payment_create_stripe_fpx_url = "{{url('payment/create/stripe_fpx')}}";
	var payment_create_stripe_oxxo_url = "{{url('payment/create/stripe_oxxo')}}";
	var payment_retrive_stripe_ideal_url = "{{url('payment/retrieve/stripe_ideal')}}";
	var payment_create_stripe_ideal_url = "{{url('payment/create/stripe_ideal')}}";
	var payment_paystack_url = "{{route('payment.paystackPurchase')}}";
	var payment_yoco_url = "{{route('payment.yocoPurchase')}}";
	var payment_paylink_url = "{{route('payment.paylinkPurchase')}}";
	var payment_checkout_url = "{{route('payment.checkoutPurchase')}}";
	var payment_khalti_url = "{{route('payment.khaltiVerification')}}";
	var payment_khalti_complete_purchase = "{{route('payment.khaltiCompletePurchase')}}";
	var check_active_subscription_url = "{{route('user.subscription.plan.checkActive', ':id')}}";
	var create_mtn_momo_token = "{{route('mtn.momo.createToken')}}";
	var payment_plugnpay_url = "{{route('payment.plugnpay.beforePayment')}}";


	$(document).on('change', '#subscription_payment_methods input[name="subscription_payment_method"]', function() {
		var method = $(this).val();
		var code = method.replace('radio-', '');

		if (code != '') {
			$("#subscription_payment_methods .option-wrapper").addClass('d-none');
			$("#subscription_payment_methods ." + code + "_element_wrapper").removeClass('d-none');
		} else {
			$("#subscription_payment_methods .option-wrapper").addClass('d-none');
		}

		if (code == 'yoco') {
			// $("#subscription_payment_methods .yoco_element_wrapper").removeClass('d-none');
			// Create a new dropin form instance

			var yoco_amount_payable = $("input[name='subscription_amount']").val();
			inline = sdk.inline({
				layout: 'field',
				amountInCents: yoco_amount_payable * 100,
				currency: 'ZAR'
			});
			// this ID matches the id of the element we created earlier.
			inline.mount('#yoco-card-frame');
		}
		// else {
		//     $("#subscription_payment_methods .yoco_element_wrapper").addClass('d-none');
		// }

		if (code == 'checkout') {
			// $("#subscription_payment_methods .checkout_element_wrapper").removeClass('d-none');
			Frames.init(checkout_public_key);
		}
		// else {
		//     $("#subscription_payment_methods .checkout_element_wrapper").addClass('d-none');
		// }
	});

	$(document).on('click', '.cancel-subscription-link', function() {
		var id = $(this).attr('data-id');
		$('#cancel-subscription-form').attr('action', user_subscription_cancel_url.replace(":id", id));
	});



	$(document).delegate('#view_all_address', 'click', function() {

		$("#view_all_address").addClass("d-none");
		$("#view_all_address").removeClass("d-block");
		$("#view_all_address_div").removeClass("d-none");

	});

	$('#meal-subscription-form .select2-multiple').select2();


	/* Wizard */


	// ------------step-wizard-------------
	$(document).ready(function() {
		$('.nav-tabs > li a[title]').tooltip();

		//Wizard
		$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {

			var target = $(e.target);

			if (target.parent().hasClass('disabled')) {
				return false;
			}
		});

		$(document).on('change', 'input[name="delivery_method"]', function() {
			if ($(this).val() == 'delivery') {
				$('.address-box').show();
				$('.address-box').find('input[name="address_id"]').attr('required', 'required')
				$('.product-list-box').css('max-height', '726px')
				$('.product-list-box').css('min-height', '726px')
			} else {
				$('.address-box').find('input[name="address_id"]').removeAttr('required')
				$('.product-list-box').css('max-height', '637px')
				$('.product-list-box').css('min-height', '637px')
				$('.address-box').hide()
			}
		});

		$(".next-step").click(function(e) {
			let form = $('#meal-subscription-form');
			if (!$(form)[0].checkValidity()) {
				$(form)[0].reportValidity();
				return false;
			}

			var amount = 0;
			var discount = 0;
			formData = form.serializeArray();
			$(formData).each(function(k, element) {
				console.log(element.name, element.value)
				switch (element.name) {
					case 'amount':
						amount = parseFloat(element.value).toFixed(2)
						$('#sub_total').html("{{$clientCurrency->currency->symbol}} " + amount)
						break;
					case 'subscription_start_date':
						$('#sub-start-date').html(element.value.replace("T", " "));
						break;
					case 'meal_package':
						$('#sub-package').html(element.value)
						break;
					case 'delivery_method':
						let delivery = element.value.charAt(0).toUpperCase() + element.value.slice(1)
						$('#sub-delivery').html(delivery)
						break;
					case 'address_id':
						$('#sub-address').html($("select#address-id :selected").text())
						break;
					case 'discount_code':
						if (element.value != '') {
							discount = parseFloat(element.value).toFixed(2)
							$('#coupon_discount').html("{{$clientCurrency->currency->symbol}} " + discount)
						}
						break;
				}
			})

			let days = $('#week-days').val().map(v => v.charAt(0).toUpperCase() + v.slice(1));
			$('#sub-days').html(days.join(','))

			$('#total').html("{{$clientCurrency->currency->symbol}} " + parseFloat(amount + discount).toFixed(2))

			var active = $('.wizard .nav-tabs li.active');
			active.next().removeClass('disabled');
			nextTab(active);

		});

		$(".prev-step").click(function(e) {

			var active = $('.wizard .nav-tabs li.active');
			prevTab(active);

		});
	});

	function nextTab(elem) {
		$(elem).next().find('a[data-toggle="tab"]').click();
	}

	function prevTab(elem) {
		$(elem).prev().find('a[data-toggle="tab"]').click();
	}


	$('.nav-tabs').on('click', 'li', function() {
		$('.nav-tabs li.active').removeClass('active');
		$(this).addClass('active');
	});
</script>
@if(in_array('kongapay',$client_payment_options))
<script src="https://kongapay-pg.kongapay.com/js/v1/production/pg.js"></script>
@endif @if(in_array('flutterwave',$client_payment_options))
<script type="text/javascript" src="https://checkout.flutterwave.com/v3.js"></script>
@endif
<script type="text/javascript" src="{{asset('js/developer.js')}}"></script>
<script type="text/javascript" src="{{asset('js/payment.js')}}"></script>



@endsection