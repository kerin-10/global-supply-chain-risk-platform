@extends('layouts.app')
@section('title', 'Perbandingan Negara')
@section('page-title')
    <i class="fas fa-balance-scale me-2" style="color:#F59E0B;"></i>Perbandingan Negara
@endsection

@section('content')
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="glass-card" style="padding:1.25rem 1.5rem;">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1"><i class="fas fa-globe me-1" style="color:#3b82f6;"></i>Negara Pertama</label>
                    <select id="negara1" class="form-select" style="background:rgba(15,23,42,0.8);border:1px solid rgba(59,130,246,0.2);color:#f1f5f9;border-radius:8px;">
                        @foreach($negaraList as $n)
                            <option value="{{ $n->id }}" data-obj="{{ json_encode($n) }}">{{ $n->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 text-center">
                    <div style="font-size:1.5rem;color:#f59e0b;font-weight:800;">VS</div>
                </div>
                <div class="col-md-4">
                    <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1"><i class="fas fa-globe me-1" style="color:#8b5cf6;"></i>Negara Kedua</label>
                    <select id="negara2" class="form-select" style="background:rgba(15,23,42,0.8);border:1px solid rgba(139,92,246,0.2);color:#f1f5f9;border-radius:8px;">
                        @foreach($negaraList as $n)
                            <option value="{{ $n->id }}" data-obj="{{ json_encode($n) }}" {{ $loop->index === 1 ? 'selected' : '' }}>{{ $n->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button onclick="bandingkanNegara()" class="btn btn-primary-glow w-100" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                        <i class="fas fa-balance-scale me-2"></i>Bandingkan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="compare-result" style="display:none;">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="glass-card" id="card-negara1" style="border-color:rgba(59,130,246,0.3);">
                <div class="text-center mb-3">
                    <div style="font-size:2rem;font-weight:800;" id="nama1">–</div>
                    <div style="color:#64748b;font-size:0.8rem;" id="kode1">–</div>
                </div>
                <div id="detail1"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card" id="card-negara2" style="border-color:rgba(139,92,246,0.3);">
                <div class="text-center mb-3">
                    <div style="font-size:2rem;font-weight:800;" id="nama2">–</div>
                    <div style="color:#64748b;font-size:0.8rem;" id="kode2">–</div>
                </div>
                <div id="detail2"></div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <div class="glass-card">
                <h6 class="fw-700 mb-3"><i class="fas fa-chart-radar me-2" style="color:#f59e0b;"></i>Perbandingan Radar Risiko</h6>
                <canvas id="chartRadar" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div id="empty-compare" class="text-center py-5" style="color:#475569;">
    <i class="fas fa-balance-scale fa-3x mb-3" style="color:#1e293b;"></i>
    <div>Pilih dua negara dan klik "Bandingkan" untuk melihat analisis perbandingan risiko.</div>
</div>
@endsection

@push('scripts')
<script>
const negaraData = @json($negaraList);

let radarChart = null;

let ts1, ts2;
document.addEventListener("DOMContentLoaded", function() {
    ts1 = new TomSelect('#negara1', {
    create: false,
    maxOptions: null,
    sortField: {
        field: 'text',
        direction: 'asc'
    }
});

ts2 = new TomSelect('#negara2', {
    create: false,
    maxOptions: null,
    sortField: {
        field: 'text',
        direction: 'asc'
    }
});
});

function getNegara(selectId) {
    const sel = document.getElementById(selectId);
    const id = parseInt(sel.value);
    return negaraData.find(n => n.id === id);
}

function renderDetail(containerId, n) {
    const skor = n.current_risk_score;
    const tk = skor ? skor.tingkat_risiko : 'N/A';
    const total = skor ? skor.total_risiko : 0;
    const color = tk==='Rendah'?'#10b981':(tk==='Sedang'?'#f59e0b':'#ef4444');

    const rows = [
        ['PDB',       '$' + ((n.pdb || 0) / 1e12).toFixed(2) + 'T'],
        ['Inflasi',   (n.inflasi || 0).toFixed(1) + '%'],
        ['Populasi',  ((n.populasi || 0) / 1e6).toFixed(1) + ' Juta'],
        ['Ibu Kota',  n.ibu_kota || '-'],
        ['Wilayah',   n.wilayah || '-'],
        ['Mata Uang', n.kode_mata_uang || '-'],
        ['Skor Risiko', `<span style="color:${color};font-weight:700;">${total}/100 (${tk})</span>`],
    ];

    document.getElementById(containerId).innerHTML = rows.map(([k,v]) => `
        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(59,130,246,0.08);">
            <span style="color:#94a3b8;font-size:0.82rem;">${k}</span>
            <span style="font-weight:600;font-size:0.82rem;">${v}</span>
        </div>
    `).join('');
}

async function bandingkanNegara() {
    const n1 = getNegara('negara1');
    const n2 = getNegara('negara2');

    // Fetch risk data
    try {
        const [r1, r2] = await Promise.all([
            axios.get(`/api/v1/risk?kode_iso2=${n1.kode_iso2}`),
            axios.get(`/api/v1/risk?kode_iso2=${n2.kode_iso2}`)
        ]);

        if (r1.data.data) Object.assign(n1, r1.data);
        if (r2.data.data) Object.assign(n2, r2.data);
    } catch(e) {}

    document.getElementById('empty-compare').style.display = 'none';
    document.getElementById('compare-result').style.display = 'block';

    document.getElementById('nama1').textContent = n1.nama;
    document.getElementById('kode1').textContent = n1.kode_iso2 + ' · ' + n1.wilayah;
    document.getElementById('nama2').textContent = n2.nama;
    document.getElementById('kode2').textContent = n2.kode_iso2 + ' · ' + n2.wilayah;

    renderDetail('detail1', n1);
    renderDetail('detail2', n2);

    // Radar chart
    const s1 = n1.current_risk_score || {};
    const s2 = n2.current_risk_score || {};

    const radarData = {
        labels: ['Risiko Cuaca', 'Risiko Inflasi', 'Risiko Sentimen', 'Risiko Nilai Tukar'],
        datasets: [
            {
                label: n1.nama,
                data: [s1.risiko_cuaca||0, s1.risiko_inflasi||0, s1.risiko_sentimen||0, s1.risiko_nilai_tukar||0],
                backgroundColor: 'rgba(59,130,246,0.15)', borderColor: '#3b82f6', borderWidth: 2, pointBackgroundColor: '#3b82f6'
            },
            {
                label: n2.nama,
                data: [s2.risiko_cuaca||0, s2.risiko_inflasi||0, s2.risiko_sentimen||0, s2.risiko_nilai_tukar||0],
                backgroundColor: 'rgba(139,92,246,0.15)', borderColor: '#8b5cf6', borderWidth: 2, pointBackgroundColor: '#8b5cf6'
            }
        ]
    };

    if (radarChart) { radarChart.data = radarData; radarChart.update(); return; }

    radarChart = new Chart(document.getElementById('chartRadar').getContext('2d'), {
        type: 'radar', data: radarData,
        options: {
            responsive: true, scales: {
                r: { ticks: { color:'#64748b', backdropColor:'transparent', stepSize:20 }, grid: { color:'rgba(59,130,246,0.1)' }, pointLabels: { color:'#94a3b8' }, min:0, max:100 }
            },
            plugins: { legend: { labels: { color:'#94a3b8', font:{ family:'Inter' } } } }
        }
    });
}
</script>
@endpush
