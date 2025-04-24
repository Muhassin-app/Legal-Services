@php
if(isset($product)){
    $meta_data =  [
        'title' => (!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->title : '',
        'meta_title'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_title:'',
        'meta_keyword'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_keyword:'',
        'meta_description'=>(!empty($product->translation) && isset($product->translation[0])) ? $product->translation[0]->meta_description:'',
    ];
}else{
    $meta_data = [
        'title' => (!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->name : $category->slug,
        'meta_title'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_title:'',
        'meta_keyword'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_keyword:'',
        'meta_description'=>(!empty($category->translation) && isset($category->translation[0])) ? $category->translation[0]->meta_description:'',
    ];
}
@endphp

@extends('layouts.store',  $meta_data)
@section('content')
@php
use Illuminate\Support\Arr;
@endphp

@include('frontend.ondemand.ondemandSection');

<!-- remove_item_modal -->
<div class="modal fade remove-item-modal" id="remove_item_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_itemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0">
                <h5 class="modal-title" id="remove_itemLabel">{{__('Remove Item')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <input type="hidden" id="vendor_id" value="">
                <input type="hidden" id="cartproduct_id" value="">
                <input type="hidden" id="product_id" value="">
                <h6 class="m-0 px-3">{{__('Are You Sure You Want To Remove This Item?')}}</h6>
            </div>
            <div class="modal-footer flex-nowrap justify-content-center align-items-center">
                <button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{__('Cancel')}}</button>
                <button type="button" class="btn btn-solid" id="remove_product_button">{{__('Remove')}}</button>
            </div>
        </div>
    </div>
</div>
<!-- end remove_item_modal -->

<!-- payment section ----->

<script type="text/template" id="payment_method_template">
    <% _.each(payment_options, function(payment_option, k){%>
        <a class="nav-link <%= payment_option.slug == 'cash_on_delivery' ? 'active': ''%>" id="v-pills-<%= payment_option.slug %>-tab" data-toggle="pill" href="#v-pills-<%= payment_option.slug %>" role="tab" aria-controls="v-pills-wallet" aria-selected="true" data-payment_option_id="<%= payment_option.id %>"><%= payment_option.title %></a>
    <% }); %>
</script>
<script type="text/template" id="payment_method_tab_pane_template">
    <% _.each(payment_options, function(payment_option, k){%>
        <div class="tab-pane fade <%= payment_option.slug == 'cash_on_delivery' ? 'active show': ''%>" id="v-pills-<%= payment_option.slug %>" role="tabpanel" aria-labelledby="v-pills-<%= payment_option.slug %>-tab">
            <form method="POST" id="<%= payment_option.slug %>-payment-form">
            @csrf
            @method('POST')
                <div class="payment_response mb-3">
                    <div class="alert p-0" role="alert"></div>
                </div>
                <div class="form_fields">
                    <div class="row">
                        <div class="col-md-12">
                            <% if(payment_option.slug == 'stripe') { %>
                                <div class="form-control">
                                    <label class="d-flex flex-row pt-1 pb-1 mb-0">
                                        <div id="stripe-card-element"></div>
                                    </label>
                                </div>
                                <span class="error text-danger" id="stripe_card_error"></span>
                            <% } %>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-md-right">
                            <button type="button" class="btn btn-solid" data-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="button" class="btn btn-solid ml-1 proceed_to_pay">{{__('Place Order')}}</button>
                            <!-- <button type="button" class="btn btn-solid ml-1 proceed_to_pay">Scheduled Now</button> -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <% }); %>
</script>
<div class="modal fade" id="proceed_to_pay_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="pay-billLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="row no-gutters">
                    <div class="col-4">
                        <div class="nav flex-column nav-pills" id="v_pills_tab" role="tablist" aria-orientation="vertical"></div>
                    </div>
                    <div class="col-8">
                        <div class="tab-content-box px-3">
                            <div class="d-flex align-items-center justify-content-between pt-3">
                                <h5 class="modal-title" id="pay-billLabel">{{__('Total Amount')}}: <span id="total_amt"></span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="tab-content h-100" id="v_pills_tabContent">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/template" id="empty_cart_template">
    <div class="row">
        <div class="col-12 text-center pb-3">
            <img class="w-50 pt-3 pb-1" src="{{ asset('front-assets/images/ic_emptycart.svg') }}" alt="">
            <h5>{{ __('Your cart is empty') }}<br/>{{ __('Add an item to begin') }}</h5>
        </div>
    </div>
</script>
<!----- end payment section ------------->
@endsection

@section('script')
<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">

    var guest_cart = {{ $guest_user ? 1 : 0 }};
    var base_url = "{{url('/')}}";
    var place_order_url = "{{route('user.placeorder')}}";
    var payment_stripe_url = "{{route('payment.stripe')}}";
    var user_store_address_url = "{{route('address.store')}}";
    var promo_code_remove_url = "{{ route('remove.promocode') }}";
    var payment_paypal_url = "{{route('payment.paypalPurchase')}}";
    var update_qty_url = "{{ url('product/updateCartQuantity') }}";
    var promocode_list_url = "{{ route('verify.promocode.list') }}";
    var payment_option_list_url = "{{route('payment.option.list')}}";
    var apply_promocode_coupon_url = "{{ route('verify.promocode') }}";
    var payment_success_paypal_url = "{{route('payment.paypalCompletePurchase')}}";
    var getTimeSlotsForOndemand = "{{route('getTimeSlotsForOndemand')}}";
    var update_cart_schedule = "{{route('cart.updateSchedule')}}";
    var update_cart_product_schedule = "{{route('cart.updateProductSchedule')}}";
    var update_cart_product_schedule_agnet = "{{route('cart.updateDispatcherAgent')}}";
    var showCart = "{{route('showCart')}}";
    var update_addons_in_cart = "{{route('addToCartAddons')}}";
    var addonids = [];
    var addonoptids = [];


    $(document).on('click', '.showMapHeader', function(){
        var lats = document.getElementById('latitude').value;
        var lngs = document.getElementById('longitude').value;

        var myLatlng = new google.maps.LatLng(lats, lngs);
        var mapProp = {
            center:myLatlng,
            zoom:13,
            mapTypeId:google.maps.MapTypeId.ROADMAP

        };
        var map=new google.maps.Map(document.getElementById("pick-address-map"), mapProp);
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            draggable:true
        });
        // marker drag event
        google.maps.event.addListener(marker,'drag',function(event) {
            document.getElementById('latitude').value = event.latLng.lat();
            document.getElementById('longitude').value = event.latLng.lng();
        });
        //marker drag event end
        google.maps.event.addListener(marker,'dragend',function(event) {
            document.getElementById('latitude').value = event.latLng.lat();
            document.getElementById('longitude').value = event.latLng.lng();
        });
        $('#pick_address').modal('show');
    });

</script>

<script>
      $(document).ready(function() {

        $('#main-nav-list').onePageNav({
          scrollThreshold: 0.2, // Adjust if Navigation highlights too early or too late
          scrollOffset: 75 //Height of Navigation Bar
        });
        var top = 0;
        // Sticky Header - http://jqueryfordesigners.com/fixed-floating-elements/

        if( $( "#mydiv").length > 0){
          top = $('#main-nav').offset().top - parseFloat($('#main-nav').css('margin-top').replace(/auto/, 0));
        }

        $(window).scroll(function (event) {
          // what the y position of the scroll is
          var y = $(this).scrollTop();

          // whether that's below the form
          if (y >= top) {
            // if so, ad the fixed class
            $('#main-nav').addClass('fixed');
          } else {
            // otherwise remove it
            $('#main-nav').removeClass('fixed');
          }
        });

      });

      ;(function($, window, document, undefined){

        // our plugin constructor
        var OnePageNav = function(elem, options){
          this.elem = elem;
          this.$elem = $(elem);
          this.options = options;
          this.metadata = this.$elem.data('plugin-options');
          this.$nav = this.$elem.find('a');
          this.$win = $(window);
          this.sections = {};
          this.didScroll = false;
          this.$doc = $(document);
          this.docHeight = this.$doc.height();
        };

        // the plugin prototype
        OnePageNav.prototype = {
          defaults: {
            currentClass: 'current',
            changeHash: false,
            easing: 'swing',
            filter: '',
            scrollSpeed: 250,
            scrollOffset: 0,
            scrollThreshold: 0.5,
            begin: false,
            end: false,
            scrollChange: false
          },

          init: function() {
            var self = this;

            // Introduce defaults that can be extended either
            // globally or using an object literal.
            self.config = $.extend({}, self.defaults, self.options, self.metadata);

            //Filter any links out of the nav
            if(self.config.filter !== '') {
              self.$nav = self.$nav.filter(self.config.filter);
            }

            //Handle clicks on the nav
            self.$nav.on('click.onePageNav', $.proxy(self.handleClick, self));

            //Get the section positions
            self.getPositions();

            //Handle scroll changes
            self.bindInterval();

            //Update the positions on resize too
            self.$win.on('resize.onePageNav', $.proxy(self.getPositions, self));

            return this;
          },

          adjustNav: function(self, $parent) {
            self.$elem.find('.' + self.config.currentClass).removeClass(self.config.currentClass);
            $parent.addClass(self.config.currentClass);
          },

          bindInterval: function() {
            var self = this;
            var docHeight;

            self.$win.on('scroll.onePageNav', function() {
              self.didScroll = true;
            });

            self.t = setInterval(function() {
              docHeight = self.$doc.height();

              //If it was scrolled
              if(self.didScroll) {
                self.didScroll = false;
                self.scrollChange();
              }

              //If the document height changes
              if(docHeight !== self.docHeight) {
                self.docHeight = docHeight;
                self.getPositions();
              }
            }, 250);
          },

          getHash: function($link) {
            return $link.attr('href').split('#')[1];
          },

          getPositions: function() {
            var self = this;
            var linkHref;
            var topPos;
            var $target;

            self.$nav.each(function() {
              linkHref = self.getHash($(this));
              $target = $('#' + linkHref);

              if($target.length) {
                topPos = $target.offset().top;
                self.sections[linkHref] = Math.round(topPos) - self.config.scrollOffset;
              }
            });
          },

          getSection: function(windowPos) {
            var returnValue = null;
            var windowHeight = Math.round(this.$win.height() * this.config.scrollThreshold);

            for(var section in this.sections) {
              if((this.sections[section] - windowHeight) < windowPos) {
                returnValue = section;
              }
            }

            return returnValue;
          },

          handleClick: function(e) {
            var self = this;
            var $link = $(e.currentTarget);
            var $parent = $link.parent();
            var newLoc = '#' + self.getHash($link);

            if(!$parent.hasClass(self.config.currentClass)) {
              //Start callback
              if(self.config.begin) {
                self.config.begin();
              }

              //Change the highlighted nav item
              self.adjustNav(self, $parent);

              //Removing the auto-adjust on scroll
              self.unbindInterval();

              //Scroll to the correct position
              $.scrollTo(newLoc, self.config.scrollSpeed, {
                axis: 'y',
                easing: self.config.easing,
                offset: {
                  top: -self.config.scrollOffset
                },
                onAfter: function() {
                  //Do we need to change the hash?
                  if(self.config.changeHash) {
                    window.location.hash = newLoc;
                  }

                  //Add the auto-adjust on scroll back in
                  self.bindInterval();

                  //End callback
                  if(self.config.end) {
                    self.config.end();
                  }
                }
              });
            }

            e.preventDefault();
          },

          scrollChange: function() {
            var windowTop = this.$win.scrollTop();
            var position = this.getSection(windowTop);
            var $parent;

            //If the position is set
            if(position !== null) {
              $parent = this.$elem.find('a[href$="#' + position + '"]').parent();

              //If it's not already the current section
              if(!$parent.hasClass(this.config.currentClass)) {
                //Change the highlighted nav item
                this.adjustNav(this, $parent);

                //If there is a scrollChange callback
                if(this.config.scrollChange) {
                  this.config.scrollChange($parent);
                }
              }
            }
          },

          unbindInterval: function() {
            clearInterval(this.t);
            this.$win.unbind('scroll.onePageNav');
          }
        };

        OnePageNav.defaults = OnePageNav.prototype.defaults;

        $.fn.onePageNav = function(options) {
          return this.each(function() {
            new OnePageNav(this, options).init();
          });
        };

      })( jQuery, window , document );
    </script>




@endsection
