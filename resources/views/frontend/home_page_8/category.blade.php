{{-- @dd($category) --}}
<div class="category-block">
    <a class="brand-box d-block black-box" href="{{route('categoryDetail', $category->slug)}}">
        <!-- <div class="bg-outter">
            <img class="brand-banner" src="{{asset('images/template-8/bg-shape.png')}}" alt="" title="">
        </div> -->
        <div class="brand-img">
            <img class="blur-up lazyload" data-src="{{$category->icon['proxy_url'].'200/200'.$category->icon['image_path']}}" alt="" title="">
            <h6 class="text-center">{{ $category->name }}</h6>
        </div>
    </a>
</div>