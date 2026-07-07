<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Platform Monitoring Risiko Rantai Pasok Global – pantau cuaca, ekonomi, dan berita geopolitik dalam satu dashboard terintegrasi.">
    <title>@yield('title', 'Dashboard') – SupplyRisk Intelligence</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Leaflet.js CSS -->
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">

    <style>
        :root {
            --bg-dark:        #0a0e1a;
            --bg-card:        rgba(16, 22, 40, 0.85);
            --bg-sidebar:     rgba(10, 14, 30, 0.97);
            --accent-blue:    #3b82f6;
            --accent-cyan:    #06b6d4;
            --accent-green:   #10b981;
            --accent-yellow:  #f59e0b;
            --accent-red:     #ef4444;
            --accent-purple:  #8b5cf6;
            --border-glass:   rgba(59, 130, 246, 0.15);
            --text-primary:   #f1f5f9;
            --text-muted:     #94a3b8;
            --glow-blue:      0 0 20px rgba(59, 130, 246, 0.3);
            --sidebar-width:  260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed; inset: 0; z-index: -1;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(59,130,246,0.08) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(139,92,246,0.07) 0%, transparent 55%),
                radial-gradient(ellipse at 50% 50%, rgba(6,182,212,0.04) 0%, transparent 70%);
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-glass);
            display: flex; flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid var(--border-glass);
        }
        .sidebar-brand .brand-logo {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: white;
            box-shadow: var(--glow-blue);
        }
        .brand-text { line-height: 1.2; }
        .brand-name { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); }
        .brand-sub  { font-size: 0.65rem; color: var(--text-muted); letter-spacing: 0.5px; text-transform: uppercase; }

        .nav-section-label {
            font-size: 0.6rem; font-weight: 600; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--text-muted);
            padding: 1rem 1.25rem 0.4rem;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 0.65rem 1.25rem;
            color: var(--text-muted);
            text-decoration: none; font-size: 0.85rem; font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            color: var(--text-primary);
            background: rgba(59,130,246,0.08);
            border-left-color: var(--accent-blue);
        }
        .sidebar-nav a .nav-icon { width: 18px; text-align: center; font-size: 0.85rem; }
        .sidebar-nav a .badge { margin-left: auto; font-size: 0.6rem; }

        /* USER INFO */
        .sidebar-user {
            margin-top: auto;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-glass);
        }
        .user-card {
            display: flex; align-items: center; gap: 10px;
        }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-purple), var(--accent-blue));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: white; flex-shrink: 0;
        }
        .user-name  { font-size: 0.82rem; font-weight: 600; color: var(--text-primary); }
        .user-role  { font-size: 0.68rem; color: var(--text-muted); }

        /* MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        /* TOP BAR */
        .topbar {
            padding: 1rem 1.75rem;
            background: rgba(10,14,26,0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-glass);
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 900;
        }
        .topbar-title { font-size: 1.1rem; font-weight: 700; }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .topbar-btn {
            background: rgba(59,130,246,0.1);
            border: 1px solid var(--border-glass);
            color: var(--text-muted);
            padding: 0.45rem 0.85rem;
            border-radius: 8px; font-size: 0.8rem;
            cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .topbar-btn:hover { color: var(--text-primary); background: rgba(59,130,246,0.2); }

        /* CONTENT AREA */
        .content-area { padding: 1.75rem; flex: 1; }

        /* GLASS CARDS */
        .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            padding: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .glass-card:hover { transform: translateY(-2px); box-shadow: var(--glow-blue); }

        /* STAT CARDS */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 14px; padding: 1.25rem;
            display: flex; align-items: center; gap: 1rem;
            transition: all 0.25s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; flex-shrink: 0;
        }
        .stat-val   { font-size: 1.6rem; font-weight: 800; line-height: 1; }
        .stat-label { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }

        /* RISK BADGES */
        .badge-rendah  { background: rgba(16,185,129,0.15); color: var(--accent-green); border: 1px solid rgba(16,185,129,0.3); }
        .badge-sedang  { background: rgba(245,158,11,0.15); color: var(--accent-yellow); border: 1px solid rgba(245,158,11,0.3); }
        .badge-tinggi  { background: rgba(239,68,68,0.15);  color: var(--accent-red);    border: 1px solid rgba(239,68,68,0.3); }

        /* ALERTS */
        .alert-glass {
            background: rgba(16,22,40,0.7);
            border: 1px solid var(--border-glass);
            border-radius: 10px; color: var(--text-primary);
            backdrop-filter: blur(8px);
        }
        .alert-glass.alert-success { border-color: rgba(16,185,129,0.4); }
        .alert-glass.alert-danger  { border-color: rgba(239,68,68,0.4); }

        /* TABLE */
        .table-glass {
            color: var(--text-primary);
            border-collapse: separate; border-spacing: 0;
        }
        .table-glass thead th {
            background: rgba(59,130,246,0.08);
            border-bottom: 1px solid var(--border-glass);
            color: var(--text-muted); font-size: 0.73rem;
            text-transform: uppercase; letter-spacing: 0.8px;
            padding: 0.75rem 1rem;
        }
        .table-glass tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(59,130,246,0.06);
            vertical-align: middle; font-size: 0.85rem;
        }
        .table-glass tbody tr:hover td { background: rgba(59,130,246,0.04); }

        /* BUTTONS */
        .btn-primary-glow {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan));
            border: none; color: white; font-weight: 600;
            border-radius: 10px; padding: 0.55rem 1.2rem;
            transition: all 0.25s; box-shadow: 0 4px 15px rgba(59,130,246,0.3);
        }
        .btn-primary-glow:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59,130,246,0.45); color: white; }

        /* RISK METER */
        .risk-meter { height: 8px; border-radius: 4px; background: rgba(255,255,255,0.08); overflow: hidden; }
        .risk-meter-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }
        .fill-rendah { background: linear-gradient(90deg, #10b981, #34d399); }
        .fill-sedang { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .fill-tinggi { background: linear-gradient(90deg, #ef4444, #f87171); }

        /* MOBILE */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(59,130,246,0.3); border-radius: 3px; }
    </style>
    @stack('styles')
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard.index') }}" class="brand-logo">
            <div class="brand-icon"><i class="fas fa-globe-asia"></i></div>
            <div class="brand-text">
                <div class="brand-name">SupplyRisk</div>
                <div class="brand-sub">Intelligence Platform</div>
            </div>
        </a>
    </div>

    <div class="sidebar-nav mt-1">
        <div class="nav-section-label">Monitoring Utama</div>
        <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span> Dashboard Utama
        </a>
        <a href="{{ route('dashboard.weather') }}" class="{{ request()->routeIs('dashboard.weather') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-cloud-sun-rain"></i></span> Peta Cuaca Global
        </a>
        <a href="{{ route('dashboard.ports') }}" class="{{ request()->routeIs('dashboard.ports') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-anchor"></i></span> Pelabuhan Global
        </a>

        <div class="nav-section-label">Analitik Ekonomi</div>
        <a href="{{ route('dashboard.currency') }}" class="{{ request()->routeIs('dashboard.currency') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-exchange-alt"></i></span> Nilai Tukar Mata Uang
        </a>
        <a href="{{ route('dashboard.visualization') }}" class="{{ request()->routeIs('dashboard.visualization') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-chart-area"></i></span> Visualisasi Data
        </a>
        <a href="{{ route('dashboard.compare') }}" class="{{ request()->routeIs('dashboard.compare') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-balance-scale"></i></span> Perbandingan Negara
        </a>

        <div class="nav-section-label">Intelijen</div>
        <a href="{{ route('dashboard.news') }}" class="{{ request()->routeIs('dashboard.news') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-newspaper"></i></span> Berita & Sentimen
        </a>
        <a href="{{ route('articles.index') }}" class="{{ request()->routeIs('articles.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-file-alt"></i></span> Artikel Analisis
        </a>

        <div class="nav-section-label">Profil</div>
        <a href="{{ route('dashboard.watchlist') }}" class="{{ request()->routeIs('dashboard.watchlist') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-star"></i></span> Daftar Pantau
        </a>

        @if(auth()->user()->isAdmin())
        <div class="nav-section-label">Administrasi</div>
        <a href="{{ route('admin.index') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fas fa-shield-alt"></i></span> Panel Admin
            <span class="badge bg-danger rounded-pill">Admin</span>
        </a>
        @endif
    </div>

    <div class="sidebar-user">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->nama, 0, 2)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->nama }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->peran) }}</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm w-100"
                    style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;border-radius:8px;font-size:0.75rem;">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </button>
        </form>
    </div>
</aside>


<main class="main-content">
    
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="topbar-btn d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
        <div class="topbar-title">
        {!! trim($__env->yieldContent('page-title', 'Dashboard')) !!}
        </div>
        </div>
        <div class="topbar-actions">
            <span class="topbar-btn">
                <i class="fas fa-clock me-1"></i>
                <span id="live-clock"></span>
            </span>
            <span class="topbar-btn" title="Status Sistem">
                <i class="fas fa-circle text-success me-1" style="font-size:0.5rem;"></i> Online
            </span>
        </div>
    </div>

    <div class="px-4 pt-3">
        @if(session('sukses'))
            <div class="alert alert-glass alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2 text-success"></i>
                {{ session('sukses') }}
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error') || $errors->any())
            <div class="alert alert-glass alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                {{ session('error') ?? $errors->first() }}
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- PAGE CONTENT -->
    <div class="content-area">
        @yield('content')
    </div>

    <footer class="text-center py-3" style="color:var(--text-muted);font-size:0.72rem;border-top:1px solid var(--border-glass);">
        &copy; {{ date('Y') }} SupplyRisk Intelligence Platform &mdash; Data dari Open-Meteo, World Bank, REST Countries, ExchangeRate-API
    </footer>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- Leaflet.js -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    // Jam digital live
    function updateClock() {
        const now = new Date();
        document.getElementById('live-clock').textContent =
            now.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Setup Axios CSRF token
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
</script>
@stack('scripts')
</body>
</html>
