<!-- Topbar Start -->
<div class="navbar-custom">
    <div class="col d-flex align-items-center justify-content-between justify-content-lg-end">
        <ul class="top-site-links d-flex align-items-center p-0 mb-0 mr-lg-2 mr-auto">


            <li class="d_none">
                <div class="logo-box">
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
            </li>
            <li class="mobile-toggle">
                <button id="shortclick" class="button-menu-mobile waves-effect waves-light">
                    <i class="fe-menu"></i>
                </button>
            </li>

            

            

            
        </ul>


        <ul class="list-unstyled topnav-menu float-right mb-2 mt-1">
            <li class="dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <strong>{{ auth()->user()->company_name ?? auth()->user()->name  }} </strong><i class="mdi mdi-chevron-down"></i> 
                </a>
                <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                    
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome !</h6>
                    </div>

                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-user"></i>
                        <span>My Account</span>
                    </a>
    
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item notify-item" href="{{ route('god.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> <i class="fe-log-out"></i><span>Logout</span>
                    </a>

                <form id="logout-form" action="{{ route('god.logout') }}" method="POST" >
                    @csrf
                </form>
    
                </div>
            </li>

            
            <li>
                <a class="navbar-toggle nav-link" data-toggle="collapse" data-target="#topnav-menu-content">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
            </li>   
            
        
        </ul>
    </div>
</div>
