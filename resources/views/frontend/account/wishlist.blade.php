@extends('layouts.store', ['title' => __('My Wishlist')])
@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet" />
    <style type="text/css">
        .main-menu .brand-logo {
            display: inline-block;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .productVariants .firstChild {
            min-width: 150px;
            text-align: left !important;
            border-radius: 0% !important;
            margin-right: 10px;
            cursor: default;
            border: none !important;
        }

        .product-right .color-variant li,
        .productVariants .otherChild {
            height: 35px;
            width: 35px;
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer;
            border: 1px solid #f7f7f7;
            text-align: center;
        }

        .productVariants .otherSize {
            height: auto !important;
            width: auto !important;
            border: none !important;
            border-radius: 0%;
        }

        .product-right .size-box ul li.active {
            background-color: inherit;
        }

        .login-page .theme-card .theme-form input {
            margin-bottom: 5px;
        }

        .invalid-feedback {
            display: block;
        }

        .box-info table tr:first-child td {
            padding-top: .85rem;
        }        
        /* .wishlist_product {
            height: 550px;
            overflow-y: auto;
            overflow-x: hidden;
        } */
        
    </style>
@endsection
@section('content')
    @php
        $timezone = Auth::user()->timezone;
        $additionalPreference = getAdditionalPreference(['is_token_currency_enable', 'token_currency']);
    @endphp
    <section class="section-b-space ">
        <div class="container">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row my-md-3 mt-5 pt-4 align-items-start">
                <div class="col-lg-3">
                    <div class="account-sidebar"><a class="popup-btn">{{ __('My Account') }}</a></div>
                    <div class="dashboard-left  mb-3">
                        <div class="collection-mobile-back"><span class="filter-back d-lg-none d-inline-block"><i
                                    class="fa fa-angle-left" aria-hidden="true"></i>{{ __('Back') }}</span></div>
                        @include('layouts.store/profile-sidebar')
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="dashboard-right">
                        <div class="dashboard">
                            <div class="page-title">
                                <h2>{{ __(getNomenclatureName('Wishlist', true)) }}</h2>
                            </div>
                            <div class="box-account wishlist_product box-info mt-md-3 mt-2">
                                <div class="row">
                                    <div class="col-sm-12 table-responsive table-responsive-xs">
                                        <table class="table wishlist-table border">
                                            <thead>
                                                <tr class="table-head">
                                                    <th scope="col">
                                                        <div class="form-group mb-0">
                                                            @if (count($wishList))
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                        id="w-all">
                                                                    <label class="custom-control-label"
                                                                        for="w-all"></label>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </th>
                                                    <th scope="col">{{ __('Image') }}</th>
                                                    <th scope="col">{{ __(getNomenclatureName('Product Name', true)) }}
                                                    </th>
                                                    <th scope="col">{{ __('Price') }}</th>
                                                    <th scope="col">{{ __('Date Added') }}</th>
                                                    @if (!p2p_module_status())
                                                        <th scope="col">
                                                            {{ __(getNomenclatureName('Stock Status', true)) }}</th>
                                                    @endif
                                                    <th scope="col"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($wishList as $key => $wish)
                                                    <tr class="wishlist-row">
                                                        <td>
                                                            <div class="form-group mb-0">
                                                                @if (empty($wish['product']['deleted_at']))
                                                                    @if ($wish['product']['variant'][0]['quantity'] > 0)
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox"
                                                                                class="custom-control-input"
                                                                                id="wp-{{ $wish['product']['id'] }}"
                                                                                data-variant="{{ $wish['product']['variant'][0]['id'] }}">
                                                                            <label class="custom-control-label"
                                                                                for="wp-{{ $wish['product']['id'] }}"></label>
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="product-icon">
                                                                @foreach ($wish['product']['media'] as $media)
                                                                    <img src="{{ $media['image']['path']['proxy_url'] . '200/200' . $media['image']['path']['image_path'] }}"
                                                                        alt="Product Image" height="50">
                                                                @break
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="product-title pl-1">
                                                            <h4 class="m-0">
                                                                {{ $wish['product']['translation_title'] }}</h4>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if ($additionalPreference['is_token_currency_enable'])
                                                            {!! "<i class='fa fa-money' aria-hidden='true'></i> " !!}{{ getInToken(decimal_format($wish['product']['variant_price'])) }}
                                                        @else
                                                            {{ Session::get('currencySymbol') . decimal_format($wish['product']['variant_price']) }}
                                                        @endif
                                                    </td>
                                                    <td>{{ dateTimeInUserTimeZone($wish['added_on'], $timezone, true, false) }}
                                                    </td>
                                                    @if (!p2p_module_status())
                                                        <td>
                                                            @if (empty($wish['product']['deleted_at']))
                                                                @if ($wish['product']['variant_quantity'] > 0)
                                                                    <i class="fa fa-check-square-o mr-1"
                                                                        aria-hidden="true"></i>
                                                                    <span>{{ __('In Stock') }}</span>
                                                                @else
                                                                    <span>{{ __('Not In Stock') }}</span>
                                                                @endif
                                                            @else
                                                                <span
                                                                    class="text-danger">{{ __('This product no longer exists') }}</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td><a href="{{ route('removeWishlist', $wish['product']['sku']) }}"
                                                            class="icon me-3"><i class="ti-close"></i> </a></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td align="center" colspan="6">
                                                        {{ __('No Item Exists In Your Wishlist') }}</td>
                                                </tr>
                                            @endforelse

                                        </tbody>
                                        @if (count($wishList))
                                        
                                            <tfoot class="border-top border-bottom">
                                                <tr>
                                                    <td colspan="7" class="pt-2">
                                                        <button type="button"
                                                            class="btn btn-solid mr-2 addWishlistToCart">{{ __('Add To Cart') }}</button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                    {{-- <button style="display: none" id="wishlist">Remove From Wishlist</button> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
<script type="text/javascript">
    var add_wishlist_to_cart_url = "{{ route('addWishlistToCart') }}";
    var ajaxCall = 'ToCancelPrevReq';
    $('.verifyEmail').click(function() {
        verifyUser('email');
    });

    $('.verifyPhone').click(function() {
        verifyUser('phone');
    });

    $("#w-all").click(function() {
        if ($(this).is(":checked")) {
            // $("#wishlist").show();
            $(".wishlist-table").find(".custom-checkbox input[type='checkbox']").prop("checked", true);
        } else {
            $(".wishlist-table").find(".custom-checkbox input[type='checkbox']").prop("checked", false);
        }
    });

    // $("#wishlist").click(function() {
    //     var final = [];
    //     $('.custom-control-input:checked').each(function() {
    //         var values = $(this).attr('data-variant');
    //         if (values != undefined) {
    //             final.push(values);
    //         }
    //     });
    //     alert(final);
    // })

    $(document).ready(function() {
        $(document).on("click", ".addWishlistToCart", function() {
            let wishlist_products = [];
            if ($('.wishlist-row .custom-control-input:checked').length > 0) {
                $(".wishlist-row .custom-control-input:checked").each(function(i, obj) {
                    var id = $(obj).attr('id');
                    var product_id = id.replace('wp-', '');
                    var product_variant_id = $(obj).attr('data-variant');
                    wishlist_products.push({
                        'product_id': product_id,
                        'variant_id': product_variant_id
                    });
                });
                addWishlistToCart(wishlist_products);
            } else {
                toastr.options.timeOut = 3000;
                toastr.error('{{ __('Please select at least one product to add in cart.') }}');
            }
        });

        function addWishlistToCart(wishlist_products) {
            $.ajax({
                type: "post",
                dataType: "json",
                url: add_wishlist_to_cart_url,
                data: {
                    "wishlistProducts": wishlist_products
                },
                success: function(response) {
                    location.reload();
                },
                error: function(data) {
                    console.log(data);
                },
            });
        }
    });

    function verifyUser($type = 'email') {
        ajaxCall = $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('verifyInformation', Auth::user()->id) }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "type": $type,
            },
            beforeSend: function() {
                if (ajaxCall != 'ToCancelPrevReq' && ajaxCall.readyState < 4) {
                    ajaxCall.abort();
                }
            },
            success: function(response) {
                var res = response.result;

            },
            error: function(data) {

            },
        });
    }
</script>
@endsection
