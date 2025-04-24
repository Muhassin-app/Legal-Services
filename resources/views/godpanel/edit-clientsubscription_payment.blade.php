<div class="modal-header border-bottom">
    <h4 class="modal-title">{{ __('Edit Subscription Payments') }}</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>

@if(!empty($subscriptiondata))
{{ Form::open(array('id' => 'subscription_payment_form', 'method' => 'post', 'enctype' => 'multipart/form-data', 'route' => 'clientsubscription.updatepayment')) }}
    @csrf
    <div class="modal-body" >
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-centered table-nowrap table-striped" id="client_subscription_table" width="100%">
                        <thead>
                            <tr>
                                <th>{{ __('Client') }}<input type="hidden" name="subsid" id="subsid" value="{{$subscriptiondata->id}}" /></th>
                                <th>{{ __('Plan') }}</th>
                                <th>{{ __('Timeframe') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Mark As Paid') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <td>{{$subscriptiondata->client_name}}</td>
                            <td>{{$subscriptiondata->billing_plan_title}} <span class="badge bg-info" style="color:#fff;">{{$subscriptiondata->plan_type}}</span></td>
                            <td>{{$subscriptiondata->billing_timeframe_title}}</td>
                            <td><span id="real_subs_price">{{$subscriptiondata->billing_price}}</span></td>
                            <td>{{$subscriptiondata->start_date}}</td>
                            <td>{{$subscriptiondata->end_date}}</td>
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="chk_is_paid" id="chk_is_paid" value="1" {{($subscriptiondata->is_paid == 1) ? 'checked' : ''}}>
                                    <label class="custom-control-label" for="chk_is_paid"></label>
                                </div>
                            </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <h4 class="header-title">Payment Details</h4>
        @if(!empty($subspaymentdata))
            <div class="row">
                <div class="col-md-2">
                    &nbsp;
                </div> 
            
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Payment Method') }}</label>
                                {!! Form::select('payment_method', $payment_methodlist, $subspaymentdata->payment_method, ['class' => 'form-control', 'id'=>'payment_method', 'required' => 'required']) !!}
                                <span class="invalid-feedback" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" id="nameInput">
                                <label>{{ __('Upload Receipt') }}</label> @if(is_array($subspaymentdata->receipt)) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{$subspaymentdata->receipt['image_fit'].'1000/1000'.$subspaymentdata->receipt['image_path']}}" target="_blank" id="viewfilelink">Click Here to View <input type="hidden" name="receipt_file" id="receipt_file" value="@if(is_array($subspaymentdata->receipt)){{$subspaymentdata->receipt['original']}}@endif" /></a>@endif
                                <input type="file" accept="image/*" data-plugins="dropify" name="receipt" class="dropify" data-default-file="{{(is_array($subspaymentdata->receipt))?$subspaymentdata->receipt['proxy_url'].'100/100'.$subspaymentdata->receipt['image_path']:''}}" />
                            </div>
                            
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="nameInput">
                                <label>{{ __('Amount Paid') }}</label>
                                {!! Form::number('paid_amount', $subspaymentdata->paid_amount, ['class'=>'form-control', 'required'=>'required', 'id'=>'paid_amount']) !!}
                                <span class="invalid-feedback" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Payment Date') }}</label>
                                {!! Form::text('payment_date', $subspaymentdata->payment_date, ['class'=>'form-control', 'required'=>'required', 'id'=>'payment_date', 'placeholder'=>'dd-mm-yyyy', 'autocomplete'=>'off']) !!}
                                <span class="invalid-feedback" role="alert">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    &nbsp;
                </div>    
            </div>
        @else
        <div class="row">
            <div class="col-md-2">
                &nbsp;
            </div> 
        
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Payment Method') }}</label>
                            {!! Form::select('payment_method', $payment_methodlist, null, ['class' => 'form-control', 'id'=>'payment_method', 'required' => 'required']) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="nameInput">
                            <label>{{ __('Upload Receipt') }}</label>
                            <input type="file" accept="image/*" data-plugins="dropify" name="receipt" class="dropify" data-default-file="" />
                        </div>
                    </div> 
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" id="nameInput">
                            <label>{{ __('Amount Paid') }}</label>
                            {!! Form::number('paid_amount', $subscriptiondata->billing_price, ['class'=>'form-control', 'required'=>'required', 'id'=>'paid_amount']) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('Payment Date') }}</label>
                            {!! Form::text('payment_date', date('d-m-Y',time()), ['class'=>'form-control', 'required'=>'required', 'id'=>'payment_date', 'placeholder'=>'dd-mm-yyyy', 'autocomplete'=>'off']) !!}
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                &nbsp;
            </div>    
        </div>
        @endif
    </div>
    @if($subscriptiondata->is_paid ==0)
    <div class="modal-footer">
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-info waves-effect waves-light submiteditSubscriptionPaymentForm">{{ __('Submit') }}</button>
            </div>
        </div>
    </div>
    @endif
{{ Form::close() }}
@else
    <div class="modal-body" >
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <ul class="m-0">
                        Something went wrong.
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endif