@extends('layouts.store', ['title' => 'Product'])
@section('content')

<style>
/* Jet Taxi Home Page Css */
.jet-taxi-banner{background:linear-gradient(180deg,rgb(248 242 255),#fff 99.91%);padding:63px 0 36px}.jet-banner-right{height:430px;margin:auto;width:auto}.jet-banner-right img{height:100%}.jet-taxi-banner h1{color:#000;font-size:56px;font-weight:600;letter-spacing:0;margin-bottom:21px;line-height:67px}.jet-taxi-banner p{color:#3a3a3a;font-size:24px;letter-spacing:0;line-height:32px;margin-bottom:23px}.work-box{border:1px solid rgba(151,151,151,.22);border-radius:4px;background-color:#fdfbff;box-shadow:0 15px 25px 0 rgba(70,44,103,.07);padding:40px 28px;height:100%}.work-box h3{color:#000;font-size:24px;font-weight:600;letter-spacing:0;margin:32px 0 12px;line-height:29px}.work-box p{color:#3a3a3a;font-size:18px;letter-spacing:0;line-height:22px;text-align:center}.app-content{width:270px;max-width:100%}.app-content .number{height:56px;line-height:56px;width:56px;border-radius:3.2px;background-color:rgba(207,172,255,.29);color:#7c1eff;font-size:25.6px;font-weight:600;letter-spacing:0;text-align:center}.app-content h4{color:#000;font-size:20px;font-weight:600;letter-spacing:0;line-height:24px}.app-content p{opacity:.68;color:#000;font-size:16px;letter-spacing:0;line-height:19px}
@media(max-width: 767px){.app-content{width: 100%;}.app-content{margin-top: 30px;}
}
</style>

    <!-- royo default demo -->

    <div class="cab-content-area">


        <!-- Dynamic Html Section Start From Here -->
        <section class="dynamic-html pb-4">
            <div class="container">
                <div class="row">
                    <div class="col-12 top-heading d-flex align-items-center justify-content-between  mb-4">
                        <h2 class="h2-heading">
                            Dynamic html
                        </h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="become-dnyamic-box text-center">
                            <div class="become-img mb-4">
                                <img class="img-fluid" src="{{asset('front-assets/images/become-driver.svg')}}" alt="">
                            </div>
                            <div class="become-content">
                                <h3><b>Become a Dasher</b></h3>
                                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Doloremque possimus placeat, similique fugit odio dolore?</p>
                                <a class="become-btn" href="#"><b>Start earning</b></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="become-dnyamic-box text-center">
                            <div class="become-img mb-4">
                                <img class="img-fluid" src="{{asset('front-assets/images/become-partner.svg')}}" alt="">
                            </div>
                            <div class="become-content">
                                <h3><b>Become a Partner</b></h3>
                                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Doloremque possimus placeat, similique fugit odio dolore?</p>
                                <a class="become-btn" href="#"><b>Signup your store</b></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="become-dnyamic-box text-center">
                            <div class="become-img mb-4">
                                <img class="img-fluid" src="{{asset('front-assets/images/become-try-app.svg')}}" alt="">
                            </div>
                            <div class="become-content">
                                <h3><b>Try the App</b></h3>
                                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Doloremque possimus placeat, similique fugit odio dolore?</p>
                                <a class="become-btn" href="#"><b>Get the App</b></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dynamic Html Section Start From Here -->
        <section class="dynamic-html pb-4">
            <div class="container">
                <div class="row">
                    <div class="col-12 top-heading d-flex align-items-center justify-content-between  mb-4">
                        <h2 class="h2-heading">
                            Tracking Order
                        </h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="become-dnyamic-box text-center">
                            <div class="become-img mb-4">
                                <img class="img-fluid" src="{{asset('front-assets/images/minimum-order.svg')}}" alt="">
                            </div>
                            <div class="become-content">
                                <h3><b>No Minimum Order</b></h3>
                                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Doloremque possimus placeat, similique fugit odio dolore?</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="become-dnyamic-box text-center">
                            <div class="become-img mb-4">
                                <img class="img-fluid" src="{{asset('front-assets/images/location-tracking.svg')}}" alt="">
                            </div>
                            <div class="become-content">
                                <h3><b>Live Order Tracking</b></h3>
                                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Doloremque possimus placeat, similique fugit odio dolore?</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="become-dnyamic-box text-center">
                            <div class="become-img mb-4">
                                <img class="img-fluid" src="{{asset('front-assets/images/fast-delivery.svg')}}" alt="">
                            </div>
                            <div class="become-content">
                                <h3><b>Lightning-Fast Delivery</b></h3>
                                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Doloremque possimus placeat, similique fugit odio dolore?</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Country Flag Section Start From Here -->
        <section class="country-flag pb-4">
            <div class="container">
                <div class="row">
                    <div class="col-sm-8">
                        <div class="country-content">
                            <p>
                                <b>
                                    Driveree acknowledges the traditional owners of country throughout Australia, & we pay our respects to them cultures, and to elders both past and present.
                                </b>
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-4 text-center d-flex align-items-center justify-content-center">
                        <div class="country-flag w-25 mr-4 mb-sm-0 mb-3">
                            <img class="img-fluid" src="{{asset('front-assets/images/flag/flag_one.png')}}" alt="">
                        </div>
                        <div class="country-flag w-25 mb-sm-0 mb-3">
                            <img class="img-fluid" src="{{asset('front-assets/images/flag/flag_two.png')}}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Royo Business Start From Here -->
        <section class="royo-business p-0">
            <div class="container p-64 pl-0 pr-0">
                <div class="row">
                    <div class="col-12">
                        <h2 class="title-36">Royo for Business</h2>
                        <div class="description-text">
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Commodi, labore!</p>
                        </div>
                        <a class="btn btn-solid new-btn d-inline-block" href="#">See how</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Royo Business Start From Here -->
        <section class="royo-rental p-0">
            <div class="container">

                <div class="row align-items-center p-64">
                    <div class="col-sm-6">
                        <div class="cab-img-box">
                            <img class="img-fluid" src="{{asset('front-assets/images/gettyimages-1139275491-2048x2048_With-Mask.jpg')}}" alt="">
                        </div>
                    </div>
                    <div class="offset-md-1 col-sm-6 col-md-5 pl-lg-4">
                        <div class="">
                            <h2 class="title-52">Royo for Business</h2>
                            <div class="description-text">
                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Rem nisi officiis numquam!</p>
                            </div>
                            <a class="learn-more bottom-line" href="#">Learn more</a>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center p-64">
                    <div class="col-sm-6 order-md-1">
                        <div class="cab-img-box">
                            <img class="img-fluid" src="{{asset('front-assets/images/rentals-iindia.jpg')}}" alt="">
                        </div>
                    </div>
                    <div class="col-sm-6 order-md-0">
                        <div class="pr-lg-5 mr-lg-5">
                            <h2 class="title-52">Royo Intercity </h2>
                            <div class="description-text">
                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Rem nisi officiis numquam!</p>
                            </div>
                            <a class="learn-more" href="#">Learn more</a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Focused On Safety Start From Here -->
        <section class="focused-on-safety p-0">
            <div class="container p-64">
                <div class="row mb-4 pb-2">
                    <div class="col-12">
                        <div class="title-36">Focused on safety, wherever you go</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="safety-box">
                            <div class="safety-img">
                                <img class="img-fluid" src="{{asset('front-assets/images/Safety_Home_Img2x.jpg')}}" alt="">
                            </div>
                            <div class="safety-content">
                                <h3 class="mt-0">Our commitment to your safety</h3>
                                <div class="safety-text">
                                    <p>With every safety feature and every standard in our Community Guidelines, we're committed to helping to create a safe environment for our users.</p>
                                </div>
                                <div class="safety-links">
                                    <a class="bottom-line" href="#">
                                        <span>Read about our Community Guidelines</span>
                                    </a>
                                    <a class="bottom-line" href="#">
                                        <span>See all safety features</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="safety-box">
                            <div class="safety-img">
                                <img class="img-fluid" src="{{asset('front-assets/images/Safety_Home_Img2x.jpg')}}" alt="">
                            </div>
                            <div class="safety-content">
                                <h3 class="mt-0">Setting 10,000+ cities in motion</h3>
                                <div class="safety-text">
                                    <p>With every safety feature and every standard in our Community Guidelines, we're committed to helping to create a safe environment for our users.</p>
                                </div>
                                <div class="safety-links">
                                    <a class="bottom-line" href="#">
                                        <span>View all cities</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- end royo default demo -->


    <!-- Cab Booking Start From Here -->
    <section class="jet-taxi-banner">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-md-0 mb-4">
                    <div class="jet-left-content">
                        <h1>Download App, <br class="d-none d-md-block"> Start driving,<br class="d-none d-md-block"> Earn money !</h1>
                        <p>Download the Jet App from playstore, create <br class="d-none d-md-block"> account, use your car and drive by yourself. Get rides and <br class="d-none d-md-block">earn money with Jet.</p>
                        <ul class="d-flex align-items-center">
                            <li class="mr-3">
                                <a href="#"><img src="{{asset('assets/images/iosstore.png')}}" alt=""></a>
                            </li>
                            <li>
                                <a href="#"><img src="{{asset('assets/images/playstore.png')}}" alt=""></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <div class="jet-banner-right">
                        <img class="img-fluid" src="{{asset('assets/images/ic_bannerimage@2x.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cab Content Area Start From Here -->

    <div class="cab-content-area">

    <!-- How It Works Section -->
    <section class="how-it-works py-lg-5 py-4">
        <div class="container">
            <div class="row mb-lg-4 mb-3">
                <div class="col-12 text-center">
                    <h2>How It Works</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-lg-3 mb-lg-4 mb-3">
                    <div class="work-box text-center">
                        <div class="work-icon">
                            <img src="{{asset('assets/images/ic_online.svg')}}" alt="">
                        </div>
                        <h3>Get Online</h3>
                        <p>Turn your availability on and start getting requests from nearby for tasks.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-lg-4 mb-3">
                    <div class="work-box text-center">
                        <div class="work-icon">
                            <img src="{{asset('assets/images/ic_accept.svg')}}" alt="">
                        </div>
                        <h3>Acccept request</h3>
                        <p>Accept the task request on viewing the task details and head to the pickup location.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-lg-4 mb-3">
                    <div class="work-box text-center">
                        <div class="work-icon">
                            <img src="{{asset('assets/images/ic_ride.svg')}}" alt="">
                        </div>
                        <h3>Start ride</h3>
                        <p>Once you have reached the pickup location, start the ride after pickup.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-lg-4 mb-3">
                    <div class="work-box text-center">
                        <div class="work-icon">
                            <img src="{{asset('assets/images/ic_end.svg')}}" alt="">
                        </div>
                        <h3>End ride</h3>
                        <p>End the ride after dropping off the customer. Get the detailed bill on your App.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Enjoy unlimited benifits with Jet App -->
    <section class="jet-app-section pb-5">
        <div class="container">
            <div class="row mb-lg-5 mb-4">
                <div class="col-12 text-center">
                    <h2>Enjoy unlimited benifits with Jet App</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 order-lg-1">
                    <div class="app-img">
                        <img class="img-fluid" src="{{asset('assets/images/ic_phone1@2x.png')}}" alt="">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 py-xl-5 text-lg-right d-flex align-items-lg-end justify-content-between flex-column pr-lg-5 order-lg-0">
                    <div class="app-content">
                        <div class="number ml-lg-auto">
                            1
                        </div>
                        <h4>Unlimited ride requets</h4>
                        <p>Get non- Stop ride requests once you register as a driver.</p>
                    </div>
                    <div class="app-content">
                        <div class="number ml-lg-auto">
                            3
                        </div>
                        <h4>No hidden costs</h4>
                        <p>Get non- Stop ride requests once you register as a driver.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4 py-xl-5 d-flex align-items-start justify-content-between flex-column pl-lg-5 order-lg-2">
                    <div class="app-content">
                        <div class="number">
                            2
                        </div>
                        <h4>Easy to operate</h4>
                        <p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.</p>
                    </div>
                    <div class="app-content">
                        <div class="number">
                            4
                        </div>
                        <h4>Maximum Profits</h4>
                        <p>Get non- Stop ride requests once you register as a driver.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cab Section -->
    <section class="cab-bottom-banner">
        <img class="img-fluid" src="{{asset('assets/images/ic_bg1@2x.png')}}" alt="">
    </section>


    </div>




    <script src="{{asset('front-assets/js/popper.min.js')}}"></script>
    <script src="{{asset('front-assets/js/slick.js')}}"></script>
    <script src="{{asset('front-assets/js/menu.js')}}"></script>
    <script src="{{asset('front-assets/js/lazysizes.min.js')}}"></script>
    <script src="{{asset('front-assets/js/bootstrap.js')}}"></script>
    <script src="{{asset('front-assets/js/jquery.elevatezoom.js')}}"></script>
    <script src="{{asset('front-assets/js/underscore.min.js')}}"></script>
    <script src="{{asset('front-assets/js/script.js')}}"></script>
    <script src="{{asset('js/custom.js')}}"></script>
    <script src="{{asset('js/location.js')}}"></script>
    <script src="{{asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{asset('assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.js')}}"></script>
    <script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>
    <script src="{{asset('assets/libs/clockpicker/clockpicker.min.js')}}"></script>

    <script src="{{asset('assets/js/pages/form-pickers.init.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>

    <script type="text/javascript">

    </script>

    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
            });
        }, false);
        })();
    </script>
    <script>
  var loadFile = function(event) {
    var output = document.getElementById('output');
    output.src = URL.createObjectURL(event.target.files[0]);
   };
    </script>
    <script>
  var loadFile = function(event) {
    var banner = document.getElementById('banner');
    banner.src = URL.createObjectURL(event.target.files[0]);
   };


    </script>



@endsection