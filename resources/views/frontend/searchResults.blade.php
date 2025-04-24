@extends('layouts.store', ['title' => "Search Results"])
@section('css')
<link defer type="text/css" rel="stylesheet"  href="{{asset('frontend/common/icons.min.css')}}">
<style type="text/css">
.main-menu .brand-logo{display:inline-block;padding-top:20px;padding-bottom:20px}.slick-track{margin-left:0}.product-box .product-detail h4,.product-box .product-info h4{font-size:16px}
body a.btn.btn-solid.col-2.al-show-vendor-map-btn {height: 37px;padding: 0 !important;line-height: 37px;border-radius: 90px;text-align: center;}
.alPageSearchView .product-box .img-wrapper {margin:0;height:100%;}
.alPageSearchView .product-box .img-wrapper .front {
    display: block;
    width: 100%;
    height: 100%;
}
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
                        <div class="product-top-filter">
                            <div class="filter-main-btn">
                                <span class="filter-btn btn btn-theme">
                                    <i class="fa fa-filter" aria-hidden="true"></i>{{__('Filter')}}
                                </span>
                            </div>
                            <div class="product-filter-content">
                                {{-- <div class="collection-view">
                                    <ul>
                                        <li><i class="fa fa-th grid-layout-view"></i></li>
                                        <li><i class="fa fa-list-ul list-layout-view"></i></li>
                                    </ul>
                                </div> --}}
                                {{-- <div class="collection-grid-view">
                                    <ul>
                                        <li><img src="{{asset('front-assets/images/icon/2.png')}}" alt="" class="product-2-layout-view"></li>
                                        <li><img src="{{asset('front-assets/images/icon/3.png')}}" alt="" class="product-3-layout-view"></li>
                                        <li><img src="{{asset('front-assets/images/icon/4.png')}}" alt="" class="product-4-layout-view"></li>
                                        <li><img src="{{asset('front-assets/images/icon/6.png')}}" alt="" class="product-6-layout-view"></li>
                                    </ul>
                                </div> --}}
                                {{-- <div class="product-page-per-view">
                                    <?php $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 8; ?>
                                    <select class="customerPaginate">
                                        <option value="8" @if($pagiNate==8) selected @endif>Show 8
                                        </option>
                                        <option value="12" @if($pagiNate==12) selected @endif>Show 12
                                        </option>
                                        <option value="24" @if($pagiNate==24) selected @endif>Show 24
                                        </option>
                                        <option value="48" @if($pagiNate==48) selected @endif>Show 48
                                        </option>
                                    </select>
                                </div> --}}
                            </div>

                        </div>
                        <h4>Showing Results for "{{$keyword}}"</h4>
                        <div class="displayProducts">
                            <div class="product-wrapper-grid">
                                @if($vendorMapView == 1)
                                <div class="googleMapArea col-md-12 p-0">
                                    <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d13720.904154980397!2d76.81441854999998!3d30.71204525!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sin!4v1657101273720!5m2!1sen!2sin" width="100%" height="550" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe> -->
                                    <div id="vendor-map-container">
                                        <div id="vendor-map" class="w-100" style="height:400px"></div>
                                    </div>
                                </div>
                                @endif

                                    @if(!empty($listData))
                                    @foreach($listData as $key => $result)
                                    @if(@$result['title'])
                                    <div class="row margin-res">
                                    <div class="col-md-12 col-12 mt-3">
                                        <!-- <div class="product-box scale-effect mt-0">

                                            <div class="product-detail"> -->
                                                <div class="inner_spacing search-heading">
                                                        <h3>{{__($result['title'])}}</h3>
                                                <!-- </div>
                                            </div> -->
                                        </div>
                                    </div>
                                    </div>
                                    @endif
                                    <div class="row margin-res mt-3">
                                        @foreach($result['result'] as $data)
                                        <div class="col-md-2 col-6 col-grid-box mt-3">
                                            <div class="product-box scale-effect mt-0">
                                                <div class="img-wrapper">
                                                    <div class="front">
                                                        @php
                                                            if(empty($data['image_url'])){
                                                                $data['image_url'] = loadDefaultImage();
                                                            }
                                                            
                                                        @endphp
                                                        <a href="{{$data['redirect_url']}}">
                                                            <img class="img-fluid blur-up lazyload" src="{{$data['image_url']}}" alt=""/>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="product-detail">
                                                    <div class="inner_spacing">
                                                        <a href="{{$data['redirect_url']}}">
                                                            <h3>{{__($data['name'])}}</h3>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="col-xl-12 col-12 mt-4">
                                        <h5 class="text-center">{{__('No Product Found')}}</h5>
                                    </div>
                                    @endif
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
    vendorAllOnMap();
    function vendorAllOnMap() {
        var latitude = "{{ $vendorLatLong[0][0] ?? 0 }}";
        var longitude = "{{ $vendorLatLong[0][1] ?? 0 }}";
        var latlng = new google.maps.LatLng(latitude, longitude);
        var prev_infowindow =false;

        map = new google.maps.Map(document.getElementById('vendor-map'), {
            center: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
            zoom: 1
        });

        var url = window.location.origin;
        var vendorData = {!!json_encode($mapViewVendorList)!!};

        //    vendor  markers
        for (let i = 0; i < vendorData.length; i++) {
            vendor = vendorData[i];

            if(vendor.address != null && vendor.latitude != "" && vendor.latitude != "0.00000000" && vendor.longitude != "0.00000000" ){
                var contentString = '';

                var vendorPhone = '';
                if(vendor.phone_no != '' && vendor.phone_no != null){
                    console.log('vendor.phone_no', vendor.phone_no);
                    vendorPhone = '<span> <i class="fas fa-phone-alt"></i>'+vendor?.dial_code +vendor?.phone_no+'</span>'
                }

                contentString = '<a target="_blank" href="'+vendor.redirect_url+'"><div class="row no-gutters align-items-start">'+
                    '<div class="col-sm-4">'+
                        '<div class="img_box mb-sm-0 mb-2"><a target="_blank" href="'+vendor.redirect_url+'"><img src="'+vendor.image_url+'"/></a></div> </div>'+
                    '<div class="col-sm-8 pl-2 user_info">'+
                        '<div class="user_name mb-2"><label class="d-block m-0">'+vendor.name+'</label>'+vendorPhone+'</div>'+
                        '<div><b class="d-block mb-2"><i class="fas fa-mobile-alt"></i> <span> '+vendor.address+
                        ' </span></b> </div>'
                    '</div>'+
                '</div></a>';

                const infowindow = new google.maps.InfoWindow({
                    content: contentString,
                    minWidth: 250,
                    minheight: 250,
                });
                // images = 'https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/Clientlogo/612e24163debe.png@webp';



                images = "{{ asset('assets/images/mapVendoricon.png') }}";

                var image = {
                    url: images, // url
                    scaledSize: new google.maps.Size(30, 40), // scaled size
                    origin: new google.maps.Point(0,0), // origin
                    anchor: new google.maps.Point(22,22) // anchor
                };
                const marker = new google.maps.Marker({
                    icon: image,
                    position: { lat: parseFloat(vendor.latitude), lng: parseFloat(vendor.longitude) },
                    map: map,
                });

                marker.addListener("click", () => {

                    if( prev_infowindow ) {
                        prev_infowindow.close();
                    }

                    prev_infowindow = infowindow;

                    infowindow.close();
                    infowindow.open(map, marker);
                });
            }

        }
    }
</script>
@endsection
