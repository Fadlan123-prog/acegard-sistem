<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Acegard') }} — @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png"  href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <!-- remix icon font css  -->
    <link rel="stylesheet"  href="{{ asset('assets/css/remixicon.css') }}">
    <!-- BootStrap css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <!-- Apex Chart css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/apexcharts.css') }}">
    <!-- Data Table css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/dataTables.min.css') }}">
    <!-- Text Editor css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/editor-katex.min.css') }}">
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/editor.atom-one-dark.min.css') }}">
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/editor.quill.snow.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Date picker css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
    <!-- Calendar css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/full-calendar.css') }}">
    <!-- Vector Map css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/jquery-jvectormap-2.0.5.css') }}">
    <!-- Popup css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/magnific-popup.css') }}">
    <!-- Slick Slider css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/slick.css') }}">
    <!-- prism css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/prism.css') }}">
    <!-- file upload css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/lib/file-upload.css') }}">

    <link rel="stylesheet"  href="{{ asset('assets/css/lib/audioplayer.css') }}">
    <!-- main css -->
    <link rel="stylesheet"  href="{{ asset('assets/css/style.css') }}">
    <!-- UI component system -->
    <link rel="stylesheet"  href="{{ asset('assets/css/ui-components.css') }}">
    @stack('page-styles')
    <style>
    .ppf-table-responsive{
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
    }

    /* biar tabel tetap rapih dan tidak “pecah” layout */
    .ppf-table-responsive table{
        min-width: 980px; /* sesuaikan, tapi ini bikin aman di mobile */
    }

    .ppf-table-responsive th,
    .ppf-table-responsive td{
        vertical-align: middle;
        white-space: nowrap;
    }

    /* kolom tertentu boleh wrap (optional) */
    .ppf-table-responsive td.wrap{
        white-space: normal;
    }
    </style>
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            min-height: 54px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 10px !important;
            padding: 6px 10px !important;
            display: flex !important;
            align-items: center !important;
            background: #fff !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 0 !important;
            padding-right: 24px !important;
            line-height: 1.25 !important;
            color: #111827 !important;
            width: 100%;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 54px !important;
            right: 8px !important;
        }

        .select2-dropdown {
            border: 1px solid #d1d5db !important;
            border-radius: 10px !important;
            overflow: hidden !important;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12) !important;
        }

        .select2-search--dropdown {
            padding: 10px !important;
            background: #f8fafc !important;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            padding: 9px 12px !important;
            outline: none !important;
        }

        .select2-results__option {
            padding: 0 !important;
        }

        .select2-results__option--highlighted[aria-selected] {
            background: #eff6ff !important;
            color: #111827 !important;
        }

        .customer-option {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            background: #fff;
        }

        .customer-option-top {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .customer-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        .customer-main {
            flex: 1;
            min-width: 0;
        }

        .customer-name {
            font-weight: 700;
            font-size: 14px;
            color: #111827;
            margin-bottom: 4px;
        }

        .customer-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            color: #64748b;
            font-size: 12px;
        }

        .customer-meta span {
            background: #f1f5f9;
            border-radius: 999px;
            padding: 3px 8px;
        }

        .customer-branch {
            margin-top: 8px;
            font-size: 12px;
            color: #475569;
            font-weight: 500;
        }

        .customer-selected {
            display: flex;
            flex-direction: column;
            gap: 3px;
            padding-top: 1px;
        }

        .customer-selected-name {
            font-weight: 700;
            font-size: 14px;
            color: #111827;
        }

        .customer-selected-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 11px;
            color: #64748b;
        }

        .customer-selected-meta span {
            background: #f1f5f9;
            border-radius: 999px;
            padding: 2px 7px;
        }

        .select2-selection__clear {
            margin-right: 10px !important;
            color: #ef4444 !important;
            font-size: 18px !important;
            font-weight: 700 !important;
        }
    </style>
</head>

<body>

    <!-- ..::  header area start ::.. -->
    @include('partials.sidebar')
    <!-- ..::  header area end ::.. -->

    <main class="dashboard-main">

        <!-- ..::  navbar start ::.. -->
        @include('partials.navbar')
        <!-- ..::  navbar end ::.. -->
        <div class="dashboard-main-body">

            <!-- ..::  breadcrumb  start ::.. -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
                <h6 class="fw-semibold mb-0">@yield('title')</h6>
                <ul class="d-flex align-items-center gap-2">
                    <li class="fw-medium">
                        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                            <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                            Dashboard
                        </a>
                    </li>
                    <li class="text-secondary-light">-</li>
                    <li class="fw-medium text-secondary-light">@yield('title')</li>
                </ul>
            </div>
            <!-- ..::  header area end ::.. -->

            {{-- Global flash messages — pull() consumes the value so child views don't duplicate it --}}
            @php
                $flashSuccess = session()->pull('success');
                $flashError   = session()->pull('error');
                $flashWarning = session()->pull('warning');
            @endphp
            @if($flashSuccess)
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-24" role="alert">
                <i class="ri-checkbox-circle-line fs-5 flex-shrink-0"></i>
                <span>{{ $flashSuccess }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if($flashError)
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-24" role="alert">
                <i class="ri-error-warning-line fs-5 flex-shrink-0"></i>
                <span>{{ $flashError }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if($flashWarning)
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-24" role="alert">
                <i class="ri-alert-line fs-5 flex-shrink-0"></i>
                <span>{{ $flashWarning }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @yield('content')

        </div>
        <!-- ..::  footer  start ::.. -->
        @include('partials.footer')
        <!-- ..::  footer area end ::.. -->

    </main>

    <!-- ..::  scripts  start ::.. -->
        <!-- jQuery library js -->
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <!-- Bootstrap js -->
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <!-- Apex Chart js -->
    <script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
    <!-- Data Table js -->
    <script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
    <!-- Iconify Font js -->
    <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
    <!-- jQuery UI js -->
    <script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
    <!-- Vector Map js -->
    <script src="{{ asset('assets/js/lib/jquery-jvectormap-2.0.5.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/jquery-jvectormap-world-mill-en.js') }}"></script>
    <!-- Popup js -->
    <script src="{{ asset('assets/js/lib/magnifc-popup.min.js') }}"></script>
    <!-- Slick Slider js -->
    <script src="{{ asset('assets/js/lib/slick.min.js') }}"></script>
    <!-- prism js -->
    <script src="{{ asset('assets/js/lib/prism.js') }}"></script>
    <!-- file upload js -->
    <script src="{{ asset('assets/js/lib/file-upload.js') }}"></script>
    <!-- audioplayer -->
    <script src="{{ asset('assets/js/lib/audioplayer.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- main js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <!-- ..::  scripts  end ::.. -->

    <!-- Global confirmation modal -->
    <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-semibold">Konfirmasi</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2">
                    <p class="text-muted mb-0" id="confirmActionMessage">Yakin lanjutkan tindakan ini?</p>
                </div>
                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-danger" id="confirmActionBtn">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Global UX: auto-dismiss success alerts, Bootstrap confirm modal, form loading state -->
    <script>
    (function () {
        var pendingForm = null;
        var confirmModal = null;

        function getConfirmModal() {
            if (!confirmModal) {
                confirmModal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
            }
            return confirmModal;
        }

        function init() {
            // Auto-dismiss success alerts after 5 s
            document.querySelectorAll('.alert-success').forEach(function (el) {
                setTimeout(function () {
                    bootstrap.Alert.getOrCreateInstance(el).close();
                }, 5000);
            });

            // Upgrade forms with onsubmit="return confirm(...)" to Bootstrap modal
            document.querySelectorAll('form[onsubmit]').forEach(function (form) {
                var attr = form.getAttribute('onsubmit');
                if (!attr.includes('confirm(')) return;
                var match = attr.match(/confirm\(['"]([^'"]+)['"]\)/);
                var msg = match ? match[1] : 'Yakin lanjutkan tindakan ini?';
                form.removeAttribute('onsubmit');
                form.dataset.confirmMsg = msg;
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    document.getElementById('confirmActionMessage').textContent = this.dataset.confirmMsg;
                    pendingForm = this;
                    getConfirmModal().show();
                });
            });

            // Confirm button handler
            document.getElementById('confirmActionBtn').addEventListener('click', function () {
                if (pendingForm) {
                    var f = pendingForm;
                    pendingForm = null;
                    getConfirmModal().hide();
                    f.submit();
                }
            });

            // Disable submit button on form submit to prevent double-submission
            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    var btn = form.querySelector('[type="submit"]:not([data-no-loading])');
                    if (btn) {
                        setTimeout(function () { btn.disabled = true; }, 0);
                    }
                });
            });
        }

        // $(function) is reliable at bottom of body — fires immediately if DOM is ready
        if (typeof $ !== 'undefined') {
            $(init);
        } else {
            document.addEventListener('DOMContentLoaded', init);
        }
    })();
    </script>
    @stack('page-scripts')
    <!-- ..::  scripts  end ::.. -->

</body>

</html>
