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
                    <div class="dropdown-menu to-top dropdown-menu-lg p-0">
                        <div class="m-16 py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <h6 class="text-lg text-primary-light fw-semibold mb-0">Branch</h6>
                            </div>
                            <span class="text-primary-600 fw-semibold text-lg w-40-px h-40-px rounded-circle bg-base d-flex justify-content-center align-items-center">05</span>
                        </div>

                        <div class="max-h-400-px overflow-y-auto scroll-sm pe-4">
  @foreach(optional(auth()->user())->branches ?? [] as $branch)
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
                            <a href="javascript:void(0)" class="text-primary-600 fw-semibold text-md">See All Notification</a>
                        </div>

                    </div>
                </div><!-- Notification dropdown end -->

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
