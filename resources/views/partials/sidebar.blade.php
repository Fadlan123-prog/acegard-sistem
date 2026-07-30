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
            $isFinance          = $user?->hasRole('finance') ?? false;
            $isPurchasing       = $user?->hasRole('purchasing') ?? false;
            $isTeknisi          = $user?->hasRole('teknisi') ?? false;
            $isKenek            = $user?->hasRole('kenek') ?? false;
            $isCs               = $user?->hasRole('customer_service') ?? false;
            $isStaff            = $isTeknisi || $isKenek;

            // Dashboard URL per role
            if ($isOwner) {
                $dashboardUrl = route('owner.dashboard');
            } elseif ($isMarketing || $isManagerMarketing) {
                $dashboardUrl = route('marketing.dashboard');
            } elseif ($isCs) {
                $dashboardUrl = route('cs.dashboard');
            } elseif ($isManagerOps) {
                $dashboardUrl = route('manager-ops.dashboard');
            } elseif ($isFinance) {
                $dashboardUrl = route('finance.dashboard');
            } elseif ($isPurchasing) {
                $dashboardUrl = route('purchasing.dashboard');
            } elseif ($isStaff) {
                $dashboardUrl = route('teknisi.dashboard');
            } else {
                $dashboardUrl = route('dashboard');
            }
        @endphp
        <a href="{{ $dashboardUrl }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
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
            @endif

            {{-- ═══════════════════════════════════════════
                 OWNER
            ═══════════════════════════════════════════ --}}
            @if($isOwner)
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
                    <span>Penugasan Kerja</span>
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
                    <li><a href="{{ route('film-stock-transfers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>History Transfer</a></li>
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
                </ul>
            </li>
            <li>
                <a href="{{ route('commission.withdrawals.index') }}">
                    <iconify-icon icon="solar:wallet-money-outline" class="menu-icon"></iconify-icon>
                    <span>Pencairan Komisi</span>
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
                    <li><a href="{{ route('film-stock-transfers.create') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Transfer ke Cabang</a></li>
                    <li><a href="{{ route('film-stock-transfers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>History Transfer</a></li>
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
                    <span>Penugasan Kerja</span>
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

            <li>
                <a href="{{ route('commission.index') }}">
                    <iconify-icon icon="fluent-emoji-high-contrast:money-bag" class="menu-icon"></iconify-icon>
                    <span>Komisi</span>
                </a>
            </li>

            @if($isSuperadmin)
            {{-- Superadmin-only items --}}
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
                <a href="{{ route('audit-logs.index') }}">
                    <iconify-icon icon="mdi:history" class="menu-icon"></iconify-icon>
                    <span>Audit Log</span>
                </a>
            </li>

            <li>
                <a href="{{ route('employee.index') }}">
                    <iconify-icon icon="healthicons:factory-worker-outline" class="menu-icon"></iconify-icon>
                    <span>Employees</span>
                </a>
            </li>

            <li>
                <a href="{{ route('ppf-products.index') }}">
                    <iconify-icon icon="mdi:shield-car" class="menu-icon"></iconify-icon>
                    <span>Produk PPF</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.claims.index') }}">
                    <iconify-icon icon="streamline-flex:warranty-badge-highlight" class="menu-icon"></iconify-icon>
                    <span>Warranty Claims</span>
                </a>
            </li>

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
                    <li><a href="{{ route('film-stock-transfers.create') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Transfer ke Cabang</a></li>
                    <li><a href="{{ route('film-stock-transfers.index') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>History Transfer</a></li>
                    <li><a href="{{ route('film-stock.usages') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>Laporan Pemakaian</a></li>
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
                <a href="{{ route('film-complaints.index') }}">
                    <iconify-icon icon="hugeicons:complaint" class="menu-icon"></iconify-icon>
                    <span>Complain / Replacement</span>
                </a>
            </li>

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
                <a href="{{ route('analytics.index') }}">
                    <iconify-icon icon="lucide:bar-chart-3" class="menu-icon"></iconify-icon>
                    <span>Laporan Analitik</span>
                </a>
            </li>

            <li>
                <a href="{{ route('notification-config.index') }}">
                    <iconify-icon icon="lucide:bell-ring" class="menu-icon"></iconify-icon>
                    <span>Notifikasi Otomatis</span>
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

            <li>
                <a href="{{ route('gallery.index') }}">
                    <iconify-icon icon="ph:images" class="menu-icon"></iconify-icon>
                    <span>Gallery</span>
                </a>
            </li>
            @endif {{-- end superadmin-only --}}

            @endif {{-- end isAdmin || isSuperadmin --}}

        </ul>
    </div>
</aside>
