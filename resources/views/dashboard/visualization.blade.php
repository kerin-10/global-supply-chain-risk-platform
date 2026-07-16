@extends('layouts.app')

@section('title','Visualisasi Data')

@section('page-title')
<i class="fas fa-chart-line me-2" style="color:#3b82f6;"></i>
Visualisasi Data
@endsection

@push('styles')
<style>
.chart-card{
    background:#FFFFFF;
    border:1px solid #E2E8F0;
    border-radius:16px;
    padding:20px;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
    transition:.25s;
    height:100%;
}

.chart-card:hover{
    transform:translateY(-3px);
    box-shadow:0 12px 28px rgba(59,130,246,.12);
}

.chart-title{
    color:#0F172A;
    font-size:16px;
    font-weight:700;
    margin-bottom:18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid #E2E8F0;
    padding-bottom:12px;
}

.filter-card{
    background:#FFFFFF;
    border:1px solid #E2E8F0;
    border-radius:16px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 4px 12px rgba(0,0,0,.06);
}

.filter-card label{
    color:#475569 !important;
    font-weight:600;
}

.form-select{
    background:#FFFFFF;
    color:#0F172A;
    border:1px solid #CBD5E1;
    border-radius:10px;
}

.form-select:focus{
    border-color:#3B82F6;
    box-shadow:0 0 0 .2rem rgba(59,130,246,.15);
}

.badge{
    font-size:.68rem !important;
    padding:.45rem .75rem;
    border-radius:8px;
}

canvas{
    width:100% !important;
    height:300px !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">

    <!-- FILTER ROW -->
    <div class="filter-card">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-light" style="font-size:0.8rem;color:#94a3b8;"><i class="fas fa-globe me-1 text-primary"></i> Pilih Negara</label>
                <select class="form-select" id="country">
                    @foreach($negaraList as $negara)
                        <option value="{{ $negara->id }}" {{ $firstCountry && $firstCountry->id === $negara->id ? 'selected' : '' }}>
                            {{ $negara->nama }} ({{ $negara->kode_iso2 }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label text-light" style="font-size:0.8rem;color:#94a3b8;"><i class="fas fa-sliders-h me-1 text-primary"></i> Filter Jenis Grafik</label>
                <select class="form-select" id="chart-type">
                    <option value="Semua Data">Semua Grafik</option>
                    <option value="GDP">GDP Nominal</option>
                    <option value="Inflation">Tingkat Inflasi</option>
                    <option value="Currency">Tren Nilai Tukar</option>
                    <option value="Risk">Profil Radar Risiko</option>
                </select>
            </div>
        </div>
    </div>

    <!-- CHARTS GRID -->
    <div class="row g-4">
        <!-- GDP CHART -->
        <div class="col-12 col-lg-6" id="gdp-card-col">
            <div class="chart-card">
                <div class="chart-title">
                    <span><i class="fas fa-chart-line me-2 text-primary"></i> Tren GDP Nominal</span>
                    <span class="badge bg-primary-subtle text-primary" style="font-size:0.65rem;">Line Chart</span>
                </div>
                <canvas id="gdpChart"></canvas>
            </div>
        </div>

        <!-- INFLATION CHART -->
        <div class="col-12 col-lg-6" id="inflation-card-col">
            <div class="chart-card">
                <div class="chart-title">
                    <span><i class="fas fa-chart-bar me-2 text-warning"></i> Tren Tingkat Inflasi</span>
                    <span class="badge bg-warning-subtle text-warning" style="font-size:0.65rem;">Bar Chart</span>
                </div>
                <canvas id="inflationChart"></canvas>
            </div>
        </div>

        <!-- CURRENCY CHART -->
        <div class="col-12 col-lg-6" id="currency-card-col">
            <div class="chart-card">
                <div class="chart-title">
                    <span><i class="fas fa-coins me-2 text-success"></i> Tren Nilai Tukar (Simulasi)</span>
                    <span class="badge bg-success-subtle text-success" style="font-size:0.65rem;">Line Chart</span>
                </div>
                <canvas id="currencyChart"></canvas>
            </div>
        </div>

        <!-- RISK RADAR CHART -->
        <div class="col-12 col-lg-6" id="risk-card-col">
            <div class="chart-card">
                <div class="chart-title">
                    <span><i class="fas fa-circle-notch me-2 text-danger"></i> Profil Indeks Radar Risiko</span>
                    <span class="badge bg-danger-subtle text-danger" style="font-size:0.65rem;">Radar Chart</span>
                </div>
                <canvas id="riskChart"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Data dari Laravel
    const negaraData = @json($negaraList);

    
    // Initialisasi chart instances kosong
    let gdpChartInstance = null;
    let inflationChartInstance = null;
    let currencyChartInstance = null;
    let riskChartInstance = null;

    // Tema warna
    const darkGridColor='rgba(148,163,184,.25)';
    const textMutedColor='#475569';

    function initCharts() {
        // Chart GDP
        gdpChartInstance = new Chart(document.getElementById('gdpChart').getContext('2d'), {
            type: 'line',
            data: { labels: [], datasets: [{ label: 'GDP', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.05)', borderWidth: 3, fill: true, tension: 0.35 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { color: darkGridColor }, ticks: { color: textMutedColor } }, y: { grid: { color: darkGridColor }, ticks: { color: textMutedColor } } }, plugins: { legend: { labels: { color: '#334155' } } } }
        });

        // Chart Inflasi
        inflationChartInstance = new Chart(document.getElementById('inflationChart').getContext('2d'), {
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'Inflasi (%)', data: [], backgroundColor: 'rgba(245,158,11,0.75)', borderColor: '#f59e0b', borderWidth: 1 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { color: darkGridColor }, ticks: { color: textMutedColor } }, y: { grid: { color: darkGridColor }, ticks: { color: textMutedColor } } }, plugins: { legend: { labels: { color: '#334155' } } } }
        });

        // Chart Currency
        currencyChartInstance = new Chart(document.getElementById('currencyChart').getContext('2d'), {
            type: 'line',
            data: { labels: [], datasets: [{ label: 'Nilai Kurs', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.05)', borderWidth: 3, fill: true, tension: 0.35 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { grid: { color: darkGridColor }, ticks: { color: textMutedColor } }, y: { grid: { color: darkGridColor }, ticks: { color: textMutedColor } } }, plugins: { legend: { labels:{color:'#334155'} } } }
        });

        // Chart Risk Radar
        riskChartInstance = new Chart(document.getElementById('riskChart').getContext('2d'), {
            type: 'radar',
            data: {
                labels: ['Risiko Cuaca', 'Risiko Inflasi', 'Risiko Nilai Tukar', 'Risiko Sentimen'],
                datasets: [{ label: 'Profil Risiko', data: [], backgroundColor: 'rgba(239,68,68,0.15)', borderColor: '#ef4444', borderWidth: 2, pointBackgroundColor: '#ef4444', r: { min: 0, max: 100 } }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines:{color:'rgba(203,213,225,.8)'},
                        grid:{color:'rgba(203,213,225,.8)'},
                        pointLabels:{color:'#334155'},
                        ticks: { color: textMutedColor, backdropColor: 'transparent' },
                        min: 0,
                        max: 100
                    }
                },
                plugins: { legend: { labels:{color:'#334155'}} }
            }
        });
    }

    function updateCharts(countryId) {
        const negara = negaraData.find(n => n.id == countryId);
        if (!negara) return;

        // Parse riwayat ekonomi
        const histories = negara.economic_histories || [];
       
        // Urutkan berdasarkan tahun ascending
        histories.sort((a, b) => a.tahun - b.tahun);

        const years = histories.map(h => h.tahun);
        const gdp = histories.map(h => h.pdb / 1e12); // Triliun USD
        const inflasi = histories.map(h => h.inflasi);

        // Update GDP Chart
        gdpChartInstance.data.labels = years;
        gdpChartInstance.data.datasets[0].data = gdp;
        gdpChartInstance.data.datasets[0].label = `GDP ${negara.nama} (Triliun USD)`;
        gdpChartInstance.update();

        // Update Inflation Chart
        inflationChartInstance.data.labels = years;
        inflationChartInstance.data.datasets[0].data = inflasi;
        inflationChartInstance.data.datasets[0].label = `Inflasi ${negara.nama} (%)`;
        inflationChartInstance.update();

        // Update Currency Chart
        const currencyCode = negara.kode_mata_uang || 'USD';
        let baseRate = 1.0;
        if (currencyCode === 'IDR') baseRate = 16000;
        else if (currencyCode === 'EUR') baseRate = 0.92;
        else if (currencyCode === 'JPY') baseRate = 155;
        else if (currencyCode === 'SGD') baseRate = 1.35;
        else if (currencyCode === 'GBP') baseRate = 0.78;
        else if (currencyCode === 'AUD') baseRate = 1.5;
        else if (currencyCode === 'CNY') baseRate = 7.25;
        else if (currencyCode === 'BRL') baseRate = 5.2;
        else if (currencyCode === 'INR') baseRate = 83;
        else baseRate = 35.0;

        const currencyData = years.map((yr, idx) => {
            const inf = inflasi[idx] || 2.0;
            const variation = 1 + ((inf - 2) / 100);
            return Math.round((baseRate * variation) * 100) / 100;
        });

        currencyChartInstance.data.labels = years;
        currencyChartInstance.data.datasets[0].data = currencyData;
        currencyChartInstance.data.datasets[0].label = `Simulasi USD/${currencyCode}`;
        currencyChartInstance.update();

        // Update Radar Risk Chart
        const risk = negara.current_risk_score || {};
        const riskData = [
            risk.risiko_cuaca || 0,
            risk.risiko_inflasi || 0,
            risk.risiko_nilai_tukar || 0,
            risk.risiko_sentimen_berita || 0
        ];
        riskChartInstance.data.datasets[0].data = riskData;
        riskChartInstance.data.datasets[0].label = `Profil Risiko ${negara.nama}`;
        riskChartInstance.update();
    }

    // Inisialisasi on DOM Load
    document.addEventListener("DOMContentLoaded", function() {
        // Init chart.js
        initCharts();

        // Init Tom Select untuk negara
       const tsCountry = new TomSelect('#country', {
            create: false,
            maxOptions: null,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            onChange: function(val) {
                if (val) updateCharts(val);
            }
        });
        // Init Tom Select untuk tipe grafik
        const tsType = new TomSelect('#chart-type', {
            create: false,
            onChange: function(val) {
                const cols = {
                    'GDP': document.getElementById('gdp-card-col'),
                    'Inflation': document.getElementById('inflation-card-col'),
                    'Currency': document.getElementById('currency-card-col'),
                    'Risk': document.getElementById('risk-card-col')
                };

                if (val === 'Semua Data') {
                    Object.values(cols).forEach(el => el.style.display = 'block');
                } else {
                    Object.keys(cols).forEach(k => {
                        cols[k].style.display = (k === val) ? 'block' : 'none';
                    });
                }
            }
        });

        // Load data pertama kali
        const initialVal = document.getElementById('country').value;
        if (initialVal) {
            updateCharts(initialVal);
        }
    });
</script>
@endpush