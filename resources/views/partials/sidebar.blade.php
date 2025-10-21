<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li>
                <a  href="{{ route('dashboard') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <a  href="{{ route('employee.index') }}">
                    <iconify-icon icon="healthicons:factory-worker-outline" class="menu-icon"></iconify-icon>
                    <span>Employees</span>
                </a>
                {{-- <ul class="sidebar-submenu">

                    <li>
                    <a href="{{ route('index2') }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> CRM</a>
                    </li>
                    <li>
                    <a href="{{ route('index3') }}"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i> eCommerce</a>
                    </li>
                    <li>
                    <a href="{{ route('index4') }}"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> Cryptocurrency</a>
                    </li>
                    <li>
                    <a href="{{ route('index5') }}"><i class="ri-circle-fill circle-icon text-success-main w-auto"></i> Investment</a>
                    </li>
                    <li>
                    <a href="{{ route('index6') }}"><i class="ri-circle-fill circle-icon text-purple w-auto"></i> LMS</a>
                    </li>
                    <li>
                    <a href="{{ route('index7') }}"><i class="ri-circle-fill circle-icon text-info-main w-auto"></i> NFT & Gaming</a>
                    </li>
                    <li>
                    <a href="{{ route('index8') }}"><i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> Medical</a>
                    </li>
                    <li>
                    <a href="{{ route('index9') }}"><i class="ri-circle-fill circle-icon text-purple w-auto"></i> Analytics</a>
                    </li>
                    <li>
                    <a href="{{ route('index10') }}"><i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> POS & Inventory </a>
                    </li>
                </ul> --}}
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="ph:users" class="menu-icon"></iconify-icon>
                    <span>Customers</span>
                </a>

                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('customer.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Customers Cars</a>
                    </li>
                    <li>
                        <a href="{{ route('customer.building.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Customers Buildings</a>
                    </li>
                    {{-- <li>
                        <a href="{{ route('customer.ppf.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Customers PPF</a>
                    </li> --}}
                </ul>
            </li>
            <li>
                <a href="{{ route('commission.index') }}">
                    <iconify-icon icon="fluent-emoji-high-contrast:money-bag" class="menu-icon"></iconify-icon>
                    <span>Commission</span>
                </a>
            </li>
            <li>
                <a href="{{ route('gallery.index') }}">
                    <iconify-icon icon="ph:images" class="menu-icon"></iconify-icon>
                    <span>Gallery</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
