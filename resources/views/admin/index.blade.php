@extends('layouts.app')
@section('title', 'Panel Admin')
@section('page-title', '<i class="fas fa-shield-alt me-2" style="color:var(--accent-purple);"></i>Panel Administrasi')

@section('content')
<div class="row g-3 mb-4">
    <!-- STATS -->
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(59,130,246,0.1); color:var(--accent-blue);">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['total_pengguna'] }}</div>
                <div class="stat-label">Total Pengguna</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.1); color:var(--accent-green);">
                <i class="fas fa-anchor"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['total_pelabuhan'] }}</div>
                <div class="stat-label">Total Pelabuhan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(139,92,246,0.1); color:var(--accent-purple);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['total_artikel'] }}</div>
                <div class="stat-label">Total Artikel</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,0.1); color:var(--accent-yellow);">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <div class="stat-val">{{ $stats['log_api'] }}</div>
                <div class="stat-label">Log Panggilan API</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- KONEKSI & PENGATURAN -->
    <div class="col-12 col-xl-7">
        <!-- VALIDASI KONEKSI API -->
        <div class="glass-card mb-3">
            <h6 class="fw-700 mb-3"><i class="fas fa-network-wired me-2" style="color:var(--accent-blue);"></i>Validasi Koneksi API Eksternal</h6>
            <div class="list-group list-group-flush">
                @foreach($statusKoneksi as $apiName => $status)
                @php
                    $isOk = $status === 'Connected';
                    $badge = $isOk ? 'bg-success' : 'bg-danger';
                    $icon = $isOk ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                @endphp
                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2" style="background:transparent; border-color:var(--border-glass);">
                    <span style="font-size:0.85rem; font-weight:600; color:var(--text-primary);"><i class="fas {{ $icon }} me-2"></i>{{ $apiName }}</span>
                    <span class="badge {{ $badge }} rounded-pill" style="font-size:0.7rem; padding:0.3rem 0.60rem;">{{ $status }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- SETTINGS FORM -->
        <div class="glass-card">
            <h6 class="fw-700 mb-3"><i class="fas fa-cog me-2" style="color:var(--accent-purple);"></i>Bobot Risiko Sistem & Kunci API</h6>
            <form action="{{ route('admin.pengaturan.simpan') }}" method="POST">
                @csrf
                <div class="row g-3">
                    @foreach($pengaturan as $setting)
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.8rem; font-weight:600; color:var(--text-primary);">
                            {{ ucwords(str_replace('_', ' ', $setting->kunci)) }}
                        </label>
                        <input type="text" name="{{ $setting->kunci }}" value="{{ $setting->nilai }}" class="form-control form-control-sm" style="border-radius:8px; background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass);">
                        <div class="form-text" style="font-size:0.68rem; color:var(--text-muted);">{{ $setting->deskripsi }}</div>
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary-glow btn-sm mt-3 w-100">
                    <i class="fas fa-save me-1"></i>Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>

    <!-- LOG TERBARU & AKSES CEPAT -->
    <div class="col-12 col-xl-5 d-flex flex-column gap-3">
        <!-- LOG API -->
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-glass);">
                <h6 class="mb-0 fw-700"><i class="fas fa-file-invoice me-2" style="color:var(--accent-yellow);"></i>Log Permintaan API Terbaru</h6>
            </div>
            <div style="max-height:260px; overflow-y:auto;">
                <table class="table table-glass mb-0">
                    <thead>
                        <tr>
                            <th>API</th>
                            <th>Status</th>
                            <th>Waktu (ms)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logTerbaru as $log)
                        @php
                            $statusOk = $log->status_respons >= 200 && $log->status_respons < 300;
                            $color = $statusOk ? 'var(--accent-green)' : 'var(--accent-red)';
                        @endphp
                        <tr>
                            <td style="font-size:0.78rem; font-weight:600; color:var(--text-primary);">{{ $log->nama_api }}</td>
                            <td>
                                <span style="color:{{ $color }}; font-weight:700;">{{ $log->status_respons }}</span>
                            </td>
                            <td style="font-size:0.78rem; color:var(--text-muted);">{{ $log->waktu_respons_ms }}ms</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted" style="color:var(--text-muted);">Belum ada log logistik.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MENU MANAJEMEN -->
        <div class="glass-card">
            <h6 class="fw-700 mb-3"><i class="fas fa-folder-open me-2" style="color:var(--accent-blue);"></i>Menu Administrasi</h6>
            <div class="d-grid gap-2">
                <a href="{{ route('admin.pengguna.daftar') }}" class="btn text-start py-2 px-3" style="background:rgba(59,130,246,0.06); border:1px solid var(--border-glass); color:var(--text-primary); border-radius:10px; font-size:0.83rem; text-decoration:none;">
                    <i class="fas fa-users-cog me-2" style="color:var(--accent-blue);"></i>Manajemen Pengguna
                </a>
                <a href="{{ route('admin.pelabuhan.daftar') }}" class="btn text-start py-2 px-3" style="background:rgba(16,185,129,0.06); border:1px solid var(--border-glass); color:var(--text-primary); border-radius:10px; font-size:0.83rem; text-decoration:none;">
                    <i class="fas fa-anchor me-2" style="color:var(--accent-green);"></i>Manajemen Pelabuhan
                </a>
                <a href="{{ route('admin.artikel.daftar') }}" class="btn text-start py-2 px-3" style="background:rgba(139,92,246,0.06); border:1px solid var(--border-glass); color:var(--text-primary); border-radius:10px; font-size:0.83rem; text-decoration:none;">
                    <i class="fas fa-edit me-2" style="color:var(--accent-purple);"></i>Manajemen Artikel Analisis
                </a>
                <a href="{{ route('admin.leksikon.daftar') }}" class="btn text-start py-2 px-3" style="background:rgba(245,158,11,0.06); border:1px solid var(--border-glass); color:var(--text-primary); border-radius:10px; font-size:0.83rem; text-decoration:none;">
                    <i class="fas fa-spell-check me-2" style="color:var(--accent-yellow);"></i>Leksikon Sentimen (Kamus)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
