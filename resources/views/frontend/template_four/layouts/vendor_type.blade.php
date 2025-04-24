@if($client_preference_detail->business_type != 'taxi' && (\Request::route()->getName() != 'customer.login') && (\Request::route()->getName() != 'customer.register') && (\Request::route()->getName() != 'user.verify') && (\Request::route()->getName() != 'user.profile') && (\Request::route()->getName() != 'user.addressBook') && (\Request::route()->getName() != 'user.orders') && (\Request::route()->getName() != 'user.wishlists') && (\Request::route()->getName() != 'user.loyalty') && (\Request::route()->getName() != 'user.wallet') && (\Request::route()->getName() != 'user.subscription.plans') && (\Request::route()->getName() != 'user.changePassword') && (\Request::route()->getName() != 'showCart') && (\Request::route()->getName() != 'order.success') && (\Request::route()->getName() != 'order.return.success'))
<div class="main-menu @if((\Request::route()->getName() != 'userHome')) no-category-image @endif">
			<div class="container-fluid text-center py-md-3 py-1" >
				<div class="row align-items-center justify-content-center position-initial">
					<div class="al_count_tabs_fourdesign"  >
								@if($mod_count > 1)
								<ul class="nav nav-tabs navigation_tab_al nav-material tab-icons vendor_mods" id="top-tab" role="tablist">
									@foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value)
										@php
										$clientVendorTypes = $vendor_typ_key.'_check';
										$VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
										$NomenclatureName = getNomenclatureName($vendor_typ_value, true);
										@endphp
										
										@if($client_preference_detail->$clientVendorTypes == 1)
										<li class="navigation-tab-item" role="presentation"> <a
										class="nav-link al_delivery {{($mod_count==1 || (Session::get('vendorType')==$VendorTypesName) || (Session::get('vendorType')=='')) ? 'active' : ''}}"
										id="{{$VendorTypesName}}_tab" VendorType="{{$VendorTypesName}}" data-toggle="tab" href="#{{$VendorTypesName}}_tab" role="tab"
										aria-controls="profile" aria-selected="false">{{$NomenclatureName}}</a> </li>
										@endif
									@endforeach
								{{-- @if($client_preference_detail->delivery_check==1) @php $Delivery=getNomenclatureName('Delivery', true); $Delivery=($Delivery==='Delivery') ? __('Delivery') : $Delivery; @endphp
									<li class="navigation-tab-item" role="presentation">
										<a class="nav-link al_delivery {{($mod_count==1 || (Session::get('vendorType')=='delivery') || (Session::get('vendorType')=='')) ? 'active' : ''}}" id="delivery_tab" data-toggle="tab" href="#delivery_tab" role="tab" aria-controls="profile" aria-selected="false">
											{{$Delivery}}
										</a>
									</li>
									@endif @if($client_preference_detail->dinein_check==1) @php $Dine_In=getNomenclatureName('Dine-In', true); $Dine_In=($Dine_In==='Dine-In') ? __('Dine-In') : $Dine_In; @endphp
									<li class="navigation-tab-item " role="presentation">
										<a class="nav-link al_dinein {{($mod_count==1 || (Session::get('vendorType')=='dine_in')) ? 'active' : ''}}" id="dinein_tab" data-toggle="tab" href="#dinein_tab" role="tab" aria-controls="dinein_tab" aria-selected="false">
											{{$Dine_In}}
										</a>
									</li>
									@endif @if($client_preference_detail->takeaway_check==1)
									<li class="navigation-tab-item " role="presentation">
										@php $Takeaway=getNomenclatureName('Takeaway', true); $Takeaway=($Takeaway==='Takeaway') ? __('Takeaway') : $Takeaway; @endphp
										<a class="nav-link al_takeway {{($mod_count==1 || (Session::get('vendorType')=='takeaway')) ? 'active' : ''}}" id="takeaway_tab" data-toggle="tab" href="#takeaway_tab" role="tab" aria-controls="takeaway_tab" aria-selected="false">
											{{$Takeaway}}
										</a>
									</li>
									@endif --}}
									<div class="navigation-tab-overlay_alnew_design"></div>
								</ul>
								@endif
							</div>
				</div>
			</div>
		</div>
@endif