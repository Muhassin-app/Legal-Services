<div class="rating-form" {{ $dispatch_rating }}>
    <fieldset class="form-group">
        <legend class="form-legend">Rating:</legend>
        <div class="form-item">

        <input id="{{$rating_type['id']}}_rating-5" name="{{$rating_type['id']}}_rating" type="radio" value="5" {{ @$dispatch_rating == 5 ? 'checked' : '' }}/>
            <label for="{{$rating_type['id']}}_rating-5" data-value="5">
                <span class="rating-star">
                    <i class="fa fa-star-o"></i>
                    <i class="fa fa-star"></i>
                </span>
                <span class="ir">5</span>
            </label>
            <input id="{{$rating_type['id']}}_rating-4" name="{{$rating_type['id']}}_rating" type="radio" value="4"  {{ @$dispatch_rating == 4 ? 'checked' : '' }}/>
            <label for="{{$rating_type['id']}}_rating-4" data-value="4">
                <span class="rating-star">
                    <i class="fa fa-star-o"></i>
                    <i class="fa fa-star"></i>
                </span>
                <span class="ir">4</span>
            </label>
            <input id="{{$rating_type['id']}}_rating-3" name="{{$rating_type['id']}}_rating" type="radio" value="3"  {{ @$dispatch_rating == 3 ? 'checked' : '' }}/>
            <label for="{{$rating_type['id']}}_rating-3" data-value="3">
                <span class="rating-star">
                    <i class="fa fa-star-o"></i>
                    <i class="fa fa-star"></i>
                </span>
                <span class="ir">3</span>
            </label>
            <input id="{{$rating_type['id']}}_rating-2" name="{{$rating_type['id']}}_rating" type="radio" value="2"  {{ @$dispatch_rating == 2 ? 'checked' : '' }}/>
            <label for="{{$rating_type['id']}}_rating-2" data-value="2">
                <span class="rating-star">
                    <i class="fa fa-star-o"></i>
                    <i class="fa fa-star"></i>
                </span>
                <span class="ir">2</span>
            </label>
            <input id="{{$rating_type['id']}}_rating-1" name="{{$rating_type['id']}}_rating" type="radio" value="1"  {{ @$dispatch_rating == 1 ? 'checked' : '' }}/>
            <label for="{{$rating_type['id']}}_rating-1" data-value="1">
                <span class="rating-star">
                    <i class="fa fa-star-o"></i>
                    <i class="fa fa-star"></i>
                </span>
                <span class="ir">1</span>
            </label>

            <div class="form-output">
                ? / 5
            </div>

        </div>
    </fieldset>
</div>