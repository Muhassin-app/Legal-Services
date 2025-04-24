<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12" id="imageInput">
                <label>{{ __("Upload Gift Card image") }}</label>
                    <input type="file" accept="image/*" data-plugins="dropify" name="image"  data-default-file="{{$GiftCard->image['proxy_url'].'600/400'.$GiftCard->image['image_path']}}" class="dropify" />
                <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 100x100</label>
                <span class="invalid-feedback" role="alert">
                    <strong></strong>
                </span>
            </div>
        </div>
        <input type="hidden" id="editGiftCard" name="editGiftCard" value="{{ $GiftCard->id  }}">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group" id="titleInput">
                    {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
                    {!! Form::text('title',$GiftCard->title, ['class' => 'form-control', 'placeholder'=>'Enter Title']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group" id="short_descInput">
                    {!! Form::label('short_desc', __('Short Description'),['class' => 'control-label']) !!}
                    {!! Form::textarea('short_desc',$GiftCard->title, ['class' => 'form-control', 'placeholder'=>'Enter Short Description', 'rows' => 3]) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            {{-- <div class="col-md-6">
                <div class="form-group" id="nameInput">
                    {!! Form::label('title', __('Gift Card Code'),['class' => 'control-label']) !!}
                    {!! Form::text('name',$GiftCard->name, ['class' => 'form-control', 'placeholder'=>'Enter Gift Card Code']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div> --}}
            

            <div class="col-md-6">
                <div class="form-group" id="amountInput">
                    {!! Form::label('title', __('Amount'),['class' => 'control-label']) !!}
                    {!! Form::number('amount',  decimal_format($GiftCard->amount), ['class' => 'form-control amountInputField', 'id' => 'amountInputField', 'placeholder'=> __('Enter total amount'), 'max' => "10000", 'min' => "1"]) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="">
                    <div class="form-group" id="expiry_dateInput">
                        @php
                        $minDate = Date('Y-m-d');
                        @endphp
                        {!! Form::label('expiry_date', __('Expiry Date'),['class' => 'control-label']) !!}
                        {!! Form::text('expiry_date',$GiftCard->expiry_date, ['class' => 'form-control downside datetime-datepicker', 'id' => 'start-datepicker', 'min' => $minDate]) !!}
                        <span class="invalid-feedback" role="alert">
                           
                            <strong></strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>