@extends('layouts.app')
@section('title', 'Berita & Analisis Sentimen')
@section('page-title', '<i class="fas fa-newspaper me-2" style="color:#06b6d4;"></i>Berita & Analisis Sentimen')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="glass-card" style="padding:1rem 1.5rem;">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label style="font-size:0.78rem;color:#94a3b8;" class="mb-1">Pilih Negara</label>
                    <select id="pilihNegara" class="form-select form-select-sm" style="background:rgba(15,23,42,0.8);border:1px solid rgba(6,182,212,0.2);color:#f1f5f9;border-radius:8px;">
                        <option value="">Semua Negara</option>
                        @foreach($negaraList as $n)
                            <option value="{{ $n->kode_iso2 }}">{{ $n->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button onclick="muatBerita()" class="btn btn-primary-glow w-100" style="font-size:0.82rem;background:linear-gradient(135deg,#06b6d4,#3b82f6);">
                        <i class="fas fa-sync-alt me-1"></i>Muat & Analisis Berita
                    </button>
                </div>
                <div class="col-md-5 text-end">
                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                        <span style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25);color:#10b981;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.72rem;"><i class="fas fa-smile me-1"></i>Positif</span>
                        <span style="background:rgba(100,116,139,0.12);border:1px solid rgba(100,116,139,0.25);color:#94a3b8;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.72rem;"><i class="fas fa-meh me-1"></i>Netral</span>
                        <span style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#ef4444;padding:0.3rem 0.75rem;border-radius:20px;font-size:0.72rem;"><i class="fas fa-frown me-1"></i>Negatif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PANEL SENTIMEN AGREGAT -->
<div class="row g-3 mb-3" id="sentiment-panel" style="display:none !important;">
    <div class="col-md-4">
        <div class="stat-card" style="border-color:rgba(16,185,129,0.3);">
            <div class="stat-icon" style="background:rgba(16,185,129,0.15);"><i class="fas fa-smile" style="color:#10b981;"></i></div>
            <div><div class="stat-val" style="color:#10b981;" id="pct-positif">0%</div><div class="stat-label">Sentimen Positif</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="border-color:rgba(100,116,139,0.3);">
            <div class="stat-icon" style="background:rgba(100,116,139,0.15);"><i class="fas fa-meh" style="color:#94a3b8;"></i></div>
            <div><div class="stat-val" style="color:#94a3b8;" id="pct-netral">0%</div><div class="stat-label">Sentimen Netral</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="border-color:rgba(239,68,68,0.3);">
            <div class="stat-icon" style="background:rgba(239,68,68,0.15);"><i class="fas fa-frown" style="color:#ef4444;"></i></div>
            <div><div class="stat-val" style="color:#ef4444;" id="pct-negatif">0%</div><div class="stat-label">Sentimen Negatif</div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- DAFTAR BERITA -->
    <div class="col-12 col-xl-8">
        <div class="glass-card" style="padding:0;overflow:hidden;">
            <div class="px-4 py-3" style="border-bottom:1px solid rgba(6,182,212,0.1);">
                <h6 class="mb-0 fw-700"><i class="fas fa-rss me-2" style="color:#06b6d4;"></i>Berita Terkini</h6>
            </div>
            <div id="news-container" style="max-height:600px;overflow-y:auto;">
                @forelse($beritaTerbaru as $berita)
                @php
                    $s = $berita->sentimen;
                    $c = $s === 'Positif' ? '#10b981' : ($s === 'Negatif' ? '#ef4444' : '#94a3b8');
                    $icon = $s === 'Positif' ? 'fa-smile' : ($s === 'Negatif' ? 'fa-frown' : 'fa-meh');
                @endphp
                <div class="px-4 py-3" style="border-bottom:1px solid rgba(6,182,212,0.06);">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div style="font-weight:600;font-size:0.87rem;line-height:1.4;flex:1;margin-right:1rem;">{{ $berita->judul }}</div>
                        <span style="background:{{ $s === 'Positif' ? 'rgba(16,185,129,0.12)' : ($s === 'Negatif' ? 'rgba(239,68,68,0.12)' : 'rgba(100,116,139,0.12)') }};color:{{ $c }};border:1px solid {{ $c }}44;padding:0.2rem 0.5rem;border-radius:12px;font-size:0.65rem;white-space:nowrap;flex-shrink:0;">
                            <i class="fas {{ $icon }} me-1"></i>{{ $s }}
                        </span>
                    </div>
                    <p style="font-size:0.78rem;color:#94a3b8;margin-bottom:0.5rem;line-height:1.5;">{{ Str::limit($berita->deskripsi, 130) }}</p>
                    <div class="d-flex align-items-center gap-3" style="font-size:0.7rem;color:#475569;">
                        <span><i class="fas fa-globe me-1"></i>{{ $berita->country->nama ?? '-' }}</span>
                        <span><i class="fas fa-building me-1"></i>{{ $berita->sumber }}</span>
                        <span><i class="fas fa-clock me-1"></i>{{ $berita->diterbitkan_pada->diffForHumans() }}</span>
                        <span>+{{ $berita->skor_sentimen_positif }}/<span style="color:#ef4444;">-{{ $berita->skor_sentimen_negatif }}</span></span>
                        <a href="{{ $berita->tautan_url }}" target="_blank" style="color:#3b82f6;margin-left:auto;"><i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5" style="color:#475569;">
                    <i class="fas fa-newspaper fa-2x mb-2"></i><br>
                    Pilih negara dan klik "Muat & Analisis Berita" untuk memulai.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- GRAFIK SENTIMEN -->
    <div class="col-12 col-xl-4">
        <div class="glass-card">
            <h6 class="fw-700 mb-3"><i class="fas fa-chart-pie me-2" style="color:#06b6d4;"></i>Distribusi Sentimen</h6>
            <canvas id="chartSentimen" height="220"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctxSentimen = document.getElementById('chartSentimen').getContext('2d');
const chartSentimen = new Chart(ctxSentimen, {
    type: 'doughnut',
    data: {
        labels: ['Positif', 'Netral', 'Negatif'],
        datasets: [{ data: [0,0,0], backgroundColor: ['rgba(16,185,129,0.7)','rgba(100,116,139,0.7)','rgba(239,68,68,0.7)'], borderWidth: 0 }]
    },
    options: {
        responsive: true, cutout: '68%',
        plugins: {
            legend: { labels: { color: '#94a3b8', font: { family:'Inter', size:12 } } },
            tooltip: { backgroundColor: 'rgba(10,14,26,0.9)', titleColor:'#f1f5f9', bodyColor:'#94a3b8' }
        }
    }
});

async function muatBerita() {
    const kode = document.getElementById('pilihNegara').value;
    if (!kode) { alert('Pilih negara terlebih dahulu.'); return; }

    const container = document.getElementById('news-container');
    container.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x" style="color:#06b6d4;"></i><br><small class="text-muted mt-2">Menganalisis sentimen berita…</small></div>';

    try {
        const res = await axios.get(`/api/v1/news?kode_iso2=${kode}&sync=true`);
        const berita = res.data.data || [];

        let pos=0, neg=0, net=0;
        let html = '';

        berita.forEach(b => {
            const s = b.sentimen;
            const c = s==='Positif'?'#10b981':(s==='Negatif'?'#ef4444':'#94a3b8');
            const ico = s==='Positif'?'fa-smile':(s==='Negatif'?'fa-frown':'fa-meh');
            const bgBadge = s==='Positif'?'rgba(16,185,129,0.12)':(s==='Negatif'?'rgba(239,68,68,0.12)':'rgba(100,116,139,0.12)');
            if(s==='Positif') pos++; else if(s==='Negatif') neg++; else net++;

            html += `
            <div class="px-4 py-3" style="border-bottom:1px solid rgba(6,182,212,0.06);">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div style="font-weight:600;font-size:0.87rem;line-height:1.4;flex:1;margin-right:1rem;">${b.judul}</div>
                    <span style="background:${bgBadge};color:${c};border:1px solid ${c}44;padding:0.2rem 0.5rem;border-radius:12px;font-size:0.65rem;white-space:nowrap;">
                        <i class="fas ${ico} me-1"></i>${s}
                    </span>
                </div>
                <p style="font-size:0.78rem;color:#94a3b8;margin-bottom:0.5rem;">${b.deskripsi?.substring(0,130) || '-'}…</p>
                <div style="font-size:0.7rem;color:#475569;" class="d-flex gap-3 flex-wrap">
                    <span><i class="fas fa-building me-1"></i>${b.sumber || '-'}</span>
                    <span style="color:#10b981;">+${b.skor_sentimen_positif}</span>/<span style="color:#ef4444;">-${b.skor_sentimen_negatif}</span>
                    <a href="${b.tautan_url}" target="_blank" style="color:#3b82f6;margin-left:auto;"><i class="fas fa-external-link-alt"></i></a>
                </div>
            </div>`;
        });

        container.innerHTML = html || '<div class="text-center py-4" style="color:#475569;">Tidak ada berita tersedia.</div>';

        const total = pos + neg + net;
        if (total > 0) {
            document.getElementById('pct-positif').textContent = Math.round(pos/total*100) + '%';
            document.getElementById('pct-negatif').textContent = Math.round(neg/total*100) + '%';
            document.getElementById('pct-netral').textContent  = Math.round(net/total*100) + '%';
            document.getElementById('sentiment-panel').style.removeProperty('display');
            chartSentimen.data.datasets[0].data = [pos, net, neg];
            chartSentimen.update();
        }
    } catch(e) {
        container.innerHTML = `<div class="text-center py-5" style="color:#ef4444;"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>${e.message}</div>`;
    }
}
</script>
@endpush
