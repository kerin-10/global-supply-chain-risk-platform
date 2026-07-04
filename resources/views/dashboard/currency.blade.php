@extends('layouts.app')
@section('title', 'Nilai Tukar Mata Uang')
@section('page-title', '<i class="fas fa-exchange-alt me-2" style="color:#10b981;"></i>Nilai Tukar Mata Uang')

@section('content')
<div class="row g-3">
    <!-- KONVERTER -->
    <div class="col-12 col-md-5">
        <div class="glass-card">
            <h6 class="fw-700 mb-3"><i class="fas fa-calculator me-2" style="color:#10b981;"></i>Kalkulator Nilai Tukar</h6>
            <div class="mb-3">
                <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1">Mata Uang Asal</label>
                <select id="base-currency" class="form-select" style="background:rgba(15,23,42,0.8);border:1px solid rgba(16,185,129,0.2);color:#f1f5f9;border-radius:8px;">
                    @foreach($negaraList as $n)
                        <option value="{{ $n->kode_mata_uang }}" {{ $n->kode_mata_uang === 'USD' ? 'selected' : '' }}>
                            {{ $n->kode_mata_uang }} – {{ $n->nama_mata_uang }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1">Mata Uang Tujuan</label>
                <select id="target-currency" class="form-select" style="background:rgba(15,23,42,0.8);border:1px solid rgba(16,185,129,0.2);color:#f1f5f9;border-radius:8px;">
                    @foreach($negaraList as $n)
                        <option value="{{ $n->kode_mata_uang }}" {{ $n->kode_mata_uang === 'IDR' ? 'selected' : '' }}>
                            {{ $n->kode_mata_uang }} – {{ $n->nama_mata_uang }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1">Jumlah</label>
                <input type="number" id="jumlah-konversi" class="form-control" value="1" min="0"
                       style="background:rgba(15,23,42,0.8);border:1px solid rgba(16,185,129,0.2);color:#f1f5f9;border-radius:8px;">
            </div>
            <button onclick="konversiMataUang()" class="btn btn-primary-glow w-100" style="background:linear-gradient(135deg,#10b981,#059669);">
                <i class="fas fa-sync-alt me-2"></i>Hitung Nilai Tukar
            </button>

            <div id="hasil-konversi" class="mt-3" style="display:none;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:12px;padding:1.25rem;text-align:center;">
                <div style="font-size:0.75rem;color:#94a3b8;" id="label-konversi">1 USD =</div>
                <div style="font-size:2rem;font-weight:800;color:#10b981;" id="nilai-konversi">…</div>
                <div style="font-size:0.72rem;color:#64748b;" id="waktu-update">Diperbarui: –</div>
            </div>
        </div>

        <!-- TABEL RATES CACHE -->
        <div class="glass-card mt-3" style="padding:0;overflow:hidden;">
            <div class="px-4 py-3" style="border-bottom:1px solid rgba(16,185,129,0.1);">
                <h6 class="mb-0 fw-700"><i class="fas fa-table me-2" style="color:#10b981;"></i>Cache Nilai Tukar (Base: USD)</h6>
            </div>
            <div style="max-height:280px;overflow-y:auto;">
                <table class="table table-glass mb-0">
                    <thead><tr><th>Mata Uang</th><th class="text-end">1 USD =</th></tr></thead>
                    <tbody>
                    @forelse($ratesCache as $rate)
                        <tr>
                            <td style="font-size:0.83rem;font-weight:600;">{{ $rate->mata_uang_tujuan }}</td>
                            <td class="text-end" style="font-size:0.83rem;color:#10b981;font-weight:600;">{{ number_format($rate->nilai_tukar, 4) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center py-3" style="color:#475569;font-size:0.82rem;">Cache kosong. Gunakan kalkulator untuk sinkronisasi data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- GRAFIK TREN -->
    <div class="col-12 col-md-7">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-700 mb-0"><i class="fas fa-chart-line me-2" style="color:#10b981;"></i>Tren Nilai Tukar (Simulasi Historis)</h6>
            </div>
            <canvas id="chartKurs" height="280"></canvas>
        </div>

        <!-- INFORMASI NEGARA + MATA UANG -->
        <div class="glass-card mt-3" style="padding:0;overflow:hidden;">
            <div class="px-4 py-3" style="border-bottom:1px solid rgba(16,185,129,0.1);">
                <h6 class="mb-0 fw-700"><i class="fas fa-coins me-2" style="color:#f59e0b;"></i>Mata Uang Negara yang Dipantau</h6>
            </div>
            <div class="px-4 py-2">
                <div class="row g-2 py-2">
                @foreach($negaraList as $n)
                <div class="col-6 col-md-4">
                    <div style="background:rgba(59,130,246,0.05);border:1px solid rgba(59,130,246,0.1);border-radius:8px;padding:0.6rem;text-align:center;">
                        <div style="font-weight:700;font-size:1rem;color:#f59e0b;">{{ $n->kode_mata_uang }}</div>
                        <div style="font-size:0.68rem;color:#94a3b8;">{{ $n->nama_mata_uang }}</div>
                        <div style="font-size:0.7rem;color:#64748b;">{{ $n->nama }}</div>
                    </div>
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
// Grafik Tren Kurs (simulasi historis berdasarkan data yang tersedia)
const chartCtx = document.getElementById('chartKurs').getContext('2d');
const chartKurs = new Chart(chartCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'USD/IDR',
            data: [15500, 15750, 15600, 15900, 16100, 16200, 16050, 15800, 15950, 16300, 16400, 16250],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.08)',
            borderWidth: 2, fill: true, tension: 0.4,
            pointBackgroundColor: '#10b981', pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 12 } } },
            tooltip: { backgroundColor: 'rgba(10,14,26,0.9)', titleColor: '#f1f5f9', bodyColor: '#94a3b8' }
        },
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(59,130,246,0.06)' } },
            y: { ticks: { color: '#64748b' }, grid: { color: 'rgba(59,130,246,0.06)' } }
        }
    }
});

async function konversiMataUang() {
    const base   = document.getElementById('base-currency').value;
    const target = document.getElementById('target-currency').value;
    const jumlah = parseFloat(document.getElementById('jumlah-konversi').value) || 1;

    try {
        const res = await axios.get(`/api/v1/currency?base=${base}&target=${target}&sync=true`);
        const d = res.data;
        if (d.nilai_tukar) {
            const hasil = (d.nilai_tukar * jumlah).toLocaleString('id-ID', { maximumFractionDigits: 4 });
            document.getElementById('hasil-konversi').style.display = 'block';
            document.getElementById('label-konversi').textContent = `${jumlah} ${base} =`;
            document.getElementById('nilai-konversi').textContent = `${hasil} ${target}`;
            document.getElementById('waktu-update').textContent = 'Diperbarui: ' + (d.terakhir_diperbarui || '-');

            // Update chart jika ada riwayat
            if (d.riwayat_tren && d.riwayat_tren.length > 0) {
                chartKurs.data.labels = d.riwayat_tren.map(r => r.tahun);
                chartKurs.data.datasets[0].label = `${base}/${target}`;
                chartKurs.data.datasets[0].data  = d.riwayat_tren.map(r => r.nilai_tukar);
                chartKurs.update();
            }
        }
    } catch(e) {
        alert('Gagal mengambil nilai tukar: ' + (e.response?.data?.pesan || e.message));
    }
}
</script>
@endpush
