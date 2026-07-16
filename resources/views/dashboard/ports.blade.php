@extends('layouts.app')
@section('title', 'Dashboard Pelabuhan Global')
@section('page-title')
    <i class="fas fa-anchor me-2" style="color:#8B5CF6;"></i>Dashboard Pelabuhan Global
@endsection

@push('styles')
<style>
#peta-pelabuhan { height: 480px; border-radius:12px; border:1px solid rgba(139,92,246,0.2); }
.badge-rendah-km { background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3); }
.badge-sedang-km { background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3); }
.badge-tinggi-km { background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3); }

/* ===== FIX Z-INDEX: Filter dropdown harus di atas peta Leaflet ===== */
.filter-bar-wrapper {
    position: relative;
    z-index: 1050;
}
.map-and-list-wrapper {
    position: relative;
    z-index: 1;
}

/* Statistik ringkasan */
.port-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-glass);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.25s;
}
.port-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}
.port-stat-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.port-stat-val { font-size: 1.35rem; font-weight: 800; line-height: 1; }
.port-stat-label { font-size: 0.7rem; color: #94a3b8; margin-top: 2px; }

/* Sync progress bar */
.sync-progress-bar {
    height: 4px;
    background: rgba(139,92,246,0.15);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 0.5rem;
}
.sync-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #8b5cf6, #3b82f6);
    border-radius: 2px;
    width: 0%;
    transition: width 0.5s ease;
    animation: pulseGlow 1.5s ease-in-out infinite;
}
@keyframes pulseGlow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

/* Port item hover effect */
.port-item {
    transition: background 0.2s ease;
    cursor: pointer;
}
.port-item:hover {
    background: rgba(139,92,246,0.06);
}
</style>
@endpush

@section('content')
<!-- Statistik Ringkasan -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="port-stat-card">
            <div class="port-stat-icon" style="background:rgba(139,92,246,0.15);color:#8b5cf6;">
                <i class="fas fa-anchor"></i>
            </div>
            <div>
                <div class="port-stat-val" id="stat-total">{{ $pelabuhanList->count() }}</div>
                <div class="port-stat-label">Total Pelabuhan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="port-stat-card">
            <div class="port-stat-icon" style="background:rgba(16,185,129,0.15);color:#10b981;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                @php
                    $rendah = $pelabuhanList->filter(fn($p) => $p->latestCongestion && $p->latestCongestion->tingkat_kemacetan === 'Rendah')->count();
                @endphp
                <div class="port-stat-val" id="stat-rendah">{{ $rendah }}</div>
                <div class="port-stat-label">Kemacetan Rendah</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="port-stat-card">
            <div class="port-stat-icon" style="background:rgba(245,158,11,0.15);color:#f59e0b;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                @php
                    $sedang = $pelabuhanList->filter(fn($p) => $p->latestCongestion && $p->latestCongestion->tingkat_kemacetan === 'Sedang')->count();
                @endphp
                <div class="port-stat-val" id="stat-sedang">{{ $sedang }}</div>
                <div class="port-stat-label">Kemacetan Sedang</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="port-stat-card">
            <div class="port-stat-icon" style="background:rgba(239,68,68,0.15);color:#ef4444;">
                <i class="fas fa-times-circle"></i>
            </div>
            <div>
                @php
                    $tinggi = $pelabuhanList->filter(fn($p) => $p->latestCongestion && $p->latestCongestion->tingkat_kemacetan === 'Tinggi')->count();
                @endphp
                <div class="port-stat-val" id="stat-tinggi">{{ $tinggi }}</div>
                <div class="port-stat-label">Kemacetan Tinggi</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar (z-index tinggi agar dropdown tidak tertutup peta) -->
<div class="filter-bar-wrapper mb-3">
    <div class="glass-card" style="padding:1rem 1.5rem;">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="mb-1" style="font-size:.78rem;color:#475569;">Cari Pelabuhan</label>

                <input type="text"
                    id="cariPelabuhan"
                    class="form-control form-control-sm"
                    placeholder="Nama pelabuhan, negara, wilayah..."
                    style="background:#FFFFFF;border:1px solid #CBD5E1;color:#0F172A;border-radius:8px;">
            </div>
            <div class="col-md-3">
                <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1">Filter Negara</label>
                <select id="filterNegara" class="form-select form-select-sm" style="background:rgba(15,23,42,0.8);border:1px solid rgba(139,92,246,0.2);color:#f1f5f9;border-radius:8px;">
                    <option value="">Semua Negara</option>
                    @foreach($negaraList as $n)
                <option value="{{ $n->kode_iso2 }}">
                    {{ $n->nama }}
                </option>
                @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button onclick="filterPelabuhan()" class="btn btn-primary-glow w-100" style="font-size:0.82rem;">
                    <i class="fas fa-search me-1"></i>Cari
                </button>
            </div>
            <div class="col-md-3">
                <button onclick="syncGlobalPorts()" id="btnSync" class="btn w-100" style="font-size:0.82rem;background:linear-gradient(135deg,#8b5cf6,#6d28d9);border:none;color:white;border-radius:10px;font-weight:600;box-shadow:0 4px 15px rgba(139,92,246,0.3);">
                    <i class="fas fa-sync-alt me-1" id="syncIcon"></i>Sync Pelabuhan Global
                </button>
            </div>
        </div>
        <!-- Sync Progress -->
        <div id="syncProgress" style="display:none;">
            <div class="sync-progress-bar">
                <div class="sync-progress-fill" id="syncProgressFill"></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span style="font-size:0.7rem;color:#94a3b8;" id="syncStatusText">Mengambil data dari World Port Index API...</span>
                <span style="font-size:0.7rem;color:#8b5cf6;" id="syncPercentText">0%</span>
            </div>
        </div>
    </div>
</div>

<!-- Peta & Daftar Pelabuhan (z-index rendah) -->
<div class="map-and-list-wrapper">
    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="glass-card" style="padding:1rem;">
                <div id="peta-pelabuhan"></div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="glass-card" style="padding:0;overflow:hidden;">
                <div class="px-4 py-3" style="border-bottom:1px solid rgba(139,92,246,0.15);">
                    <h6 class="mb-0 fw-700"><i class="fas fa-list me-2" style="color:#8b5cf6;"></i>Daftar Pelabuhan <span id="port-count" class="badge" style="background:rgba(139,92,246,0.2);color:#8b5cf6;font-size:0.7rem;">{{ $pelabuhanList->count() }}</span></h6>
                </div>
                <div style="max-height:420px;overflow-y:auto;" id="port-list">
                    @foreach($pelabuhanList as $pelabuhan)
                    @php
                        $kemacetan = $pelabuhan->latestCongestion;
                        $tk = $kemacetan ? $kemacetan->tingkat_kemacetan : 'N/A';
                        $badgeClass = $tk === 'Rendah' ? 'badge-rendah-km' : ($tk === 'Sedang' ? 'badge-sedang-km' : ($tk === 'Tinggi' ? 'badge-tinggi-km' : ''));
                        $delay = $kemacetan ? $kemacetan->waktu_tunda_jam : 0;
                    @endphp
                    <div class="port-item px-4 py-3" style="border-bottom:1px solid rgba(139,92,246,0.06);"
                         data-nama="{{ strtolower($pelabuhan->nama) }}"
                         data-negara="{{ $pelabuhan->kode_negara }}"
                         data-lat="{{ $pelabuhan->lintang }}"
                         data-lng="{{ $pelabuhan->bujur }}"
                         onclick="focusPort({{ $pelabuhan->lintang }}, {{ $pelabuhan->bujur }}, '{{ addslashes($pelabuhan->nama) }}')">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div style="font-weight:600;font-size:0.85rem;">{{ $pelabuhan->nama }}</div>
                                <div style="font-size:0.7rem;color:#64748b;">
                                    {{ $pelabuhan->kode_negara }} · {{ $pelabuhan->wilayah }} · WPI: {{ $pelabuhan->nomor_wpi ?? '-' }}
                                </div>
                            </div>
                            <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size:0.68rem;border-radius:6px;">{{ $tk }}</span>
                        </div>
                        @if($kemacetan)
                        <div class="mt-2 d-flex gap-3" style="font-size:0.72rem;color:#94a3b8;">
                            <span><i class="fas fa-clock me-1" style="color:#f59e0b;"></i>Tunda: <strong>{{ $delay }}j</strong></span>
                            <span><i class="fas fa-map-marker-alt me-1" style="color:#8b5cf6;"></i>{{ number_format($pelabuhan->lintang,4) }}, {{ number_format($pelabuhan->bujur,4) }}</span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const pelabuhanData = @json($pelabuhanList->load('latestCongestion'));

const map = L.map('peta-pelabuhan', { center: [20, 110], zoom: 2, attributionControl: false });
L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 19,
    attribution: '&copy; Esri'
}).addTo(map);

function getPortColor(tk) {
    if (tk === 'Rendah') return '#10b981';
    if (tk === 'Sedang') return '#f59e0b';
    if (tk === 'Tinggi') return '#ef4444';
    return '#64748b';
}

let markers = [];
function renderMarkers(data) {
    markers.forEach(m => m.remove());
    markers = [];
    data.forEach(p => {
        if (!p.lintang || !p.bujur) return;
        const kemacetan = p.latest_congestion;
        const tk = kemacetan ? kemacetan.tingkat_kemacetan : 'N/A';
        const color = getPortColor(tk);
        const delay = kemacetan ? kemacetan.waktu_tunda_jam : 0;

        const icon = L.divIcon({
            html: `<div style="width:10px;height:10px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.6);box-shadow:0 0 8px ${color}99;"></div>`,
            className: '', iconSize: [10,10]
        });

        const popup = `
            <div style="font-family:Inter,sans-serif;font-size:0.82rem;min-width:200px;">
                <h6 style="font-weight:700;margin-bottom:6px;color:#1e293b;">${p.nama}</h6>
                <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #f0f4f8;"><span>Negara</span><strong>${p.kode_negara}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #f0f4f8;"><span>Wilayah</span><strong>${p.wilayah || '-'}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #f0f4f8;"><span>WPI</span><strong>${p.nomor_wpi || '-'}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #f0f4f8;"><span>Kemacetan</span><strong style="color:${color};">${tk}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:3px 0;"><span>Waktu Tunda</span><strong>${delay} jam</strong></div>
            </div>
        `;

        const marker = L.marker([p.lintang, p.bujur], { icon }).addTo(map).bindPopup(popup);
        markers.push(marker);
    });
}

renderMarkers(pelabuhanData);

// Fokus ke pelabuhan tertentu saat item di-klik
function focusPort(lat, lng, nama) {
    map.setView([lat, lng], 10, { animate: true });
    // Cari dan buka popup marker terdekat
    markers.forEach(m => {
        const pos = m.getLatLng();
        if (Math.abs(pos.lat - lat) < 0.01 && Math.abs(pos.lng - lng) < 0.01) {
            m.openPopup();
        }
    });
}

async function filterPelabuhan() {
    const cari = document.getElementById('cariPelabuhan').value;
    const negara = document.getElementById('filterNegara').value;
    const portList = document.getElementById('port-list');

    portList.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;color:#8b5cf6 !important;"></div>
            <div style="font-size:0.8rem;color:#64748b;" class="mt-2">Memuat pelabuhan dari API…</div>
        </div>
    `;

    try {
        const res = await axios.get('/api/v1/ports', {
            params: { cari: cari, negara: negara }
        });
        const data = res.data.data || [];

        renderMarkers(data);
        updatePortList(data);

    } catch (e) {
        portList.innerHTML = `<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle me-1"></i> Gagal memuat data pelabuhan.</div>`;
        console.error(e);
    }
}

function updatePortList(data) {
    const portList = document.getElementById('port-list');
    let html = '';
    let countRendah = 0, countSedang = 0, countTinggi = 0;

    data.forEach(p => {
        const kemacetan = p.latest_congestion;
        const tk = kemacetan ? kemacetan.tingkat_kemacetan : 'N/A';
        const badgeClass = tk === 'Rendah' ? 'badge-rendah-km' : (tk === 'Sedang' ? 'badge-sedang-km' : (tk === 'Tinggi' ? 'badge-tinggi-km' : ''));
        const delay = kemacetan ? kemacetan.waktu_tunda_jam : 0;

        if (tk === 'Rendah') countRendah++;
        if (tk === 'Sedang') countSedang++;
        if (tk === 'Tinggi') countTinggi++;

        html += `
            <div class="port-item px-4 py-3" style="border-bottom:1px solid rgba(139,92,246,0.06);"
                 onclick="focusPort(${p.lintang}, ${p.bujur}, '${(p.nama||'').replace(/'/g, "\\'")}')">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="font-weight:600;font-size:0.85rem;">${p.nama}</div>
                        <div style="font-size:0.7rem;color:#64748b;">
                            ${p.kode_negara} · ${p.wilayah || '-'} · WPI: ${p.nomor_wpi || '-'}
                        </div>
                    </div>
                    <span class="badge ${badgeClass} px-2 py-1" style="font-size:0.68rem;border-radius:6px;">${tk}</span>
                </div>
                ${kemacetan ? `
                <div class="mt-2 d-flex gap-3" style="font-size:0.72rem;color:#94a3b8;">
                    <span><i class="fas fa-clock me-1" style="color:#f59e0b;"></i>Tunda: <strong>${delay}j</strong></span>
                    <span><i class="fas fa-map-marker-alt me-1" style="color:#8b5cf6;"></i>${Number(p.lintang).toFixed(4)}, ${Number(p.bujur).toFixed(4)}</span>
                </div>
                ` : ''}
            </div>
        `;
    });

    portList.innerHTML = html || '<div class="text-center py-5 text-muted">Tidak ada pelabuhan ditemukan.</div>';
    document.getElementById('port-count').textContent = data.length;

    // Update stat cards
    document.getElementById('stat-total').textContent = data.length;
    document.getElementById('stat-rendah').textContent = countRendah;
    document.getElementById('stat-sedang').textContent = countSedang;
    document.getElementById('stat-tinggi').textContent = countTinggi;
}

// ===== SINKRONISASI PELABUHAN GLOBAL =====
async function syncGlobalPorts() {
    const btn = document.getElementById('btnSync');
    const icon = document.getElementById('syncIcon');
    const progress = document.getElementById('syncProgress');
    const progressFill = document.getElementById('syncProgressFill');
    const statusText = document.getElementById('syncStatusText');
    const percentText = document.getElementById('syncPercentText');

    // Disable button & show spinning
    btn.disabled = true;
    icon.classList.add('fa-spin');
    progress.style.display = 'block';

    // Animate progress (simulated since we don't have real progress from backend)
    let pct = 0;
    const progressInterval = setInterval(() => {
        pct = Math.min(pct + Math.random() * 8, 90);
        progressFill.style.width = pct + '%';
        percentText.textContent = Math.round(pct) + '%';

        if (pct < 30) statusText.textContent = 'Mengambil data dari World Port Index API...';
        else if (pct < 60) statusText.textContent = 'Mencocokkan pelabuhan dengan negara di database...';
        else statusText.textContent = 'Menyimpan data pelabuhan ke database...';
    }, 500);

    try {
        const res = await axios.post('/api/v1/ports/sync-global');
        clearInterval(progressInterval);

        // Complete
        progressFill.style.width = '100%';
        percentText.textContent = '100%';

        const detail = res.data.detail || {};
        statusText.innerHTML = `<span style="color:#10b981;"><i class="fas fa-check-circle me-1"></i>${res.data.pesan}</span>`;

        // Refresh data pelabuhan
        setTimeout(async () => {
            await filterPelabuhan();
            progress.style.display = 'none';
            btn.disabled = false;
            icon.classList.remove('fa-spin');
        }, 2000);

    } catch (e) {
        clearInterval(progressInterval);
        progressFill.style.width = '100%';
        progressFill.style.background = 'linear-gradient(90deg, #ef4444, #f87171)';
        statusText.innerHTML = `<span style="color:#ef4444;"><i class="fas fa-times-circle me-1"></i>Gagal sinkronisasi: ${e.response?.data?.pesan || e.message}</span>`;
        percentText.textContent = 'Error';

        setTimeout(() => {
            progress.style.display = 'none';
            progressFill.style.background = 'linear-gradient(90deg, #8b5cf6, #3b82f6)';
            btn.disabled = false;
            icon.classList.remove('fa-spin');
        }, 4000);

        console.error(e);
    }
}

let tsFilter;
document.addEventListener("DOMContentLoaded", function() {
    tsFilter = new TomSelect('#filterNegara', {
    create:false,
    valueField:'value',
    labelField:'text',
    searchField:['text'],
    sortField:{
        field:'text',
        direction:'asc'
    },
    maxOptions:null,
    onChange:filterPelabuhan
});
});

document.getElementById('cariPelabuhan').addEventListener('input', filterPelabuhan);
</script>
@endpush
