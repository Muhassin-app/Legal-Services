@if(isset($vendor) && isset($vendor->banner))
    @php
    $url = $vendor->banner['image_fit'] . '1920/1080' . $vendor->banner['image_path'];
   
    if(empty(imageExistsS3($url))){
      $url = loadDefaultImage(); 
    }
    @endphp
    <div class="common-banner my-banner"><img class="blur-up lazyload" alt="" data-src="{{ $url }}" ></div>
@elseif(isset($brand))
    @php
    $url = $brand->image_banner['image_fit'] . '1920/1080' . $brand->image_banner['image_path'];
    if(empty(imageExistsS3($url))){
      $url = loadDefaultImage(); 
    }
    @endphp
    <div class="common-banner my-banner"><img class="blur-up lazyload" alt="" data-src="{{ $url }}" ></div>
@elseif(isset($celebrity))
    @php
    $url = $celebrity->avatar['image_fit'] . '1920/1080' . $celebrity->avatar['image_path'];
    if(empty(imageExistsS3($url))){
      $url = loadDefaultImage(); 
    }
    @endphp
    <div class="common-banner my-banner"><img class="blur-up lazyload" data-src="{{ $url }}" alt=""></div>
@elseif(isset($category))
    @php
    $url = $category->image['image_fit'] . '1920/1080' . $category->image['image_path'];
    if(empty(imageExistsS3($url))){
      $url = loadDefaultImage(); 
    }
    @endphp
    <div class="common-banner my-banner"><img alt="" class="blur-up lazyload" data-src="{{ $url }}"></div>
@else
    @php $url = loadDefaultImage(); @endphp
    <div class="common-banner my-banner"><img alt="" class="blur-up lazyload" data-src="{{ $url }}" ></div>
@endif
