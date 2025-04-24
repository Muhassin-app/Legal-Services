<div class="order_detail order_detail_data align-items-top pb-3 card-box no-gutters mb-2">
	<% if((vendor.delivery_fee > 0) || (order.scheduled_date_time)){%>
		<div class="progress-order font-12">
			<% if(order.scheduled_slot==null){%>
			<% if(order.scheduled_date_time){%>
			<span class="badge badge-success ml-2">Scheduled</span>
			<span class="ml-2">{{__('Your order will arrive by ')}}<%=order.converted_scheduled_date_time %></span>
			<%}else{%>
			<span class="ml-2">{{__('Your order will arrive by ')}}<%=vendor.ETA %></span>
			<%}%>
			<%}else{%>
			<span class="badge badge-success ml-2">Scheduled</span> <span class="ml-2">{{__('Your order will arrive by ')}}<%=order.converted_scheduled_date_time %>, Slot : <%=order.scheduled_slot %></span>
			<%}%>
		</div>
		<%}%>
		<span class="left_arrow pulse"></span>
		<div class="row">
			<div class="col-5 col-sm-3">
				<h5 class="m-0">{{__('Order Status')}}</h5>
				<ul class="status_box mt-1 pl-0">
					<% if(vendor.order_status){%>
					<li>
						<% if(vendor.order_status=='placed'){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/order-icon.svg')}}" alt="" title="">
						<%}else if(vendor.order_status=='accepted'){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/payment_icon.svg')}}" alt="" title="">
						<%}else if(vendor.order_status=='processing'){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/customize_icon.svg')}}" alt="" title="">
						<%}else if(vendor.order_status=='out for delivery'){%> <img class="blur-up lazyload" data-src="{{asset('assets/images/driver_icon.svg')}}" alt="" title="">
						<%}%>
						<label class="m-0 in-progress"><%=(vendor.order_status).charAt(0).toUpperCase() + (vendor.order_status).slice(1) %></label>
					</li>
					<%}%>
					<% if(vendor.dispatch_traking_url){%>
					<li class="">
						<img class="blur-up lazyload" data-src="{{asset('assets/images/order-icon.svg')}}" alt="" title="">
						<a href="{{route('front.booking.details')}}/<%=order.order_number %>" target="_blank">{{__('Details')}}</a>
					</li>
					<%}%>
					<% if(vendor.dineInTable){%>
					<li>
						<h5 class="mb-1">{{__('Dine-in')}}</h5>
						<h6 class="m-0"><%=vendor.dineInTableName %></h6>
						<h6 class="m-0">Category : <%=vendor.dineInTableCategory %></h6>
						<h6 class="m-0">Capacity : <%=vendor.dineInTableCapacity %></h6>
					</li>
					<%}%>
				</ul>
			</div>
			<div class="col-7 col-sm-4">
				<ul class="product_list d-flex align-items-center p-0 flex-wrap m-0">
					<% _.each(vendor.products, function(product, k){%>
						<% if(vendor.vendor_id==product.vendor_id){%>
							<li class="text-center"> <img class="blur-up lazyload" style="height:50px;" data-src="<%=product.image_url %>" alt="" title=""> <span class="item_no position-absolute">x <%=product.quantity %></span>
								<label class="items_price">{{Session::get('currencySymbol')}}
									<%= Helper.formatPrice(product.price * product.pricedoller_compare) %>
								</label>
							</li>
							<% product_total_price=product.price * product.doller_compare; product_total_count +=product.quantity * product_total_price; product_taxable_amount +=product.taxable_amount; total_tax_order_price +=product.taxable_amount; %>
								<%}%>
									<%}); %>
				</ul>
			</div>
			<div class="col-md-5 mt-md-0 mt-sm-2">
				<ul class="price_box_bottom m-0 p-0">
					<li class="d-flex align-items-center justify-content-between">
						<label class="m-0">{{__('Product Total')}}</label> <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor.subtotal_amount) %></span> </li>
					<li class="d-flex align-items-center justify-content-between">
						<label class="m-0">{{__('Coupon Discount')}}</label> <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor.discount_amount) %></span> </li>
					<li class="d-flex align-items-center justify-content-between">
						<label class="m-0">{{__('Delivery Fee')}}</label> <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor.delivery_fee) %></span> </li>
					<li class="grand_total d-flex align-items-center justify-content-between">
						<label class="m-0">{{__('Amount')}}</label>
						<% product_subtotal_amount=product_total_count - vendor.discount_amount + vendor.delivery_fee; subtotal_order_price +=product_subtotal_amount; %> <span>{{Session::get('currencySymbol')}}<%= Helper.formatPrice(vendor.payable_amount) %></span> </li>
				</ul>
			</div>
		</div>
</div>