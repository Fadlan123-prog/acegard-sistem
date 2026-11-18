<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sedang Maintenance — Acegard</title>
    <style>
        :root { --bg:#F3F5FB; --surface:#fff; --text:#111827; --muted:#4B5563; --primary:#2563EB; --border:#DDE2F0; }
        @media (prefers-color-scheme: dark) {
            :root { --bg:#0F172A; --surface:#1C2537; --text:#F1F5F9; --muted:#94A3B8; --primary:#60A5FA; --border:#1E3050; }
        }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px;
               background:var(--bg); color:var(--text); font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; line-height:1.5; }
        main { width:100%; max-width:480px; text-align:center; background:var(--surface); border:1px solid var(--border);
               border-radius:12px; padding:40px 24px; }
        img { height:36px; margin-bottom:24px; }
        h1 { font-size:1.5rem; margin:0 0 8px; }
        p { margin:0 0 20px; color:var(--muted); }
        .actions { display:flex; flex-wrap:wrap; gap:8px; justify-content:center; }
        a { display:inline-flex; align-items:center; min-height:44px; padding:0 16px; border-radius:8px; text-decoration:none;
            font-weight:600; border:1px solid var(--border); color:var(--text); }
        a.primary { background:var(--primary); border-color:var(--primary); color:#fff; }
        a:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }
    </style>
</head>
<body>
    <main role="main">
        <img src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard">
        <h1>Website sedang dalam maintenance</h1>
        <p>Kami sedang melakukan pembaruan agar layanan lebih baik. Silakan kembali beberapa saat lagi.</p>
        <div class="actions">
            <a class="primary" href="tel:+6282121212093">Hubungi Kami</a>
            <a href="mailto:acegardindonesia@gmail.com">Kirim Email</a>
            <a href="{{ route('warranty.form') }}">Cek Garansi</a>
        </div>
    </main>
</body>
</html>
