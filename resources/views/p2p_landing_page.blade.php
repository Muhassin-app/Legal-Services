@extends('layouts.store', ['demo' => 'creative', 'title' => 'Home'])
@section('customcss')
<style type="text/css">
		@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700&display=swap');

		* {
			margin: 0px;
			padding: 0px;
		}

		body {
			font-family: 'Poppins', sans-serif !important;
		}
		.mobile-account span{
			font-family: 'Poppins', sans-serif;
		}
		.al_body_template_nine .main-menu .menu-right .icon-nav li.mobile-account ul li a{
			font-family: 'Poppins', sans-serif;
		}
		.washvalley-text h2 span {
			color: #2a1bbb;
		}
		.service-item .item-img {
			width: 100%;
			height: 100%;
		}

		.washvalley-text a {
			background: #2a1bbb;
			color: #fff;
			padding: 9px 17px;
			font-size: 16px;
			border-radius: 6px;
		}

		.washvalley-text a:hover {
			background: #2a1bbb;
			color: #fff;
			transition: all.2s ease-in-out 0s;
		}

		.washvalley-text h2 {
			font-size: 30px;
			position: relative;
		}

		.ser_img:hover h6 {
			color: #fff
		}

		.ser_img:hover p {
			color: #fff
		}

		.washvalley-text h2:before {
			content: '';
			position: absolute;
			width: 47px;
			height: 2px;
			background: #2a1bbb;
			bottom: -15%;
		}

		.washvalley-text p {
			color: #5e5d5d;
			line-height: 22px;
			font-size: 14px;
		}

		.rent-about h2 {
			font-size: 30px;
			font-weight: 600;
			margin-bottom: 10px;
		}

		.rent-about h2 span {
			color: #2a1bbb;
		}

		.rent-about p {
			font-size: 14px;
			width: 100%;
			max-width: 70%;
			margin: 0px auto;
		}

		.service-item {
			text-align: center;
			border: 1px solid#ededed;
			cursor: pointer;
			padding: 0px 0px;
		}

		.service-item h3 {
			font-size: 18px;
			text-transform: capitalize;
			font-weight: 600;
			margin-bottom: 6px;color:#000;
		}

		.service-item p {
			font-size: 14px;
			line-height: 22px;color:#000;
		}

		.washvalley-laundry {
			background: url(https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/4Nj1kVg7xRvAoRytglM4xpmGr9EIq8p25UWWEMBx.jpg) no-repeat;
			background-color: #ffffff9c;
			background-size: cover;
			background-blend-mode: color;
			background-position: center;
			background-attachment: fixed;
		}

		.laundry-img h3 {
			color: #000;
			font-size: 45px;
			position: relative;
		}

		.laundry-img h3:before {
			content: '';
			position: absolute;
			width: 47px;
			height: 2px;
			background: #2a1bbb;
			bottom: 0%;
			left: 48%;
		}

		.laundry-img h3 span {
			color: #2a1bbb;
		}

		.laundry-img p {
			font-size: 17px;
			color: #000;
		}

		.laundry-img a {
			color: #fff !important;
			padding: 14px 37px !important;
			font-size: 16px;
			border-radius: 6px;
			background: #2a1bbb;
			text-decoration: none;
		}

		.laundry-img a:hover {
			background: #5444e7;
			color: #fff;
			transition: all.2s ease-in-out 0s;
		}

		.dry-faclity-img img {
			width: 100%;
			max-width: 90%;
		}

		.faclity h2 {
			font-size: 30px;
			position: relative;
		}

		.faclity h2 span {
			color: #2a1bbb;
		}

		.pas41HowWork .pasStepBox .pasStepBoxText .pasStepBoxTextIocns svg {
			vertical-align: middle;
		}

		.washvalley-bed-bath h3 span {
			color: #2a1bbb;
		}

		.faclity h2 span:before {
			content: '';
			position: absolute;
			width: 47px;
			height: 2px;
			background: #2a1bbb;
			bottom: -15%;
			left: 0;
		}

		.faclity p {
			color: #5e5d5d;
			line-height: 22px;
			font-size: 14px;
		}

		.dry-faclity-img {
			position: relative;
		}

		.dry-faclity-img:before {
			position: absolute;
			content: '';
			background: url(https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/VG7VZnxKmpzbE90rcv6vycqsdtcai0vgm7HyeSmV.png);
			width: 420px;
			height: 406px;
			left: -60px;
			bottom: -50px;
			background-size: cover;
			background-repeat: no-repeat;
			z-index: -1;
		}

		.dry-text-icon img {
			width: 65px;
		}

		.dry-text-icon p {
			font-size: 14px;
			line-height: 22px;
		}

		.btn-washvalley {
			padding: 0px 10px;
		}

		.dry-text-icon p {
			color: #685f5f;
			line-height: 22px;
		}

		.bed-bath {
			background: url(https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/7gJtA62dpstUFVcvyo8ykqm0xb7cGG5PSxbzOLXc.jpg) no-repeat;
			background-color: #0000000f;
			background-size: cover;
			background-blend-mode: color;
			background-position: center;
			background-attachment: fixed;
		}

		.bed-bath {
			margin: 6% 0% 0% 0% !important;
			padding: 6% 0% !important;
		}

		.washvalley-bed-bath {
			background: #1d202257;
		}

		.washvalley-bed-bath h3 {
			font-size: 40px;
			color: #fff;
			position: relative;font-weight: 600;
		}

		.washvalley-bed-bath h3:before {
			content: '';
			position: absolute;
			width: 47px;
			height: 2px;
			background: #2a1bbb;
			bottom: -50%;
			left: 50%;
		}

		.about_zuzu .about_text span {
			font-size: 30px;
			font-weight: 600;
			color: #000;
		}

		.about_zuzu .about_text h5 {
			font-size: 38px;
			font-weight: 600;
		}

		.about_zuzu .about_text h5 span {
			font-size: 38px;
			font-weight: 600;
			color: #2a1bbb;
		}

		.about_zuzu .about_text p {
			font-size: 14px;
			padding: 12px 0px 0px 0px;
			color: #000;
		}

		.about_zuzu .about_text ul li {
			padding: 6px 0px;
			color: #000;
			font-size: 12px;
			list-style: none;
			position: relative;
			cursor: pointer;
		}

		.about_zuzu .about_text a {
			background: #2a1bbb;
			color: #fff !important;
			padding: 14px 24px;
			margin-top: 14px;
			display: inline-block;
			border-radius: 5px;
		}

		.about_zuzu .about_text a:hover {
			background-color: #2a1bbb;
		}

		.about_zuzu .about_text ul li:after {
			content: '';
			background: url(https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/1tHCKocc468TJTbU6ZN4ALbgD2zqxBOaXUB1LL93.png);
			position: absolute;
			left: -28px;
			width: 20px;
			height: 20px;
			background-size: contain;
			top: 12px;
		}

		.about_zuzu .about_text ul li:hover {
			color: #2a1bbb;
		}

		.ser_img {
			background: #f7f7f7;
			border-radius: 10px;
			box-shadow: 13px 9px 13px #3c3b3b59;
			cursor: pointer;
		}

		.ser_img h6 {
			font-size: 18px;
			font-weight: 600;
			color:#000;
		}

		.ser_img p {
			font-size: 12px;color:#000;
			line-height: 20px;
		}

		.ser_img:hover {
			background: #2a1bbb;
		}

		.vec_img img {
			height: 100%;
			max-height: 170px;
			min-height: 170px;
		}


		/*how it works css start here----*/

		.pasStepBox {
			position: relative;
		}

		.pasStepBoxOuter {
			border-radius: 20px;
			border: 2px solid #393849;
			position: absolute;
			height: 100%;
			width: 90%;
			left: 0;
			top: 35px;
			z-index: 0;
		}

		.pasStepBoxOuter:before {
			position: absolute;
			content: "";
			height: 0;
			width: 0;
			border-width: 70px;
			border-style: solid;
			border-color: #fff;
			top: -20px;
			right: -4px;
			z-index: 0;
		}

		.pasStepBoxTextIocns {
			background-color: #393849;
			border-radius: 50%;
			height: 70px;
			width: 70px;
			border: 3px solid #fff;
			box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
			line-height: 60px;
			display: inline-block;
		}

		.pasStepBoxText {
			position: relative;
			z-index: 1;
			min-height: 240px;
		}

		.pasStepBoxOuter:after {
			position: absolute;
			height: 2px;
			background-color: #393849;
			width: 50px;
			top: auto;
			content: "";
			bottom: 115px;
			right: -50px;
		}

		.pasStepBoxOuterInner {
			position: absolute;
			content: "";
			width: 10px;
			height: 10px;
			border: solid #393849;
			border-width: 0 3px 3px 0;
			bottom: 111px;
			right: -50px;
			-webkit-transform: rotate(-45deg);
			transform: rotate(-45deg);
			z-index: 1;
		}

		.redColor .pasStepBoxOuter {
			border: 2px solid #2a1bbb;
		}

		.redColor .pasStepBoxOuter:after,
		.redColor .pasStepBoxTextIocns {
			background-color: #2a1bbb;
		}

		.redColor .pasStepBoxOuterInner {
			border-color: #2a1bbb;
		}

		.alFour .pasStepBoxOuter:after {
			display: none;
		}

		.pasStepBoxText p {
			font-size: 14px !important;
		}

		@media screen and (max-width:567px) {
			.why-rota-serv ul {
				padding-left: 10px !important;
			}

			.why-rota-serv b {
				margin-top: 10px;
			}

			.rota-cab-service {
				margin-bottom: 20px;
			}

			.rota-call {
				padding: 10px !important;
			}

			.mobile-spce {
				margin-bottom: 20px;
			}

			.why-rota-choose {
				padding: 10% 0%;
			}
		}

		@media screen and (max-width:567px) {
			.washvalley-img img {
				margin-top: 20px;
			}

			.faclity h2 {
				font-size: 22px;
				position: relative;
			}
		}
	</style>
@endsection
@section('content')
	<section class="rentzilla">
		<div class="container">
			<div class="row pb-5 pt-3">
				<div class="col-md-12 text-center">
					<div class="rent-about">
						<h2>Welcome to <span>Rentzilla</span></h2>
						<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloremque magni unde hic quas modi
							id incidunt! Ad dicta velit aperiam quo tempora molestias eos officia reprehenderit
							molestiae, excepturi, odio adipisci.</p>
					</div>
				</div>
			</div>

			<div class="row pb-5">
				<div class="col-lg-4 col-md-6">
					<div class="service-item">
						<div class="item-img">
							<figure><img
									src="https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/FTYO5VxQ3CfYQki7gtXj6sAgseykVXNaEwlvvg4y.jpg"
									alt="" class="w-100"></figure>
						</div>
						<h3>Camping</h3>
						<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Hic, ipsum alias. Quis, amet
							distinctio quo eius repudiandae voluptates,</p>
					</div>
				</div>

				<div class="col-lg-4 col-md-6">
					<div class="service-item">
						<div class="item-img">
							<figure><img
									src="https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/FTYO5VxQ3CfYQki7gtXj6sAgseykVXNaEwlvvg4y.jpg"
									alt="" class="w-100"></figure>

						</div>
						<h3>Beach</h3>
						<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Hic, ipsum alias. Quis, amet
							distinctio quo eius repudiandae voluptates,</p>
					</div>
				</div>

				<div class="col-lg-4 col-md-6">
					<div class="service-item">
						<div class="item-img">
							<figure><img
									src="https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/FTYO5VxQ3CfYQki7gtXj6sAgseykVXNaEwlvvg4y.jpg"
									alt="" class="w-100"></figure>
						</div>
						<h3>Household</h3>
						<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Hic, ipsum alias. Quis, amet
							distinctio quo eius repudiandae voluptates,</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="about_zuzu py-5">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-sm-6">
					<div class="clean_img">
						<img src="https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/VNMGDgUjqZMOJA1qINrZxJnGW30lj5NOtf5mYxfp.png"
							alt="" class="w-100">
					</div>
				</div>

				<div class="col-sm-6">
					<div class="about_text pl-4">
						<span>About <b>Rentzilla</b></span>

						<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Ducimus, dignissimos mollitia quo,
							atque sit ratione rerum odio numquam eius architecto libero! Veniam, necessitatibus
							laboriosam aliquid quo rerum quaerat doloribus dolorem.</p>

						<a href="javascript:void(0);">More Details</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="washvalley-laundry py-5 my-5">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="laundry-img text-center py-5">
						<h3 class="py-2">Need <span>Rentzilla</span> On Rent ?</h3>
						<h2>Find it Easiest Way!</h2>
						<p class="py-3">Get up 10% Off On Selected Rentzilla</p>
						<a class="py-2  d-inline-block" href="javascript:void(0);">Book Now</a>
					</div>
				</div>
			</div>
		</div>
	</section>


	<section class="dry-clean my-5">
		<div class="container">
			<div class="faclity">
				<h2 class="pb-2">Rentzilla <span> Services</span></h2>
				<p class="pt-2">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quia totam eos enim sapiente ex
					ab praesentium recusandae molestiae similique qui. Dicta corporis pariatur, cum accusantium
					molestias ipsa hic distinctio minima.</p>
			</div>
			<div class="row d-flex align-items-center">
				<div class="col-md-6">
					<div class="dry-faclity-img pt-3">
						<img src="https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/ogkP71ogQ47J4WEk94AfZ1S86ezqsJzAaAolt4ur.png"
							alt="">
					</div>
				</div>
				<div class="col-md-6">
					<div class="row justify-content-center">
						<div class="col-sm-6">
							<div class="ser_img text-center p-4 mb-4">
								<div class="vec_img d-none">
									<img src="https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/1UxQukMpEcbDRR6QGf4ziy7aR5asUXMzj40m89oU.png"
										alt="" class="pb-2">
								</div>
								<h6>camping</h6>
								<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Assumenda rem maxime,
									provident iusto suscipit incidunt aut.</p>
							</div>
						</div>

						<div class="col-sm-6">
							<div class="ser_img text-center p-4">
								<div class="vec_img d-none">
									<img src="https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/vDhD4D7lg7fKWf61bKjMDUqBNi3tzKRcroVQ0TRT.png"
										alt="" class="pb-2">
								</div>
								<h6>beach</h6>
								<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Assumenda rem maxime,
									provident iusto suscipit incidunt aut.</p>
							</div>
						</div>

						<div class="col-sm-6">
							<div class="ser_img text-center p-4">
								<div class="vec_img d-none">
									<img src="https://s3.us-west-2.amazonaws.com/royoorders2.0-assets/prods/RCpGcAS6AwvbD5GQp6oTai5JYpReXCPXrvH6m5pv.png"
										alt="" class="pb-2">
								</div>
								<h6>household</h6>
								<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Assumenda rem maxime,
									provident iusto suscipit incidunt aut.</p>
							</div>
						</div>


					</div>
				</div>
			</div>
		</div>
	</section>
	<section class="bed-bath">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="washvalley-bed-bath text-center py-5">
						<h3> Rentzilla <span>Your Trip </span>Today!</h3>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection