@extends('layouts.store', ['title' => isset($page_title) ? $page_title :__('All Vendors')])
@section('css-links')
<link rel="stylesheet" href="{{asset('assets/css/intlTelInput.css')}}">
@endsection
@section('content')

@if(count($products) > 0)
<section class="section-b-space new-pages pb-265 ad al_new_all_venders">
   <div class="container">
      <div class="row">
         <div class="col-12" >
            <h2 class="mb-3 mt-3">{{ isset($page_title) ? $page_title :__('Recent View Product')}}</h2>
         </div>
      </div>
      <div class="row margin-res">
        @foreach($products as $product)
            <div class="col-md-3">
                <div class="deals-product product-card-box position-relative text-center al_custom_vendors_sec"  >
                    <a class="suppliers-box d-block" href="{{ $product["vendor"]->slug }}/product/{{ $product["url_slug"] }}">
                        <div class="suppliers-img-outer position-relative ">
                            <img  class="fluid-img mx-auto blur-up lazyload" data-src="{{ $product['image_url']  }}" alt="" title="">
                        </div>
                        <div class="supplier-rating">
                            <h4>{{  $product['title'] }}</h4>
                            
                            <h5>{{ (($product['discount_percentage'])>0)?$product['discount_percentage'].' %OFF':''}}</h5>
                            <a href="{{ $product["vendor"]->slug }}/product/{{ $product["url_slug"] }}">SHOP NOW</a>
                        </div>
                    </a>
                </div>
            </div>
        @endforeach
         <div class="col-12"  >
            <div class="pagination pagination-rounded justify-content-end mb-0">
               @if(!empty($products))
               {{ $products->links() }}
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
