<?php
use Carbon\Carbon;
?>
<style>
    .alInfoIocn .tooltiptext {
        visibility: hidden;
        width: 200px;
        background-color: black;
        color: #fff;
        text-align: center;
        padding: 5px 0;
        border-radius: 6px;
        position: absolute;
        z-index: 1;
        margin-left: 5px;
        margin-top: 5px;
    }

    .alInfoIocn {
        position: absolute;
        top: 0;
        right: 0;
        cursor: pointer;
    }

    .alInfoIocn:hover .tooltiptext {
        visibility: visible;
    }

    .cross-sell .img-outer-box.position-relative img,
    .upsell-sell .img-outer-box.position-relative img {
        position: absolute;
        height: 100%;
        width: 100%;
        object-fit: cover;
    }

    .cross-sell .img-outer-box.position-relative,
    .upsell-sell .img-outer-box.position-relative {
        padding-bottom: 100%;
    }

    .cross-sell .media-body,
    .upsell-sell .media-body {
        padding: 0 10px;
    }

    .cross-sell .media-body .product-description,
    .upsell-sell .media-body .product-description {
        text-align: left;
        padding: 0;
    }

    .cross-sell .slick-slide>div {
        margin: 0 12px;
    }

    .order-user-name p {
        display: inline-block;
    }

    .order-user-name {
        background: #eeeeee;
        padding: 6px 6px;
        border-radius: 4px;
    }

    .cart-checkout_btn #order_placed_btn {
        padding: 10px 5px !important;
        display: inline-block;
        font-size: 14px !important;
    }

</style>

@php
$serviceType = Session::get('vendorType');

$additionalPreference = $getAdditionalPreference;
$is_service_product_price_from_dispatch_forOnDemand = 0;
$hidden_token = '';
if ($additionalPreference['is_token_currency_enable'] == 1) {
$hidden_token = 'd-none';
}
$getOnDemandPricingRule = getOnDemandPricingRule($serviceType, (@Session::get('onDemandPricingSelected') ?? ''),$additionalPreference);
// if(($additionalPreference['is_service_product_price_from_dispatch'] == 1) && ( Session::get('vendorType') == 'on_demand')){
// $is_service_product_price_from_dispatch_forOnDemand =1;
// }
$is_service_product_price_from_dispatch_forOnDemand = $getOnDemandPricingRule['is_price_from_freelancer'] ?? 0;
$cartProduct = $cart_details->products[0] ?? null;
if($cartProduct){
    $vendorProduct = $cartProduct->vendor_products[0] ?? null;
    $variantData = $vendorProduct->pvariant;
    $product = $vendorProduct->product ?? null;
    $rentalProtection = $product->rental_protections ?? null;
    $bookingOptions = $product->booking_options ?? null;
    $translation = $vendorProduct->product ? $vendorProduct->product->translation : null;
    $startDate = Carbon::parse($vendorProduct->start_date_time);
    $endDate = Carbon::parse($vendorProduct->end_date_time);
    $difference = $startDate->diffInDays($endDate);

}
  

$serviceType = Session::get('serviceType');
@endphp

@if ($cart_details->totalQuantity <= 0) <div class="container">
    <div class="row mt-2 mb-4 mb-lg-5">
        <div class="col-12 text-center">
            <div class="cart_img_outer" style="height:200px;">
                <img class="blur-up lazyload" data-src="{{ asset('front-assets/images/empty_cart.png') }}">
            </div>
            <h3>{{ __('Your Cart Is Empty!') }}</h3>
            <p>{{ __('Add items to it now.') }}</p>
            <a class="btn btn-solid" href="{{ url('/') }}">{{ __('Continue Shopping') }}</a>
        </div>
    </div>
    </div>
    @else
    <div class="row">
        <div class="col-md-8 left-item">
            <div class="item">
                @if(!empty($rentalProtection))
                <div class="select_product">
                    <h2>Select Protection</h2>
                    @foreach($rentalProtection as $key => $protection)
                    @if($protection->type_id == 1)
                    <h5>Included in your booking</h5>
                    <ul class="d-flex">
                        <li><img src="/yacht-images/check.png" alt="">{{$protection->rental_protection->description}}</li>
                    </ul>
                    @endif
                    @endforeach
                    <div class="select_product_form">
                        <form>
                            @foreach($rentalProtection as $key => $protection)
                            @if($protection->type_id == 2)
                            <div class="form-group">
                                <input type="radio" name="product_select" class="protection-box" id="inclusive" data-id="{{$protection->rental_protection->id}}" data-amount="{{$protection->rental_protection->price}}" data-title="{{$protection->rental_protection->title}}">
                                <label for="inclusive">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3><img src="/yacht-images/inclusive.png" alt="">{{$protection->rental_protection->title}}</h3>
                                        <div class="price">
                                            @php
                                            switch($protection->rental_protection->validity){
                                            case 1:
                                            $per = 'day';
                                            break;
                                            case 2:
                                            $per = 'week';
                                            break;
                                            case 3:
                                            $per = 'month';
                                            break;
                                            default:
                                            $per = 'day';
                                            }
                                            @endphp
                                            <p>{{Session::get('currencySymbol')}}{{$protection->rental_protection->price}} /{{$per}}</p>
                                        </div>
                                    </div>
                                    <p class="d-none">Financial Responsibility:<span> $0.00</span></p>

                                    <ul>
                                        <li><img src="/yacht-images/check.png" alt="">{{$protection->rental_protection->description}}</li>
                                    </ul>
                                </label>
                                <span></span>
                            </div>
                            @endif
                            @endforeach
                            {{-- <div class="form-group d-none">
                                <input type="radio" name="product_select" name="" id="smart">
                                <label for="smart">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3><img src="/yacht-images/inclusive.png" alt="">Smart</h3>
                                        <div class="price">
                                            <p><span>AED</span> 100/day</p>
                                        </div>
                                    </div>
                                    <p>Financial Responsibility: <span> $0.00</span></p>
                                    <ul>
                                        <li><img src="/yacht-images/check.png" alt="">Loss damage waiver for collision damages, scratches, bumps and theft.</li>
                                        <li><img src="/yacht-images/check.png" alt="">Tire and Windshield Protection.</li>
                                        <li><img src="/yacht-images/check.png" alt="">Interior Protection.</li>
                                        <li><img src="/yacht-images/check.png" alt="">Personal accident protection.</li>
                                        <li><img src="/yacht-images/check.png" alt="">Mobility service.</li>
                                        <li><img src="/yacht-images/check.png" alt="">24/7 breakdown assistance.</li>
                                    </ul>
                                </label>
                                <span></span>
                            </div> --}}
                        </form>
                    </div>
                </div>
                @endif

                <div class="select_product mt-5 adone_item">
                    <h2>Choose Ad-Ons</h2>
                    <div class="grid">
                        <!-- 1 -->
                        @foreach($addons as $key => $addon)

                        <div class="item" data-max="{{$addon->max_select}}" data-min="{{$addon->min_select}}">
                            <div class="image">
                                <img src="/yacht-images/adone/1.png" alt="">
                            </div>
                            <div class="text">
                                <h6>{{ucfirst($addon->title)}} (Min: {{$addon->min_select}}, Max : {{$addon->max_select}})</h6>
                                @foreach($addon->option as $key => $option)
                                    <p>{{$option->title}}</p>
                                    <div class="d-flex justify-content-between">
                                        <span>{{Session::get('currencySymbol')}}{{number_format($option->price, 2)}}</span>
                                            <div class="addcart_cta addon" data-id="{{$addon->id}}" data-option-id="{{$option->id}}" data-amount="{{number_format($option->price, 2)}}" data-days="{{$difference}}" data-title="{{$option->title}}">
                                            <input type="checkbox" name="opt{{$addon->id}}" class="opt{{$addon->id}}" data-max="{{$addon->max_select}}" data-min="{{$addon->min_select}}"/>
                                            {{-- <span class="minus" data-min={{$addon->min_select}}>-</span>
                                            <span class="num">0</span>
                                            <span class="plus" data-max={{$addon->max_select}} id="cart_plus">+</span> --}}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @if(!empty($bookingOptions))
                <div class="select_product mt-5 adone_item">
                    <h2>Booking Option</h2>
                    <div class="bokking_form">
                        <form>
                            @foreach($bookingOptions as $key => $option)
                            <div class="form-group">
                                <input type="radio" name="booking" value="" class="booking-options" id="bestproce" data-id="{{$option->booking_option->id}}" data-title="{{$option->booking_option->title}}" data-amount="{{$option->booking_option->price}}">
                                <label for="bestproce">
                                    <div class="image">
                                        <img src="/yacht-images/aed.png" alt="">
                                    </div>
                                    <div class="text">
                                        <h3>{{$option->booking_option->title}}</h3>
                                        <p>{{$option->booking_option->description}}</p>
                                        <ul class="d-none">
                                            <li><img src="/yacht-images/check.png" alt="">Before scheduled pick-up time: {{Session::get('currencySymbol')}}{{$option->booking_option->price}}</li>
                                            <li><img src="/yacht-images/check.png" alt="">After scheduled pick-up time: No refund</li>
                                        </ul>
                                        <span class="">Included</span>
                                    </div>
                                </label>
                                <span></span>
                            </div>
                            @endforeach
                            {{-- <div class="form-group d-none">
                                <input type="radio" name="booking" value="" id="flexible">
                                <label for="flexible">
                                    <div class="image">
                                        <img src="/yacht-images/aed.png" alt="">
                                    </div>
                                    <div class="text">
                                        <h3>Flexible</h3>
                                        <p>Stay flexible by paying later.</p>
                                        <ul>
                                            <li><img src="/yacht-images/check.png" alt="">Pay at pick-up</li>
                                            <li><img src="/yacht-images/check.png" alt="">Free cancellation</li>
                                        </ul>
                                        <span>+ $14.63</span>
                                    </div>
                                </label>
                                <span></span>
                            </div> --}}

                        </form>
                    </div>
                </div>
                @endif

            </div>
        </div>
        @php
        $product = $cartProduct->vendor_products[0]->product;
        $fields = [];
        $desc = [];
        $detail = [
            'Mileage',
            'Engine',
            'Transmission',
            'BHP',
            'Seats',
            'Boot Space',
            'Fuel Type',
            'Cabins',
            'Berths',
            'Baths'
        ];
    
        foreach ($product->product_attribute as $productAttribute) {
            //$attribute = $productAttribute->attribute;
            //$img = $attribute->icon['proxy_url'] . '100/100' . $attribute->icon['image_path'];
            if ($productAttribute->attribute_option) {
                $title = $productAttribute->attribute_option->title ?? $productAttribute->key_value;
            if(in_array($productAttribute->key_name, $detail)){
                $fields[$productAttribute->key_name]['title'] = $title;
                //$fields[$productAttribute->key_name]['img'] = $img;
            }else{
                $desc[$productAttribute->key_name]['title'] = $title;
                //$desc[$productAttribute->key_name]['img'] = $img;
            }
            }
        }
        @endphp
        <div class="col-md-4 right">
            <div class="item ">
                <div class="booking_day">
                    <div class="text">
                        <div class="">
                            <span class="d-none">
                                <input type="checkbox" name="checked_cart_product" class="checked-cart-product" id="checked_cart_product" value="{{$vendorProduct->id}}" {{ $vendorProduct->is_cart_checked ? 'checked' : '' }}>
                                <input type="hidden" name="without_category_kyc" value="{{ $cart_details->without_category_kyc }}">
                                {!! $cart_details->left_section !!}
                            </span>
                            <h3>{{$product->translation_one->title}}</h3>
                            <div class="productList d-flex justify-content-between">
                                <ul class="product-features">
                                @if(Session::get('serviceType') == 'rental')
                                    <li><a href="javscript:void(0);">{{$fields['Transmission']['title'] ?? ''}}</a></li>
                                    <li><a href="javscript:void(0);">{{$fields['Fuel Type']['title'] ?? ''}}</a></li>
                                    <li><a href="javscript:void(0);">{{$fields['Seats']['title'] ?? ''}} Seats</a></li>
                                @else
                                    <li><a href="javascript:void(0);">{{$fields['Cabins']['title'] ?? '0'}} Cabins</a></li>
                                    <li><a href="javascript:void(0);">{{$fields['Baths']['title'] ?? '0'}} Baths</a></li>
                                    <li><a href="javascript:void(0);">{{$fields['Berths']['title'] ?? '0'}} Seats</a></li>
                                @endif
                                </ul>
                            </div>
                            @if($serviceType == 'rental')
                                <span>Booking for {{$difference}} days</span>
                            @endif
                        </div>
                    </div>
                    <div class="image">
                    @php
                        $media = $product->media[0] ?? null;
                        if($media){
                            if(isset($media->pimage)){
                                $img = $media->pimage->image;
                            }else{
                                $img = $media->image;
                            }
                        }else{
                            $img = $vendorProduct->image_url;
                        }
                    @endphp
                        <img src="{{@$img->path->image_fit.'1000/1000'.@$img->path->image_path}}" src="{{@$img->path->image_fit.'100/100'.@$img->path->image_path}}" alt="">
                    </div>
                </div>
                <div class="booking_date">
                    @if($serviceType == 'rental')
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="start_time inner_item">
                                <h6>Start :<span>{{date('H:i',strtotime($vendorProduct->start_date_time))}}</span></h6>
                                <h6>{{date('d M y',strtotime($vendorProduct->start_date_time))}}</h6>
                            </div>
                            <div class="seleed_date">
                                <span>{{$difference}} Day(s)</span>
                            </div>
                            <div class="end_time inner_item">
                                <h6>End :<span>{{date('H:i',strtotime($vendorProduct->end_date_time))}}</span></h6>
                                <h6>{{date('d M y',strtotime($vendorProduct->end_date_time))}}</h6>
                            </div>
                        </div>
                    @elseif($serviceType == 'yacht')
                        <div class="d-flex justify-content-between align-items-center">
                                <h6>{{$variantData->vset[0]->option_data->title}}</h6>
                                <h6>{{Session::get('currencySymbol')}}{{$variantData->price}}</h6>
                        </div>
                    @endif
                    <ul>
                        <img src="/yacht-images/6.png">
                        <li><img src="/yacht-images/4.png" alt=""><span>{{$cart_details->vendor_detail->vendor_address->address ?? ""}}</span> Pickup</li>
                        <li><img src="/yacht-images/5.png" alt=""><span>{{$cart_details->vendor_detail->vendor_address->address ?? ""}} </span>DROP</li>
                    </ul>
                </div>
                <div class="rental_item">
                    <div class="rentalcharges rental-box">
                        <h3>Rental Charges</h3>
                        <div class="inner_item d-flex justify-content-between align-items-center">
                                <p>{{$difference}} Rental Days</p>
                                <span>{{Session::get('currencySymbol')}}{{$cart_details->sub_total*$difference}}</span>
                        </div>
                        <div class="inner_item d-flex justify-content-between align-items-center mt-2">
                                <p>Security Amount</p>
                                <span>{{Session::get('currencySymbol')}}{{$cart_details->security_amount}}</span>
                        </div>
                    </div>
                    <div class="rentalcharges" id="protection-amount-box" style="display:none;">
                        <h3>Select Protection</h3>
                        <div class="inner_item d-flex justify-content-between align-items-center">
                            <p></p>
                            <span></span>
                        </div>
                    </div>
                    <div class="rentalcharges Booking" id="booking-box" style="display:none;">
                        <h3>Booking Option</h3>
                        <div class="inner_item d-flex justify-content-between align-items-center">
                            <p></p>
                            <span></span>
                        </div>
                    </div>
                    <div class="rentalcharges" id="addon-box" style="display:none;">
                        <h3>Addons</h3>
                    </div>
                </div>

                <div class="taxes_fees">
                    <h3>Taxes and Fees</h3>
                    <ul>
                        <li>Discount Amount <span>{{Session::get('currencySymbol')}}{{$cart_details->total_discount_amount}}</span></li>
                        <li>Taxes <span>{{Session::get('currencySymbol')}}{{$cart_details->total_taxable_amount}}</span></li>
                        <li>Total(incl.tax) <span id="gross-total" data-amount="{{$cart_details->new_gross_amount}}">{{Session::get('currencySymbol')}}{{$cart_details->new_gross_amount}}</span></li>
                    </ul>
                </div>


            </div>
            <div class="confirm_cta">
                
                    <button type="button" style="width:100%;" class="btn btn-solid" id="order_placed_btn">{{__('Confirm and Pay')}}<i class="fa fa-angle-right"></i>
                        <img style="width:5%; display:none;" id="proceed_to_pay_loader" src="{{asset('assets/images/loader.gif')}}"/>
                    </button>
            </div>
        </div>
        @endif

        <script>
            const plus = document.querySelectorAll(".plus")
                , minus = document.querySelectorAll(".minus")
                protectionBox = document.querySelector(".protection-box"),
                protectionAmountBox = document.querySelector("#protection-amount-box"),
                totalAmount = document.querySelector('#gross-total'),
                bookingBox = document.querySelector('#booking-box'),
                bookingOption = document.querySelector('.booking-options'),
                addonBox = document.querySelector('#addon-box'),
                addAddonButtons = document.querySelectorAll(".add-addon"),
                checkboxes = document.querySelectorAll('input[class^="opt"]');

            var states = {
                'booking' : false,
                'rentalProtection' : false
            };

            var bookingOptionId = [];
            var rentalProtectionId = [];
            var addonsId = [];
            var addonsOptionId = [];

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    let parent = this.parentNode;
                    let id = parent.getAttribute('data-id');
                    let optionId = parent.getAttribute('data-option-id');
                    let amount = parseFloat(parent.getAttribute('data-amount')).toFixed(2);
                    let maxLimit = this.getAttribute('data-max')
                    var checkedCheckboxes = document.querySelectorAll(`input[name="opt${id}"]:checked`);                        
                    var style = window.getComputedStyle(addonBox);
                    
                    if (checkedCheckboxes.length > maxLimit) {
                        this.closest('.item').style.transition = 'border 0.2s ease-in-out';
                        this.closest('.item').style.border = '1px solid red';

                        setTimeout(() => {
                            this.closest('.item').style.border = 'none';
                        }, 3000);

                        this.checked = false;
                        return false;
                    }
                    
                    let totalAmountData = parseFloat(totalAmount.getAttribute('data-amount'));
                    
                    if(!this.checked && (ele = addonBox.querySelector(`#addon${optionId}`))){ //if checkbox is unchecked and element already exists remove element
                        ele.remove();
                        if(!ele.length){ //check if element is deleted
                            if(!checkedCheckboxes.length){ // check if any of the child checkbox are checked
                                let indexToRemove = addonsId.indexOf(id);
                                if (indexToRemove !== -1) { //remove addon id
                                    addonsId.splice(indexToRemove, 1);
                                }
                            }
                            //delete addon option id from array
                            indexToRemove = addonsOptionId.indexOf(optionId);
                            if (indexToRemove !== -1) {
                                addonsOptionId.splice(indexToRemove, 1);
                            }
                        }
                        totalAmountData -= parseFloat(amount);
                    }else{ //create new element
                        createElement(addonBox, parseFloat(amount), optionId, parent)
                        
                        if(style.display == 'none') //if addon box is not visible make it visible
                            addonBox.style.display = 'block';

                        if(!addonsId.includes(id)) // push addon id
                            addonsId.push(id)
                    
                        if(!addonsOptionId.includes(optionId)) //push addon option id
                            addonsOptionId.push(optionId)
                        
                        totalAmountData += parseFloat(amount);
                    }

                    totalAmount.textContent = `{{Session::get('currencySymbol')}}${totalAmountData.toFixed(2)}`;
                    totalAmount.setAttribute('data-amount', totalAmountData.toFixed(2));
                    
                    if(!addonBox.querySelectorAll('[id^="addon"]').length) // if no addon is selected hide addon box
                        addonBox.style.display = 'none';

                });
            });

            
            function createElement(addonBox, totalPrice, optionId, parent){
                const parentDataTitle = parent.getAttribute('data-title');
                const totalPriceFormatted = `{{$cart_details->currency_code}} ${totalPrice.toFixed(2)}`;

                const containerDiv = document.createElement('div');
                containerDiv.setAttribute('id',`addon${optionId}`);
                containerDiv.setAttribute('class','inner_item d-flex justify-content-between align-items-center');
                const p = document.createElement('p');
                p.textContent = parentDataTitle;
                containerDiv.appendChild(p);

                const span = document.createElement('span');
                span.textContent = totalPriceFormatted;
                containerDiv.appendChild(span);

                addonBox.appendChild(containerDiv);
            }


            [bookingOption, protectionBox].forEach(element => {
                if(element){
                    element.addEventListener("click", function(e) {
                        let box = this.getAttribute('name');
                        const id = this.getAttribute('data-id');
                        if(box == 'booking'){
                            if(states.booking){
                                return false;
                            }
                            states.booking = true;
                            box = bookingBox;
                            bookingOptionId.push(id);
                        }else{
                            if(states.rentalProtection){
                                return false;
                            }
                            states.rentalProtection = true;
                            box = protectionAmountBox;
                            rentalProtectionId.push(id);
                        }
                        const amount = parseFloat(this.getAttribute('data-amount'));
                        const title = this.getAttribute('data-title');
                    
                        const protectionAmountParagraph = box.querySelector('p');
                        const protectionAmountSpan = box.querySelector('span');

                        protectionAmountParagraph.textContent = title;
                        protectionAmountSpan.textContent = `{{Session::get('currencySymbol')}}${amount.toFixed(2)}`;

                        const totalAmountData = parseFloat(totalAmount.getAttribute('data-amount'));
                        const newTotalAmount = totalAmountData + amount;
                        totalAmount.setAttribute('data-amount', newTotalAmount);
                        totalAmount.textContent = `{{Session::get('currencySymbol')}}${newTotalAmount.toFixed(2)}`;
                        box.style.pointerEvents = 'none';
                        box.style.display = 'block';
                    });
                }
            });


            //add the addons
            /*plus.forEach(element => {
                element.addEventListener("click", function(e) {
                    let parent = this.parentNode;
                    let id = parent.getAttribute('data-id');
                    let optionId = parent.getAttribute('data-option-id');
                    let amount = parseFloat(parent.getAttribute('data-amount')).toFixed(2);
                    let days = parent.getAttribute('data-days');
                    num = parent.querySelector('.num');
                    a = num.innerText;
                    let maxLimit = this.getAttribute('data-max')
                    if (a == maxLimit || a > maxLimit){
                        a = maxLimit;
                        return false;
                    }
                    a++;
                    totalPrice = amount * a;
                    
                    if(addonBox.querySelector(`#addon${optionId}`)){
                        addonBox.querySelector(`#addon${optionId}`).querySelector('p').textContent = parent.getAttribute('data-title') + ' x '+ a;
                        addonBox.querySelector(`#addon${optionId}`).querySelector('span').textContent = `{{Session::get('currencySymbol')}} ${totalPrice.toFixed(2)}`;
                    }else{
                        createElement(addonBox, totalPrice, optionId, a, parent)
                    }
                    
                    let totalAmountData = parseFloat(totalAmount.getAttribute('data-amount'));
                    totalAmountData += parseFloat(amount);
                    totalAmount.textContent = `{{Session::get('currencySymbol')}} ${totalAmountData.toFixed(2)}`;
                    totalAmount.setAttribute('data-amount', totalAmountData.toFixed(2));
                    num.innerText = a;
                    addonBox.style.display = 'block';
                    
                    if(!addonsId.includes(id))
                        addonsId.push(id)
                    if(!addonsOptionId.includes(optionId))
                        addonsOptionId.push(optionId)
                });
            });*/

            //remove the addons
            /*minus.forEach(element => {
                element.addEventListener("click", function(e) {
                    let parent = this.parentNode;
                    let id = parent.getAttribute('data-id');
                    let optionId = parent.getAttribute('data-option-id');
                    let amount = parseFloat(parent.getAttribute('data-amount')).toFixed(2);
                    let days = parent.getAttribute('data-days');
                    num = parent.querySelector('.num');
                    a = num.innerText;
                    let minLimit = this.getAttribute('data-min')
                    if (a < minLimit){
                        return false;
                    }
                    if (a > 0) {
                        a--;
                        totalPrice = amount * a;

                        if(addonBox.querySelector(`#addon${optionId}`)){
                            addonBox.querySelector(`#addon${optionId}`).querySelector('p').textContent = parent.getAttribute('data-title') + ' x '+ a;
                            addonBox.querySelector(`#addon${optionId}`).querySelector('span').textContent = `{{Session::get('currencySymbol')}} ${totalPrice.toFixed(2)}`;
                        }else{
                            createElement(addonBox, totalPrice, optionId, a, parent)
                        }
                        
                        let totalAmountData = parseFloat(totalAmount.getAttribute('data-amount'));
                        totalAmountData -= amount;
                        totalAmount.textContent = `{{Session::get('currencySymbol')}} ${totalAmountData.toFixed(2)}`;
                        totalAmount.setAttribute('data-amount', totalAmountData.toFixed(2));
                        num.innerText = a
                        addonBox.style.display = 'block';
                    }

                    if(a < 1){
                        addonBox.querySelector(`#addon${optionId}`).remove(); //Delete the element if count less than 0
                        
                        //delete addon id from array
                        var indexToRemove = addonsId.indexOf(id);
                        if (indexToRemove !== -1) {
                            addonsId.splice(indexToRemove, 1);
                        }
                        //delete addon option id from array
                        indexToRemove = addonsOptionId.indexOf(optionId);
                        console.log(indexToRemove)
                        if (indexToRemove !== -1) {
                            addonsOptionId.splice(indexToRemove, 1);
                        }
                    }
                    if(!addonBox.querySelectorAll('[id^="addon"]').length)
                        addonBox.style.display = 'none';
                });
            });*/
        </script>