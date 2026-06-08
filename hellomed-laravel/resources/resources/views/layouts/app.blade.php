<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HelloMed') }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f5f7fb;
            --surface: #ffffff;
            --text: #112033;
            --muted: #607089;
            --primary: #0f766e;
            --primary-strong: #115e59;
            --border: #d7e0ea;
            --accent: #dff6f3;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(180deg, #f0f6fb 0%, var(--bg) 100%);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }
        .container { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
        .nav {
            position: sticky;
            top: 0;
            background: rgba(245, 247, 251, 0.9);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            z-index: 10;
        }
        .nav-inner, .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 0; }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 800; letter-spacing: 0.2px; }
        .brand-badge {
            width: 40px; height: 40px; border-radius: 14px; display: grid; place-items: center;
            background: linear-gradient(135deg, var(--primary), #22c55e); color: white; font-weight: 800;
        }
        .nav-links, .pill-row, .meta-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .nav-links a, .pill, .button, .ghost-button {
            padding: 10px 14px; border-radius: 999px; border: 1px solid transparent; font-size: 14px;
        }
        .nav-links a:hover, .ghost-button:hover { border-color: var(--border); background: var(--surface); }
        .button { background: var(--primary); color: white; font-weight: 700; }
        .button:hover { background: var(--primary-strong); }
        .ghost-button { background: rgba(255,255,255,0.65); }
        .hero {
            padding: 72px 0 36px;
            display: grid;
            grid-template-columns: 1.3fr 0.9fr;
            gap: 28px;
            align-items: center;
        }
        .card, .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.06);
        }
        .card { padding: 24px; }
        .panel { padding: 20px; }
        h1, h2, h3, h4 { margin: 0 0 12px; line-height: 1.1; }
        h1 { font-size: clamp(2.4rem, 4vw, 4.9rem); }
        h2 { font-size: clamp(1.6rem, 2.5vw, 2.4rem); }
        p { color: var(--muted); line-height: 1.7; margin: 0 0 16px; }
        .grid { display: grid; gap: 18px; }
        .grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid.cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid.cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .section { padding: 20px 0 36px; }
        .stat { padding: 18px; border-radius: 18px; background: linear-gradient(180deg, #fff, #f8fbfd); border: 1px solid var(--border); }
        .stat strong { display: block; font-size: 1.8rem; margin-bottom: 4px; }
        .tag {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 12px; border-radius: 999px; background: var(--accent); color: var(--primary-strong);
            font-size: 13px; font-weight: 700; margin-bottom: 14px;
        }
        .list { display: grid; gap: 12px; }
        .list-item { padding: 16px; border: 1px solid var(--border); border-radius: 18px; background: #fff; }
        .list-item h3 { margin-bottom: 6px; }
        .footer { margin-top: 20px; border-top: 1px solid var(--border); }
        .muted { color: var(--muted); }
        input, select, textarea {
            width: 100%; margin-top: 6px; padding: 12px 14px; border: 1px solid var(--border); border-radius: 14px;
            font: inherit; background: white;
        }
        label { display: block; font-weight: 600; margin-bottom: 14px; }
        textarea { min-height: 140px; resize: vertical; }
        .notice { padding: 14px 16px; border-radius: 14px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; margin-bottom: 16px; }
        .table { width: 100%; border-collapse: collapse; overflow: hidden; border-radius: 16px; }
        .table th, .table td { text-align: left; padding: 14px; border-bottom: 1px solid var(--border); }
        .table th { font-size: 13px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted); }
        @media (max-width: 960px) {
            .hero, .grid.cols-2, .grid.cols-3, .grid.cols-4 { grid-template-columns: 1fr; }
            .nav-inner, .footer-inner { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="nav">
        <div class="container nav-inner">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-badge">HM</span>
                <span>
                    <div>HelloMed</div>
                    <small class="muted">Hospital booking & article platform</small>
                </span>
            </a>
            <div class="nav-links">
                <a href="{{ route('departments.index') }}">Departments</a>
                <a href="{{ route('doctors.index') }}">Doctors</a>
                <a href="{{ route('articles.index') }}">Articles</a>
                <a href="{{ route('contact') }}">Contact</a>
                <a href="{{ route('admin.dashboard') }}" class="ghost-button">Admin</a>
            </div>
        </div>
    </div>

    <main class="container">
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <div>
                <strong>HelloMed</strong>
                <p class="muted">Online and offline hospital services with central appointment booking.</p>
            </div>
            <div class="muted">Built with Laravel</div>
        </div>
    </footer>
</body>
</html>
