<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Tautan atur-password memuat token di URL: jangan bocor lewat Referer dan jangan diindeks. --}}
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — Acegard</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
    <section class="auth bg-base d-flex flex-wrap justify-content-center">
        <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
            <div class="card w-100">
                <div class="max-w-464-px mx-auto my-60 w-100">
                    <div class="text-center">
                        <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard" style="width: 170px;" class="mb-40">
                        <h4 class="mb-16">@yield('heading')</h4>
                        @hasSection('subheading')
                            <p class="mb-32 text-secondary-light">@yield('subheading')</p>
                        @endif

                        @if(session('status'))
                            <div class="alert alert-success text-start">{{ session('status') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger text-start">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $msg)
                                        <li>{{ $msg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    @yield('content')
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
    <script>
        $(".toggle-password").on("click", function () {
            var isHidden = $(this).attr("icon") === "mdi:eye-off-outline";
            $(this).attr("icon", isHidden ? "mdi:eye-outline" : "mdi:eye-off-outline");
            var input = $($(this).attr("data-toggle"));
            input.attr("type", input.attr("type") === "password" ? "text" : "password");
        });
    </script>
</body>

</html>
