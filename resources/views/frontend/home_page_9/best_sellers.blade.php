<div class="col-md-12 p-0">
    <div class="deals-product product-card-box position-relative text-center al_custom_vendors_sec"  >
        <a class="suppliers-box d-block" href="{{route('vendorDetail')}}/{{ $vendor->slug }}">
            <div class="suppliers-img-outer position-relative ">
                @if($vendor->is_vendor_closed==1) 
                    <img class="fluid-img mx-auto blur-up lazyload grayscale-image" data-src="{{ $vendor->logo['image_fit'] }}200/200{{ $vendor->logo['image_path'] }}" alt="" title="">
                @else
                    <img  class="fluid-img mx-auto blur-up lazyload" data-src="{{ $vendor->logo['image_fit'] }}200/200{{ $vendor->logo['image_path'] }}" alt="" title="">
                @endif

            </div>
            <div class="supplier-rating">
                <h4>{{ $vendor->name }}</h4>
                {{-- <h5>10-50% OFF</h5> --}}
                <a href="#">SHOP NOW</a>
                <!-- {{--<p title="<%=vendor.categoriesList %>" class="vendor-cate mb-1 ellips d-none">
                    <%=vendor.categoriesList %>
                </p>--}}
                    {{-- <% if(vendor.timeofLineOfSightDistance !=undefined){%>
                        <div class="pref-timing"> <span><%=vendor.timeofLineOfSightDistance %></span> </div>
                    <%}%> --}} -->
            </div>
            @if($client_preference_detail && $client_preference_detail->rating_check==1)
            @if($vendor->vendorRating >0) <span class="rating-number">{{ $vendor->vendorRating }} </span>
            @endif @endif 
        </a>
    </div>
</div>