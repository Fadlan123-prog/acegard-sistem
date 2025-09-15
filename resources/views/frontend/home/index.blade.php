@extends('frontend.layout.index')

@once
    @push('styles')
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
		<link rel="icon" type="image/png" href="{{asset('assets/frontend/images/acegard-favicon.png')}}">
    @endpush
@endonce

@section('content')
<div class="banner-area bg-black bg-img" data-background="./assets/frontend/images/banner-bg-shape.png">
			<div class="container mw-1680">
				<div class="position-relative z-1" data-cues="slideInUp" data-group="images">
					<div class="banner-content">
						<h1 class="mb-0">Hallo <span>Auto & Property Owner</span></h1>
						<h1 class="mb-0">Kami adalah Acegard, Spesialis Kaca Film</h1>
						<h1>Terpercaya di <span>Indonesia</span></h1>
						<a href="services.html" class="banner-btn">
							<span>
								Hubungi Sekarang
								<i class="fa-solid fa-arrow-up-right"></i>
							</span>
						</a>
					</div>
					<div class="creative-agency">

						<p>Hi 👋. Kami adalah Acegard – solusi kaca film untuk mobil dan bangunan dengan teknologi terdepan untuk kenyamanan, perlindungan, dan tampilan elegan.</p>
					</div>
					<a href="#about-us" class="scroll-btn">
						<i class="fa-sharp fa-light fa-arrow-down"></i>
					</a>
				</div>
			</div>

			<img src="{{asset('assets/frontend/images/shape-1.png')}}" class="shape shape-1" alt="shape">

		</div>
		<!--=== End Banner Area ===-->

		<!--=== Start Brands Area ===-->
		<div class="brands-area pt-100">
			<div class="container">
				<div class="brands-title">
					<span data-cues="slideInUp">Partner Terpercaya kami</span>
				</div>
				<div class="d-flex justify-content-center justify-content-lg-between flex-wrap gap-2" data-cues="slideInUp">
					<div class="brands-item">
						<a href="about-us.html">
							<img src="{{asset('assets/frontend/images/cars-logo/bmw.png')}}" alt="brands">
						</a>
					</div>
					<div class="brands-item">
						<a href="about-us.html">
							<img src="{{asset('assets/frontend/images/cars-logo/mercy.png')}}" alt="brands">
						</a>
					</div>

					<div class="brands-item">
						<a href="about-us.html">
							<img src="{{asset('assets/frontend/images/cars-logo/honda.png')}}" alt="brands">
						</a>
					</div>

					<div class="brands-item">
						<a href="about-us.html">
							<img src="{{asset('assets/frontend/images/cars-logo/hyundai.png')}}" alt="brands">
						</a>
					</div>

					<div class="brands-item">
						<a href="about-us.html">
							<img src="{{asset('assets/frontend/images/cars-logo/vw.png')}}" alt="brands">
						</a>
					</div>

					<div class="brands-item">
						<a href="about-us.html">
							<img src="{{asset('assets/frontend/images/cars-logo/mazda.png')}}" alt="brands">
						</a>
					</div>

					<div class="brands-item">
						<a href="about-us.html">
							<img src="{{asset('assets/frontend/images/cars-logo/nissan.png')}}" alt="brands">
						</a>
					</div>

					<div class="brands-item">
						<a href="about-us.html">
							<img src="{{asset('assets/frontend/images/cars-logo/toyota.png')}}" alt="brands">
						</a>
					</div>

				</div>
			</div>
		</div>
		<!--=== End Brands Area ===-->

		<!--=== Start About Us Area ===-->
		<div class="about-us-area ptb-100" id="about-us">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-lg-6">
						<div class="about-us-img" data-cues="slideInUp">
							<img src="{{asset('assets/frontend/images/home1.webp')}}" alt="about-us">
						</div>
					</div>

					<div class="col-lg-6">
						<div class="about-us-content" data-cues="slideInUp">
							<span class="top-title">Tentang Kami</span>
							<h2>Solusi Kaca Film Mobil & Gedung yang Profesional</h2>
							<p>Acegard adalah brand kaca film terpercaya di Indonesia yang mengedepankan kualitas, teknologi, dan pelayanan. Kami menyediakan berbagai pilihan kaca film berkualitas tinggi untuk kebutuhan kendaraan dan properti Anda dengan performa penolakan panas yang maksimal dan perlindungan dari sinar UV.</p>
							<div class="row">
								<div class="col-lg-6 col-sm-6">
									<ul class="ps-0 list-unstyled about-list">
										<li>
											Garansi Resmi Hingga 5 Tahun
										</li>
										<li>
											Tolak Panas 99%
										</li>
										<li>
											Instalasi Cepat & Profesional
										</li>
									</ul>
								</div>
								<div class="col-lg-6 col-sm-6">
									<ul class="ps-0 list-unstyled about-list">
										<li>
											Varian Black Master, Notch UV400, dan 4K Alpha Pro
										</li>
										<li>
											Cocok untuk Mobil dan Gedung
										</li>
										<li>
											Layanan di Jabodetabek, Bandung, Bengkulu, Medan, Jambi
										</li>
									</ul>
								</div>
							</div>
							<div class="d-inline-block">
								<a href="about-us.html" class="circle-btn d-inline-block text-decoration-none d-flex align-items-center">
									<span>Hubungi Sekarang</span>
									<i class="fa-solid fa-arrow-up-right"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--=== End About Us Area ===-->

		<!--=== Start Services Area ===-->
		<div class="services-area bg-color-030303 ptb-100">
			<div class="container">
				<div class="section-title white-title" data-cues="slideInUp">
					<span class="top-title">Product Kami</span>
					<h2>Kaca Film Unggulan Acegard</h2>
				</div>

				<div class="row justify-content-center" data-cues="slideInUp">
					<div class="col-xl-4 col-sm-4">
						<div class="services-single-item">
							<div class="icon">
								<img src="{{asset('assets/frontend/images/services-icon-1.svg')}}" alt="services-icon">
							</div>
							<h3>
								<a href="service-details.html">Black Master</a>
							</h3>
							<p>Expert guidance in formulating and growth plans.</p>
							<ul class="ps-0 mb-0 list-unstyled">
								<li>Product Management</li>
								<li>Financial Advisory</li>
								<li>Product Strategy</li>
							</ul>
						</div>
					</div>
					<div class="col-xl-4 col-sm-4">
						<div class="services-single-item">
							<div class="icon">
								<img src="{{asset('assets/frontend/images/services-icon-2.svg')}}" alt="services-icon">
							</div>
							<h3>
								<a href="service-details.html">Notch UV 400</a>
							</h3>
							<p>Expert guidance in formulating and growth plans.</p>
							<ul class="ps-0 mb-0 list-unstyled">
								<li>Marketing Strategies</li>
								<li>Business Development</li>
								<li>Financial Advisory</li>
							</ul>
						</div>
					</div>
					<div class="col-xl-4 col-sm-4">
						<div class="services-single-item">
							<div class="icon">
								<img src="{{asset('assets/frontend/images/services-icon-3.svg')}}" alt="services-icon">
							</div>
							<h3>
								<a href="service-details.html">4K Alpha Pro</a>
							</h3>
							<p>Expert guidance in formulating and growth plans.</p>
							<ul class="ps-0 mb-0 list-unstyled">
								<li>Technology Solutions</li>
								<li>Market Research Analysis</li>
								<li>Project Management</li>
							</ul>
						</div>
					</div>
					<div class="col-lg-12 text-center">
						<div class="text-center services-btn d-inline-block m-auto mt-lg-4">
							<a href="services.html" class="circle-btn text-decoration-none d-flex align-items-center justify-content-center">
								<span>Hubungi Sekarang</span>
								<i class="fa-solid fa-arrow-up-right"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--=== End Services Area ===-->

		<!--=== Start Who We Are Area ===-->
		<div class="who-we-are-area ptb-100">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-lg-6">
						<div class="who-we-are-content" data-cues="slideInUp">
							<span class="top-title">Siapa kami</span>
							<h2>Solusi Kaca Film Bernilai Tinggi untuk Kendaraan & Properti</h2>
							<p>Acegard hadir sebagai penyedia kaca film profesional yang memadukan teknologi penolak panas canggih, estetika tinggi, serta pelayanan berkualitas. Dengan komitmen terhadap kenyamanan dan keamanan pelanggan, kami terus berkembang menjadi brand yang dipercaya oleh ribuan pengguna mobil dan pemilik properti di seluruh Indonesia.</p>

							<div class="all-skill-bar">
								<div class="skill-bar" data-percentage="99%">
									<h4 class="progress-title-holder">
										<span class="progress-title">Tolak Panas</span>
										<span class="progress-number-wrapper">
											<span class="progress-number-mark">
												<span class="percent"></span>
												<span class="down-arrow"></span>
											</span>
										</span>
									</h4>

									<div class="progress-content-outter">
										<div class="progress-content"></div>
									</div>
								</div>

								<div class="skill-bar" data-percentage="99%">
									<h4 class="progress-title-holder clearfix">
										<span class="progress-title">Perlindungan UV</span>
										<span class="progress-number-wrapper">
											<span class="progress-number-mark">
												<span class="percent"></span>
												<span class="down-arrow"></span>
											</span>
										</span>
									</h4>

									<div class="progress-content-outter">
										<div class="progress-content"></div>
									</div>
								</div>

								<div class="skill-bar" data-percentage="95%">
									<h4 class="progress-title-holder clearfix">
										<span class="progress-title">Kepuasan Pelanggan</span>
										<span class="progress-number-wrapper">
											<span class="progress-number-mark">
												<span class="percent"></span>
												<span class="down-arrow"></span>
											</span>
										</span>
									</h4>

									<div class="progress-content-outter">
										<div class="progress-content"></div>
									</div>
								</div>
							</div>

							<div class="d-inline-block">
								<a href="about-us.html" class="circle-btn d-inline-block text-decoration-none mt-4 mt-xl-5 d-flex align-items-center">
									<span>Hubungi Sekarang</span>
									<i class="fa-solid fa-arrow-up-right"></i>
								</a>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="who-we-are-img" data-cues="slideInUp">
							<img src="{{asset('assets/frontend/images/home2.webp')}}" alt="who-we-are-img">
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--=== End Who We Are Area ===-->

		<!--=== Start Testimonials Area ===-->
		<div class="testimonial-area pb-100">
			<div class="container">
				<div class="testimonial-slide owl-carousel owl-theme">
					<div class="d-md-flex align-items-center testimonial-single-item">
						<div class="flex-shrink-0">
							<img src="{{('assets/frontend/images/profile1.webp')}}" class="testimonial-img" alt="testimonial">
						</div>
						<div class="flex-grow-1 ms-md-4 mt-3 mt-md-0">
							<p>Gedung kantor kami jauh lebih nyaman dan hemat listrik setelah menggunakan Acegard Crystal Series.</p>
							<h4>Aditya Rahman / <span>Manajer Properti</span></h4>
						</div>
						<img src="{{asset('assets/frontend/images/quat.svg')}}" class="quat" alt="quat">
					</div>
					<div class="d-md-flex align-items-center testimonial-single-item">
						<div class="flex-shrink-0">
							<img src="{{asset('assets/frontend/images/profile2.webp')}}" class="testimonial-img" alt="testimonial">
						</div>
						<div class="flex-grow-1 ms-md-4 mt-3 mt-md-0">
							<p>Kaca film Acegard benar-benar menurunkan suhu kabin mobil saya. Hasil pemasangannya juga sangat rapi.</p>
							<h4>Zulfikri / <span>Pelanggan</span></h4>
						</div>
						<img src="{{asset('assets/frontend/images/quat.svg')}}" class="quat" alt="quat">
					</div>
				</div>
			</div>
		</div>
		<!--=== End Testimonials Area ===-->

		<!--=== Start Case Study Area ===-->
		<div class="case-study-area overflow-hidden pb-100">
			<div class="container-fluid p-0">
				<div class="section-title" data-cues="slideInUp">
					<span class="top-title">Proyek Kami</span>
					<h2>Hasil Pemasangan Nyata Kaca Film Acegard</h2>
				</div>

				<div class="case-study-content-wrap">
					<ul class="slider" data-cues="slideInUp">
						<li class="bg-img" data-background="{{ asset('assets/frontend/images/bd1.webp')}}">
							<h1>01</h1>
							<a href="cases-study-details.html">
								<div class="case-study-content">
									<span>Financial Advisory</span>
									<div></div>
									<h4>Assistance in Financial Planning</h4>
								</div>
							</a>
						</li>
						<li class="bg-img" data-background="{{ asset('assets/frontend/images/car1.webp')}}">
							<h1>02</h1>
							<a href="cases-study-details.html">
								<div class="case-study-content">
									<span>Financial Advisory</span>
									<div></div>
									<h4>Assistance in Financial Planning</h4>
								</div>
							</a>
						</li>
						<li class="bg-img" data-background="{{ asset('assets/frontend/images/bd2.webp')}}">
							<h1>03</h1>
							<a href="cases-study-details.html">
								<div class="case-study-content">
									<span>Financial Advisory</span>
									<div></div>
									<h4>Assistance in Financial Planning</h4>
								</div>
							</a>
						</li>
						<li class="bg-img" data-background="{{ asset('assets/frontend/images/car2.webp')}}">
							<h1>04</h1>
							<a href="cases-study-details.html">
								<div class="case-study-content">
									<span>Financial Advisory</span>
									<div></div>
									<h4>Assistance in Financial Planning</h4>
								</div>
							</a>
						</li>
						<li class="bg-img" data-background="{{ asset('assets/frontend/images/bd3.webp')}}">
							<h1>05</h1>
							<a href="cases-study-details.html">
								<div class="case-study-content">
									<span>Financial Advisory</span>
									<div></div>
									<h4>Assistance in Financial Planning</h4>
								</div>
							</a>
						</li>
					</ul>
				</div>

				<div class="text-center" data-cues="slideInUp">
					<div class="d-inline-block">
						<a href="case-study.html" class="circle-btn d-inline-block text-decoration-none mt-4 mt-xl-5 d-flex align-items-center justify-content-center">
							<span>Konsultasi Sekarang</span>
							<i class="fa-solid fa-arrow-up-right"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
		<!--=== End Case Study Area ===-->

		<!--=== Start FAQ Area ===-->
		<div class="faq-area pb-100">
			<div class="container">
				<div class="section-title text-start ms-0" data-cues="slideInUp">
					<span class="top-title">FAQs</span>
					<h2>Informasi Seputar Produk Acegard</h2>
				</div>

				<div class="faq-img" data-cues="slideInUp">
					<img src="{{asset('assets/frontend/images/banner.webp')}}" alt="faq-img">
				</div>

				<div class="accordion accordion-content" id="accordionExample" data-cues="slideInUp">
					<div class="accordion-item">
						<h2 class="accordion-header">
							<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
								<span class="d-sm-flex align-items-center">
									<span class="flex-shrink-0">
										<span class="count">01</span>
									</span>
									<span class="flex-grow-1 mt-3 mt-sm-0">
										<span> Apakah semua tipe kaca film Acegard bisa digunakan di gedung?</span>
									</span>
								</span>
							</button>
						</h2>
						<div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
							<div class="accordion-body">
								<p>Ya, kami menyediakan varian khusus untuk gedung yang memiliki ukuran dan karakteristik pencahayaan berbeda dari kendaraan.</p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								<span class="d-sm-flex align-items-center">
									<span class="flex-shrink-0">
										<span class="count">02</span>
									</span>
									<span class="flex-grow-1 mt-3 mt-sm-0">
										<span>Apa perbedaan antara Black Master, Notch UV400, dan 4K Alpha Pro?</span>
									</span>
								</span>
							</button>
						</h2>
						<div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
							<div class="accordion-body">
								<p> Black Master unggul dalam privasi, Notch UV400 menawarkan kejernihan maksimal, sedangkan 4K Alpha Pro memberikan kombinasi performa terbaik untuk mobil dan gedung mewah.</p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								<span class="d-sm-flex align-items-center">
									<span class="flex-shrink-0">
										<span class="count">03</span>
									</span>
									<span class="flex-grow-1 mt-3 mt-sm-0">
										<span>Apakah kaca film Acegard mengganggu sinyal GPS atau ponsel?</span>
									</span>
								</span>
							</button>
						</h2>
						<div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
							<div class="accordion-body">
								<p>Tidak. Produk Acegard dirancang untuk tetap ramah sinyal sehingga tidak mengganggu koneksi GPS, ponsel, atau smart key.</p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
								<span class="d-sm-flex align-items-center">
									<span class="flex-shrink-0">
										<span class="count">04</span>
									</span>
									<span class="flex-grow-1 mt-3 mt-sm-0">
										<span>Apakah Acegard menyediakan layanan pemasangan di rumah/kantor?</span>
									</span>
								</span>
							</button>
						</h2>
						<div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
							<div class="accordion-body">
								<p> Ya, kami menyediakan layanan pemasangan kaca film di tempat untuk area Jabodetabek dan Bandung.</p>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
								<span class="d-sm-flex align-items-center">
									<span class="flex-shrink-0">
										<span class="count">05</span>
									</span>
									<span class="flex-grow-1 mt-3 mt-sm-0">
										<span>Berapa lama garansi produk Acegard?</span>
									</span>
								</span>
							</button>
						</h2>
						<div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
							<div class="accordion-body">
								<p>Produk kami memiliki garansi hingga 5 tahun tergantung tipe kaca film yang dipilih.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--=== End FAQ Area ===-->

		<!--=== Start Contact Us Area ===-->
		<div class="contact-us-area bg-color-030303 ptb-100">
			<div class="container">
				<div class="section-title white-title" data-cues="slideInUp">
					<span class="top-title">Appointment</span>
					<h2>Love to Hear from You Get in Touch!</h2>
				</div>
				<form class="appointment-form">
					<div class="row" data-cues="slideInUp">
						<div class="col-lg-6">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="Your Name *">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<input type="email" class="form-control" placeholder="Your Email *">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="What you are interested *">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="Project Budget *">
							</div>
						</div>
						<div class="col-lg-12">
							<div class="form-group">
								<textarea cols="30" rows="5" class="form-control" placeholder="Message *"></textarea>
							</div>
						</div>
						<div class="col-lg-12">
							<div class="services-btn d-inline-block">
								<button type="submit" class="circle-btn text-decoration-none d-flex align-items-center bg-transparent">
									<span>Send Message</span>
									<i class="fa-solid fa-arrow-up-right"></i>
								</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!--=== End Contact Us Area ===-->
@endsection
