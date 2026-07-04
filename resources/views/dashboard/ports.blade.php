@extends('layouts.app')
@section('title', 'Dashboard Pelabuhan Global')
@section('page-title', '<i class="fas fa-anchor me-2" style="color:#8b5cf6;"></i>Dashboard Pelabuhan Global')

@push('styles')
<style>
#peta-pelabuhan { height: 480px; border-radius:12px; border:1px solid rgba(139,92,246,0.2); }
.badge-rendah-km { background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3); }
.badge-sedang-km { background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3); }
.badge-tinggi-km { background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3); }
</style>
@endpush

@section('content')
<!-- Filter -->
<div class="glass-card mb-3" style="padding:1rem 1.5rem;">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1">Cari Pelabuhan</label>
            <input type="text" id="cariPelabuhan" class="form-control form-control-sm" placeholder="Nama pelabuhan, negara, wilayah…"
                   style="background:rgba(15,23,42,0.8);border:1px solid rgba(139,92,246,0.2);color:#f1f5f9;border-radius:8px;">
        </div>
        <div class="col-md-4">
            <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1">Filter Negara</label>
            <select id="filterNegara" class="form-select form-select-sm" style="background:rgba(15,23,42,0.8);border:1px solid rgba(139,92,246,0.2);color:#f1f5f9;border-radius:8px;">
                <option value="">Semua Negara</option>
                @foreach($negaraList as $n)
                    <option value="{{ $n->kode_iso2 }}">{{ $n->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button onclick="filterPelabuhan()" class="btn btn-primary-glow w-100" style="font-size:0.82rem;">
                <i class="fas fa-search me-1"></i>Cari Pelabuhan
            </button>
        </div>
    </div>
</div>

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
                     data-negara="{{ $pelabuhan->kode_negara }}">
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
@endsection

@push('scripts')
<script>
const pelabuhanData = @json($pelabuhanList->load('latestCongestion'));

const map = L.map('peta-pelabuhan', { center: [20, 110], zoom: 2, attributionControl: false });
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 18 }).addTo(map);

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
            html: `<div style="width:12px;height:12px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.6);box-shadow:0 0 8px ${color}99;"></div>`,
            className: '', iconSize: [12,12]
        });

        const popup = `
            <div style="font-family:Inter,sans-serif;font-size:0.82rem;min-width:180px;">
                <h6 style="font-weight:700;margin-bottom:6px;color:#1e293b;">${p.nama}</h6>
                <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #f0f4f8;"><span>Negara</span><strong>${p.kode_negara}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #f0f4f8;"><span>Wilayah</span><strong>${p.wilayah || '-'}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid #f0f4f8;"><span>Kemacetan</span><strong style="color:${color};">${tk}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:3px 0;"><span>Waktu Tunda</span><strong>${delay} jam</strong></div>
            </div>
        `;

        const marker = L.marker([p.lintang, p.bujur], { icon }).addTo(map).bindPopup(popup);
        markers.push(marker);
    });
}

renderMarkers(pelabuhanData);

function filterPelabuhan() {
    const cari = document.getElementById('cariPelabuhan').value.toLowerCase();
    const negara = document.getElementById('filterNegara').value;

    let filtered = pelabuhanData;
    if (cari) filtered = filtered.filter(p => p.nama.toLowerCase().includes(cari) || (p.wilayah && p.wilayah.toLowerCase().includes(cari)));
    if (negara) filtered = filtered.filter(p => p.kode_negara === negara);

    renderMarkers(filtered);

    document.querySelectorAll('.port-item').forEach(el => {
        const nm = el.dataset.nama;
        const ng = el.dataset.negara;
        const vis = (!cari || nm.includes(cari)) && (!negara || ng === negara);
        el.style.display = vis ? '' : 'none';
    });

    document.getElementById('port-count').textContent = filtered.length;
}

document.getElementById('cariPelabuhan').addEventListener('input', filterPelabuhan);
document.getElementById('filterNegara').addEventListener('change', filterPelabuhan);
</script>
@endpush
