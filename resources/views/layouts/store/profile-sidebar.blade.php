<?php 
use App\Models\PaymentOption;


?>

@php
$urlImg = URL::to('/').'/assets/images/users/user-1.jpg';
$clientData = \App\Models\Client::select('id', 'logo','socket_url')->first();
$azulExist =  PaymentOption::where('code', 'azul')->where('status', 1)->first();
$getAdditionalPreference = getAdditionalPreference(['is_gift_card','is_token_currency_enable', 'is_rental_weekly_monthly_price']);
@endphp
@switch($client_preference_detail->business_type)
    @case('taxi')
        <?php $ordertitle = 'Rides'; ?>
        <?php $hidereturn = 1; ?>
        @break
    @default
    <?php $ordertitle = 'Orders';  ?>
@endswitch
<div class="dashboard-left">
    <div class="collection-mobile-back">
        <span class="filter-back d-lg-none d-inline-block">
            <i class="fa fa-angle-left" aria-hidden="true"></i> back
        </span>
    </div>
    <div class="block-content">
        <ul>
            <li class="{{ (request()->is('user/profile')) ? 'active' : '' }}"><a href="{{route('user.profile')}}">{{ __('Account Info') }}</a></li>
            <!-- @if($clientData->socket_url)
                <li  class="{{ (request()->is('user/chat/userVendor') || request()->is('user/chat/userAgent') ) ? 'active' : '' }}" >
                    <a href="#chat" data-toggle="collapse">
                    <span class="mdi-message"></span>
                        <span> {{ __('Chat') }} </span>
                    </a>
                    <div class="collapse" id="chat">
                        <ul class="nav-second-level">
                            <li  class="{{ (request()->is('user/chat/userVendor')) ? 'active' : '' }}">
                                <a href="{{route('userChat.UservendorChat')}}">{{ __('Vendor Chat') }}</a>
                            </li>
                            @if(p2p_module_status())
                            
                            <li  class="{{ (request()->is('user/chat/userToUser')) ? 'active' : '' }}">
                                <a href="{{route('userChat.UserToUserChat')}}">{{ __('User Chat') }}</a>
                            </li>
                            @endif
                            <li  class="{{ (request()->is('user/chat/userAgent')) ? 'active' : '' }}">
                                <a href="{{route('userChat.UserAgentChat')}}">{{ __('Driver Chat') }}</a>
                            </li>
 
                            {{-- <li>
                                <a href="{{route('report.productperformance')}}">{{ __("Product Performance Report") }}</a>
                            </li> --}}
                        </ul>
                    </div>
                </li>
            @endif -->
            <li class="{{ (request()->is('user/orders*')) ? 'active' : '' }}"><a href="{{route('user.orders')}}">{{ __('My '.getNomenclatureName($ordertitle, true) )}}</a></li>
            @if(false)
            <li class="{{ (request()->is('user/addressBook')) ? 'active' : '' }}"><a href="{{route('user.addressBook')}}">
                {{ __('Address Book') }}
            </a></li>
            @if(!empty($azulExist))
            
            <li class="{{ (request()->is('payment/get-user-cards')) ? 'active' : '' }}"><a href="{{route('payment.user.cards')}}">
                {{ __('Saved Cards') }}
            </a></li>
            @endif
            
           

            @if(@$getAdditionalPreference['is_rental_weekly_monthly_price'] == 1)
                <li class="{{ (request()->is('user/lander-orders*')) ? 'active' : '' }}"><a href="{{route('user.lander-orders')}}">{{ __('My Order As Lender')}}</a></li>
                <li class="{{ (request()->is('user/borrower-orders*')) ? 'active' : '' }}"><a href="{{route('user.borrower-orders')}}">{{ __('My Order As Borrower')}}</a></li>
            @endif

            <li class="{{ (request()->is('user/wishlists')) ? 'active' : '' }}"><a href="{{route('user.wishlists')}}">{{ __(getNomenclatureName('Wishlist', true) )}}</a></li>
            <li class="{{ (request()->is('user/loyalty')) ? 'active' : '' }}"><a href="{{route('user.loyalty')}}">{{ __('My Loyalty') }}</a></li>
            
            <li class="{{ (request()->is('user/wallet')) ? 'active' : '' }}"><a href="{{route('user.wallet')}}">{{ $getAdditionalPreference['is_token_currency_enable'] ? __('My Wallet/Token') : __('My Wallet') }}</a></li>

            @if (@getAdditionalPreference(['is_bid_enable'])['is_bid_enable'] && getAdditionalPreference(['is_bid_enable'])['is_bid_enable']==1)
                <li class="{{ (request()->is('user/bidRequest') || request()->is('bid/Details') ) ? 'active' : '' }}"><a href="{{route('user.bidRequest')}}">{{ __('Bid Request') }}</a></li>
            @endif
          
            @if( (isset($client_preference_detail->subscription_mode)) && ($client_preference_detail->subscription_mode == 1) )
                <li class="{{ (request()->is('user/subscription*')) ? 'active' : '' }}"><a href="{{route('user.subscription.plans')}}">{{ __('My Subscriptions') }}</a></li>
            @endif
            <li class="last {{ (request()->is('user/notification')) ? 'active' : '' }}"><a href="{{route('user.notification')}}">{{ __('Notification') }}</a></li>
            <li class="last {{ (request()->is('user/my-ads')) ? 'active' : '' }}"><a href="{{route('user.productList')}}">{{__('My Ads')}}</a></li>
            @if(@getAdditionalPreference(['is_enable_allergic_items'])['is_enable_allergic_items'])
                <li class="last {{ (request()->is('user/allergic-items')) ? 'active' : '' }}"><a href="{{route('list.allergicItems')}}">{{__('Allergic Items')}}</a></li>
            @endif
            @if(is_p2p_vendor())
                <li class=""><a href="{{route('posts.index', ['fullPage'=>1])}}">{{ __('Add Post') }}</a></li>
    @endif
            @if(@getAdditionalPreference(['is_gift_card'])['is_gift_card']==1)
                <li class="{{ (request()->is('user/giftCard')) ? 'active' : '' }}"><a href="{{route('giftCard.index')}}">{{ __('Gift Card') }}</a></li>
            @endif
            @endif
            <li class="{{ (request()->is('user/changePassword')) ? 'active' : '' }}"><a href="{{route('user.changePassword')}}">{{ __('Change Password') }}</a></li>
            <li class="last {{ (request()->is('user/logout')) ? 'active' : '' }}"><a href="{{route('user.logout')}}">{{ __('Log Out') }}</a></li>
            <!-- <li class="last {{ (request()->is('user/refer-earn')) ? 'active' : '' }}"><a href="{{route('refer-earn.index')}}">{{ __('Refer & Earn') }}</a></li> -->
        </ul>
    </div>
</div>
