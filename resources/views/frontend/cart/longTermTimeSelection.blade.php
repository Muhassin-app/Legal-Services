<hr class="my-1">
<div class="row mb-1 pl-2 d-flex align-items-center LongTermProduct">
    <div class="product">
        <h6 class="product-title mb-0">{{ __('Service Product') }}:
            <span class="ml-1">
              {{ !empty($vendor_product->product->long_term_products->product) && isset($vendor_product->product->long_term_products->product->translation_one) ? $vendor_product->product->long_term_products->product->translation_one->title : ($vendor_product->product->long_term_products->product->title ?? "na")  }}
            </span>
        </h5>
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="product-title mt-0">{{ __('No. of Bookings') }}:
                <span class="ml-1">
                    {{ !empty($vendor_product->long_term_products) ? $vendor_product->long_term_products->quantity : '' }}
                </span>
            </h6>
        </div>   
    </div>
</div> 
<hr class="my-1">
<div class="row mb-1 d-flex align-items-center LongTermSechudel" id ='LongTermSechudel_{{ $vendor_product->id}}' data-cart_product_id="{{$vendor_product->id}}">

    <div class="col-3 vendor_service_timing" >
        <div class="hsProductTiming">
            <h6 class="product-title mt-0">{{ __('Service Time') }}:<br>
            </h6>
            <select class="form-control selectize-select"
                id="service_period" name="service_period" disabled>
                @foreach (config('constants.Period') as $key => $value)
                    @if (in_array($key, $vendor_product->product->ServicePeriods))
                        <option value="{{ $key }}" {{ $vendor_product->service_period == $key ? 'selected' : '' }} >
                            {{ __($value) }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-3 service_date_div {{  $vendor_product->service_period == 'months' ? '' : 'd-none' }}" >
        <label for="">{{ __('Date') }}</label>
        <select class="form-control selectize-select" id="service_date"
            name="date" disabled>
            @for ($i = 1; $i <= 28; $i++)
                <option value="{{ $i }}" {{ $vendor_product->service_date == $i ? 'selected' : '' }} >{{ $i }}
                </option>
                @if ($i == 28)
                    <option value="0" {{ $vendor_product->service_date == 0 ? 'selected' : '' }}> {{ __('Last day of month') }}
                    </option>
                @endif
            @endfor
        </select>
    </div>
    <div class="service_day_div col-3 {{  $vendor_product->service_period == 'week' ? '' : 'd-none' }}">
        <label for="">{{ __('Day:') }}</label>
        <select class="form-control selectize-select" id="service_day"
            name="day" disabled>
            @foreach (config('constants.weekDay') as $dayKey => $day)
                <option value="{{ $dayKey }}" {{ $vendor_product->service_day == $dayKey ? 'selected' : '' }} >{{ __($day) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="service_time_div col-3">
        <label for="">{{ __('Time:') }}</label>
        <input type="time" id="service_start_time" value="{{ $vendor_product->service_start_time  }}"  disabled class="form-control">
    </div>
</div>