<style>
    li{list-style:none;}
    .userDetailsImage {
        background-color: #ddd;
        width: 50px;
        border-radius: 50px;
        height: 50px;
        overflow: hidden;
    }
    .fa-star{
        color: gold;
    }
    #productPriceModel .search input[type="datetime-local"] {
    width: 80%;
    margin: 0 !important;border-radius:  7px 0 0 7px;
}
#productPriceModel button.btn.btn-danger {
    border-radius: 50px;
}
li.userDetailsName.d-block {
    color: #000;
    font-weight: 500;
    font-size: 15px;
}
li.userDetailsJobDone.d-block {
    font-size: 12px;
}
li.userDetailsJobDone.d-block span.text-right {
    font-size: 14px;
    font-weight: 600;
}
.listofdrivers .card:hover {
    box-shadow: 0 6px 14px rgb(0 0 0 / 10%);
}
.listofdrivers .card{
  border-radius: 10px;
  cursor: pointer;
  overflow: hidden;
}
/* #productPriceModel .search button#search_Driver_fee {
    margin-top: -1px !important;
    padding-bottom: 8px;
    margin-left: -1px;
} */
.empty_driver_price {
    text-align: center;
}
.empty_driver_price h2 {
    font-size: 20px;
    color: #000;
}

.empty_driver_price p {
    font-size: 14px;
    line-height: 18px;
}
.price_list select {
    font-size: 12px;
    padding: 4px 0px  !important;
    border: 1px solid#dfd4d4;
    background: transparent;
    border-radius: 3px;
}
.address_list-child {
    border: 1px solid#dbd7d7;
    background: transparent;
    padding: 0px 4px;
    font-size: 14px;
    color: #524e4e;
}

#productPriceModel form.form-inline.my-2.my-lg-0.d-flex {
    display: flex;
    flex-wrap: wrap;
    padding-right: 12px;
}
#productPriceModel  form.form-inline.my-2.my-lg-0.d-flex > div,  #productPriceModel  form.form-inline.my-2.my-lg-0.d-flex select {
    width: 50%!important;
    margin: 0 auto 0!important;
}
#productPriceModel  form.form-inline.my-2.my-lg-0.d-flex select {
    background-color: #fff;
    min-height: 37px;
    border: 1px solid#dbd7d7;
    background: transparent;
    padding:0px 4px!important;
    font-size: 14px;
    color: #524e4e;
}

#productPriceModel form.form-inline.my-2.my-lg-0.d-flex > div {
    padding-right: 10px;
}
#productPriceModel .search button#search_Driver_fee {
    margin-top: -1px !important;
    padding-bottom: 8px;
    margin-left: -1px;
    margin: 10px auto 0!important;
}
#productPriceModel  form.form-inline.my-2.my-lg-0.d-flex > div input {
    width: 100%!important;
}
 #productPriceModel .search input[type="datetime-local"]{
  border-radius: 0;
}
    </style>
@php

  $user_id = auth()->user() ? auth()->user()->id : '';
  
  $client_timezone = \DB::table('clients')->first('timezone');
  $timezone = auth()->user() ? auth()->user()->timezone : $client_timezone->timezone;
  $minDate = \Carbon\Carbon::now()->setTimezone($timezone)->format('Y-m-d');
  $time    = \Carbon\Carbon::now()->setTimezone($timezone)->format('H:m');
  $address = \DB::table('user_addresses')->where('user_id', $user_id)->get();

@endphp

<div class="modal  fade" id="productPriceModel">
    <div class="modal-dialog  modal-dialog-centered "  >
      <div class="modal-content ">
        <input type="hidden" id='productPriceModel_todayDate' value="{{ $minDate }}">
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">{{ __('Prices') }}</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
  
        <!-- Modal body -->
        <div class="modal-body">
          <div class="col text-right pl-0 address_lista" >
              <select name="productPrice_address_id" id='productPrice_address_id'  class="address_list-child  select-2  p-1 w-100 mb-2">
                <option value="">{{ __('Select Address') }}</option>
                  @foreach ($address as $addres)
                  <option value="{{ $addres->id }}">{{ $addres->address }}</option>
                  @endforeach
              </select>
          </div>
            <div class="search border-bottom mb-3 pb-2">
              <form class="form-inline my-2 my-lg-0 d-flex">
                <div>
                <input class="form-control mr-sm-2" type="hidden" id="driver_product_variant_id">
                <input class="form-control mr-sm-2" type="date" time-zone="{{  $timezone  }}" min="{{ $minDate }}" value="{{ $minDate }}" id="onDemandBookingdate" placeholder="Search" aria-label="Search">
                </div>
                <select name="productPrice_slot" id='productPrice_slot'  class="productPrice_slot select-2 p-1 w-100 mb-2">
                 
                </select>
                <button class="btn btn-solid my-2 my-sm-0" id="search_Driver_fee">{{ __('Search') }}</button>
              </form>
          </div>
          <div class="col text-right pl-0 price_list" >
              <select name="order_type" id='driver_sort_by'  class="product_tag_filter select-2 p-1">
                <option value="">{{ __('Sort') }}</option>
                  
                  <option value="low_to_high">{{ __('Price : Low to High') }}</option>
                  <option value="high_to_low">{{ __('Price : High to Low') }}</option>
                  <option value="2">{{ __('Sort By Rating') }}</option>
              </select>
          </div>
          <div class="listofdrivers" id ="listofdrivers">
             
          </div>
        </div>
  
        <!-- Modal footer -->
        <div class="modal-footer">
        
        </div>
  
      </div>
    </div>
  </div>