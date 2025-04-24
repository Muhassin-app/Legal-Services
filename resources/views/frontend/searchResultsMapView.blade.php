@extends('layouts.store', ['title' => "Search Results"])
@section('css')
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/common/icons.min.css')}}">
<style type="text/css">
.main-menu .brand-logo{display:inline-block;padding-top:20px;padding-bottom:20px}.slick-track{margin-left:0}.product-box .product-detail h4,.product-box .product-info h4{font-size:16px}
body a.btn.btn-solid.col-2.al-show-vendor-map-btn {height: 37px;padding: 0 !important;line-height: 37px;border-radius: 90px;text-align: center;}
.gm-style-iw.gm-style-iw-c {
    width: 300px ;
    padding: 12px ;
    position: absolute;
    box-sizing: border-box;
    overflow: hidden;
    top: 0;
    left: 0;
    transform: translate3d(-50%,-100%,0);
    background-color: white;
    border-radius: 8px;
    padding: 12px;
    box-shadow: 0 2px 7px 1px rgb(0 0 0 / 30%);
}
.gm-style .gm-style-iw-d {
    overflow: hidden !important;
}
.img_box {
    width: 100%;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
}
.img_box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.user_name label {
    font-size: 14px;
    color: #000;
    text-transform: capitalize;
    margin: 0 0 12px !important;
    display: block;
}
.user_info span, .user_info b {
    font-size: 12px;
    font-weight: 500;
}
.user_info i {
    font-size: 14px;
    width: 20px;
    text-align: center;
    color: #6658dd;
}
.gm-style-iw-d b.d-block.mb-2 {
    display: flex !important;
    vertical-align: middle;
    align-items: start;
    padding-right:10px;
}
.gm-style-iw-d b.d-block.mb-2 i {
    margin-right: 8px;
    margin-top: 3px;
    padding-left: 5px;
}
</style>
<link rel="stylesheet" type="text/css" href="{{asset('front-assets/css/price-range.css')}}">
@endsection
@section('content')

<section class="section-b-space ratio_asos alPageSearchView">
    <div class="collection-wrapper">
        <div class="container">
            <div class="collection-content my-4">
                <div class="page-main-content">
                    <div class="collection-product-wrapper w-100">
                        
                        <h4>Showing Results for "{{$keyword}}"</h4>
                        <div class="displayProducts">
                            <div class="product-wrapper-grid">
                                <div class="googleMapArea col-md-12 p-0">
                                    <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d13720.904154980397!2d76.81441854999998!3d30.71204525!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1657101273720!5m2!1sen!2sin" width="100%" height="550" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe> -->
                                    <div id="vendor-map-container">
                                        <div id="vendor-map" class="w-100" style="height:400px"></div>
                                    </div>
                                </div>
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
<script src="{{asset('front-assets/js/rangeSlider.min.js')}}"></script>
<script src="{{asset('front-assets/js/my-sliders.js')}}"></script>
<script>
    

</script>
@endsection