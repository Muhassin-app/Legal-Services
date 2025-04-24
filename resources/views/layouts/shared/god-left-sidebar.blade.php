<div class="left-side-menu">
        <div class="logo-box d-none d-lg-block">
            <a href="{{route('client.index')}}" class="logo logo-dark text-center">
                <span class="logo-sm">
                    <img src="{{asset('assets/images/logo-dark.png')}}"alt="" height="50">
                    <!-- <span class="logo-lg-text-light">UBold</span> -->
                </span>
                <span class="logo-lg">
                    <img src="{{asset('assets/images/logo-dark.png')}}"alt="" height="20">
                    <!-- <span class="logo-lg-text-light">U</span>
                    $urlImg = \Storage::disk('s3')->url('assets/client_00000125/agents5fc76c71abdb3.png/A9B2zHkr5thbcyTKHivaYm4kNYrSXOiov6USdFpV.png'); 
                    -->
                </span>
            </a>

            @php
                
             $urlImg = asset('assets/images/logo-dark.png');
             //$image = \Phumbor::url($urlImg)->fitIn(90,50);

            @endphp
    
            <a href="{{route('client.index')}}" class="logo logo-light text-center">
                <span class="logo-sm">
                    <img src="{{$urlImg}}"alt="" height="30" style="padding-top: 4px;">
                </span>
                <span class="logo-lg">
                    <img src="{{$urlImg}}"alt="" height="50" style="padding-top: 4px;">
                </span>
            </a>
        </div>

    <div class="h-100" data-simplebar>
        <div class="user-box text-center">
            <img src="{{asset('assets/images/users/user-1.jpg')}}" alt="user-img" title="Mat Helme"
                class="rounded-circle avatar-md">
            <div class="dropdown">
                <a href="javascript: void(0);" class="text-dark dropdown-toggle h5 mt-2 mb-1 d-block"
                    data-toggle="dropdown">User</a>
                <div class="dropdown-menu user-pro-dropdown">
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-user mr-1"></i>
                        <span>My Account</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-log-out mr-1"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
            <p class="text-muted">Admin Head</p>
        </div>
        <div id="sidebar-menu">
            <ul id="side-menu">
                <li>
                    <a href="{{route('dashboard')}}">
                        <i class="fe-airplay"></i>
                        <span> Dashboard </span>
                    </a>
                </li>
                <li>
                    <a href="#planbilling" data-toggle="collapse">
                    &nbsp;<span class="icon-subscribe"></span>
                        <span>&nbsp;&nbsp;Plan & Billing</span>
                    </a>
                    <div class="collapse" id="planbilling">
                        <ul class="nav-second-level">
                                <li>
                                    <a href="{{route('billingplans')}}">&nbsp;&nbsp;&nbsp;Plans</a>
                                </li>
                                <li>
                                    <a href="{{route('billingtimeframes')}}">&nbsp;&nbsp;&nbsp;Timeframes</a>
                                </li>
                                <li>
                                    <a href="{{route('billingpricing')}}">&nbsp;&nbsp;&nbsp;Pricing</a>
                                </li>
                                <li>
                                    <a href="{{route('clientsubscription')}}">&nbsp;&nbsp;&nbsp;Client Subscriptions</a>
                                </li>
                                <li>
                                    <a href="{{route('democlients')}}">&nbsp;&nbsp;&nbsp;Demo Clients</a>
                                </li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a href="#chatsocket" data-toggle="collapse">
                    &nbsp;<span class="icon-settings-1-1"></span>
                        <span>&nbsp;&nbsp;Configuration</span>
                    </a>
                    <div class="collapse" id="chatsocket">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{route('chatsocket')}}">&nbsp;&nbsp;&nbsp;Chat Socket</a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li>
                    <a href="{{route('client.index')}}">
                        <i data-feather="users"></i>
                        <span> Clients </span>
                    </a>
                </li>
                <li>
                    <a href="{{route('language.index')}}">
                        <i data-feather="layout" class="icon-dual"></i>
                        <span> Language </span>
                    </a>
                </li>
                <li>
                    <a href="{{route('currency.index')}}">
                        <i data-feather="dollar-sign" class="icon-dual"></i>
                        <span> Currency </span>
                    </a>
                </li>
                <li>
                    <a href="{{route('map.index')}}">
                        <i data-feather="map" class="icon-dual"></i>
                        <span> Map Providers </span>
                    </a>
                </li>
                <li>
                    <a href="{{route('sms.index')}}">
                        <i data-feather="message-square" class="icon-dual"></i>
                        <span> SMS Providers </span>
                    </a>
                </li>
                <li>
                    <a href="{{route('lumen')}}">
                        <i data-feather="message-square" class="icon-dual"></i>
                        <span> Lumen </span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
