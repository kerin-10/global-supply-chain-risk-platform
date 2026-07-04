@extends('layouts.app')
@section('title', 'Peta Cuaca Global')
@section('page-title', '<i class="fas fa-cloud-sun-rain me-2" style="color:#06b6d4;"></i>Peta Cuaca Global')

@push('styles')
<style>
#peta-cuaca { height: 500px; border-radius: 12px; border: 1px solid rgba(59,130,246,0.2); }
.leaflet-container { background: #0f172a !important; }
.cuaca-popup { font-family:'Inter',sans-serif; font-size:0.82rem; min-width:180px; }
.cuaca-popup h6 { font-weight:700; margin-bottom:6px; color:#1e293b; }
.cuaca-item { display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid #f0f4f8; }
</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="glass-card" style="padding:1rem 1.5rem;">
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <h6 class="mb-0 fw-600"><i class="fas fa-info-circle me-2" style="color:#06b6d4;"></i>Peta interaktif menampilkan kondisi cuaca real-time di ibu kota setiap negara yang dipantau.</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#10b981;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.73rem;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Cerah / Risiko Rendah</span>
                    <span style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#f59e0b;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.73rem;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Hujan / Risiko Sedang</span>
                    <span style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.73rem;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Badai / Risiko Tinggi</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <div class="glass-card" style="padding:1rem;">
            <div id="peta-cuaca"></div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="glass-card" style="padding:0;overflow:hidden;max-height:540px;overflow-y:auto;">
            <div class="px-4 py-3" style="border-bottom:1px solid rgba(59,130,246,0.1);position:sticky;top:0;background:rgba(16,22,40,0.95);backdrop-filter:blur(10px);">
                <h6 class="mb-0 fw-700"><i class="fas fa-list me-2" style="color:#06b6d4;"></i>Kondisi Cuaca Negara</h6>
            </div>
            @foreach($negaraList as $negara)
            @php
                $suhu = $negara->cuaca_suhu;
                $angin = $negara->cuaca_kecepatan_angin;
                $hujan = $negara->cuaca_curah_hujan;
                $badai = $negara->cuaca_risiko_badai;
                $skor  = $negara->currentRiskScore;
                $tk    = $skor ? $skor->tingkat_risiko : 'N/A';
                $color = $tk === 'Rendah' ? '#10b981' : ($tk === 'Sedang' ? '#f59e0b' : '#ef4444');
                $icon  = $suhu === null ? 'fa-question-circle' : (($hujan > 5 || $badai > 60) ? 'fa-bolt-lightning' : ($hujan > 1 ? 'fa-cloud-rain' : 'fa-sun'));
            @endphp
            <div class="px-4 py-3" style="border-bottom:1px solid rgba(59,130,246,0.06);">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas {{ $icon }}" style="color:{{ $color }};width:18px;text-align:center;"></i>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem;">{{ $negara->nama }}</div>
                            <div style="font-size:0.7rem;color:#64748b;">{{ $negara->ibu_kota }}</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:1.1rem;font-weight:800;color:{{ $color }};">
                            {{ $suhu !== null ? number_format($suhu,1).'°C' : '--' }}
                        </div>
                        <div style="font-size:0.68rem;color:#475569;">{{ $angin !== null ? number_format($angin,0).' km/h' : '--' }}</div>
                    </div>
                </div>
                @if($hujan !== null && $hujan > 0)
                <div class="mt-1" style="font-size:0.7rem;color:#94a3b8;"><i class="fas fa-tint me-1" style="color:#3b82f6;"></i>Hujan: {{ number_format($hujan,1) }} mm</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Data negara dari Laravel
const negaraData = @json($negaraList);

// Inisialisasi Leaflet Map
const map = L.map('peta-cuaca', {
    center: [20, 10],
    zoom: 2,
    zoomControl: true,
    attributionControl: false
});

// Layer OpenStreetMap Dark
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 18
}).addTo(map);

// Fungsi warna marker berdasarkan tingkat risiko cuaca
function getMarkerColor(tingkat) {
    if (tingkat === 'Rendah') return '#10b981';
    if (tingkat === 'Sedang') return '#f59e0b';
    return '#ef4444';
}

// Buat marker untuk tiap negara
negaraData.forEach(function(negara) {
    if (!negara.lintang || !negara.bujur) return;

    const skor  = negara.current_risk_score;
    const tk    = skor ? skor.tingkat_risiko : 'N/A';
    const color = getMarkerColor(tk);

    const icon = L.divIcon({
        html: `<div style="width:14px;height:14px;border-radius:50%;background:${color};border:2px solid rgba(255,255,255,0.5);box-shadow:0 0 10px ${color}88;"></div>`,
        className: '',
        iconSize: [14, 14]
    });

    const suhu  = negara.cuaca_suhu   !== null ? negara.cuaca_suhu.toFixed(1)+'°C'  : 'N/A';
    const angin = negara.cuaca_kecepatan_angin !== null ? negara.cuaca_kecepatan_angin.toFixed(0)+' km/h' : 'N/A';
    const hujan = negara.cuaca_curah_hujan !== null ? negara.cuaca_curah_hujan.toFixed(1)+' mm' : 'N/A';
    const total = skor ? skor.total_risiko : '-';

    const popup = `
        <div class="cuaca-popup">
            <h6>${negara.nama} (${negara.kode_iso2})</h6>
            <div class="cuaca-item"><span>Suhu</span><strong>${suhu}</strong></div>
            <div class="cuaca-item"><span>Angin</span><strong>${angin}</strong></div>
            <div class="cuaca-item"><span>Curah Hujan</span><strong>${hujan}</strong></div>
            <div class="cuaca-item"><span>Skor Risiko</span><strong style="color:${color};">${total}/100</strong></div>
            <div class="cuaca-item"><span>Tingkat</span><strong style="color:${color};">${tk}</strong></div>
        </div>
    `;

    L.marker([negara.lintang, negara.bujur], { icon }).addTo(map).bindPopup(popup);
});
</script>
@endpush
