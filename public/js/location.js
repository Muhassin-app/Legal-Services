let nav_click_vendor_mode = 0;
let redirect = 0;
jQuery(window).scroll(function () {
    var scroll = jQuery(window).scrollTop();

    if (scroll <= 192) {

        jQuery(".category-btns").removeClass("category-active-btns");

    } else {

        jQuery(".category-btns").addClass("category-active-btns");

    }
});
$(document).ready(async function () {
    getLocation();


    if (window.location.pathname == '/') {
        let latitude = "";
        let longitude = "";
        if ($("#address-latitude").length > 0) {
            latitude = $("#address-latitude").val();
        }
        if ($("#address-longitude").length > 0) {
            longitude = $("#address-longitude").val();
        }
        // await getHomePageCategoryMenu(latitude, longitude);
        //  getHomePage(latitude, longitude);
        // $(document).ready(function () {
        if ($.cookie("age_restriction") != 1) {
            if (is_age_restricted == "1" || is_age_restricted == 1) {
                if (redirect) {
                    return false;
                }
                $('#age_restriction').modal({ backdrop: 'static', keyboard: false });
            }
        }
        $(".shimmer_effect").hide();
    }
    else {
        $(".shimmer_effect").hide();
    }

    $(".age_restriction_no").click(function () {
        window.location.replace("https://google.com");
    });

    if ($.cookie('age_restriction') == 1 && ($.cookie('show_subscription_plan') == undefined || $.cookie('show_subscription_plan') == 0)) {
        if (setShowSubscriptionPlan == 'showed') {
            $("#show-subscription-plan-mdl").modal("show");
            $.cookie('show_subscription_plan', 1);
        }
    }

    $(".age_restriction_yes").click(function () {
        $.cookie('age_restriction', 1);
        $('#age_restriction').modal('hide');
        if (setShowSubscriptionPlan == 'showed') {
            $("#show-subscription-plan-mdl").modal("show");
            $.cookie('show_subscription_plan', 1);
        }
    });

    // $( document ).ready(function() {
    $('.date-items').removeClass('hide');
    $('.date-items').slick({
        infinite: true,
        speed: 300,
        arrows: true,
        dots: false,
        slidesToShow: 7,
        slidesToScroll: 5,
        autoplay: false,
        autoplaySpeed: 5000,
        rtl: false,
        responsive: [
            {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 5,
                    slidesToScroll: 1,
                    arrows: true
                }
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    arrows: true
                }
            }
        ]
    });

    $('.booking-time').slick({
        infinite: true,
        speed: 300,
        arrows: true,
        dots: false,
        slidesToShow: 3,
        slidesToScroll: 3,
        autoplay: false,
        autoplaySpeed: 5000,
        rtl: false,
        responsive: [{
            breakpoint: 1200,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        },
        {
            breakpoint: 767,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true
            }
        }]
    });

    $('.hours-slot').slick({
        infinite: true,
        speed: 300,
        arrows: true,
        dots: false,
        slidesToShow: 9,
        slidesToScroll: 3,
        autoplay: false,
        autoplaySpeed: 5000,
        rtl: false,
        responsive: [{
            breakpoint: 1200,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        },
        {
            breakpoint: 767,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true
            }
        }]
    });

    $('.materials-slide').slick({
        infinite: true,
        speed: 300,
        arrows: true,
        dots: false,
        slidesToShow: 4,
        slidesToScroll: 3,
        autoplay: false,
        autoplaySpeed: 5000,
        rtl: false,
        responsive: [{
            breakpoint: 1200,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        },
        {
            breakpoint: 767,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true
            }
        }]
    });


    // });

    // $(document ).ready(function() {
    $("#number").hide();
    $("#add_btn").click(function () {
        $("#number").show();
        $(this).hide();
    });

    // });

    if ($(".vendor_mods .nav-link").hasClass('active')) {
        var tabs = $('.vendor_mods .nav-link.active').parent('.navigation-tab-item').prevAll().length;
        if ($('body').attr('dir') == 'rtl') {
            $(".navigation-tab-overlay").css({
                right: tabs * 130 + "px"
            });
        } else {
            $(".navigation-tab-overlay").css({
                left: tabs * 100 + "px"
            });
        }
    }

    //$(".navigation-tab-item").click(function() {
    $(document).on('click', '.navigation-tab-item > a', async function () {
        // if($.hasAjaxRunning()){
        //     return false;
        // }

        //$(".navigation-tab-item").removeClass("active");
        $('.vendor_mods').find('.nav-link').removeClass('active');
        $(this).addClass("active");
        if ($('body').attr('dir') == 'rtl') {
            $(".navigation-tab-overlay").css({
                right: $(this).prevAll().length * 130 + "px"
            });
        } else {
            $(".navigation-tab-overlay").css({
                left: $(this).prevAll().length * 100 + "px"
            });
        }

        let latitude = "";
        let longitude = "";
        let type = "";
        let sessionType = "";
        //var id = $(this).attr('id');
        type = $(this).attr('vendortype');
        sessionType = $(this).data("sessiontype");

        if (type == sessionType) {
            window.location.href = home_page_url;
            return false;
        }
        if ($("#address-latitude").length > 0) {
            latitude = $("#address-latitude").val();
        }
        if ($("#address-longitude").length > 0) {
            longitude = $("#address-longitude").val();
        }

        nav_click_vendor_mode = 1;
        // if(!$.hasAjaxRunning()){
        //     vendorType(latitude, longitude, type);
        // }
        setSession(type);
    });

    $('#remove_cart_modal').on("hide.bs.modal", function () {

        $('#delivery_tab,#dinein_tab,#takeaway_tab').removeClass('active');
        $('.vendor_mods').find('.nav-link').removeClass('active');
        $('#' + session_vendor_type + '_tab').addClass('active');
        // location.reload();.

    })
    async function setSession(type = "delivery") {
        var cartData = (OrderStorage.getStorage('cartData') != '') ? JSON.parse(OrderStorage.getStorage('cartData')) : [];
        var cartProductCount = OrderStorage.getStorage('cartProductCount');
        if (cartProductCount > 0) {
            $("#remove_cart_modal").modal('show');
            $("#remove_cart_modal #remove_cart_button").attr("data-cart_id", cartData.id);

            $(".nav-tabs.vendor_mods").attr("data-mod", type);
            return false;
        }
        await $.ajax({
            type: "get",
            dataType: 'json',
            url: `/setSessionIndex?type=${type}`,
            success: function (response) {
                window.location.href = home_page_url;
            }
        });
    }

    async function vendorType(latitude, longitude, type = "delivery") {
        $.ajax({
            type: "get",
            dataType: 'json',
            url: cart_details_url,
            success: function (response) {
                if (response.data != "") {
                    let cartProducts = response.data.products;
                    if (cartProducts != "") {
                        $("#remove_cart_modal").modal('show');
                        $("#remove_cart_modal #remove_cart_button").attr("data-cart_id", response.data.id);
                        $(".nav-tabs.vendor_mods").attr("data-mod", type);
                    } else {
                        getHomePageCategoryMenu(latitude, longitude, type);
                        getHomePage(latitude, longitude, type);
                    }
                } else {
                    getHomePageCategoryMenu(latitude, longitude, type);
                    getHomePage(latitude, longitude, type);
                }
            }
        });
    }
    function getcart() {
        $.ajax({
            type: "get",
            dataType: 'json',
            url: cart_details_url,
            success: async function (response) {
                if (response.data != "") {
                    let cartProducts = response.data.products;
                    if (cartProducts != "") {
                        $("#remove_cart_modal").modal('show');
                        $("#remove_cart_modal #remove_cart_button").attr("data-cart_id", response.data.id);
                        $(".nav-tabs.vendor_mods").attr("data-mod", type);
                    } else {
                        await getHomePageCategoryMenu(latitude, longitude, type);
                        await getHomePage(latitude, longitude, type);
                    }
                } else {
                    await getHomePageCategoryMenu(latitude, longitude, type);
                    await getHomePage(latitude, longitude, type);
                }
            }
        });
    }

    async function getHomePage(latitude, longitude, vtype = "") {
        if (vtype != '') {
            vendor_type = vtype;
        }
        let selected_address = $("#address-input").val();
        // return 0;
        let selected_place_id = $("#address-place-id").val();
        $(".homepage-address span").text(selected_address).attr({ "title": selected_address, "data-original-title": selected_address });
        let ajaxData = { type: vendor_type };
        if ((latitude) && (longitude) && (selected_address)) {
            ajaxData.latitude = latitude;
            ajaxData.longitude = longitude;
            ajaxData.selectedAddress = sele
            cted_address;
            ajaxData.selectedPlaceId = selected_place_id;
        }
        /// remove_spinner('#our_vendor_main_div');
        add_spinner('#our_vendor_main_div');

        $.ajax({
            data: ajaxData,
            type: "POST",
            dataType: 'json',
            url: home_page_data_url_new,
            beforeSend: function () {

                //$(".no-store-wrapper, .home-slider, .home-banner-slider, #our_vendor_main_div").hide();
            },
            success: function (response) {
                if (response.status == "Success") {
                    remove_spinner('#our_vendor_main_div');
                    var path = window.location.pathname;
                    if (path == '/') {

                        const layouts = response.data.data;
                        layouts.forEach(function (obj, index) {
                            setTimeout(function () {
                                myFunctionGetDataHomePage(obj, index);
                            }, 500 * (index + 1));
                        });


                        // let vendors = response.data.vendors;

                        // let banner_template = _.template($('#banner_template').html());
                        // let vendors_template = _.template($('#vendors_template').html());
                        // let products_template = _.template($('#products_template').html());
                        // let trending_vendors_template = _.template($('#trending_vendors_template').html());
                        // let recent_orders_template = _.template($('#recent_orders_template').html());
                        // $(".render_brands").append(banner_template({ brands: response.data.brands, type: brand_language }));
                        // $(".render_vendors").append(vendors_template({ vendors: response.data.vendors , type: vendor_language}));
                        // $(".render_new_products").append(products_template({ products: response.data.new_products, type: new_product_language }));
                        // $(".render_best_sellers").append(products_template({ products: response.data.new_products, type: best_seller_product_language}));
                        // $(".render_featured_products").append(products_template({ products: response.data.feature_products, type: featured_product_language }));
                        // $(".render_on_sale").append(products_template({ products: response.data.on_sale_products, type: on_sale_product_language }));
                        // $(".render_trending_vendors").append(trending_vendors_template({ trending_vendors: response.data.trending_vendors , type: vendor_language}));
                        // $(".render_recent_orders").append(recent_orders_template({ recent_orders: response.data.active_orders}));

                        // if (response.data.new_products.length > 0) {
                        //     $('.render_full_new_products').removeClass('d-none');
                        // } else {
                        //     $('.render_full_new_products1').addClass('d-none');
                        // }
                        // if (response.data.new_products.length > 0) {
                        //     $('.render_full_best_sellers').removeClass('d-none');
                        // } else {
                        //     $('.render_full_best_sellers').addClass('d-none');
                        // }
                        // if (response.data.on_sale_products.length > 0) {
                        //     $('.render_full_on_sale').removeClass('d-none');
                        // } else {
                        //     $('.render_full_on_sale').addClass('d-none');
                        // }
                        // if (response.data.feature_products.length > 0) {
                        //     $('.render_full_featured_products').removeClass('d-none');
                        // } else {
                        //     $('.render_full_featumyFunctionGetDataHomePagered_products').addClass('d-none');
                        // }
                        // if (vendors.length > 0) {
                        //     $('#our_vendor_main_div').removeClass('d-none');
                        //     $(".no-store-wrapper").hide();
                        // } else {
                        //     $('#our_vendor_main_div').addClass('d-none');
                        //     $(".no-store-wrapper").show();
                        // }
                        // if (response.data.active_orders.length > 0) {
                        //     $('.render_full_recent_orders').removeClass('d-none');
                        // } else {
                        //     $('.render_full_recent_orders').addClass('d-none');
                        // }
                        // initializeSlider();

                    }
                    else {
                        //if ((latitude) && (longitude) && (selected_address)) {
                        if (!$.hasAjaxRunning()) {
                            window.location.href = home_page_url;
                            //console.log(home_page_url);
                        }
                        //}
                    }
                }
            },
            complete: function (data) {
                remove_spinner('#our_vendor_main_div');
                // Hide image container

                if ($("body").hasClass("al_body_template_six")) {
                    $(".main_shimer").hide();
                    $(".shimmer_effect").hide();
                } else {
                    $(".shimmer_effect").hide();
                }

                $(".home-slider, .home-banner-slider").show();
                //  $("#our_vendor_main_div").show();
            }
        });
    }

    function getHomePageBanners(lat = '', long = '') {
        ajaxDataSet = {};
        if ((lat) && (long)) {
            ajaxDataSet.latitude = lat;
            ajaxDataSet.longitude = long;
        }

        $.ajax({
            data: ajaxDataSet,
            type: "POST",
            dataType: 'json',
            url: home_page_banners_url,
            beforeSend: function () {

            },
            success: function (response) {
                if (response.status == "Success") {
                    $(".al_desktop_banner").html('');
                    if (response.data.banners != '') {
                        let desktop_banners_template = _.template($('#desktop_banners_template').html());
                        $(".al_desktop_banner").append(desktop_banners_template({ banners: response.data.banners }));
                    }
                    if (response.data.mobile_banners != '') {
                        $(".al_mobile_banner").html('');
                        let mobile_banners_template = _.template($('#mobile_banners_template').html());
                        $(".al_mobile_banner").append(mobile_banners_template({ banners: response.data.mobile_banners }));
                    }

                    $('.carousel').carousel();
                }
            },
            complete: function (data) {
                // Hide image container
                // $(".shimmer_effect").hide();
            }
        });
    }

    function myFunctionGetDataHomePage(item, index) {
        $(".shimmer_effect_" + item).show();

        switch (item) {
            case 'trending_vendors':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'vendors':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'featured_products':
                getHomePageDataSingleBySingle(item, index);

                break;
            case 'new_products':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'on_sale':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'best_sellers':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'brands':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'recent_orders':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'pickup_delivery':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'dynamic_page':
                getHomePageDataSingleBySingle(item, index);
                break;
            case 'cities':
                getHomePageDataSingleBySingle(item, index);
                break;
        }

        $(".shimmer_effect_" + item).hide();
    }

    function getHomePageDataSingleBySingle(item, index) {

        let latitude = "";
        let longitude = "";
        if ($("#address-latitude").length > 0) {
            latitude = $("#address-latitude").val();
        }
        if ($("#address-longitude").length > 0) {
            longitude = $("#address-longitude").val();
        }

        vtype = '';
        if (vtype != '') {
            vendor_type = vtype;
        }
        let selected_address = $("#address-input").val();
        // return 0;
        let selected_place_id = $("#address-place-id").val();
        $(".homepage-address span").text(selected_address).attr({ "title": selected_address, "data-original-title": selected_address });
        $("#edit-address").modal('hide');
        let ajaxDataSet = { type: vendor_type };
        if ((latitude) && (longitude) && (selected_address)) {
            ajaxDataSet.latitude = latitude;
            ajaxDataSet.longitude = longitude;
            ajaxDataSet.selectedAddress = selected_address;
            ajaxDataSet.selectedPlaceId = selected_place_id;
        }

        ajaxDataSet.slug = item;
        $.ajax({
            data: ajaxDataSet,
            type: "POST",
            dataType: 'json',
            url: postHomePageDataSingle,
            beforeSend: function () {

            },
            success: async function (response) {
                if (response.status == "Success") {
                    var path = window.location.pathname;
                    if (path == '/') {

                        let products_template = _.template($('#products_template').html());

                        switch (item) {
                            case 'trending_vendors':
                                if (response.data.trending_vendors.length > 0) {
                                    $('.render_full_trending_vendors').removeClass('d-none');
                                    if ($('.suppliers-slider-trending_vendors').hasClass('slick-initialized')) {
                                        $(".suppliers-slider-trending_vendors").slick('destroy');
                                        $(".render_vendors").html('');
                                    }
                                    let trending_vendors_template = _.template($('#trending_vendors_template').html());
                                    $(".render_trending_vendors").append(trending_vendors_template({ trending_vendors: response.data.trending_vendors, type: vendor_language }));
                                    $('.suppliers-slider-trending_vendors').slick({
                                        infinite: true,
                                        speed: 300,
                                        slidesToShow: 6,
                                        slidesToScroll: 1,
                                        centerMode: false,
                                        centerPadding: '60px',
                                        arrows: true,
                                        dots: false,
                                        responsive: [
                                            { breakpoint: 1199, settings: { slidesToShow: 4, slidesToScroll: 3, infinite: true, dots: false, centerMode: true, } },
                                            { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 1, dots: false, centerMode: true, } },
                                            { breakpoint: 767, settings: { slidesToShow: 3, slidesToScroll: 1, dots: false, centerMode: true, } },
                                            { breakpoint: 576, settings: { slidesToShow: 3, slidesToScroll: 1, dots: false, centerMode: true, centerPadding: '0px', } }
                                        ]
                                    });
                                    $("#our_vendor_main_div").show();
                                } else {
                                    $('.render_full_trending_vendors').addClass('d-none');
                                }
                                break;
                            case 'vendors':
                                $(".render_vendors").html('');
                                if ($('.suppliers-slider-vendors').hasClass('slick-initialized')) {
                                    $(".suppliers-slider-vendors").slick('destroy');
                                }
                                let vendors_template = _.template($('#vendors_template').html());
                                $(".render_vendors").append(vendors_template({ vendors: response.data.vendors, type: vendor_language }));
                                $('.suppliers-slider-vendors').slick({
                                    infinite: true,
                                    speed: 300,
                                    slidesToShow: 6,
                                    slidesToScroll: 1,
                                    centerMode: false,
                                    centerPadding: '60px',
                                    arrows: true,
                                    dots: false,
                                    responsive: [
                                        { breakpoint: 1199, settings: { slidesToShow: 4, slidesToScroll: 1, infinite: true, dots: false, centerMode: true, } },
                                        { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 1, dots: false, centerMode: true, } },
                                        { breakpoint: 767, settings: { slidesToShow: 3, slidesToScroll: 1, dots: false, centerMode: true, } },
                                        { breakpoint: 576, settings: { slidesToShow: 3, slidesToScroll: 1, dots: false, centerMode: true, centerPadding: '0', } }
                                    ]
                                });

                                if (response.data.vendors.length > 0) {
                                    $('.render_full_vendors').removeClass('d-none');
                                } else {
                                    $('.render_full_vendors').addClass('d-none');
                                }
                                break;
                            case 'featured_products':
                                if ($('.product-4-featured_products').hasClass('slick-initialized')) {
                                    $(".product-4-featured_products").slick('destroy');
                                    $("#featured_products").html('');
                                    $(".render_featured_products").html('');
                                }
                                $(".render_featured_products").append(products_template({ products: response.data.feature_products, type: featured_product_language }));
                                $(".product-4-featured_products").slick({
                                    dots: false,
                                    infinite: true,
                                    speed: 300,
                                    slidesToShow: 10,
                                    centerMode: true,
                                    centerPadding: '60px',
                                    slidesToScroll: 4,
                                    arrows: true,
                                    responsive: [
                                        { breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } },
                                        { breakpoint: 991, settings: { slidesToShow: 2, arrows: true, slidesToScroll: 2 } },
                                        { breakpoint: 767, settings: { slidesToShow: 2, arrows: true, slidesToScroll: 1, centerPadding: '0px', } }
                                    ]
                                });
                                if (response.data.feature_products.length > 0) {
                                    $('.render_full_featured_products').removeClass('d-none');
                                } else {
                                    $('.render_full_featured_products').addClass('d-none');
                                }
                                break;
                            case 'new_products':
                                if (response.data.new_products.length > 0) {
                                    if ($('.product-4-new_products').hasClass('slick-initialized')) {
                                        $(".product-4-new_products").slick('destroy');
                                        $(".render_new_products").html('');
                                    }
                                    $(".render_new_products").append(products_template({ products: response.data.new_products, type: new_product_language }));
                                    $(".product-4-new_products").slick({
                                        dots: false,
                                        infinite: true,
                                        speed: 300,
                                        slidesToShow: 4,
                                        centerMode: true,
                                        centerPadding: '60px',
                                        slidesToScroll: 4,
                                        arrows: true,
                                        responsive: [
                                            { breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } },
                                            { breakpoint: 991, settings: { slidesToShow: 2, arrows: true, slidesToScroll: 2 } },
                                            { breakpoint: 767, settings: { slidesToShow: 2, arrows: true, slidesToScroll: 1, centerPadding: '0px' } }
                                        ]
                                    });
                                    $('.render_full_new_products').removeClass('d-none');
                                } else {
                                    $('.render_full_new_products').addClass('d-none');
                                }

                                break;
                            case 'on_sale':
                                if (response.data.on_sale_products.length > 0) {
                                    if ($('.product-4-on_sale').hasClass('slick-initialized')) {
                                        $(".product-4-on_sale").slick('destroy');
                                        $(".render_on_sale").html('');
                                    }
                                    $(".render_on_sale").append(products_template({ products: response.data.on_sale_products, type: on_sale_product_language }));
                                    $(".product-4-on_sale").slick({
                                        dots: false,
                                        infinite: true,
                                        speed: 300,
                                        slidesToShow: 4,
                                        centerMode: true,
                                        centerPadding: '60px',
                                        slidesToScroll: 4,
                                        arrows: true,
                                        responsive: [
                                            { breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } },
                                            { breakpoint: 991, settings: { slidesToShow: 2, arrows: true, slidesToScroll: 2 } },
                                            { breakpoint: 767, settings: { slidesToShow: 2, arrows: true, slidesToScroll: 1, centerPadding: '0px', } }
                                        ]
                                    });

                                    $('.render_full_on_sale').removeClass('d-none');
                                } else {
                                    $('.render_full_on_sale').addClass('d-none');
                                }
                                break;
                            case 'best_sellers':
                                if (response.data.new_products.length > 0) {
                                    if ($('.product-4-best_sellers').hasClass('slick-initialized')) {
                                        $("#best_sellers").html('');
                                        $(".render_best_sellers").html('');
                                    }
                                    $(".render_best_sellers").append(products_template({ products: response.data.new_products, type: best_seller_product_language }));
                                    $('.render_full_best_sellers').removeClass('d-none');
                                } else {
                                    $('.render_full_best_sellers').addClass('d-none');
                                }
                                break;
                            case 'brands':
                                if (response.data.brands.length > 0) {
                                    if ($('.render_brands').hasClass('slick-initialized')) {
                                        $(".brand-slider").slick('destroy');
                                        $(".render_brands").html('');
                                    }
                                    let banner_template = _.template($('#banner_template').html());
                                    $(".render_brands").append(banner_template({ brands: response.data.brands, type: brand_language }));
                                    $(".brand-slider").slick({
                                        arrows: true,
                                        dots: false,
                                        infinite: true,
                                        speed: 300,
                                        slidesToShow: 5,
                                        slidesToScroll: 3,
                                        responsive: [
                                            { breakpoint: 1367, settings: { slidesToShow: 4, slidesToScroll: 2, infinite: true } },
                                            { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 1 } },
                                            { breakpoint: 767, settings: { slidesToShow: 3, slidesToScroll: 1 } },
                                            { breakpoint: 360, settings: { slidesToShow: 3, slidesToScroll: 1 } }
                                        ]
                                    });
                                    $('.render_full_brands').removeClass('d-none');
                                } else {
                                    $('.render_full_brands').addClass('d-none');
                                }
                                break;
                            case 'recent_orders':
                                if (response.data.active_orders.length > 0) {
                                    if ($('.recent-orders').hasClass('slick-initialized')) {
                                        $(".recent-orders").slick('destroy');
                                        $(".render_recent_orders").html('');
                                    }
                                    let recent_orders_template = _.template($('#recent_orders_template').html());
                                    $(".render_recent_orders").append(recent_orders_template({ Helper: NumberFormatHelper, recent_orders: response.data.active_orders }));
                                    $(".recent-orders").slick({
                                        arrows: true,
                                        dots: false,
                                        infinite: true,
                                        speed: 300,
                                        // centerMode: true,
                                        // centerPadding: '60px',
                                        slidesToShow: 2,
                                        slidesToScroll: 1,
                                        responsive: [
                                            { breakpoint: 1200, settings: { slidesToShow: 1, slidesToScroll: 2 } }
                                        ]
                                    });
                                    $('.render_full_recent_orders').removeClass('d-none');
                                } else {
                                    $('.render_full_recent_orders').addClass('d-none');
                                }
                                break;
                            case 'cities':
                                if (response.data.cities.length > 0) {
                                    if ($('.suppliers-slider-cities').hasClass('slick-initialized')) {
                                        $(".suppliers-slider-cities").slick('destroy');
                                        $(".render_cities").html('');
                                    }
                                    let city_template = _.template($('#cities_template').html());
                                    $(".render_cities").append(city_template({ cities: response.data.cities }));
                                    $(".suppliers-slider-cities").slick({
                                        arrows: true,
                                        dots: false,
                                        infinite: true,
                                        speed: 300,
                                        slidesToShow: 5,
                                        slidesToScroll: 3,
                                        responsive: [
                                            { breakpoint: 1367, settings: { slidesToShow: 4, slidesToScroll: 2, infinite: true } },
                                            { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 1 } },
                                            { breakpoint: 767, settings: { slidesToShow: 3, slidesToScroll: 1 } },
                                            { breakpoint: 360, settings: { slidesToShow: 3, slidesToScroll: 1 } }
                                        ]
                                    });
                                    $('.render_full_cities').removeClass('d-none');
                                } else {
                                    $('.render_full_cities').addClass('d-none');
                                }
                                break;
                        }

                        let vendors = response.data.vendors;

                        if (vendors.length > 0) {
                            $('#our_vendor_main_div').removeClass('d-none');
                            $(".no-store-wrapper").hide();
                        } else {
                            $('#our_vendor_main_div').addClass('d-none');
                            $(".no-store-wrapper").show();
                        }

                    }
                    else {
                        if ((latitude) && (longitude) && (selected_address)) {
                            window.location.href = home_page_url;
                        }
                    }
                }
            },
            complete: function (data) {
                // Hide image container
                $(".shimmer_effect").hide();
                $(".home-slider, .home-banner-slider").show();
                $("#our_vendor_main_div").show();
            }
        });
    }




    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, defaultPosition);
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }

    function showPosition(position) {
        let lat = $("#address-latitude").val();
        let long = $("#address-longitude").val();
        let selectedPlaceId = $("#address-place-id").val();
        let selectedAddress = $("#address-input").val();
        if ((lat != '') && (long != '')) {
            // displayLocation(lat, long, selectedPlaceId, selectedAddress);
        } else {
            let lat = position.coords.latitude;
            let long = position.coords.longitude;
            displayLocation(lat, long);
        }
    }

    function defaultPosition() {
        // displayLocation(defaultLatitude, defaultLongitude, '', defaultLocationName);
    }

    if (is_hyperlocal) {
        // if (!selected_address) {
        getLocation();
        // }
        // let lat = $("#address-latitude").val();
        // let long = $("#address-longitude").val();
        // let placeId = $("#address-place-id").val();
        // displayLocation(lat, long, placeId);
    }

    $(document).delegate(".confirm_address_btn", "click", function () {
        let latitude = $("#address-latitude").val();
        let longitude = $("#address-longitude").val();
        let address = $("#address-input").val();

        setSessionLocatin(latitude, longitude, address)
        //bindLatestCoords(latitude, longitude);

        // $.ajax({
        //     type: "get",
        //     dataType: 'json',
        //     url: cart_details_url,
        //     success: async function (response) {
        //         if (response.data != "") {
        //             let cartProducts = response.data.products;
        //             if (cartProducts != "") {
        //                 $("#remove_cart_modal").modal('show');
        //                 $("#remove_cart_modal #remove_cart_button").attr("data-cart_id", response.data.id);
        //             } else {
        //                 getHomePageBanners(latitude, longitude);
        //                 getHomePageCategoryMenu(latitude, longitude);
        //                 getHomePage(latitude, longitude);
        //                 let selected_address = $("#address-input").val();
        //                 $(".homepage-address span").text(selected_address).attr({ "title": selected_address, "data-original-title": selected_address });
        //             }
        //         } else {
        //             getHomePageBanners(latitude, longitude);
        //             getHomePageCategoryMenu(latitude, longitude);
        //             getHomePage(latitude, longitude);
        //         }
        //     }
        // });
    });

    $(document).delegate("#remove_cart_button", "click", function () {
        let latitude = $("#address-latitude").val();
        let longitude = $("#address-longitude").val();
        let address = $("#address-input").val();
        setSessionLocatin(latitude, longitude, address);

        let cart_id = $(this).attr("data-cart_id");
        let ondemand_pricing_mode = $(this).attr("data-ondemand_vendor_type");
        $("#remove_cart_modal").modal('hide');
        removeCartData(cart_id, ondemand_pricing_mode);
    });

    function removeCartData(cart_id, ondemand_pricing_mode = '') {

        $.ajax({
            type: "post",
            dataType: 'json',
            url: delete_cart_url,
            data: { 'cart_id': cart_id },
            success: function (response) {
                if (response.status == 'success') {
                    let latitude = $("#address-latitude").val();
                    let longitude = $("#address-longitude").val();
                    let vendor_mod = "";
                    if ($(".nav-tabs.vendor_mods .nav-link").length > 0) {
                        vendor_mod = $(".nav-tabs.vendor_mods").attr("data-mod");
                    }
                    OrderStorage.setStorageSingle('cartProductCount', 0);
                    if (ondemand_pricing_mode != '' && ondemand_pricing_mode != undefined) {
                        setSessionOndemandPricing(ondemand_pricing_mode);
                    }
                    setSession(vendor_mod);
                    //getHomePageCategoryMenu(latitude, longitude, vendor_mod);
                    //getHomePage(latitude, longitude, vendor_mod);
                }
            }
        });
    }

    function displayLocation(latitude, longitude, placeId = '', location = '') {
        var geocoder;
        geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(latitude, longitude);

        const map = new google.maps.Map(document.getElementById('address-map'), {
            center: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
            zoom: 13
        });

        const marker = new google.maps.Marker({
            map: map,
            position: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
        });

        var geodata = { 'latLng': latlng };
        if (placeId != '') {
            geodata = { 'placeId': placeId };
            // geodata.placeId = placeId;
        }

        geocoder.geocode(geodata,
            function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        var add = results[0].formatted_address;
                        var value = add.split(",");
                        if (placeId == '') {
                            placeId = results[0].place_id;
                        }
                        if (location != '') {
                            add = location;
                        }

                        count = value.length;
                        country = value[count - 1];
                        state = value[count - 2];
                        city = value[count - 3];
                        if (!selected_address) {
                            $("#address-place-id").val(placeId);
                            $("#address-input").val(add);
                            $("#address-latitude").val(latitude);
                            $("#address-longitude").val(longitude);
                            $(".homepage-address span").text(value).attr({ "title": value, "data-original-title": value });
                            getHomePageCategoryMenu(latitude, longitude);
                            getHomePage(latitude, longitude);
                        }
                    }
                    else {
                        // $("#address-input").val("address not found");
                    }
                }
                else {
                    $("#address-input").val("Geocoder failed due to: " + status);
                }
            }
        );
    }


    ////////////// *****************   home page category icon **************** //////////////////
    async function getHomePageCategoryMenu(latitude, longitude, vtype = "") {

        if (vtype != '') {
            vendor_type = vtype;
        }
        let selected_address = $("#address-input").val();
        // return 0;
        let selected_place_id = $("#address-place-id").val();
        $(".homepage-address span").text(selected_address).attr({ "title": selected_address, "data-original-title": selected_address });
        $("#edit-address").modal('hide');
        let ajaxData = {
            type: vendor_type,
            request_from: nav_click_vendor_mode
        };
        if ((latitude) && (longitude) && (selected_address)) {
            ajaxData.latitude = latitude;
            ajaxData.longitude = longitude;
            ajaxData.selectedAddress = selected_address;
            ajaxData.selectedPlaceId = selected_place_id;
        }

        // alert(vendor_type);
        await $.ajax({
            data: ajaxData,
            type: "POST",
            dataType: 'json',
            url: home_page_data_url_category_menu,
            beforeSend: function () {
                $("#main-menu").hide();
                $(".shimmer_effect .menu-slider").css("display", "flex");
            },
            success: async function (response) {
                nav_click_vendor_mode = 0;
                if (response.status == "Success") {

                    var data = response.data;
                    // console.log(data);
                    // return 0;
                    if ((data.navCategories).length > 0 && vendor_type == "pick_drop") {
                        var category = data.navCategories[0].slug;
                        window.location.href = category_page_url.replace(":id", category);
                        redirect = 1;
                        return false;
                    }

                    if ($('.menu-slider').hasClass('slick-initialized')) {
                        $('.menu-slider').slick('destroy');
                    }
                    $('#main-menu').smartmenus('destroy');
                    let nav_categories_template = _.template($('#nav_categories_template').html());
                    await $("#main-menu").html(nav_categories_template({ nav_categories: response.data.navCategories }));
                    await $("#main-menu").smartmenus({ subMenusSubOffsetX: 1, subMenusSubOffsetY: -8 }), $("#sub-menu").smartmenus({ subMenusSubOffsetX: 1, subMenusSubOffsetY: -8 });
                    //     if($(window).width() >= 320){
                    //         if(!$('.menu-slider').hasClass('slick-initialized')){
                    //             loadMainMenuSlider();
                    //         }
                    //    }
                    resizeMenuSlider();
                    $("#main-menu").css("display", "flex");

                    var path = window.location.pathname;
                    if (path == '/') {

                    }
                    else {
                        // if ((latitude) && (longitude) && (selected_address)) {
                        //     window.location.href = home_page_url;
                        // }
                    }
                }
            },
            complete: function (data) {
                $("#main-menu").show();
            }
        });
    }



    /////////// **************  end home page category icon *************** //////////////


});
function emptyCart(type = vendor_type) {
    var return_val = 1;
    var cartData = (OrderStorage.getStorage('cartData') != '') ? JSON.parse(OrderStorage.getStorage('cartData')) : [];
    var cartProductCount = OrderStorage.getStorage('cartProductCount');
    if (cartProductCount > 0) {
        $("#remove_cart_modal").modal('show');
        $("#remove_cart_modal #remove_cart_button").attr("data-cart_id", cartData.id);

        $(".nav-tabs.vendor_mods").attr("data-mod", type);

        return_val = 0;
    }
    return return_val;
}

$(document).on("click", '.on_demand_top_selection', async function (e) {
    $("#ondemand_price_selection_model").modal("show");
});

$(document).on("click", '.select_on_demand_pricing_by_user', async function (e) {
    e.preventDefault();
    var type = document.querySelector('input[name="onDemandpricingselection"]:checked').value;
    if (type == undefined || type == '') {
        console.log('not selecter');
    }
    if (ondemand_selected_price == type) {
        return false
    }
    var cart_check = await emptyCart();
    if (cart_check == 0) {
        $("#remove_cart_modal #remove_cart_button").attr("data-ondemand_vendor_type", type);
        return false;
    }

    //  var cartData = (OrderStorage.getStorage('cartData') != '') ? JSON.parse(OrderStorage.getStorage('cartData')) : [];
    //  var cartProductCount = OrderStorage.getStorage('cartProductCount');
    //  if(cartProductCount > 0){
    //      $("#remove_cart_modal").modal('show');
    //      $("#remove_cart_modal #remove_cart_button").attr("data-cart_id", cartData.id);
    //      $("#remove_cart_modal #remove_cart_button").attr("data-ondemand_vendor_type", type);
    //      $(".nav-tabs.vendor_mods").attr("data-mod", type);
    //      return false;
    //}
    setSessionOndemandPricing(type);
    //console.log(type);
});

async function setSessionLocatin(latitude, longitude, address) {
    var cartData = (OrderStorage.getStorage('cartData') != '') ? JSON.parse(OrderStorage.getStorage('cartData')) : [];
    var cartProductCount = OrderStorage.getStorage('cartProductCount');
    if (cartProductCount > 0) {
        $("#remove_cart_modal").modal('show');
        $("#remove_cart_modal #remove_cart_button").attr("data-cart_id", cartData.id);
        $(".nav-tabs.vendor_mods").attr("data-mod");
        return false;
    }
    let url = `/updateLocation?latitude=${latitude}&&longitude=${longitude}&&address=${address}`;
    window.location.href = url;
}
async function setSessionOndemandPricing(type = "vendor") {
    let url = `/ondemandPricing?type=${type}`;
    window.location.href = url;
}
function addressInputDisplay(locationWrapper, inputWrapper, input) {
    $(inputWrapper).removeClass("d-none").addClass("d-flex");
    $(locationWrapper).removeClass("d-flex").addClass("d-none");
    var val = $(input).val();
    $(input).focus().val('').val(val);
}

function addressInputHide(locationWrapper, inputWrapper, input) {
    $(inputWrapper).addClass("d-none").removeClass("d-flex");
    $(locationWrapper).addClass("d-flex").removeClass("d-none");
}

function initMap() {
    const locationInputs = document.getElementsByClassName("map-input");

    const autocompletes = [];
    let geocoder = new google.maps.Geocoder;
    for (let i = 0; i < locationInputs.length; i++) {

        const input = locationInputs[i];
        const fieldKey = input.id.replace("-input", "");
        const isEdit = document.getElementById(fieldKey + "-latitude").value != '' && document.getElementById(fieldKey + "-longitude").value != '';

        const latitude = parseFloat(document.getElementById(fieldKey + "-latitude").value) || -33.8688;
        const longitude = parseFloat(document.getElementById(fieldKey + "-longitude").value) || 151.2195;

        const map = new google.maps.Map(document.getElementById(fieldKey + '-map'), {
            center: { lat: latitude, lng: longitude },
            zoom: 13
        });
        const marker = new google.maps.Marker({
            map: map,
            position: { lat: latitude, lng: longitude },
            draggable: true
        });

        marker.setVisible(isEdit);

        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', bindMap);
        autocomplete.key = fieldKey;
        if (is_map_search_perticular_country) {
            autocomplete.setComponentRestrictions({ 'country': [is_map_search_perticular_country] });
        }

        autocompletes.push({ input: input, map: map, marker: marker, autocomplete: autocomplete });
    }

    for (let i = 0; i < autocompletes.length; i++) {
        const input = autocompletes[i].input;
        let autocomplete = autocompletes[i].autocomplete;
        const map = autocompletes[i].map;
        const marker = autocompletes[i].marker;

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            marker.setVisible(false);
            const place = autocomplete.getPlace();

            geocoder.geocode({ 'placeId': place.place_id }, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    let lat = results[0].geometry.location.lat();
                    let lng = results[0].geometry.location.lng();

                    $("#address-place-id").val(place.place_id);
                    // $(".homepage-address span").text(place.formatted_address);
                    setLocationCoordinates(autocomplete.key, lat, lng);
                }
            });


            if (!place.geometry) {
                window.alert("No details available for input: '" + place.name + "'");
                input.value = "";
                return;
            }

            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(13);
            }
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

        });


        google.maps.event.addListener(marker, 'dragend', function () {
            geocoder.geocode({ 'latLng': marker.getPosition() }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        var address_components = results[0].address_components;
                        var components = {};
                        jQuery.each(address_components, function (k, v1) { jQuery.each(v1.types, function (k2, v2) { components[v2] = v1.long_name }); });
                        var city;
                        var postal_code;
                        var state;
                        var country;

                        if (components.locality) {
                            city = components.locality;
                        }

                        if (!city) {
                            city = components.administrative_area_level_1;
                        }

                        if (components.postal_code) {
                            postal_code = components.postal_code;
                        }

                        if (components.administrative_area_level_1) {
                            state = components.administrative_area_level_1;
                        }

                        if (components.country) {
                            country = components.country;
                        }

                        // $.ajax({
                        //     url : 'url',
                        //     data: {state : state, country : country},
                        //     type: 'POST',
                        //     success: function(data) {
                        //         $('#input-country').val(data['country']);
                        //         $('#input-zone').val(data['zone']);
                        //     }
                        // });

                        console.log(city);
                        console.log(state);
                        console.log(country);
                        console.log(postal_code);
                        console.log(marker.getPosition().lat());
                        console.log(marker.getPosition().lng());
                        $('#address-input').val(results[0].formatted_address);
                        setSessionLocatin(marker.getPosition().lat(), marker.getPosition().lng(), autocomplete.key)
                        setLocationCoordinates(autocomplete.key, marker.getPosition().lat(), marker.getPosition().lng());

                    }
                }
            });
        });

        setTimeout(function () {
            //$(".pac-container").appendTo("#edit-address .address-input-group");
        }, 300);
    }
}

function setLocationCoordinates(key, lat, lng) {
    const latitudeField = document.getElementById(key + "-" + "latitude");
    const longitudeField = document.getElementById(key + "-" + "longitude");
    latitudeField.value = lat;
    longitudeField.value = lng;
}
google.maps.event.addDomListener(window, 'load', initMap);


////   cab booking section

$(document).on("input", ".edit-other-stop", function () {
    var random_id = $(this).attr("id");
    var rel = $(this).attr("data-rel");
    initializeNewCabHome(random_id, rel);
});


$(document).delegate("#edit-address #address-input", "focus", function () {
    initMap();
});


function initializeNewCabHome(random_id, rel) {
    var input = document.getElementById(random_id);
    var autocomplete = new google.maps.places.Autocomplete(input);
    if (is_map_search_perticular_country) {
        autocomplete.setComponentRestrictions({ 'country': [is_map_search_perticular_country] });
    }
    autocomplete.bindTo('bounds', bindMap);

    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();
        document.getElementById(random_id + '_latitude_home').value = place.geometry.location.lat();
        document.getElementById(random_id + '_longitude_home').value = place.geometry.location.lng();




    });
}
