@extends('layouts.app')
@section('title', 'Dashboard Utama')
@section('page-title')
<i class="fas fa-tachometer-alt me-2" style="color:#3b82f6;"></i> Dashboard Utama
@endsection


@push('styles')
<style>
.country-row { cursor:pointer; transition:background 0.2s; }
.country-row:hover td { background:rgba(59,130,246,0.07) !important; }
.sync-btn { background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.3); color:#3b82f6; padding:0.25rem 0.6rem; border-radius:6px; font-size:0.72rem; cursor:pointer; transition:all 0.2s; }
.sync-btn:hover { background:#3b82f6; color:#fff; }
.pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.5;} }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-end mb-3">
    <button id="btnSyncAll" class="btn btn-primary-glow">
        <i class="fas fa-sync-alt me-2"></i>
        Sinkronisasi Semua Data
    </button>
</div>

<!-- STATS ROW -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(59,130,246,0.15);">
                <i class="fas fa-globe" style="color:#3b82f6;"></i>
            </div>
            <div>
                <div class="stat-val" style="color:#3b82f6;">{{ $stats['total_negara'] }}</div>
                <div class="stat-label">Total Negara</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(239,68,68,0.15);">
                <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
            </div>
            <div>
                <div class="stat-val" style="color:#ef4444;">{{ $stats['risiko_tinggi'] }}</div>
                <div class="stat-label">Risiko Tinggi</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,0.15);">
                <i class="fas fa-exclamation-circle" style="color:#f59e0b;"></i>
            </div>
            <div>
                <div class="stat-val" style="color:#f59e0b;">{{ $stats['risiko_sedang'] }}</div>
                <div class="stat-label">Risiko Sedang</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.15);">
                <i class="fas fa-check-circle" style="color:#10b981;"></i>
            </div>
            <div>
                <div class="stat-val" style="color:#10b981;">{{ $stats['risiko_rendah'] }}</div>
                <div class="stat-label">Risiko Rendah</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(6,182,212,0.15);">
                <i class="fas fa-newspaper" style="color:#06b6d4;"></i>
            </div>
            <div>
                <div class="stat-val" style="color:#06b6d4;">{{ $stats['total_berita'] }}</div>
                <div class="stat-label">Arsip Berita</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(139,92,246,0.15);">
                <i class="fas fa-anchor" style="color:#8b5cf6;"></i>
            </div>
            <div>
                <div class="stat-val" style="color:#8b5cf6;">{{ $stats['total_pelabuhan'] }}</div>
                <div class="stat-label">Pelabuhan</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- TABEL NEGARA + SKOR RISIKO -->
    <div class="col-12 col-xl-8">
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid rgba(59,130,246,0.1);">
                <h6 class="mb-0 fw-700"><i class="fas fa-globe me-2" style="color:#3b82f6;"></i>Monitoring Negara & Skor Risiko</h6>
                <span class="pulse" style="font-size:0.72rem;color:#10b981;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Live</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="table table-glass mb-0">
                    <thead>
                        <tr>
                            <th>Negara</th>
                            <th>Wilayah</th>
                            <th>PDB (Triliun USD)</th>
                            <th>Inflasi (%)</th>
                            <th>Total Risiko</th>
                            <th>Tingkat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-negara">
                    @foreach($negaraList as $negara)
                    @php
                        $skor = $negara->currentRiskScore;
                        $total = $skor ? $skor->total_risiko : 0;
                        $tingkat = $skor ? $skor->tingkat_risiko : 'N/A';
                        $badgeClass = $tingkat === 'Rendah' ? 'badge-rendah' : ($tingkat === 'Sedang' ? 'badge-sedang' : 'badge-tinggi');
                        $fillClass  = $tingkat === 'Rendah' ? 'fill-rendah' : ($tingkat === 'Sedang' ? 'fill-sedang' : 'fill-tinggi');
                    @endphp
                    <tr class="country-row" id="row-{{ $negara->kode_iso2 }}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:30px;height:22px;background:rgba(59,130,246,0.15);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700;color:#3b82f6;">
                                    {{ $negara->kode_iso2 }}
                                </div>
                                <div>
                                   <a href="{{ route('dashboard.country.detail',$negara->id) }}"
                                        style="font-weight:600;font-size:0.85rem;color:#60A5FA;;text-decoration:none;">
                                        {{ $negara->nama }}
                                    </a>
                                    <div style="font-size:0.68rem;color:#64748b;">{{ $negara->ibu_kota }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color:#94a3b8;font-size:0.82rem;">{{ $negara->wilayah }}</td>
                        <td style="font-size:0.85rem;color:#10b981;font-weight:600;">
                            ${{ number_format($negara->pdb / 1e12, 2) }}T
                        </td>
                        <td>
                            <span style="color:{{ $negara->inflasi > 6 ? '#ef4444' : ($negara->inflasi > 3 ? '#f59e0b' : '#10b981') }};font-weight:600;font-size:0.85rem;">
                                {{ number_format($negara->inflasi, 1) }}%
                            </span>
                        </td>
                        <td style="min-width:100px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="risk-meter flex-grow-1">
                                    <div class="risk-meter-fill {{ $fillClass }}" style="width:{{ $total }}%"></div>
                                </div>
                                <span style="font-size:0.78rem;font-weight:700;min-width:28px;">{{ $total }}</span>
                            </div>
                        </td>
                        <td><span class="badge {{ $badgeClass }} px-2 py-1" style="font-size:0.7rem;border-radius:6px;">{{ $tingkat }}</span></td>
                        <td>
                            <button class="sync-btn" onclick="sinkronisasiNegara('{{ $negara->kode_iso2 }}', this)" title="Sinkronisasi Data">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <a href="{{ route('dashboard.watchlist') }}" class="ms-1 sync-btn" style="color:#f59e0b;border-color:rgba(245,158,11,0.3);">
                                <i class="fas fa-star"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- WATCHLIST + QUICK STATS SIDEBAR -->
    <div class="col-12 col-xl-4 d-flex flex-column gap-3">
        <!-- WATCHLIST -->
        <div class="glass-card">
            <h6 class="fw-700 mb-3"><i class="fas fa-star me-2" style="color:#f59e0b;"></i>Daftar Pantau Saya</h6>
            @if($watchlist->isEmpty())
                <div class="text-center py-3" style="color:#475569;font-size:0.85rem;">
                    <i class="fas fa-star fa-2x mb-2" style="color:#1e293b;"></i><br>
                    Belum ada negara yang dipantau.<br>
                    <a href="{{ route('dashboard.watchlist') }}" style="color:#3b82f6;font-size:0.8rem;">Tambah sekarang →</a>
                </div>
            @else
                <div class="d-flex flex-column gap-2">
                @foreach($watchlist as $item)
                @php
                    $s = $item->currentRiskScore;
                    $t = $s ? $s->total_risiko : 0;
                    $tk = $s ? $s->tingkat_risiko : '-';
                    $bc = $tk === 'Rendah' ? 'badge-rendah' : ($tk === 'Sedang' ? 'badge-sedang' : 'badge-tinggi');
                    $fc = $tk === 'Rendah' ? 'fill-rendah' : ($tk === 'Sedang' ? 'fill-sedang' : 'fill-tinggi');
                @endphp
                <div style="background:rgba(59,130,246,0.05);border:1px solid rgba(59,130,246,0.1);border-radius:10px;padding:0.75rem;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">{{ $item->nama }}</div>
                            <div style="font-size:0.7rem;color:#64748b;">{{ $item->kode_iso2 }} · {{ $item->wilayah }}</div>
                        </div>
                        <span class="badge {{ $bc }} px-2 py-1" style="font-size:0.68rem;border-radius:6px;">{{ $tk }}</span>
                    </div>
                    <div class="risk-meter">
                        <div class="risk-meter-fill {{ $fc }}" style="width:{{ $t }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1" style="font-size:0.7rem;color:#94a3b8;">
                        <span>Risiko: {{ $t }}/100</span>
                        <span>Inflasi: {{ number_format($item->inflasi,1) }}%</span>
                    </div>
                </div>
                @endforeach
                </div>
            @endif
        </div>

        <!-- QUICK LINKS -->
        <div class="glass-card">
            <h6 class="fw-700 mb-3"><i class="fas fa-bolt me-2" style="color:#f59e0b;"></i>Akses Cepat</h6>
            <div class="d-grid gap-2">
                <a href="{{ route('dashboard.weather') }}" class="btn-primary-glow btn text-start py-2 px-3" style="background:linear-gradient(135deg,rgba(59,130,246,.2),rgba(6,182,212,.2));border:1px solid rgba(59,130,246,.3);color:#0F172A;font-weight:600;border-radius:10px;font-size:.83rem;">
                    <i class="fas fa-cloud-sun-rain me-2" style="color:#06b6d4;"></i>Peta Cuaca Global
                </a>
                <a href="{{ route('dashboard.ports') }}" class="btn text-start py-2 px-3" style="background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.3);color:#fffff;border-radius:10px;font-size:0.83rem;">
                    <i class="fas fa-anchor me-2" style="color:#8b5cf6;"></i>Dashboard Pelabuhan
                </a>
                <a href="{{ route('dashboard.compare') }}" class="btn text-start py-2 px-3" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);color:#fffff;border-radius:10px;font-size:0.83rem;">
                    <i class="fas fa-balance-scale me-2" style="color:#f59e0b;"></i>Bandingkan Negara
                </a>
                <a href="{{ route('dashboard.news') }}" class="btn text-start py-2 px-3" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#fffff;border-radius:10px;font-size:0.83rem;">
                    <i class="fas fa-newspaper me-2" style="color:#10b981;"></i>Berita & Sentimen
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal loading sinkronisasi -->
<div id="sync-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:rgba(16,22,40,0.95);border:1px solid rgba(59,130,246,0.3);border-radius:16px;padding:2rem;text-align:center;min-width:280px;">
        <div class="mb-3"><i class="fas fa-sync-alt fa-spin fa-2x" style="color:#3b82f6;"></i></div>
        <div style="font-weight:600;margin-bottom:0.5rem;">Menyinkronisasi Data…</div>
        <div id="sync-msg" style="font-size:0.8rem;color:#64748b;">Menghubungi API eksternal…</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function sinkronisasiNegara(kode, btn) {
    const overlay = document.getElementById('sync-overlay');
    const msg = document.getElementById('sync-msg');
    overlay.style.display = 'flex';
    msg.textContent = 'Mengambil data cuaca, ekonomi, dan berita untuk ' + kode + '…';

    try {
        const res = await axios.post('/api/v1/countries/sync', { kode_iso2: kode });
        if (res.data.status === 'sukses') {
            msg.textContent = '✓ Sinkronisasi selesai! Memperbarui tampilan…';
            setTimeout(() => { overlay.style.display='none'; location.reload(); }, 1000);
        }
    } catch(e) {
        overlay.style.display = 'none';
        alert('Sinkronisasi gagal: ' + (e.response?.data?.pesan || e.message));
    }
}

document.getElementById('btnSyncAll').addEventListener('click', async function () {

    if (!confirm('Sinkronisasi seluruh data negara? Proses ini bisa memakan beberapa menit.')) {
        return;
    }

    const btn = this;

    btn.disabled = true;

    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Sedang Sinkronisasi...
    `;

    try {

        const res = await axios.post('/api/v1/countries/sync-all');

        alert(res.data.message ?? 'Sinkronisasi berhasil');

        location.reload();

    } catch (e) {

        alert('Sinkronisasi gagal');

        console.error(e);

    } finally {

        btn.disabled = false;

        btn.innerHTML = `
            <i class="fas fa-sync-alt me-2"></i>
            Sinkronisasi Semua Data
        `;

    }

});
</script>
@endpush
