<script type="text/template" id="payment_method_template">
    <% _.each(payment_options, function(payment_option, k){%>
        <a class="nav-link <%= payment_option.slug == 'cash_on_delivery' ? 'active': ''%>" id="v-pills-<%= payment_option.slug %>-tab" data-toggle="pill" href="#v-pills-<%= payment_option.slug %>" role="tab" aria-controls="v-pills-wallet" aria-selected="true" data-payment_option_id="<%= payment_option.id %>"><%= payment_option.title %></a>
    <% }); %>
</script>
<script type="text/template" id="payment_method_tab_pane_template">
    <% if(payment_options == '') { %>
        <h6>{{__('Payment Options Not Avaialable')}}</h6>
    <% }else{ %>
        <div class="modal-body pb-0">
            <h5 class="text-17 mb-2">{{__('Debit From')}}</h5>
            <form method="POST" id="cart_payment_form">
                @csrf
                @method('POST')
                <% _.each(payment_options, function(payment_option, k){%>
                    <div class="" id="" role="tabpanel">
                        <label class="radio mt-2">
                            <%= payment_option.title %>
                            <input type="radio" name="cart_payment_method" id="radio-<%= payment_option.slug %>" value="<%= payment_option.id %>" data-payment_option_id="<%= payment_option.id %>">
                            <span class="checkround"></span>
                        </label>
                        <% if(payment_option.slug == 'stripe') { %>
                            <div class="col-md-12 mt-3 mb-3 stripe_element_wrapper option-wrapper d-none">
                                <div class="form-control">
                                    <label class="mb-0">
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
                            <div class="col-md-12 mt-3 mb-3">
                                <div id="pp-button"></div>
                            </div>
                        <% } %>

                        <% if(payment_option.slug == 'plugnpay') { %>
                            <div class="col-md-12 mt-3 mb-3 plugnpay_element_wrapper option-wrapper d-none">
                                <div class="row no-gutters">
                                    <div class="col-12">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-name-element" placeholder="Enter card holder name" />
                                    </div>
                                    <div class="col-6">
                                        <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="plugnpay-card-element" placeholder="Enter card Number" />
                                    </div>
                                    <div class="col-3">
                                        <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="5"  id="plugnpay-date-element" placeholder="MM/YY" />
                                    </div>
                                    <div class="col-3">
                                        <input type="password" max="3" style=" border-left: none;"  class="form-control" id="plugnpay-cvv-element" placeholder="CVV" />
                                    </div>
                                     <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-addr1-element" placeholder="Enter address"/>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-addr2-element" placeholder="Enter alternate address (optional)" />
                                    </div>
                                    <div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-zip-element" placeholder="Enter zip code"/>
                                    </div>
<div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-city-element" placeholder="Enter city name"/>
                                    </div>
<div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-state-element" placeholder="Enter state code e.g. NY"/>
                                    </div>
<div class="col-6">
                                        <input type="text" min="4" max="32" style=" border-right: none;" class="form-control" id="plugnpay-country-element" placeholder="Enter country code e.g. US"/>
                                    </div>
                                </div>

                                <span class="error text-danger" id="plugnpay_card_error"></span>
                            </div>
                        <% } %>
                         <% if(payment_option.slug == 'azulpay') { %>
                    <div class="col-md-12 mt-3 mb-3 azulpay_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="number" min="16" max="16" style=" border-right: none;" class="form-control" id="azul-card-element" placeholder="Enter card Number" required />
                            </div>
                            <div class="col-3">
                                <input type="text" style=" border-left: none; border-right: none;" class="form-control" max="6"  id="azul-date-element" placeholder="YYYYMM" required />
                            </div>
                            <div class="col-3">
                                <input type="password" max="4" style=" border-left: none;"  class="form-control" id="azul-cvv-element" placeholder="CVV" required />
                            </div>
                        </div>

                        <span class="error text-danger" id="azul_card_error"></span>
                    </div>
                <% } %>

                <% if(payment_option.slug == 'powertrans') { %>
                    <div class="col-md-12 mt-3 mb-3 powertrans_element_wrapper option-wrapper d-none">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <input type="number" min="16" maxlength="16" style=" border-right: none;" class="form-control" id="card-element-powertrans" placeholder="Enter card Number" required 
                                oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                            </div>
                            <div class="col-3">
                                <input type="number" style=" border-left: none; border-right: none;" class="form-control" maxLength="4"  id="date-element-powertrans" placeholder="YYMM" required />
                            </div>
                            <div class="col-3">
                                <input type="password" maxLength="4" style=" border-left: none;"  class="form-control" id="cvv-element-powertrans" placeholder="CVV" required />
                            </div>
                        </div>

                        <span class="error text-danger" id="card_error_powertrans"></span>
                    </div>
                <% } %>

                    </div>
                <% }); %>
                {{-- <div class="" id="" role="tabpanel">
                    <label class="radio mt-2">
                        Apple Pay
                        <input type="radio" name="cart_payment_method" id="radio-paytab_apple_pay" value="100" data-payment_option_id="100">
                        <span class="checkround"></span>
                    </label>
                    <div class="col-md-12 mt-3 mb-3 paytab_apple_pay_element_wrapper option-wrapper d-none">
                        <button type="button" id="applepay-btn">Pay Now</button>
                        <span class="error text-danger" id="paytab_apple_pay_error"></span>
                    </div>
                </div> --}}
                <div class="payment_response">
                    <div class="alert p-0 m-0" role="alert"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer d-block text-center pt-0">
            <div class="row">
                <div class="col-sm-12 p-0 d-flex flex-fill">
                    <button type="button" style="width:100%;" class="btn btn-solid ml-1 extend_order_proceed_to_pay">{{__('Extend Order')}}
                        <img style="width:5%; display:none;" id="proceed_to_pay_loader" src="{{asset('assets/images/loader.gif')}}"/>
                    </button>
                </div>
            </div>
        </div>
    <% } %>
</script>
<div class="modal fade" id="proceed_to_pay_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="pay-billLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="pay-billLabel">Total Amount: <span id="total_amt"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div id="v_pills_tabContent">
                
            </div>
        </div>
    </div>
</div>
