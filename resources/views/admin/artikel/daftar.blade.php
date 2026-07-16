@extends('layouts.app')
@section('title', 'Manajemen Artikel')
@section('page-title', '<i class="fas fa-edit me-2" style="color:var(--accent-purple);"></i>Manajemen Artikel')

@section('content')
<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 m-0"><i class="fas fa-list me-2" style="color:var(--accent-purple);"></i>Daftar Artikel Insight Logistik</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.artikel.buat') }}" class="btn btn-sm btn-primary-glow" style="background:linear-gradient(135deg, var(--accent-purple), #8b5cf6);">
                <i class="fas fa-plus me-1"></i>Buat Artikel Baru
            </a>
            <a href="{{ route('admin.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.75rem;">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-glass mb-0">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Penulis</th>
                    <th>Status</th>
                    <th>Tanggal Rilis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $artikel)
                @php
                    $isPublished = $artikel->status === 'Published';
                    $badge = $isPublished ? 'bg-success' : 'bg-secondary';
                @endphp
                <tr>
                    <td style="font-weight:600; color:var(--text-primary);">{{ $artikel->judul }}</td>
                    <td style="color:var(--text-primary);">{{ $artikel->kategori ?? 'Umum' }}</td>
                    <td style="color:var(--text-muted);">{{ $artikel->author->nama ?? 'Admin' }}</td>
                    <td>
                        <span class="badge {{ $badge }} rounded-pill" style="font-size:0.7rem; padding:0.25rem 0.5rem;">
                            {{ $artikel->status }}
                        </span>
                    </td>
                    <td style="color:var(--text-muted); font-size:0.78rem;">
                        {{ $artikel->diterbitkan_pada ? $artikel->diterbitkan_pada->format('d M Y H:i') : '-' }}
                    </td>
                    <td>
                        <form action="{{ route('admin.artikel.hapus', $artikel->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:6px; font-size:0.75rem; padding:0.25rem 0.5rem;" title="Hapus Artikel">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted" style="color:var(--text-muted);">Belum ada artikel ditulis.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
