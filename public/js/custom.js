
$(function () {
    $(".al_main_category").hover(
        function () {
            $('body').addClass("add_overlay");
        },
        function () {
            $('body').removeClass("add_overlay");
        }
    );

    let cardJson = {
        'cno': '',
        'dt': '',
        'cv': '',
        'name': '',
    }

    var slotValidater = 2;


    var footer_height = jQuery('.footer-light').height();
    var header_height = jQuery('.site-header').height();
    var window_height = jQuery(window).height();
    var header_content_width = jQuery('#content-wrap').height();
    // console.log('header_height',header_height,'footer_height',footer_height);
    jQuery(".al_offset-top-home, .inner-pages-offset").css('margin-top', header_height + 'px');
    jQuery("#content-wrap").css('padding-bottom', footer_height);

    jQuery("h2.category-head, .scrollspy-menu, .cart-main-box").css('top', header_height);

    jQuery(window).scroll(function () {
        var scroll = jQuery(window).scrollTop();
        if (scroll <= 100) {
            jQuery(".site-header").removeClass("fixed-bar");

        } else {
            jQuery(".site-header").addClass("fixed-bar");
        }
    });

    jQuery('.scrollspy-menu a').on('click', function () {
        jQuery("html, body").animate({ scrollTop: jQuery('#' + jQuery(this).data('slug')).offset().top - (header_height + 30) });
    })
});

$(".mobile-account .fa").click(function () {
    $(".onhover-show-div").toggleClass("open");
});

$(document).on("click", '.discard_editing_order', function (e) {
    Swal.fire({
        title: confirm_discard_edit_order_title,
        text: confirm_discard_edit_order_desc,
        showCancelButton: true,
        confirmButtonText: 'Ok',
    }).then((result) => {
        if (result.value) {
            $.ajax({
                type: "post",
                dataType: "json",
                url: discard_order_editing_url,
                data: {
                    "_token": $('meta[name="_token"]').attr('content'),
                    "orderid": $(this).data('orderid')
                },
                success: function (res) {
                    if (res.status == "Success") {
                        success_error_alert('success', res.message, success_error_container);
                        location.reload();
                    } else {
                        success_error_alert('error', res.message, success_error_container);
                    }
                }
            });
        } else {
            return false;
        }
    });
});

// Material Select Initialization
$(document).ready(function () {
    //$('.mdb-select').materialSelect();

});
// function add_spinner(id){
//     var html = `<div id="overlay">
//             <div class="cv-spinner">
//             <span class="spinner"></span>
//             </div>
//         </div>`;
//     $(id).prepend(html);
// }
// function remove_spinner(id){
//     $(id + ' > #overlay' ).remove();
// }
$(function () {
    document.ajax_loading = false;
    $.hasAjaxRunning = function () {

        return document.ajax_loading;
    };
    $(document).ajaxStart(function () {
        //add_spinner('#our_vendor_main_div');
        document.ajax_loading = true;
    });
    $(document).ajaxStop(function () {
        //remove_spinner('#our_vendor_main_div');
        document.ajax_loading = false;
    });
    $(document).ajaxComplete(function () {
        document.ajax_loading = false;
    });
});

$(window).scroll(function () {
    var windscroll = $(window).scrollTop();
    var windowheight = $(window).height();
    var header_height = $('.site-header').height();

    if (windscroll >= windowheight) {
        $('section.scrolling_section').each(function (i) {
            // The number at the end of the next line is how pany pixels you from the top you want it to activate.
            if ($(this).position().top <= windscroll - windowheight + 650) {
                $('.scrollspy-menu li.active').removeClass('active');
                $('.scrollspy-menu li').eq(i).addClass('active');
            }
        });

    } else {
        $('.scrollspy-menu li.active').removeClass('active');
        $('.scrollspy-menu li:first').addClass('active');
        jQuery(".alScrollspyProduct").css('margin-top', '0px');
    }

}).scroll();

window.easyZoomInitialize = function easyZoomInitialize() {
    let thumbs = $('.gallery-parent').children('.gallery-thumbs'),
        top = $('.gallery-parent').children('.gallery-top');

    // activation carousel plugin
    let galleryThumbs = new Swiper(thumbs, {
        spaceBetween: 5,
        freeMode: true,
        watchSlidesVisibility: true,
        watchSlidesProgress: true,
        breakpoints: {
            0: {
                slidesPerView: 3,
            },
            992: {
                slidesPerView: 4,
            },
        },
    });
    let galleryTop = new Swiper(top, {
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        thumbs: {
            swiper: galleryThumbs,
        },
    });

    // change carousel item height
    // gallery-top
    let productCarouselTopWidth = top.outerWidth();
    top.css('height', productCarouselTopWidth);

    // gallery-thumbs
    let productCarouselThumbsItemWith = thumbs.find('.swiper-slide').outerWidth();
    thumbs.css('height', productCarouselThumbsItemWith);
}

window.loadMainMenuSlider = function loadMainMenuSlider() {
    $('.slick-track').css("display", "flex");
    // $(".menu-slider").slick({arrows:true,dots:!1,infinite:!1,variableWidth:!0,autoplay:!1,speed:300,slidesToShow:6,slidesToScroll:1});
    $(".menu-slider").slick({
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 13,
        slidesToScroll: 1,
        arrows: true,
        responsive: [
            {
                breakpoint: 1366,
                settings: {
                    slidesToShow: 8,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 6,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1
                }
            }
        ]
    });
}

loadMainMenuSlider();

window.resizeMenuSlider = function resizeMenuSlider() {
    var windowWidth = $(window).width();
    if (windowWidth < 320) {
        $('#main-menu').removeClass('items-center');
    } else {
        if (!$('.menu-slider').hasClass('slick-initialized')) {

            setTimeout(function () {
                loadMainMenuSlider();
                if ($('body').hasClass('al_body_template_six')) {
                    if ($('#main-menu .slick-slide').length > 6) {
                        $('#main-menu').addClass('items-center');
                    }
                } else {
                    if ($('#main-menu .slick-slide').length > 13) {
                        $('#main-menu').addClass('items-center');
                    }
                    setTimeout(function () {
                        $(".sm-horizontal").css("right", "0px");
                    }, 200);
                }

            }, 100);

        }
    }
}

let path = window.location.pathname;
if (path != '/') {
    resizeMenuSlider();
}
$(window).resize(function () {
    resizeMenuSlider();
});

if ($(window).width() < 767) {
    $('.footer-contant').addClass('footer-mobile-contant');
    $('.footer-contant').removeClass('footer-contant');
}

window.initializeSlider = function initializeSlider() {
    $(".slide-6").slick({ arrows: !0, dots: !1, infinite: !0, speed: 300, centerMode: !0, centerPadding: "60px", slidesToShow: 5, slidesToScroll: 3, responsive: [{ breakpoint: 1367, settings: { slidesToShow: 5, slidesToScroll: 5, infinite: !0 } }, { breakpoint: 1024, settings: { slidesToShow: 4, slidesToScroll: 4, infinite: !0 } }, { breakpoint: 767, settings: { slidesToShow: 3, arrows: !0, slidesToScroll: 3, infinite: !0 } }, { breakpoint: 480, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".product-4").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "20px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }] });
    $(".product-4-featured_products").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "60px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".product-4-new_products").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "60px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".al_mobile_banner").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "60px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".product-4-on_sale").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "60px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".recent-orders").slick({ arrows: !0, dots: !1, infinite: !0, speed: 300, slidesToShow: 2, slidesToScroll: 1, responsive: [{ breakpoint: 1199, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });

    $(".brand-slider").slick({
        arrows: !0, dots: !1, infinite: !0, speed: 300, slidesToShow: 4, slidesToScroll: 3, responsive: [{
            breakpoint: 1367, settings:
                { slidesToScroll: 2, infinite: !0 }
        },
        { breakpoint: 991, settings: { slidesToScroll: 2 } },
        { breakpoint: 767, settings: { slidesToShow: 4, slidesToScroll: 1, } }]
    });

    $(".suppliers-slider").slick({ infinite: !0, speed: 300, slidesToShow: 4, slidesToScroll: 1, centerMode: !1, centerPadding: "60px", arrows: !0, dots: !1, responsive: [{ breakpoint: 1199, settings: { slidesToShow: 4, slidesToScroll: 3, infinite: !0, dots: !1, centerMode: !1 } }, { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 3, dots: !1, centerMode: !0 } }, { breakpoint: 767, settings: { slidesToShow: 1, slidesToScroll: 1, dots: !1, centerMode: !0 } }] });
    $(".al_t2_suppliers-slider").slick({ infinite: !0, speed: 300, slidesToShow: 6, slidesToScroll: 1, centerMode: !1, centerPadding: "0", arrows: !0, dots: !1, responsive: [{ breakpoint: 1367, settings: { slidesToShow: 4, slidesToScroll: 2, infinite: !0 } }, { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 1 } }, { breakpoint: 767, settings: { slidesToShow: 2, slidesToScroll: 1 } }, { breakpoint: 360, settings: { slidesToShow: 1, slidesToScroll: 1 } }] });
    $(".product-5").slick({ arrows: !0, dots: !1, infinite: !0, dots: !1, speed: 300, slidesToShow: 6, slidesToScroll: 3, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }, { breakpoint: 420, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".vendor-product").slick({ infinite: !0, speed: 300, arrows: !0, dots: !1, slidesToShow: 4, slidesToScroll: 2, autoplay: !0, autoplaySpeed: 5e3, rtl: !1, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 767, settings: { slidesToShow: 1, slidesToScroll: 1, arrows: !0 } }] });
    $(".booking-time, .agentSlotSlider").slick({ dots: !1, arrows: !0, infinite: !0, speed: 300, slidesToShow: 3, slidesToScroll: 3, responsive: [{ breakpoint: 1367, settings: { slidesToShow: 4, slidesToScroll: 4, infinite: !0 } }, { breakpoint: 1024, settings: { slidesToShow: 4, slidesToScroll: 4, infinite: !0 } }, { breakpoint: 767, settings: { slidesToShow: 3, arrows: !0, slidesToScroll: 3, infinite: !0 } }, { breakpoint: 480, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    //$('.').slick({dots:!1,arrows:!0,infinite:!0,speed:300,slidesToShow:3,slidesToScroll:3,responsive:[{breakpoint:1367,settings:{slidesToShow:4,slidesToScroll:4,infinite:!0}},{breakpoint:1024,settings:{slidesToShow:4,slidesToScroll:4,infinite:!0}},{breakpoint:767,settings:{slidesToShow:3,arrows:!0,slidesToScroll:3,infinite:!0}},{breakpoint:480,settings:{slidesToShow:1,arrows:!0,slidesToScroll:1}}]});
    if ($('body').attr('dir') == 'rtl') {
        $(".slide-6, .brand-slider, .product-4, .product-5, .brand-slider, .suppliers-slider, .al_t2_suppliers-slider, .booking-time, .vendor-product").slick('slickSetOption', { rtl: true }, true);
    }
}


window.initializeSliderNew = function initializeSliderNew() {
    $(".product-4-featured_products").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "60px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".product-4-new_products").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "60px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".product-4-on_sale").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "60px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".recent-orders").slick({ arrows: !0, dots: !1, infinite: !0, speed: 300, slidesToShow: 2, slidesToScroll: 1, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 4, slidesToScroll: 2 } }, { breakpoint: 991, settings: { slidesToShow: 3, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }, { breakpoint: 420, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });

    $(".brand-slider").slick({ arrows: !0, dots: !1, infinite: !0, speed: 300, slidesToShow: 4, slidesToScroll: 3, responsive: [{ breakpoint: 1367, settings: { slidesToShow: 4, slidesToScroll: 2, infinite: !0 } }, { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 1 } }, { breakpoint: 767, settings: { slidesToShow: 4, slidesToScroll: 1 } }, { breakpoint: 360, settings: { slidesToShow: 4, slidesToScroll: 1 } }] });

    $(".suppliers-slider-trending_vendors").slick({ infinite: !0, speed: 300, slidesToShow: 6, slidesToScroll: 1, centerMode: !1, centerPadding: "60px", arrows: !0, dots: !1, responsive: [{ breakpoint: 1199, settings: { slidesToShow: 4, slidesToScroll: 3, infinite: !0, dots: !1, centerMode: !1 } }, { breakpoint: 991, settings: { slidesToShow: 3, slidesToScroll: 3, dots: !1, centerMode: !0 } }, { breakpoint: 767, settings: { slidesToShow: 1, slidesToScroll: 1, dots: !1, centerMode: !0 } }, { breakpoint: 576, settings: { slidesToShow: 1, slidesToScroll: 1, dots: !1, centerMode: !0 } }] });
    $(".product-5").slick({ arrows: !0, dots: !1, infinite: !0, dots: !1, speed: 300, slidesToShow: 6, slidesToScroll: 3, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }, { breakpoint: 420, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });
    $(".vendor-product").slick({ infinite: !0, speed: 300, arrows: !0, dots: !1, slidesToShow: 4, slidesToScroll: 2, autoplay: !0, autoplaySpeed: 5e3, rtl: !1, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 767, settings: { slidesToShow: 1, slidesToScroll: 1, arrows: !0 } }] });
    $(".booking-time").slick({ dots: !1, arrows: !0, infinite: !0, speed: 300, slidesToShow: 3, slidesToScroll: 3, responsive: [{ breakpoint: 1367, settings: { slidesToShow: 4, slidesToScroll: 4, infinite: !0 } }, { breakpoint: 1024, settings: { slidesToShow: 4, slidesToScroll: 4, infinite: !0 } }, { breakpoint: 767, settings: { slidesToShow: 3, arrows: !0, slidesToScroll: 3, infinite: !0 } }, { breakpoint: 480, settings: { slidesToShow: 1, arrows: !0, slidesToScroll: 1 } }] });

    if ($('body').attr('dir') == 'rtl') {
        $(".slide-6, .brand-slider, .product-4, .product-5, .brand-slider, .suppliers-slider, .booking-time, .vendor-product").slick('slickSetOption', { rtl: true }, true);
    }
}



$(document).ready(function () {

    // $(".toggle-nav").click(function() {
    //     $("body").toggleClass("overflow-hidden");
    // });

    $(".toggle-password").click(function () { $(this).toggleClass("eye"); var t = $($(this).attr("toggle")); "password" == t.attr("type") ? t.attr("type", "text") : t.attr("type", "password") });
    $(".mobile-search-btn").click(function () { $(".radius-bar").slideToggle(); });
    $("#side_menu_toggle").click(function () { $(".manu-bars").toggleClass("menu-btn"); $(".scrollspy-menu").toggleClass("side-menu-open"); $("body").toggleClass("overflow-hidden"); });
    $('#myModal').on('show.bs.modal', function (e) { document.querySelector('meta[name="viewport"]').content = 'width=device-width, minimum-scale=1.0, maximum-scale=1.0, initial-scale=1.0'; });

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') }
    });

    let queryString = window.location.search;
    let path = window.location.pathname;
    let urlParams = new URLSearchParams(queryString);
    if ((urlParams.has('PayerID')) && (urlParams.has('token'))) {
        $('.spinner-overlay').show();
        let tipAmount = 0;
        if (urlParams.has('tip')) {
            tipAmount = urlParams.get('tip');
        }
        order_number = 0;
        if (urlParams.has('ordernumber')) {
            order_number = urlParams.get('ordernumber');
        }
        paymentSuccessViaPaypal(urlParams.get('amount'), urlParams.get('token'), urlParams.get('PayerID'), path, tipAmount, order_number);
    }

    initialize();
    cartHeader();

});
$("#main_search_box").blur(function (e) {
    setTimeout(function () {
        $('#search_box_main_div').html('').hide();
    },
        500);
});
$("#mobile_search_box_btn").click(function () {
    $('.radius-bar').slideToggle();
});

$(document).on("click", "#search_viewall", function (e) {
    let keyword = $("#main_search_box").val();
    let url = "/search-all/" + keyword;
    // url = url.replace(':id', keyword);
    // document.location.href=url;
    window.location.href = url;
    return false;
});
$(document).on("click", "#search_map_view", function (e) {
    let keyword = $("#main_search_box").val();
    let url = "/search-all/" + keyword + "/map-view";
    window.location.href = url;
    return false;
});
$('input[type=search]').on('search', function () {
    $('#search_box_main_div').html('').hide();
});
$("#main_search_box").focus(function () {
    let keyword = $(this).val();
    searchResults(keyword);
});
$("#main_search_box").keyup(function () {
    let keyword = $(this).val();
    searchResults(keyword);
});
var searchAjaxCall = 'ToCancelPrevReq';
function searchResults(keyword) {
    if (keyword.length <= 2) {
        $('#search_box_main_div').html('').hide();
    }
    if (keyword.length >= 2) {
        searchAjaxCall = $.ajax({
            type: "GET",
            dataType: 'json',
            url: autocomplete_url,
            data: { keyword: keyword },
            beforeSend: function () {
                if (searchAjaxCall != 'ToCancelPrevReq' && searchAjaxCall.readyState < 4) {
                    searchAjaxCall.abort();
                }
            },
            success: function (response) {
                if (response.status == 'Success') {
                    $('#search_box_main_div').html('');
                    if (response.data.length != 0) {
                        let search_box_category_template = _.template($('#search_box_main_div_template').html());
                        $("#search_box_main_div").append(search_box_category_template({ results: response.data })).show();
                    } else {
                        $("#search_box_main_div").html('<p class="text-center my-3">No result found. Please try a new search</p>').show();
                    }
                }
            }
        });
    }
}

if ($('#cart_main_page').length > 0) {
    let address_checked = $("input:radio[name='address_id']").is(":checked");
    if (address_checked) {
        $('#order_placed_btn').prop('disabled', false);
    } else {
        $('#order_placed_btn').prop('disabled', true);
    }
    $("form").submit(function (e) {
        let address_id = $("input:radio[name='address_id']").is(":checked");
        if (!address_id) {
            // alert('Address field required.');
            Swal.fire({
                // title: "Warning!",
                text: _language.getLanString('Address field required.'),
                icon: "error",
                button: "OK",
            });
            return false;
        }
    });
}
var card = '';
var stripe = '';
var yoco = '';


$(".search_btn").click(function () {
    $(".search_warpper").slideToggle("slow");
});

$(".close_btn").click(function () {
    $(".search_warpper").slideUp("slow");
});
$(document).delegate(".mobile-back", "click", function () {
    $(".sm-horizontal").css("right", "-410px");
});

function settingData(type = '', v1 = '', v2 = '') {
    $.ajax({
        type: "post",
        dataType: "json",
        url: change_primary_data_url,
        data: {
            "type": type,
            "value1": v1,
            "value2": v2
        },
        success: function (response) {
            location.reload();
        },
        error: function (data) {
            location.reload();
        },
    });
}

function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
$('.addWishList').click(function () {
    var sku = $(this).attr('proSku');
    var remveFrmWishlist = $(this).attr('remWishlist');
    var addWishlist = $(this).attr('addWishlist');
    var _this = $(this);
    $.ajax({
        type: "post",
        dataType: "json",
        url: add_to_whishlist_url,
        data: {
            "_token": $('meta[name="_token"]').attr('content'),
            "sku": sku,
            "variant_id": $('#prod_variant_id').val()
        },
        success: function (res) {
            $('.wishListCount').removeClass('fa-heart');
            $('.wishListCount').removeClass('fa-heart-o');
            if (res.status == "success") {
                if (_this.hasClass('btn-solid')) {
                    if (res.message.indexOf('added') !== -1) {
                        _this.text(remveFrmWishlist);
                    } else {
                        _this.text(addWishlist);

                    }
                }
                if (res.wishListCount > 0) {
                    $('.wishListCount').addClass('fa-heart');
                } else {
                    $('.wishListCount').addClass('fa-heart-o');
                }
            } else {
                location.reload();
            }
        }
    });
});
$('.customerLang').click(function () {
    var changLang = $(this).attr('langId');
    settingData('language', changLang);
});
$('.change_language_selector').on('change', function () {
    var changLang = $(this).val();
    settingData('language', changLang);
});

$('.customerCurr').click(function () {
    var changcurrId = $(this).attr('currId');
    var changSymbol = $(this).attr('currSymbol');
    settingData('currency', changcurrId, changSymbol);
});

function stripeInitialize() {
    stripe = Stripe(stripe_publishable_key);
    var elements = stripe.elements();
    var style = {
        base: { fontSize: '16px', color: '#32325d', borderColor: '#ced4da' },
    };
    card = elements.create('card', { hidePostalCode: true, style: style });
    card.mount('#stripe-card-element');
}

function stripeOXXOInitialize() {
    stripeOxxo = Stripe(stripe_oxxo_publishable_key);
}

function stripeIdealInitialize() {
    stripe_ideal = Stripe(stripe_ideal_publishable_key);
    var elements = stripe_ideal.elements();
    var options = {
        // Custom styling can be passed to options when creating an Element
        style: {
            base: {
                padding: '10px 12px',
                color: '#32325d',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                },
            },
        },
    };

    // Create an instance of the idealBank Element
    var idealBank = elements.create('idealBank', options);
    // Add an instance of the idealBank Element into
    // the `ideal-bank-element` <div>
    idealBank.mount('#ideal-bank-element');
}

function stripeFPXInitialize() {
    stripe_fpx = Stripe(stripe_fpx_publishable_key);
    var elements = stripe_fpx.elements();
    var style = {
        base: {
            // Add your base input styles here. For example:
            padding: '10px 12px',
            color: '#32325d',
            fontSize: '16px',
        },
    };
    fpxBank = elements.create('fpxBank',
        {
            style: style,
            accountHolderType: 'individual',
        }
    );
    // Add an instance of the fpxBank Element into the container with id `fpx-bank-element`.
    fpxBank.mount('#fpx-bank-element');
}

if (($("#stripe-card-element").length > 0) && (stripe_publishable_key != '')) {
    stripeInitialize();
}
if (($("#fpx-bank-element").length > 0) && (stripe_fpx_publishable_key != '')) {
    stripeFPXInitialize();
}

if (($("#ideal-bank-element").length > 0) && (stripe_ideal_publishable_key != '')) {
    stripeIdealInitialize();
}


$(document).delegate(".subscribe_btn", "click", function () {
    var sub_id = $(this).attr('data-id');
    $.ajax({
        type: "get",
        dataType: "json",
        url: check_active_subscription_url.replace(":id", sub_id),
        success: function (response) {
            if (response.status == "Success") {
                $.ajax({
                    type: "get",
                    dataType: "json",
                    url: subscription_payment_options_url.replace(":id", sub_id),
                    success: function (response) {
                        if (response.status == "Success") {
                            $("#subscription_payment #subscription_title").html(response.sub_plan.title);
                            $("#subscription_payment #subscription_price").html(response.currencySymbol + response.sub_plan.price);
                            $("#subscription_payment #subscription_frequency").html(response.sub_plan.frequency);
                            $("#subscription_payment #features_list").html(response.sub_plan.features);
                            $("#subscription_payment #subscription_id").val(sub_id);
                            $("#subscription_payment #subscription_amount").val(response.sub_plan.price);
                            $("#subscription_payment #type_id").val(response.sub_plan.type_id);
                            $("#subscription_payment #subscription_payment_methods").html('');
                            let payment_method_template = _.template($('#payment_method_template').html());
                            $("#subscription_payment #subscription_payment_methods").append(payment_method_template({ payment_options: response.payment_options }));
                            if (response.payment_options == '') {
                                $("#subscription_payment .subscription_confirm_btn").hide();
                            }
                            $("#subscription_payment").modal("show");
                            if (response.sub_plan.type_id == 2) {
                                var form = document.getElementById('meal-subscription-form');
                                var formData = new FormData(form); // Create a new FormData object

                                var serializedData = JSON.stringify(Object.fromEntries(formData)); // Serialize the form data to JSON

                                var serializedField = document.createElement("input"); // Create a new input element
                                serializedField.type = "hidden";
                                serializedField.name = "serializedForm";
                                serializedField.value = serializedData;

                                document.getElementById('subscription_payment_form').appendChild(serializedField); // Append the serialized field to the form

                                // Display the serialized form data in the console
                            }
                            if (stripe_publishable_key != '') {
                                stripeInitialize();
                            }
                            if (stripe_fpx_publishable_key != '') {
                                stripeFPXInitialize();
                            }
                            if (stripe_ideal_publishable_key != '') {
                                stripeIdealInitialize();
                            }
                        }
                    },
                    error: function (error) {
                        var response = $.parseJSON(error.responseText);
                        let error_messages = response.message;
                        $("#error_response .message_body").html(error_messages);
                        $("#error_response").modal("show");
                    }
                });
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
            $("#error_response .message_body").html(error_messages);
            $("#error_response").modal("show");
        }
    });
});
$(document).delegate(".subscription_confirm_btn", "click", function () {
    var _this = $(".subscription_confirm_btn");
    _this.attr("disabled", true);
    var selected_option = $("input[name='subscription_payment_method']:checked");
    // var subscription_id = $('#subscription_payment_form #subscription_id').val();
    var payment_option_id = selected_option.data("payment_option_id");

    if (payment_option_id == 58) {
        var expData = $('#date-element-powertrans').val();
        var [expMonth, expYear] = [expData.slice(2), expData.slice(0, 2)]
        var newDate = expMonth + '/' + expYear;
        cardJson = {
            'cno': $('#card-element-powertrans').val(),
            'dt': newDate,
            'cv': $('#cvv-element-powertrans').val(),
            'name': 'powertrans',
        }
        if (cardValidation(cardJson)) {
            console.log('Credit card information is valid.');
        }
        else {
            success_error_alert('error', 'Invalid credit card information', "#powertrans_card_error");
            return false;
        }
    }

    if ((selected_option.length > 0) && (payment_option_id > 0)) {
        subscriptionPaymentOPtions(payment_option_id);

    } else {
        _this.attr("disabled", false);
        success_error_alert('error', 'Please select any payment option', "#subscription_payment .payment_response");
    }
});

function productRemove(product_id, cartproduct_id, vendor_id) {

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: delete_cart_product_url,
        data: { cartproduct_id: cartproduct_id },
        beforeSend: function () {
            if ($("#cart_table").length > 0) {
                $(".spinner-box").show();
                $("#cart_table").hide();
            }
        },
        success: function (data) {
            if (data.status == 'success') {
                $('#cart_product_' + cartproduct_id).remove();
                $('#shopping_cart1_' + cartproduct_id).remove();
                $('#tr_vendor_products_' + cartproduct_id).remove();
                if ($("#tbody_" + vendor_id + " > vendor_products_tr").length == 0) {
                    $('#tbody_' + vendor_id).remove();
                    $('#thead_' + vendor_id).remove();
                }
                // if ($("[id^=tr_vendor_products_]").length == 0) {
                //     if ($("#cart_main_page").length) {
                //         $("#cart_main_page").html('');
                //         $('#tbody_' + vendor_id).remove()
                //         let empty_cart_template = _.template($('#empty_cart_template').html());
                //         $("#cart_main_page").append(empty_cart_template());
                //     }
                // }
                if ($("[id^=cart_product_]").length == 0) {
                    $(".shopping-cart").html('');
                }
                cartHeader();
                cartTotalProductCount();
                if ($(`#add_button_href${cartproduct_id}`).length > 0) {
                    $(`#add_button_href${cartproduct_id}`).show();
                    $(`#added_button_href${cartproduct_id}`).hide();
                    $(`#add_button_href${cartproduct_id}`).text('Add');
                }
                if ($(`#add_button_href${product_id}`).length > 0) {
                    $(`#add_button_href${product_id}`).show();
                    $(`#added_button_href${product_id}`).hide();
                }

                if ($('#show_plus_minus' + cartproduct_id).length != 0) {
                    if ($('.addon_variant_quantity_' + cartproduct_id).closest('.customized_product_row').length > 0) {
                        $('.addon_variant_quantity_' + cartproduct_id).closest('.cart-box-outer').remove();
                        var total_qty = $('.add_vendor_product[data-product_id="' + product_id + '"]').next().find('input').val();
                        if (total_qty > 1) {
                            $('.add_vendor_product[data-product_id="' + product_id + '"]').next().find('input').val(--total_qty);
                        } else {
                            $('#customize_repeated_item_modal').modal('hide');
                            $('.add_vendor_product[data-product_id="' + product_id + '"]').show();
                            $('.add_vendor_product[data-product_id="' + product_id + '"]').next().hide();
                        }
                    }
                    else {
                        $('#show_plus_minus' + cartproduct_id).find('.input_qty').val(1);
                        $('#show_plus_minus' + cartproduct_id).hide();
                        $('#add_button_href' + cartproduct_id).show();

                        let addons_div = $('#addon_div' + cartproduct_id);
                        addons_div.hide();
                    }
                }
                if ($('#next-button-ondemand-2').length != 0) {
                    $("#next-button-ondemand-2").hide();
                }

            }
        }
    });
}

$(document).on("change", "input:radio[name='address_id']", function () {
    if ($(this).val()) {
        $('#order_placed_btn').prop('disabled', false);
        if ($("#cart_table").length > 0) {
            $(".spinner-box").show();
            $("#cart_table").hide();
        }
        if (is_service_product_price_from_dispatch_forOnDemand == 1) {
            id = "cart_address_id_{{$address->id}}"
            $("input:radio[name=address_id]:checked")[0].checked = false;
            $selectedAddress = OrderStorage.getStorage('cartAddressId');
            $(`input:radio[name=address_id][value=${selectedAddress}]`).prop('checked', true)
            $(".spinner-box").hide();
            $("#cart_table").show();
            Swal.fire({
                icon: 'error',
                title: _language.getLanString('Oops...'),
                text: _language.getLanString('Sorry ! address can not be change!'),
            })
            return false;
        }

        cartHeader($(this).val());
    }
});


$(document).on("change", ".schedule_datetime", function () {
    var schedule_dt = $(this).val();
    var vendor_id = $('#vendor_id').val();
    if (typeof $("#edit_order_schedule_datetime").val() != 'undefined' && $("#edit_order_schedule_datetime").val() != '' && ($("#edit_order_schedule_datetime").val() != schedule_dt)) {
        success_error_alert('error', error_unchanged_schedule_date, ".cart_response");
        var edit_order_schedule_datetime = $("#edit_order_schedule_datetime").val();
        schedule_datetime = edit_order_schedule_datetime.split(" ")[0];
        $(this).val(schedule_datetime);
        return false;
    }
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: check_schedule_slots,
        data: { date: schedule_dt, vendor_id: vendor_id },
        success: function (response) {
            if (response.status == "Success") {
                $('#slot').html(response.data);
                if (typeof $('#edit_order_schedule_slot').val() != 'undefined') {
                    $(".schedule_datetime").change();
                    $('#slot').val($('#edit_order_schedule_slot').val());
                    $('#slot option').prop('disabled', true);
                    $('#slot option[value="' + $('#edit_order_schedule_slot').val() + '"]').attr("disabled", false);
                }
            } else {
                success_error_alert('error', response.message, ".cart_response");
                $('#slot').html(response.data);
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            success_error_alert('error', response.message, ".cart_response");
            $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
        }
    });
});

$(document).on("change", ".pickup_schedule_datetime", function () {
    var schedule_dt = $(this).val();
    var vendor_id = $('#vendor_id').val();
    $('#loaderforjs').show();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: check_pickup_schedule_slots,
        data: { date: schedule_dt, vendor_id: vendor_id },
        success: function (response) {
            if (response.status == "Success") {
                $('#schedule_pickup_slot').html(response.data);
                $('#loaderforjs').hide();
            } else {
                success_error_alert('error', response.message, ".cart_response");
                $('#schedule_pickup_slot').html(response.data);
                $('#loaderforjs').hide();
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            success_error_alert('error', response.message, ".cart_response");
            $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            $('#loaderforjs').hide();
        }
    });
});

$(document).on("change", ".dropoff_schedule_datetime", function () {
    var schedule_dt = $(this).val();
    var vendor_id = $('#vendor_id').val();
    $('#loaderfordrop').show();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: check_dropoff_schedule_slots,
        data: { date: schedule_dt, vendor_id: vendor_id },
        success: function (response) {
            if (response.status == "Success") {
                $('#schedule_dropoff_slot').html(response.data);
                $('#loaderfordrop').hide();
            } else {
                success_error_alert('error', response.message, ".cart_response");
                $('#schedule_dropoff_slot').html(response.data);
                $('#loaderfordrop').hide();
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            success_error_alert('error', response.message, ".cart_response");
            $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            $('#loaderfordrop').hide();
        }
    });
});

$(document).on('click', '#taskschedule', function () {
    $('#schedule_div').show();
    $('.taskschedulebtn').hide();
    $('.cross').show();
    $('#tasknow').val('schedule');
});
$(document).on('click', '.cross', function () {
    $('#schedule_div').attr("style", "display: none !important");
    $('.taskschedulebtn').show();
    $('.cross').hide();
    $('#schedule_datetime').val('');
    $('#tasknow').val('now');
    // cartHeader();
});

$(document).on("click", ".clproduct_cart_order_form", function (e) {
    e.preventDefault();
    let cart_product_id = $(this).attr("data-product_id");
    let cart_vendor_id = $(this).attr("data-vendor_id");
    var href = get_product_faq + "/" + cart_product_id;
    $.get(href, function (response) {
        $('#cart_product_order_form').modal('show');
        $('#cart_product-order-form-modal').html(response);
    });
});
// category kyc verification
$(document).on("click", ".cl_category_kyc_form", function (e) {
    e.preventDefault();
    let category_ids = $(this).attr("data-category_id");
    //$(this).prop('disabled', true);
    var href = get_category_kyc_document;

    $.ajax({
        data: { category_ids: category_ids },
        type: "GET",
        dataType: 'json',
        url: get_category_kyc_document,
        success: function (response) {
            $('#cart_product_order_form').modal('show');
            $('#cart_product-order-form-modal').html(response);
        },
        error: function (error) {

        }
        //$(this).prop('disabled', false);
    });

});

$(document).on('click', '#category_kycform_submit', function (e) {
    $('#proceed_to_pay_loader').show();
    e.preventDefault();
    var input = '';

    $(this).attr("disabled", true);
    var form = document.getElementById('category_kyc_form_in_cart');
    var data_uri = post_category_kyc_document;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: "post",
        headers: {
            Accept: "application/json"
        },
        url: data_uri,
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.status == 'success') {
                $('#category_kycform_submit').attr("disabled", false);
                $(".modal .close").click();
                location.reload();
            } else {
                $('#category_kycform_submit').attr("disabled", false);
                $(".show_all_error.invalid-feedback").show();
                $(".show_all_error.invalid-feedback").text(response.message);

            }
            return response;
        },
        beforeSend: function () {
            $(".loader_box").show();
        },
        complete: function () {
            $(".loader_box").hide();
        },
        error: function (response) {
            $('#category_kycform_submit').attr("disabled", false);
            if (response.status === 422) {
                let errors = response.responseJSON.errors;
                Object.keys(errors).forEach(function (key) {
                    $("#" + key + "Input input").addClass("is-invalid");
                    $("#" + key + "Input span.invalid-feedback").children("strong").text(errors[key][0]);
                    $("#" + key + "Input span.invalid-feedback").show();
                });
            } else {
                $(".show_all_error.invalid-feedback").show();
                $(".show_all_error.invalid-feedback").text('Something went wrong, Please try Again.');
            }
            return response;
        }
    });

});

function toTimestamp(strDate) {
    var date = new Date(strDate);
    return date.getTime();
}

async function checkSlotValidation() {
    $returnVal = 1;
    var product_schedule_slot = document.getElementsByClassName("vendor_product_schedule_slot");
    await $.each(product_schedule_slot, function (index, value) {

        var sel_val = $(value).val();
        if (sel_val == '') {
            $returnVal = 0;
            return false;

        }

    });
    return $returnVal;

}
$(document).on("click", "#order_placed_btn", async function () {

    $('.adone_item .item').each(function (index, element) {
        let min = $(this).data('min');
        let max = $(this).data('max');
        let checkedBox = $(this).find('input[type="checkbox"]:checked').length
        if (checkedBox < min || checkedBox > max) {

            $(this).css('transition', 'border 0.2s ease-in-out');
            $(this).css('border', '1px solid red');

            setTimeout(() => {
                $(this).css('border', 'none');
                $(this).css('transition', 'none');
            }, 3000);
        }
    });

    // return true;

    if (typeof $("#edit_order_schedule_datetime").val() != 'undefined' && $("#edit_order_schedule_datetime").val() != '' && ($("#edit_order_schedule_datetime").val() != $("#schedule_datetime").val())) {
        success_error_alert('error', error_unchanged_schedule_date, ".cart_response");
        $("#schedule_datetime").val($("#edit_order_schedule_datetime").val());
        return false;
    }

    $(".validate_prescription").each(function () {
        var cart_product_prescription = $(this).data('cart_product_prescription');
        if (cart_product_prescription == 0) {
            $(this).addClass('has_error');
        }
    });

    if ($(".prescription_btn").hasClass('has_error')) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'kindly select a prescription!',
            //footer: '<a href="">Why do I have this issue?</a>'
        })
        return false;
    }



    // var checkboxes = $('.checked-cart-product');
    // var checkedCheckboxes = checkboxes.filter(':checked');
    // if (checkedCheckboxes.length === 0) {

    //     Swal.fire({
    //         icon: 'error',
    //         title: 'Oops...',
    //         text: 'Please select at least one product.',
    //         //footer: '<a href="">Why do I have this issue?</a>'
    //     });
    //     return false;
    // }



    if ($("#agree_term_check").length > 0) {
        var checkbox = document.getElementById("agree_term_check");
        if (!checkbox.checked) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please accept terms and conditions.',
                //footer: '<a href="">Why do I have this issue?</a>'
            });
            return false;
        }
    }

    var delivery_type = 'D';
    var other_taxes_string = '';
    if ($("#other_taxes_string").val() != null) {
        other_taxes_string = $("#other_taxes_string").val();
    }
    var selected = document.querySelector(".delivery-fee.select");
    if (selected) {
        delivery_type = selected.value;
    }

    if ($("input[name='product_faq_ids']").length > 0) {
        success_error_alert('error', 'Product order form is required! kindly fill the details.', ".cart_response");
        return false;
    }


    //$("input[name='category_kyc_ids']").length > 0 ||

    if (($("input[name='without_category_kyc']").val() != 1)) {
        success_error_alert('error', 'User Place Order is required! kindly fill the details.', ".cart_response");
        return false;

    }

    var returnData = await checkSlotValidation();
    if (returnData == 0) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'kindly select a slot!',
            //footer: '<a href="">Why do I have this issue?</a>'
        })
        return false;
    }

    var vendorScheduleDatetime = $('.vendor_schedule_datetime').length
    if (vendorScheduleDatetime > 0) {
        $(".vendor_schedule_datetime").each(function () {
            var scheduleTime = $(this).closest('.vendor_slot_cart').find('select').find(':selected').val();
            if (scheduleTime == '') {
                success_error_alert('error', error_Slot_is_required, ".cart_response");
                return false;
            }
        });
    }

    //$('.alert-danger').html('');
    if ((typeof guest_cart != undefined) && (guest_cart == 1)) {
        // window.location.href = login_url;
        $("#login_modal").modal("show");
        return false;
    }

    var address = $("input[name='address_id']").val();
    if ((vendor_type == 'delivery' || vendor_type == 'on_demand' || vendor_type == 'rental') && ((address == '') || (address < 1) || ($("input[name='address_id']").length < 1))) {
        success_error_alert('error', 'Please add a valid address to continue', ".cart_response");
        return false;
    }
    if (vendor_type == 'dine_in') {
        var dinein_table = $("#vendor_table").val();
        if (dinein_table == '') {
            success_error_alert('error', 'Please book a table to continue', ".cart_response");
            return false;
        }
    }
    if (business_type == 'laundry' && scheduling_with_slots == 1 && off_scheduling_at_cart == 0) {
        var task_type = 'schedule';
        var schedule_dt = $("#pickup_schedule_datetime").val();
        var schedule_dt_dropoff = $("#dropoff_schedule_datetime").val();
        var slot = $("#schedule_pickup_slot").val();
        var slot_dropoff = $("#schedule_dropoff_slot").val();
        var checkSlot = $('#checkPickUpSlot').val();
        var checkDropoffSlot = $('#checkDropoffSlot').val();
    } else {
        var task_type = $("input[name='task_type']").val();
        var schedule_dt = $("#schedule_datetime").val();
        var slot = $("#slot").val();
        var checkSlot = $('#checkSlot').val();
    }

    var now = new Date().toISOString();
    if (task_type == 'schedule') {
        if (slot) {
            var stime = 'T' + slot.split(" - ", 1);
            var schedule_dtck = toTimestamp(schedule_dt + ' ' + slot.split(" - ", 1));
            var schedule_dt_check = schedule_dt + stime;
        } else {
            var schedule_dt_check = ((schedule_dt) ? toTimestamp(schedule_dt) : 'undefined');
            var now = toTimestamp(now);
        }

        if (business_type == 'laundry' && scheduling_with_slots == 1) {
            var schedule_dt_dropoff_time = 'T' + slot_dropoff.split(" - ", 1);
            var schedule_dt_dropoffck = toTimestamp(schedule_dt_dropoff + ' ' + slot_dropoff.split(" - ", 1));
            var schedule_dt_dropoff = schedule_dt_dropoff + schedule_dt_dropoff_time;


            if (schedule_dtck > schedule_dt_dropoffck) {
                // return false if  pickup_schedule_datetime and dropoff_schedule_datetime is same | By Ovi
                success_error_alert('error', 'Dropoff datetime is not less than pickup datetime.', ".cart_response");
                return false;
            }

        }

        if (schedule_dt == '' || schedule_dt == 'undefined') {
            success_error_alert('error', error_Schedule_date_is_required, ".cart_response");
            return false;
        } else if (schedule_dt_check < now) {
            success_error_alert('error', error_Invalid_Schedule_date, ".cart_response");
            return false;
        }
    } else {
        var checkSlot = 0;
    }
    if (checkSlot == '1') {
        if (!slot) {
            success_error_alert('error', error_Slot_is_required, ".cart_response");
            return false;
        }

        if (business_type == 'laundry' && scheduling_with_slots == 1) {
            if (!checkDropoffSlot) {
                success_error_alert('error', 'Dropoff Slot is required.', ".cart_response");
                return false;
            }
        }
    }
    let cartAmount = $("input[name='cart_total_payable_amount']").val();
    let comment_for_pickup_driver = $("input[name='comment_for_pickup_driver']").val(); //commnet for pickup
    let comment_for_dropoff_driver = $("input[name='comment_for_dropoff_driver']").val(); //commnet for dropoff
    let comment_for_vendor = $("input[name='comment_for_vendor']").val(); //commnet for vendor

    var bookingOptionId = '';
    var rentalProtectionId = '';
    // var addonsId = '';
    // var addonsOptionId = '';
    var rentalProtectionId = '';
    var bookingOptionId = '';

    if (business_type == 'laundry' && scheduling_with_slots == 1) {
        var schedule_pickup = schedule_dt;
        var schedule_dropoff = schedule_dt_dropoff;
    } else {
        var schedule_pickup = $("#schedule_datetime_pickup").val();
        var schedule_dropoff = $("#schedule_datetime_dropoff").val();
    }
    var specific_instructions = $("#specific_instructions").val();
    let tip = $("#cart_tip_amount").val();

    if ((schedule_pickup != undefined)) {
        if (schedule_pickup == '') {
            success_error_alert('error', 'Please select schedule pickup date & time', ".cart_response");
            return false;
        }
    }

    if ((schedule_dropoff != undefined)) {
        if (schedule_dropoff == '') {
            success_error_alert('error', 'Please select schedule dropoff date & time', ".cart_response");
            return false;
        }
    }

    if (cartAmount == 0) {
        var params = [specific_instructions, task_type, schedule_dropoff, schedule_pickup, schedule_dt, comment_for_pickup_driver, comment_for_dropoff_driver, comment_for_vendor, delivery_type, slot, address];
        // Save Cart Page Detail Forcely If user is paying from his cart.
        var checkParam = saveCartPageDetails(params);
        if (checkParam != false) {
            placeOrder(address, 1, '', tip, delivery_type, other_taxes_string); // Adready Added
            return false;
        }
    } else {
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: update_cart_schedule,
            data: { specific_instructions: specific_instructions, task_type: task_type, schedule_dropoff: schedule_dropoff, schedule_pickup: schedule_pickup, schedule_dt: schedule_dt, comment_for_pickup_driver: comment_for_pickup_driver, comment_for_dropoff_driver: comment_for_dropoff_driver, comment_for_vendor: comment_for_vendor, delivery_type: delivery_type, slot: slot, dropoff_scheduled_slot: slot_dropoff, address: address, payable_amount: cartAmount },
            success: function (response) {
                $(".error_prescription").attr("style", "display:none");
                if (response.status == "passbase_submitted") {
                    Swal.fire({
                        text: response.message,
                        icon: "error",
                        button: "OK",
                    });
                    return false;
                } else if (response.status == "passbase_rejected" || response.status == "passbase_pending") {
                    Swal.fire({
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: `Ok`,
                    }).then((result) => {
                        if (result.value) {
                            window.location.replace(passbase_page);
                        }
                    });
                    return false;
                } else if (response.status == "error_prescription") {
                    $.each(response.presciptionProducts, function (key, product_id) {
                        $("#error_prescription_" + product_id).attr("style", "display:block");
                    });

                    return false;
                } else if (response.status == "Pending") {
                    window.location.replace(verifyaccounturl);
                } else if (response.status == "Success") {
                    $.ajax({
                        data: {},
                        type: "POST",
                        dataType: 'json',
                        url: payment_option_list_url,
                        success: function (response) {
                            if (response.status == "Success") {
                                // $('#v_pills_tab').html('');
                                $('#v_pills_tabContent').html('');
                                // let payment_method_template = _.template($('#payment_method_template').html());
                                // $("#v_pills_tab").append(payment_method_template({ payment_options: response.data }));
                                let payment_method_tab_pane_template = _.template($('#payment_method_tab_pane_template').html());
                                $("#v_pills_tabContent").append(payment_method_tab_pane_template({ payment_options: response.data }));
                                $('#proceed_to_pay_modal').modal('show');

                                //mohit sir branch code added by sohail
                                var advanceCartTotalPayableAmount = $('#advance_cart_total_payable_amount').length;
                                if (advanceCartTotalPayableAmount == 1) {
                                    var amtHTML = 'Advanced Token Amount: <span id="total_amt">' + $('#advance_cart_total_payable_amount').html() + '</span>';
                                    $('#proceed_to_pay_modal #pay-billLabel').html(amtHTML);
                                } else {
                                    $('#proceed_to_pay_modal #total_amt').html($('#cart_total_payable_amount').html());
                                }
                                //till here
                                if (stripe_publishable_key != '') {
                                    stripeInitialize();
                                }
                                if (stripe_fpx_publishable_key != '') {
                                    stripeFPXInitialize();
                                }
                                if (stripe_ideal_publishable_key != '') {
                                    stripeIdealInitialize();
                                }
                            }
                        },
                        error: function (error) {
                            var response = $.parseJSON(error.responseText);

                            let error_messages = response.message;
                            $.each(error_messages, function (key, error_message) {
                                $('#min_order_validation_error_' + error_message.vendor_id).html(error_message.message).show();
                            });
                        }
                    });
                }
            },
            error: function (error) {
                var response = $.parseJSON(error.responseText);
                success_error_alert('error', response.message, ".cart_response");
                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            }
        });
    }
});

function saveCartPageDetails(...params) {
    var param = params.toString().split(',');
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: update_cart_schedule,
        data: { specific_instructions: param[0], task_type: param[1], schedule_dropoff: param[2], schedule_pickup: param[3], schedule_dt: param[4], comment_for_pickup_driver: param[5], comment_for_dropoff_driver: param[6], comment_for_vendor: param[7], delivery_type: param[8], slot: param[9], address: param[10] },
        success: function (response) {
            if (response.status == "Success") {
                return true
            } else {
                return false;
            }
        },
    });

}

$(document).delegate("#topup_wallet_btn", "click", function () {
    $.ajax({
        data: {},
        type: "POST",
        async: false,
        dataType: 'json',
        url: wallet_payment_options_url,
        success: function (response) {
            if (response.status == "Success") {
                $('#wallet_payment_methods').html('');
                let payment_method_template = _.template($('#payment_method_template').html());
                $("#wallet_payment_methods").append(payment_method_template({ payment_options: response.data }));
                if (response.data == '') {
                    $("#topup_wallet .topup_wallet_confirm").hide();
                } else {
                    if (stripe_publishable_key != '') {
                        stripeInitialize();
                    }
                    if (stripe_fpx_publishable_key != '') {
                        stripeFPXInitialize();
                    }
                    if (stripe_ideal_publishable_key != '') {
                        stripeIdealInitialize();
                    }
                }
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
        }
    });
});

$(document).delegate(".topup_wallet_btn_for_tip", "click", function () {
    // var order_number = $(this).data('order_number');
    // var tip_radio = $("input:radio.tip_radio:checked").val();
    // var custom_tip = $('#custom_tip_amount'+order_number).val();
    // if(tip_radio == 'custom')
    // {
    //     if(custom_tip <= 0 )
    //     {
    //         // swal('Waring!','Tip must be greater than 0','warning');
    //         // return false;
    //     }
    // }
    $.ajax({
        data: {},
        type: "POST",
        async: false,
        dataType: 'json',
        url: wallet_payment_options_url,
        success: function (response) {
            if (response.status == "Success") {
                $('#wallet_payment_methods').html('');
                let payment_method_template = _.template($('#payment_method_template').html());
                $("#wallet_payment_methods").append(payment_method_template({ payment_options: response.data }));
                if (response.data == '') {
                    $("#topup_wallet .topup_wallet_confirm").hide();
                } else {
                    if (stripe_publishable_key != '') {
                        stripeInitialize();
                    }
                    if (stripe_fpx_publishable_key != '') {
                        stripeFPXInitialize();
                    }
                    if (stripe_ideal_publishable_key != '') {
                        stripeIdealInitialize();
                    }

                }
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
        }
    });
});



var paymentAjaxData = {};

function stripePaymentMethodHandler(result) {
    let total_amount = 0;
    let tip = 0;
    let cartElement = $("input[name='cart_total_payable_amount']");
    let walletElement = $("input[name='wallet_amount']");
    let subscriptionElement = $("input[name='subscription_amount']");
    let pending_amount = $("input[name='amount_pending']");
    let tipElement = $("#cart_tip_amount");
    let payment_form = '';
    let type = $("input[name='type_id']").val();
    let mealSubscriptionForm = '';
    if (type == '2') {
        mealSubscriptionForm = $("input[name='serializedForm']").val()
        paymentAjaxData.days = $('#sub-days').html();
    }
    // let payment_option_id = paymentAjaxData.payment_option_id;

    paymentAjaxData.payment_method_id = result.paymentMethod.id;
    paymentAjaxData.type_id = type;
    // paymentAjaxData.payment_option_id = payment_option_id;
    if (path.indexOf("cart") !== -1) {
        payment_form = 'cart';
        total_amount = cartElement.val();
    } else if ((path.indexOf("wallet") !== -1) || ((typeof cabbookingwallet !== 'undefined') && (cabbookingwallet == 1))) {
        payment_form = 'wallet';
        total_amount = walletElement.val();
    } else if (path.indexOf("subscription") !== -1 || path.indexOf("mealSubscription") !== -1) {
        payment_form = 'subscription';
        total_amount = subscriptionElement.val();
        // paymentAjaxData = $("#subscription_payment_form").serializeArray();
        paymentAjaxData.subscription_id = $("#subscription_payment_form #subscription_id").val();
    } else if ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1)) {
        total_amount = walletElement.val();
        payment_form = 'tip';
        paymentAjaxData.order_number = $("#order_number").val();
    } else if (path.indexOf("giftCard") !== -1) {
        payment_form = 'giftCard';
        total_amount = $("input[name='giftCard_amount']").val();
        // paymentAjaxData = $("#subscription_payment_form").serializeArray();
        paymentAjaxData.gift_card_id = $("#giftCard_id").val();
        paymentAjaxData.send_card_to_name = $("input[name='send_card_to_name']").val();
        paymentAjaxData.send_card_to_mobile = $("input[name='send_card_to_mobile']").val();
        paymentAjaxData.send_card_to_email = $("input[name='send_card_to_email']").val();
        paymentAjaxData.send_card_to_address = $("input[name='send_card_to_address']").val();
        paymentAjaxData.send_card_is_delivery = $("#send_card_is_delivery").val();

    }

    if ((typeof pending_amount_for_past_order !== 'undefined') && (pending_amount_for_past_order == 1)) {
        total_amount = pending_amount.val();
        payment_form = 'pending_amount_form';
        paymentAjaxData.order_number = $("#order_number").val();
    }
    paymentAjaxData.payment_form = payment_form;
    paymentAjaxData.total_amount = total_amount;
    if (mealSubscriptionForm !== 'undefined' && mealSubscriptionForm !== '') {
        paymentAjaxData.mealSubscriptionForm = mealSubscriptionForm;
    }
    if (result.error) {
        swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something Went Wrong',
        }).then(function () {
            window.location.reload();
        });
    } else {
        fetch('/payment/payment_init', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-Token": $('input[name="_token"]').val()
            },
            credentials: "same-origin",
            body: JSON.stringify(paymentAjaxData)
        }).then(function (result) {
            // Handle server response (see Step 4)
            result.json().then(function (json) {
                handleServerResponse(json);
            })
        });
    }
}

function handleServerResponse(response) {
    if (response.error) {
        swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: response.error,
        }).then(function () {
            window.location.reload();
        });
        // Show error from server on payment form
    } else if (response.requires_action) {
        // Use Stripe.js to handle required card action
        if (response.hasOwnProperty('type') && response.type == 'subscription') {
            stripe.confirmCardPayment(response.payment_intent_client_secret, {
                payment_method: {
                    card: card
                },
            })
                .then(function () {
                    setTimeout(() => {
                        window.location.href = response.result;
                    }, 1500);
                });
        } else {
            stripe.handleCardAction(
                response.payment_intent_client_secret
            ).then(handleStripeJsResult);
        }
    } else {
        setTimeout(() => {
            window.location.href = response.result;
        }, 1500);
    }
}

function handleStripeJsResult(result) {
    if (result.error) {
        swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: result.error.message,
        }).then(function () {
            window.location.reload();
        });
        // Show error in payment form
    } else {
        paymentAjaxData.payment_intent_id = result.paymentIntent.id;

        // let total_amount = 0;
        // let tip = 0;
        // let cartElement = $("input[name='cart_total_payable_amount']");
        // let walletElement = $("input[name='wallet_amount']");
        // let subscriptionElement = $("input[name='subscription_amount']");
        // let tipElement = $("#cart_tip_amount");
        // let payment_form = '';
        // let payment_option_id = localStorage.getItem('payment_option');
        // let ajaxData = {
        //     payment_intent_id : result.paymentIntent.id,
        //     total_amount      : total_amount,
        //     payment_option_id : payment_option_id
        // };
        // if (path.indexOf("cart") !== -1) {
        //     payment_form = 'cart';
        //     total_amount = cartElement.val();
        //     // order_number = localStorage.getItem('order_number');
        //     ajaxData.address_id = 1;
        //     ajaxData.order_number = localStorage.getItem('order_number');
        // } else if ((path.indexOf("wallet") !== -1) || ((typeof cabbookingwallet !== 'undefined') && (cabbookingwallet == 1))) {
        //     payment_form = 'wallet';
        //     total_amount = walletElement.val();
        // } else if (path.indexOf("subscription") !== -1) {
        //     payment_form = 'subscription';
        //     total_amount = subscriptionElement.val();
        //     ajaxData = $("#subscription_payment_form").serializeArray();
        // } else if ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1)) {
        //     total_amount = walletElement.val();
        //     payment_form = 'tip';
        //     ajaxData.order_number = localStorage.getItem('order_number');
        // }
        // ajaxData.payment_form = payment_form;
        // ajaxData.total_amount = total_amount;

        // The card action has been handled
        // The PaymentIntent can be confirmed again on the server
        fetch('/payment/payment_init', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-Token": $('input[name="_token"]').val()
            },
            body: JSON.stringify(paymentAjaxData)
        }).then((response) => response.json())
            .then((responseJSON) => {
                $('#proceed_to_pay_loader').hide();
                window.location.href = responseJSON.result;
            });
    }

}

function paymentViaStripe(stripe_token, address_id, payment_option_id, delivery_type = 'D', order = '') {
    let total_amount = 0;
    let tip = 0;
    let cartElement = $("input[name='cart_total_payable_amount']");
    let walletElement = $("input[name='wallet_amount']");
    let subscriptionElement = $("input[name='subscription_amount']");
    let tipElement = $("#cart_tip_amount");
    let payment_form = '';

    let ajaxData = [];
    if (path.indexOf("cart") !== -1) {
        payment_form = 'cart';
        total_amount = cartElement.val();
        tip = tipElement.val();
        ajaxData.push(
            { name: 'tip', value: tip },
            { name: 'order_number', value: order.order_number }
        );
    } else if ((path.indexOf("wallet") !== -1) || ((typeof cabbookingwallet !== 'undefined') && (cabbookingwallet == 1))) {
        payment_form = 'wallet';
        total_amount = walletElement.val();
    } else if (path.indexOf("subscription") !== -1) {
        payment_form = 'subscription';
        total_amount = subscriptionElement.val();
        ajaxData = $("#subscription_payment_form").serializeArray();
    } else if ((typeof tip_for_past_order !== 'undefined') && (tip_for_past_order == 1)) {
        total_amount = walletElement.val();
        payment_form = 'tip';
        ajaxData.push(
            { name: 'order_number', value: $("#order_number").val() }
        );
    }
    ajaxData.push(
        { name: 'payment_form', value: payment_form },
        { name: 'stripe_token', value: stripe_token },
        { name: 'amount', value: total_amount },
        { name: 'payment_option_id', value: payment_option_id }
    );
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: payment_stripe_url,
        data: ajaxData,
        success: function (resp) {
            if (resp.status == 'Success') {
                if (path.indexOf("cart") !== -1) {
                    // placeOrder(address_id, payment_option_id, resp.data.id, tip,delivery_type);
                } else if (path.indexOf("wallet") !== -1) {
                    // creditWallet(total_amount, payment_option_id, resp.data.id);
                } else if (path.indexOf("subscription") !== -1) {
                    // userSubscriptionPurchase(total_amount, payment_option_id, resp.data.id);
                } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {

                    // let order_number = $("#order_number").val();
                    // if (order_number.length > 0) {
                    //     order_number = order_number;
                    // }
                    // creditTipAfterOrder(total_amount, payment_option_id, resp.data.id, order_number);
                } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                    // creditWallet(total_amount, payment_option_id, resp.data.id);
                }
                window.location.href = resp.data;
            } else {
                if (path.indexOf("cart") !== -1) {
                    success_error_alert('error', resp.message, ".payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (path.indexOf("wallet") !== -1) {
                    success_error_alert('error', resp.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                } else if (path.indexOf("subscription") !== -1) {
                    success_error_alert('error', resp.message, "#subscription_payment_form .payment_response");
                    $(".subscription_confirm_btn").removeAttr("disabled");
                } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
                    success_error_alert('error', resp.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                    success_error_alert('error', resp.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                }
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            if (path.indexOf("cart") !== -1) {
                success_error_alert('error', response.message, ".payment_response");
                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            } else if (path.indexOf("wallet") !== -1) {
                success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                $(".topup_wallet_confirm").removeAttr("disabled");
            } else if (path.indexOf("subscription") !== -1) {
                success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                $(".subscription_confirm_btn").removeAttr("disabled");
            }
        }
    });
}

function paymentViaPaypal() {
    let total_amount = 0;
    let tip = 0;
    let tipElement = $("#cart_tip_amount");
    let cartElement = $("input[name='cart_total_payable_amount']");
    let walletElement = $("input[name='wallet_amount']");
    let ajaxData = {};
    if (cartElement.length > 0) {
        total_amount = cartElement.val();
        tip = tipElement.val();
        ajaxData.tip = tip;
    } else if (walletElement.length > 0) {
        total_amount = walletElement.val();
    }

    ajaxData.amount = total_amount;
    ajaxData.returnUrl = path;
    ajaxData.cancelUrl = path;

    if (typeof tip_for_past_order !== 'undefined') {
        if (tip_for_past_order != undefined && tip_for_past_order == 1) {
            let order_number = $("#order_number").val();
            ajaxData.order_number = order_number;
            order_number = order_number;
        }
    }


    $.ajax({
        type: "POST",
        dataType: 'json',
        url: payment_paypal_url,
        data: ajaxData,
        success: function (response) {
            if (response.status == "Success") {
                window.location.href = response.data;
            } else {
                if (cartElement.length > 0) {
                    success_error_alert('error', response.message, ".payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (walletElement.length > 0) {
                    success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    $(".topup_wallet_confirm").removeAttr("disabled");
                }
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            if (cartElement.length > 0) {
                success_error_alert('error', response.message, ".payment_response");
                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            } else if (walletElement.length > 0) {
                success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                $(".topup_wallet_confirm").removeAttr("disabled");
            }
        }
    });
}

function paymentViaMastercard(paymentMehod, order) {
    const walletElement = $('input[name="wallet_amount"]');
    const cartElement = $("input[name='cart_total_payable_amount']");
    const subscriptionElement = $('input[name="subscription_amount"]');
    const subscriptionIdElement = $('#subscription_payment_form #subscription_id');

    let ajaxData = [{ name: 'payment_from', value: paymentMehod }];
    let total_amount;

    switch (paymentMehod) {
        case 'wallet':
            total_amount = walletElement.val();
            if (typeof tip_for_past_order !== 'undefined' && tip_for_past_order == 1) {
                ajaxData[0].value = 'tip';
                ajaxData.push({ name: 'order_number', value: $("#order_number").val() });
            }
            break;

        case 'cart':
            ajaxData.push({ name: 'order_number', value: order['order_number'] });
            total_amount = cartElement.val();
            break;
        case 'subscription':
            ajaxData.push({ name: 'subscription_id', value: subscriptionIdElement.val() });
            total_amount = subscriptionElement.val();
            break;

        case 'pickup_delivery':
            total_amount = order.total_amount;
            ajaxData.push({ name: 'order_number', value: order.order_number });
            break;
        default: throw new Error('unknown payment method');
    }

    ajaxData.push({ name: 'amount', value: total_amount });

    $.ajax({
        type: 'POST',
        dataType: 'json',
        data: ajaxData,
        url: mastercard_create_session_url,
        success({ session }) {
            const { id } = session;
            console.log(id);
            Checkout.configure({
                session: { id }
            })
            Checkout.showPaymentPage();
        },
    });
}

function paymentViaRazorpay_wallet(address_id, payment_option_id) {
    let walletElement = $("input[name='wallet_amount']");
    let subscriptionElement = $("input[name='subscription_amount']");
    let total_amount = 0;
    let ajaxData = [];
    if (path.indexOf("wallet") !== -1) {
        total_amount = walletElement.val();
        // ajaxData.payment_from = 'wallet';
        ajaxData.push({ name: 'payment_from', value: 'wallet' });
    } else if (path.indexOf("subscription") !== -1) {
        total_amount = subscriptionElement.val();
        ajaxData = $("#subscription_payment_form").serializeArray();
        ajaxData.push({ name: 'payment_from', value: 'subscription' });
    } else if ((tip_for_past_order != undefined) && (tip_for_past_order == 1)) {
        total_amount = walletElement.val();
        ajaxData.push(
            { name: 'payment_from', value: 'tip' },
            { name: 'order_number', value: $("#order_number").val() }
        );
    }
    ajaxData.push(
        { name: 'amount', value: total_amount },
        { name: 'returnUrl', value: path }
    );
    ajaxData.push({ name: 'payment_option_id', value: payment_option_id });

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: payment_razorpay_url,
        data: ajaxData,
        success: function (response) {
            if (response.status == "Success") {
                razorpay_options.amount = response.data.amount;
                razorpay_options.order_id = response.data.order_id;
                razorpay_options.currency = response.data.currency;
                $('#proceed_to_pay_modal').hide();
                razourPayView(response.data);
            }
        }
    });
}
//totalpay
function paymentViaTotalpay(address_id, payment_option_id, order) {
    let walletElement = $("input[name='wallet_amount']");
    let subscriptionElement = $("input[name='subscription_amount']");
    let total_amount = 0;
    let ajaxData = [];

    if (path.indexOf("wallet") !== -1) {
        total_amount = walletElement.val();
        ajaxData.push({ name: 'payment_from', value: 'wallet' });
    } else if (path.indexOf("cart") !== -1) {
        ajaxData.push({ name: 'payment_from', value: 'cart' },
            { name: 'order_number', value: order['order_number'] }
        );
    } else if (path.indexOf("subscription") !== -1) {
        total_amount = subscriptionElement.val();
        ajaxData = $("#subscription_payment_form").serializeArray();
        ajaxData.push({ name: 'payment_from', value: 'subscription' });
    } else if (typeof tip_for_past_order !== 'undefined' && tip_for_past_order == 1) {
        total_amount = walletElement.val();
        ajaxData.push(
            { name: 'payment_from', value: 'tip' },
            { name: 'order_number', value: $("#order_number").val() }
        );
    }

    ajaxData.push
        (
            { name: 'amount', value: total_amount },
            { name: 'returnUrl', value: path },
            { name: 'payment_option_id', value: payment_option_id }
        );
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: pyment_totalpay_url,
        data: ajaxData,
        success: function (response) {
            if (response.status == "Success") {
                let paymentUrl = response.payment_url;
                window.location.href = paymentUrl;
            }
        }
    });
}
//totalpay Ends

//hitpay Pg starts
function paymentViaHitpay(address_id, payment_option_id, order) {
    let walletElement = $("input[name='wallet_amount']");
    let subscriptionElement = $("input[name='subscription_amount']");
    let total_amount = 0;
    let tip = 0;
    let tipElement = $("#cart_tip_amount");
    let cartElement = $("input[name='cart_total_payable_amount']");
    let ajaxData = [];
    if (path.indexOf("cart") !== -1) {
        payment_form = 'cart';
        total_amount = cartElement.val();
        tip = tipElement.val();
        ajaxData.push(
            { name: 'tip', value: tip },
            { name: 'order_number', value: order.order_number },
            { name: 'payment_from', value: payment_form }
        );

    }
    else if (path.indexOf("wallet") !== -1) {
        total_amount = walletElement.val();
        ajaxData.push({ name: 'payment_from', value: 'wallet' });
    } else if (path.indexOf("cart") !== -1) {
        ajaxData.push({ name: 'payment_from', value: 'cart' },
            { name: 'order_number', value: order['order_number'] }
        );
    } else if (path.indexOf("subscription") !== -1) {
        payment_form = 'subscription';
        total_amount = subscriptionElement.val();
        ajaxData = $("#subscription_payment_form").serializeArray();

        ajaxData.push({ name: 'payment_from', value: payment_form });
        console.log(ajaxData);
    } else if (typeof tip_for_past_order !== 'undefined' && tip_for_past_order == 1) {
        total_amount = walletElement.val();
        ajaxData.push(
            { name: 'payment_from', value: 'tip' },
            { name: 'order_number', value: $("#order_number").val() }
        );
    }

    ajaxData.push
        (
            { name: 'amount', value: total_amount },
            { name: 'returnUrl', value: path },
            { name: 'payment_option_id', value: payment_option_id }
        );

    console.log(payment_hitpay_url);
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: payment_hitpay_url,
        data: ajaxData,
        success: function (response) {
            if (response.status == "Success") {
                console.log(response);
                let paymentUrl = response.payment_url;
                window.location.href = paymentUrl;
            }
        },
        error: function (xrh, error, h) {

            console.log(xrh, error, h);
        }

    });
}


//The Thawani Pg  starts
function paymentViaThawanipg(address_id, payment_option_id, order) {
    let walletElement = $("input[name='wallet_amount']");
    let subscriptionElement = $("input[name='subscription_amount']");
    let total_amount = 0;
    let ajaxData = [];

    if (path.indexOf("wallet") !== -1) {
        total_amount = walletElement.val();
        ajaxData.push({ name: 'payment_from', value: 'wallet' });
    } else if (path.indexOf("cart") !== -1) {
        total_amount = order['total_amount'];
        ajaxData.push({ name: 'payment_from', value: 'cart' },
            { name: 'order_number', value: order['order_number'] },
        );
    } else if (path.indexOf("subscription") !== -1) {
        total_amount = subscriptionElement.val();
        ajaxData = $("#subscription_payment_form").serializeArray();
        ajaxData.push({ name: 'payment_from', value: 'subscription' });
    } else if (typeof tip_for_past_order !== 'undefined' && tip_for_past_order == 1) {
        total_amount = walletElement.val();
        ajaxData.push(
            { name: 'payment_from', value: 'tip' },
            { name: 'order_number', value: $("#order_number").val() }
        );
    }

    ajaxData.push
        (
            { name: 'amount', value: total_amount },
            { name: 'returnUrl', value: path },
            { name: 'payment_option_id', value: payment_option_id }
        );
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: payment_thawani_url,
        data: ajaxData,
        success: function (response) {
            if (response.status == "Success") {
                let paymentUrl = response.payment_url;
                window.location.href = paymentUrl;
            }
        }
    });
}
//Thawani Pg Ends Here Ends
function paymentSuccessViaPaypal(amount, token, payer_id, path, tip = 0, order_number = 0) {
    let address_id = 0;
    var currentUrl = window.location.origin;
    var paypalCompletePurchaseUrl = currentUrl + "/payment/paypal/CompletePurchase";
    if (path.indexOf("cart") !== -1) {
        // $('#order_placed_btn').trigger('click');
        // $('#v-pills-paypal-tab').trigger('click');
        $("#order_placed_btn, .proceed_to_pay").attr("disabled", true);
        address_id = $("input:radio[name='address_id']:checked").val();
    } else if (path.indexOf("wallet") !== -1) {
        // $('#topup_wallet_btn').trigger('click');
        // $('#wallet_topup_form #radio-paypal').prop("checked", true);
        $("#topup_wallet_btn, .topup_wallet_confirm").attr("disabled", true);
    } else if (path.indexOf("orders") !== -1) {
        // $('#topup_wallet_btn').trigger('click');
        // $('#wallet_topup_form #radio-paypal').prop("checked", true);
        $("#topup_wallet_btn, .topup_wallet_confirm").attr("disabled", true);
    }
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: paypalCompletePurchaseUrl,
        data: { 'amount': amount, 'token': token, 'PayerID': payer_id },
        success: function (response) {
            if (response.status == "Success") {
                if (path.indexOf("details") !== -1) {
                    paypalDebitTransaction(amount, 3, response.data);
                } else if (path.indexOf("cart") !== -1) {
                    placeOrder(address_id, 3, response.data, tip);
                } else if (path.indexOf("wallet") !== -1) {
                    creditWallet(amount, 3, response.data);
                } else if (path.indexOf("orders") !== -1) {
                    creditTipAfterOrder(amount, 3, response.data, order_number);
                } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                    creditWallet(amount, 3, response.data);
                }
            } else {
                $('.spinner-overlay').hide();
                if (path.indexOf("cart") !== -1) {
                    // success_error_alert('error', response.message, "#paypal-payment-form .payment_response");
                    $(".cart_response").removeClass('d-none');
                    success_error_alert('error', response.message, ".cart_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                } else if (path.indexOf("wallet") !== -1) {
                    // success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    ("#wallet_response .message").removeClass('d-none');
                    success_error_alert('error', response.message, "#wallet_response .message");
                    $("#topup_wallet_btn, .topup_wallet_confirm").removeAttr("disabled");
                } else if ((cabbookingwallet != undefined) && (cabbookingwallet == 1)) {
                    // success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                    ("#wallet_response .message").removeClass('d-none');
                    success_error_alert('error', response.message, "#wallet_response .message");
                    $("#topup_wallet_btn, .topup_wallet_confirm").removeAttr("disabled");
                }
            }
        },
        error: function (error) {
            $('.spinner-overlay').hide();
            var response = $.parseJSON(error.responseText);
            if (path.indexOf("cart") !== -1) {
                // success_error_alert('error', response.message, "#paypal-payment-form .payment_response");
                $(".cart_response").removeClass('d-none');
                success_error_alert('error', response.message, ".cart_response");
                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            } else if (path.indexOf("wallet") !== -1) {
                // success_error_alert('error', response.message, "#wallet_topup_form .payment_response");
                ("#wallet_response .message").removeClass('d-none');
                success_error_alert('error', response.message, "#wallet_response .message");
                $("#topup_wallet_btn, .topup_wallet_confirm").removeAttr("disabled");
            }
        }
    });
}

window.placeOrder = function placeOrder(address_id = 0, payment_option_id, transaction_id = 0, tip = 0, delivery_type = 'D', other_taxes_string = '', total_amount = '') {
    var task_type = $("input[name='task_type']").val();
    var schedule_dt = $("#schedule_datetime").val();
    var slot = $("#slot").val();
    if (business_type == 'laundry' && scheduling_with_slots == 1) {
        var schedule_dropoff_slot = $("#schedule_dropoff_slot").val();
    } else {
        var schedule_dropoff_slot = null;
    }
    var is_gift = $('#is_gift:checked').val() ?? 0;
    var total_fixed_fee_amount = $("input[name='total_fixed_fee_amount']").val() ?? 0;

    if ((task_type == 'schedule') && (schedule_dt == '')) {
        $("#proceed_to_pay_modal").modal('hide');
        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
        success_error_alert('error', 'Schedule date time is required', ".cart_response");
        return false;
    }


    $.ajax({
        type: "POST",
        dataType: 'json',
        url: place_order_url,
        data: { address_id: address_id, payment_option_id: payment_option_id, transaction_id: transaction_id, tip: tip, task_type: task_type, schedule_dt: schedule_dt, is_gift: is_gift, delivery_type: delivery_type, slot: slot, total_fixed_fee_amount: total_fixed_fee_amount, other_taxes_string: other_taxes_string, schedule_dropoff_slot: schedule_dropoff_slot, is_postpay: post_pay_edit_order, total_amount: total_amount },

        success: function (response) {
            if (response.status == "Success") {
                setTimeout(function () {
                    window.location.href = `${base_url}/order/success/${response.data.id}`;
                }, 1000)
            } else {
                if ($(".cart_response").length > 0) {
                    $(".cart_response").removeClass("d-none");
                    success_error_alert(
                        "error",
                        response.message,
                        ".cart_response"
                    );
                    $("#order_placed_btn, .proceed_to_pay").removeAttr(
                        "disabled"
                    );
                }
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            // success_error_alert('error', response.message, ".payment_response");
            if ($('.cart_response').length > 0) {
                $(".cart_response").removeClass('d-none');
                success_error_alert('error', response.message, ".cart_response");
                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            }
            if (response.code == 404) {
                setTimeout(function () {
                    window.location.reload();
                }, 2000)
            }

        },
        complete: function (data) {
            $('.spinner-overlay').hide();
        }
    });
}

window.placeOrderBeforePayment = function placeOrderBeforePayment(address_id = 0, payment_option_id, tip = 0) {
    var task_type = $("input[name='task_type']").val();
    var schedule_dt = $("#schedule_datetime").val();
    var slot = $("#slot").val();
    var other_taxes_string = $("#other_taxes_string").val();
    var is_gift = $('#is_gift:checked').val() ?? 0;
    // place_order_url=domain+/user/
    if ((task_type == 'schedule') && (schedule_dt == '')) {
        $("#proceed_to_pay_modal").modal('hide');
        $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
        success_error_alert('error', 'Schedule date time is required', ".cart_response");
        return false;
    }
    var orderResponse = '';

    $.ajax({
        type: "POST",
        dataType: 'json',
        async: false,
        url: place_order_url,
        data: { address_id: address_id, payment_option_id: payment_option_id, tip: tip, task_type: task_type, schedule_dt: schedule_dt, is_gift: is_gift, slot: slot, other_taxes_string: other_taxes_string },
        success: function (response) {
            if (response.status == "Success") {
                orderResponse = response.data;
                // return orderResponse;
            } else {
                if ($(".payment_response").length > 0) {
                    $(".payment_response").removeClass("d-none");
                    success_error_alert("error", response.message, ".payment_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                }
            }
        },
        error: function (error) {

            var response = $.parseJSON(error.responseText);
            // success_error_alert('error', response.message, ".payment_response");
            if ($('.payment_response').length > 0) {
                $(".payment_response").removeClass('d-none');
                success_error_alert('error', response.message, ".payment_response");
                $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            }
        },
        complete: function (data) {
            $('.spinner-overlay').hide();
        }
    });
    return orderResponse;
}
$(document).on("click", ".proceed_to_pay", function () {
    let payment_option_id = $("#cart_payment_form input[name='cart_payment_method']:checked").val();
    if (payment_option_id == undefined) {
        success_error_alert('error', 'Please select payment option', ".payment_response");
        return false;
    }

    if (payment_option_id == 49) {
        cno = $('#plugnpay-card-element').val();
        dt = $('#plugnpay-date-element').val();
        cv = $('#plugnpay-cvv-element').val();
        if ((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == '')) {
            success_error_alert('error', 'Please Fill Details', "#plugnpay_card_error");
            return false;
        }
    }

    if (payment_option_id == 50) {
        cno = $('#azul-card-element').val();
        dt = $('#azul-date-element').val();
        cv = $('#azul-cvv-element').val();

        $("#azul_card_error").html();
        if (!creditCardValidation()) {
            if ((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == '')) {
                success_error_alert('error', 'Please Fill Details', "#azul_card_error");
                return false;
            }
        }
    }

    if (payment_option_id == 58) {
        var expData = $('#date-element-powertrans').val();
        var [expMonth, expYear] = [expData.slice(2), expData.slice(0, 2)]
        var newDate = expMonth + '/' + expYear;
        cardJson = {
            'cno': $('#card-element-powertrans').val(),
            'dt': newDate,
            'cv': $('#cvv-element-powertrans').val(),
            'name': 'powertrans',
        }
        if (cardValidation(cardJson)) {
            console.log('Credit card information is valid.');
        }
        else {
            success_error_alert('error', 'Invalid credit card information', "#powertrans_card_error");
            return false;
        }
    }

    $('#proceed_to_pay_loader').show();
    // startLoader('body',"{{getClientPreferenceDetail()->wb_color_rgb}}");
    $("#order_placed_btn, .proceed_to_pay").attr("disabled", true);
    var delivery_type = $("input:radio.delivery-fee:checked").attr('data-dcode');
    var other_taxes_string = '';
    if ($("#other_taxes_string").val() != null) {
        other_taxes_string = $("#other_taxes_string").val();
    }

    let address_id = $("input:radio[name='address_id']:checked").val();

    if ((vendor_type == 'delivery') && ((address_id == '') || (address_id < 1) || ($("input[name='address_id']").length < 1))) {
        success_error_alert('error', 'Please add a valid address to continue', ".payment_response");
        return false;
    }

    let tip = $("#cart_tip_amount").val();
    //let cartElement = $("input[name='cart_total_payable_amount']");
    let total_amount = $("input[name='cart_total_payable_amount']").val();
    // alert(total_amount);
    // return false;
    if (payment_option_id == 1 || payment_option_id == 38 || post_pay_edit_order == 1) {
        placeOrder(address_id, payment_option_id, '', tip, delivery_type, other_taxes_string, total_amount);
    } else {
        cartPaymentOptions(payment_option_id, address_id, tip, delivery_type);
    }
});



window.creditWallet = function creditWallet(amount, payment_option_id, transaction_id) {

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: credit_wallet_url,
        data: { wallet_amount: amount, payment_option_id: payment_option_id, transaction_id: transaction_id },
        success: function (response) {
            // var currentUrl = window.location.href;
            location.href = path;
            if (response.status == "Success") {
                // $("#topup_wallet").modal("hide");
                // $(".table.wallet-transactions table-body").html('');
                $(".wallet_balance").text(response.data.wallet_balance);
                success_error_alert('success', response.message, "#wallet_response");
                // let wallet_transactions_template = _.template($('#wallet_transactions_template').html());
                // $(".table.wallet-transactions table-body").append(wallet_transactions_template({wallet_transactions:response.data.transactions}));
            } else {
                $("#wallet_response .message").removeClass('d-none');
                success_error_alert('error', response.message, "#wallet_response .message");
                $("#topup_wallet_btn, .topup_wallet_confirm").attr("disabled", false);
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            $("#wallet_response .message").removeClass('d-none');
            success_error_alert('error', response.message, "#wallet_response .message");
            $("#topup_wallet_btn, .topup_wallet_confirm").removeAttr("disabled");
        },
        complete: function (data) {
            $('.spinner-overlay').hide();
        }
    });
}

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    let queryString = window.location.search;
    let path = window.location.pathname;
    let urlParams = new URLSearchParams(queryString);
    // if ((urlParams.get('gateway') == 'paylink') && urlParams.has('checkout')) {
    //     $('.spinner-overlay').show();

    //     if (urlParams.has('checkout')) {

    //         transaction_id = urlParams.get('checkout');
    //     }
    //     if (urlParams.has('amount')) {

    //         total_amount = urlParams.get('amount');
    //     }

    //     creditWallet(urlParams.get('amount'), 9, urlParams.get('checkout'));
    // }
    if ((urlParams.get('gateway') == 'razorpay') && urlParams.has('checkout')) {
        $('.spinner-overlay').show();

        if (urlParams.has('checkout')) {

            transaction_id = urlParams.get('checkout');
        }
        if (urlParams.has('amount')) {

            total_amount = urlParams.get('amount');
        }

        creditWallet(urlParams.get('amount') / 10000, 9, urlParams.get('checkout'));
    }
});


window.userSubscriptionPurchase = function userSubscriptionPurchase(amount, payment_option_id, transaction_id) {
    var id = $("#subscription_payment_form #subscription_id").val();
    if (id != '') {
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: user_subscription_purchase_url.replace(":id", id),
            data: { amount: amount, payment_option_id: payment_option_id, transaction_id: transaction_id },
            success: function (response) {
                if (response.status == "Success") {
                    location.href = path;
                } else {
                    success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                    $(".subscription_confirm_btn").attr("disabled", false);
                }
            },
            error: function (error) {
                var response = $.parseJSON(error.responseText);
                success_error_alert('error', response.message, "#subscription_payment_form .payment_response");
                $(".subscription_confirm_btn").removeAttr("disabled");
            }
        });
    } else {
        success_error_alert('error', 'Invalid data', "#wallet_topup_form .payment_response");
        $(".topup_wallet_confirm").removeAttr("disabled");
    }
}

$(document).on("click", ".topup_wallet_confirm", function () {
    var wallet_amount = $('#wallet_amount').val();
    let payment_option_id = $('#wallet_payment_methods input[name="wallet_payment_method"]:checked').data('payment_option_id');
    if ((wallet_amount == undefined || wallet_amount <= 0) && (amount_required_error_msg != undefined)) {
        $('#wallet_amount_error').html(amount_required_error_msg);
        return false;
    } else {
        $('#wallet_amount_error').html('');
    }

    if (payment_option_id == 49) {
        cno = $('#plugnpay-card-element').val();
        dt = $('#plugnpay-date-element').val();
        cv = $('#plugnpay-cvv-element').val();
        if ((cno == undefined || dt == undefined || cv == undefined) || (cno == '' || dt == '' || cv == '')) {
            success_error_alert('error', 'Please Fill Details', ".payment_response");
            return false;
        }
    }

    if (payment_option_id == 58) {
        var expData = $('#date-element-powertrans').val();
        var [expMonth, expYear] = [expData.slice(2), expData.slice(0, 2)]
        var newDate = expMonth + '/' + expYear;
        cardJson = {
            'cno': $('#card-element-powertrans').val(),
            'dt': newDate,
            'cv': $('#cvv-element-powertrans').val(),
            'name': 'powertrans',
        }
        if (cardValidation(cardJson)) {
            console.log('Credit card information is valid.');
        }
        else {
            success_error_alert('error', 'Invalid credit card information', "#powertrans_card_error");
            return false;
        }

    }

    if ((payment_option_id == undefined || payment_option_id <= 0) && (payment_method_required_error_msg != undefined)) {
        $('#wallet_payment_methods_error').html(payment_method_required_error_msg);
        return false;
    } else {
        $('#wallet_payment_methods_error').html('');
    }
    $(".topup_wallet_confirm").attr("disabled", true);
    // $('#topup_wallet').modal('hide');
    walletPaymentOPtions(payment_option_id);

});

$(document).on("click", ".remove_promo_code_btn", function () {
    let cart_id = $(this).data('cart_id');
    let coupon_id = $(this).data('coupon_id');
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: promo_code_remove_url,
        data: { coupon_id: coupon_id, cart_id: cart_id },
        success: function (response) {
            if (response.status == "Success") {
                cartHeader();
            }
        }
    });
});
$(document).on("click", ".promo_code_list_btn", function () {
    let cart_product_ids = $("input[name='cart_product_ids[]']").map(function () { return $(this).val(); }).get();;
    let amount = $(this).attr('data-amount');
    let cart_id = $(this).attr('data-cart_id');
    let vendor_id = $(this).attr('data-vendor_id');
    $(".invalid-feedback.manual_promocode").html("");
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: promocode_list_url,
        data: { vendor_id: vendor_id, amount: amount, cart_id: cart_id, cart_product_ids: cart_product_ids },
        success: function (response) {
            $("#promo_code_list_main_div").html('');
            $(document).find('.manual_promocode_input').val("");
            if (response.status == "Success") {
                $('.validate_promo_code_btn').attr('data-vendor_id', vendor_id);
                $('.validate_promo_code_btn').attr('data-cart_id', cart_id);
                $('.validate_promo_code_btn').attr('data-amount', amount);
                $('#refferal-modal').modal('show');
                if (response.data.length != 0) {
                    let promo_code_template = _.template($('#promo_code_template').html());
                    $("#promo_code_list_main_div").append(promo_code_template({ promo_codes: response.data, vendor_id: vendor_id, cart_id: cart_id, amount: amount }));
                } else {
                    // let no_promo_code_template = _.template($('#no_promo_code_template').html());
                    // $("#promo_code_list_main_div").append(no_promo_code_template());
                }
            }
        }
    });
});
$(document).on("click", ".apply_promo_code_btn", function () {
    let amount = $(this).data('amount');
    let cart_id = $(this).data('cart_id');
    let vendor_id = $(this).data('vendor_id');
    let coupon_id = $(this).data('coupon_id');
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: apply_promocode_coupon_url,
        data: { cart_id: cart_id, vendor_id: vendor_id, coupon_id: coupon_id, amount: amount },
        success: function (response) {
            if (response.status == "Success") {
                $('#refferal-modal').modal('hide');
                cartHeader();
            }
        },
        error: function (reject) {
            if (reject.status === 422) {
                var message = $.parseJSON(reject.responseText);
                //alert(message.message);
                Swal.fire({
                    // title: "Warning!",
                    text: message.message,
                    icon: "error",
                    button: "OK",
                });
            }
        }
    });
});
$(document).on("click", ".remove-product", function () {
    let vendor_id = $(this).data('vendor_id');
    let cartproduct_id = $(this).data('product');
    productRemove(0, cartproduct_id, vendor_id);
});
$(document).on("click", ".remove_product_via_cart", function () {
    $('#remove_item_modal').modal('show');
    let vendor_id = $(this).data('vendor_id');
    let cartproduct_id = $(this).data('product');
    let product_id = $(this).data('product_id');
    $('#remove_item_modal #product_id').val(product_id);
    $('#remove_item_modal #vendor_id').val(vendor_id);
    $('#remove_item_modal #cartproduct_id').val(cartproduct_id);
});
$(document).on("click", "#remove_product_button", function () {
    $('#remove_item_modal').modal('hide');
    let vendor_id = $('#remove_item_modal #vendor_id').val();
    let product_id = $('#remove_item_modal #product_id').val();
    let cartproduct_id = $('#remove_item_modal #cartproduct_id').val();
    productRemove(product_id, cartproduct_id, vendor_id);
});

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function initialize() {
    var input = document.getElementById('address');
    if (input) {
        var autocomplete = new google.maps.places.Autocomplete(input);
        if (is_map_search_perticular_country) {
            autocomplete.setComponentRestrictions({ 'country': [is_map_search_perticular_country] });
        }
        autocomplete.bindTo('bounds', bindMap);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            // document.getElementById('city').value = place.name;
            document.getElementById('longitude').value = place.geometry.location.lng();
            document.getElementById('latitude').value = place.geometry.location.lat();
            for (let i = 1; i < place.address_components.length; i++) {
                let mapAddress = place.address_components[i];
                if (mapAddress.long_name != '') {
                    let streetAddress = '';
                    if (mapAddress.types[0] == "street_number") {
                        streetAddress += mapAddress.long_name;
                    }
                    if (mapAddress.types[0] == "route") {
                        streetAddress += mapAddress.short_name;
                    }
                    if ($('#street').length > 0) {
                        document.getElementById('street').value = streetAddress;
                    }
                    if (mapAddress.types[0] == "locality") {
                        document.getElementById('city').value = mapAddress.long_name;
                    }
                    if (mapAddress.types[0] == "administrative_area_level_1") {
                        document.getElementById('state').value = mapAddress.long_name;
                        document.getElementById('state_code').value = mapAddress.short_name;
                    }
                    if (mapAddress.types[0] == "postal_code") {
                        document.getElementById('pincode').value = mapAddress.long_name;
                    } else {
                        document.getElementById('pincode').value = '';
                    }
                    if (mapAddress.types[0] == "country") {
                        var country = document.getElementById('country');
                        if (typeof country.options != "undefined") {
                            for (let i = 0; i < country.options.length; i++) {
                                if (country.options[i].text.toUpperCase() == mapAddress.long_name.toUpperCase()) {
                                    country.value = country.options[i].value;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        });
        setTimeout(function () {
            $(".pac-container").appendTo("#add_new_address_form .address-input-group");
        }, 300);
    }
}
// initialize();
// google.maps.event.addDomListener(window, 'load', initialize);
function cartTotalProductCount() {
    let cart_qty_total = 0;
    $(".shopping-cart li").each(function (index) {
        if ($(this).data('qty')) {
            cart_qty_total += $(this).data('qty');

        }
    });
    if (cart_qty_total > 0) {
        $("#cart_qty_span").addClass("bg-cart-header");
        $('#cart_qty_span, .cart_qty_cls').html(cart_qty_total).show();
    } else {
        $("#cart_qty_span").removeClass("bg-cart-header");
        $('#cart_qty_span, .cart_qty_cls').html(cart_qty_total).hide();
    }
}

function displayMapLocation(latitude, longitude, elementID) {

    // Commented By Sujata
    var geocoder;
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(latitude, longitude);

    const map = new google.maps.Map(document.getElementById(elementID), {
        center: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
        zoom: 13
    });

    const marker = new google.maps.Marker({
        map: map,
        position: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
    });
    // End Comment


    // geocoder.geocode(
    //     { 'latLng': latlng },
    //     function (results, status) {
    //         if (status == google.maps.GeocoderStatus.OK) {
    //         }
    //         else {
    //             $("#address-input").val("Geocoder failed due to: " + status);
    //         }
    //     }
    // );
}
function checkIfInCart(v_p) {
    v_p.vendor_products.map((data) => {
        if (data.pvariant.id == $('#prod_variant_id').val()) {
            localStorage.setItem('in_cart', 'true');
        }
    })
}
function cartHeader(address_id = null) {
    $(".shopping-cart").html("");
    $(".spinner-box").show();
    OrderStorage.setStorageSingle('cartData', []);
    OrderStorage.setStorageSingle('cartProductCount', 0);
    OrderStorage.setStorageSingle('LongTermServiceAdded', '');
    OrderStorage.setStorageSingle('RecurringBookingAdded', '');
    OrderStorage.setStorageSingle('cartFirstProductId', '');
    OrderStorage.setStorageSingle('cartAddressId', '');
    $.ajax({
        data: { address_id: address_id, schedule_date_delivery: $("#schedule_datetime").val() },
        type: "get",
        dataType: 'json',
        url: cart_product_url,
        success: function (response) {
            if (response.status == "success") {
                if (response.wishListCount > 0) {
                    $('.wishListCount').removeClass('fa-heart-o');
                    $('.wishListCount').addClass('fa-heart');
                } else {
                    $('.wishListCount').removeClass('fa-heart');
                    $('.wishListCount').addClass('fa-heart-o');
                }
                $("#cart_table").html('');
                $(".spinner-box").hide();
                $("#mycart").html(response.mycart);


                // if(response.loggedIn ==  "true") {
                //     $('.onhover-show-div').html(`<li>
                //         <a href="/client/dashboard" data-lng="en">Control Panel</a>
                //     </li>
                //     <li>
                //         <a href="/user/profile" data-lng="en">Profile</a>
                //     </li>
                //     <li>
                //         <a href="/user/logout" data-lng="es">Logout</a>
                //     </li>`);
                // } else{
                //     $('.onhover-show-div').html( `<li>
                //         <a href="/user/login" data-lng="en">Login</a>
                //     </li>
                //     <li>
                //         <a href="/user/register" data-lng="es">Register</a>
                //     </li>`);
                // }
                //return true;
                var cart_details = response.cart_details;
                var client_preference_detail = response.client_preference_detail;
                var is_token_enable = response.is_token_enable;
                var token_val = response.token_val;
                if (cart_details != undefined) {
                    // if((response.is_token_enable == 1) && (response.token_val > 0) ){
                    //     response.token_val;
                    // }
                    OrderStorage.setStorageSingle('cartData', JSON.stringify(cart_details));
                    if (cart_details.products.length > 0) {
                        if (cart_details.address_id != '') {
                            $(`input:radio[name=address_id][value=${cart_details.address_id}]`).prop('checked', true);
                        }
                        OrderStorage.setStorageSingle('cartAddressId', cart_details.address_id);
                        OrderStorage.setStorageSingle('cartProductCount', cart_details.products.length);
                        OrderStorage.setStorageSingle('cartFirstProductId', cart_details.products[0].product_id);
                        OrderStorage.setStorageSingle('LongTermServiceAdded', cart_details.products[0].is_long_term_service);
                        OrderStorage.setStorageSingle('RecurringBookingAdded', cart_details.products[0].is_recurring_booking);
                        OrderSessionStorage.setStorageSingle('dispatcher_agent_id', cart_details.products[0].dispatch_agent_id)
                        //map array  cart_details.products.map(checkIfInCart);
                        var headerCartData = _.extend({ Helper: NumberFormatHelper }, { cart_details: cart_details, show_cart_url: show_cart_url, client_preference_detail: client_preference_detail, is_token_enable: is_token_enable, token_val: token_val });

                        let header_cart_template = _.template($('#header_cart_template').html());

                        $("#header_cart_main_ul").append(header_cart_template(headerCartData));
                        if (response.cart_details.totalQuantity > 0) {
                            $('#expected_vendors').html('');
                            $('#expected_vendors').html(response.expected_vendor_html);

                            if (vendor_type != 'delivery' && vendor_type != 'on_demand') {
                                var latitude = $('#latitude').val();
                                var longitude = $('#longitude').val();
                                if ($('#vendor-address-map').length > 0) {
                                    displayMapLocation(latitude, longitude, 'vendor-address-map');
                                }
                            }
                            //initialize();
                            if (cart_details.deliver_status == 0) {
                                // $("#order_placed_btn").attr("disabled", true);
                                // $("#order_placed_btn").addClass("d-none");
                            } else {

                                $("#order_placed_btn").removeAttr("disabled");
                                $("#order_placed_btn").removeClass("d-none");
                            }
                            //if(response.schedule_datetime!=null){
                            var schedule_datetime = '';
                            if (typeof $("#edit_order_schedule_datetime").val() != 'undefined' && $("#edit_order_schedule_datetime").val() != '' && typeof $('#edit_order_schedule_slot').val() != 'undefined' && $('#edit_order_schedule_slot').val() != '') {

                                var edit_order_schedule_datetime = $("#edit_order_schedule_datetime").val();
                                schedule_datetime = edit_order_schedule_datetime.split(" ")[0];
                            } else {
                                schedule_datetime = $("#edit_order_schedule_datetime").val();
                            }

                            if (schedule_datetime != '' && schedule_datetime != undefined && typeof $("#schedule_datetime").val() != 'undefined') {

                                $("#schedule_datetime").val(schedule_datetime);
                                $("#schedule_datetime").attr("value", $("#schedule_datetime").val());
                                $("#schedule_datetime").attr("max", $("#schedule_datetime").val());
                                $("#schedule_datetime").attr("min", $("#schedule_datetime").val());
                                $("#edit_order_schedule_datetime").val($("#schedule_datetime").val());
                                if (typeof $('#slot').val() != 'undefined' && typeof $('#edit_order_schedule_slot').val() != 'undefined') {
                                    $(".schedule_datetime").change();
                                    $('#slot').val($('#edit_order_schedule_slot').val());
                                }
                                $("#taskschedule").trigger('click');
                            }
                            if (response.cart_error_message != '') {
                                success_error_alert('error', response.cart_error_message, ".cart_response");
                                $("#order_placed_btn").attr("disabled", true);
                                $("#order_placed_btn").addClass("d-none");
                            }
                            //}

                        }
                        cartTotalProductCount();
                        if ($("#header_cart_template_ondemand").length != 0) {
                            $("#header_cart_main_ul_ondemand").html('');
                            let header_cart_template_ondemand = _.template($('#header_cart_template_ondemand').html());
                            var CartTemplateOndemandData = _.extend({ Helper: NumberFormatHelper }, { cart_details: cart_details, show_cart_url: show_cart_url });
                            $("#header_cart_main_ul_ondemand").removeClass('d-none');

                            $("#header_cart_main_ul_ondemand").html(header_cart_template_ondemand(CartTemplateOndemandData));
                            $("#next-button-ondemand-2").show();
                            $('#placeorder_form_ondemand .left_box').html('');
                            $('#placeorder_form_ondemand .left_box').html(cart_details.left_section);
                            initialize();
                            if (cart_details.deliver_status == 0) {

                                $("#order_placed_btn").attr("disabled", true);
                                $("#order_placed_btn").addClass("d-none");
                            } else {

                                $("#order_placed_btn").removeAttr("disabled");
                                $("#order_placed_btn").removeClass("d-none");
                            }
                        }

                    } else {
                        if ($('#cart_main_page').length != 0) {
                            $('#cart_main_page').html('');
                            let empty_cart_template = _.template($('#empty_cart_template').html());
                            $("#cart_main_page").append(empty_cart_template());
                        }
                        if ($('.categories-product-list').length > 0) {
                            $('#header_cart_main_ul_ondemand').html('');
                            let empty_cart_template = _.template($('#empty_cart_template').html());
                            $("#header_cart_main_ul_ondemand").append(empty_cart_template());
                        }
                        if ($("#header_cart_template_ondemand").length != 0) {
                            $('#header_cart_main_ul_ondemand').html('');
                            $("#header_cart_main_ul_ondemand").removeClass("d-none");
                            $("#header_cart_main_ul_ondemand").show();
                            let empty_cart_template = _.template($('#empty_cart_template').html());
                            $("#header_cart_main_ul_ondemand").append(empty_cart_template());
                        }
                    }
                } else {
                    if ($('#cart_main_page').length != 0) {
                        $('#cart_main_page').html('');
                        let empty_cart_template = _.template($('#empty_cart_template').html());
                        $("#cart_main_page").append(empty_cart_template());
                    }
                    if ($('.categories-product-list').length > 0) {
                        $('#header_cart_main_ul_ondemand').html('');
                        let empty_cart_template = _.template($('#empty_cart_template').html());
                        $("#header_cart_main_ul_ondemand").append(empty_cart_template());
                    }
                    if ($("#header_cart_template_ondemand").length != 0) {
                        $('#header_cart_main_ul_ondemand').html('');
                        let empty_cart_template = _.template($('#empty_cart_template').html());
                        $("#header_cart_main_ul_ondemand").append(empty_cart_template());
                    }
                    if ($("#header_cart_main_ul_ondemand").length != 0) {

                        $('#header_cart_main_ul_ondemand').html('');
                        $("#header_cart_main_ul_ondemand").removeClass("d-none");
                        $("#header_cart_main_ul_ondemand").show();
                        let empty_cart_template = _.template($('#empty_cart_template').html());
                        $("#header_cart_main_ul_ondemand").append(empty_cart_template());
                    }

                }

                $.each($('.vendor_schedule_slot'), function () {
                    if ($(this).val() != '') {
                        $responst = checkSlotAvailability(this);
                    }
                });
            }
        },
        complete: function (data) {
            if ($("#cart_table").length > 0) {
                $(".spinner-box").hide();
                $("#cart_table").show();
            }

            if ($("#header_cart_main_ul_ondemand").length > 0) {
                $(".spinner-box").hide();
                $("#header_cart_main_ul_ondemand").show();
                $(".number .qty-minus-ondemand .fa").removeAttr("class").addClass("fa fa-minus");
                $(".number .qty-plus-ondemand .fa").removeAttr("class").addClass("fa fa-plus");
            }

            if ($(".number .fa-spinner fa-pulse").length > 0) {
                $(".number .qty-minus .fa").removeAttr("class").addClass("fa fa-minus");
                $(".number .qty-plus .fa").removeAttr("class").addClass("fa fa-plus");

            }
        }
    });
}



function cartHeaderDilivery(address_id, code) {
    $(".shopping-cart").html("");
    $(".spinner-box").show();
    $.ajax({
        data: { address_id: address_id, code: code },
        type: "get",
        dataType: 'json',
        url: cart_product_url,
        success: function (response) {
            if (response.status == "success") {
                $("#cart_table").html('');
                $(".spinner-box").hide();
                var cart_details = response.cart_details;
                var client_preference_detail = response.client_preference_detail;
                if (response.cart_details.length != 0) {
                    if (response.cart_details.products.length != 0) {

                        var headerCartData = _.extend({ Helper: NumberFormatHelper }, { cart_details: cart_details, show_cart_url: show_cart_url, client_preference_detail: client_preference_detail });

                        let header_cart_template = _.template($('#header_cart_template').html());
                        $("#header_cart_main_ul").append(header_cart_template(headerCartData));
                        if ($('#cart_main_page').length != 0) {

                            // simplified mock of the helpers

                            var extendedData = _.extend({ Helper: NumberFormatHelper }, { cart_details: cart_details, client_preference_detail: client_preference_detail });

                            let cart_template = _.template($('#cart_template').html());
                            $("#cart_table").append(cart_template(extendedData));
                            $(".other_cart_products").html('');
                            let other_cart_products_template = _.template($('#other_cart_products_template').html());
                            $(".other_cart_products").append(other_cart_products_template(extendedData));
                            initializeSlider();
                            $('#placeorder_form .left_box').html('');
                            $('#placeorder_form .left_box').html(cart_details.left_section);
                            if (vendor_type != 'delivery') {
                                var latitude = $('#latitude').val();
                                var longitude = $('#longitude').val();
                                displayMapLocation(latitude, longitude, 'vendor-address-map');
                            }
                            initialize();
                            if (cart_details.deliver_status == 0) {
                                $("#order_placed_btn").attr("disabled", true);
                                $("#order_placed_btn").addClass("d-none");
                            } else {
                                $("#order_placed_btn").removeAttr("disabled");
                                $("#order_placed_btn").removeClass("d-none");
                            }
                        }
                        cartTotalProductCount();

                        if ($("#header_cart_template_ondemand").length != 0) {
                            $("#header_cart_main_ul_ondemand").html('');

                            let header_cart_template_ondemand = _.template($('#header_cart_template_ondemand').html());
                            var CartTemplateOndemandData = _.extend({ Helper: NumberFormatHelper }, { cart_details: cart_details, show_cart_url: show_cart_url });

                            $("#header_cart_main_ul_ondemand").append(header_cart_template_ondemand(CartTemplateOndemandData));
                            $("#next-button-ondemand-2").show();
                            $('#placeorder_form_ondemand .left_box').html('');
                            $('#placeorder_form_ondemand .left_box').html(cart_details.left_section);
                            initialize();
                            if (cart_details.deliver_status == 0) {
                                $("#order_placed_btn").attr("disabled", true);
                                $("#order_placed_btn").addClass("d-none");
                            } else {
                                $("#order_placed_btn").removeAttr("disabled");
                                $("#order_placed_btn").removeClass("d-none");
                            }
                        }

                    } else {
                        if ($('#cart_main_page').length != 0) {
                            $('#cart_main_page').html('');
                            let empty_cart_template = _.template($('#empty_cart_template').html());
                            $("#cart_main_page").append(empty_cart_template());
                        }
                        if ($('.categories-product-list').length > 0) {
                            $('#header_cart_main_ul_ondemand').html('');
                            let empty_cart_template = _.template($('#empty_cart_template').html());
                            $("#header_cart_main_ul_ondemand").append(empty_cart_template());
                        }
                    }
                } else {
                    if ($('#cart_main_page').length != 0) {
                        $('#cart_main_page').html('');
                        let empty_cart_template = _.template($('#empty_cart_template').html());
                        $("#cart_main_page").append(empty_cart_template());
                    }
                    if ($('.categories-product-list').length > 0) {
                        $('#header_cart_main_ul_ondemand').html('');
                        let empty_cart_template = _.template($('#empty_cart_template').html());
                        $("#header_cart_main_ul_ondemand").append(empty_cart_template());
                    }
                }
            }
        },
        complete: function (data) {
            if ($("#cart_table").length > 0) {
                $(".spinner-box").hide();
                $("#cart_table").show();
            }

            if ($("#header_cart_main_ul_ondemand").length > 0) {
                $(".spinner-box").hide();
                $("#header_cart_main_ul_ondemand").show();
                $(".number .qty-minus-ondemand .fa").removeAttr("class").addClass("fa fa-minus");
                $(".number .qty-plus-ondemand .fa").removeAttr("class").addClass("fa fa-plus");
            }

            if ($(".number .fa-spinner fa-pulse").length > 0) {
                $(".number .qty-minus .fa").removeAttr("class").addClass("fa fa-minus");
                $(".number .qty-plus .fa").removeAttr("class").addClass("fa fa-plus");

            }
        }
    });
}

function updateQuantity(cartproduct_id, quantity, base_price, iconElem = '') {
    if (iconElem != '') {
        let elemClasses = $(iconElem).attr("class");
        $(iconElem).removeAttr("class").addClass("fa fa-spinner fa-pulse");
    }

    ajaxCall = $.ajax({
        type: "post",
        dataType: "json",
        url: update_qty_url,
        data: { "quantity": quantity, "cartproduct_id": cartproduct_id },
        success: function (response) {
            if (response.status == "error") {
                Swal.fire({
                    title: "Warning!",
                    text: response.message,
                    icon: "warning",
                    button: "OK",
                });
                if ($(".fa-spinner.fa-pulse").length > 0) {
                    $(".qty-minus .fa").removeAttr("class").addClass("fa fa-minus");
                    $(".qty-plus .fa").removeAttr("class").addClass("fa fa-plus");
                }
                $('#quantity_' + cartproduct_id).val(response.quantity);

            } else {
                var latest_price = parseInt(base_price) * parseInt(quantity);
                $('#product_total_amount_' + cartproduct_id).html('$' + latest_price);
                // return false;
                cartHeader();
            }
        },
        error: function (err) {
            if ($(".number .fa-spinner fa-pulse").length > 0) {
                $(".number .qty-minus .fa").removeAttr("class").addClass("fa fa-minus");
                $(".number .qty-plus .fa").removeAttr("class").addClass("fa fa-plus");
            }

        }
    });
}

function updateCartProductStatus(cartproduct_id, is_cart_checked) {
    ajaxCall = $.ajax({
        type: "post",
        dataType: "json",
        url: update_cart_product_status,
        data: { "cartproduct_id": cartproduct_id, "is_cart_checked": is_cart_checked },
        success: function (response) {
            if (response.status == "error") {
                Swal.fire({
                    title: "Warning!",
                    text: response.message,
                    icon: "warning",
                    button: "OK",
                });
            } else {
                cartHeader();
            }
        },
        error: function (err) { }
    });
}

$(document).on('click', '.tip_radio_controls .tip_radio', function () {
    var tip = $(this).val();
    var amount_payable = parseFloat($("#cart_payable_amount_original").val());
    var currency = $("#cart_payable_amount_original").attr('data-curr');
    // if this was previously checked
    if ($(this).hasClass("active")) {
        $(this).prop('checked', false);
        $(this).removeClass('active');
        setTipAmount(0, amount_payable, currency);
    } else {
        $('.tip_radio_controls .tip_radio').removeClass("active");
        $(this).addClass('active');
        setTipAmount(tip, amount_payable, currency);
    }

});

function setTipAmount(tip, amount_payable, currency) {
    var fixed_fee_amount = $("#fixed_fee_amount").val();
    if ((fixed_fee_amount == '') || (isNaN(fixed_fee_amount))) {
        fixed_fee_amount = 0;
    }
    // var other_taxes=parseFloat($('#other_taxes').text());
    var other_taxes = 0;
    if (tip != 'custom') {
        if ((tip == '') || (isNaN(tip))) {
            tip = 0;
        }
        var wallet_amount_used_fixed = parseFloat($('#wallet_amount_used_fixed').text());


        if (isNaN(wallet_amount_used_fixed) || wallet_amount_used_fixed == null) {
            wallet_amount_used_fixed = 0;
        }
        amount_payable = parseFloat(amount_payable) + parseFloat(tip) + other_taxes;
        $("#cart_tip_amount").val(parseFloat(tip).toFixed(parseInt(digit_count)));
        $("#cart_total_payable_amount").html(currency + parseFloat(amount_payable).toFixed(parseInt(digit_count)));
        $("input[name='cart_total_payable_amount']").val(parseFloat(amount_payable).toFixed(parseInt(digit_count)));
        $(".custom_tip").addClass("d-none");
        $("#custom_tip_amount").val('');
        if (parseFloat(amount_payable) + wallet_amount_used_fixed >= parseFloat($('#mov').text())) {
            // $("#order_placed_btn").removeAttr("disabled");
            //  $("#order_placed_btn").removeClass("d-none");
            $("#MOV_Notification").addClass("d-none");
        } else {
            // $("#order_placed_btn").attr("disabled", true);
            //  $("#order_placed_btn").addClass("d-none");
            $("#MOV_Notification").removeClass("d-none");
        }
    } else {
        // amount_payable = parseFloat(amount_payable) +parseFloat(fixed_fee_amount);
        $("#cart_total_payable_amount").text(currency + parseFloat(amount_payable + other_taxes).toFixed(parseInt(digit_count)));
        $("#cart_tip_amount").val(0);
        $(".custom_tip").removeClass("d-none");
        $("#custom_tip_amount").focus();
        $("input[name='cart_total_payable_amount']").val(parseFloat(amount_payable + other_taxes).toFixed(parseInt(digit_count)));
    }

    //$("input[name='cart_total_payable_amount']").val(parseFloat(amount_payable).toFixed(parseInt(digit_count)));
}

function initialize_values(value) {
    value = value.replace(',', '');
    if ((value == '') || (isNaN(value))) {
        value = 0;
    }
    return parseFloat(value);
}
$(document).on('keyup', '#custom_tip_amount', function () {
    // var other_taxes                 =initialize_values($('#other_taxes').text());
    var other_taxes = 0;
    var loyalty_amount = initialize_values($('#loyalty_amount').text());
    var token_currency = initialize_values($('#token_currency').text());
    var wallet_amount_available = initialize_values($('#wallet_amount_available').text());
    var wallet_amount_used_fixed = initialize_values($('#wallet_amount_used_fixed').text());
    var gross_amount = initialize_values($('#gross_amount').text());
    var total_taxable_amount = initialize_values($('#total_taxable_amount').text());
    var total_subscription_discount = initialize_values($('#total_subscription_discount').text());
    //var fixed_fee_amount            =initialize_values($('#fixed_fee_amount').val());
    var tip = initialize_values($(this).val());




    var amount_elem = $("#cart_payable_amount_original");
    var currency = amount_elem.attr('data-curr');
    var amount_payable = initialize_values(amount_elem.val());

    var payable_amount = 0;

    // if (!tip) {
    //     alert(amount_payable);
    //     $("#cart_total_payable_amount").html( currency +   amount_payable.toFixed(parseInt(digit_count)));
    //     $("input[name='cart_total_payable_amount']").val(  amount_payable.toFixed(parseInt(digit_count)));
    // }

    $("#cart_tip_amount").val(tip.toFixed(parseInt(digit_count)));
    // $("#cart_total_payable_amount").html(currency + (amount_payable+other_taxes).toFixed(parseInt(digit_count)));
    // $("input[name='cart_total_payable_amount']").val((amount_payable+other_taxes).toFixed(parseInt(digit_count)));
    if (token_currency > 0) {
        currency = '';
        $("#cart_tip_amount").val(tip / token_currency);
    }
    if (wallet_amount_available > 0) {
        if (wallet_amount_available >= wallet_amount_used_fixed + tip) {

            /* Paid amount is less then available wallet amount*/
            $("#wallet_amount_used").text(" - " + currency + " " + (token_currency * (wallet_amount_used_fixed + (tip / token_currency))).toFixed(parseInt(digit_count)));
        } else {

            /* Paid amount is greater then available wallet amount*/
            $("#wallet_amount_used").text(" - " + currency + " " + wallet_amount_available.toFixed(parseInt(digit_count)));
            payable_amount = ((gross_amount + tip + total_taxable_amount) - wallet_amount_available);
            // payable_amount=((amount_payable + tip)  - (total_subscription_discount+wallet_amount_available+loyalty_amount));

            $("#cart_total_payable_amount").html(currency + payable_amount.toFixed(parseInt(digit_count)));
            $("input[name='cart_total_payable_amount']").val(payable_amount.toFixed(parseInt(digit_count)));
        }
        if (amount_payable + wallet_amount_used_fixed + tip >= parseFloat($('#mov').text())) {
            $("#order_placed_btn").removeAttr("disabled");
            $("#order_placed_btn").removeClass("d-none");
            $("#MOV_Notification").addClass("d-none");
        } else {
            $("#order_placed_btn").attr("disabled", true);
            $("#order_placed_btn").addClass("d-none");
            $("#MOV_Notification").removeClass("d-none");
        }
    } else {

        // payable_amount=((amount_payable + tip + total_taxable_amount)  - (total_subscription_discount+loyalty_amount));
        payable_amount = ((amount_payable + tip));
        $("#cart_total_payable_amount").html(currency + payable_amount.toFixed(parseInt(digit_count)));
        $("input[name='cart_total_payable_amount']").val(payable_amount.toFixed(parseInt(digit_count)));
        if (amount_payable >= parseFloat($('#mov').text())) {
            $("#order_placed_btn").removeAttr("disabled");
            $("#order_placed_btn").removeClass("d-none");
            $("#MOV_Notification").addClass("d-none");
        } else {
            $("#order_placed_btn").attr("disabled", true);
            $("#order_placed_btn").addClass("d-none");
            $("#MOV_Notification").removeClass("d-none");
        }
    }


});
$(document).on('click', '.qty-minus', function () {
    $('.qty-minus').prop('disabled', true);
    $('.qty-plus').prop('disabled', true);
    let base_price = $(this).data('base_price');
    let cartproduct_id = $(this).attr("data-id");
    let minimum_order_count = $(this).attr("data-minimum_order_count");
    let batch_count = $(this).attr("data-batch_count");
    if (batch_count > 0)
        batch_count = batch_count;
    else
        batch_count = 1;

    if (minimum_order_count > 0)
        minimum_order_count = minimum_order_count;
    else
        minimum_order_count = 1;
    let qty = $('#quantity_' + cartproduct_id).val();
    let decrevalue = parseInt(qty) - parseInt(batch_count);


    $(this).find('.fa').removeClass("fa-minus").addClass("fa-spinner fa-pulse");
    if (decrevalue >= minimum_order_count) {
        $('#quantity_' + cartproduct_id).val(decrevalue);
        updateQuantity(cartproduct_id, decrevalue, base_price);
    } else {
        // alert('remove this product');
        $('#remove_item_modal').modal('show');
        let vendor_id = $(this).data('vendor_id');
        $('#remove_item_modal #vendor_id').val(vendor_id);
        $('#remove_item_modal #cartproduct_id').val(cartproduct_id);
    }
});

$(document).on('click', '.checked-cart-product', function () {
    if ($(this).is(':checked')) {
        var cart_product_id = $(this).val();
        var is_cart_checked = 1;
    } else {
        var cart_product_id = $(this).val();
        var is_cart_checked = 0;
    }
    $('#fa_spinner_' + cart_product_id).removeClass("d-none");
    $(this).addClass("d-none");
    updateCartProductStatus(cart_product_id, is_cart_checked);
});

$(document).on('click', '.qty-plus', function () {
    $('.qty-plus').prop('disabled', true);
    $('.qty-minus').prop('disabled', true);
    let base_price = $(this).data('base_price');
    let cartproduct_id = $(this).attr("data-id");
    let qty = $('#quantity_' + cartproduct_id).val();
    let minimum_order_count = $(this).attr("data-minimum_order_count");
    let batch_count = $(this).attr("data-batch_count");
    if (batch_count > 0)
        batch_count = batch_count;
    else
        batch_count = 1;

    if (minimum_order_count > 0)
        minimum_order_count = minimum_order_count;
    else
        minimum_order_count = 1;

    let increvalue = parseInt(qty) + parseInt(batch_count);

    $('#quantity_' + cartproduct_id).val(increvalue);
    $(this).find('.fa').removeClass("fa-minus").addClass("fa-spinner fa-pulse");
    updateQuantity(cartproduct_id, increvalue, base_price);
});

$(document).on('blur', 'input.input-number', function () {
    let base_price = $(this).data('base_price');
    let cartproduct_id = $(this).attr("data-id");
    let qty = $(this).val();
    let minimum_order_count = $(this).attr("data-minimum_order_count");
    let batch_count = $(this).attr("data-batch_count");
    if (batch_count > 0)
        batch_count = batch_count;
    else
        batch_count = 1;

    if (minimum_order_count > 0)
        minimum_order_count = minimum_order_count;
    else
        minimum_order_count = 1;

    let increvalue = parseInt(qty);

    $('#quantity_' + cartproduct_id).val(increvalue);
    updateQuantity(cartproduct_id, increvalue, base_price);
});

$(document).on('change', '.delivery-fee', function () {
    let code = $(this).val();
    let address_id = $("input[type='radio'][name='address_id']:checked").val();
    cartHeaderDilivery(address_id, code);
});


//    cartHeader();
$(document).on("click", "#cancel_save_address_btn", function () {
    $('#add_new_address').show();
    $('#add_new_address_btn').show();
    $('#add_new_address_form').hide();
});
$(document).on("click", "#add_new_address_btn a.add-address", function () {
    if (auth) {
        // $(this).hide();
        initialize();
        $("#add_new_address_form_modal").modal('show');
        $('#add_new_address_form').show();
    } else {
        $('#login_modal').modal('show');
    }
});
$(document).on("click", "#save_address", function () {
    let city = $('#add_new_address_form #city').val();
    let state = $('#add_new_address_form #state').val();
    let state_code = $('#add_new_address_form #state_code').val();
    let street = $('#add_new_address_form #street').val();
    let address = $('#add_new_address_form #address').val();
    let country = $('#add_new_address_form #country').val();
    let pincode = $('#add_new_address_form #pincode').val();
    let type = $("input[name='address_type']:checked").val();
    let latitude = $('#add_new_address_form #latitude').val();
    let longitude = $('#add_new_address_form #longitude').val();
    let house_number = $('#add_new_address_form #house_number').val();
    let extra_instruction = $('#add_new_address_form #extra_instruction').val();
    if (latitude != '' && longitude != '') {
        $.ajax({
            type: "post",
            dataType: "json",
            url: user_store_address_url,
            data: {
                "city": city,
                "type": type,
                "state": state,
                "street": street,
                "address": address,
                "country": country,
                "pincode": pincode,
                "latitude": latitude,
                "longitude": longitude,
                "house_number": house_number,
                "extra_instruction": extra_instruction,
                "state_code": state_code
            },
            beforeSend: function () {
                if ($("#cart_table").length > 0) {
                    $(".spinner-box").show();
                    $("#cart_table").hide();
                }
            },
            success: function (response) {
                if (response.status == 'error') {
                    Swal.fire({
                        title: "Warning!",
                        text: response.message,
                        icon: "warning",
                        button: "OK",
                    });
                    return
                }
                if ($("#add_edit_address").length > 0) {

                    $("#add_edit_address").modal('hide');
                    location.reload();
                } else {
                    $("#add_new_address_form_modal").modal('hide');
                    // let address_template = _.template($('#address_template').html());
                    if (address.length > 0) {
                        //   $('#order_placed_btn').attr('disabled', false);
                        //   $("#address_template_main_div").append(address_template({address:response.address}));
                        cartHeader(response.address.id);
                    }
                }
            },
            error: function (reject) {
                if ($("#cart_table").length > 0) {
                    $(".spinner-box").hide();
                    $("#cart_table").show();
                }
                if (reject.status === 422) {
                    var message = $.parseJSON(reject.responseText);
                    $.each(message.errors, function (key, val) {
                        $("#" + key + "_error").text(val[0]);
                    });
                }
            }
        });
    } else {
        Swal.fire({
            title: "Warning!",
            text: "Please select address from suggessions or from map.",
            icon: "warning",
            button: "OK",
        });
        $(".showMapHeader").click();
    }
});

$(document).on("click", ".addToCart", function () {
    if (localStorage.in_cart == "true") {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Product is already in cart!',
            //footer: '<a href="">Why do I have this issue?</a>'
        })
        return false;
    }
    if (!$.hasAjaxRunning()) {
        addToCart();
    }
});


function checkIsolateSingleVendor(vendor_id) {
    var resp = '';
    $.ajax({
        type: "post",
        dataType: "json",
        async: false,
        url: check_isolate_single_vendor_url,
        data: { vendor_id: vendor_id },
        success: function (response) {
            resp = response;
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            resp = response;
        },
    });
    return resp;
}
async function showRemoveCart(modelText) {
    var start_date = $('#start_time').val();
    var end_date = $('#end_time').val();
    var incremental_hrs = $('#incremental_hrs').val();
    var total_booking_time = $('#total_hrs').val();
    var data_service_day = '';
    var data_service_date = '';
    var data_service_start_time = '';
    var data_total_booking_time = '';
    if (typeof (service_day) != "undefined" && service_day !== null) {
        var data_service_day = service_day;
    }
    if (typeof (service_date) != "undefined" && service_date !== null) {
        var data_service_date = service_date;
    }
    if (typeof (service_start_time) != "undefined" && service_start_time !== null) {
        var data_service_start_time = service_start_time;
    }
    if (typeof (total_booking_time) != "undefined" && total_booking_time !== null) {
        var data_total_booking_time = total_booking_time;
    }
    $("#single_vendor_order_modal_text").html(modelText);
    $("#single_vendor_remove_cart_btn").attr({
        'data-product_id': product_id,
        'data-variant_id': $('#prod_variant_id').val(),
        'data-quantity': $('.quantity_count').val(),
        'data-vendor_id': vendor_id,
        'data-page': 'productDetail',
        'data-start_time': start_date,
        'data-end_time': end_date,
        'data-incremental_hrs': incremental_hrs,
        'data-service_period': service_period,
        'data-service_day': data_service_day,
        'data-service_date': data_service_date,
        'data-service_start_time': data_service_start_time,
        'data-total_hrs': data_total_booking_time,
    });
    $("#single_vendor_order_modal").modal('show');
}

function addToCart() {
    var breakOut = false;
    var Product_quantity = $('.quantity_count').val();
    var booking_time = $('#range-datepicker').val();
    var start_date_time, end_date_time;

    if (booking_time) {


        if (booking_time.includes(' to ')) {
            var dateParts = booking_time.split(' to ');
            start_date_time = dateParts[0];
            end_date_time = dateParts[1];
        } else if (booking_time.trim() !== '') {
            start_date_time = booking_time;
            end_date_time = booking_time;
        }
    }
    var addLongTerm = 0;
    var addRecurringBooking = 0;
    vendor_id = (vendor_id == undefined || vendor_id == '') ? document.querySelector('input[name=vendor_id]').value : vendor_id;
    if ($('#is_recurring_booking').length > 0) {
        // var booking_type   = $('input[name="booking_type"]:checked').val();
        // recurringformPost
        if (product_id == OrderStorage.getStorage('cartFirstProductId')) {
            var message = _language.getLanString('Product Already added in cart');
            sweetAlert.error('', message);
            return false;
        }
    } else {
        recurringformPost = {};
    }
    if (Product_quantity <= 0) {
        Swal.fire({
            // title: "Warning!",
            text: _language.getLanString('Please enter quantity'),
            icon: "warning",
            button: "OK",
        });


        breakOut = true;
        return false;

    }

    if ($('#pincode').val() == '') {

        Swal.fire({
            text: _language.getLanString('Please enter pincode to continue'),
            icon: "warning",
            button: "OK",
        });
        return false;
    }

    if ($('#date_input').val() == '') {

        Swal.fire({
            text: _language.getLanString('Please select delivery date to continue'),
            icon: "warning",
            button: "OK",
        });
        return false;
    }

    // if($('#sele_slot_id').val() == ''){

    //     Swal.fire({
    //         text: _language.getLanString('Please select delivery slot to continue'),
    //         icon: "warning",
    //         button: "OK",
    //     });
    //     return false;
    // }

    if ($('#is_long_term_service').length > 0) {
        addLongTerm = 1;
        if (product_id == OrderStorage.getStorage('cartFirstProductId')) {
            Swal.fire({
                text: _language.getLanString('Product Already added in cart'),
                icon: "warning",
                button: "OK",
            });
            return false;
        }


        var service_start_time = $('#service_start_time').val();
        if (service_start_time == '' || service_start_time == undefined) {
            Swal.fire({
                text: _language.getLanString('Please enter service timing'),
                icon: "warning",
                button: "OK",
            });
            return false;
        }
    }


    // Recuring booking code



    // if($('#is_recurring_bookingss').val() > 0){
    //     addRecurringBooking =1;


    //     var booking_type        = $('input[name="booking_type"]:checked').val();
    //     var recurring_week_type = '';
    //     var recurring_week_day  = '';
    //     if(booking_type == 3){
    //         var recurringBookingTime      = $("#month_booking_time").val();
    //         var recurringBookingDate      = '2023-01-23,2023-01-24,2023-01-30,2023-01-31';
    //        // var recurringBookingDate      = $("#month-datepicker").val();
    //         var message                   = _language.getLanString('Please select monthly dates');
    //     }else if(booking_type == 4){
    //         var recurringBookingTime      = $("#custom_booking_time").val();
    //         var recurringBookingDate      = '2023-01-23,2023-01-24,2023-01-30,2023-01-31';
    //         //var recurringBookingDate      = $("#custom-datepicker").val();
    //         var message                   = _language.getLanString('Please select custom dates');
    //     }


    //     if(recurringBookingTime == '' || recurringBookingTime== undefined){
    //         Swal.fire({
    //             text: _language.getLanString('Please enter booking timing'),
    //             icon: "warning",
    //             button: "OK",
    //         });
    //         return false;
    //     }

    //     if(recurringBookingDate == '' || recurringBookingDate== undefined){
    //         Swal.fire({
    //             text: message,
    //             icon: "warning",
    //             button: "OK",
    //         });
    //         return false;
    //     }
    // }


    $(".productAddonSetOptions").each(function (index) {
        var min_select = $(this).attr("data-min");
        var max_select = $(this).attr("data-max");
        var addon_set_title = $(this).attr("data-addonset-title");
        if ((min_select > 0) && ($(this).find(".productDetailAddonOption:checked").length < min_select)) {
            Swal.fire({
                // title: "Warning!",
                text: "Minimum " + min_select + " " + addon_set_title + " required",
                icon: "warning",
                button: "OK",
            });
            // alert("Minimum " + min_select + " " + addon_set_title + " required");

            breakOut = true;
            return false;
        }
        if ((max_select > 0) && ($(this).find(".productDetailAddonOption:checked").length > max_select)) {
            Swal.fire({
                // title: "Warning!",
                text: "You can select maximum " + max_select + " " + addon_set_title,
                icon: "warning",
                button: "OK",
            });
            // alert("You can select maximum " + max_select + " " + addon_set_title);
            breakOut = true;
            return false;
        }
    });
    if (!breakOut) {
        var sVendorResponse = checkIsolateSingleVendor(vendor_id);
        if (sVendorResponse.status == 'Success') {
            var service_period = $('#service_period').val();
            var service_day = $('#service_day').val();
            var service_date = $('#service_date').val();
            var service_start_time = $('#service_start_time').val();
            var is_template = $('#is_template').val();



            if (((sVendorResponse.isSingleVendorEnabled == 1) && (sVendorResponse.otherVendorExists == 1)) || (OrderStorage.getStorage('LongTermServiceAdded') == 1) || (OrderStorage.getStorage('cartProductCount') > 0 && addLongTerm == 1)) {
                var modelText = _language.getLanString('You can only buy products for single vendor. Do you want to remove all your cart products to continue ?');
                if (OrderStorage.getStorage('LongTermServiceAdded') == 1 || addLongTerm == 1) {
                    modelText = _language.getLanString('You can only buy Single Long Term Serivce . Do you want to remove  all your cart products to continue ?');
                }
                if (OrderStorage.getStorage('RecurringBookingAdded') == 1 || addRecurringBooking == 1) {
                    modelText = _language.getLanString('You can only buy Single Recurring Booking Serivce . Do you want to remove  all your cart products to continue ?');
                }
                showRemoveCart(modelText);
            } else {
                // if(serviceType == 'yacht'){

                //     var booking_duration = $('input[name="booking_duration"]');
                //     let isDurationSelected = false;

                //     booking_duration.each(function(inbdex, element){
                //         if(element.checked){
                //             let isDurationSelected = false;
                //             console.log(element.value)
                //         }
                //     })
                //     return false;
                // }
                var variant_id = $('#prod_variant_id').val();
                var start_date = $('#start_time').val();

                var end_date = $('#end_time').val();
                var quantity = $('.quantity_count').val();
                var incremental_hrs = $('#incremental_hrs').val();
                var total_booking_time = $('#total_hrs').val();

                var sele_slot_id = $("#sele_slot_id").val();
                var sele_slot_price = $("#sele_slot_price").val();
                var delivery_date = $("#date_input").val();
                submitAddtoCart(addonids, addonoptids, product_id, variant_id, quantity, vendor_id, start_date, end_date, incremental_hrs, total_booking_time, service_period, service_day, service_start_time, service_date, sele_slot_id, sele_slot_price, delivery_date, recurringformPost, is_template, start_date_time, end_date_time);
            }
        }
    }
}

function submitAddtoCart(addonids, addonoptids, product_id, variant_id, quantity, vendor_id, start_date = '', end_date = '', incremental_hrs = '', total_booking_time = '', service_period = '', service_day = '', service_start_time = '', service_date = '', sele_slot_id = '', sele_slot_price = '', delivery_date = '', recurringformPost = '', is_template = '', start_date_time = '', end_date_time = '') {

    var returnResponse = false;
    $.ajax({
        type: "post",
        dataType: "json",
        async: false,
        url: add_to_cart_url,
        data: {
            "addonID": addonids,
            "vendor_id": vendor_id,
            "product_id": product_id,
            "addonoptID": addonoptids,
            "quantity": quantity,
            "variant_id": variant_id,
            "start_date": start_date,
            "end_date": end_date,
            "incremental_hrs": incremental_hrs,
            "total_booking_time": total_booking_time,
            "service_period": service_period,
            "service_day": service_day,
            "service_date": service_date,
            "service_start_time": service_start_time,
            "recurringformPost": recurringformPost,
            "sele_slot_id": sele_slot_id,
            "delivery_date": delivery_date,
            "sele_slot_price": sele_slot_price,
            "is_template": is_template,
            "start_date_time": start_date_time,
            "end_date_time": end_date_time,
        },
        success: function (response) {
            if (response.status == 'success') {
                // $(".shake-effect").effect("shake", { times: 0 }, 1200);
                returnResponse = true;
                cartHeader();

                if ($("#pickup_service").is(":checked")) {
                    location.href = '/category/cabservice?destination_location=' + response.vendor.address + '&destination_location_latitude' + response.vendor.latitude + '&destination_location_longitude' + response.vendor.longitude + '&yacht_id=' + product_id;
                }
                if (vendor_type == 'rental' || vendor_type == 'p2p') {
                    location.href = '/viewcart';
                }
                if (response.vendor.rental == 1) {
                    location.href = '/viewcart';
                }
            } else {
                Swal.fire({
                    // title: "Warning!",
                    text: response.message,
                    icon: "warning",
                    button: "OK",
                });
                //alert(response.message);
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
            Swal.fire({
                // title: "Warning!",
                text: error_messages,
                icon: "error",
                button: "OK",
            });
            // alert(error_messages);
        },
    });
    return returnResponse;
}

$(document).delegate("#single_vendor_remove_cart_btn", "click", function () {
    $(this).parents('.modal').modal('hide');
    var product_id = $(this).attr('data-product_id');
    var variant_id = $(this).attr('data-variant_id');
    var quantity = $(this).attr('data-quantity');
    var vendor_id = $(this).attr('data-vendor_id');
    var start_date = $(this).attr('data-start_time');
    var end_date = $(this).attr('data-end_time');
    var incremental_hrs = $(this).attr('data-incremental_hrs');
    var total_booking_time = $(this).attr('data-total_hrs');
    var service_period = $(this).attr('data-service_period');
    var service_day = $(this).attr('data-service_day');
    var service_start_time = $(this).attr('data-service_start_time');
    var service_date = $(this).attr('data-service_date');

    if ($(this).attr('data-page') == 'productDetail') {
        submitAddtoCart(addonids, addonoptids, product_id, variant_id, quantity, vendor_id, start_date, end_date, incremental_hrs, total_booking_time, service_period, service_day, service_start_time, service_date);
    } else if ($(this).attr('data-page') == 'vendorProducts') {
        var elem = $(this).attr('data-element_id');
        submitAddtoCartProductsAddons($('#' + elem), addonids, addonoptids, product_id, variant_id, quantity, vendor_id);
    }
});

// ********************************************* all functions for vendor product new page ************************************** //


window.getProductAddons = function getProductAddons(slug, variantId = 0, vendorId = 0) {
    $.ajax({
        type: "post",
        dataType: "json",
        url: get_product_addon_url,
        data: { "slug": slug, "variant": variantId, "vendor": vendorId },
        success: function (response) {
            if (response.status == 'Success') {
                $("#product_addon_modal .modal-content").html('');
                let addon_template = _.template($('#addon_template').html());
                $("#product_addon_modal .modal-content").append(addon_template({ Helper: NumberFormatHelper, addOnData: response.data }));
                $("#product_addon_modal").modal('show');
            } else {
                // alert(response.message);
                Swal.fire({
                    // title: "Warning!",
                    text: response.message,
                    icon: "warning",
                    button: "OK",
                });
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
            Swal.fire({
                // title: "Warning!",
                text: error_messages,
                icon: "error",
                button: "OK",
            });
            //alert(error_messages);
        },
    });
}

window.getEstimateProductAddons = function getEstimateProductAddons(slug, variantId = 0) {
    $.ajax({
        type: "post",
        dataType: "json",
        url: get_estimate_product_addon_url,
        data: { "slug": slug, "variant": variantId },
        success: function (response) {
            if (response.status == 'Success') {
                $("#estimate_product_addon_modal .modal-content").html('');
                let estimate_addon_template = _.template($('#estimate_addon_template').html());
                $("#estimate_product_addon_modal .modal-content").append(estimate_addon_template({ estimateAddOnData: response.data }));
                $("#estimate_product_addon_modal").modal('show');
            } else {
                alert(response.message);
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
            alert(error_messages);
        },
    });
}

function getLastAddedProductVariant(_this, cart_id, product_id, addon) {
    $('#repeat_item_modal').find(".last_cart_product_id").val('');
    $('#repeat_item_modal').find(".curr_product_id").val('');
    $('#repeat_item_modal').find('.curr_product_has_addons').val('');
    ajaxCall = $.ajax({
        type: "post",
        dataType: "json",
        url: get_last_added_product_variant_url,
        data: { "cart_id": cart_id, "product_id": product_id },
        success: function (response) {
            if (response.status == 'Success') {
                $('#repeat_item_modal').modal('show');
                $('#repeat_item_modal').find(".last_cart_product_id").val(response.data.id);
                $('#repeat_item_modal').find(".curr_product_id").val(product_id);
                $('#repeat_item_modal').find('.curr_product_has_addons').val(addon);
            }
        },
        error: function (response) {
            var error = $.parseJSON(response.responseText);
            Swal.fire({
                // title: "Warning!",
                text: error.message,
                icon: "error",
                button: "OK",
            });
            //alert(error.message);
        }
    });
}

function getProductVariantWithDifferentAddons(_this, cart_id, product_id) {
    ajaxCall = $.ajax({
        type: "post",
        dataType: "json",
        url: get_product_variant_with_different_addons_url,
        data: { "cart_id": cart_id, "product_id": product_id },
        success: function (response) {
            if (response.status == 'Success') {
                $('#customize_repeated_item_modal .modal-content').html(response.data);
            } else {
                $('#customize_repeated_item_modal .modal-content').html('');
                //alert(response.message);
                Swal.fire({
                    // title: "Warning!",
                    text: response.message,
                    icon: "warning",
                    button: "OK",
                });
            }
        },
        error: function (response) {
            var error = $.parseJSON(response.responseText);
            Swal.fire({
                // title: "Warning!",
                text: error.message,
                icon: "error",
                button: "OK",
            });
            //alert(error.message);
            $('#customize_repeated_item_modal .modal-content').html('');
        }
    });
}

$(document).on('click', '.qty-minus-product', function () {
    let base_price = $(this).data('base_price');
    let cartproduct_id = $(this).attr("data-id");
    let cart_id = $(this).attr("data-cart");
    let product_id = $(this).attr("data-product_id");
    let variant_id = $(this).attr("data-variant_id");
    let minimum_order_count = $(this).attr("data-minimum_order_count");
    let batch_count = $(this).attr("data-batch_count");
    if (batch_count > 0)
        batch_count = batch_count;
    else
        batch_count = 1;

    if (minimum_order_count > 0)
        minimum_order_count = minimum_order_count;
    else
        minimum_order_count = 1;

    let qty = $(this).next().val();
    let decrevalue = parseInt(qty) - parseInt(batch_count);
    if (!$.hasAjaxRunning()) {
        if (decrevalue >= minimum_order_count) {
            if ($(this).hasClass('remove-customize') && $(this).hasClass('m-open')) {
                $(this).find('.fa').removeClass("fa-minus").addClass("fa-spinner fa-pulse");
                updateProductQuantity(product_id, cartproduct_id, decrevalue, base_price, this);
            }
            else if ($(this).hasClass('remove-customize') && !$(this).hasClass('m-open')) {
                $('#customize_repeated_item_modal').modal('show');
                getProductVariantWithDifferentAddons(this, cart_id, product_id);
            }
            else {
                $(this).find('.fa').removeClass("fa-minus").addClass("fa-spinner fa-pulse");
                $('#quantity_ondemand_' + cartproduct_id).val(decrevalue);
                updateProductQuantity(product_id, cartproduct_id, decrevalue, base_price, this);
            }
        } else {
            // alert('remove this product');
            $('#remove_item_modal').modal('show');
            let vendor_id = $(this).data('vendor_id');
            $('#remove_item_modal #vendor_id').val(vendor_id);
            $('#remove_item_modal #product_id').val(product_id);
            $('#remove_item_modal #cartproduct_id').val(cartproduct_id);
        }
    }
});
$(document).on('click', '.qty-plus-product', function () {
    let base_price = $(this).data('base_price');
    let cartproduct_id = $(this).attr("data-id");
    let cart_id = $(this).attr("data-cart");
    let product_id = $(this).attr("data-product_id");
    let minimum_order_count = $(this).attr("data-minimum_order_count");
    let batch_count = $(this).attr("data-batch_count");
    if (batch_count > 0)
        batch_count = batch_count;
    else
        batch_count = 1;
    // let variant_id = $(this).attr("data-variant_id");
    // let vendor_id = $(this).attr("data-vendor_id");
    let addon = $(this).attr("data-addon");
    let qty = $(this).prev().val();
    let increvalue = parseInt(qty) + parseInt(batch_count);
    if (!$.hasAjaxRunning()) {
        if ($(this).hasClass('repeat-customize') && $(this).hasClass('m-open')) {
            $(this).find('.fa').removeClass("fa-plus").addClass("fa-spinner fa-pulse");
            updateProductQuantity(product_id, cartproduct_id, increvalue, base_price, this);
        }
        else if ($(this).hasClass('repeat-customize') && !$(this).hasClass('m-open')) {
            getLastAddedProductVariant(this, cart_id, product_id, addon);
        } else {
            $(this).prev().val(increvalue);
            $(this).find('.fa').removeClass("fa-plus").addClass("fa-spinner fa-pulse");
            updateProductQuantity(product_id, cartproduct_id, increvalue, base_price, this);
        }
    }
});

$(document).delegate('#repeat_item_btn', 'click', function () {
    let that = $(this).closest('.modal');
    let cartproduct_id = that.find('.last_cart_product_id').val();
    let qty = $('#quantity_ondemand_' + cartproduct_id).val();
    $('#quantity_ondemand_' + cartproduct_id).val(++qty);
    $(this).find('.fa').removeClass("fa-plus").addClass("fa-spinner fa-pulse");
    $('#repeat_item_modal').modal('hide');
    updateProductQuantity(0, cartproduct_id, qty, 0, this);
});

function updateProductQuantity(product_id, cartproduct_id, quantity, base_price = 0, iconElem = '') {

    ajaxCall = $.ajax({
        type: "post",
        dataType: "json",
        url: update_qty_url,
        data: { "quantity": quantity, "cartproduct_id": cartproduct_id },
        success: function (response) {

            if (response.status == "error") {
                Swal.fire({
                    title: "Warning!",
                    text: response.message,
                    icon: "warning",
                    button: "OK",
                });
                $('#quantity_ondemand_' + cartproduct_id).val(response.quantity);
            } else {
                var latest_price = parseFloat(parseInt(base_price) * parseInt(quantity)).toFixed(parseInt(digit_count));
                $('#product_total_amount_' + cartproduct_id).html('$' + latest_price);
                if ($(iconElem).hasClass('remove-customize') && $(iconElem).hasClass('m-open')) {
                    $(iconElem).next().val(quantity);
                    $(iconElem).closest('.customized_product_row').find('.total_product_price').text(latest_price);
                    var total_qty = $('.add_vendor_product[data-product_id="' + product_id + '"]').next().find('input').val();
                    $('.add_vendor_product[data-product_id="' + product_id + '"]').next().find('input').val(--total_qty);
                }
                if ($(iconElem).hasClass('repeat-customize') && $(iconElem).hasClass('m-open')) {
                    $(iconElem).prev().val(quantity);
                    $(iconElem).closest('.customized_product_row').find('.total_product_price').text(latest_price);
                    var total_qty = $('.add_vendor_product[data-product_id="' + product_id + '"]').next().find('input').val();
                    $('.add_vendor_product[data-product_id="' + product_id + '"]').next().find('input').val(++total_qty);
                }
                cartHeader();
            }
        },
        complete: function (data) {
            if ($(iconElem).hasClass("qty-minus-product")) {
                $(iconElem).find(".fa").removeAttr("class").addClass("fa fa-minus");
            } else if ($(iconElem).hasClass("qty-plus-product")) {
                $(iconElem).find(".fa").removeAttr("class").addClass("fa fa-plus");
            }
        }
    });
}

$(document).delegate("#repeat_item_new_addon_btn", "click", function () {
    let that = $(this).closest('.modal');
    let cartproduct_id = that.find('.last_cart_product_id').val();
    initAddVendorProduct($("#add_button_href" + cartproduct_id));
});

$(document).delegate('.counter-container .qty-action', 'click', function () {
    var qty = $('.counter-container .addon-input-number').val();
    if (qty > 1) {
        if ($(this).hasClass('minus')) {
            $('.counter-container .addon-input-number').val(--qty);
        }
    }
    if ($(this).hasClass('plus')) {
        $('.counter-container .addon-input-number').val(++qty);
    }
    let parentdiv = $(this).parents('.modal-content');
    calculateVariantPriceWithAddon(parentdiv);
});

$(document).delegate(".product_addon_option", "click", function () {
    //  add addons data
    var addonSet = $(this).parents('.productAddonSetOptions');
    var addon_minlimit = addonSet.attr("data-min");
    var addon_maxlimit = addonSet.attr("data-max");
    if (addonSet.find(".product_addon_option:checked").length > addon_maxlimit) {
        this.checked = false;
    }
    let parentdiv = $(this).parents('.modal-body');
    calculateVariantPriceWithAddon(parentdiv);
});

function calculateVariantPriceWithAddon(parentdiv) {
    let addon_elem = parentdiv.find('.productAddonSetOptions');
    let addonVariantPriceVal = $("#addonVariantPriceVal").val();
    let addon_variant_qty = $(".addon-input-number").val();
    let total_addon_price = 0;
    addonids = [];
    addonoptids = [];

    addon_elem.find('.product_addon_option').each(function (index, value) {
        var addonId = $(value).attr("addonId");
        var addonOptId = $(value).attr("addonOptId");
        if ($(value).is(":checked")) {
            var addonPrice = $(value).attr("addonPrice");
            addonids.push(addonId);
            addonoptids.push(addonOptId);
            total_addon_price = parseFloat(total_addon_price) + parseFloat(addonPrice);
        }
    });
    let addon_variant_price = (parseInt(addon_variant_qty) * (parseFloat(addonVariantPriceVal) + parseFloat(total_addon_price))).toFixed(parseInt(digit_count));
    $(".addon_variant_price").text(addon_variant_price);
}

$(document).delegate(".add_vendor_addon_product", "click", function () {
    let that = $(this);
    let addon_variant_qty = $(".addon-input-number").val();
    addToCartProductsAddons(that, addon_variant_qty);
});

$(document).on("click", ".add_vendor_product", function () {
    let that = $(this);
    initAddVendorProduct(that);
});

$(document).on("click", ".add_vendor_product_btn", function () {
    let that = $(this);
    initAddEstimateProduct(that);
});

$(document).delegate("#repeat_item_with_new_addon_btn", "click", function () {
    let that = $(this).closest('.modal');
    let curr_product_id = that.find('.curr_product_id').val();
    let curr_product = $('.add_vendor_product[data-product_id="' + curr_product_id + '"]');
    initAddVendorProduct(curr_product);
});

function initAddVendorProduct(that) {
    let check_addon = that.attr('data-addon');
    if (check_addon > 0) {
        var variant_id = that.data("variant_id");
        let slug = that.parents('.product_row').attr('data-slug');
        let vendor_id = that.data("vendor_id");
        getProductAddons(slug, variant_id, vendor_id);
        return false;
    }

    var minimum_order_count = $(that).data("minimum_order_count");
    if (minimum_order_count > 0)
        minimum_order_count = minimum_order_count;
    else
        minimum_order_count = 1;


    // end addons data
    if (!$.hasAjaxRunning()) {
        addToCartProductsAddons(that, minimum_order_count);
    }
}

function initAddEstimateProduct(that) {
    let check_addon = that.attr('data-addon');
    if (check_addon > 0) {
        var variant_id = that.data("variant_id");
        let slug = that.parents('.product_row').attr('data-slug');
        getEstimateProductAddons(slug, variant_id);
        return false;
    }

    var minimum_order_count = $(that).data("minimum_order_count");
    if (minimum_order_count > 0)
        minimum_order_count = minimum_order_count;
    else
        minimum_order_count = 1;


    // end addons data
    // if (!$.hasAjaxRunning()) {
    //     addToCartProductsAddons(that,minimum_order_count);
    // }
}

// add to cart on new page
function addToCartProductsAddons(that, quantity = 1) {
    var isAddonSection = false;
    var breakOut = false;
    if (that.hasClass('add_vendor_addon_product')) {
        isAddonSection = true;
        that.parents('.modal').find(".productAddonSetOptions").each(function (index) {
            var min_select = $(this).attr("data-min");
            var max_select = $(this).attr("data-max");
            var addon_set_title = $(this).attr("data-addonset-title");
            if ((min_select > 0) && ($(this).find(".product_addon_option:checked").length < min_select)) {
                success_error_alert('error', "Minimum " + min_select + " " + addon_set_title + " required", ".addon_response");
                breakOut = true;
                return false;
            }
            if ((max_select > 0) && ($(this).find(".product_addon_option:checked").length > max_select)) {
                success_error_alert('error', "You can select maximum " + max_select + " " + addon_set_title, ".addon_response");
                breakOut = true;
                return false;
            }
        });
    }
    if (!breakOut) {
        var ajaxCall = 'ToCancelPrevReq';
        var vendor_id = that.data("vendor_id");
        var product_id = that.data("product_id");
        var variant_id = that.data("variant_id");
        var show_plus_minus = "#show_plus_minus" + product_id;

        var sVendorResponse = checkIsolateSingleVendor(vendor_id);
        if (sVendorResponse.status == 'Success') {
            if ((sVendorResponse.isSingleVendorEnabled == 1) && (sVendorResponse.otherVendorExists == 1) || (OrderStorage.getStorage('LongTermServiceAdded') == 1)) {
                var modelText = _language.getLanString('You can only buy products for single vendor. Do you want to remove all your cart products to continue ?');
                if (OrderStorage.getStorage('LongTermServiceAdded') == 1) {
                    modelText = _language.getLanString('You can only buy Single Long Term Serivce . Do you want to remove this Serivce to continue ?');
                }
                $("#single_vendor_remove_cart_btn").attr({
                    'data-product_id': product_id,
                    'data-variant_id': variant_id,
                    'data-quantity': quantity,
                    'data-vendor_id': vendor_id,
                    'data-element_id': that.attr('id'),
                    'data-page': 'vendorProducts'
                });
                $("#single_vendor_order_modal_text").html(modelText);
                $("#single_vendor_order_modal").modal('show');
            } else {
                submitAddtoCartProductsAddons(that, addonids, addonoptids, product_id, variant_id, quantity, vendor_id);
            }
        }
    }
}

function submitAddtoCartProductsAddons(that, addonids, addonoptids, product_id, variant_id, quantity, vendor_id) {
    var returnResponse = false;
    ajaxCall = $.ajax({
        type: "post",
        dataType: "json",
        url: that.data("add_to_cart_url"),
        data: {
            "addonID": addonids,
            "vendor_id": vendor_id,
            "product_id": product_id,
            "addonoptID": addonoptids,
            "quantity": quantity,
            "variant_id": variant_id,
        },
        success: function (response) {
            if (response.status == 'success') {
                returnResponse = true;
                // $(".shake-effect").effect("shake", { times: 0 }, 1200);
                cartHeader();
                if (that.hasClass('add_vendor_addon_product')) {
                    that.parents('.modal').modal('hide');
                    window.location.reload();
                } else {
                    $(that).next().show();
                    $(that).next().find('.minus').attr('data-id', response.cart_product_id);
                    $(that).next().find('.plus').attr('data-id', response.cart_product_id);
                    $(that).next().find('.input_qty').attr('id', "quantity_ondemand_" + response.cart_product_id);
                    $(that).next().find('.qty-minus-ondemand').attr('data-parent_div_id', "show_plus_minus" + response.cart_product_id);
                    $(that).next().attr('id', "show_plus_minus" + response.cart_product_id);

                    $(that).attr('id', "add_button_href" + response.cart_product_id);
                    $(that).hide();
                    $(that).next().show();

                    let parentdiv = $(that).parents('.classes_wrapper');
                    let addons_div = parentdiv.find('.addons-div');
                    if (addonoptids.length >= 0) {
                        let addons_div = parentdiv.find('.addons-div');
                        addons_div.hide();
                    }
                }
            } else {
                //alert(response.message);
                Swal.fire({
                    // title: "Warning!",
                    text: response.message,
                    icon: "error",
                    button: "OK",
                });
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
            Swal.fire({
                // title: "Warning!",
                text: error_messages,
                icon: "error",
                button: "OK",
            });
            //alert(error_messages);
        },
    });
    return returnResponse;
}

// ********************************************* End vendor product new page ************************************** //



// **********************************************   all function for ondemand services   *****************************************  ////////////////////////

$(document).on("click", "#next-button-ondemand-3", function () {
    $('.alert-danger').html('');

    var valid = checkSlotTimeSelecedValidation();

    if (slotValidater == 1) { // some date or time not selected
        sweetAlert.error('Oops...', 'Schedule date time is required');
        return false;
    }

    var urlParams = new URLSearchParams(window.location.search);
    var mode = urlParams.get('mode'); // Get 'mode' from URL
   

    var task_type = (mode == 'instant') ? 'now' : 'schedule';
    var schedule_date = $("input[name='booking_date']:checked").val();
    var schedule_time = $("input[name='booking_time']:checked").val();
    var specific_instructions = $("#specific_instructions").val();
    var productid = $("#last_cart_product_id").val();
    //alert(schedule_date);
    //alert(schedule_time);
    var schedule_dt = schedule_date;
    //alert(schedule_dt);
    // var schedule_dt = schedule_date +' '+schedule_time;
    if ((task_type == 'schedule') && (schedule_dt == '')) {
        success_error_alert('error', 'Schedule date time is required', ".cart_response");
        return false;
    }
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: update_cart_schedule,
        data: { task_type: task_type, specific_instructions: specific_instructions, productid: productid }, //, schedule_dt: schedule_dt,schedule_time:schedule_time
        success: function (response) {
            if (response.status == "Success") {
                window.location.href = showCart;
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            alert(response.message);
            success_error_alert('error', response.message, ".cart_response");

        }
    });
});




$(document).on("click", ".add_on_demand", function () {
    let that = $(this);


    // end addons data

    var ajaxCall = 'ToCancelPrevReq';
    var vendor_id = that.data("vendor_id");
    var product_id = that.data("product_id");
    var add_to_cart_url = that.data("add_to_cart_url");
    var variant_id = that.data("variant_id");
    var show_plus_minus = "#show_plus_minus" + product_id;


    if (!$.hasAjaxRunning()) {
        addToCartOnDemand(ajaxCall, vendor_id, product_id, addonids, addonoptids, add_to_cart_url, variant_id, show_plus_minus, that);

    }

});

$(document).on("click", ".productAddonOption", function () {
    var cart_id = '';
    var cart_product_id = '';
    let that = $(this);

    //  add addons data
    let parentdiv = $(this).parents('.add-on-main-div');
    let addon_elem = parentdiv.find('.productAddonSetOptions');



    addon_elem.find('.productAddonOption').each(function (index, value) {
        var addonId = $(value).attr("addonId");
        var addonOptId = $(value).attr("addonOptId");
        if ($(value).is(":checked")) {
            addonids.push(addonId);
            addonoptids.push(addonOptId);
        }
    });

    var cart_id = addon_elem.attr("data-cart_id");
    var cart_product_id = addon_elem.attr("data-cart_product_id");



    ajaxCall = $.ajax({
        type: "post",
        dataType: "json",
        url: update_addons_in_cart,
        data: { "addonID": addonids, "addonoptID": addonoptids, "cart_id": cart_id, "cart_product_id": cart_product_id },
        success: function (response) {
            if (response.status == 'success') {
                cartHeader();
                addonids = [];
                addonoptids = [];
            } else {
                addonids = [];
                addonoptids = [];
                //alert(response.message);
                Swal.fire({
                    // title: "Warning!",
                    text: response.message,
                    icon: "warning",
                    button: "OK",
                });
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.error;
            addonids = [];
            addonoptids = [];
            that.prop('checked', false);
            Swal.fire({
                // title: "Warning!",
                text: error_messages,
                icon: "error",
                button: "OK",
            });
            //alert(error_messages);
        }
    });

});

$(document).on('click', '.qty-minus-ondemand', function () {
    let base_price = $(this).data('base_price');
    let cartproduct_id = $(this).attr("data-id");
    let qty = $('#quantity_ondemand_' + cartproduct_id).val();
    $(this).find('.fa').removeClass("fa-minus").addClass("fa-spinner fa-pulse");
    if (qty > 1) {
        $('#quantity_ondemand_' + cartproduct_id).val(--qty);
        updateQuantityOnDemand(cartproduct_id, qty, base_price);
    } else {
        // alert('remove this product');
        $('#remove_item_modal').modal('show');
        let vendor_id = $(this).data('vendor_id');
        $('#remove_item_modal #vendor_id').val(vendor_id);
        $('#remove_item_modal #cartproduct_id').val(cartproduct_id);


    }
});
$(document).on('click', '.qty-plus-ondemand', function () {
    let base_price = $(this).data('base_price');
    let cartproduct_id = $(this).attr("data-id");
    let qty = $('#quantity_ondemand_' + cartproduct_id).val();
    $('#quantity_ondemand_' + cartproduct_id).val(++qty);
    $(this).find('.fa').removeClass("fa-minus").addClass("fa-spinner fa-pulse");
    updateQuantityOnDemand(cartproduct_id, qty, base_price);
});


function updateQuantityOnDemand(cartproduct_id, quantity, base_price, iconElem = '') {
    if (iconElem != '') {
        let elemClasses = $(iconElem).attr("class");
        $(iconElem).removeAttr("class").addClass("fa fa-spinner fa-pulse");
    }
    ajaxCall = $.ajax({
        type: "post",
        dataType: "json",
        url: update_qty_url,
        data: { "quantity": quantity, "cartproduct_id": cartproduct_id },
        success: function (response) {
            var latest_price = parseInt(base_price) * parseInt(quantity);
            $('#product_total_amount_' + cartproduct_id).html('$' + latest_price);
            cartHeader();
        },
        error: function (err) {
            if ($(".number .fa-spinner fa-pulse").length > 0) {
                $(".number .qty-minus .fa").removeAttr("class").addClass("fa fa-minus");
                $(".number .qty-plus .fa").removeAttr("class").addClass("fa fa-plus");
            }
        }
    });
}

// on demand add to cart
// dispatcherAgentData coming where we selecte price from dispatchr driver
function addToCartOnDemand(ajaxCall, vendor_id, product_id, addonids, addonoptids, add_to_cart_url, variant_id, show_plus_minus, that, dispatcherAgentData = "") {
    $.ajax({
        type: "post",
        dataType: "json",
        url: add_to_cart_url,
        data: {
            "addonID": addonids,
            "vendor_id": vendor_id,
            "product_id": product_id,
            "addonoptID": addonoptids,
            "quantity": 1,
            "variant_id": variant_id,
            "dispatcherAgentData": dispatcherAgentData,
        },
        success: function (response) {
            var address_id = dispatcherAgentData?.address_id;
            if (response.status == 'success') {
                // $(".shake-effect").effect("shake", { times: 0 }, 1200);
                cartHeader(address_id);
                if ($(`#added_button_href${product_id}`).length > 0) {
                    $(`#add_button_href${product_id}`).hide();
                    $(`#added_button_href${product_id}`).show();
                }
                $(that).next().show();
                $(that).next().find('.minus').attr('data-id', response.cart_product_id);
                $(that).next().find('.plus').attr('data-id', response.cart_product_id);
                $(that).next().find('.input_qty').attr('id', "quantity_ondemand_" + response.cart_product_id);
                $(that).next().find('.qty-minus-ondemand').attr('data-parent_div_id', "show_plus_minus" + response.cart_product_id);
                $(that).next().attr('id', "show_plus_minus" + response.cart_product_id);

                $(that).attr('id', "add_button_href" + response.cart_product_id);
                $(that).hide();
                $(that).next().show();

                let parentdiv = $(that).parents('.classes_wrapper');
                let addons_div = parentdiv.find('.addons-div');
                if (addonoptids.length >= 0) {

                    let addons_div = parentdiv.find('.addons-div');
                    addons_div.hide();
                }
            } else {
                //alert(response.message);
                Swal.fire({
                    // title: "Warning!",
                    text: response.message,
                    icon: "warning",
                    button: "OK",
                });
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
            Swal.fire({
                // title: "Warning!",
                text: error_messages,
                icon: "error",
                button: "OK",
            });
            // alert(error_messages);

        },
    });
}




// get time slots according to date
$(document).on('click', '.check-time-slots', function () {
    $('.check-time-slots').removeClass('ondemand_checked');
    let cur_date = $(this).val();
    let cart_product_id = $(this).data("cart_product_id");
    let product_vendor_id = $(this).data("product_vendor_id");
    let product_id = $(this).data("product_id");
    let product_tag = $(this).data("product_tag");
    let product_category_type = $(this).data("product_category_type");

    getTimeSlots(cur_date, cart_product_id, product_vendor_id, product_id, product_tag, product_category_type);

});

$(document).on('change', '.vendor_schedule_datetime, .vendor_schedule_slot', async function () {

    var task_type = 'schedule';
    let schedule_type = $(this).data("schedule_type");
    let cart_product_id = $(this).data("cart_product_id");
    let vendor_id = $(this).data("vendor_id");

    if (schedule_type != 'ProductDateTime') {
        if (schedule_type == 'date') {
            var schedule_dt = $(this).val();
            var schedule_time = $(this).closest('.vendor_slot_cart').find('select').find(':selected').val();

            $.ajax({
                type: "POST",
                dataType: 'json',
                url: check_schedule_slots,
                data: { date: schedule_dt, vendor_id: vendor_id },
                success: function (response) {

                    if (response.status == "Success") {
                        $('#vendor_schedule_slot_' + vendor_id).html(response.data);
                    } else {
                        success_error_alert('error', response.message, ".cart_response");
                        $('#vendor_schedule_slot_' + vendor_id).html(response.data);
                    }
                },
                error: function (error) {
                    var response = $.parseJSON(error.responseText);
                    success_error_alert('error', response.message, ".cart_response");
                    $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
                }

            });
        } else {
            var schedule_time = $(this).val();
            var schedule_dt = $(this).closest('.vendor_slot_cart').find('.vendor_schedule_datetime').val();
            $responst = await checkSlotAvailability(this);
            if ($responst == 0) {
                return false;
            }
        }
    } else {
        var schedule_dt = $(this).closest('.vendor_slot_cart').find('.vendor_schedule_datetime').val();
    }

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: update_cart_product_schedule,
        data: { task_type: task_type, schedule_dt: schedule_dt, schedule_time: schedule_time, cart_product_id: cart_product_id },
        success: function (response) {
            if (response.status == "Success") {
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            success_error_alert('error', response.message, ".cart_response");

        }
    });

});
// Check Slot Availability
async function checkSlotAvailability(obj) {
    var schedule_datetime = $(obj).closest('.vendor_slot_cart').find('.vendor_schedule_datetime').val();
    var schedule_slot = $(obj).val();
    var vendor_id = $(obj).data('vendor_id');
    var res = 0;
    var rep = await $.ajax({
        type: "GET",
        data: {
            "schedule_datetime": schedule_datetime,
            "schedule_slot": schedule_slot,
            "vendor_id": vendor_id,
        },
        url: checkSlotOrdersUrl,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (output) {
            // Check if orderCount is greaten equal to orders_per_slot //&& (output.orders_per_slot !=0)
            if (output.orderCount >= output.orders_per_slot && (output.orders_per_slot != 0)) {
                success_error_alert('error', 'All slots are full for the selected date & slot please choose another date or slot.', ".cart_response");
                // Disable the place order button
                $('#order_placed_btn').attr("disabled", true);
                res = 0;
            } else {
                $(".cart_response ").find(".alert").css("display", "none");
                // Enable the place order button
                $('#order_placed_btn').attr("disabled", false);
                res = 1;
            }
        },
        error: function (output) {
            // console.log(output);
            res = 0;
        },

    });
    return res;
}


$(document).on('click', '.selected-time', function () {

    let selected_time = $(this).data("value");
    let cart_product_id = $(this).data("cart_product_id");
    let dispatch_agent_id = '';
    let agent_ids = $(this).data("agent_ids");
    let show_agent = $(this).data("show_agent");

    //&& (show_agent != undefined && show_agent !='' )
    if ((agent_ids != undefined && agent_ids != '')) {

        dispatch_agent_id = agent_ids[0];
    }
    if ((show_agent != undefined && show_agent == 1) && (agent_ids != undefined && agent_ids != '')) {
        showDispatchDriver(agent_ids, cart_product_id, '');
    }
    $("#show_time" + cart_product_id).html(selected_time);
    $("#message_of_time" + cart_product_id).html("Your service will start between " + selected_time);
    $("#next-button-ondemand-3").show();
    selected_time = (dispatch_agent_id != '' && dispatch_agent_id != undefined) ? $(this).data("value") : selected_time;
    var task_type = 'schedule';
    //var schedule_date = $("#date_time_set_div" + cart_product_id + " input[name='booking_date" + cart_product_id + "']:checked").val();

    //var schedule_date = $("#date_time_set_div" + cart_product_id + " input[name='booking_date']:checked").val();
    var schedule_date = $(`input[name='booking_date_${cart_product_id}']:checked`).val();// $("input[name='booking_date']:checked").val();
    // var schedule_time = $(this).data("value");
    //var specific_instructions = $("#specific_instructions").val();
    // alert(specific_instructions);

    //var schedule_dt = schedule_date + ' ' + schedule_time;
    var schedule_dt = schedule_date;
    if ((task_type == 'schedule') && (schedule_dt == '' || schedule_dt == undefined)) {
        success_error_alert('error', 'Schedule date time is required', ".cart_response");
        return false;
    }

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: update_cart_product_schedule,
        data: { task_type: task_type, schedule_dt: schedule_dt, schedule_time: selected_time, cart_product_id: cart_product_id, dispatch_agent_id: dispatch_agent_id },
        success: function (response) {
            if (response.status == "Success") {
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            success_error_alert('error', response.message, ".cart_response");

        }
    });

});

$(document).delegate('#specific_instructions', 'blur focusout', function () {
    var specific_instructions = $("#specific_instructions").val();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: update_cart_schedule,
        data: { specific_instructions: specific_instructions },
        success: function (response) {
            if (response.status == "Success") {
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            success_error_alert('error', response.message, ".cart_response");

        }
    });

});

// on demand add to cart
function getTimeSlots(cur_date, cart_product_id, product_vendor_id, product_id, product_tag, product_category_type) {
    $("#show_date" + cart_product_id).html(cur_date);
    $.ajax({
        type: "post",
        dataType: "json",
        url: getTimeSlotsForOndemand,
        data: {
            "cur_date": cur_date,
            "cart_product_id": cart_product_id,
            "vendor_type": vendor_type,
            "product_vendor_id": product_vendor_id,
            "product_category_type": product_category_type,
            "product_id": product_id,
            "product_tag": product_tag
        },
        success: function (response) {
            var booking_time_slick = $("#show-all-time-slots" + cart_product_id).find('.booking-time');
            if (booking_time_slick.length > 0) {
                booking_time_slick.slick('unslick');
            }
            $("#show-all-time-slots" + cart_product_id).show();
            $('#show-all-time-slots' + cart_product_id).html(response);
            $("#show-all-time-slots" + cart_product_id + " .booking-time").slick({
                dots: !1,
                infinite: !0,
                speed: 300,
                slidesToShow: 4,
                slidesToScroll: 6,
                responsive: [
                    { breakpoint: 1367, settings: { slidesToShow: 4, slidesToScroll: 4, infinite: !0 } },
                    { breakpoint: 1024, settings: { slidesToShow: 4, slidesToScroll: 4, infinite: !0 } },
                    { breakpoint: 767, settings: { slidesToShow: 3, arrows: true, slidesToScroll: 3, infinite: !0 } },
                    { breakpoint: 480, settings: { slidesToShow: 2, arrows: true, slidesToScroll: 2 } },
                ],
            });
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            let error_messages = response.message;
            Swal.fire({
                // title: "Warning!",
                text: error_messages,
                icon: "error",
                button: "OK",
            });
            //  alert(error_messages);

        },
    });
}

/// ***************************************       END show cart data for on demand services    ***************************************************************/////////////////





// end on demand add to cart
$(document).delegate('.quantity-right-plus', 'click', function () {
    var quan = parseInt($('.quantity_count').val());
    var hasInv = $('#hasInventory').val();
    var str = $('#instock').val();
    $message = 'Only ' + str + ' is available for this product';
    var batch_count = $(this).data("batch_count");
    // var res = parseInt(str.substring(10, str.length - 1));
    if (batch_count > 0)
        batch_count = batch_count;
    else
        batch_count = 1;

    if ((quan + batch_count) > str && hasInv == "1") {
        Swal.fire({
            // title: "Warning!",
            text: $message,
            icon: "warning",
            button: "OK",
        });
        // alert("Quantity is not available in stock");
        $('.quantity_count').val(str);
    } else {
        var s = $(".qty-box .input-qty-number"),
            i = parseInt(s.val(), 10);
        isNaN(i) || s.val(i + batch_count);
    }
});
$(document).delegate(".quantity-left-minus", "click", function () {

    var batch_count = $(this).data("batch_count");
    var str = $('#instock').val();
    if (batch_count > 0 || str == '0')
        batch_count = batch_count;
    else
        batch_count = 1;

    var minimum_order_count = $(this).data("minimum_order_count");
    if (minimum_order_count > 0)
        minimum_order_count = minimum_order_count;
    else
        minimum_order_count = 1;
    // var res = parseInt(str.substring(10, str.length - 1));


    // console.log(minimum_order_count);

    var s = $(".qty-box .input-qty-number"),
        i = parseInt(s.val(), 10);

    if (i - batch_count < minimum_order_count) {
        Swal.fire({
            // title: "Warning!",
            text: "Minimum Quantity count is " + minimum_order_count,
            icon: "warning",
            button: "OK",
        });
        //alert("Minimum Quantity count is " + minimum_order_count);
        return false;
    }
    !isNaN(i) && i > 1 && s.val(i - batch_count);
});
$(document).delegate('.quantity_count', 'change', function () {
    var quan = $(this).val();
    var str = $('#instock').val();

    if (parseInt(quan) > parseInt(str)) {
        Swal.fire({
            // title: "Warning!",
            text: "Quantity is not available in stock",
            icon: "warning",
            button: "OK",
        });
        // alert("Quantity is not available in stock");
        $('.quantity_count').val(str);
    }
});


window.success_error_alert = function success_error_alert(responseClass, message, element) {
    $(element).find(".alert").html('');
    $(element).removeClass('d-none');
    if (responseClass == 'success') {
        if ($(element).find(".alert").length > 0) {
            $(element).find(".alert").html("<div class='alert-success p-1'>" + message + "</div>").show();
        } else {
            $(element).html(message).show();
        }
    } else if (responseClass == 'error') {
        if ($(element).find(".alert").length > 0) {
            $(element).find(".alert").html("<div class='alert-danger p-1'>" + message + "</div>").show();
        } else {
            $(element).html(message).show();
        }
    }
    $('html, body').animate({
        scrollTop: $(element).offset().top - 200
    }, 500);
    setTimeout(function () {
        $(element).addClass('d-none');
        $(element).find(".alert").hide();
    }, 8000);
}

$(document).on('click', '.prescription-doc-remove', function (e) {
    var prescriptionId = $(this).data("prescription_id");
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
    $.ajax({
        type: "post",
        headers: {
            Accept: "application/json"
        },
        url: get_product_prescription,
        dataType: 'json',
        data: { prescriptionId: prescriptionId, requestType: 'delete_prescription' },
        beforeSend: function () {
            $(".loader_box").show();
        },
        success: function (response) {
            if (response.status == 'success') {
                $(".modal .close").click();
                location.reload();
            }
        },
        complete: function () {
            $('.loader_box').hide();
        }
    });
});

$(document).on('click', '.prescription_btn', function (e) {
    e.preventDefault();
    $(".uploaded-prescription").html("");
    $(".uploaded-prescription-img").val(null);
    var cart = $(this).data("cart");
    var product = $(this).data("product");
    var vendor = $(this).data("vendor_id");
    var cart_product_prescription = $(this).data("cart_product_prescription");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
    $.ajax({
        type: "post",
        headers: {
            Accept: "application/json"
        },
        url: get_product_prescription,
        dataType: 'json',
        data: { cart: cart, product: product },
        beforeSend: function () {
            $(".loader_box").show();
        },
        success: function (response) {
            $("#product_id").val(product);
            $("#vendor_idd").val(vendor);
            $("#uploaded_pres_count").val(cart_product_prescription);

            // show-prescription-doc
            var showPrescriptionDoc = '';
            $.each(response, function (key, res) {
                showPrescriptionDoc += '<div class="show-prescription-close"><i class="fa fa-times prescription-doc-remove" data-prescription_id="' + res.id + '" aria-hidden="true"></i><img src="' + res.prescription.proxy_url + '50/50' + res.prescription.image_path + '" alt="product-img" height="60"></div>'
            });

            $(".show-prescription-doc").html(showPrescriptionDoc);
            $('#prescription_form').modal('show');
        },
        complete: function () {
            $('.loader_box').hide();
        }
    });
});

$(document).on('click', '.submitPrescriptionForm', function (e) {
    e.preventDefault();
    var form = document.getElementById('save_prescription_form');
    var formData = new FormData(form);
    var route_uri = "add/product/prescription";
    if (checkUploadFileLimit()) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            type: "post",
            headers: {
                Accept: "application/json"
            },
            url: route_uri,
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $(".loader_box").show();
            },
            success: function (response) {

                if (response.status == 'success') {
                    $(".modal .close").click();
                    location.reload();
                } else {
                    $(".show_all_error.invalid-feedback").show();
                    $(".show_all_error.invalid-feedback").text(response.message);
                }
                return response;
            },
            complete: function () {
                $('.loader_box').hide();
            }
        });
    }
});

function checkUploadFileLimit() {
    var limit = 5;
    var uploaded_prescription_count = $('.uploaded-prescription img').length;
    var already_uploaded = $('#uploaded_pres_count').val();
    var total_files = parseInt(uploaded_prescription_count) + parseInt(already_uploaded);
    if (total_files > limit) {
        $('#save_prescription_form .validate-file-error').text("You can select max " + limit + " file.");
        $('#prescription_file').val('');
        return false;
    } else {
        $('#save_prescription_form .validate-file-error').text("");
        return true;
    }
}

//prescription upload for bidding

// $(document).on('click', '.prescription-doc-remove', function (e) {
//     var prescriptionId = $(this).data("prescription_id");
//     $.ajaxSetup({
//         headers: {
//             'X-CSRF-TOKEN': $('input[name="_token"]').val()
//         }
//     });
//     $.ajax({
//         type: "post",
//         headers: {
//             Accept: "application/json"
//         },
//         url: get_product_prescription,
//         dataType: 'json',
//         data: {prescriptionId:prescriptionId,requestType:'delete_prescription'},
//         beforeSend: function () {
//             $(".loader_box").show();
//         },
//         success: function (response) {
//             if (response.status == 'success') {
//                 $(".modal .close").click();
//                 location.reload();
//             }
//         },
//         complete: function () {
//             $('.loader_box').hide();
//         }
//     });
// });

// $(document).on('click', '.bid_prescription_btn', function (e) {
//     e.preventDefault();
//     $(".uploaded-bidding-prescription").html("");
//     $(".uploaded-bidding-prescription-img").val(null);
//     var ID = $(this).data("id");
//     $.ajaxSetup({
//         headers: {
//             'X-CSRF-TOKEN': $('input[name="_token"]').val()
//         }
//     });
//     $.ajax({
//         type: "post",
//         headers: {
//             Accept: "application/json"
//         },
//         url: get_bid_prescription,
//         dataType: 'json',
//         data: {id:ID},
//         beforeSend: function () {
//             $(".loader_box").show();
//         },
//         success: function (response) {

//             // show-prescription-doc
//             var showPrescriptionDoc = '';
//             $.each(response, function (key, res) {
//                 showPrescriptionDoc += '<div class="show-prescription-close"><i class="fa fa-times prescription-doc-remove" data-prescription_id="'+res.id+'" aria-hidden="true"></i><img src="'+res.prescription.proxy_url+'50/50'+res.prescription.image_path+'" alt="product-img" height="60"></div>'
//             });

//             $(".show-bid_prescription-doc").html(showPrescriptionDoc);
//             $('#bid_prescription_form').modal('show');
//         },
//         complete: function () {
//             $('.loader_box').hide();
//         }
//     });
// });

// $(document).on('click', '.submitBidPrescriptionForm', function (e) {
//     e.preventDefault();
//     var form = document.getElementById('savebidprescriptionform');
//     var formData = new FormData(form);
//     var route_uri = "add/bid/prescription";

//     $.ajaxSetup({
//         headers: {
//             'X-CSRF-TOKEN': $('input[name="_token"]').val()
//         }
//     });
//     $.ajax({
//         type: "post",
//         headers: {
//             Accept: "application/json"
//         },
//         url: route_uri,
//         data: formData,
//         contentType: false,
//         processData: false,
//         beforeSend: function () {
//             $(".loader_box").show();
//         },
//         success: function (response) {

//             if (response.status == 'success') {
//                 $(".modal .close").click();
//                 location.reload();
//             } else {
//                 $(".show_all_error.invalid-feedback").show();
//                 $(".show_all_error.invalid-feedback").text(response.message);
//             }
//             return response;
//         },
//         complete: function () {
//             $('.loader_box').hide();
//         }
//     });

// });

$(document).on('click', '#tasknow', function () {
    //$('#schedule_div').attr("style", "display: none !important");
});
$(document).on('click', '#taskschedule', function () {
    // $('#schedule_div').attr("style", "display: flex !important");
});
// var x = document.getElementById("schedule_div").autofocus;


///////////////// tip after order place /////////////////////////////////

window.creditTipAfterOrder = function creditTipAfterOrder(amount, payment_option_id, transaction_id, order_number) {
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: credit_tip_url,
        data: { tip_amount: amount, payment_option_id: payment_option_id, transaction_id: transaction_id, order_number: order_number },
        success: function (response) {
            // var currentUrl = window.location.href;
            location.href = path;
            if (response.status == "Success") {
                // $("#topup_wallet").modal("hide");
                // $(".table.wallet-transactions table-body").html('');
                $(".wallet_balance").text(response.data.wallet_balance);
                success_error_alert('success', response.message, "#wallet_response");
                // let wallet_transactions_template = _.template($('#wallet_transactions_template').html());
                // $(".table.wallet-transactions table-body").append(wallet_transactions_template({wallet_transactions:response.data.transactions}));
            } else {
                $("#wallet_response .message").removeClass('d-none');
                success_error_alert('error', response.message, "#wallet_response .message");
                $("#topup_wallet_btn, .topup_wallet_confirm").attr("disabled", false);
            }
        },
        error: function (error) {
            var response = $.parseJSON(error.responseText);
            $("#wallet_response .message").removeClass('d-none');
            success_error_alert('error', response.message, "#wallet_response .message");
            $("#topup_wallet_btn, .topup_wallet_confirm").removeAttr("disabled");
        },
        complete: function (data) {
            $('.spinner-overlay').hide();
        }
    });
}

// *****************************  End tip after order place ****************************///

$(document).on('click', '.validate_promo_code_btn', function () {
    let amount = $(this).attr('data-amount');
    let cart_id = $(this).attr('data-cart_id');
    let vendor_id = $(this).attr('data-vendor_id');
    let promocode = $(document).find('.manual_promocode_input').val();
    if (promocode && promocode != "") {
        // let coupon_id = $(this).data('coupon_id');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: validate_promocode_coupon_url,
            data: { cart_id: cart_id, vendor_id: vendor_id, amount: amount, promocode: promocode },
            success: function (response) {
                if (response.status == "Success") {
                    $('.validate_promo_div').find('.apply_promo_code_btn').attr('data-amount', amount);
                    $('.validate_promo_div').find('.apply_promo_code_btn').attr('data-cart_id', cart_id);
                    $('.validate_promo_div').find('.apply_promo_code_btn').attr('data-vendor_id', vendor_id);
                    $('.validate_promo_div').find('.apply_promo_code_btn').attr('data-coupon_id', response.data.id);
                    $('.validate_promo_div').find('.apply_promo_code_btn').trigger('click');
                    $('#refferal-modal').modal('hide');
                    cartHeader();
                }
            },
            error: function (reject) {
                if (reject.status === 422) {
                    var message = $.parseJSON(reject.responseText);
                    sweetAlert.error(message.message, "");
                    $(".invalid-feedback.manual_promocode").html("<strong>" + message.message + "</strong>");
                }
            }
        });
    } else {
        sweetAlert.error("Enter a Promocode", "");
        $(".invalid-feedback.manual_promocode").html("<strong>Please enter promocode</strong>");
    }
});


function subscriptionPaymentOPtions(payment_option_id) {
    switch (payment_option_id) {

        case 3:
            paymentViaPaypal('', payment_option_id);
            break;

        case 4:
            stripe.createSource(card).then(function (result) {
                if (result.error) {
                    alert("as");
                    $('#stripe_card_error').html(result.error.message);
                    $("#subscription_confirm_btn").attr("disabled", false);
                } else {
                    paymentAjaxData.payment_option_id = payment_option_id;
                    var three_d_secure = result.source.card.three_d_secure;
                    // if(three_d_secure == 'required'){
                    stripe.createPaymentMethod({
                        type: 'card',
                        card: card,
                    }).then(stripePaymentMethodHandler);
                    // }else{
                    //     stripe.createToken(card).then(function(result) {
                    //         if (result.error) {
                    //             $('#stripe_card_error').html(result.error.message);
                    //             $(".subscription_confirm_btn").attr("disabled", false);
                    //         } else {
                    //             $("#card_last_four_digit").val(result.token.card.last4);
                    //             $("#card_expiry_month").val(result.token.card.exp_month);
                    //             $("#card_expiry_year").val(result.token.card.exp_year);
                    //             paymentViaStripe(result.token.id, '', payment_option_id, '', '');
                    //         }
                    //     });
                    // }
                }
            });

            // stripe.createToken(card).then(function (result) {
            // if (result.error) {
            //     $('#stripe_card_error').html(result.error.message);
            //     _this.attr("disabled", false);
            // } else {
            //     $("#card_last_four_digit").val(result.token.card.last4);
            //     $("#card_expiry_month").val(result.token.card.exp_month);
            //     $("#card_expiry_year").val(result.token.card.exp_year);
            //     paymentViaStripe(result.token.id, '', payment_option_id, '', '');
            // }
            // });

            break;

        case 8:

            inline.createToken().then(function (result) {
                if (result.error) {
                    $('#yoco_card_error').html(result.error.message);
                    _this.attr("disabled", false);
                } else {
                    const token = result;
                    paymentViaYoco(token.id, '', '');
                }
            }).catch(function (error) {
                // Re-enable button now that request is complete
                _this.attr("disabled", false);
                //alert("error occured: " + error);
                Swal.fire({
                    // title: "Warning!",
                    text: "error occured: " + error,
                    icon: "error",
                    button: "OK",
                });
            });

            break;

        case 9:
            paymentViaPaylink('', '');
            break;

        case 10:
            paymentViaRazorpay_wallet('', payment_option_id);
            break;

        case 12:
            paymentViaSimplify('', '');
            break;

        case 13:
            paymentViaSquare('', '');
            break;

        case 14:
            paymentViaOzow('', '');
            break;

        case 15:
            paymentViaPagarme('', '');
            break;

        case 17:
            paymentViaCheckout('', '');
            break;

        case 18:
            paymentViaAuthorize('', '');
            break;

        case 19:
            paymentViaStripeFPX('', 19, '');
            break;

        case 20:
            payWithKPG('');
            break;

        case 21:
            payWithVivaWallet('');
            break;

        case 22:
            payWithCcAvenue('');
            break;

        case 23:
            paymentViaEasyPaisaPay('');
            break;

        case 24:
            paymentViaCashfree('');
            break;

        case 25:
            payWithEasebuss('');
            break;

        case 26:
            paymentViaToyyibPay('');
            break;

        case 27:
            paymentViaPaytab('', '');
            break;

        case 29:
            payWithMvodafone('', '');
            break;

        case 30:
            payWithFlutterWave('', '');
            break;

        case 32:
            payphoneButton('', '');
            break;

        case 33:
            paymentViaBraintree('');
            break;

        case 34:
            payWithWindcave('', '');
            break;

        case 35:
            payWithPaytech('', '');
            break;

        case 36:
            paymentViaMyCash('', payment_option_id, '');
            break;

        case 37:
            stripeOXXOInitialize();
            paymentViaStripeOXXO('', payment_option_id, '');
            break;

        case 39:
            paymentViaStripeIdeal('', payment_option_id, '');
            break;
        case 40:
            paymentViaUseRede('', payment_option_id, '');
            break;
        case 41:
            payWithOpenPay('', payment_option_id, '');
            break;
        case 42:
            paymentViaDpoSubscription('', payment_option_id, '');
            break;
        case 43:
            paymentViaUPay('', payment_option_id, '');
            break;
        case 44:
            paymentViaConekta('', payment_option_id, '');
            break;
        case 45:
            paymentViaTelr('', payment_option_id, '');
            break;
        case 46:
            paymentViaMastercard('subscription');
        case 47:
            paymentViaKhalti('', '');
            break;
        case 48:
            paymentViaMtnMomo('', payment_option_id, '');
            break;
        case 49:
            paymentViaplugnpay('', payment_option_id, '');
            break;
        case 50:
            paymentViazulpay('', payment_option_id, '');
            break;

        case 52:
            paymentViaSkipCash('', payment_option_id, '');
            break;

        case 55:
            paymentViaDataTrans('', payment_option_id, '');
            break;
        case 56:
            paymentViaOboPay('', payment_option_id);
            break;

        case 57:
            payWithPesapal(payment_option_id, '');
            break;

        case 58:
            payWithPowerTrans(payment_option_id, '');
            break;
        case 59:
            payWithLivees(payment_option_id);
            break;
        case 62:
            paymentViaMpesaSafari('', payment_option_id, '');
            break;
        case 65:
            paymentViaTotalpay('', payment_option_id, '');
            break;
        case 67:
            paymentViaThawanipg('', payment_option_id, '');
            break;
        case 69:
            paymentViaHitpay('', payment_option_id, '');
            break;
        case 70:
            paymentViaCyberSourcePay('', payment_option_id, '');
            break;
        case 71:
            paymentViaOrangePay('', payment_option_id, '');
            break;

    }

}

function cartPaymentOptions(payment_option_id, address_id, tip, delivery_type) {
    var action = payment_option_id;
    switch (action) {
        case '3':
            paymentViaPaypal(address_id, payment_option_id);
            break;

        case '4':
            stripe.createSource(card).then(function (result) {
                if (result.error) {
                    $('#stripe_card_error').html(result.error.message);
                    $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
                } else {
                    var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
                    if (order != '') {
                        paymentAjaxData.address_id = address_id;
                        paymentAjaxData.order_number = order.order_number;
                        paymentAjaxData.payment_option_id = payment_option_id;

                        // localStorage.setItem('order_number', order.order_number);
                        // localStorage.setItem('payment_option', payment_option_id);
                        var three_d_secure = result.source.card.three_d_secure;
                        // if(three_d_secure == 'required'){
                        stripe.createPaymentMethod({
                            type: 'card',
                            card: card,
                        }).then(stripePaymentMethodHandler);
                        // paymentViaStripeSource(result.source.id, address_id, payment_option_id,delivery_type, order);
                        // }else{
                        //     stripe.createToken(card).then(function(result) {
                        //         if (result.error) {
                        //             $('#stripe_card_error').html(result.error.message);
                        //             $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
                        //         } else {
                        //             paymentViaStripe(result.token.id, address_id, payment_option_id, delivery_type, order);
                        //         }
                        //     });
                        // }
                    } else {
                        return false;
                    }
                }
            });

            break;

        case '5':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaPaystack(address_id, order);
            } else {
                return false;
            }
            // paymentViaPaystack(address_id, payment_option_id);
            break;

        case '6':
            paymentViaPayfast(address_id, payment_option_id);
            break;

        case '7':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaMobbex(address_id, order);
            } else {
                return false;
            }
            break;

        case '8':
            var order;
            inline.createToken().then(function (result) {
                if (result.error) {

                    $('#yoco_card_error').html(result.error.message);
                    $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
                } else {
                    const token = result;
                    // alert("card successfully tokenised: " + token.id);
                    payment_option_id = 8;

                    order = placeOrderBeforePayment(address_id, payment_option_id, tip);
                    if (order != '') {
                        paymentViaYoco(token.id, address_id, order);
                    } else {
                        return false;
                    }
                }
            }).catch(function (error) {
                // Re-enable button now that request is complete
                //alert("error occured: " + error);
                Swal.fire({
                    // title: "Warning!",
                    text: "error occured: " + error,
                    icon: "error",
                    button: "OK",
                });
            });

            break;

        case '9':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaPaylink(address_id, order);
            } else {
                return false;
            }
            break;

        case '10':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaRazorpay(address_id, order, 'cart');
            } else {
                return false;
            }
            break;

        case '12':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaSimplify(address_id, order);
            } else {
                return false;
            }
            break;

        case '13':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaSquare(address_id, order);
            } else {
                return false;
            }
            break;

        case '14':
            paymentViaOzow('', '');
            break;

        case '15':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaPagarme(address_id, order);
            } else {
                return false;
            }
            break;

        case '17':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaCheckout(address_id, order);
            } else {
                return false;
            }
            break;

        case '18':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaAuthorize(address_id, order);
            } else {
                return false;
            }
            break;

        case '19':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaStripeFPX(address_id, payment_option_id, order);
            }
            else {
                return false;
            }
            break;

        case '20': //Kongapay
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {

                payWithKPG(order);
            }
            else {
                return false;
            }
            break;

        case '21':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //Viva Wallet
                payWithVivaWallet(order);
            }
            else {
                return false;
            }
            break;

        case '22':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                payWithCcAvenue(order);
            }
            else {
                return false;
            }
            break;

        case '23':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaEasyPaisaPay(order);
            }
            else {
                return false;
            }
            break;

        case '24':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaCashfree(address_id, payment_option_id, order);
            }
            else {
                return false;
            }
            break;

        case '25':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //Easebuzz payment gateway
                payWithEasebuss(address_id, payment_option_id, order);
            } else {
                return false;
            }
            break;

        case '26':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //alert(12345677888);
                paymentViaToyyibPay(address_id, payment_option_id, order);
            }
            else {
                return false;
            }


            break;

        case '27':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaPaytab(address_id, order);
            } else {
                return false;
            }


            break;

        case '28':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //Easebuzz payment gateway
                payWithVNpay(address_id, payment_option_id, order);
            }
            else {
                return false;
            }


            break;

        case '29':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //Mvodafone
                payWithMvodafone(order);

            }
            else {
                return false;
            }
            break;

        case '30':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //FlutterWave
                payWithFlutterWave(order);
            }
            else {
                return false;
            }
            break;

        case '31':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //PayU
                payWithPayU(address_id, payment_option_id, order);
            }
            else {
                return false;
            }
            break;

        case '32':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //payphoneButton
                payphoneButton(order);
            }
            else {
                return false;
            }
            break;

        case '33':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaBraintree(address_id, order);
            } else {
                return false;
            }
            break;

        case '34':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                //payWithWindcave
                payWithWindcave(order);
            }
            else {
                return false;
            }
            break;

        case '35':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                payWithPaytech(order);
            }
            else {
                return false;
            }
            break;

        case '36':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaMyCash(address_id, payment_option_id, order);
            }
            else {
                return false;
            }
            break;

        case '37':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                stripeOXXOInitialize();
                paymentViaStripeOXXO(address_id, payment_option_id, order);
            }
            else {
                return false;
            }
            break;

        case '39':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaStripeIdeal(address_id, payment_option_id, order);
            }
            else {
                return false;
            }
            break;
        case '40':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaUseRede(address_id, payment_option_id, order);
            }
            else {
                return false;
            }
            break;
        case '41':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                payWithOpenPay(address_id, payment_option_id, order);
            } else {
                return false;
            }
            break;
        case '42':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            // console.log('order', order);
            if (order != '') {
                //payWithDpo
                payWithDpo(order);
            }
            else {
                return false;
            }
            break;
        case '43':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            // console.log('order', order);
            if (order != '') {
                paymentViaUPay(address_id, order);
            }
            else {
                return false;
            }
            break;
        case '44':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaConekta(address_id, order);
            }
            else {
                return false;
            }
            break;
        case '45':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaTelr(address_id, order);
            }
            else {
                return false;
            }
            break;

        case '46': {
            let order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            paymentViaMastercard('cart', order);
            break;
        }

        case '47':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaKhalti(address_id, order, payment_from = 'cart');
            }
            else {
                return false;
            }
            break;
        case '48':

            //console.log('address_id',address_id,'payment_option_id',payment_option_id,'tip',tip);
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaMtnMomo(address_id, order, payment_from = 'cart');
            }
            else {
                return false;
            }
            break;

        case '49':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaplugnpay(address_id, payment_option_id, order);
            }
            else {
                return false;
            }
            break;

        case '50':
            if (creditCardValidation()) {
                var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
                if (order != '') {
                    paymentViazulpay(address_id, payment_option_id, order);
                }
                else {
                    return false;
                }
            } else {
                $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
            }
            break;
        case '52':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaSkipCash(address_id, order);
            } else {
                return false;
            }
            break;

        case '53':

            cardJson = {
                'cno': $('#card-element-nmi').val(),
                'dt': $('#date-element-nmi').val(),
                'cv': $('#cvv-element-nmi').val(),
                'name': 'nmi',
            }
            if (cardValidation(cardJson)) {
                var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
                if (order != '') {
                    paymentNmipay(address_id, payment_option_id, order, cardJson);
                }
                else {
                    return false;
                }
            } else {
                $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
            }
            break;

        case '55':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaDataTrans(address_id, payment_option_id, order);
            }
            break;

        case '56':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaOboPay(address_id, payment_option_id, order);
            } else {
                return false;
            }
            break;

        case '57':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                payWithPesapal(payment_option_id, order);
            } else {
                return false;
            }
            break;

        case '58':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                payWithPowerTrans(payment_option_id, order);
            }
            break;
        case '59':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                payWithLivees(address_id, payment_option_id, order);
            }
            break;
        case '62':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaMpesaSafari(address_id, payment_option_id, order);
            }
            break;
        case '65':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaTotalpay(address_id, payment_option_id, order);
            }
            break;
        case '67':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaThawanipg(address_id, payment_option_id, order);
            } else {
                return false;
            }
            break;
        case '69':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaHitpay(address_id, payment_option_id, order);
            }
            break;
        case '70':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaOranngepay('', payment_option_id, '');
                paymentViaCyberSourcePay('', payment_option_id, '');
            }
            break;
        case '71':
            var order = placeOrderBeforePayment(address_id, payment_option_id, tip);
            if (order != '') {
                paymentViaOrangePay('', payment_option_id, '');
            }
            break;
    }

}


function walletPaymentOPtions(payment_option_id) {

    switch (payment_option_id) {
        case 3:
            paymentViaPaypal('', payment_option_id);
            break;

        case 4:
            stripe.createSource(card).then(function (result) {
                if (result.error) {
                    $('#stripe_card_error').html(result.error.message);
                    $("#order_placed_btn, .proceed_to_pay").attr("disabled", false);
                } else {
                    paymentAjaxData.payment_option_id = payment_option_id;
                    var three_d_secure = result.source.card.three_d_secure;
                    // if(three_d_secure == 'required'){
                    stripe.createPaymentMethod({
                        type: 'card',
                        card: card,
                    }).then(stripePaymentMethodHandler);
                    // paymentViaStripeSource(result.source.id, address_id, payment_option_id,delivery_type, order);

                }
            });

            break;

        case 5:
            paymentViaPaystack();
            break;

        case 6:
            paymentViaPayfast();
            break;

        case 7:

            break;

        case 8:
            inline.createToken().then(function (result) {
                if (result.error) {
                    $('#yoco_card_error').html(result.error.message);
                    $(".topup_wallet_confirm").attr("disabled", false);
                } else {
                    const token = result;
                    paymentViaYoco(token.id, '', '');
                }
            }).catch(function (error) {
                // Re-enable button now that request is complete
                Swal.fire({
                    // title: "Warning!",
                    text: "error occured: " + error,
                    icon: "error",
                    button: "OK",
                });

            });

            break;

        case 9:
            paymentViaPaylink('', '');
            break;

        case 10:
            paymentViaRazorpay_wallet('', payment_option_id);
            break;

        case 11:
            paymentViaGCash('', '');
            break;

        case 12:
            paymentViaSimplify('', '');
            break;

        case 13:
            paymentViaSquare('', '');
            break;

        case 14:
            paymentViaOzow('', '');
            break;

        case 15:
            paymentViaPagarme('', '');
            break;

        case 17:
            paymentViaCheckout('', '');
            break;

        case 18:
            paymentViaAuthorize('', '');
            break;

        case 19:
            paymentViaStripeFPX('', payment_option_id, '');
            break;

        case 20: //Kongapay
            payWithKPG('');
            break;

        case 21:
            payWithVivaWallet('');
            break;

        case 22:
            payWithCcAvenue('');
            break;

        case 23:
            paymentViaEasyPaisaPay('');
            break;

        case 24:
            paymentViaCashfree('', payment_option_id, '');
            break;

        case 25:
            payWithEasebuss('', payment_option_id, '');
            break;

        case 26:
            paymentViaToyyibPay('', payment_option_id, '');
            break;

        case 27:
            paymentViaPaytab('', payment_option_id, '');
            break;

        case 28:
            payWithVNpay('', payment_option_id, '');
            break;

        case 29:
            payWithMvodafone('', payment_option_id, '');
            break;

        case 30:
            payWithFlutterWave('', payment_option_id, '');
            break;

        case 32:
            payphoneButton('', payment_option_id, '');
            break;

        case 33:
            paymentViaBraintree('', payment_option_id, '');
            break;

        case 34:
            payWithWindcave('', payment_option_id, '');
            break;

        case 35:
            payWithPaytech('', payment_option_id, '');
            break;

        case 36:
            paymentViaMyCash('', payment_option_id, '');
            break;

        case 37:
            stripeOXXOInitialize();
            paymentViaStripeOXXO('', payment_option_id, '');
            break;

        case 39:
            paymentViaStripeIdeal('', payment_option_id, '');
            break;
        case 40:
            paymentViaUseRede('', payment_option_id, '');
            break;
        case 41:
            payWithOpenPay('', payment_option_id, '');
            break;
        case 42:
            paymentViaDpo('', payment_option_id, '');
            break;
        case 43:
            paymentViaUPay('', payment_option_id, '');
            break;
        case 44:
            paymentViaConekta('', payment_option_id, '');
            break;
        case 45:
            paymentViaTelr('', payment_option_id, '');
            break;
        case 46:
            paymentViaMastercard('wallet');
            break;
        case 47:
            paymentViaKhalti('', '');
            break;
        case 48:
            paymentViaMtnMomo('', payment_option_id, '');
            break;
        case 49:
            paymentViaplugnpay('', payment_option_id, '');
            break;
        case 50:
            paymentViazulpay('', payment_option_id, '');
            break;

        case 52:
            paymentViaSkipCash('', payment_option_id, '');
            break;

        case 53:
            cardJson = {
                'cno': $('#card-element-nmi').val(),
                'dt': $('#date-element-nmi').val(),
                'cv': $('#cvv-element-nmi').val(),
                'name': 'nmi',
            }
            if (cardValidation(cardJson)) {
                paymentNmipay('', payment_option_id, '', cardJson);
            }

            break;

        case 55:
            paymentViaDataTrans('', payment_option_id, null);
            break;

        case 56:
            paymentViaOboPay('', payment_option_id);

        case 57:
            payWithPesapal(payment_option_id, '');
            break;

        case 58:
            payWithPowerTrans(payment_option_id, '');
            break;
        case 59:
            payWithLivees(payment_option_id, payment_from = 'wallet');
            break;
        case 62:
            paymentViaMpesaSafari('', payment_option_id, '');
            break;
        case 65:
            paymentViaTotalpay('', payment_option_id, '');
            break;
        case 67:
            paymentViaThawanipg('', payment_option_id, '');
            break;
        case 69:
            paymentViaHitpay('', payment_option_id, '');
            break;
        case 70:
            paymentViaCyberSourcePay('', payment_option_id, '');
            break;
        case 71:
            paymentViaOrangePay('', payment_option_id, '');
            break;
    }
}

//});

function numberWithCommas(x) {
    // x=x.toFixed(2)
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

$(".related-css").slick({ dots: !1, infinite: !0, speed: 300, slidesToShow: 4, centerMode: !0, centerPadding: "20px", slidesToScroll: 4, arrows: !0, responsive: [{ breakpoint: 1200, settings: { slidesToShow: 3, slidesToScroll: 3 } }, { breakpoint: 991, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }, { breakpoint: 767, settings: { slidesToShow: 2, arrows: !0, slidesToScroll: 2 } }] });

$('#add_edit_address').on('hidden.bs.modal', function () {
    $("html").removeClass("intro");
});

$(".add_edit_address_btn").click(function () {
    $("html").addClass("intro");
});


// nine template slider
$('.categories_slider').slick({
    slidesToShow: 7,
    slidesToScroll: 1,
    arrows: true,
    dots: false,
    speed: 300,
    infinite: true,
    autoplaySpeed: 5000,
    autoplay: true,
    responsive: [
        {
            breakpoint: 991,
            settings: {
                slidesToShow: 4,
            }
        },
        {
            breakpoint: 767,
            settings: {
                slidesToShow: 3,
            }
        },
        {
            breakpoint: 576,
            settings: {
                slidesToShow: 2,
            }
        }
    ]
});

$('.featured_slider').slick({
    slidesToShow: 5,
    slidesToScroll: 1,
    arrows: true,
    dots: false,
    speed: 300,
    infinite: true,
    autoplaySpeed: 5000,
    autoplay: true,
    responsive: [
        {
            breakpoint: 1440,
            settings: {
                slidesToShow: 4,
            }
        },
        {
            breakpoint: 991,
            settings: {
                slidesToShow: 3,
            }
        },
        {
            breakpoint: 767,
            settings: {
                slidesToShow: 2,
            }
        },
        {
            breakpoint: 576,
            settings: {
                slidesToShow: 1,
            }
        }
    ]
});




$('.category_responsive').slick({
    dots: false,
    infinite: false,
    speed: 300,
    slidesToShow: 6,
    slidesToScroll: 1,
    responsive: [
        {
            breakpoint: 1024,
            settings: {
                slidesToShow: 5,
                slidesToScroll: 1,
                infinite: true,
                dots: false
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1
            }
        }
    ]
});




if (typeof action_type !== "undefined" && action_type === "p2p") {
    const initReadMore = new readMore();
    initReadMore.bootstrap();
}


AOS.init({ disable: 'mobile' });
