@extends('layouts.store', ['title' => isset($page_title) ? $page_title :__('All Vendors')])
@section('css-links')
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
@endsection
@section('content')

@if(count($vendors) > 0)
<section class="section-b-space new-pages pb-265 ad al_new_all_venders">
   <div class="container">
      <div class="row">
         <div class="col-12" >
            <h2 class="mb-3 mt-3">{{ isset($page_title) ? $page_title :__('All Vendors')}}</h2>
         </div>
      </div>
      <div class="row margin-res">
         @foreach($vendors as $vendor)
         <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-3">
            <a class="suppliers-box d-block custom-vender-card" href="{{route('vendorDetail', $vendor->slug)}}">
               <div class="suppliers-img-outer">
                  <img class="fluid-img mx-auto blur-up lazyload" data-src="{{$vendor->logo['image_fit']}}200/200{{$vendor->logo['image_path']}}" alt="">

               </div>
               <div class="supplier-rating">
                  <h6 class="mb-1 ellips">{{$vendor->name}}</h6>
                  @if($client_preference_detail && $client_preference_detail->rating_check == 1 && $vendor->vendorRating > 0)
                  <span class="rating-number"><i class="fa fa-star"></i> {{$vendor->vendorRating}}</span>
                  @endif
                  <p title="{{$vendor->categoriesList}}" class="vendor-cate {{$vendor->is_show_vendor_details == 1 ? ' border-bottom':''}} pb-1 mb-1 ellips">{{$vendor->categoriesList}}</p>
                  <div class="product-timing">
                    @if ($vendor->is_show_vendor_details == 1)
                     <small title="{{$vendor->address}}" class="ellips d-block">
                     <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         width="20px" height="20px" viewBox="0 0 368.666 368.666" style="enable-background:new 0 0 368.666 368.666;" xml:space="preserve">
                        <g id="XMLID_2_">
                            <g>
                                <g>
                                    <path d="M184.333,0C102.01,0,35.036,66.974,35.036,149.297c0,33.969,11.132,65.96,32.193,92.515
                                        c27.27,34.383,106.572,116.021,109.934,119.479l7.169,7.375l7.17-7.374c3.364-3.46,82.69-85.116,109.964-119.51
                                        c21.042-26.534,32.164-58.514,32.164-92.485C333.63,66.974,266.656,0,184.333,0z M285.795,229.355
                                        c-21.956,27.687-80.92,89.278-101.462,110.581c-20.54-21.302-79.483-82.875-101.434-110.552
                                        c-18.228-22.984-27.863-50.677-27.863-80.087C55.036,78.002,113.038,20,184.333,20c71.294,0,129.297,58.002,129.296,129.297
                                        C313.629,178.709,304.004,206.393,285.795,229.355z"/>
                                    <path d="M184.333,59.265c-48.73,0-88.374,39.644-88.374,88.374c0,48.73,39.645,88.374,88.374,88.374s88.374-39.645,88.374-88.374
                                        S233.063,59.265,184.333,59.265z M184.333,216.013c-37.702,0-68.374-30.673-68.374-68.374c0-37.702,30.673-68.374,68.374-68.374
                                        s68.373,30.673,68.374,68.374C252.707,185.341,222.035,216.013,184.333,216.013z"/>
                                </g>
                            </g>
                        </g>
                        </svg>
                        {{$vendor->address}}</small>
                    @endif
                    @if(isset($vendor->timeofLineOfSightDistance))
                     <ul class="timing-box">
                        <li>
                           <small class="d-block">
                           <?xml version="1.0" encoding="iso-8859-1"?><svg style="height:16px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 368.666 368.666" style="enable-background:new 0 0 368.666 368.666;" xml:space="preserve"><g id="XMLID_2_"><g><g><path d="M184.333,0C102.01,0,35.036,66.974,35.036,149.297c0,33.969,11.132,65.96,32.193,92.515c27.27,34.383,106.572,116.021,109.934,119.479l7.169,7.375l7.17-7.374c3.364-3.46,82.69-85.116,109.964-119.51c21.042-26.534,32.164-58.514,32.164-92.485C333.63,66.974,266.656,0,184.333,0z M285.795,229.355c-21.956,27.687-80.92,89.278-101.462,110.581c-20.54-21.302-79.483-82.875-101.434-110.552c-18.228-22.984-27.863-50.677-27.863-80.087C55.036,78.002,113.038,20,184.333,20c71.294,0,129.297,58.002,129.296,129.297C313.629,178.709,304.004,206.393,285.795,229.355z"/><path d="M184.333,59.265c-48.73,0-88.374,39.644-88.374,88.374c0,48.73,39.645,88.374,88.374,88.374s88.374-39.645,88.374-88.374S233.063,59.265,184.333,59.265z M184.333,216.013c-37.702,0-68.374-30.673-68.374-68.374c0-37.702,30.673-68.374,68.374-68.374s68.373,30.673,68.374,68.374C252.707,185.341,222.035,216.013,184.333,216.013z"/></g></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
                           <!-- <img class="d-inline-block mr-1 blur-up lazyload" data-src="{{ asset('front-assets/images/distance.png') }}" alt="">  -->
                           <span>{{$vendor->lineOfSightDistance}}</span></small>
                        </li>
                        <li>
                           <small class="d-block">
                           <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                    width="16px" height="16px" viewBox="0 0 594.258 594.258" style="enable-background:new 0 0 594.258 594.258;"
                                    xml:space="preserve">
                                <g>
                                    <g>
                                        <path d="M506.877,87.381c-27.229-27.228-58.945-48.611-94.273-63.553C376.006,8.35,337.154,0.5,297.128,0.5
                                            c-40.025,0-78.877,7.85-115.475,23.329c-35.328,14.942-67.046,36.325-94.274,63.553c-27.228,27.228-48.61,58.946-63.552,94.273
                                            C8.349,218.252,0.5,257.103,0.5,297.129c0,40.025,7.849,78.877,23.328,115.475c14.942,35.328,36.325,67.047,63.553,94.273
                                            c27.228,27.229,58.946,48.611,94.273,63.553c36.598,15.48,75.449,23.328,115.475,23.328c40.025,0,78.877-7.848,115.475-23.328
                                            c35.328-14.941,67.047-36.324,94.273-63.553c27.229-27.227,48.611-58.945,63.553-94.273
                                            c15.48-36.598,23.328-75.449,23.328-115.475c0-40.026-7.848-78.877-23.328-115.475
                                            C555.486,146.328,534.105,114.609,506.877,87.381z M297.128,550.918c-140.163,0-253.789-113.625-253.789-253.789
                                            c0-140.164,113.626-253.789,253.789-253.789c140.163,0,253.79,113.626,253.79,253.789
                                            C550.918,437.293,437.291,550.918,297.128,550.918z"/>
                                        <path d="M297.129,594.258c-40.095,0-79.012-7.862-115.669-23.367c-35.386-14.967-67.158-36.385-94.432-63.66
                                            c-27.274-27.273-48.693-59.045-63.66-94.433C7.862,376.139,0,337.222,0,297.129c0-40.093,7.862-79.01,23.368-115.669
                                            c14.967-35.386,36.385-67.157,63.659-94.432c27.274-27.274,59.046-48.692,94.433-63.66C218.121,7.862,257.037,0,297.128,0
                                            s79.008,7.862,115.669,23.368c35.385,14.965,67.156,36.384,94.433,63.66c27.271,27.272,48.69,59.043,63.66,94.432
                                            c15.505,36.657,23.367,75.574,23.367,115.669c0,40.096-7.862,79.012-23.367,115.669c-14.967,35.388-36.385,67.159-63.66,94.433
                                            c-27.273,27.275-59.045,48.693-94.433,63.66C376.141,586.396,337.225,594.258,297.129,594.258z M297.128,1
                                            c-39.957,0-78.743,7.835-115.28,23.289c-35.268,14.917-66.933,36.263-94.115,63.446c-27.183,27.183-48.528,58.848-63.445,94.114
                                            C8.835,218.385,1,257.17,1,297.129c0,39.959,7.835,78.744,23.289,115.28c14.917,35.268,36.263,66.933,63.446,94.114
                                            c27.183,27.184,58.848,48.53,94.115,63.445c36.533,15.454,75.319,23.289,115.28,23.289s78.746-7.835,115.28-23.289
                                            c35.268-14.916,66.933-36.262,94.114-63.445c27.184-27.182,48.529-58.847,63.445-94.114
                                            c15.454-36.534,23.289-75.319,23.289-115.28s-7.835-78.747-23.289-115.28c-14.919-35.27-36.265-66.935-63.445-94.115
                                            c-27.185-27.185-58.85-48.531-94.114-63.446C375.871,8.835,337.085,1,297.128,1z M297.128,551.418
                                            c-67.923,0-131.78-26.45-179.809-74.479c-48.029-48.029-74.48-111.887-74.48-179.81s26.451-131.78,74.48-179.81
                                            C165.348,69.291,229.206,42.84,297.128,42.84c67.922,0,131.78,26.451,179.809,74.48c48.029,48.029,74.48,111.886,74.48,179.809
                                            s-26.451,131.78-74.48,179.81S365.051,551.418,297.128,551.418z M297.128,43.84c-67.656,0-131.262,26.347-179.102,74.187
                                            c-47.84,47.84-74.187,111.447-74.187,179.103c0,67.656,26.347,131.263,74.187,179.103c47.84,47.84,111.446,74.187,179.102,74.187
                                            s131.262-26.347,179.102-74.187c47.841-47.84,74.188-111.446,74.188-179.103c0-67.656-26.347-131.262-74.188-179.102
                                            C428.391,70.187,364.784,43.84,297.128,43.84z"/>
                                    </g>
                                    <g>
                                        <path d="M333.848,275.709c-8.436,0-15.299-6.863-15.299-15.3v-85.156c0-11.83-9.59-21.42-21.42-21.42
                                            c-11.83,0-21.42,9.59-21.42,21.42v85.156c0,32.058,26.081,58.14,58.139,58.14h129.328c11.83,0,21.42-9.59,21.42-21.42
                                            c0-11.83-9.59-21.42-21.42-21.42H333.848z"/>
                                        <path d="M463.176,319.049H333.848c-32.334,0-58.639-26.306-58.639-58.64v-85.156c0-12.087,9.833-21.92,21.92-21.92
                                            c12.087,0,21.92,9.833,21.92,21.92v85.156c0,8.161,6.639,14.8,14.799,14.8h129.328c12.087,0,21.92,9.833,21.92,21.92
                                            S475.263,319.049,463.176,319.049z M297.128,154.333c-11.535,0-20.92,9.385-20.92,20.92v85.156
                                            c0,31.783,25.857,57.64,57.639,57.64h129.328c11.535,0,20.92-9.385,20.92-20.92s-9.385-20.92-20.92-20.92H333.848
                                            c-8.712,0-15.799-7.088-15.799-15.8v-85.156C318.049,163.718,308.664,154.333,297.128,154.333z"/>
                                    </g>
                                </g>
                                </svg>

                            <!-- <i class="fa fa-clock-o"></i> -->
                                <span>{{$vendor->timeofLineOfSightDistance}} </span></small>
                        </li>
                     </ul>
                     @if($client_preference_detail->max_safety_mod == 1)
                     <div class="mt-2">
                        <ul class="timing-box_al">
                          <li><img height="30px" src="{{asset('images/max-safety.png')}}" alt=""></li>
                          <li>Follows all Max Safety measures to ensure your food is safe</li>
                        </ul>
                     </div>
                     @endif
                     @endif

                  </div>
                  @if($client_preference_detail)
                  @if($client_preference_detail->rating_check == 1)
                  @if($vendor->vendorRating > 0)
                  <ul class="custom-rating m-0 p-0 d-none">
                     @for($i=0; $i < 5; $i++)
                     @php
                     if($i <= $vendor->vendorRating){
                     $starFillClass = 'fa-star';
                     }else{
                     $starFillClass = 'fa-star-o';
                     }
                     @endphp
                     <li><i class="fa {{$starFillClass}}" aria-hidden="true"></i></li>
                     @endfor
                  </ul>
                  @endif
                  @endif
                  @endif
               </div>
            </a>
         </div>
         {{--<div class="col-md-3 col-6 col-grid-box mt-3">
            <div class="product-box scale-effect text-center">
               <div class="img-wrapper">
                  <div class="front">
                     <a href="{{route('vendorDetail')}}/{{ $vendor->slug }}">
                     <img class="img-fluid blur-up lazyloaded" data-src="{{ $vendor->logo['proxy_url'] .'200/200'. $vendor->logo['image_path'] }}" alt="">
                     </a>
                  </div>
               </div>
               <div class="product-detail">
                  <div class="inner_spacing text-center">
                     <a href="{{route('vendorDetail')}}/{{ $vendor->slug }}">
                        <h3>{{ $vendor->name }}</h3>
                        @if($client_preference_detail)
                        @if($client_preference_detail->rating_check == 1)
                        <div class="custom_rating">
                           @if($vendor->vendorRating > 0)
                           <span class="rating">{{$vendor->vendorRating}} <i class="fa fa-star text-white p-0"></i></span>
                           @endif
                        </div>
                        @endif
                        @endif
                     </a>
                  </div>
               </div>
            </div>
         </div>--}}
         @endforeach
         <div class="col-12"  >
            <div class="pagination pagination-rounded justify-content-end mb-0">
               @if(!empty($vendors))
               {{ $vendors->links() }}
               @endif
            </div>
         </div>
      </div>
   </div>
</section>
@else
<section class="no-store-wrapper mb-3">
   <div class="container">
      @if(count($for_no_product_found_html))
      @foreach($for_no_product_found_html as $key => $homePageLabel)
      @include('frontend.included_files.dynamic_page')
      @endforeach
      @else
      <div class="row">
         <div class="col-12 text-center">
            <img class="no-store-image mt-2 mb-2 blur-up lazyload" data-src="{{ asset('images/no-stores.svg') }}" style="max-height: 250px;">
         </div>
      </div>
      <div class="row">
         <div class="col-12 text-center mt-2">
            <h4>{{__('There are no stores available in your area currently.')}}</h4>
         </div>
      </div>
      @endif
   </div>
</section>
@endif
@endsection
@section('script')
<script src="{{asset('assets/js/intlTelInput.js')}}"></script>
<script src="{{asset('front-assets/js/jquery.exitintent.js')}}"></script>
<script src="{{asset('front-assets/js/fly-cart.js')}}"></script>
<script type="text/javascript">
   var text_image = "{{url('images/104647.png')}}";
   $(document).ready(function() {
       $.ajaxSetup({
           headers: {
               'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
           }
       });
       function getExtension(filename) {
           return filename.split('.').pop().toLowerCase();
       }
       $("#phone").keypress(function(e) {
           if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
               return false;
           }
           return true;
       });
       function readURL(input, previewId) {
           if (input.files && input.files[0]) {
               var reader = new FileReader();
               var extension = getExtension(input.files[0].name);
               reader.onload = function(e) {
                   if (extension == 'pdf') {
                       $(previewId).attr('src', 'https://image.flaticon.com/icons/svg/179/179483.svg');
                   } else if (extension == 'csv') {
                       $(previewId).attr('src', text_image);
                   } else if (extension == 'txt') {
                       $(previewId).attr('src', text_image);
                   } else if (extension == 'xls') {
                       $(previewId).attr('src', text_image);
                   } else if (extension == 'xlsx') {
                       $(previewId).attr('src', text_image);
                   } else {
                       $(previewId).attr('src', e.target.result);
                   }
               }
               reader.readAsDataURL(input.files[0]);
           }
       }
       $(document).on('change', '[id^=input_file_logo_]', function(event) {
           var rel = $(this).data('rel');
           // $('#plus_icon_'+rel).hide();
           readURL(this, '#upload_logo_preview_' + rel);
       });
       $("#input_file_logo").change(function() {
           readURL(this, '#upload_logo_preview');
       });
       $("#input_file_banner").change(function() {
           readURL(this, '#upload_banner_preview');
       });
       // var input = document.querySelector("#phone");
       // window.intlTelInput(input, {
       //     separateDialCode: true,
       //     hiddenInput: "full_number",
       //     utilsScript: "{{asset('assets/js/utils.js')}}",
       //     initialCountry: "{{ Session::get('default_country_code','US') }}",
       // });

       function initialize() {
           var input = document.getElementById('address');
           var autocomplete = new google.maps.places.Autocomplete(input);
           if(is_map_search_perticular_country){
                autocomplete.setComponentRestrictions({'country': [is_map_search_perticular_country]});
            }
           google.maps.event.addListener(autocomplete, 'place_changed', function() {
               var place = autocomplete.getPlace();
               document.getElementById('longitude').value = place.geometry.location.lng();
               document.getElementById('latitude').value = place.geometry.location.lat();
               for (let i = 1; i < place.address_components.length; i++) {
                   let mapAddress = place.address_components[i];
                   if (mapAddress.long_name != '') {
                       let streetAddress = '';
                       if (mapAddress.types[0] == "street_number") {
                           streetAddress += mapAddress.long_name;
                       }
                       if (mapAddress.types[0] == "route") {
                           streetAddress += mapAddress.short_name;
                       }
                       if ($('#street').length > 0) {
                           document.getElementById('street').value = streetAddress;
                       }
                       if (mapAddress.types[0] == "locality") {
                           document.getElementById('city').value = mapAddress.long_name;
                       }
                       if (mapAddress.types[0] == "administrative_area_level_1") {
                           document.getElementById('state').value = mapAddress.long_name;
                       }
                       if (mapAddress.types[0] == "postal_code") {
                           document.getElementById('pincode').value = mapAddress.long_name;
                       } else {
                           document.getElementById('pincode').value = '';
                       }
                       if (mapAddress.types[0] == "country") {
                           var country = document.getElementById('country');
                           for (let i = 0; i < country.options.length; i++) {
                               if (country.options[i].text.toUpperCase() == mapAddress.long_name.toUpperCase()) {
                                   country.value = country.options[i].value;
                                   break;
                               }
                           }
                       }
                   }
               }
           });
       }
       $('.iti__country').click(function() {
           var code = $(this).attr('data-country-code');
           $('#countryData').val(code);
           var dial_code = $(this).attr('data-dial-code');
           $('#dialCode').val(dial_code);
       });
       $('#register_btn').click(function() {
           var that = $(this);
           $(this).attr('disabled', true);
           $('#register_btn_loader').show();
           $('.form-control').removeClass("is-invalid");
           $('.invalid-feedback').children("strong").html('');
           var form = document.getElementById('vendor_signup_form');
           var formData = new FormData(form);
           $.ajax({
               type: "POST",
               data: formData,
               contentType: false,
               processData: false,
               url: "{{ route('vendor.register') }}",
               headers: {
                   Accept: "application/json"
               },
               success: function(data) {
                   $('#register_btn_loader').hide();
                   that.attr('disabled', false);
                   if (data.status == 'success') {
                       $('input[type=file]').val('');
                       $("#vendor_signup_form")[0].reset();
                       $('#vendor_signup_form img').attr('src', '');
                       $('html,body').animate({
                           scrollTop: '0px'
                       }, 1000);
                       $('#success_msg').html(data.message).show();
                       setTimeout(function() {
                           $('#success_msg').html('').hide();
                       }, 3000);
                   }
               },
               error: function(response) {
                   that.attr('disabled', false);
                   $('html,body').animate({
                       scrollTop: '0px'
                   }, 1000);
                   $('#register_btn_loader').hide();
                   if (response.status === 422) {
                       let errors = response.responseJSON.errors;
                       Object.keys(errors).forEach(function(key) {
                           $("#" + key + "Input input").addClass("is-invalid");
                           $("#" + key + "_error").children("strong").text(errors[key][0]).show();
                           $("#" + key + "Input div.invalid-feedback").show();
                       });
                   } else {
                       $(".show_all_error.invalid-feedback").show();
                       $(".show_all_error.invalid-feedback").text('Something went wrong, Please try Again.');
                   }
               }
           });
       });
   });
</script>
@endsection
