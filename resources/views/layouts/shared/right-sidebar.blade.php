<!-- Right Sidebar -->
<div class="right-bar">
  <div data-simplebar class="h-100">

      <!-- Nav tabs -->
      <ul class="nav nav-tabs nav-bordered nav-justified" role="tablist">
          <!-- <li class="nav-item">
              <a class="nav-link py-2" data-toggle="tab" href="#chat-tab" role="tab">
                  <i class="mdi mdi-message-text d-block font-22 my-1"></i>
              </a>
          </li>
          <li class="nav-item">
              <a class="nav-link py-2" data-toggle="tab" href="#tasks-tab" role="tab">
                  <i class="mdi mdi-format-list-checkbox d-block font-22 my-1"></i>
              </a>
          </li> -->
          <li class="nav-item">
              <a class="nav-link py-2 active" data-toggle="tab" href="#settings-tab" role="tab">
                  <i class="mdi mdi-cog-outline d-block font-22 my-1"></i>
              </a>
          </li>
      </ul>

      <!-- Tab panes -->
      <div class="tab-content pt-0">
          <div class="tab-pane" id="chat-tab" role="tabpanel">

              <form class="search-bar p-3">
                  <div class="position-relative">
                      <input type="text" class="form-control" placeholder="{{ __('Search') }}...">
                      <span class="mdi mdi-magnify"></span>
                  </div>
              </form>

              <h6 class="font-weight-medium px-3 mt-2 text-uppercase">Group Chats</h6>

              <div class="p-2">
                  <a href="javascript: void(0);" class="text-reset notification-item pl-3 mb-2 d-block">
                      <i class="mdi mdi-checkbox-blank-circle-outline mr-1 text-success"></i>
                      <span class="mb-0 mt-1">App Development</span>
                  </a>

                  <a href="javascript: void(0);" class="text-reset notification-item pl-3 mb-2 d-block">
                      <i class="mdi mdi-checkbox-blank-circle-outline mr-1 text-warning"></i>
                      <span class="mb-0 mt-1">Office Work</span>
                  </a>

                  <a href="javascript: void(0);" class="text-reset notification-item pl-3 mb-2 d-block">
                      <i class="mdi mdi-checkbox-blank-circle-outline mr-1 text-danger"></i>
                      <span class="mb-0 mt-1">Personal Group</span>
                  </a>

                  <a href="javascript: void(0);" class="text-reset notification-item pl-3 d-block">
                      <i class="mdi mdi-checkbox-blank-circle-outline mr-1"></i>
                      <span class="mb-0 mt-1">Freelance</span>
                  </a>
              </div>

              <h6 class="font-weight-medium px-3 mt-3 text-uppercase">Favourites <a href="javascript: void(0);" class="font-18 text-danger"><i class="float-right mdi mdi-plus-circle"></i></a></h6>

              <div class="p-2">

              </div>

              <h6 class="font-weight-medium px-3 mt-3 text-uppercase">Other Chats <a href="javascript: void(0);" class="font-18 text-danger"><i class="float-right mdi mdi-plus-circle"></i></a></h6>

              <div class="p-2 pb-4">


                  <div class="text-center mt-3">
                      <a href="javascript:void(0);" class="btn btn-sm btn-white">
                          <i class="mdi mdi-spin mdi-loading mr-2"></i>
                          Load more
                      </a>
                  </div>
              </div>

          </div>

          <div class="tab-pane" id="tasks-tab" role="tabpanel">
              <h6 class="font-weight-medium p-3 m-0 text-uppercase">Working Tasks</h6>
              <div class="px-2">
                  <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                      <p class="text-muted mb-0">App Development<span class="float-right">75%</span></p>
                      <div class="progress mt-2" style="height: 4px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                  </a>

                  <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                      <p class="text-muted mb-0">Database Repair<span class="float-right">37%</span></p>
                      <div class="progress mt-2" style="height: 4px;">
                          <div class="progress-bar bg-info" role="progressbar" style="width: 37%" aria-valuenow="37" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                  </a>

                  <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                      <p class="text-muted mb-0">Backup Create<span class="float-right">52%</span></p>
                      <div class="progress mt-2" style="height: 4px;">
                          <div class="progress-bar bg-warning" role="progressbar" style="width: 52%" aria-valuenow="52" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                  </a>
              </div>

              <h6 class="font-weight-medium px-3 mb-0 mt-4 text-uppercase">Upcoming Tasks</h6>

              <div class="p-2">
                  <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                      <p class="text-muted mb-0">Sales Reporting<span class="float-right">12%</span></p>
                      <div class="progress mt-2" style="height: 4px;">
                          <div class="progress-bar bg-danger" role="progressbar" style="width: 12%" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                  </a>

                  <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                      <p class="text-muted mb-0">Redesign Website<span class="float-right">67%</span></p>
                      <div class="progress mt-2" style="height: 4px;">
                          <div class="progress-bar bg-primary" role="progressbar" style="width: 67%" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                  </a>

                  <a href="javascript: void(0);" class="text-reset item-hovered d-block p-2">
                      <p class="text-muted mb-0">New Admin Design<span class="float-right">84%</span></p>
                      <div class="progress mt-2" style="height: 4px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 84%" aria-valuenow="84" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                  </a>
              </div>

              <div class="p-3 mt-2">
                  <a href="javascript: void(0);" class="btn btn-success btn-block waves-effect waves-light">Create Task</a>
              </div>

          </div>
          <div class="tab-pane active" id="settings-tab" role="tabpanel">
              <h6 class="font-weight-medium px-3 m-0 py-2 font-13 text-uppercase bg-light">
                  <span class="d-block py-1">Theme Settings</span>
              </h6>

              <div class="p-3">
                  <div class="alert alert-warning" role="alert">
                      <strong>Customize </strong> the overall color scheme, sidebar menu, etc.
                  </div>

                  <h6 class="font-weight-medium font-14 mt-4 mb-2 pb-1">Color Scheme</h6>
                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="color-scheme-mode" value="light"
                          id="light-mode-check" checked />
                      <label class="custom-control-label" for="light-mode-check">Light Mode</label>
                  </div>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="color-scheme-mode" value="dark"
                          id="dark-mode-check" />
                      <label class="custom-control-label" for="dark-mode-check">Dark Mode</label>
                  </div>

                  <!-- Width -->
                  <h6 class="font-weight-medium font-14 mt-4 mb-2 pb-1">Width</h6>
                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="width" value="fluid" id="fluid-check" checked />
                      <label class="custom-control-label" for="fluid-check">Fluid</label>
                  </div>
                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="width" value="boxed" id="boxed-check" />
                      <label class="custom-control-label" for="boxed-check">Boxed</label>
                  </div>

                  <!-- Menu positions -->
                  <h6 class="font-weight-medium font-14 mt-4 mb-2 pb-1">Menus (Leftsidebar and Topbar) Positon</h6>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="menus-position" value="fixed" id="fixed-check"
                          checked />
                      <label class="custom-control-label" for="fixed-check">Fixed</label>
                  </div>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="menus-position" value="scrollable"
                          id="scrollable-check" />
                      <label class="custom-control-label" for="scrollable-check">Scrollable</label>
                  </div>

                  <!-- Left Sidebar-->
                  <h6 class="font-weight-medium font-14 mt-4 mb-2 pb-1">Left Sidebar Color</h6>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="leftsidebar-color" value="light" id="light-check" checked />
                      <label class="custom-control-label" for="light-check">Light</label>
                  </div>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="leftsidebar-color" value="dark" id="dark-check" />
                      <label class="custom-control-label" for="dark-check">Dark</label>
                  </div>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="leftsidebar-color" value="brand" id="brand-check" />
                      <label class="custom-control-label" for="brand-check">Brand</label>
                  </div>

                  <div class="custom-control custom-switch mb-3">
                      <input type="radio" class="custom-control-input" name="leftsidebar-color" value="gradient" id="gradient-check" />
                      <label class="custom-control-label" for="gradient-check">Gradient</label>
                  </div>

                  <!-- size -->
                  <h6 class="font-weight-medium font-14 mt-4 mb-2 pb-1">Left Sidebar Size</h6>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="leftsidebar-size" value="default"
                          id="default-size-check" checked />
                      <label class="custom-control-label" for="default-size-check">Default</label>
                  </div>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="leftsidebar-size" value="condensed"
                          id="condensed-check" />
                      <label class="custom-control-label" for="condensed-check">Condensed <small>(Extra Small size)</small></label>
                  </div>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="leftsidebar-size" value="compact"
                          id="compact-check" />
                      <label class="custom-control-label" for="compact-check">Compact <small>(Small size)</small></label>
                  </div>

                  <!-- User info -->
                  <h6 class="font-weight-medium font-14 mt-4 mb-2 pb-1">Sidebar User Info</h6>

                  <div class="custom-control custom-switch mb-1">
                      <input type="checkbox" class="custom-control-input" name="leftsidebar-user" value="fixed" id="sidebaruser-check" />
                      <label class="custom-control-label" for="sidebaruser-check">Enable</label>
                  </div>


                  <!-- Topbar -->
                  <h6 class="font-weight-medium font-14 mt-4 mb-2 pb-1">Topbar</h6>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="topbar-color" value="dark" id="darktopbar-check"
                          checked />
                      <label class="custom-control-label" for="darktopbar-check">Dark</label>
                  </div>

                  <div class="custom-control custom-switch mb-1">
                      <input type="radio" class="custom-control-input" name="topbar-color" value="light" id="lighttopbar-check" />
                      <label class="custom-control-label" for="lighttopbar-check">Light</label>
                  </div>


                  <button class="btn btn-primary btn-block mt-4" id="resetBtn">Reset to Default</button>

                  <a href="https://1.envato.market/uboldadmin"
                      class="btn btn-danger btn-block mt-3" target="_blank"><i class="mdi mdi-basket mr-1"></i> Purchase Now</a>

              </div>

          </div>
      </div>

  </div> <!-- end slimscroll-menu-->
</div>
<!-- /Right-bar -->

<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>

<!-- Modal -->
<div class="modal fade received-orders" id="reached_location_new_order" tabindex="-1" aria-labelledby="received_ordersLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content py-md-5 px-md-4 p-sm-3 p-4">
            <i class="fa fa-bell fa-2x text-center my-3" style="color:blue"></i>
            <div class="modal-body pt-0 text-center">
                <h5 class="modal-title text-center">{{__('Cutomer Notification')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5>{{__('Cutomer arrived at your location')}}</h5>
                <h5>{{__('Order Number : ')}} <b id="orderNo"></b></h5>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade received-orders order_received1" id="received_new_orders" tabindex="-1" aria-labelledby="received_ordersLabel" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="received_ordersLabel">{{__('New Order Received')}}</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-0">

            </div>
        </div>
    </div>
</div>


<div class="modal fade bd-example-modal-lg" id="vendor_order_product_price_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __('Update Product Price') }}</h4><br>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="update-order-product-price-form" class="p-3">
                @csrf
                <input type="hidden" name="or_vend_prod_id" id="or-vend-prod-id">
                <input type="hidden" name="or_prod_old_price" id="or-prod-old-price">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('title', __('New Price'),['class' => 'control-label']) !!}
                            <input class="form-control" placeholder="New Price" name="product_price" type="number" min=0 required />
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group" id="pushBody">
                            <label for="push_body" class="control-label">{{__("Reason")}}</label>
                            <textarea class="txtarea form-control" rows="3" required placeholder={{__("Reason")}} name="update_price_reason" type="text" id="update_price_reason"></textarea>
                            <span class="invalid-feedback" role="alert">
                                <strong></strong>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-info waves-effect waves-light submitOrderUpdatedPriceByVendor">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- till here --}}

<script type="text/template" id="latest_order_template">

    <div class="row">
        <% _.each(orders, function(order, k){%>
            <% if(order.vendors.length !== 0) { %>
                <div class="col-xl-12"  id="latest_full_order_div<%= k %>">
                    <% _.each(order.vendors, function(vendor, ve){%>
                        <div class="row  <%= ve ==0 ? 'mt-0' : 'mt-2'%>" id="latest_single_order_div<%= k %><%= ve %>">
                            <div class="col-12 order-hover-btn">

                                <div id="update-single-status" class="my-2">
                                    <% if(vendor.order_status_option_id == 1) { %>
                                        <button class="update_order_status btn-info" data-full_div="#latest_full_order_div<%= k %>"  data-single_div="#latest_single_order_div<%= k %><%= ve %>" data-count="<%= ve %>" data-order_id="<%= order.id %>"  data-vendor_id="<%= vendor.vendor_id %>"  data-status_option_id="2" data-order_vendor_id="<%= vendor.order_vendor_id %>">{{ __('Accept') }}</button>
                                        <button class="update_order_status btn-danger" data-full_div="#latest_full_order_div<%= k %>"  data-single_div="#latest_single_order_div<%= k %><%= ve %>"  data-count="<%= ve %>"   data-order_id="<%= order.id %>"  data-vendor_id="<%= vendor.vendor_id %>" data-status_option_id="3" data-order_vendor_id="<%= vendor.order_vendor_id %>">{{ __("Reject") }}</button>
                                        <% } else if(vendor.order_status_option_id == 2) { %>
                                            <button class="update_order_status btn-warning" data-full_div="#latest_full_order_div<%= k %>"  data-single_div="#latest_single_order_div<%= k %><%= ve %>"  data-count="<%= ve %>"  data-order_id="<%= order.id %>"  data-vendor_id="<%= vendor.vendor_id %>"  data-status_option_id="4" data-order_vendor_id="<%= vendor.order_vendor_id %>">{{ __("Processing") }}</button>
                                        <% } else if(vendor.order_status_option_id == 4) { %>
                                                <button class="update_order_status btn-success" data-full_div="#latest_full_order_div<%= k %>"  data-single_div="#latest_single_order_div<%= k %><%= ve %>"  data-count="<%= ve %>"  data-order_id="<%= order.id %>"  data-vendor_id="<%= vendor.vendor_id %>"  data-status_option_id="5" data-order_vendor_id="<%= vendor.order_vendor_id %>">{{ __("Out For Delivery") }}</button>
                                        <% } else if(vendor.order_status_option_id == 5) { %>
                                            <button class="update_order_status btn-info" data-full_div="#latest_full_order_div<%= k %>"  data-single_div="#latest_single_order_div<%= k %><%= ve %>"  data-count="<%= ve %>"  data-order_id="<%= order.id %>"  data-vendor_id="<%= vendor.vendor_id %>"  data-status_option_id="6" data-order_vendor_id="<%= vendor.order_vendor_id %>">{{ __("Delivered") }}</button>
                                        <% } else { %>

                                    <% } %>
                                </div>

                                <a href="<%= vendor.vendor_detail_url %>" class="row order_detail order_detail_data align-items-top pb-1 mb-0 card-box no-gutters h-100">
                                    <span class="left_arrow pulse">
                                    </span>
                                    <div class="col-5 col-sm-3">
                                        <h5 class="m-0"><%= vendor.vendor_name %></h5>
                                        <ul class="status_box mt-1 pl-0">
                                            <li>
                                                <img src="{{ asset('assets/images/order-icon.svg') }}" alt="">
                                                <label class="m-0 in-progress"><%= vendor.order_status %></label>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-7 col-sm-6">
                                        <div class="row no-gutters product_list align-items-top flex-wrap">
                                            <% _.each(vendor.products, function(product, pr){%>
                                                <div class="col-4 text-center mb-2">
                                                    <div class="list-img">
                                                        <img src="<%= product.image_path.proxy_url %>74/100<%= product.image_path.image_path %>">
                                                        <span class="item_no position-absolute">x<%= product.quantity %></span>
                                                    </div>
                                                    <!-- <h6 class="mx-1 mb-0 mt-1 ellips">Vendor Name</h6>    -->
                                                    <label class="items_price">{{ App\Models\ClientCurrency::getAdminCurrencySymbol() }}<%= Helper.formatPrice(product.price) %></label>
                                                </div>
                                            <% }); %>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-md-0 mt-sm-2">
                                        <ul class="price_box_bottom m-0 p-0">
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">Total</label>
                                                <span>{{ App\Models\ClientCurrency::getAdminCurrencySymbol() }}<%= Helper.formatPrice(vendor.subtotal_amount) %></span>
                                            </li>
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">Promocode</label>
                                                <span>{{ App\Models\ClientCurrency::getAdminCurrencySymbol() }}<%= Helper.formatPrice(vendor.discount_amount) %></span>
                                            </li>
                                            <li class="d-flex align-items-center justify-content-between">
                                                <label class="m-0">Delivery</label>
                                                <% if(vendor.delivery_fee !== null) { %>
                                                <span>{{ App\Models\ClientCurrency::getAdminCurrencySymbol() }}<%= Helper.formatPrice(vendor.delivery_fee) %></span>
                                                <% }else { %>
                                                    <span>{{ App\Models\ClientCurrency::getAdminCurrencySymbol() }} 0.00</span>
                                                <% } %>
                                            </li>
                                            <li class="grand_total d-flex align-items-center justify-content-between">
                                                <label class="m-0">Amount</label>
                                                <span>{{ App\Models\ClientCurrency::getAdminCurrencySymbol() }}<%= Helper.formatPrice(vendor.payable_amount) %></span>
                                            </li>
                                        </ul>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <% }); %>
                </div>
            <% } %>
        <% }); %>
    </div>
</script>
