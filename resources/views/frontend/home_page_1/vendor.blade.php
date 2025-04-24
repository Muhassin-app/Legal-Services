<div class="product-card-box position-relative ">
    <a class="suppliers-box d-block" href="{{route('vendorDetail')}}/{{ $vendor->slug }}">
        <div class="suppliers-img-outer position-relative">
            @if($vendor->is_vendor_closed==1) 
                <img class="fluid-img mx-auto blur blurload grayscale-image" data-src="{{ get_file_path($vendor->logo,'FILL_URL','200','200') }}" src="{{ get_file_path($vendor->logo,'FILL_URL','20','20') }}" alt="" title="">
            @else
                <img class="fluid-img mx-auto blur blurload" data-src="{{ get_file_path($vendor->logo,'FILL_URL','200','200') }}" src="{{ get_file_path($vendor->logo,'FILL_URL','20','20') }}" alt="" title="">
            @endif
            @if( isset($vendor->timeofLineOfSightDistance)) 
                <div class="pref-timing"> <span>{{ $vendor->timeofLineOfSightDistance }}</span> </div>
            @endif
        </div>
        <div class="supplier-rating">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-1 ellips">{{ $vendor->name }}</h6> 
                @if($client_preference_detail && $client_preference_detail->rating_check==1 ) 
                    @if($vendor->vendorRating >0)
                        <span class="rating-number"> {{ $vendor->vendorRating }}</span>
                    @endif
                @endif 
            </div>
            <p title="{{ $vendor->categoriesList }}" class="vendor-cate mb-1 ellips">
                {{ $vendor->categoriesList }}
            </p>
        </div>
    </a>
</div>