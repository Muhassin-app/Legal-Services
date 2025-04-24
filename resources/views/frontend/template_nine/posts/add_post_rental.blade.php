@extends('layouts.store', ['title' => 'Add Post'])

@section('css')
    <link href="{{ asset('assets/libs/multiselect/multiselect.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')
    <div class="wrapper Register_form">
        <div class="container">
            <div class="start_form_register ">
                <div class="item">
                    <div class="alPostHead text-center bg-light position-relative py-3">
                        <!-- <a href="#" class="backArroww position-absolute"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg></a> -->
                        <h3>Post Your Ad</h3>
                    </div>


                    <div class=" row">
                        <div class="alPostBoxOuter form_top  col-md-11 mx-auto mt-2   ">
                            <div class="px-0">
                                <div class="d-flex mb-2 align-items-center justify-content-between alCategoryItemsHead">
                                    <h6 class="m-0">CHOOSE A CATEGORY </h6>
                                    <div class="fillter_div">
                                        <span>Filter:</span>
                                        <select name="category-filter" id="category_filter" value="">
                                            <option value="all">All</option>
                                            <option value="10">Rent</option>
                                            <option value="13">Sell</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="categoriesSlider">
                                    <ul class=" p-0 m-0 no-gutters view-all_cats slider category_responsive">
                                        @if (@$categories)
                                            @foreach ($categories as $key => $category)
                                                @php  $icon = $category['icon']['proxy_url'] . '200/200' . $category['icon']['image_path'];  @endphp
                                                <li class="px-1 category-list" id="category_{{ $category->id }}">
                                                    <a class="cate-item text-center w-100 py-3 mb-0 rounded select-category"
                                                        data-name="{{@$category['translation_one']['name'] }}"
                                                        data-id="{{ $category['id'] }}" data-type-id="{{$category->type_id}}" href="javascript:void(0);">
                                                        <div class="alCategoryItems">
                                                            <img class="" src="{{ $icon }}">                                                                                                              
                                                            <h3>{{ @$category['translation_one']['name'] }}</h3>
                                                        </div>
                                                    </a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                                <ul class=" p-0 m-0 no-gutters view-rental_cats d-none  ">
                                    <div class="category_responsive">
                                    @if (@$categories)
                                        @foreach ($categories as $key => $category)
                                            @php  $icon = $category['icon']['proxy_url'] . '200/200' . $category['icon']['image_path'];  @endphp
                                            @if ($category->type_id == 10)
                                                <li class="px-1 category-list" id="category_{{ $category->id }}">
                                                    <a class="cate-item text-center w-100 py-3 mb-0 rounded select-category"
                                                        data-name="{{ @$category['translation_one']['name'] }}"
                                                        data-id="{{ $category['id'] }}" data-type-id="{{$category->type_id}}" href="javascript:void(0);">
                                                        <div class="alCategoryItems">
                                                            <img class="" src="{{ $icon }}">                                                                                                              
                                                            <h3>{{ @$category['translation_one']['name'] }}</h3>
                                                        </div>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                    </div>
                                </ul>
                                <ul class=" p-0 m-0 no-gutters   view-p2psell_cats d-none ">
                                    <div class="category_responsive">
                                    @if (@$categories)
                                        @foreach ($categories as $key => $category)
                                            @php  $icon = $category['icon']['proxy_url'] . '200/200' . $category['icon']['image_path'];  @endphp
                                            @if ($category->type_id == 13)
                                                <li class="px-1 category-list" id="category_{{ $category->id }}">
                                                    <a class="cate-item text-center w-100 py-3 mb-0 rounded select-category"
                                                        data-name="{{ @$category['translation_one']['name'] }}"
                                                        data-id="{{ $category['id'] }}" data-type-id="{{$category->type_id}}" href="javascript:void(0);">
                                                        <div class="alCategoryItems">
                                                            <img class="" src="{{ $icon }}">                                                                                                              
                                                            <h3>{{ @$category['translation_one']['name'] }}</h3>
                                                        </div>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                    </div>
                                </ul>
                                <label class="cat-error text-danger mt-2 pl-1 d-none">Please select category.</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="alPostBoxOuter col-md-11 mx-auto mb-4 p2p-category-form d-none">
                            <div class="p-3">
                                <form action="{{ route('posts.addProductWithAttribute') }}" enctype="multipart/form-data"
                                    method="post" id="product_form">
                                    @csrf
                                    <h6 class="pb-0">SELECTED CATEGORY</h6>
                                    <input type="hidden" name="category_id" id="category_id" required />
                                    <nav aria-label="breadcrumb"
                                        class="d-flex justify-content-between align-items-center mb-2">
                                        <ol class="breadcrumb bg-transparent p-0 m-0">
                                            <li class="breadcrumb-item selected-category"></li>
                                            {{-- <li class="breadcrumb-item active" aria-current="page">Motors</li>                             --}}
                                        </ol>
                                        {{-- <ol class="breadcrumb bg-transparent p-0 m-0 alCategoryItemsHead">
                                        <li class="breadcrumb-item"><a href="">Back</a></li>
                                    </ol> --}}
                                    </nav>
                                    <div class="col-12 border py-2 px-3">
                                        <div class="row alPostItemsDataOuter  ">
                                            <div class="alPostItemsData">
                                                <h5 class="text-uppercase my-2">Include some details</h5>
                                                <div class="form-group">
                                                    <label>Title *</label>
                                                    <input type="text" class="form-control" name="product_name" required
                                                        id="" aria-describedby="">
                                                </div>
                                                <div class="form-group">
                                                    <label>Description *</label>
                                                    <textarea class="form-control" id="" name="product_description" required rows="3"></textarea>
                                                </div>
                                                {{-- <div class="form-group">
                                                    <label>Emirate *</label>
                                                    <input type="text" class="form-control" name="emirate" required
                                                        id="" aria-describedby="">
                                                </div> --}}
                                                <div class="form-group">
                                                    <label for="inputAddress">Location Availability *</label>
                                                    <input type="hidden" name="lat" id="latitude" value="">
                                                    <input type="hidden" name="long" id="longitude" value="">
                                                    <input type="text" name="address" class="form-control" id="address"
                                                        placeholder="{{ __('Address') }}"aria-label="Recipient's Address"
                                                        aria-describedby="button-addon2" value=""
                                                        autocomplete="off" required="required">
                                                </div>
                                                <div class="p2p-cat-fields d-none">
                                                    <div class="alPostItemsData">
                                                        <h5 class="text-uppercase py-3">SET A PRICE</h5>
                                                        <div class="form-group">
                                                            <label>Price *</label>
                                                            <div class="input-group mb-2">
                                                                <div class="input-group-prepend">
                                                                  <div class="input-group-text">{{getPrimaryCurrencySymbol()}}</div>
                                                                </div>
                                                                <input type="text" class="form-control" required name="p2p_price" id="" placeholder="">
                                                              </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rental-cat-fields d-none">
                                                    <div class="form-group">
                                                        <label>Pricing Detail For *</label>
                                                        <div class="input-group mb-2">
                                                            <div class="row">
                                                                <div class="col input-group-prepend">
                                                                    <div class="input-group-text">
                                                                        {{ getPrimaryCurrencySymbol() }}
                                                                    </div>
                                                                    <input type="text" class="form-control" required
                                                                        name="price" id="day_price" placeholder="Day">
                                                                </div>
                                                                <div class="col input-group-prepend">
                                                                    <div class="input-group-text">
                                                                        {{ getPrimaryCurrencySymbol() }}
                                                                    </div>
                                                                    <input type="text" class="form-control" required
                                                                        name="week_price" id="week_price" placeholder="Week"
                                                                        readonly>
                                                                </div>
                                                                <div class="col input-group-prepend">
                                                                    <div class="input-group-text">
                                                                        {{ getPrimaryCurrencySymbol() }}
                                                                    </div>
                                                                    <input type="text" class="form-control" required
                                                                        name="month_price" id="month_price"
                                                                        placeholder="Month" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Original Price of Item *</label>
                                                        <div class="input-group mb-2">
                                                            <div class="input-group-prepend">
                                                                <div class="input-group-text">{{ getPrimaryCurrencySymbol() }}
                                                                </div>
                                                            </div>
                                                            <input type="text" class="form-control" required
                                                                name="compare_at_price" id="" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Date Range *</label>
                                                        <input type="text" class="form-control" name="date_availability"
                                                            value="" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alPostItemsData" id="productAttributes"></div>
                                        </div>
                                        {{-- <div class="row alPostItemsDataOuter border-top">
                                        <div class="alPostItemsData">
                                            <h5 class="text-uppercase py-3">SET A PRICE</h5>
                                            <div class="form-group">
                                                <label>Price *</label>
                                                <div class="input-group mb-2">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">{{getPrimaryCurrencySymbol()}}</div>
                                                    </div>
                                                    <input type="text" class="form-control" required name="price" id="" placeholder="">
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                        <div class="row alPostItemsDataOuter ">
                                            <div class="alPostItemsData">
                                                <h5 class="text-uppercase py-3">Upload up to 20 photos</h5>
                                                <div class="form-group choose_file">
                                                    {{-- <input type="file" accept="image/*"   data-plugins="dropify" name="images[]" class="dropify ss_form_submit" id="image" multiple /> --}}
                                                    <input type="file" class="form-control-file" required
                                                        name="file[]" accept="image/*"
                                                         multiple>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="row alPostItemsDataOuter border-top">
                                        <div class="alPostItemsData">
                                            <h5 class="text-uppercase py-3">Confirm your location</h5>
                                            <div class="form-group">
                                                <label for="inputAddress">Address</label>
                                                <input type="text" class="form-control" id="inputAddress" placeholder="1234 Main St">
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label for="inputCity">City</label>
                                                    <input type="text" class="form-control" id="inputCity">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputState">State</label>
                                                    <select id="inputState" class="form-control">
                                                        <option selected>Choose...</option>
                                                        <option>...</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="inputZip">Zip</label>
                                                    <input type="text" class="form-control" id="inputZip">
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                        <div class="row alPostItemsDataOuter">
                                            <div class="alPostItemsData mt-4">
                                                <button type="submit" class="btn btn-outline-secondary btn-lg" id="save-post">Post Now</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <link href="{{ asset('assets/libs/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/dropify/dropify.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('assets/libs/dropzone/dropzone.min.js') }}"></script>
    <script src="{{ asset('assets/libs/dropify/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/libs/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/toastr.init.js') }}"></script>
    <script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).on('click', '.category-list', function() {
            $('.category-list').find('.select-category').removeClass('active');
            $(this).find('.select-category').addClass('active');
            $(this).show();
            $('.choose-category').show();
        });

        $('.dropify').dropify();
        $(document).on('click', '.select-category', function() {
            var category_id = $(this).data('id');
            $("#category_id").val(category_id);
            $(".selected-category").text($(this).data('name'));
            var type_id = $(this).data('type-id');
            $(".p2p-category-form").removeClass('d-none');
            if(type_id == '10'){
                $(".rental-cat-fields").removeClass('d-none');
                $(".p2p-cat-fields").addClass('d-none');
            }else{
                $(".rental-cat-fields").addClass('d-none');
                $(".p2p-cat-fields").removeClass('d-none');
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });
            $.ajax({
                url: "{{ route('category.attributes') }}",
                type: "GET",
                data: {
                    category_id: category_id
                },
                success: function(response) {
                    if (response.success) {
                        $("#productAttributes").html(response.html);
                    }
                },
            });
        });

        function checkAddressString(obj, name) {
            if ($(obj).val() == "") {
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
            }
        }

        //calender and day_price

        $(document).on('keyup', '#day_price', function() {
            var dayPrice = $('#day_price').val();
            var weekPrice = (dayPrice * 4) / 7;
            var monthPrice = (dayPrice * 4 * 3) / 30;
            $('#week_price').val(weekPrice.toFixed(2));
            $('#month_price').val(monthPrice.toFixed(2));
        });

        $(function() {
            var date = new Date();
            var currentMonth = date.getMonth();
            var currentDate = date.getDate();
            var currentYear = date.getFullYear();
            $('input[name="date_availability"]').daterangepicker({
                minDate: new Date(currentYear, currentMonth, currentDate),
                dateFormat: 'yy-mm-dd',
                //startDate: moment(date).add(1,'days'),
                // endDate: moment(date).add(2,'days'),
                locale: {
                    format: 'DD.MM.YYYY'
                }
            });
        });

        var form = document.getElementById("product_form");
        document.getElementById("save-post").addEventListener("click", function (e) {

        
            e.preventDefault();
            const elements = document.querySelectorAll('.select-category.active');
            const hasElements = elements.length > 0;
            var productName = $('input[name="product_name"]').val();
            var description = $('textarea[name="product_description"]').val();
            var address = $('input[name="address"]').val();
            var files = $('input[name="file[]')[0].files;

            // Validate form fields.
            if (productName === '') {
                sweetAlert.error('Product name is required', '');
                return;
            }
            if (description === '') {
                sweetAlert.error('Description is required', '');
                return;
            }
            if (address === '') {
                sweetAlert.error('Address is required', '');
                return;
            }
           
           
            if (files.length === 0) {
               sweetAlert.error('Image file is required', '');
               return;
           }
            if (hasElements) {
                $('.cat-error').addClass('d-none');
                form.submit();
            } else {
                $('.cat-error').removeClass('d-none');
            }
        });

        $(document).on('change', '#category_filter', function(){
            var value = $(this).val();
            var $viewAllCats = $('.view-all_cats');
            var $viewP2PSellCats = $('.view-p2psell_cats');
            var $viewRentalCats = $('.view-rental_cats');
            var $categoryID = $("#category_id");
            var $selectedCategory = $(".selected-category");
            var $p2pCategoryForm = $(".p2p-category-form");
    
            $viewAllCats.addClass('d-none');
            $viewP2PSellCats.addClass('d-none');
            $viewRentalCats.addClass('d-none');
            switch (value) {
                case '10':
                $viewRentalCats.removeClass('d-none');
                
                break;
                case '13':
                $viewP2PSellCats.removeClass('d-none');               
                // $(".slick-arrow").click();
                break;
                default:
                $viewAllCats.removeClass('d-none');
                break;
            }
            $('.category_responsive').slick('refresh');
            $categoryID.val('');
            $selectedCategory.text('');
            $p2pCategoryForm.addClass('d-none');
        });

    </script>

    <?php
    // dd(Session::get('toaster'));
    if (Session::has('toaster')) {
        $toast = Session::get('toaster');
        echo '<script>
                $(document).ready(function(){
                    $.NotificationApp.send("' .
            $toast['title'] .
            '", "' .
            $toast['body'] .
            '", "top-right", "' .
            $toast['color'] .
            '", "' .
            $toast['type'] .
            '");
                });
            </script>';
    }
    ?>
@endsection
