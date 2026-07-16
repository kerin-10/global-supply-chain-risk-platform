@extends('layouts.app')
@section('title', 'Daftar Pantau Negara')
@section('page-title')
    <i class="fas fa-star me-2" style="color:#F59E0B;"></i>Daftar Pantau Negara
@endsection

@section('content')
<div class="row g-3">
    <!-- TAMBAH NEGARA BARU KE WATCHLIST -->
    <div class="col-12">
        <div class="glass-card mb-4" style="padding:1.25rem 1.5rem;">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <h6 class="mb-0 fw-700 text-white"><i class="fas fa-plus me-1 text-primary"></i> Tambahkan Negara Baru ke Daftar Pantau</h6>
                    <p class="text-muted mb-0" style="font-size:0.75rem;">Pilih negara untuk dipantau secara berkala dan dianalisis tingkat risikonya.</p>
                </div>
                <div class="col-md-5">
                    <div class="d-flex gap-2">
                        <select id="negara-baru" class="form-select">
                            <option value="">-- Cari & Pilih Negara --</option>
                            @foreach($negaraList as $n)
                                @if(!$watchlist->contains('id', $n->id))
                                    <option value="{{ $n->id }}">{{ $n->nama }} ({{ $n->kode_iso2 }})</option>
                                @endif
                            @endforeach
                        </select>
                        <button onclick="tambahKeWatchlist()" class="btn btn-primary-glow" style="white-space:nowrap;">
                            <i class="fas fa-plus-circle me-1"></i> Tambah
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4" id="watchlist-grid">
    @forelse($watchlist as $negara)
        @php
            $skor = $negara->currentRiskScore;
            $totalRisiko = $skor ? $skor->total_risiko : 0;
            $tingkat = $skor ? $skor->tingkat_risiko : 'Rendah';
            $color = $tingkat === 'Rendah' ? '#10b981' : ($tingkat === 'Sedang' ? '#f59e0b' : '#ef4444');
            $bg = $tingkat === 'Rendah' ? 'rgba(16,185,129,0.1)' : ($tingkat === 'Sedang' ? 'rgba(245,158,11,0.1)' : 'rgba(239,68,68,0.1)');
        @endphp
        <div class="col-12 col-md-6 col-xl-4 watchlist-item" id="watchlist-card-{{ $negara->id }}">
            <div class="glass-card h-100 d-flex flex-column justify-content-between" style="border-color: rgba(255,255,255,0.06);">
                <div>
                    <!-- Flag & Name Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            @if($negara->bendera_url)
                                <img src="{{ $negara->bendera_url }}" alt="Flag {{ $negara->nama }}" style="width: 48px; height: 32px; border-radius: 4px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
                            @else
                                <div class="bg-secondary d-flex align-items-center justify-content-center" style="width: 48px; height: 32px; border-radius: 4px;">
                                    <i class="fas fa-flag text-white-50" style="font-size: 0.8rem;"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0 fw-700 text-white">{{ $negara->nama }}</h6>
                                <span class="text-muted" style="font-size: 0.68rem;">{{ $negara->kode_iso2 }} &bull; {{ $negara->wilayah }}</span>
                            </div>
                        </div>
                        <span class="badge text-uppercase" style="font-size: 0.65rem; color: {{ $color }}; background: {{ $bg }}; border: 1px solid {{ $color }}40; font-weight:700;">
                            {{ $tingkat }}
                        </span>
                    </div>

                    <!-- Risk Gauge Meter -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:0.75rem;">
                            <span class="text-muted">Skor Risiko Rantai Pasok:</span>
                            <span class="fw-700" style="color: {{ $color }}">{{ $totalRisiko }}/100</span>
                        </div>
                        <div class="risk-meter">
                            <div class="risk-meter-fill {{ $tingkat === 'Rendah' ? 'fill-rendah' : ($tingkat === 'Sedang' ? 'fill-sedang' : 'fill-tinggi') }}" style="width: {{ $totalRisiko }}%;"></div>
                        </div>
                    </div>

                    <!-- Short Weather & Economic Specs -->
                    <div class="row g-2 py-2 mb-3 text-center" style="background:rgba(255,255,255,0.02); border-radius:10px;">
                        <div class="col-4" style="border-right: 1px solid rgba(255,255,255,0.05);">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase;">Cuaca</div>
                            <div class="fw-700 text-info" style="font-size:0.78rem;">
                                {{ $negara->cuaca_suhu !== null ? number_format($negara->cuaca_suhu,0).'°C' : '--' }}
                            </div>
                        </div>
                        <div class="col-4" style="border-right: 1px solid rgba(255,255,255,0.05);">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase;">Inflasi</div>
                            <div class="fw-700 text-warning" style="font-size:0.78rem;">
                                {{ $negara->inflasi !== null ? number_format($negara->inflasi,1).'%' : '--' }}
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase;">Valuta</div>
                            <div class="fw-700 text-success" style="font-size:0.78rem;">
                                {{ $negara->kode_mata_uang }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-2">
                    <a href="{{ route('dashboard.country.detail', $negara->id) }}" class="btn btn-sm btn-primary-glow flex-grow-1 text-center" style="font-size:0.75rem;">
                        <i class="fas fa-chart-line me-1"></i> Dashboard
                    </a>
                    <button onclick="toggleWatchlist({{ $negara->id }})" class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;" title="Hapus dari Daftar Pantau">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted" id="empty-watchlist">
            <i class="fas fa-star fa-3x mb-3" style="color: #1e293b;"></i>
            <div>Daftar pantau kamu masih kosong. Tambahkan negara di atas untuk memulai pemantauan.</div>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    let tsNegaraBaru;
    document.addEventListener("DOMContentLoaded", function() {
        tsNegaraBaru = new TomSelect('#negara-baru', {
            create: false
        });
    });

    async function tambahKeWatchlist() {
        const select = document.getElementById('negara-baru');
        const id = select.value;
        if (!id) {
            alert('Pilih negara terlebih dahulu');
            return;
        }

        try {
            const res = await axios.post('{{ route("dashboard.watchlist.toggle") }}', { negara_id: id });
            if (res.data.status === 'sukses') {
                window.location.reload();
            }
        } catch(e) {
            alert('Gagal menambahkan negara ke daftar pantau: ' + (e.response?.data?.pesan || e.message));
        }
    }

    async function toggleWatchlist(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus negara ini dari daftar pantau?')) return;
        
        try {
            const res = await axios.post('{{ route("dashboard.watchlist.toggle") }}', { negara_id: id });
            if (res.data.status === 'sukses') {
                const card = document.getElementById(`watchlist-card-${id}`);
                if (card) {
                    card.remove();
                }
                
                // Jika grid kosong, tampilkan placeholder
                const items = document.querySelectorAll('.watchlist-item');
                if (items.length === 0) {
                    const grid = document.getElementById('watchlist-grid');
                    grid.innerHTML = `
                        <div class="col-12 text-center py-5 text-muted" id="empty-watchlist">
                            <i class="fas fa-star fa-3x mb-3" style="color: #1e293b;"></i>
                            <div>Daftar pantau kamu masih kosong. Tambahkan negara di atas untuk memulai pemantauan.</div>
                        </div>
                    `;
                }
            }
        } catch(e) {
            alert('Gagal menghapus negara dari daftar pantau: ' + (e.response?.data?.pesan || e.message));
        }
    }
</script>
@endpush
