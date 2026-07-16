@extends('layouts.app')
@section('title', 'Leksikon Sentimen')
@section('page-title', '<i class="fas fa-spell-check me-2" style="color:var(--accent-yellow);"></i>Leksikon Sentimen')

@section('content')
<div class="row g-3 mb-3 text-end">
    <div class="col-12">
        <a href="{{ route('admin.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.75rem;">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard Admin
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- KATA POSITIF -->
    <div class="col-12 col-md-6">
        <div class="glass-card mb-3">
            <h6 class="fw-700 mb-3" style="color:var(--accent-green);"><i class="fas fa-plus-circle me-2"></i>Tambah Kata Positif</h6>
            <form action="{{ route('admin.leksikon.positif.tambah') }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="text" name="kata" placeholder="Ketik kata positif (cth: profit)..." required class="form-control form-control-sm" style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:8px;">
                <button type="submit" class="btn btn-sm btn-primary-glow" style="background:var(--accent-green); font-size:0.78rem;">
                    Tambah
                </button>
            </form>
        </div>

        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-glass);">
                <h6 class="fw-700 m-0" style="color:var(--accent-green);"><i class="fas fa-smile me-2"></i>Kamus Kata Positif ({{ $kataPositif->count() }})</h6>
            </div>
            <div style="max-height:350px; overflow-y:auto; padding:1rem;">
                <div class="d-flex flex-wrap gap-2">
                    @forelse($kataPositif as $k)
                    <span class="d-inline-flex align-items-center gap-2 px-2.5 py-1.5" style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); border-radius:20px; font-size:0.78rem; font-weight:600; color:var(--accent-green);">
                        {{ $k->kata }}
                        <form action="{{ route('admin.leksikon.positif.hapus', $k->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:transparent; border:none; color:var(--accent-red); font-size:0.75rem; padding:0; line-height:1; cursor:pointer;">
                                &times;
                            </button>
                        </form>
                    </span>
                    @empty
                    <div class="text-center w-100 py-3 text-muted" style="color:var(--text-muted);">Belum ada kata positif.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- KATA NEGATIF -->
    <div class="col-12 col-md-6">
        <div class="glass-card mb-3">
            <h6 class="fw-700 mb-3" style="color:var(--accent-red);"><i class="fas fa-minus-circle me-2"></i>Tambah Kata Negatif</h6>
            <form action="{{ route('admin.leksikon.negatif.tambah') }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="text" name="kata" placeholder="Ketik kata negatif (cth: delay)..." required class="form-control form-control-sm" style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:8px;">
                <button type="submit" class="btn btn-sm btn-primary-glow" style="background:var(--accent-red); font-size:0.78rem;">
                    Tambah
                </button>
            </form>
        </div>

        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div class="px-4 py-3" style="border-bottom:1px solid var(--border-glass);">
                <h6 class="fw-700 m-0" style="color:var(--accent-red);"><i class="fas fa-frown me-2"></i>Kamus Kata Negatif ({{ $kataNegatif->count() }})</h6>
            </div>
            <div style="max-height:350px; overflow-y:auto; padding:1rem;">
                <div class="d-flex flex-wrap gap-2">
                    @forelse($kataNegatif as $k)
                    <span class="d-inline-flex align-items-center gap-2 px-2.5 py-1.5" style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:20px; font-size:0.78rem; font-weight:600; color:var(--accent-red);">
                        {{ $k->kata }}
                        <form action="{{ route('admin.leksikon.negatif.hapus', $k->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:transparent; border:none; color:var(--accent-red); font-size:0.75rem; padding:0; line-height:1; cursor:pointer;">
                                &times;
                            </button>
                        </form>
                    </span>
                    @empty
                    <div class="text-center w-100 py-3 text-muted" style="color:var(--text-muted);">Belum ada kata negatif.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
