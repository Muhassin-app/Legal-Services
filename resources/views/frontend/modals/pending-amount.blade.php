<div class="modal fade" id="pending_amount_modal" tabindex="-1" aria-labelledby="topup_walletLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-bottom">
          <h5 class="modal-title text-17 mb-0 mt-0" id="topup_walletLabel">Pending Amount</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="" id="wallet_topup_form">
          @csrf
          @method('POST')
          <div class="modal-body pb-0">
              <div class="form-group">
                  <div class="text-36">{{Session::get('currencySymbol')}}<span class="wallet_balance"></span></div>
              </div>

              <div class="form-group">

                  <input class="form-control" name="amount_pending" id="amount_pending" type="hidden" placeholder="Enter Amount">
                  <input type="hidden" name="cart_tip_amount" id="cart_tip_amount" value="0">
                  <input type="hidden" name="order_number" id="order_number" value="0">
                  <span class="error-msg" id="amount_error_pending"></span>
              </div>
               <hr class="mt-0 mb-1" />
              <div class="payment_response">
                  <div class="alert p-0 m-0" role="alert"></div>
              </div>
              <h5 class="text-17 mb-2">{{__('Debit From')}}</h5>
              <div class="form-group" id="payment_methods_pending">
              </div>
              <span class="error-msg" id="payment_methods_error_pending"></span>
          </div>
          <div class="modal-footer d-block text-center">
              <div class="row">
                  <div class="col-sm-12 p-0 d-flex justify-space-around">
                      <button type="button" class="btn btn-block btn-solid mr-1 mt-2 topup_wallet_confirm_pending">{{__('Submit')}}</button>
                      <button type="button" class="btn btn-block btn-solid ml-1 mt-2" data-dismiss="modal">{{__('Cancel')}}</button>
                  </div>
              </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script type="text/template" id="payment_method_template">
      <% if(payment_options == '') { %>
          <h6>{{__('Payment Options Not Avaialable')}}</h6>
      <% }else{ %>
          <% _.each(payment_options, function(payment_option, k){%>
              <% if( (payment_option.slug != 'cash_on_delivery') && (payment_option.slug != 'loyalty_points') ) { %>
                  <label class="radio mt-2">
                      <%= payment_option.title %>
                      <input type="radio" name="wallet_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.slug %>" data-payment_option_id="<%= payment_option.id %>">
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
