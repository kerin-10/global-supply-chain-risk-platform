@extends('layouts.app')
@section('title', 'Manajemen Pelabuhan')
@section('page-title', '<i class="fas fa-anchor me-2" style="color:var(--accent-green);"></i>Manajemen Pelabuhan')

@section('content')
<div class="row g-3">
    <!-- ADD PORT FORM -->
    <div class="col-12 col-xl-4">
        <div class="glass-card">
            <h6 class="fw-700 mb-3"><i class="fas fa-plus-circle me-2" style="color:var(--accent-green);"></i>Tambah Pelabuhan Baru</h6>
            <form action="{{ route('admin.pelabuhan.simpan') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label class="form-label" style="font-size:0.75rem; font-weight:600; color:var(--text-primary);">Nama Pelabuhan</label>
                    <input type="text" name="nama" class="form-control form-control-sm" placeholder="Contoh: Port of Tanjung Priok" required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass);">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:0.75rem; font-weight:600; color:var(--text-primary);">Pilih Negara</label>
                    <select name="negara_id" class="form-select form-select-sm" required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass);">
                        <option value="">-- Pilih Negara --</option>
                        @foreach($negaraList as $neg)
                        <option value="{{ $neg->id }}">{{ $neg->nama }} ({{ $neg->kode_iso2 }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600; color:var(--text-primary);">Lintang (Latitude)</label>
                        <input type="number" step="any" name="lintang" class="form-control form-control-sm" placeholder="-6.1" required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass);">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600; color:var(--text-primary);">Bujur (Longitude)</label>
                        <input type="number" step="any" name="bujur" class="form-control form-control-sm" placeholder="106.8" required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass);">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600; color:var(--text-primary);">Kode Pelabuhan</label>
                        <input type="text" name="kode_pelabuhan" class="form-control form-control-sm" placeholder="IDTPP" style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass);">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:0.75rem; font-weight:600; color:var(--text-primary);">Nomor WPI</label>
                        <input type="text" name="nomor_wpi" class="form-control form-control-sm" placeholder="WPI-0012" style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass);">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary-glow btn-sm w-100" style="background:linear-gradient(135deg, var(--accent-green), #10b981);">
                    <i class="fas fa-save me-1"></i>Simpan Pelabuhan
                </button>
            </form>
        </div>
    </div>

    <!-- PORT LIST TABLE -->
    <div class="col-12 col-xl-8">
        <div class="glass-card" style="padding:0; overflow:hidden;">
            <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-bottom:1px solid var(--border-glass);">
                <h6 class="fw-700 m-0"><i class="fas fa-list me-2" style="color:var(--accent-green);"></i>Daftar Pelabuhan Aktif</h6>
                <a href="{{ route('admin.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.75rem;">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div style="max-height:480px; overflow-y:auto;">
                <table class="table table-glass mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Negara</th>
                            <th>WPI ID</th>
                            <th>Koordinat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daftar as $port)
                        <tr>
                            <td style="font-weight:600; color:var(--text-primary);">{{ $port->nama }}</td>
                            <td style="color:var(--text-primary);">{{ $port->country->nama ?? $port->kode_negara }}</td>
                            <td style="color:var(--text-muted); font-size:0.78rem;">{{ $port->nomor_wpi ?? '-' }}</td>
                            <td style="color:var(--text-muted); font-size:0.75rem;">
                                {{ number_format($port->lintang, 4) }}, {{ number_format($port->bujur, 4) }}
                            </td>
                            <td>
                                <form action="{{ route('admin.pelabuhan.hapus', $port->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelabuhan ini?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:6px; font-size:0.75rem; padding:0.25rem 0.5rem;" title="Hapus Pelabuhan">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted" style="color:var(--text-muted);">Belum ada pelabuhan tersimpan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
