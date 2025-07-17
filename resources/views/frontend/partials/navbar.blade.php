<div class="navbar-area bg-black">
			<!--=== Start Main Navbar Section ===-->
			<div class="main-nav">
				<div class="container mw-1680">
					<nav class="navbar navbar-expand-md navbar-light">
						<a class="navbar-brand" href="index.html">
							<img src="{{asset('assets/frontend/images/white-logo.svg')}}" alt="logo">
						</a>

						<div class="collapse navbar-collapse for-mobile-menu" id="navbarSupportedContent">
							<ul class="navbar-nav me-auto">
								<li class="nav-item">
									<a href="{{route('frontend.home')}}" class="nav-link  {{Route::currentRouteName() == 'frontend.home' ? 'active' : ''}}">
										Home
									</a>

								</li>



								<li class="nav-item">
									<a href="#" class="nav-link {{Route::currentRouteName() == 'frontend.service.car' || Route::currentRouteName() == 'frontend.service.building' || Route::currentRouteName() == 'frontend.service.franchise' ? 'active' : ''}}">
										Services
									</a>

									<ul class="dropdown-menu">
										<li class="nav-item">
											<a href="{{route('frontend.service.car')}}" class="nav-link {{Route::currentRouteName() == 'frontend.service.car' ? 'active' : ''}}">Car</a>
										</li>
										<li class="nav-item">
											<a href="{{route('frontend.service.building')}}" class="nav-link {{Route::currentRouteName() == 'frontend.service.building' ? 'active' : ''}}">Building</a>
										</li>
										<li class="nav-item">
											<a href="{{route('frontend.service.franchise')}}" class="nav-link {{Route::currentRouteName() == 'frontend.service.franchise' ? 'active' : ''}}">Franchise</a>
										</li>

									</ul>
								</li>

								<li class="nav-item">
									<a href="{{route('frontend.about')}}" class="nav-link {{Route::currentRouteName() == 'frontend.about' ? 'active' : ''}}">
										Tentang Kami
									</a>
								</li>

								<li class="nav-item">
									<a href="{{route('frontend.contact')}}" class="nav-link {{Route::currentRouteName() == 'frontend.contact' ? 'active' : ''}}">
										Kontak Kami
									</a>


								</li>


							</ul>

							<div class="nav-right-options d-flex align-items-center">

							</div>
						</div>
					</nav>
				</div>
			</div>
			<!--=== End Main Navbar Section ===-->

			<!--=== Start Mobile Navbar Section ===-->
			<div class="mobile-nav">
				<div class="container">
					<div class="mobile-menu">
						<div class="logo">
							<a href="index.html">
								<img src="assets/images/white-logo.svg" alt="logo">
							</a>
						</div>
					</div>
				</div>
			</div>
			<!--=== End Mobile Navbar Section ===-->
		</div>
