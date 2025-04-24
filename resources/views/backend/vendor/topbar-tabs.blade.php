<ul class="nav nav-pills navtab-bg nav-justified">
        <li class="nav-item">
            <a href="{{ route('vendor.catalogs', $vendor->id) }}" aria-expanded="false"
                class="nav-link {{ $tab == 'catalog' ? 'active' : '' }} {{ $vendor->status == 1 ? '' : 'disabled' }}">
                {{ __('Catalog') }}
            </a>
        </li>
        
        @if((($client_preference_detail->business_type != 'taxi') && (($client_preference_detail->is_hyperlocal==1) || ($vendor->show_slot!=1))) || (($client_preference_detail->business_type == 'taxi') && ($client_preference_detail->pickup_delivery_service_area == 1)))
        <li class="nav-item">
            <a href="{{ route('vendor.show', $vendor->id) }}" aria-expanded="false"
                class="nav-link {{ $tab == 'configuration' ? 'active' : '' }} {{ $vendor->status == 1 ? '' : 'disabled' }}">
                {{ __('Configuration') }}
            </a>
        </li>
        @endif
        <!-- @if ($client_preference_detail->business_type != 'taxi')
            <li class="nav-item">
                <a href="{{ route('vendor.categories', $vendor->id) }}" aria-expanded="true"
                    class="nav-link {{ $tab == 'category' ? 'active' : '' }} {{ $vendor->status == 1 ? '' : 'disabled' }}">
                    {{ __('Categories & Add Ons') }}
                </a>
            </li>
        @endif -->
        @if ($is_payout_enabled == 1)
            <li class="nav-item">
                <a href="{{ route('vendor.payout', $vendor->id) }}" aria-expanded="false"
                    class="nav-link {{ $tab == 'payout' ? 'active' : '' }} {{ $vendor->status == 1 ? '' : 'disabled' }}">
                    {{ __('Payout') }}
                </a>
            </li>
        @endif

         @if(@getAdditionalPreference(['is_bid_enable'])['is_bid_enable'] == 1)
            <li class="nav-item">
                <a href="{{ route('vendor.bid.request', $vendor->id) }}" aria-expanded="false"
                    class="nav-link">
                    {{ __('Bid Requests').' ('.@$reqBidCnt.')' }}
                </a>
            </li>
         @endif
        
    </ul>
