<div class="product-box scale-effect">
    <div class="img-wrapper">
        <div class="front">
            <a href="{{route('vendorDetail')}}/{{ $vendor->slug }}">
                @if($vendor->is_vendor_closed==1) 
                    <img class="img-fluid blur-up lazyload m-auto bg-img grayscale-image" alt="xx" src="{{ get_file_path($vendor->logo,'FILL_URL','200','200') }}">
                @else
                        <img class="img-fluid blur-up lazyload m-auto bg-img" alt="xx" src="{{ get_file_path($vendor->logo,'FILL_URL','200','200') }}">
                @endif

            </a>
        </div>
    </div>
    <div class="product-detail inner_spacing text-center m-0 w-100">
        <a href="{{route('vendorDetail')}}/{{ $vendor->slug }}">
            <h3 class="d-flex justify-content-between p-0">
                <span>{{ $vendor->name }}</span>
       
                    @if($client_preference_detail && $client_preference_detail->rating_check == 1)
                    @if($vendor->vendorRating >0)
                            <span class="rating m-0">{{ $vendor->vendorRating }} <i class="fa fa-star text-white p-0"></i></span>
                        @endif
                    @endif
               
            </h3>
        </a>
        {{-- <% if(vendor.timeofLineOfSightDistance != undefined){ %>
            <h6 class="d-flex justify-content-between">
                <small><svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0.848633 6.15122C0.848633 2.7594 3.60803 0 6.99985 0C10.3917 0 13.1511 2.7594 13.1511 6.15122C13.1511 8.18227 12.1614 9.98621 10.6392 11.107L7.46151 15.7563C7.3573 15.9088 7.18455 16 6.99985 16C6.81516 16 6.64237 15.9088 6.5382 15.7563L3.36047 11.107C1.8383 9.98621 0.848633 8.18227 0.848633 6.15122ZM6.99981 10.4225C7.23979 10.4225 7.47461 10.4072 7.70177 10.3806C9.73302 10.0446 11.2871 8.27613 11.287 6.15122C11.287 3.78725 9.36375 1.86402 6.99977 1.86402C4.6358 1.86402 2.71257 3.78725 2.71257 6.15122C2.71257 8.27613 4.26665 10.0446 6.29786 10.3806C6.52498 10.4072 6.75984 10.4225 6.99981 10.4225ZM9.23683 6.15089C9.23683 7.38626 8.23537 8.38772 7.00001 8.38772C5.76464 8.38772 4.76318 7.38626 4.76318 6.15089C4.76318 4.91552 5.76464 3.91406 7.00001 3.91406C8.23537 3.91406 9.23683 4.91552 9.23683 6.15089Z" fill="black"/></svg> <%= vendor.lineOfSightDistance %></small>
                <small><i class="fa fa-clock"></i> <%= vendor.timeofLineOfSightDistance %></small>
            </h6>
        <% } %> --}}
    </div>
</div>