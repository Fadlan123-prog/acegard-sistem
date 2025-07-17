<!doctype html>
<html lang="id">
    <head>
		<!--=== Required meta tags ===-->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!--=== CSS Link ===-->
		<link rel="stylesheet" href="{{asset('assets/frontend/css/bootstrap.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/meanmenu.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/owl.theme.default.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/owl.carousel.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/icofont.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/font-awesome-pro.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/scrollcue.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/magnific-popup.min.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/style.css')}}">
		<link rel="stylesheet" href="{{asset('assets/frontend/css/responsive.css')}}">
		<!-- Favicon -->
		<link rel="icon" type="image/png" href="{{asset('assets/frontend/images/favicon.png')}}">

		<title>Acegard - Spesialis Kaca Film Mobil & Gedung</title>
    </head>

    <body>
		<!--=== Start Preloader Area ===-->
		<div class="preloader">
            <div class="content">
                <div class="ball"></div>
                <div class="ball"></div>
                <div class="ball"></div>
                <div class="ball"></div>
                <div class="ball"></div>
                <div class="ball"></div>
                <div class="ball"></div>
                <div class="ball"></div>
                <div class="ball"></div>
                <div class="ball"></div>
            </div>
        </div>
		<!--=== End Preloader Area ===-->

		<!--=== Start Header Area ===-->
		@include('frontend.partials.header')
		<!--=== Start Header Area ===-->

		<!--=== Start Navbar Area ===-->
		@include('frontend.partials.navbar')
		<!--=== End Navbar Area ===-->

		<!--=== Start Banner Area ===-->
		@yield('content')

		<!--=== Start Footer Area ===-->
		@include('frontend.partials.footer')
		<!--=== End Footer Area ===-->

		<!--=== Start CopyRight Area ===-->
		<div class="container">
			<p class="copy-right">Copyright, <span>Stir</span> All Rights Reserved</p>
		</div>
		<!--=== End CopyRight Area ===-->

		<div class="back-to-top text-center">
			<i class="icofont-simple-up"></i>
		</div>

        <!--=== JS Link ===-->
        <script src="{{asset('assets/frontend/js/jquery.min.js')}}"></script>
        <script src="{{asset('assets/frontend/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/frontend/js/jquery.meanmenu.js')}}"></script>
		<script src="{{asset('assets/frontend/js/owl.carousel.min.js')}}"></script>
        <script src="{{asset('assets/frontend/js/scrollcue.js')}}"></script>
        <script src="{{asset('assets/frontend/js/magnific-popup.min.js')}}"></script>
		<script src="{{asset('assets/frontend/js/counterup.min.js')}}"></script>
        <script src="{{asset('assets/frontend/js/waypoints.min.js')}}"></script>
		<script src="{{asset('assets/frontend/js/mixitup.min.js')}}"></script>
        <script src="{{asset('assets/frontend/js/smoothscroll.min.js')}}"></script>
		<script src="{{asset('assets/frontend/js/custom.js')}}"></script>
    </body>
</html>
