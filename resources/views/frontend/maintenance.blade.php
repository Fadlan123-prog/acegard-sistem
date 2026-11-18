<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#0F2A5F">
    <title>Sedang Maintenance — Acegard</title>
    <style>
        :root {
            --navy: #0F2A5F; --blue: #2563EB; --blue-hover: #1D4ED8; --blue-soft: #EAF1FF;
            --text: #111827; --muted: #4B5563; --border: #DDE2F0; --surface: #FFFFFF;
        }
        * { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            margin: 0; min-height: 100vh; min-height: 100dvh; display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 24px 16px; color: var(--text); line-height: 1.6;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
            background:
                radial-gradient(900px 400px at 50% -10%, #C9DBFA 0%, transparent 70%),
                linear-gradient(180deg, #EAF1FF 0%, #DCE7FB 100%);
        }
        main {
            width: 100%; max-width: 520px; text-align: center; background: var(--surface);
            border: 1px solid var(--border); border-radius: 16px; padding: 40px 24px 32px;
            box-shadow: 0 1px 2px rgba(15, 42, 95, .06), 0 16px 40px rgba(15, 42, 95, .12);
        }
        .logo { display: block; height: 36px; width: auto; max-width: 100%; margin: 0 auto 28px; }
        .badge {
            display: flex; align-items: center; justify-content: center; width: 72px; height: 72px;
            border-radius: 50%; background: var(--blue-soft); color: var(--blue); margin: 0 auto 20px;
        }
        .badge svg { width: 36px; height: 36px; animation: spin 12s linear infinite; }
        h1 { font-size: clamp(1.375rem, 4.5vw, 1.75rem); line-height: 1.25; margin: 0 0 12px; color: var(--navy); }
        .lead { margin: 0 auto 24px; max-width: 40ch; color: var(--muted); font-size: 1rem; }
        .actions { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 20px;
            border-radius: 10px; font-weight: 600; font-size: 1rem; text-decoration: none; border: 1px solid var(--border);
            color: var(--navy); background: var(--surface); transition: background-color .15s ease, border-color .15s ease, transform .15s ease;
        }
        .btn:hover { background: var(--blue-soft); border-color: var(--blue); }
        .btn:active { transform: translateY(1px); }
        .btn.primary { background: var(--blue); border-color: var(--blue); color: #fff; }
        .btn.primary:hover { background: var(--blue-hover); border-color: var(--blue-hover); }
        .btn:focus-visible { outline: 3px solid var(--navy); outline-offset: 2px; }
        .btn svg { width: 20px; height: 20px; flex: none; }
        .note { margin: 0; padding-top: 20px; border-top: 1px solid var(--border); font-size: .875rem; color: var(--muted); }
        footer { margin-top: 20px; font-size: .8125rem; color: var(--muted); }
        @media (min-width: 480px) {
            main { padding: 48px 40px 36px; }
            .actions { flex-direction: row; justify-content: center; }
            .btn { flex: 1 1 0; }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            .badge svg { animation: none; }
            .btn { transition: none; }
        }
    </style>
</head>
<body>
    <main role="main">
        <img class="logo" src="{{ asset('assets/frontend/images/new-acegard-logo.webp') }}" alt="Acegard">

        <div class="badge" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h0a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h0a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
        </div>

        <h1>Website sedang dalam maintenance</h1>
        <p class="lead">Kami sedang melakukan pembaruan agar layanan Acegard lebih baik. Silakan kembali beberapa saat lagi.</p>

        <div class="actions">
            <a class="btn primary" href="{{ route('warranty.form') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                Cek Garansi
            </a>
            <a class="btn" href="tel:+6282121212093">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Hubungi Kami
            </a>
        </div>

        <p class="note">Butuh bantuan mendesak? Kirim email ke
            <a href="mailto:acegardindonesia@gmail.com" style="color:var(--blue)">acegardindonesia@gmail.com</a>.</p>
    </main>
    <footer>&copy; {{ date('Y') }} Acegard</footer>
</body>
</html>
