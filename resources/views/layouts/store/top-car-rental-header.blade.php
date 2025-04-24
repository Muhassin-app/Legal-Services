@php
$clientData = \App\Models\Client::select('id', 'logo')->where('id', '>', 0)->first();
$urlImg = $clientData->logo['image_fit'].'150/60'.$clientData->logo['image_path'];
@endphp

<header id="" class="site_header {{ Request::is('/') ? '' : 'inner_header' }}">
	<div class="container">
		<div class="row  align-items-center justify-content-between">
			<div class="left">
				<div class="logo">
					<div class="image">
					<a href="{{route('userHome')}}">
						<img src="{{$urlImg}}" alt="Logo">
					</a>
					</div>
				</div>
			</div>
			<div class="right">
				<nav class="menu">
					<ul class="d-flex  align-items-center">
						@php
							$searchDate = date('d M Y H:i').' to '.date('d M Y H:i', strtotime('+1 week'));
						@endphp
						<li class="navigation-tab-item"><a href="{{route('userHome')}}" title="">Home</a></li>
						<li><a href="{{ route('productSearch',['service' => 'rental', 'pick_drop_time' => $searchDate]) }}" title="">Car Rental</a></li>
						{{-- <li><a href="{{ url('category/airport',['service' => 'pick_drop']) }}" title="">Airport Pickup and Drop</a></li>
						<li><a href="{{ route('productSearch',['service' => 'yacht', 'pick_drop_time' => $searchDate]) }}" title="">Yacht</a></li> --}}
						@if (Auth::guest())
							<li><a href="user/login" title="">Sign in / Login</a></li>
						@else	
							<li><a href="{{route('user.profile')}}" title="">My Account</a></li>
						@endif
					</ul>
				</nav>
			</div>
		</div>
	</div>
</header>