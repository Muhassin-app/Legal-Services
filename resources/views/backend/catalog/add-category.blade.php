
<div class="row ">
    <div class="col-md-12">
        <div class="row border-bottom">
            <div class="col-md-3">
                @csrf
                <div class="form-group" id="slugInputEdit">
                    {!! Form::label('title', __('URL Slug'),['class' => 'control-label']) !!}
                    {!! Form::text('slug', null, ['class'=>'form-control', 'required' => 'required', 'onkeypress' => "return alphaNumeric(event)", 'id' => 'slug']) !!}
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                    {!! Form::hidden('login_user_type', session('preferences.login_user_type'), ['class'=>'form-control']) !!}
                    {!! Form::hidden('login_user_id', auth()->user()->id, ['class'=>'form-control']) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('title', __('Select Parent Category'),['class' => 'control-label']) !!}
                    <select class="selectize-select1 form-control parent-category" id="cateSelectBox" name="parent_cate">
                        @foreach($parCategory as $pc)
                            <option value="{{$pc->id}}">{{$pc->translation_one ? ucfirst($pc->translation_one['name']) : ' '}}</option>
                        @endforeach
                    </select>
                    <span class="invalid-feedback" role="alert">
                        <strong></strong>
                    </span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('title', __('Visible In Menus'),['class' => 'control-label']) !!}
                    <div>
                        <input type="checkbox" data-plugin="switchery" name="is_visible" class="form-control switch_menu" data-color="#43bee1" checked='checked'>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('title', __('Wishlist'),['class' => 'control-label']) !!}
                    <div>
                        <input type="checkbox" data-plugin="switchery" name="show_wishlist" class="form-control wishlist_switch" data-color="#43bee1" checked='checked'>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6" style="display:none;" id="addDispatcherHide">
                <div class="form-group mb-0">
                </div>
            </div>
        </div>
        <div class="row border-bottom category-icon-image pt-1">
            <div class="col-md-3">
                <label>{{ __("Category Icon") }}</label>
                <input type="file" accept="image/*" data-plugins="dropify" name="icon" class="dropify" data-default-file="" />
                <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 150x150</label>
            </div>
            <div class="col-md-3">
                <label>{{ __("Hover Icon") }}</label>
                <input type="file" accept="image/*" data-plugins="dropify" name="icon_two" class="dropify" data-default-file="" />
                <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 150x150</label>
            </div>
            <div class="col-md-6">
                <label>{{ __('Banner image') }}</label>
                <input type="file" accept="image/*" data-plugins="dropify" name="image" class="dropify" data-default-file="" />
                <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 1370x300</label>
            </div>
        </div>
        <div class="row border-bottom pt-1">
            <div class="px-2 ">
                <div class="mb-1 Category-select_option">
                    <select class="form-control w-auto" name="cat_lang[lang_id]">
                        @foreach($category->translationSetUnique as $trans)
                            <option value="{{ $trans->language_id }}">{{ $trans->langName.' Language' }}</option>
                        @endforeach
                        @foreach($languages as $langs)
                            <option value="{{ $langs->langId }}">{{ $langs->langName.' Language' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row rowYK">
                    <div class="col-md-6">
                        <div class="form-group" id="nameInputEdit">
                            <label for="title" class="control-label">Name</label>
                                <input class="form-control" required="required" name="cat_lang[name]" id="cat-lang-name" type="text" value="">
                                <span class="invalid-feedback" role="alert"><strong></strong></span>
                        </div>
                        <div class="form-group">
                            <label for="title" class="control-label">Meta Description</label>
                            <textarea class="form-control" rows="3" name="cat_lang[meta_description]" id="cat-lang-meta-description" cols="50"></textarea>
                        </div>
                    </div>
                    <input name="cat_lang[language_id]" id="cat-lang-language-id" type="hidden" value="">
                    <input name="cat_lang[trans_id]" id="cat-lang-trans-id" type="hidden" value="">
                    <div class="col-md-6">
                        <div class="form-group" id="meta_titleInput">
                            <label for="title" class="control-label">Meta Title</label>
                            <input class="form-control" name="cat_lang[meta_title]" id="cat-lang-meta-title" type="text" value="">
                        </div>
                        <div class="form-group">
                            <label for="title" class="control-label">Meta Keywords</label>
                            <textarea class="form-control" rows="3" name="cat_lang[meta_keywords]" id="cat-lang-meta-keywords" cols="50"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="row mt-3 edit-category modal-category-list">
            @foreach($typeArray as $type)
            @if($type->title == 'Celebrity' && $preference->celebrity_check == 0)
            @continue
            @endif
            <div class="col-6 col-sm-3">
                <div class="card p-0 text-center select-category" id="tooltip-container">
                    <input class="form-check-input type-select" for="edit" type="radio" id="type_id_{{$type->id}}" {{$type->id == 1 ? 'checked=""' : " "}} name="type_id" @if($category->type_id == $type->id) checked @endif value="{{$type->id}}">
                    <label for="type_id_{{$type->id}}" class="card-body p-0 mb-0">
                        <div class="category-img">
                            <img style="height:208px;" src="{{url('images/'.$type->image)}}" alt="">
                        </div>
                        <div class="form-check form-check-info modal-category-btm-title p-2">
                            <h6 for="customradio5">{{$type->title}}</h6>
                            <!-- <p class="add-cat-text text-left">Lorem ipsum... </p> -->
                        </div>
                    </label>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row" id="additional-fields-dv">
            <div class="px-3 py-2 mb-3">
                <div class="row rowYK">
                    <h4 class="col-md-12">{{ __("Additional Fields") }}</h4>
                    <div class="col-md-12">
                        <div class="row w-100" id="addProductHide">
                            <div class="form-group">
                                {!! Form::label('title', __('Can Add Products'),['class' => 'control-label']) !!}
                                <div>
                                    <input type="checkbox" data-plugin="switchery" name="can_add_products" class="form-control add_product_switch" data-color="#43bee1" checked='checked'>
                                </div>
                            </div>
                        </div>
                        <div style="{{($category->type_id != 1) ? 'display:none;' : ''}}" class="border-bottom cat-banners pt-1">
                            <label>{{ __("Banners") }}</label>
                            <div class="row w-100 cat-banner-apnd">
                                <div class="col cat-banner-fst">
                                    <input type="file" accept="image/*" data-plugins="dropify" name="cat_banner[]" class="dropify" data-default-file="" />
                                    <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 1370x300</label>
                                </div>
                                <div class="col">
                                    <input type="file" accept="image/*" data-plugins="dropify" name="cat_banner[]" class="dropify" data-default-file="" />
                                    <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 1370x300</label>
                                </div>
                                <div class="col">
                                    <input type="file" accept="image/*" data-plugins="dropify" name="cat_banner[]" class="dropify" data-default-file="" />
                                    <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 1370x300</label>
                                </div>
                                <div class="col">
                                    <input type="file" accept="image/*" data-plugins="dropify" name="cat_banner[]" class="dropify" data-default-file="" />
                                    <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 1370x300</label>
                                </div>
                                <div class="col">
                                    <input type="file" accept="image/*" data-plugins="dropify" name="cat_banner[]" class="dropify" data-default-file="" />
                                    <label class="logo-size d-block text-right mt-1">{{ __("Image Size") }} 1370x300</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {

    var inputs = $('input.radio-none');
    var checked = inputs.filter(':checked').val();

    inputs.on('click', function() {

        if ($(this).val() === checked) {

            $(this).prop('checked', false);

            checked = '';

        } else {
            $(this).prop('checked', true);
            checked = $(this).val();

        }
    });

});
</script>