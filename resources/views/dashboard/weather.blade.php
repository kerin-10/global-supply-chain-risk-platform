@extends('layouts.app')
@section('title', 'Peta Cuaca Global')
@section('page-title')
    <i class="fas fa-cloud-sun me-2" style="color:#0EA5E9;"></i>Peta Cuaca Global
@endsection

@push('styles')
<style>
#peta-cuaca { height: 500px; border-radius: 12px; border: 1px solid rgba(59,130,246,0.2); }
.leaflet-container{background:#ffffff !important;}
.cuaca-popup { font-family:'Inter',sans-serif; font-size:0.82rem; min-width:180px; }
.cuaca-popup h6 { font-weight:700; margin-bottom:6px; color:#1e293b; }
.cuaca-item { display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid #f0f4f8; }

</style>
@endpush

@section('content')
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="glass-card" style="padding:1rem 1.5rem;">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-7">
                    <h6 class="mb-0 fw-600"><i class="fas fa-info-circle me-2" style="color:#06b6d4;"></i>Peta interaktif menampilkan kondisi cuaca real-time di ibu kota setiap negara yang dipantau.</h6>
                </div>
                <div class="col-12 col-md-5">
                    <div class="d-flex gap-2 align-items-center justify-content-md-end">
                        <label style="font-size:0.75rem;color:#94a3b8;white-space:nowrap;" class="mb-0"><i class="fas fa-search me-1"></i>Cari & Fokus Negara:</label>
                        <div id="wrapperNegaraCuaca" style="width:280px; position:relative; z-index:99999;">
                            <select id="cariNegaraCuaca" class="form-select form-select-sm">
                                <option value="">-- Pilih Negara --</option>
                                @foreach($negaraList as $n)
                                    @if($n->lintang && $n->bujur)
                                        <option value="{{ $n->id }}" data-lat="{{ $n->lintang }}" data-lng="{{ $n->bujur }}">{{ $n->nama }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap mt-3 pt-2" style="border-top:1px solid rgba(59,130,246,0.1);">
                <span style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#10b981;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.73rem;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Cerah / Risiko Rendah</span>
                <span style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#f59e0b;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.73rem;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Hujan / Risiko Sedang</span>
                <span style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.73rem;"><i class="fas fa-circle me-1" style="font-size:0.5rem;"></i>Badai / Risiko Tinggi</span>
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
                $suhuTerasa = $negara->cuaca_suhu_terasa;
                $kelembaban = $negara->cuaca_kelembaban;
                $tekanan = $negara->cuaca_tekanan_udara;
                $jarakPandang = $negara->cuaca_jarak_pandang;
                $tutupanAwan = $negara->cuaca_tutupan_awan;
                $deskripsi = $negara->cuaca_deskripsi ?? 'N/A';
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
                            <div style="font-size:0.7rem;color:#64748b;">{{ $negara->ibu_kota }} &bull; {{ $deskripsi }}</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:1.1rem;font-weight:800;color:{{ $color }};">
                            {{ $suhu !== null ? number_format($suhu,1).'°C' : '--' }}
                        </div>
                        <div style="font-size:0.68rem;color:#94a3b8;">Terasa: {{ $suhuTerasa !== null ? number_format($suhuTerasa,1).'°C' : '--' }}</div>
                    </div>
                </div>
                
                @if($suhu !== null)
                <div class="mt-2 pt-2" style="border-top:1px dashed rgba(255,255,255,0.05); font-size:0.72rem; color:#94a3b8;">
                    <div class="row g-1">
                        <div class="col-6"><i class="fas fa-wind me-1 text-info"></i>Angin: {{ number_format($angin,1) }} km/h</div>
                        <div class="col-6"><i class="fas fa-tint me-1 text-primary"></i>Lembab: {{ $kelembaban }}%</div>
                        <div class="col-6"><i class="fas fa-gauge me-1 text-warning"></i>Tekanan: {{ $tekanan }} hPa</div>
                        <div class="col-6"><i class="fas fa-cloud me-1 text-secondary"></i>Awan: {{ $tutupanAwan }}%</div>
                        <div class="col-6"><i class="fas fa-eye me-1 text-success"></i>Pandang: {{ number_format($jarakPandang,1) }} km</div>
                        <div class="col-6"><i class="fas fa-cloud-showers-heavy me-1 text-primary"></i>Hujan: {{ number_format($hujan,1) }} mm</div>
                    </div>
                    <div class="mt-1 text-end" style="font-size:0.65rem; color:#64748b;">
                        Risiko Badai: <span class="fw-600" style="color:{{ $color }}">{{ number_format($badai,0) }}%</span>
                    </div>
                </div>
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
const markers = [];

// Inisialisasi Leaflet Map
const map = L.map('peta-cuaca', {
    center: [20, 10],
    zoom: 2,
    zoomControl: true,
    attributionControl: false
});

// Layer OpenStreetMap Dark
L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
    attribution: '&copy; Esri'
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
    const suhuTerasa = negara.cuaca_suhu_terasa !== null ? negara.cuaca_suhu_terasa.toFixed(1)+'°C' : 'N/A';
    const angin = negara.cuaca_kecepatan_angin !== null ? negara.cuaca_kecepatan_angin.toFixed(0)+' km/h' : 'N/A';
    const hujan = negara.cuaca_curah_hujan !== null ? negara.cuaca_curah_hujan.toFixed(1)+' mm' : 'N/A';
    const kelembaban = negara.cuaca_kelembaban !== null ? negara.cuaca_kelembaban + '%' : 'N/A';
    const deskripsi = negara.cuaca_deskripsi || 'N/A';
    const total = skor ? skor.total_risiko : '-';

    const popup = `
        <div class="cuaca-popup">
            <h6 class="mb-1" style="font-weight:700;color:#1e293b;">${negara.nama} (${negara.kode_iso2})</h6>
            <div style="font-size:0.72rem;color:#64748b;margin-bottom:6px;">Ibu Kota: ${negara.ibu_kota} &bull; ${deskripsi}</div>
            <div class="cuaca-item"><span>Suhu</span><strong>${suhu} (Terasa: ${suhuTerasa})</strong></div>
            <div class="cuaca-item"><span>Angin</span><strong>${angin}</strong></div>
            <div class="cuaca-item"><span>Kelembaban</span><strong>${kelembaban}</strong></div>
            <div class="cuaca-item"><span>Curah Hujan</span><strong>${hujan}</strong></div>
            <div class="cuaca-item"><span>Skor Risiko</span><strong style="color:${color};">${total}/100</strong></div>
            <div class="cuaca-item"><span>Tingkat</span><strong style="color:${color};">${tk}</strong></div>
        </div>
    `;

    const marker = L.marker([negara.lintang, negara.bujur], { icon }).addTo(map).bindPopup(popup);
    markers.push({ id: negara.id, marker: marker });
});

// Inisialisasi Tom Select untuk mencari negara & zoom
document.addEventListener("DOMContentLoaded", function () {

    const ts = new TomSelect("#cariNegaraCuaca", {

        create: false,

        maxOptions: null,

        dropdownParent: "body",

        sortField: {
            field: "text",
            direction: "asc"
        },

        onInitialize() {
            this.dropdown.style.zIndex = "999999";
        },

        onChange: function(id) {

            if (!id) return;

            const opt = document
                .getElementById("cariNegaraCuaca")
                .querySelector(`option[value="${id}"]`);

            if (opt) {

                const lat = parseFloat(opt.dataset.lat);

                const lng = parseFloat(opt.dataset.lng);

                if (lat && lng) {

                    map.setView([lat,lng],6);

                    const mObj = markers.find(m=>m.id==id);

                    if(mObj){
                        mObj.marker.openPopup();
                    }

                }

            }

        }

    });

});
</script>
@endpush
