<div>
    <a class="brand-box d-block black-box" href="{{ $brand->redirect_url }}">
        <div class="brand-ing">
            <img class="blur-up lazyload" data-src="{{ $brand->image->image_fit }}200/250{{ $brand->image['image_path'] }}" alt="" title="">
        </div>
        <h6>
            {{ $brand->translation_title }}</h6>
    </a>
</div>