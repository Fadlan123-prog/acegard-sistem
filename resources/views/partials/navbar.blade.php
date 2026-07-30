<div class="navbar-header">
    <div class="row align-items-center justify-content-between">
        <div class="col-auto">
            <div class="d-flex flex-wrap align-items-center gap-4">
                <button type="button" class="sidebar-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon text-2xl non-active"></iconify-icon>
                    <iconify-icon icon="iconoir:arrow-right" class="icon text-2xl active"></iconify-icon>
                </button>
                <button type="button" class="sidebar-mobile-toggle">
                    <iconify-icon icon="heroicons:bars-3-solid" class="icon"></iconify-icon>
                </button>
                <form class="navbar-search">
                    <input type="text" name="search" placeholder="Search">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <button type="button" data-theme-toggle class="w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center"></button>
                <div class="dropdown">
                    <button class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center" type="button" data-bs-toggle="dropdown">
                        <iconify-icon icon="proicons-branch" class="text-primary-light text-xl"></iconify-icon>
                    </button>
                    @php
                        $navUser = auth()->user();
                        $navBranches = $navUser?->hasRole('superadmin')
                            ? \App\Models\Branch::orderByRaw("FIELD(name, 'Acegard Pusat') DESC")->orderBy('name')->get()
                            : ($navUser?->branches ?? collect());
                    @endphp
                    <div class="dropdown-menu to-top dropdown-menu-lg p-0">
                        <div class="m-16 py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <h6 class="text-lg text-primary-light fw-semibold mb-0">Branch</h6>
                            </div>
                            <span class="text-primary-600 fw-semibold text-lg w-40-px h-40-px rounded-circle bg-base d-flex justify-content-center align-items-center">{{ $navBranches->count() }}</span>
                        </div>
                        <div class="max-h-400-px overflow-y-auto scroll-sm pe-4">
                        @foreach($navBranches as $branch)
                            <form method="POST" action="{{ route('switch-branch') }}">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                            <button type="submit"
                                class="w-100 text-start px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between {{ session('active_branch_id') == $branch->id ? 'bg-neutral-50' : '' }}">
                                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                                <span class="w-44-px h-44-px bg-primary-subtle text-primary-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                                    {{ strtoupper(substr($branch->name, 0, 2)) }}
                                </span>
                                <div>
                                    <h6 class="text-md fw-semibold mb-1">{{ $branch->name }}</h6>
                                    <p class="mb-0 text-sm text-secondary-light">{{ $branch->city }}</p>
                                </div>
                                </div>
                                @if(session('active_branch_id') == $branch->id)
                                <span class="text-sm text-success-main flex-shrink-0">Aktif</span>
                                @endif
                            </button>
                            </form>
                        @endforeach
                        </div>


                        <div class="text-center py-12 px-16">
                            <a href="{{ route('branches.index') }}" class="text-primary-600 fw-semibold text-md">Kelola Cabang</a>
                        </div>

                    </div>
                </div>

                @if(auth()->user()->hasRole('superadmin'))
                @php
                $unreadCount  = auth()->user()->unreadNotifications()->count();
                $latestNotifs = auth()->user()->unreadNotifications()->latest()->take(8)->get();

                $notifMeta = [
                    'commission_period_due'   => ['icon' => 'mdi:calendar-clock',  'color' => '#d97706', 'label' => 'Komisi Jatuh Tempo'],
                    'job_assigned'            => ['icon' => 'mdi:car-wrench',       'color' => '#2563eb', 'label' => 'Pekerjaan Baru'],
                    'unreleased_commission'   => ['icon' => 'mdi:cash-remove',      'color' => '#dc2626', 'label' => 'Komisi Belum Dirilis'],
                    'warranty_claim_submitted'=> ['icon' => 'mdi:shield-alert',     'color' => '#7c3aed', 'label' => 'Klaim Garansi'],
                    'automated_alert'         => ['icon' => 'lucide:bell-ring',     'color' => '#0891b2', 'label' => 'Notifikasi Otomatis'],
                ];
                @endphp

                <div class="dropdown">
                <button class="btn btn-light position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                    <iconify-icon icon="mdi:bell-outline"></iconify-icon>
                    @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                    @endif
                </button>

                <div class="dropdown-menu dropdown-menu-end p-0" style="width: 380px;">
                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <strong class="text-sm">Notifikasi</strong>
                        @if($unreadCount > 0)
                        <a href="{{ route('notifications.readAll') }}" class="btn btn-sm btn-outline-secondary py-0" style="font-size:.75rem;">Baca semua</a>
                        @endif
                    </div>

                    <div class="list-group list-group-flush" style="max-height: 380px; overflow-y:auto;">
                    @forelse($latestNotifs as $n)
                        @php
                        $d    = $n->data;
                        $type = $d['type'] ?? (isset($d['claim_id']) ? 'warranty_claim_submitted' : 'unknown');
                        $meta = $notifMeta[$type] ?? ['icon' => 'mdi:bell', 'color' => '#6b7280', 'label' => 'Notifikasi'];
                        $msg  = $d['message'] ?? ($d['complaint'] ?? '');
                        @endphp
                        <a href="{{ route('notifications.open', $n->id, false) }}"
                           class="list-group-item list-group-item-action px-3 py-2">
                            <div class="d-flex align-items-start gap-2">
                                <span class="flex-shrink-0 mt-1 d-flex align-items-center justify-content-center rounded-circle"
                                      style="width:32px;height:32px;background:{{ $meta['color'] }}1a">
                                    <iconify-icon icon="{{ $meta['icon'] }}" style="color:{{ $meta['color'] }};font-size:16px;"></iconify-icon>
                                </span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-center gap-1 mb-1">
                                        <span class="fw-semibold" style="font-size:.78rem;">{{ $meta['label'] }}</span>
                                        <small class="text-muted text-nowrap" style="font-size:.7rem;">{{ $n->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if($msg)
                                    <div class="text-muted" style="font-size:.75rem;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $msg }}</div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted small">
                            <iconify-icon icon="mdi:bell-off-outline" style="font-size:32px;opacity:.4;display:block;margin-bottom:6px;"></iconify-icon>
                            Tidak ada notifikasi baru.
                        </div>
                    @endforelse
                    </div>

                    <div class="px-3 py-2 border-top text-center">
                        <a href="{{ route('notifications.index') }}" class="text-sm">Lihat semua notifikasi</a>
                    </div>
                </div>
                </div>
                @endif
                <!-- Notification dropdown end -->

                <div class="dropdown">
                    <button class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center" type="button" data-bs-toggle="dropdown">
                        <iconify-icon icon="mage:user" class="text-primary-light text-xl"></iconify-icon>
                    </button>
                    </button>
                    <div class="dropdown-menu to-top dropdown-menu-sm">
                        <div class="py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                            <div>
                                @auth
                                    <h6 class="text-lg text-primary-light fw-semibold mb-2">
                                        {{ auth()->user()->name }}
                                    </h6>
                                @endauth
                                <span class="text-secondary-light fw-medium text-sm">Admin</span>
                            </div>
                            <button type="button" class="hover-text-danger">
                                <iconify-icon icon="radix-icons:cross-1" class="icon text-xl"></iconify-icon>
                            </button>
                        </div>
                        <ul class="to-top-list">

                            <li>
                                <a href="#" class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> Log Out
                                </a>
                            </li>

                        </ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div><!-- Profile dropdown end -->
            </div>
        </div>
    </div>
</div>
