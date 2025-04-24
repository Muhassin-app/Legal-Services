<div>
{{-- {{dd($brand)}} --}}
    <a class="brand-box d-block black-box" href="{{ $brand->redirect_url }}">
        <div class="brand-ing">
            <img class="blur blurload" data-src="{{ get_file_path($brand->image,'FIT_URL','260','260') }}" src="{{ get_file_path($brand->image,'FIT_URL','26','26') }}" alt="" title="">
        </div>
        <h6>{{ $brand->translation_title }}</h6>
    </a>
</div>