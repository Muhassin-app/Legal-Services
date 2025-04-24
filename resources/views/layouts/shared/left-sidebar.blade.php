@php
    $getAdditionalPreference = getAdditionalPreference(['is_seller_module','is_gift_card','is_marg_enable','is_vendor_marg_configuration','is_car_rental_enable','product_measurment']);
@endphp
<div class="left-side-menu">
    <div class="logo-box d-lg-block" style="height: auto">
        @php
            $urlImg = URL::to('/').'/assets/images/users/user-1.jpg';
            $clientData = \App\Models\Client::select('id', 'logo','dark_logo','socket_url')->first();
            $client_preference = \App\Models\ClientPreference::where(['id' => 1])->first();
            if($clientData){
                if($client_preference->theme_admin == 'dark'){
                    $urlImg = $clientData ? $clientData->dark_logo['original'] : ' ';
                }else{
                    $urlImg = $clientData ? $clientData->logo['original'] : ' ';
                }
            }
            $marketing_permissions = array("banner", "promocode", "loyalty_cards");
            $subscription_permissions = array("subscription_plans_customers", "subscription_plans_vendors");
            $extra_permissions = array("celebrity", "inquiries");
            $setting_permissions = array("profile", "customize", "app_styling", "web_styling", "catalog", "configurations", "tax", "payment");
            $styling_permissions = array("app_styling", "web_styling");
            $order_permissions = array("dashboard", "orders", "vendors", "accounting_orders","accounting_loyality", "accounting_promo_codes", "accounting_taxes","accounting_vendors", "subscriptions_customers", "subscriptions_vendors", "customers");
            $accounting_permissions = array("accounting_orders", "accounting_loyality", "accounting_promo_codes", "accounting_taxes", "accounting_vendors");
            $Vendors = getNomenclatureName('Vendors', true);
            $VendorsTrans = ($Vendors=="Vendors")?__('Vendors'):$Vendors;

    @endphp
        <a href="{{route('client.dashboard')}}" class="logo logo-dark text-center">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="50">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" height="20">
            </span>
        </a>

        <a href="{{route('client.dashboard')}}" class="logo logo-light text-center d-none d-sm-block">
            <span class="logo-sm">
                <img src="{{$urlImg}}"
                    alt="" height="30" style="padding-top: 4px;">
            </span>
            <span class="logo-lg">
                <img src="{{$urlImg}}"
                    alt="" height="50" style="padding-top: 4px;">
            </span>
        </a>
    </div>
    <div class="h-100" data-simplebar>
        <div class="user-box text-center">
            <img src="{{asset('assets/images/users/user-1.jpg')}}" alt="user-img" title="Mat Helme" class="rounded-circle avatar-md">
            <div class="dropdown">
                <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block" data-toggle="dropdown">User</a>
                <div class="dropdown-menu user-pro-dropdown">
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-user mr-1"></i>
                        <span>My Account</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-log-out mr-1"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
            <p class="text-muted">Admin Head</p>
        </div>

        <div id="sidebar-menu">
            <?php
            $allowed = [];
            if (Auth::user()->is_superadmin == 0) {
                foreach (@Auth::user()->getAllPermissions as $value) {
                    array_push($allowed, $value->permission->slug);
                }
            } else {
                array_push($allowed, '99999');
            }
            ?>

            <ul id="side-menu">
                 {{-- @if(Auth::user()->is_admin || Auth::user()->is_superadmin ) --}}
                 @if(Auth::user())
                <li>
                    <a class="menu-title pl-1" href="#">
                        @php
                            $ordermenu = getNomenclatureName('Orders', true);
                            $ordermenulabel = ($ordermenu=="Orders")?__('Orders'):__($ordermenu);

                        @endphp
                        <!-- <span class="icon-orders"></span> -->
                        <span>{{ __(ucwords($ordermenulabel)) }}</span>
                    </a>
                    <ul class="nav-second-level p-0 mx-2">
                            @if(@auth()->user()->can('dashboard-view') || Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="{{route('client.dashboard')}}">
                                        {{-- <span class="icon-dash"></span> --}}
                                        <span><img src="{{asset('assets/images/dashbord/icon/1.svg')}}" alt=""></span>
                                        <span>{{ __('Dashboard') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if(@auth()->user()->can('order-view') || Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="{{route('order.index')}}">
                                        {{-- <span class="icon-orders"></span> --}}
                                        <span><img src="{{asset('assets/images/dashbord/icon/2.svg')}}" alt=""></span>
                                        @php
                                            $ordersNom = getNomenclatureName('Orders', true);
                                            $ordersNom = ($ordersNom=="Orders")?__('Orders'):__($ordersNom);
                                        @endphp
                                        <span> {{ __($ordersNom) }} </span>
                                    </a>
                                </li>

                                {{-- <li>
                                    <a href="#sidebarorders" data-toggle="collapse">
                                    <span class="icon-accounting"></span>
                                        <span> {{ __('Orders') }} </span>
                                    </a>
                                    <div class="collapse" id="sidebarorders">
                                        <ul class="nav-second-level">

                                                <li>
                                                    <a href="{{route('order.index')}}">{{ __('All Orders') }}</a>
                                                </li>

                                                <li>
                                                    <a href="{{route('backend.order.returns',['Pending'])}}">{{ __("Return Request") }}</a>
                                                </li>

                                                <li>
                                                    <a href="{{route('cancel-order.requests')}}">{{ __("Cancel Order Request") }}</a>
                                                </li>
                                        </ul>
                                    </div>
                                </li> --}}
                            @endif
                            @if(@auth()->user()->can('vendor-view') || Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="{{route('vendor.index')}}">
                                    {{-- <span class="icon-vendor"></span> --}}
                                    <span><img src="{{asset('assets/images/dashbord/icon/3.svg')}}" alt=""></span>
                                    @php
                                        $vendormenu = getNomenclatureName('Vendors', true);
                                        $vendormenulabel = ($vendormenu=="Vendors")?__('Vendors'):__($vendormenu);

                                    @endphp
                                        {{-- <span>{{getNomenclatureName('Vendors', true)}}</span> --}}
                                        <span>{{ __($vendormenulabel) }}</span>
                                    </a>
                                </li>
                            @endif
                            @if(Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="{{route('lawyer.index')}}">
                                    <span><img src="{{asset('assets/images/dashbord/icon/3.svg')}}" alt=""></span>
                                        <span>{{ __('Lawyers') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if(@$getAdditionalPreference['is_vendor_marg_configuration'] == '1')
                                <li>
                                    <a href="{{route('failed-marg-orders')}}">
                                    <span class="icon-orders"></span>
                                    @php
                                        $vendormenu = getNomenclatureName('Marg Failed Orders', true);
                                        $vendor_orders_count = \App\Models\OrderVendor::select('id')->whereHas('orderDetail', function ($query){
                                            $query->where('marg_status', '=',null);
                                            $query->where('marg_max_attempt', '>',2);
                                        })->whereHas('vendor.permissionToUser', function ($query){
                                            if (auth()->user()->is_admin) {
                                                $query->where('user_id', auth()->user()->id);
                                            }
                                        })->count();
                                    @endphp
                                        {{-- <span>{{getNomenclatureName('Vendors', true)}}</span> --}}
                                        <span>{{ __('Marg Failed Orders') }} {{ $vendor_orders_count ? '('. $vendor_orders_count .')' : '' }}</span>
                                    </a>
                                </li>
                            @endif

                            @if(@$getAdditionalPreference['is_seller_module'] == '1')
                                <li>
                                    <a href="{{route('seller.index')}}">
                                    <span class="icon-vendor"></span>
                                    @php
                                        $vendormenu = getNomenclatureName('Sellers', true);
                                        $vendormenulabel = ($vendormenu=="Sellers")?__('Sellers'):__($vendormenu);

                                    @endphp
                                        {{-- <span>{{getNomenclatureName('Vendors', true)}}</span> --}}
                                        <span>{{ __($vendormenulabel) }}</span>
                                    </a>
                                </li>
                            @endif


                            @if(@auth()->user()->can('accounting-view') || Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="#sidebaraccounting" data-toggle="collapse">
                                    <span class="icon-accounting"></span>
                                        <span> {{ __('Accounting') }} </span>
                                    </a>
                                    <div class="collapse" id="sidebaraccounting">
                                        <ul class="nav-second-level">
                                            <!-- <li>
                                                <a href="{{route('vendorPaymentReport')}}">
                                                     {{ __('Vendors Payment Report') }}
                                                </a>
                                            </li> -->
                                            @if(@auth()->user()->can('accounting-orders') || Auth::user()->is_superadmin == 1)
                                                <li>
                                                    <a href="{{route('account.orders')}}">{{ __($ordermenulabel) }}</a>
                                                </li>
                                            @endif
                                            @if(@auth()->user()->can('accounting-loyalty-cards') || Auth::user()->is_superadmin == 1)
                                                <li>

                                                @php
                                                    $loyaltyCards = getNomenclatureName('Loyalty Cards', true);
                                                    $loyaltyCardsLabel = ($loyaltyCards=="Loyalty Cards")?__('Loyalty Cards'):$loyaltyCards;
                                                @endphp
                                                    <a href="{{route('account.loyalty')}}">{{ $loyaltyCardsLabel }}</a>
                                                </li>
                                            @endif
                                            @if(@auth()->user()->can('accounting-promo-codes') || Auth::user()->is_superadmin == 1)
                                                <!-- <li>
                                                    <a href="{{route('account.promo.code')}}">{{ __('Promo Codes') }}</a>
                                                </li> -->
                                            @endif
                                            @if(@auth()->user()->can('accounting-taxes') || Auth::user()->is_superadmin == 1)
                                                <li>
                                                    <a href="{{route('account.tax')}}">{{ __('Taxes') }}</a>
                                                </li>
                                            @endif
                                            @if(@auth()->user()->can('accounting-vendors') || Auth::user()->is_superadmin == 1)
                                                <li>

                                                    <a href="{{route('account.vendor')}}">{{ __($VendorsTrans) }}</a>
                                                </li>
                                            @endif
                                            @if(@auth()->user()->can('accounting-payout-request') || Auth::user()->is_superadmin == 1)
                                                <!-- <li>
                                                    <a href="{{route('account.vendor.payout.requests')}}">{{ __('Payout Requests') }}</a>
                                                </li> -->
                                            @elseif(@auth()->user()->can('accounting-order-refund') || @auth()->user()->is_superadmin == 1)
                                                <li>
                                                    <a href="{{route('backend.order.refund')}}">{{ __('Order Refunds') }}</a>
                                                </li>
                                            @endif
                                            @if(Auth::user()->is_superadmin == 1 && @$getAdditionalPreference['is_gift_card']==1)
                                                <li>
                                                    <a href="{{route('account.redeemedcard')}}">{{ __('Gift Cards') }}</a>
                                                </li>
                                            @endif
                                            @if(@auth()->user()->can('accounting-subscription-discount') || Auth::user()->is_superadmin == 1)
                                                <!-- <li>
                                                    <a href="{{route('account.userSubscription')}}">{{ __('Subscription Discount') }}</a>
                                                </li> -->
                                            @endif
                                        </ul>
                                    </div>
                                </li>
                            @endif



                            @if(@auth()->user()->can('subscription-customer-view') || @auth()->user()->can('subscription-vendor-view') || Auth::user()->is_superadmin == 1)
                            {{-- @if(count(array_intersect($subscription_permissions, $allowed)) || Auth::user()->is_superadmin == 1) --}}
                                @if($client_preference->subscription_mode == 1)
                                    <li>
                                        <a href="#sidebarsubscriptions" data-toggle="collapse">
                                            <span class="icon-subscribe"></span>
                                            <span> {{ __('Subscriptions') }}</span>
                                        </a>
                                        <div class="collapse" id="sidebarsubscriptions">
                                            <ul class="nav-second-level">
                                                @if(@auth()->user()->can('subscription-customer-view') || Auth::user()->is_superadmin == 1)
                                                    <li>
                                                        <a href="{{route('subscription.plans.user')}}">{{ __('Manage Users') }}</a>
                                                    </li>
                                                @endif
                                                @if(@auth()->user()->can('subscription-vendor-view') || Auth::user()->is_superadmin == 1)
                                                    <li>
                                                        <a href="{{route('subscription.plans.vendor')}}">{{ __($VendorsTrans) }}</a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </li>
                                @endif
                            @endif
                            {{-- @if(in_array('customers',$allowed) || Auth::user()->is_superadmin == 1) --}}
                            @if(@auth()->user()->can('customers-view') || Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="{{route('customer.index')}}">
                                        {{-- <span class="icon-customer-2"></span> --}}
                                        <span><img src="{{asset('assets/images/dashbord/icon/4.svg')}}" alt=""></span>
                                        <span> {{ __('Manage Users') }} </span>
                                    </a>
                                </li>
                            @endif

                            @if(@auth()->user()->can('review-view') || @auth()->user()->can('review-product-performance') || Auth::user()->is_superadmin == 1)
                            <!-- <li>
                                <a href="#sidebarreports" data-toggle="collapse">
                                {{-- <span class="mdibookoutline"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="41.833px" height="41.833px" viewBox="0 0 41.833 41.833" style="enable-background:new 0 0 41.833 41.833;" xml:space="preserve"><g><g><path d="M2.5,31.458h15.792v3.333H15.75c-0.276,0-0.5,0.226-0.5,0.5v3.167c0,0.275,0.224,0.5,0.5,0.5h10.333c0.274,0,0.5-0.225,0.5-0.5v-3.167c0-0.274-0.226-0.5-0.5-0.5h-2.541v-3.333h15.791c1.379,0,2.5-1.122,2.5-2.5V5.375c0-1.378-1.121-2.5-2.5-2.5H2.5c-1.378,0-2.5,1.122-2.5,2.5v23.583C0,30.336,1.122,31.458,2.5,31.458z M16.25,37.958v-0.583h9.333v0.583H16.25z M25.583,36.375H16.25v-0.583h2.542h4.25h2.543L25.583,36.375L25.583,36.375z M22.542,34.792h-3.25v-3.333h3.25V34.792z M39.333,30.458H23.042h-4.25H2.5c-0.827,0-1.5-0.673-1.5-1.5v-1.75h39.833v1.75C40.833,29.786,40.159,30.458,39.333,30.458z M2.5,3.875h36.833c0.826,0,1.5,0.673,1.5,1.5v20.833H1V5.375C1,4.547,1.673,3.875,2.5,3.875z"/><path d="M7.667,25.125c0.138,0,0.276-0.059,0.375-0.169l6.642-7.512l3.457,3.57c0.003,0.004,0.006,0.004,0.009,0.006c0.003,0.004,0.004,0.008,0.006,0.01c0.031,0.029,0.068,0.047,0.103,0.064c0.019,0.012,0.035,0.026,0.055,0.035c0.06,0.023,0.123,0.037,0.187,0.037s0.128-0.014,0.189-0.038c0.02-0.008,0.036-0.024,0.056-0.036c0.035-0.021,0.072-0.037,0.103-0.066c0.003-0.002,0.003-0.006,0.006-0.01c0.003-0.002,0.006-0.002,0.009-0.006l5.24-5.519l4.084,2.119c0.008,0.004,0.019,0.003,0.025,0.006c0.064,0.03,0.135,0.05,0.205,0.05c0.08,0,0.158-0.025,0.23-0.064c0.021-0.012,0.039-0.031,0.063-0.047c0.029-0.022,0.063-0.038,0.088-0.067l6.834-8.125c0.179-0.211,0.148-0.527-0.063-0.705c-0.211-0.177-0.523-0.15-0.703,0.061l-6.574,7.818l-4.064-2.108c-0.01-0.005-0.02-0.004-0.024-0.008c-0.033-0.015-0.065-0.022-0.104-0.029c-0.027-0.006-0.058-0.014-0.086-0.015c-0.031-0.001-0.063,0.006-0.097,0.011c-0.03,0.005-0.063,0.009-0.095,0.021c-0.026,0.01-0.053,0.027-0.075,0.042c-0.031,0.018-0.063,0.036-0.088,0.061c-0.009,0.007-0.019,0.009-0.022,0.017l-5.141,5.414l-3.47-3.583c-0.005-0.005-0.012-0.007-0.017-0.011c-0.005-0.005-0.007-0.011-0.012-0.015c-0.02-0.018-0.045-0.025-0.067-0.039c-0.029-0.019-0.056-0.039-0.088-0.051c-0.03-0.011-0.06-0.014-0.091-0.019c-0.032-0.005-0.062-0.013-0.095-0.013c-0.033,0.001-0.065,0.01-0.097,0.017c-0.03,0.007-0.059,0.01-0.088,0.023c-0.031,0.013-0.057,0.035-0.086,0.054c-0.022,0.015-0.047,0.023-0.067,0.042c-0.005,0.005-0.007,0.012-0.011,0.017c-0.005,0.005-0.011,0.007-0.015,0.012l-7,7.917C7.109,24.5,7.128,24.814,7.335,25C7.432,25.083,7.549,25.125,7.667,25.125z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></span> --}}
                                <span><img src="{{asset('assets/images/dashbord/icon/5.svg')}}" alt=""></span>
                                <span> {{ __('Reports') }} </span>
                                </a>
                                <div class="collapse" id="sidebarreports">
                                    <ul class="nav-second-level">
                                        @if(@auth()->user()->can('review-view') || Auth::user()->is_superadmin == 1)
                                        @php
                                            $productmenu = getNomenclatureName('Products', true);
                                            $productmenulabel = ($productmenu=="Products")?__('Products'):__($productmenu);

                                        @endphp
                                        <li>
                                            <a href="{{route('review.index')}}">{{ __($productmenulabel. ' Reviews') }}</a>
                                        </li>
                                        @endif

                                        @if(@auth()->user()->can('review-product-performance') || Auth::user()->is_superadmin == 1)
                                        <li>
                                            <a href="{{route('report.productperformance')}}">{{ __($productmenulabel. " Performance Report") }}</a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </li> -->


                            @endif

                            <!-- @if(Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('admin.serviceArea.index')}}">
                                    <span class="icon-customer-2"></span>
                                    <span> {{ __('Admin Service Area') }} </span>
                                </a>
                            </li>
                            @endif -->
                            {{-- <li>
                                <a href="{{route('company.getList')}}">
                                    <span class="icon-customer-2"></span>
                                    <span> {{ __('Companies') }} </span>
                                </a>
                            </li> --}}

                            @if(Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="#chat" data-toggle="collapse">
                                        <span class="mdichat"><?xml version="1.0" encoding="iso-8859-1"?><!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 224.376 224.376" style="enable-background:new 0 0 224.376 224.376;" xml:space="preserve"><g><g><g><path d="M168.024,46.027c-9.418,0-18.59,2.312-26.789,6.723c-9.18-16.859-26.973-27.531-46.496-27.531c-24.988,0-46.648,17.703-51.73,41.703C19.043,68.399,0,88.251,0,112.441c0,25.148,20.582,45.609,45.879,45.609h10.516v41.106l36.59-41.106l75.926-0.008c30.582-0.465,55.465-25.59,55.465-56.004C224.375,71.152,199.098,46.027,168.024,46.027z M168.848,150.051h-79.45l-25.004,28.09v-28.09H45.879C24.992,150.051,8,133.18,8,112.441c0-20.738,16.992-37.609,37.879-37.609h3.961l0.496-3.43c3.129-21.77,22.219-38.184,44.402-38.184c17.863,0,34.012,10.508,41.144,26.774l1.887,4.301l3.945-2.547c7.832-5.051,16.926-7.719,26.308-7.719c26.66,0,48.352,21.539,48.352,48.012C216.375,128.105,195.027,149.644,168.848,150.051z"/><path d="M78.188,90.052c-7.719,0-14,6.281-14,14c0,7.719,6.281,14,14,14s14-6.281,14-14C92.188,96.332,85.906,90.052,78.188,90.052z M78.188,110.052c-3.308,0-6-2.692-6-6c0-3.308,2.692-6,6-6c3.308,0,6,2.692,6,6C84.188,107.36,81.496,110.052,78.188,110.052z"/><path d="M118.188,90.052c-7.719,0-14,6.281-14,14c0,7.719,6.281,14,14,14s14-6.281,14-14C132.188,96.332,125.906,90.052,118.188,90.052z M118.188,110.052c-3.308,0-6-2.692-6-6c0-3.308,2.692-6,6-6c3.308,0,6,2.692,6,6C124.188,107.36,121.496,110.052,118.188,110.052z"/><path d="M158.188,90.052c-7.719,0-14,6.281-14,14c0,7.719,6.281,14,14,14s14-6.281,14-14C172.188,96.332,165.906,90.052,158.188,90.052z M158.188,110.052c-3.308,0-6-2.692-6-6c0-3.308,2.692-6,6-6c3.308,0,6,2.692,6,6C164.188,107.36,161.496,110.052,158.188,110.052z"/></g></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></span>
                                        <span> {{ __('Chat') }} </span>
                                    </a>
                                    <div class="collapse" id="chat">
                                        <ul class="nav-second-level">
                                            <li>
                                                <a href="{{route('chat.userUserChat')}}">{{ __('User/User') }}</a>
                                            </li>
                                            <!-- <li>
                                                <a href="{{route('chat.VendorUserChat')}}">{{ __('User/Vendor') }}</a>
                                            </li>
                                            @if(Auth::user()->is_superadmin == 1)
                                                <li>
                                                    <a href="{{route('chat.userAgentChatRoom')}}">{{ __('User/Driver') }}</a>
                                                </li>
                                                @endif -->
                                            {{-- <li>
                                                <a href="{{route('report.productperformance')}}">{{ __("Product Performance Report") }}</a>
                                            </li> --}}
                                        </ul>
                                    </div>
                                </li>
                            @endif
                        </ul>
                </li>
                @endif
                @if(@auth()->user()->can('setting-customize-view') || @auth()->user()->can('configuration-view') || Auth::user()->is_superadmin == 1)
                <li>
                   <a class="menu-title pl-1" href="#">
                        <span>{{ __('SETTINGS') }}</span>
                    </a>
                    <ul class="nav-second-level p-0 mx-2">

                        @if(@auth()->user()->can('setting-profile-view') || Auth::user()->is_superadmin == 1)
                        <li>  <a href="{{route('client.profile')}}">
                                    <span class="icon-profile"></span>
                                    <span> {{ __('Profile') }} </span>
                                </a>
                            </li>
                            @endif

                        @if(@auth()->user()->can('setting-customize-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('configure.customize')}}">
                                    <span class="icon-customzie"></span>
                                    <span> {{ __('Customize') }} </span>
                                </a>
                            </li>
                        @endif
                        @if(@auth()->user()->can('setting-webstyle-view') || @auth()->user()->can('setting-appstyle-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="#sidebarstyling" data-toggle="collapse">
                                    <span class="icon-styling"></span>
                                    <span> {{ __('Styling') }} </span>
                                </a>
                                <div class="collapse" id="sidebarstyling">
                                    <ul class="nav-second-level">
                                        @if(@auth()->user()->can('setting-appstyle-view') || Auth::user()->is_superadmin == 1)
                                            <li>
                                                <a href="{{route('appStyling.index')}}">{{ __('App Styling') }}</a>
                                            </li>
                                        @endif
                                        @if(@auth()->user()->can('setting-webstyle-view') || Auth::user()->is_superadmin == 1)
                                            <li>
                                                <a href="{{route('webStyling.index')}}">{{ __('Web Styling') }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </li>
                        @endif
                        <li>
                            <a href="#sidebarcms" data-toggle="collapse">
                                <span class="icon-cms"></span>
                                <span>{{ __("CMS") }}</span>
                            </a>
                            <div class="collapse" id="sidebarcms">
                                <ul class="nav-second-level">
                                    @if(@auth()->user()->can('cms-pages-view') || Auth::user()->is_superadmin == 1)
                                        <li>
                                            <a href="{{route('cms.pages')}}">{{ __('Pages') }}</a>
                                        </li>
                                    @endif
                                    @if(@auth()->user()->can('cms-email-view') || Auth::user()->is_superadmin == 1)
                                        <li>
                                            <a href="{{route('cms.emails')}}">{{ __('Emails') }}</a>
                                        </li>
                                    @endif
                                    @if(@auth()->user()->can('cms-notification-view') || Auth::user()->is_superadmin == 1)
                                        <li>
                                            <a href="{{route('cms.notifications')}}">{{ __('Notifications') }}</a>
                                        </li>
                                    @endif
                                    @if(@auth()->user()->can('cms-sms-view') || Auth::user()->is_superadmin == 1)
                                        <li>
                                            <a href="{{route('cms.sms')}}">{{ __('SMS') }}</a>
                                        </li>
                                    @endif
                                    @if(@auth()->user()->can('cms-reason-view') || Auth::user()->is_superadmin == 1)
                                        <li>
                                            <a href="{{route('reason.index')}}">{{ __('Reasons') }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>

                        @if(@auth()->user()->can('category-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('category.index')}}">
                                    <span class="icon-catalogue"></span>
                                    <span> {{ __('Catalog') }}</span>
                                </a>
                            </li>
                        @endif
                        @if(@auth()->user()->can('configuration-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('configure.index')}}">
                                    <span class="icon-configuration"></span>
                                    <span> {{ __('Configurations') }} </span>
                                </a>
                            </li>
                        @endif

                        @if(@auth()->user()->can('tax-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('tax.index')}}">
                                    <span class="icon-tax"></span>
                                    <span> {{ __('Tax') }} </span>
                                </a>
                            </li>
                        @endif
                        @if(@auth()->user()->can('payment-option-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('payoption.index')}}">
                                    <span class="icon-payment-options"></span>
                                    <span> {{ __('Payment Options') }} </span>
                                </a>
                            </li>
                        @endif


                        <!-- @if(@auth()->user()->can('delivery-option-view') || Auth::user()->is_superadmin == 1)
                            @if($client_preference_detail->business_type != 'taxi')


                                <li>
                                    <a href="#delivery" data-toggle="collapse">
                                        <span class="icon-payment-option_s aldelivery">
                                            <svg width="22" height="14" viewBox="0 0 22 14" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M21.931 7.12361C21.9413 7.18637 21.9482 7.24964 21.9517 7.31315C21.9704 7.34585 21.9693 7.38066 21.9683 7.41505C21.9671 7.45675 21.9659 7.49781 21.9999 7.5337V7.99548C21.8892 8.40563 21.804 8.82226 21.7449 9.24297C21.6435 9.74564 21.5509 10.2513 21.4585 10.756C21.4096 11.0229 21.3608 11.2895 21.3107 11.5553C21.301 11.6714 21.2461 11.7791 21.1578 11.8552C21.0695 11.9312 20.9549 11.9696 20.8386 11.962C20.6527 11.974 20.4662 11.974 20.2803 11.962C20.1528 11.9551 20.1149 12.0068 20.0804 12.1205C19.946 12.6016 19.6578 13.0255 19.2599 13.3274C18.862 13.6294 18.3762 13.7929 17.8766 13.7929C17.3771 13.7929 16.8913 13.6294 16.4934 13.3274C16.0955 13.0255 15.8073 12.6016 15.6728 12.1205C15.6349 11.9861 15.5729 11.9654 15.4523 11.9654H10.2142C10.0833 11.962 10.0384 12.0033 10.0005 12.1343C9.8629 12.6119 9.57365 13.0319 9.17644 13.3308C8.77923 13.6297 8.29559 13.7914 7.79847 13.7914C7.30136 13.7914 6.81772 13.6297 6.42051 13.3308C6.0233 13.0319 5.73405 12.6119 5.59641 12.1343C5.56195 12.0102 5.51715 11.9654 5.38275 11.9654H4.13526C4.03178 11.969 3.93025 11.9366 3.84795 11.8738C3.76564 11.8109 3.70764 11.7216 3.68381 11.6208C3.66141 11.5336 3.66624 11.4416 3.69766 11.3572C3.72908 11.2728 3.78558 11.2 3.85957 11.1487C3.94547 11.0811 4.054 11.0491 4.16282 11.0591H5.38619C5.40961 11.064 5.4338 11.0641 5.45722 11.0591C5.48064 11.0542 5.50279 11.0445 5.52224 11.0306C5.54169 11.0166 5.55803 10.9988 5.5702 10.9782C5.58238 10.9576 5.59013 10.9347 5.59296 10.9109C5.7218 10.4233 6.00833 9.99206 6.4079 9.68436C6.80746 9.37666 7.29761 9.2098 7.80192 9.2098C8.30624 9.2098 8.79638 9.37666 9.19594 9.68436C9.59551 9.99206 9.88204 10.4233 10.0109 10.9109C10.0384 11.0108 10.0764 11.0487 10.1832 11.0487H12.3301C12.4661 11.0519 12.4831 10.9738 12.501 10.8916C12.5026 10.8843 12.5042 10.8769 12.5059 10.8695C12.6439 10.2671 12.7807 9.66458 12.9178 9.06149C13.0237 8.59519 13.1298 8.12853 13.2364 7.6612C13.6546 5.8003 14.0773 3.9417 14.5046 2.08539C14.5494 1.89241 14.4874 1.89241 14.3392 1.89241H4.31101C3.89747 1.89241 3.67003 1.73044 3.66658 1.43063C3.66314 1.13082 3.89403 0.95851 4.30411 0.95851H14.9871C15.0932 0.942345 15.2017 0.958721 15.2982 1.00548C15.3948 1.05224 15.475 1.12718 15.5281 1.22041C15.557 1.27249 15.575 1.32991 15.5809 1.38917C15.5868 1.44842 15.5806 1.50826 15.5626 1.56503C15.4633 1.99797 15.3653 2.43222 15.2671 2.86775C15.205 3.14289 15.1428 3.41855 15.0801 3.69473C15.1339 3.73311 15.1914 3.72779 15.2485 3.72251C15.2683 3.72067 15.288 3.71885 15.3076 3.71885H17.8163C18.794 3.70837 19.7437 4.04462 20.497 4.66793C21.2503 5.29123 21.7583 6.16127 21.931 7.12361ZM7.20753 12.8362C7.39693 12.892 7.59611 12.9065 7.79158 12.8786C8.12016 12.8318 8.42082 12.668 8.63832 12.4173C8.85583 12.1666 8.97558 11.8459 8.97558 11.514C8.97558 11.1821 8.85583 10.8613 8.63832 10.6106C8.42082 10.3599 8.12016 10.1961 7.79158 10.1493C7.59611 10.1214 7.39693 10.1359 7.20753 10.1917C7.01813 10.2475 6.84292 10.3434 6.69378 10.4728C6.54464 10.6022 6.42504 10.7621 6.34307 10.9417C6.2611 11.1214 6.21868 11.3165 6.21868 11.514C6.21868 11.7114 6.2611 11.9066 6.34307 12.0862C6.42504 12.2658 6.54464 12.4258 6.69378 12.5552C6.84292 12.6845 7.01813 12.7804 7.20753 12.8362ZM17.0887 12.644C17.316 12.7969 17.5837 12.8786 17.8577 12.8786C18.218 12.8787 18.564 12.7377 18.8216 12.4859C19.0792 12.2341 19.228 11.8914 19.2361 11.5312C19.2423 11.2573 19.1667 10.9878 19.019 10.7571C18.8714 10.5264 18.6583 10.3449 18.407 10.2358C18.1558 10.1267 17.8777 10.0949 17.6083 10.1445C17.3389 10.1942 17.0904 10.323 16.8945 10.5144C16.6986 10.7059 16.5642 10.9514 16.5084 11.2196C16.4527 11.4878 16.4781 11.7666 16.5815 12.0203C16.6848 12.274 16.8614 12.4911 17.0887 12.644ZM20.7161 9.75821C20.798 9.3287 20.8801 8.89815 20.9592 8.46759H20.9454C21.0637 8.06296 21.0966 7.63812 21.0419 7.2201C20.894 6.48607 20.494 5.82689 19.9112 5.35675C19.3284 4.88661 18.5995 4.63512 17.8508 4.64585H15.0939C14.9078 4.64241 14.863 4.71133 14.8286 4.8733C14.6134 5.83648 14.3931 6.79967 14.1725 7.76409C14.0821 8.15918 13.9917 8.55447 13.9015 8.95005C13.8286 9.27753 13.7549 9.60502 13.6808 9.93429C13.5973 10.3049 13.5134 10.6779 13.4294 11.0556H15.5419C15.641 11.0556 15.6506 10.9973 15.6596 10.9422C15.6622 10.9265 15.6648 10.9109 15.6694 10.8971C15.805 10.4159 16.0928 9.99165 16.4898 9.68784C16.8868 9.38403 17.3716 9.21706 17.8715 9.21196C18.3752 9.21066 18.8652 9.37587 19.2653 9.68187C19.6654 9.98788 19.9532 10.4175 20.0839 10.904C20.1253 11.045 20.1823 11.0463 20.2744 11.0484C20.2971 11.0489 20.3219 11.0495 20.3492 11.0522C20.4741 11.0647 20.4858 10.9924 20.4998 10.9069C20.5012 10.898 20.5027 10.889 20.5043 10.8799C20.5734 10.5072 20.6447 10.1331 20.7161 9.75821ZM4.11805 2.80907H5.89969C5.96459 2.8011 6.03046 2.807 6.09291 2.82639C6.15536 2.84578 6.21299 2.87821 6.26197 2.92154C6.31095 2.96487 6.35017 3.01811 6.37703 3.07773C6.40389 3.13736 6.41778 3.20201 6.41778 3.2674C6.41778 3.3328 6.40389 3.39745 6.37703 3.45707C6.35017 3.5167 6.31095 3.56994 6.26197 3.61327C6.21299 3.6566 6.15536 3.68903 6.09291 3.70842C6.03046 3.72781 5.96459 3.73371 5.89969 3.72574H2.31917C2.25493 3.7326 2.18995 3.7257 2.12858 3.7055C2.06721 3.68529 2.01084 3.65225 1.96324 3.60856C1.91564 3.56487 1.87789 3.51154 1.85251 3.45212C1.82713 3.3927 1.81469 3.32856 1.81604 3.26396C1.81604 2.98482 2.01591 2.80907 2.3433 2.80907H4.11805ZM5.87902 5.55907H3.64939L1.44043 5.5694C1.31797 5.55627 1.19502 5.58946 1.09582 5.66245C1.02354 5.71493 0.969202 5.78842 0.940218 5.87291C0.911233 5.9574 0.909009 6.04877 0.93385 6.13457C0.962353 6.24494 1.03039 6.34101 1.12504 6.40453C1.21969 6.46806 1.33438 6.49462 1.44732 6.47918H5.87902C5.94547 6.48922 6.01331 6.48464 6.07781 6.46575C6.1423 6.44687 6.2019 6.41413 6.25243 6.36983C6.30297 6.32553 6.34322 6.27073 6.37039 6.20927C6.39756 6.1478 6.41098 6.08114 6.40973 6.01395C6.40973 5.73137 6.22364 5.55907 5.87902 5.55907ZM5.87912 8.30561H3.21182L0.534191 8.3125C0.203364 8.3125 4.50053e-05 8.48481 4.50053e-05 8.76394C-0.000864157 8.82858 0.0120107 8.89267 0.0378158 8.95193C0.0636208 9.0112 0.101764 9.06429 0.149698 9.10766C0.197632 9.15103 0.254258 9.18368 0.315804 9.20345C0.37735 9.22321 0.442402 9.22963 0.506624 9.22228H5.88945C5.95543 9.23043 6.02239 9.22402 6.08563 9.2035C6.14886 9.18298 6.20683 9.14886 6.25545 9.10352C6.30407 9.05818 6.34217 9.00274 6.36705 8.9411C6.39193 8.87945 6.403 8.8131 6.39948 8.74671C6.39259 8.47102 6.1996 8.30561 5.87912 8.30561Z" fill="#6E768E"/></svg>
                                        </span>
                                        <span> {{ __('Manage Delivery') }}</span>
                                    </a>
                                    <div class="collapse" id="delivery">
                                        <ul class="nav-second-level">


                                    <li>
                                            <a href="{{route('deliveryoption.index')}}">
                                                <span> {{ __('Delivery Options') }} </span>
                                            </a>
                                        </li>

                                        <li>
                                            <a href="{{route('delivery-slot.index')}}">
                                                <span title="{{__('Same Day Delivery Slots')}}"> {{ __('Delivery Slots') }} </span>
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            </li>

                            @endif
                        @endif -->

                        @if(Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('roles')}}">
                                    <i class="icon-profile"></i>
                                    <span>{{ __("Manage Roles") }}</span>
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('manageCache')}}">
                                    <i class="icon-profile"></i>
                                    <span>{{ __("Cache Control") }}</span>
                                    </a>
                            </li>
                            <li class="d-none">
                                <a href="{{route('manage.attribute')}}">
                                    <i class="icon-profile"></i>
                                    <span>{{ __("Manage Attributes") }}</span>
                                </a>
                            </li>
                        @endif
                        @if($getAdditionalPreference['is_car_rental_enable']==1)
                        <li>
                            <a href="{{route('rental.protection')}}">
                                <i class="icon-profile"></i>
                                <span>{{ __("Rental Protection") }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{route('booking.option')}}">
                                <i class="icon-profile"></i>
                                <span>{{ __("Booking Option") }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{route('destinations')}}">
                                <i class="icon-profile"></i>
                                <span>{{ __("Destination") }}</span>
                            </a>
                        </li>
                        @endif

                        {{-- @if(Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('verifyoption.index')}}">
                                    <span class="icon-verification-options">
                                        <svg width="20px" height="20px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill="#555" d="M2.72727273,3.33333333 C1.97415716,3.33333333 1.36363636,3.930287 1.36363636,4.66666667 L1.36363636,15.3333333 C1.36363636,16.069713 1.97415716,16.6666667 2.72727273,16.6666667 L17.2727273,16.6666667 C18.0258428,16.6666667 18.6363636,16.069713 18.6363636,15.3333333 L18.6363636,4.66666667 C18.6363636,3.930287 18.0258428,3.33333333 17.2727273,3.33333333 L2.72727273,3.33333333 Z M17.2727273,2 C18.7789584,2 20,3.19390733 20,4.66666667 L20,15.3333333 C20,16.8060927 18.7789584,18 17.2727273,18 L2.72727273,18 C1.22104159,18 0,16.8060927 0,15.3333333 L0,4.66666667 C0,3.19390733 1.22104159,2 2.72727273,2 L17.2727273,2 Z M8.16666667,13.1333333 L4,13.1333333 C3.62344222,13.1333333 3.31818182,13.4318102 3.31818182,13.8 C3.31818182,14.1681898 3.62344222,14.4666667 4,14.4666667 L4,14.4666667 L8.16666667,14.4666667 C8.54322445,14.4666667 8.84848485,14.1681898 8.84848485,13.8 C8.84848485,13.4318102 8.54322445,13.1333333 8.16666667,13.1333333 L8.16666667,13.1333333 Z M13.75,5.33333333 C12.2256209,5.33333333 10.9848485,6.52447482 10.9848485,8 C10.9848485,8.77302759 11.3254047,9.46800006 11.8688812,9.95452977 C10.8391408,10.5627758 10.1515152,11.6575022 10.1515152,12.9090402 C10.1515152,13.2662051 10.2078201,13.6168504 10.3172774,13.9516079 C10.4319483,14.3023104 10.8156694,14.4957175 11.1743424,14.3835948 C11.5330155,14.2714721 11.7308182,13.8962782 11.6161473,13.5455757 C11.5494668,13.3416441 11.5151515,13.1279418 11.5151515,12.9090402 C11.5151515,11.7335986 12.5129554,10.7757069 13.75,10.7757069 C14.9870446,10.7757069 15.9848485,11.7335986 15.9848485,12.9090402 C15.9848485,13.0815383 15.9635437,13.2508273 15.9218367,13.4147606 C15.8309344,13.7720611 16.0534757,14.1337638 16.4188968,14.2226461 C16.7843178,14.3115284 17.154241,14.0939324 17.2451433,13.7366318 C17.3135782,13.4676418 17.3484848,13.1902722 17.3484848,12.9090402 C17.3484848,11.6575022 16.6608592,10.5627758 15.6313396,9.95339218 C16.1745953,9.46800006 16.5151515,8.77302759 16.5151515,8 C16.5151515,6.52447482 15.2743791,5.33333333 13.75,5.33333333 Z M8.16666667,9.63333333 L4,9.63333333 C3.62344222,9.63333333 3.31818182,9.93181017 3.31818182,10.3 C3.31818182,10.6681898 3.62344222,10.9666667 4,10.9666667 L4,10.9666667 L8.16666667,10.9666667 C8.54322445,10.9666667 8.84848485,10.6681898 8.84848485,10.3 C8.84848485,9.93181017 8.54322445,9.63333333 8.16666667,9.63333333 L8.16666667,9.63333333 Z M13.75,6.66666667 C14.5268073,6.66666667 15.1515152,7.26638618 15.1515152,8 C15.1515152,8.73361382 14.5268073,9.33333333 13.75,9.33333333 C12.9731927,9.33333333 12.3484848,8.73361382 12.3484848,8 C12.3484848,7.26638618 12.9731927,6.66666667 13.75,6.66666667 Z M8.16666667,6.13333333 L4,6.13333333 C3.62344222,6.13333333 3.31818182,6.43181017 3.31818182,6.8 C3.31818182,7.16818983 3.62344222,7.46666667 4,7.46666667 L4,7.46666667 L8.16666667,7.46666667 C8.54322445,7.46666667 8.84848485,7.16818983 8.84848485,6.8 C8.84848485,6.43181017 8.54322445,6.13333333 8.16666667,6.13333333 L8.16666667,6.13333333 Z"/></svg>

                                    </span>
                                    <span> {{ __('Verification Options') }} </span>
                                </a>
                            </li>
                        @endif --}}
                    </ul>
                </li>
                @endif
                @if(Auth::user()->is_superadmin == 1 || Auth::user()->is_admin == 1)
                <li>
                    <a class="menu-title pl-1" href="#">
                        <!-- <span class="icon-marketing"></span> -->
                        <span>{{ __('MARKETING') }}</span>
                    </a>
                    <ul class="nav-second-level p-0 mx-2">

                        @if(Auth::user()->is_superadmin == 1)
                        <li>
                            <a href="#sidebarbanner" data-toggle="collapse">
                                <span class="icon-styling"></span>
                                <span> {{ __('Banners') }} </span>
                            </a>
                            <div class="collapse" id="sidebarbanner">
                                <ul class="nav-second-level">
                                        @if($client_preference_detail->business_type != 'taxi')
                                        <li>
                                            <a href="{{route('banner.index')}}">{{ __('Web Banners') }}</a>
                                        </li>
                                        @endif
                                         <li>
                                            <a href="{{route('mobilebanner.index')}}">{{ __('Mobile Banners') }}</a>
                                        </li>
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(@getAdditionalPreference(['is_influencer_refer_and_earn'])['is_influencer_refer_and_earn'] == 1)

                        <li>
                            <a href="#influencer" data-toggle="collapse">
                                <span class="icon-profile"></span>
                                <span> {{ __('Influencer') }} </span>
                            </a>
                            <div class="collapse" id="influencer">
                                <ul class="nav-second-level">
                                    <li>
                                        <a href="{{route('influencer-refer-earn.index')}}">
                                            <span> {{ __('Influencer Refer & Earn') }} </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{route('influencer-refer-earn.list')}}">
                                            <span> {{ __('Influencer User') }} </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endif



                        <!-- @if(@auth()->user()->can('promo-code-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('promocode.index')}}">
                                    <span class="icon-discount-voucher"></span>
                                    <span> {{ __('Promocode') }} </span>
                                </a>
                            </li>
                        @endif -->
                        @if(@auth()->user()->can('loyalty-code-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('loyalty.index')}}">
                                    <span class="icon-loyaltycard"></span>
                                    @php
                                        $LoyaltyCards =  getNomenclatureName('Loyalty Cards', true)

                                    @endphp
                                    <span> {{ $LoyaltyCards === "Loyalty Cards" ?  __("Loyalty Cards")  : $LoyaltyCards }}</span>
                                </a>
                            </li>
                        @endif
                        @if(@auth()->user()->can('campaign-code-view') || Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="{{ route('campaign.index')}}">
                                        <span class="icon-celebrity"></span>
                                        <span> {{ __("Campaigns") }} </span>
                                    </a>
                                </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(count(array_intersect($extra_permissions, $allowed)) || (Auth::user()->is_admin == 1 || Auth::user()->is_superadmin == 1 ) || in_array('tools',$allowed))
                    <li>
                        <a class="menu-title pl-1">
                            <!-- <span class="icon-extra"></span> -->
                            <span>{{ __("EXTRA") }}</span>
                        </a>
                        <ul class="nav-second-level p-0 mx-2">

                            @if( Auth::user()->is_superadmin == 1 && @$getAdditionalPreference['is_gift_card']==1)
                            <li>
                                <a href="{{route('giftCart.index')}}">
                                    <span class="icon-settings-1-1"></span>
                                    <span> {{ __('Gift Card') }} </span>
                                </a>
                            </li>
                            @endif
                            @if(Auth::user()->is_superadmin == 1 && $client_preference->celebrity_check == 1)
                                @if(in_array('celebrity',$allowed) || Auth::user()->is_superadmin == 1)
                                    <li>
                                        <a href="{{ route('celebrity.index') }}">
                                            <span class="icon-celebrity"></span>
                                            <span> {{ __("Influencer") }} </span>
                                        </a>
                                    </li>
                                @endif
                            @endif

                            @if($client_preference_detail->get_estimations == 1)
                            <li>
                                <a href="{{route('estimations.index')}}">
                                    <span class="icon-estimation"><?xml version="1.0" encoding="iso-8859-1"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 363 363" style="enable-background:new 0 0 363 363;" xml:space="preserve"><g><path d="M149.475,0H20.525C9.208,0,0,9.208,0,20.525v128.949C0,160.792,9.208,170,20.525,170h128.949c11.317,0,20.525-9.208,20.525-20.525V20.525C170,9.208,160.792,0,149.475,0z M155,149.475c0,3.047-2.479,5.525-5.525,5.525H20.525c-3.047,0-5.525-2.479-5.525-5.525V20.525C15,17.479,17.479,15,20.525,15h128.949c3.047,0,5.525,2.479,5.525,5.525V149.475z"/><path d="M149.475,193H20.525C9.208,193,0,202.208,0,213.525v128.949C0,353.792,9.208,363,20.525,363h128.949c11.317,0,20.525-9.208,20.525-20.525V213.525C170,202.208,160.792,193,149.475,193z M155,342.475c0,3.047-2.479,5.525-5.525,5.525H20.525c-3.047,0-5.525-2.479-5.525-5.525V213.525c0-3.047,2.479-5.525,5.525-5.525h128.949c3.047,0,5.525,2.479,5.525,5.525V342.475z"/><path d="M342.475,0H213.525C202.208,0,193,9.208,193,20.525v128.949c0,11.318,9.208,20.525,20.525,20.525h128.949c11.317,0,20.525-9.208,20.525-20.525V20.525C363,9.208,353.792,0,342.475,0z M348,149.475c0,3.047-2.479,5.525-5.525,5.525H213.525c-3.047,0-5.525-2.479-5.525-5.525V20.525c0-3.047,2.479-5.525,5.525-5.525h128.949c3.047,0,5.525,2.479,5.525,5.525V149.475z"/><path d="M342.475,193H213.525C202.208,193,193,202.208,193,213.525v128.949c0,11.318,9.208,20.525,20.525,20.525h128.949c11.317,0,20.525-9.208,20.525-20.525V213.525C363,202.208,353.792,193,342.475,193z M348,342.475c0,3.047-2.479,5.525-5.525,5.525H213.525c-3.047,0-5.525-2.479-5.525-5.525V213.525c0-3.047,2.479-5.525,5.525-5.525h128.949c3.047,0,5.525,2.479,5.525,5.525V342.475z"/><path d="M130,77.5H92.5V40c0-4.142-3.357-7.5-7.5-7.5s-7.5,3.358-7.5,7.5v37.5H40c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h37.5V130c0,4.142,3.357,7.5,7.5,7.5s7.5-3.358,7.5-7.5V92.5H130c4.143,0,7.5-3.358,7.5-7.5S134.143,77.5,130,77.5z"/><path d="M315.123,47.877c-2.93-2.929-7.678-2.929-10.607,0L278,74.393l-26.516-26.516c-2.93-2.929-7.678-2.929-10.607,0c-2.929,2.929-2.929,7.678,0,10.606L267.393,85l-26.517,26.517c-2.929,2.929-2.929,7.678,0,10.606c1.465,1.464,3.385,2.197,5.304,2.197s3.839-0.732,5.304-2.197L278,95.607l26.516,26.516c1.465,1.464,3.385,2.197,5.304,2.197s3.839-0.732,5.304-2.197c2.929-2.929,2.929-7.678,0-10.606L288.606,85l26.517-26.517C318.052,55.555,318.052,50.806,315.123,47.877z"/><path d="M323,270.5h-90c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h90c4.143,0,7.5-3.358,7.5-7.5S327.143,270.5,323,270.5z"/><path d="M126,250.5H44c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h82c4.143,0,7.5-3.358,7.5-7.5S130.143,250.5,126,250.5z"/><path d="M126,290.5H44c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h82c4.143,0,7.5-3.358,7.5-7.5S130.143,290.5,126,290.5z"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></span>
                                    <span> {{ __('Estimations') }} </span>
                                </a>
                            </li>
                            @if($client_preference_detail->is_scan_qrcode_bag == 1)
                            <li>
                                <a href="{{route('estimations.barcode')}}">
                                    <span class="icon-estimation"><?xml version="1.0" encoding="iso-8859-1"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 363 363" style="enable-background:new 0 0 363 363;" xml:space="preserve"><g><path d="M149.475,0H20.525C9.208,0,0,9.208,0,20.525v128.949C0,160.792,9.208,170,20.525,170h128.949c11.317,0,20.525-9.208,20.525-20.525V20.525C170,9.208,160.792,0,149.475,0z M155,149.475c0,3.047-2.479,5.525-5.525,5.525H20.525c-3.047,0-5.525-2.479-5.525-5.525V20.525C15,17.479,17.479,15,20.525,15h128.949c3.047,0,5.525,2.479,5.525,5.525V149.475z"/><path d="M149.475,193H20.525C9.208,193,0,202.208,0,213.525v128.949C0,353.792,9.208,363,20.525,363h128.949c11.317,0,20.525-9.208,20.525-20.525V213.525C170,202.208,160.792,193,149.475,193z M155,342.475c0,3.047-2.479,5.525-5.525,5.525H20.525c-3.047,0-5.525-2.479-5.525-5.525V213.525c0-3.047,2.479-5.525,5.525-5.525h128.949c3.047,0,5.525,2.479,5.525,5.525V342.475z"/><path d="M342.475,0H213.525C202.208,0,193,9.208,193,20.525v128.949c0,11.318,9.208,20.525,20.525,20.525h128.949c11.317,0,20.525-9.208,20.525-20.525V20.525C363,9.208,353.792,0,342.475,0z M348,149.475c0,3.047-2.479,5.525-5.525,5.525H213.525c-3.047,0-5.525-2.479-5.525-5.525V20.525c0-3.047,2.479-5.525,5.525-5.525h128.949c3.047,0,5.525,2.479,5.525,5.525V149.475z"/><path d="M342.475,193H213.525C202.208,193,193,202.208,193,213.525v128.949c0,11.318,9.208,20.525,20.525,20.525h128.949c11.317,0,20.525-9.208,20.525-20.525V213.525C363,202.208,353.792,193,342.475,193z M348,342.475c0,3.047-2.479,5.525-5.525,5.525H213.525c-3.047,0-5.525-2.479-5.525-5.525V213.525c0-3.047,2.479-5.525,5.525-5.525h128.949c3.047,0,5.525,2.479,5.525,5.525V342.475z"/><path d="M130,77.5H92.5V40c0-4.142-3.357-7.5-7.5-7.5s-7.5,3.358-7.5,7.5v37.5H40c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h37.5V130c0,4.142,3.357,7.5,7.5,7.5s7.5-3.358,7.5-7.5V92.5H130c4.143,0,7.5-3.358,7.5-7.5S134.143,77.5,130,77.5z"/><path d="M315.123,47.877c-2.93-2.929-7.678-2.929-10.607,0L278,74.393l-26.516-26.516c-2.93-2.929-7.678-2.929-10.607,0c-2.929,2.929-2.929,7.678,0,10.606L267.393,85l-26.517,26.517c-2.929,2.929-2.929,7.678,0,10.606c1.465,1.464,3.385,2.197,5.304,2.197s3.839-0.732,5.304-2.197L278,95.607l26.516,26.516c1.465,1.464,3.385,2.197,5.304,2.197s3.839-0.732,5.304-2.197c2.929-2.929,2.929-7.678,0-10.606L288.606,85l26.517-26.517C318.052,55.555,318.052,50.806,315.123,47.877z"/><path d="M323,270.5h-90c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h90c4.143,0,7.5-3.358,7.5-7.5S327.143,270.5,323,270.5z"/><path d="M126,250.5H44c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h82c4.143,0,7.5-3.358,7.5-7.5S130.143,250.5,126,250.5z"/><path d="M126,290.5H44c-4.143,0-7.5,3.358-7.5,7.5s3.357,7.5,7.5,7.5h82c4.143,0,7.5-3.358,7.5-7.5S130.143,290.5,126,290.5z"/></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg></span>
                                    <span> {{ __('QR Code') }} </span>
                                </a>
                            </li>
                            @endif
                            @endif

                            @if(!empty($client_preference) && $client_preference->enquire_mode == 1)
                            @if(@auth()->user()->can('inquiry-code-view') || Auth::user()->is_superadmin == 1)
                                    <li>
                                        <a href="{{ route('inquiry.index') }}">
                                            <span class="icon-question"></span>
                                            <span> {{ __("Inquiries") }} </span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                            @if(@auth()->user()->can('tool-view') || Auth::user()->is_superadmin == 1)
                            <li>
                                <a href="{{route('tools.index')}}">
                                    <span class="icon-settings-1-1"></span>
                                    <span> {{ __('Tools') }} </span>
                                </a>
                            </li>
                            @endif
                            @if(@auth()->user()->can('database-log-view') || Auth::user()->is_superadmin == 1)
                                <li>
                                    <a href="{{route('databaseAuditingLogs')}}">
                                        <span class="icon-tax"></span>
                                        <span> {{ __('DB Audit Logs') }} </span>
                                    </a>
                                </li>
                            @endif
                            @if( Auth::user()->is_superadmin == 1 && @$getAdditionalPreference['product_measurment']==1)
                            <li>
                                <a href="{{route('measurement.index')}}">
                                    <span class="icon-celebrity"></span>
                                    <span> {{ __('Product Measurment') }} </span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>

<!-- spinner Start -->

<!-- <div class="nb-spinner-main">

    <div class="nb-spinner"></div>

    </div> -->

    <!-- spinner End -->
