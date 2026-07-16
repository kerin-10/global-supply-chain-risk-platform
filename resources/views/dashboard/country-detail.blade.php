@extends('layouts.app')

@section('title', $negara->nama)

@section('page-title')
<a href="{{ route('dashboard.index') }}" class="btn btn-sm btn-outline-secondary me-2 text-decoration-none" style="border-color: rgba(255,255,255,0.15);">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
<i class="fas fa-globe me-2" style="color:#3b82f6;"></i> Dashboard {{ $negara->nama }}
@endsection

@push('styles')
<style>
    .hero-banner{
    background:#fff;
    border:1px solid #E2E8F0;
    border-radius:16px;
    padding:2rem;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.flag-img{
    width:100px;
    border-radius:10px;
    border:1px solid #E2E8F0;
    box-shadow:0 4px 12px rgba(0,0,0,.08);
}

.risk-radial-card{
    background:#fff;
    border:1px solid #E2E8F0;
    border-radius:14px;
    padding:1.5rem;
    text-align:center;
    min-height:100%;
}

.circle-score{
    width:110px;
    height:110px;
    border-radius:50%;
    margin:auto;
    margin-bottom:1rem;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    border:5px solid;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.circle-score-val{
    font-size:1.8rem;
    font-weight:800;
}

.circle-score-label{
    font-size:.7rem;
    color:#64748B;
}

.risk-breakdown-item{
    background:#F8FAFC;
    border:1px solid #E2E8F0;
    border-radius:10px;
    padding:1rem;
    margin-bottom:1rem;
}

.detail-card{
    background:#fff;
    border:1px solid #E2E8F0;
    border-radius:14px;
    min-height:100%;
    box-shadow:0 5px 15px rgba(0,0,0,.05);
}

.weather-grid-item{
    background:#F8FAFC;
    border:1px solid #E2E8F0;
    border-radius:10px;
    padding:1rem;
    text-align:center;
}

.weather-grid-item i{
    font-size:20px;
    margin-bottom:6px;
}

.weather-grid-item div:first-of-type{
    color:#64748B !important;
}

.weather-grid-item div:last-of-type{
    color:#0F172A;
    font-weight:700;
}

.news-item{
    border-bottom:1px solid #E2E8F0;
    padding:1rem 0;
}

.news-item:last-child{
    border-bottom:none;
}

.loader-overlay{
    position:fixed;
    inset:0;
    display:none;
    justify-content:center;
    align-items:center;
    flex-direction:column;
    background:rgba(255,255,255,.85);
    backdrop-filter:blur(6px);
    z-index:9999;
}

.loader-spinner{
    width:60px;
    height:60px;
    border:6px solid #E2E8F0;
    border-top:6px solid #2563EB;
    border-radius:50%;
    animation:spin 1s linear infinite;
}

.glass-card{
    background:#fff;
    border:1px solid #E2E8F0;
    box-shadow:0 5px 15px rgba(0,0,0,.05);
}

.glass-card h6,
.hero-banner h2,
.hero-banner h5,
.hero-banner h6{
    color:#0F172A !important;
}

.glass-card p,
.hero-banner p{
    color:#64748B !important;
}

.table{
    color:#0F172A;
}

.table td{
    border-color:#E2E8F0;
}

.table-striped>tbody>tr:nth-of-type(odd){
    background:#F8FAFC;
}

.progress{
    background:#E2E8F0;
}

.badge{
    font-weight:600;
}

@keyframes spin{
    to{
        transform:rotate(360deg);
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-2">

    <!-- LOADING OVERLAY -->
    <div id="loader" class="loader-overlay">
        <div class="loader-spinner mb-3"></div>
        <h5 class="mb-0 fw-800 text-dark">"Sinkronisasi Data Real-time...</h5>
        <p class="text-muted" style="font-size:0.85rem;">Menghubungkan ke REST Countries, World Bank & Open-Meteo...</p>
    </div>

    <!-- HERO / BANNER NEGARA -->
    <div class="hero-banner mb-4">
        <div class="row align-items-center g-4">
            <div class="col-12 col-md-8">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
                    @if($negara->bendera_url)
                        <img src="https://flagcdn.com/w320/{{ strtolower($negara->kode_iso2) }}.png"
                            alt="Bendera {{ $negara->nama }}"
                            class="flag-img">
                    @else
                        <div class="flag-img bg-secondary d-flex align-items-center justify-content-center" style="width:100px;height:60px;">
                            <i class="fas fa-flag fa-2x text-white-50"></i>
                        </div>
                    @endif
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h2 class="mb-0 fw-800 text-white">{{ $negara->nama }}</h2>
                            <span class="badge bg-secondary" style="font-size:0.8rem; background:rgba(255,255,255,0.08) !important;">{{ $negara->kode_iso2 }} / {{ $negara->kode_iso3 }}</span>
                        </div>
                        <p class="text-muted mb-0 mt-1" style="font-size:0.9rem;">
                            <i class="fas fa-map-marker-alt me-1 text-primary"></i> Wilayah: <strong>{{ $negara->wilayah }}</strong> &bull; Ibu Kota: <strong>{{ $negara->ibu_kota ?? '-' }}</strong>
                        </p>
                        <p class="text-muted mb-0 mt-1" style="font-size:0.8rem;">
                            Update Terakhir: {{ $negara->sinkronisasi_terakhir_pada ? $negara->sinkronisasi_terakhir_pada->diffForHumans() : 'Belum pernah disinkronkan' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <button onclick="syncCountry('{{ $negara->kode_iso2 }}')" class="btn btn-primary-glow px-4 py-2">
                    <i class="fas fa-sync-alt me-2"></i> Sinkronisasi Real-time
                </button>
            </div>
        </div>
    </div>

    @php
        $skor = $negara->currentRiskScore;
        $totalRisiko = $skor ? $skor->total_risiko : 0;
        $tingkat = $skor ? $skor->tingkat_risiko : 'Rendah';
        $riskColor = $tingkat === 'Rendah' ? '#10b981' : ($tingkat === 'Sedang' ? '#f59e0b' : '#ef4444');
        $riskBg = $tingkat === 'Rendah' ? 'rgba(16,185,129,0.15)' : ($tingkat === 'Sedang' ? 'rgba(245,158,11,0.15)' : 'rgba(239,68,68,0.15)');
        
        // Bobot
        $bobotCuaca = 30;
        $bobotInflasi = 20;
        $bobotKurs = 10;
        $bobotSentimen = 40;
    @endphp

    <!-- BARIS PERTAMA: BREAKDOWN & ESTIMASI RISIKO -->
    <div class="row g-4 mb-4">
        <!-- CARD SKOR RISIKO TOTAL -->
        <div class="col-12 col-lg-4">
            <div class="risk-radial-card d-flex flex-column justify-content-center">
                <h6 class="fw-700 text-muted mb-3">Tingkat Risiko Rantai Pasok</h6>
                
                <div class="circle-score" style="border-color: {{ $riskColor }}; color: {{ $riskColor }}; background: {{ $riskBg }};">
                    <span class="circle-score-val">{{ $totalRisiko }}</span>
                    <span class="circle-score-label">Skor Indeks</span>
                </div>
                
                <h5 class="fw-700 mt-2" style="color: {{ $riskColor }};">Risiko {{ $tingkat }}</h5>
                <p class="text-muted px-2" style="font-size:0.78rem;">
                    Skor dihitung berdasarkan bobot gabungan indikator cuaca real-time ({{ $bobotCuaca }}%), stabilitas inflasi ({{ $bobotInflasi }}%), volatilitas nilai tukar ({{ $bobotKurs }}%), dan analisis sentimen berita logistik global ({{ $bobotSentimen }}%).
                </p>
            </div>
        </div>

        <!-- BREAKDOWN PER KATEGORI -->
        <div class="col-12 col-lg-8">
            <div class="glass-card detail-card">
                <h6 class="fw-700 mb-3 text-white"><i class="fas fa-chart-pie me-2 text-primary"></i> Rincian Bobot Kontribusi Risiko</h6>
                
                <div class="row g-3">
                    <!-- KATEGORI CUACA -->
                    <div class="col-12 col-md-6">
                        <div class="risk-breakdown-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-600 text-white" style="font-size:0.82rem;"><i class="fas fa-cloud-sun-rain me-2 text-info"></i> Risiko Cuaca</span>
                                <span class="fw-700 text-info" style="font-size:0.85rem;">{{ $skor ? $skor->risiko_cuaca : 0 }}/100</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.05);">
                                <div class="progress-bar bg-info" style="width: {{ $skor ? $skor->risiko_cuaca : 0 }}%;"></div>
                            </div>
                            <span class="text-muted d-block" style="font-size:0.68rem;">
                                @if($negara->cuaca_suhu !== null)
                                    {{ $negara->cuaca_deskripsi ?? 'Cuaca terdeteksi' }} &bull; Suhu {{ $negara->cuaca_suhu }}°C &bull; Angin {{ $negara->cuaca_kecepatan_angin }} km/h &bull; Kontribusi: <strong>{{ number_format(($skor ? $skor->risiko_cuaca : 0) * ($bobotCuaca/100), 1) }}%</strong>
                                @else
                                    Data cuaca belum tersedia. Harap sinkronisasi.
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- KATEGORI INFLASI / EKONOMI -->
                    <div class="col-12 col-md-6">
                        <div class="risk-breakdown-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-600 text-white" style="font-size:0.82rem;"><i class="fas fa-chart-line me-2 text-warning"></i> Risiko Inflasi</span>
                                <span class="fw-700 text-warning" style="font-size:0.85rem;">{{ $skor ? $skor->risiko_inflasi : 0 }}/100</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.05);">
                                <div class="progress-bar bg-warning" style="width: {{ $skor ? $skor->risiko_inflasi : 0 }}%;"></div>
                            </div>
                            <span class="text-muted d-block" style="font-size:0.68rem;">
                                Laju inflasi: {{ $negara->inflasi ?? '0.0' }}% &bull; GDP: ${{ number_format($negara->pdb / 1e9, 2) }} Miliar &bull; Kontribusi: <strong>{{ number_format(($skor ? $skor->risiko_inflasi : 0) * ($bobotInflasi/100), 1) }}%</strong>
                            </span>
                        </div>
                    </div>

                    <!-- KATEGORI NILAI TUKAR -->
                    <div class="col-12 col-md-6">
                        <div class="risk-breakdown-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-600 text-white" style="font-size:0.82rem;"><i class="fas fa-coins me-2 text-success"></i> Risiko Nilai Tukar</span>
                                <span class="fw-700 text-success" style="font-size:0.85rem;">{{ $skor ? $skor->risiko_nilai_tukar : 0 }}/100</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.05);">
                                <div class="progress-bar bg-success" style="width: {{ $skor ? $skor->risiko_nilai_tukar : 0 }}%;"></div>
                            </div>
                            <span class="text-muted d-block" style="font-size:0.68rem;">
                                Mata Uang: {{ $negara->kode_mata_uang }} ({{ $negara->nama_mata_uang ?? 'Valuta' }}) &bull; Kontribusi: <strong>{{ number_format(($skor ? $skor->risiko_nilai_tukar : 0) * ($bobotKurs/100), 1) }}%</strong>
                            </span>
                        </div>
                    </div>

                    <!-- KATEGORI SENTIMEN BERITA -->
                    <div class="col-12 col-md-6">
                        <div class="risk-breakdown-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-600 text-white" style="font-size:0.82rem;"><i class="fas fa-newspaper me-2 text-danger"></i> Risiko Sentimen Berita</span>
                                <span class="fw-700 text-danger" style="font-size:0.85rem;">{{ $skor ? $skor->risiko_sentimen_berita : 0 }}/100</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.05);">
                                <div class="progress-bar bg-danger" style="width: {{ $skor ? $skor->risiko_sentimen_berita : 0 }}%;"></div>
                            </div>
                            <span class="text-muted d-block" style="font-size:0.68rem;">
                                Sentimen dihitung dari penyebutan kata-kata negatif logistik di media &bull; Kontribusi: <strong>{{ number_format(($skor ? $skor->risiko_sentimen_berita : 0) * ($bobotSentimen/100), 1) }}%</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS KEDUA: CUACA DETAIL & PROFIL EKONOMI -->
    <div class="row g-4 mb-4">
        <!-- DETAIL CUACA LENGKAP -->
        <div class="col-12 col-md-6">
            <div class="glass-card detail-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-700 text-white mb-0"><i class="fas fa-temperature-high me-2 text-info"></i> Informasi Cuaca Detil</h6>
                    @if($negara->cuaca_suhu !== null)
                        <span class="badge bg-info-subtle text-info">{{ $negara->cuaca_deskripsi }}</span>
                    @endif
                </div>

                @if($negara->cuaca_suhu !== null)
                    <div class="row g-3">
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item">
                                <i class="fas fa-thermometer-half text-danger"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Suhu Aktual</div>
                                <div style="font-size:1rem;font-weight:700;">{{ number_format($negara->cuaca_suhu,1) }}°C</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item">
                                <i class="fas fa-user-tie text-warning"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Suhu Terasa</div>
                                <div style="font-size:1rem;font-weight:700;">{{ number_format($negara->cuaca_suhu_terasa ?? $negara->cuaca_suhu,1) }}°C</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item">
                                <i class="fas fa-tint text-primary"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Kelembaban</div>
                                <div style="font-size:1rem;font-weight:700;">{{ $negara->cuaca_kelembaban ?? 'N/A' }}%</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item">
                                <i class="fas fa-gauge-high text-success"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Tekanan Udara</div>
                                <div style="font-size:1rem;font-weight:700;">{{ $negara->cuaca_tekanan_udara ?? 'N/A' }} hPa</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item">
                                <i class="fas fa-eye text-info"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Jarak Pandang</div>
                                <div style="font-size:1rem;font-weight:700;">{{ number_format($negara->cuaca_jarak_pandang ?? 10,1) }} km</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item">
                                <i class="fas fa-cloud text-secondary"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Tutupan Awan</div>
                                <div style="font-size:1rem;font-weight:700;">{{ $negara->cuaca_tutupan_awan ?? '0' }}%</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item">
                                <i class="fas fa-wind text-white-50"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Kecepatan Angin</div>
                                <div style="font-size:1rem;font-weight:700;">{{ number_format($negara->cuaca_kecepatan_angin, 1) }} km/h</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item">
                                <i class="fas fa-cloud-showers-heavy text-primary"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Curah Hujan</div>
                                <div style="font-size:1rem;font-weight:700;">{{ number_format($negara->cuaca_curah_hujan,1) }} mm</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="weather-grid-item" style="background: {{ $riskBg }}">
                                <i class="fas fa-bolt-lightning" style="color: {{ $riskColor }}"></i>
                                <div style="font-size:0.68rem;color:#94a3b8;">Risiko Badai</div>
                                <div style="font-size:1rem;font-weight:700;color: {{ $riskColor }}">{{ number_format($negara->cuaca_risiko_badai ?? 10.0,0) }}%</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-cloud-sun-rain fa-3x mb-3"></i>
                        <div>Kondisi cuaca belum sinkron. Klik tombol sinkronisasi.</div>
                    </div>
                @endif
            </div>
        </div>
<!-- INFORMASI EKONOMI & NEGARA -->
<div class="col-12 col-md-6">
    <div class="glass-card detail-card" style="padding:0;overflow:hidden;">
        <div class="px-4 py-3" style="border-bottom:1px solid #E2E8F0;">
            <h6 class="mb-0 fw-700 text-dark">
                <i class="fas fa-globe-americas me-2 text-success"></i>
                Profil & Ekonomi Real-time
            </h6>
        </div>

        <div class="px-4 py-2" style="max-height:280px;overflow-y:auto;">
            <table class="table table-striped table-hover mb-0" style="font-size:.85rem;">
                <tbody>

                <tr>
                    <td class="text-secondary py-2" width="170">Populasi Penduduk</td>
                    <td class="text-dark py-2 fw-semibold">
                        @if($negara->populasi)
                            {{ number_format($negara->populasi) }} jiwa ({{ number_format($negara->populasi / 1e6,2) }} Juta)
                        @else
                            -
                        @endif
                    </td>
                </tr>

                <tr>
                    <td class="text-secondary py-2">GDP Nominal</td>
                    <td class="text-dark py-2 fw-semibold">
                        @if($negara->pdb)
                            ${{ number_format($negara->pdb) }} ({{ number_format($negara->pdb / 1e12,2) }} Triliun USD)
                        @else
                            -
                        @endif
                    </td>
                </tr>

                <tr>
                    <td class="text-secondary py-2">Tingkat Inflasi</td>
                    <td class="text-warning py-2 fw-semibold">
                        {{ $negara->inflasi ?? '0.0' }}%
                    </td>
                </tr>

                <tr>
                    <td class="text-secondary py-2">Luas Wilayah</td>
                    <td class="text-dark py-2 fw-semibold">
                        @if($negara->luas_wilayah)
                            {{ number_format($negara->luas_wilayah) }} km²
                        @else
                            -
                        @endif
                    </td>
                </tr>

                <tr>
                    <td class="text-secondary py-2">Mata Uang Resmi</td>
                    <td class="text-success py-2 fw-semibold">
                        {{ $negara->kode_mata_uang }} – {{ $negara->nama_mata_uang ?? 'N/A' }}
                    </td>
                </tr>

                <tr>
                    <td class="text-secondary py-2">Bahasa Resmi</td>
                    <td class="text-dark py-2 fw-semibold" style="font-size:.8rem;">
                        {{ $negara->bahasa ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="text-secondary py-2">Volume Ekspor / Impor</td>
                    <td class="text-dark py-2 fw-semibold">
                        Ekspor :
                        <span class="text-success">
                            ${{ number_format($negara->nilai_ekspor / 1e9,1) }}B
                        </span>
                        <br>

                        Impor :
                        <span class="text-danger">
                            ${{ number_format($negara->nilai_impor / 1e9,1) }}B
                        </span>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- BARIS KETIGA: PELABUHAN & BERITA TERKINI -->
    <div class="row g-4 mb-4">
        <!-- PELABUHAN -->
        <div class="col-12 col-md-5">
            <div class="glass-card detail-card" style="padding:0;overflow:hidden;">
                <div class="px-4 py-3 d-flex justify-content-between align-items-center" style="border-bottom:1px solid rgba(255,255,255,0.05);">
                    <h6 class="mb-0 fw-700 text-white"><i class="fas fa-anchor me-2 text-primary"></i> Pelabuhan ({{ $negara->ports->count() }})</h6>
                    <a href="{{ route('dashboard.ports') }}?negara={{ $negara->kode_iso2 }}" class="btn btn-sm btn-link text-decoration-none text-primary" style="font-size:0.75rem;">
                        Semua Pelabuhan <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
                <div style="max-height: 350px; overflow-y: auto;">
                    @forelse($negara->ports as $port)
                        @php
                            $congestion = $port->latestCongestion;
                            $tk = $congestion ? $congestion->tingkat_kemacetan : 'N/A';
                            $badge = $tk === 'Rendah' ? 'bg-success' : ($tk === 'Sedang' ? 'bg-warning' : 'bg-danger');
                        @endphp
                        <div class="px-4 py-3" style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-600 text-white" style="font-size:0.85rem;">{{ $port->nama }}</span>
                                <span class="badge {{ $badge }} text-dark" style="font-size:0.68rem; font-weight:700;">{{ $tk }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size:0.72rem;">
                                <span>WPI: {{ $port->nomor_wpi ?? '-' }}</span>
                                <span>Delay: <strong>{{ $congestion ? $congestion->waktu_tunda_jam : 0 }} jam</strong></span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-anchor fa-2x mb-2"></i>
                            <div>Tidak ada pelabuhan terdaftar untuk negara ini.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- BERITA TERBARU -->
        <div class="col-12 col-md-7">
            <div class="glass-card detail-card" style="padding:0;overflow:hidden;">
                <div class="px-4 py-3" style="border-bottom:1px solid rgba(255,255,255,0.05);">
                    <h6 class="mb-0 fw-700 text-white"><i class="fas fa-newspaper me-2 text-danger"></i> Berita Logistik & Sentimen</h6>
                </div>
                <div class="px-4" style="max-height:350px; overflow-y:auto;">
                    @forelse($negara->newsCaches as $news)
                        @php
                            $sentimen = $news->sentimen;
                            $sColor = $sentimen === 'Positif' ? 'text-success' : ($sentimen === 'Negatif' ? 'text-danger' : 'text-warning');
                            $sBg = $sentimen === 'Positif' ? 'rgba(16,185,129,0.1)' : ($sentimen === 'Negatif' ? 'rgba(239,68,68,0.1)' : 'rgba(245,158,11,0.1)');
                        @endphp
                        <div class="news-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="{{ $news->tautan_url }}" target="_blank" class="fw-600 text-white text-decoration-none" style="font-size:0.83rem; line-height:1.3;">
                                    {{ $news->judul }}
                                </a>
                                <span class="badge text-uppercase" style="font-size:0.6rem; color: {{ $sColor }}; background: {{ $sBg }}; font-weight:700;">
                                    {{ $sentimen }}
                                </span>
                            </div>
                            <p class="text-muted mb-0 mt-1" style="font-size:0.75rem; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                {{ $news->deskripsi }}
                            </p>
                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size:0.68rem;">
                                <span>Sumber: {{ $news->sumber }}</span>
                                <span>{{ $news->diterbitkan_pada ? $news->diterbitkan_pada->format('d M Y, H:i') : '-' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-newspaper fa-2x mb-2"></i>
                            <div>Belum ada berita tersinkronisasi.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS KEEMPAT: TREN EKONOMI HISTORIS -->
    @if($negara->economicHistories->count() > 0)
    <div class="row g-4">
        <div class="col-12">
            <div class="glass-card">
                <h6 class="fw-700 text-white mb-3"><i class="fas fa-chart-area me-2 text-warning"></i> Tren Pertumbuhan Ekonomi Historis (GDP & Inflasi)</h6>
                <canvas id="econChart" height="280"></canvas>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // FUNGSI SINKRONISASI REAL-TIME
    async function syncCountry(kode_iso2) {
        const loader = document.getElementById('loader');
        loader.style.display = 'flex';
        
        try {
            const res = await axios.post('/api/v1/countries/sync', { kode_iso2: kode_iso2 });
            if (res.data.status === 'sukses') {
                window.location.reload();
            } else {
                alert('Gagal sinkronisasi data: ' + (res.data.pesan || 'Kesalahan sistem'));
                loader.style.display = 'none';
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan atau server: ' + (e.response?.data?.pesan || e.message));
            loader.style.display = 'none';
        }
    }

    // GRAFIK EKONOMI TREN HISTORIS
    @if($negara->economicHistories->count() > 0)
        const histData = @json($negara->economicHistories->sortBy('tahun')->values());
        
        const labels = histData.map(h => h.tahun);
        const gdpData = histData.map(h => h.pdb / 1e12); // Convert to Trillion USD
        const inflationData = histData.map(h => h.inflasi);

        const ctx = document.getElementById('econChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'GDP (Triliun USD)',
                        data: gdpData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Inflasi (%)',
                        data: inflationData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.05)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'GDP (Triliun USD)',
                            color: '#94a3b8'
                        },
                        ticks: { color: '#64748b' },
                        grid: { color: 'rgba(255,255,255,0.03)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Inflasi (%)',
                            color: '#94a3b8'
                        },
                        ticks: { color: '#64748b' },
                        grid: { drawOnChartArea: false }, // Only show grid for left axis
                    },
                    x: {
                        ticks: { color: '#64748b' },
                        grid: { color: 'rgba(255,255,255,0.03)' }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#94a3b8', font: { family: 'Inter' } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(10,14,26,0.95)',
                        titleColor: '#fff',
                        bodyColor: '#94a3b8',
                        borderColor: 'rgba(59,130,246,0.2)',
                        borderWidth: 1
                    }
                }
            }
        });
    @endif
</script>
@endpush