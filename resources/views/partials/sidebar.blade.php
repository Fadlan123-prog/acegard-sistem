<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>

    <div>
        @php
            $user = auth()->user();

            $isSuperadmin       = $user?->hasRole('superadmin') ?? false;
            $isOwner            = $user?->hasRole('owner') ?? false;
            $isAdmin            = $user?->hasRole('admin') ?? false;
            $isManagerOps       = $user?->hasRole('manager_operasional') ?? false;
            $isManagerMarketing = $user?->hasRole('manager_marketing') ?? false;
            $isMarketing        = $user?->hasRole('marketing') ?? false;
            $isSales            = $user?->hasRole('sales') ?? false;
            $isFinance          = $user?->hasRole('finance') ?? false;
            $isPurchasing       = $user?->hasRole('purchasing') ?? false;
            $isTeknisi          = $user?->hasRole('teknisi') ?? false;
            $isKenek            = $user?->hasRole('kenek') ?? false;
            $isCs               = $user?->hasRole('customer_service') ?? false;
            $isStaff            = $isTeknisi || $isKenek;

            // Restrukturisasi ERP (2026-08)
            $isItManager            = $user?->hasRole('it_manager') ?? false;
            $isDevelopment          = $user?->hasRole('development') ?? false;
            $isExportImport         = $user?->hasRole('export_import') ?? false;
            $isStrategiPengembangan = $user?->hasRole('strategi_pengembangan') ?? false;
            $isDesigner             = $user?->hasRole('designer') ?? false;
            $isVideographer         = $user?->hasRole('videographer') ?? false;
            $isCreativeTeam         = $isDesigner || $isVideographer;

            // Dashboard URL per role
            if ($isOwner) {
                $dashboardUrl = route('owner.dashboard');
            } elseif ($isMarketing || $isManagerMarketing) {
                $dashboardUrl = route('marketing.dashboard');
            } elseif ($isSales) {
                $dashboardUrl = route('sales.dashboard');
            } elseif ($isCs) {
                $dashboardUrl = route('cs.dashboard');
            } elseif ($isManagerOps) {
                $dashboardUrl = route('manager-ops.dashboard');
            } elseif ($isFinance) {
                $dashboardUrl = route('finance.dashboard');
            } elseif ($isPurchasing || $isItManager) {
                $dashboardUrl = route('purchasing.dashboard');
            } elseif ($isStaff) {
                $dashboardUrl = route('teknisi.dashboard');
            } elseif ($isDevelopment) {
                $dashboardUrl = route('development-reports.index');
            } elseif ($isExportImport) {
                $dashboardUrl = route('import-orders.index');
            } elseif ($isStrategiPengembangan || $isCreativeTeam) {
                $dashboardUrl = route('creative-tasks.index');
            } else {
                $dashboardUrl = route('dashboard');
            }
        @endphp
        <a href="{{ $dashboardUrl }}" class="sidebar-logo">
            <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="site logo" class="light-logo" style="max-height: 1.2rem;">
            <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="site logo" class="dark-logo" style="max-height: 1.2rem; filter: brightness(0) invert(1);">
            <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
        </a>
    </div>

    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">

            {{-- ═══════════════════════════════════════════
                 DASHBOARD LINK
            ═══════════════════════════════════════════ --}}
            <li>
                <a href="{{ $dashboardUrl }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- ═══════════════════════════════════════════
                 CUTI & IZIN (semua karyawan)
            ═══════════════════════════════════════════ --}}
            <li>
                <a href="{{ route('my-leave-requests.index') }}">
                    <iconify-icon icon="solar:calendar-mark-outline" class="menu-icon"></iconify-icon>
                    <span>Cuti &amp; Izin</span>
                </a>
            </li>
            <li>
                <a href="{{ route('my-kasbon.index') }}">
                    <iconify-icon icon="solar:cash-outline" class="menu-icon"></iconify-icon>
                    <span>Kasbon</span>
                </a>
            </li>

            {{-- ═══════════════════════════════════════════
                 TEKNISI & KENEK
            ═══════════════════════════════════════════ --}}
            @if($isStaff)
            <li>
                <a href="{{ route('teknisi.orders.index') }}">
                    <iconify-icon icon="solar:clipboard-list-outline" class="menu-icon"></iconify-icon>
                    <span>Semua Orderan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('jadwal.index') }}">
                    <iconify-icon icon="solar:calendar-outline" class="menu-icon"></iconify-icon>
                    <span>Jadwal Pemasangan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('teknisi.komisi') }}">
                    <iconify-icon icon="fluent-emoji-high-contrast:money-bag" class="menu-icon"></iconify-icon>
                    <span>Komisi Saya</span>
                </a>
            </li>
            <li>
                <a href="{{ route('teknisi.pencairan') }}">
                    <iconify-icon icon="solar:wallet-money-outline" class="menu-icon"></iconify-icon>
                    <span>Riwayat Pencairan</span>
                </a>
            </li>
            @if(Route::has('my-claims.index'))
            <li>
                <a href="{{ route('my-claims.index') }}">
                    <iconify-icon icon="mdi:receipt-text-check-outline" class="menu-icon"></iconify-icon>
                    <span>Klaim Saya</span>
                </a>
            </li>
            @endif
            @endif

            {{-- ═══════════════════════════════════════════
                 APPROVAL (Manager Operasional & siapa pun approver)
            ═══════════════════════════════════════════ --}}
            @if(Route::has('approvals.index') && $user?->hasAnyRole(['manager_operasional', 'owner']))
            <li>
                <a href="{{ route('approvals.index') }}">
                    <iconify-icon icon="mdi:clipboard-check-outline" class="menu-icon"></iconify-icon>
                    <span>Menunggu Approval Saya</span>
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 IT MANAGER (merangkap Purchasing & Finance)
            ═══════════════════════════════════════════ --}}
            @if($isItManager)
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:cart-outline" class="menu-icon"></iconify-icon>
                    <span>Purchasing</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('purchases.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Purchasing</a></li>
                    <li><a href="{{ route('purchase.reports.monthly') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Report Purchasing</a></li>
                    <li><a href="{{ route('suppliers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Supplier</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:handshake-outline" class="menu-icon"></iconify-icon>
                    <span>Mitra & Non-Franchise</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('mitras.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Mitra</a></li>
                    <li><a href="{{ route('mitra-invoices.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Invoice Mitra</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('petty-cash.index') }}">
                    <iconify-icon icon="mdi:wallet-outline" class="menu-icon"></iconify-icon>
                    <span>Petty Cash</span>
                </a>
            </li>
            <li>
                <a href="{{ route('bank-accounts.index') }}">
                    <iconify-icon icon="ri:bank-line" class="menu-icon"></iconify-icon>
                    <span>Rekonsiliasi Bank</span>
                </a>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:bank-transfer" class="menu-icon"></iconify-icon>
                    <span>Hutang & Piutang</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('hutang.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Hutang ke Vendor</a></li>
                    <li><a href="{{ route('piutang.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Piutang dari Pelanggan</a></li>
                </ul>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 DEVELOPMENT (strategi pertumbuhan perusahaan)
            ═══════════════════════════════════════════ --}}
            @if($isDevelopment && Route::has('development-reports.index'))
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:chart-line" class="menu-icon"></iconify-icon>
                    <span>Development</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('development-reports.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Laporan Berkala</a></li>
                    <li><a href="{{ route('development-plans.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Rencana Tahunan</a></li>
                </ul>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 EXPORT & IMPORT
            ═══════════════════════════════════════════ --}}
            @if($isExportImport && Route::has('import-orders.index'))
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:earth" class="menu-icon"></iconify-icon>
                    <span>Export & Import</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('import-orders.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Import (Bahan Baku)</a></li>
                    <li><a href="{{ route('export-orders.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Export (Produk Jadi)</a></li>
                    <li><a href="{{ route('international-partners.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Supplier & Buyer</a></li>
                </ul>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 STRATEGI PENGEMBANGAN (assign tugas Team Creative)
            ═══════════════════════════════════════════ --}}
            @if($isStrategiPengembangan && Route::has('creative-tasks.index'))
            <li>
                <a href="{{ route('creative-tasks.index') }}">
                    <iconify-icon icon="mdi:palette-outline" class="menu-icon"></iconify-icon>
                    <span>Tugas Team Creative</span>
                </a>
            </li>
            <li>
                <a href="{{ route('creative-tasks.create') }}">
                    <iconify-icon icon="mdi:plus-box-outline" class="menu-icon"></iconify-icon>
                    <span>Buat Tugas Baru</span>
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 TEAM CREATIVE (Designer, Videographer)
            ═══════════════════════════════════════════ --}}
            @if($isCreativeTeam && Route::has('creative-tasks.index'))
            <li>
                <a href="{{ route('creative-tasks.index') }}">
                    <iconify-icon icon="mdi:palette-outline" class="menu-icon"></iconify-icon>
                    <span>Tugas Saya</span>
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 OWNER
            ═══════════════════════════════════════════ --}}
            @if($isOwner)
            @if(Route::has('owner-monitoring.index'))
            <li>
                <a href="{{ route('owner-monitoring.index') }}">
                    <iconify-icon icon="mdi:view-dashboard-outline" class="menu-icon"></iconify-icon>
                    <span>Monitoring Semua Role</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('commission.index') }}">
                    <iconify-icon icon="fluent-emoji-high-contrast:money-bag" class="menu-icon"></iconify-icon>
                    <span>Komisi</span>
                </a>
            </li>
            <li>
                <a href="{{ route('analytics.index') }}">
                    <iconify-icon icon="lucide:bar-chart-3" class="menu-icon"></iconify-icon>
                    <span>Laporan Analitik</span>
                </a>
            </li>
            @if(Route::has('development-reports.index'))
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:chart-line" class="menu-icon"></iconify-icon>
                    <span>Development</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('development-reports.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Laporan Berkala</a></li>
                    <li><a href="{{ route('development-plans.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Rencana Tahunan</a></li>
                </ul>
            </li>
            @endif
            @endif

            {{-- ═══════════════════════════════════════════
                 CUSTOMER SERVICE
            ═══════════════════════════════════════════ --}}
            @if($isCs)
            <li>
                <a href="{{ route('leads.index') }}">
                    <iconify-icon icon="solar:target-outline" class="menu-icon"></iconify-icon>
                    <span>Lead Follow Up</span>
                </a>
            </li>
            <li>
                <a href="{{ route('customer.index') }}">
                    <iconify-icon icon="ph:users" class="menu-icon"></iconify-icon>
                    <span>Customer</span>
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 MARKETING (data scoped to own leads)
            ═══════════════════════════════════════════ --}}
            @if($isMarketing)
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:target-outline" class="menu-icon"></iconify-icon>
                    <span>CRM</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('leads.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Lead Saya</a></li>
                    <li><a href="{{ route('quotations.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Quotation Saya</a></li>
                    <li><a href="{{ route('sales-orders.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Sales Order</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('customer.index') }}">
                    <iconify-icon icon="ph:users" class="menu-icon"></iconify-icon>
                    <span>Customer Saya</span>
                </a>
            </li>
            <li>
                <a href="{{ route('marketing.komisi') }}">
                    <iconify-icon icon="fluent-emoji-high-contrast:money-bag" class="menu-icon"></iconify-icon>
                    <span>Komisi Saya</span>
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 REFERRAL & HIERARKI SALES (2026-10)
            ═══════════════════════════════════════════ --}}
            @if($isSales)
            <li>
                <a href="{{ route('sales.dashboard') }}">
                    <iconify-icon icon="mdi:account-tie-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard Sales</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sales-transactions.index') }}">
                    <iconify-icon icon="mdi:cart-outline" class="menu-icon"></iconify-icon>
                    <span>Transaksi Sales</span>
                </a>
            </li>
            @endif
            @if($isSales || $isMarketing)
            <li>
                <a href="{{ route('sales-network.my-network') }}">
                    <iconify-icon icon="mdi:account-group-outline" class="menu-icon"></iconify-icon>
                    <span>Jaringan Sales Saya</span>
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 MANAGER MARKETING
            ═══════════════════════════════════════════ --}}
            @if($isManagerMarketing)
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:target-outline" class="menu-icon"></iconify-icon>
                    <span>CRM</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('dashboard.crm') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Dashboard CRM</a></li>
                    <li><a href="{{ route('leads.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Lead Management</a></li>
                    <li><a href="{{ route('quotations.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Quotation</a></li>
                    <li><a href="{{ route('sales-orders.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Sales Order</a></li>
                    <li><a href="{{ route('jadwal.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Jadwal Pemasangan</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="ph:users" class="menu-icon"></iconify-icon>
                    <span>Customers</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('customer.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Cars</a></li>
                    <li><a href="{{ route('customer.building.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Buildings</a></li>
                    <li><a href="{{ route('ppf-customers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>PPF</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('commission.index') }}">
                    <iconify-icon icon="fluent-emoji-high-contrast:money-bag" class="menu-icon"></iconify-icon>
                    <span>Komisi Marketing</span>
                </a>
            </li>
            <li>
                <a href="{{ route('ranking.marketing') }}">
                    <iconify-icon icon="ri:bar-chart-grouped-line" class="menu-icon"></iconify-icon>
                    <span>Ranking Marketing</span>
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 MANAGER OPERASIONAL
            ═══════════════════════════════════════════ --}}
            @if($isManagerOps)
            <li>
                <a href="{{ route('work-orders.index') }}">
                    <iconify-icon icon="solar:clipboard-list-outline" class="menu-icon"></iconify-icon>
                    <span>Monitoring Pekerjaan</span>
                </a>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="ph:users" class="menu-icon"></iconify-icon>
                    <span>Customers</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('customer.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Cars</a></li>
                    <li><a href="{{ route('customer.building.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Buildings</a></li>
                    <li><a href="{{ route('ppf-customers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>PPF</a></li>
                    <li><a href="{{ route('ppf-installations.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Pemasangan PPF</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:cube-outline" class="menu-icon"></iconify-icon>
                    <span>Roll Stock</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('film-stock.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Stock Pool</a></li>
                    <li><a href="{{ route('film-stock.usages') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Laporan Pemakaian</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('commission.index') }}">
                    <iconify-icon icon="fluent-emoji-high-contrast:money-bag" class="menu-icon"></iconify-icon>
                    <span>Komisi</span>
                </a>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="ri:bar-chart-grouped-line" class="menu-icon"></iconify-icon>
                    <span>Ranking Karyawan</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('ranking.teknisi') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Ranking Teknisi</a></li>
                    <li><a href="{{ route('ranking.marketing') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Ranking Marketing</a></li>
                </ul>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 FINANCE
            ═══════════════════════════════════════════ --}}
            @if($isFinance)
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:receipt-text-outline" class="menu-icon"></iconify-icon>
                    <span>Invoice</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('customer.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Cars</a></li>
                    <li><a href="{{ route('customer.building.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Buildings</a></li>
                    <li><a href="{{ route('ppf-installations.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>PPF</a></li>
                    <li><a href="{{ route('mitra-invoices.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Mitra & Non-Franchise</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:cash-multiple" class="menu-icon"></iconify-icon>
                    <span>Payroll</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('payrolls.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Payroll</a></li>
                    <li><a href="{{ route('payrolls.create') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Generate Payroll</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('petty-cash.index') }}">
                    <iconify-icon icon="mdi:wallet-outline" class="menu-icon"></iconify-icon>
                    <span>Petty Cash</span>
                </a>
            </li>
            <li>
                <a href="{{ route('bank-accounts.index') }}">
                    <iconify-icon icon="ri:bank-line" class="menu-icon"></iconify-icon>
                    <span>Rekonsiliasi Bank</span>
                </a>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:bank-transfer" class="menu-icon"></iconify-icon>
                    <span>Hutang & Piutang</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('hutang.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Hutang ke Vendor</a></li>
                    <li><a href="{{ route('piutang.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Piutang dari Pelanggan</a></li>
                    <li><a href="{{ route('finance.payment-status') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Status Pembayaran Customer</a></li>
                </ul>
            </li>
            <li>
                <a href="{{ route('commission.withdrawals.index') }}">
                    <iconify-icon icon="solar:wallet-money-outline" class="menu-icon"></iconify-icon>
                    <span>Pencairan Komisi</span>
                </a>
            </li>
            @if(Route::has('claims-disbursement.index'))
            <li>
                <a href="{{ route('claims-disbursement.index') }}">
                    <iconify-icon icon="mdi:cash-fast" class="menu-icon"></iconify-icon>
                    <span>Pencairan Klaim Karyawan</span>
                </a>
            </li>
            @endif
            @endif

            {{-- Data Kasbon: role gate disamakan persis dengan middleware route kasbons.index
                 (superadmin|admin|finance|it_manager) — sengaja TIDAK dinest di dalam blok
                 @if($isFinance) di atas, karena superadmin/admin belum tentu juga punya role
                 'finance' sehingga tidak akan lolos kalau ikut nested di situ. --}}
            @if($isFinance || $isAdmin || $isSuperadmin || $isItManager)
            <li>
                <a href="{{ route('kasbons.index') }}">
                    <iconify-icon icon="mdi:cash-refund" class="menu-icon"></iconify-icon>
                    <span>Data Kasbon</span>
                </a>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 PURCHASING
            ═══════════════════════════════════════════ --}}
            @if($isPurchasing)
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:cart-outline" class="menu-icon"></iconify-icon>
                    <span>Purchasing</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('purchases.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Purchasing</a></li>
                    <li><a href="{{ route('purchase.reports.monthly') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Report Purchasing</a></li>
                    <li><a href="{{ route('suppliers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Supplier</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:cube-outline" class="menu-icon"></iconify-icon>
                    <span>Roll Stock</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('film-stock.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Stock Pool</a></li>
                    <li><a href="{{ route('film-stock.create') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Roll Masuk / Import</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:handshake-outline" class="menu-icon"></iconify-icon>
                    <span>Mitra & Non-Franchise</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('mitras.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Mitra</a></li>
                    <li><a href="{{ route('mitra-invoices.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Invoice Mitra</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:package-variant-closed" class="menu-icon"></iconify-icon>
                    <span>Barang Pendukung</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('supporting-goods.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Daftar Stok</a></li>
                    <li><a href="{{ route('supporting-goods.create') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Tambah Barang</a></li>
                </ul>
            </li>
            @endif

            {{-- ═══════════════════════════════════════════
                 ADMIN + SUPERADMIN (full operational)
            ═══════════════════════════════════════════ --}}
            @if($isAdmin || $isSuperadmin)

            <li class="sidebar-menu-group-title">CRM & Penjualan</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:target-outline" class="menu-icon"></iconify-icon>
                    <span>CRM</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('dashboard.crm') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Dashboard CRM</a></li>
                    <li><a href="{{ route('leads.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Lead Management</a></li>
                    <li><a href="{{ route('quotations.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Quotation</a></li>
                    <li><a href="{{ route('sales-orders.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Sales Order</a></li>
                    <li><a href="{{ route('jadwal.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Jadwal Pemasangan</a></li>
                </ul>
            </li>

            <li>
                <a href="{{ route('work-orders.index') }}">
                    <iconify-icon icon="solar:clipboard-list-outline" class="menu-icon"></iconify-icon>
                    <span>Monitoring Pekerjaan</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="ph:users" class="menu-icon"></iconify-icon>
                    <span>Customers</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('customer.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Cars</a></li>
                    <li><a href="{{ route('customer.building.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Buildings</a></li>
                    <li><a href="{{ route('ppf-customers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>PPF</a></li>
                    <li><a href="{{ route('ppf-installations.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Pemasangan PPF</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:receipt-text-outline" class="menu-icon"></iconify-icon>
                    <span>Invoice</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('customer.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Cars</a></li>
                    <li><a href="{{ route('customer.building.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Buildings</a></li>
                    <li><a href="{{ route('ppf-installations.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>PPF</a></li>
                </ul>
            </li>

            @if($isSuperadmin)
            {{-- Superadmin-only items --}}

            <li class="sidebar-menu-group-title">Operasional & Stok</li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:cart-outline" class="menu-icon"></iconify-icon>
                    <span>Purchasing</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('purchases.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Purchasing</a></li>
                    <li><a href="{{ route('purchase.reports.monthly') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Report Purchasing</a></li>
                    <li><a href="{{ route('suppliers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Supplier</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:cube-outline" class="menu-icon"></iconify-icon>
                    <span>Roll Stock</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('film-stock.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Stock Pool</a></li>
                    <li><a href="{{ route('film-stock.create') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Roll Masuk / Import</a></li>
                    <li><a href="{{ route('film-stock.usages') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Laporan Pemakaian</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:handshake-outline" class="menu-icon"></iconify-icon>
                    <span>Mitra & Non-Franchise</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('mitras.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Mitra</a></li>
                    <li><a href="{{ route('mitra-invoices.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Invoice Mitra</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:package-variant-closed" class="menu-icon"></iconify-icon>
                    <span>Barang Pendukung</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('supporting-goods.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Daftar Stok</a></li>
                    <li><a href="{{ route('supporting-goods.create') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Tambah Barang</a></li>
                </ul>
            </li>

            <li>
                <a href="{{ route('ppf-products.index') }}">
                    <iconify-icon icon="mdi:shield-car" class="menu-icon"></iconify-icon>
                    <span>Produk PPF</span>
                </a>
            </li>

            <li>
                <a href="{{ route('film-complaints.index') }}">
                    <iconify-icon icon="hugeicons:complaint" class="menu-icon"></iconify-icon>
                    <span>Complain / Replacement</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.claims.index') }}">
                    <iconify-icon icon="streamline-flex:warranty-badge-highlight" class="menu-icon"></iconify-icon>
                    <span>Warranty Claims</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">Keuangan</li>

            <li>
                <a href="{{ route('commission.index') }}">
                    <iconify-icon icon="fluent-emoji-high-contrast:money-bag" class="menu-icon"></iconify-icon>
                    <span>Komisi</span>
                </a>
            </li>

            <li>
                <a href="{{ route('commission.withdrawals.index') }}">
                    <iconify-icon icon="solar:wallet-money-outline" class="menu-icon"></iconify-icon>
                    <span>Pencairan Komisi</span>
                </a>
            </li>

            <li>
                <a href="{{ route('commission.periods.index') }}">
                    <iconify-icon icon="mdi:calendar-check-outline" class="menu-icon"></iconify-icon>
                    <span>Periode Komisi</span>
                </a>
            </li>

            <li>
                <a href="{{ route('commission.override.index') }}">
                    <iconify-icon icon="mdi:cog-transfer-outline" class="menu-icon"></iconify-icon>
                    <span>Setting Override Komisi</span>
                </a>
            </li>

            <li>
                <a href="{{ route('commission.settings.index') }}">
                    <iconify-icon icon="mdi:account-cash-outline" class="menu-icon"></iconify-icon>
                    <span>Setting Komisi Karyawan</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:sitemap-outline" class="menu-icon"></iconify-icon>
                    <span>Referral & Sales</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('sales-network.admin-index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Jaringan Sales</a></li>
                    <li><a href="{{ route('sales-network.pending') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Approval Rekrutan</a></li>
                    <li><a href="{{ route('sales-products.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Produk Sales</a></li>
                    <li><a href="{{ route('sales-transactions.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Transaksi Sales</a></li>
                </ul>
            </li>

            @if(Route::has('claims-disbursement.index'))
            <li>
                <a href="{{ route('claims-disbursement.index') }}">
                    <iconify-icon icon="mdi:cash-fast" class="menu-icon"></iconify-icon>
                    <span>Pencairan Klaim Karyawan</span>
                </a>
            </li>
            @endif

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:cash-multiple" class="menu-icon"></iconify-icon>
                    <span>Payroll</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('payrolls.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Data Payroll</a></li>
                    <li><a href="{{ route('payrolls.create') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Generate Payroll</a></li>
                </ul>
            </li>

            <li>
                <a href="{{ route('petty-cash.index') }}">
                    <iconify-icon icon="mdi:wallet-outline" class="menu-icon"></iconify-icon>
                    <span>Petty Cash</span>
                </a>
            </li>

            <li>
                <a href="{{ route('bank-accounts.index') }}">
                    <iconify-icon icon="ri:bank-line" class="menu-icon"></iconify-icon>
                    <span>Rekonsiliasi Bank</span>
                </a>
            </li>

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:bank-transfer" class="menu-icon"></iconify-icon>
                    <span>Hutang & Piutang</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('hutang.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Hutang ke Vendor</a></li>
                    <li><a href="{{ route('piutang.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Piutang dari Pelanggan</a></li>
                    <li><a href="{{ route('finance.payment-status') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Status Pembayaran Customer</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">SDM & Absensi</li>

            <li>
                <a href="{{ route('employee.index') }}">
                    <iconify-icon icon="healthicons:factory-worker-outline" class="menu-icon"></iconify-icon>
                    <span>Employees</span>
                </a>
            </li>

            @if(Route::has('employee-health-balances.index'))
            <li>
                <a href="{{ route('employee-health-balances.index') }}">
                    <iconify-icon icon="mdi:hospital-box-outline" class="menu-icon"></iconify-icon>
                    <span>Saldo Biaya Kesehatan</span>
                </a>
            </li>
            @endif

            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="mdi:calendar-check-outline" class="menu-icon"></iconify-icon>
                    <span>Absensi</span>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="{{ route('attendances.daily') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Absensi Harian</a></li>
                    <li><a href="{{ route('attendances.report') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Laporan Absensi Bulanan</a></li>
                    <li><a href="{{ route('attendances.monthlyReport') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Laporan Gaji Bulanan</a></li>
                    <li><a href="{{ route('meal-allowances.weekly') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Uang Makan Mingguan</a></li>
                    <li><a href="{{ route('shifts.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Shift Kerja</a></li>
                    <li><a href="{{ route('leave-requests.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Izin & Cuti</a></li>
                    <li><a href="{{ route('leave-requests.schedule') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Jadwal Libur</a></li>
                </ul>
            </li>

            <li class="sidebar-menu-group-title">Laporan & Audit</li>

            <li>
                <a href="{{ route('analytics.index') }}">
                    <iconify-icon icon="lucide:bar-chart-3" class="menu-icon"></iconify-icon>
                    <span>Laporan Analitik</span>
                </a>
            </li>

            <li>
                <a href="{{ route('audit-logs.index') }}">
                    <iconify-icon icon="mdi:history" class="menu-icon"></iconify-icon>
                    <span>Audit Log</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">Website Publik</li>

            <li>
                <a href="{{ route('dealers.index') }}">
                    <iconify-icon icon="mdi:map-marker-multiple-outline" class="menu-icon"></iconify-icon>
                    <span>Dealers (Website)</span>
                </a>
            </li>

            <li>
                <a href="{{ route('articles.index') }}">
                    <iconify-icon icon="mdi:newspaper-variant-outline" class="menu-icon"></iconify-icon>
                    <span>Artikel (Website)</span>
                </a>
            </li>

            <li>
                <a href="{{ route('gallery.index') }}">
                    <iconify-icon icon="ph:images" class="menu-icon"></iconify-icon>
                    <span>Gallery</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">Administrasi Sistem</li>

            <li>
                <a href="{{ route('branches.index') }}">
                    <iconify-icon icon="proicons:branch" class="menu-icon"></iconify-icon>
                    <span>Branch</span>
                </a>
            </li>

            <li>
                <a href="{{ route('users.index') }}">
                    <iconify-icon icon="mdi:users" class="menu-icon"></iconify-icon>
                    <span>Users</span>
                </a>
            </li>

            <li>
                <a href="{{ route('roles.index') }}">
                    <iconify-icon icon="mdi:shield-account-outline" class="menu-icon"></iconify-icon>
                    <span>Roles & Permissions</span>
                </a>
            </li>

            <li>
                <a href="{{ route('notification-config.index') }}">
                    <iconify-icon icon="lucide:bell-ring" class="menu-icon"></iconify-icon>
                    <span>Notifikasi Otomatis</span>
                </a>
            </li>
            @endif {{-- end superadmin-only --}}

            @endif {{-- end isAdmin || isSuperadmin --}}

        </ul>
    </div>
</aside>
