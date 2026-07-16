@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title')
    <i class="fas fa-users-cog me-2" style="color:var(--accent-blue);"></i> Manajemen Pengguna
@endsection

@section('content')
<div class="glass-card mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 m-0"><i class="fas fa-list me-2" style="color:var(--accent-blue);"></i>Daftar Pengguna Platform</h6>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary-glow" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna" style="background:linear-gradient(135deg, var(--accent-blue), #60a5fa);">
                <i class="fas fa-plus me-1"></i>Tambah Pengguna
            </button>
            <a href="{{ route('admin.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.75rem;">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-glass mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Departemen</th>
                    <th>Peran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($daftar as $pengguna)
                <tr>
                    <td style="font-weight:600; color:var(--text-primary);">{{ $pengguna->nama }}</td>
                    <td style="color:var(--text-muted);">{{ $pengguna->email }}</td>
                    <td style="color:var(--text-muted);">{{ $pengguna->profile->departemen ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $pengguna->isAdmin() ? 'bg-danger' : 'bg-primary' }} rounded-pill" style="font-size:0.7rem; padding:0.25rem 0.5rem;">
                            {{ ucfirst($pengguna->peran) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <!-- TOMBOL EDIT -->
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditPengguna{{ $pengguna->id }}" style="border-radius:6px; font-size:0.75rem; padding:0.25rem 0.5rem;" title="Edit Pengguna">
                                <i class="fas fa-edit"></i>
                            </button>

                            <!-- HAPUS -->
                            @if($pengguna->id !== auth()->id())
                            <form action="{{ route('admin.pengguna.hapus', $pengguna->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:6px; font-size:0.75rem; padding:0.25rem 0.5rem;" title="Hapus Pengguna">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH PENGGUNA -->
<div class="modal fade" id="modalTambahPengguna" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.pengguna.simpan') }}" method="POST" class="modal-content glass-card p-0">
            @csrf
            <div class="modal-header border-bottom border-secondary border-opacity-10 py-3">
                <h6 class="modal-title fw-700 text-primary m-0"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control form-control-sm" required style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Email</label>
                    <input type="email" name="email" class="form-control form-control-sm" required style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Kata Sandi</label>
                    <input type="password" name="kata_sandi" class="form-control form-control-sm" required style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Departemen</label>
                    <input type="text" name="departemen" class="form-control form-control-sm" style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Peran</label>
                    <select name="peran" class="form-select form-select-sm" required style="border-radius:8px;">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-10 py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-primary-glow" style="background:linear-gradient(135deg, var(--accent-blue), #60a5fa);"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

@foreach($daftar as $pengguna)
<!-- MODAL EDIT PENGGUNA -->
<div class="modal fade" id="modalEditPengguna{{ $pengguna->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.pengguna.update', $pengguna->id) }}" method="POST" class="modal-content glass-card p-0">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom border-secondary border-opacity-10 py-3">
                <h6 class="modal-title fw-700 text-primary m-0"><i class="fas fa-user-edit me-2"></i>Edit Pengguna: {{ $pengguna->nama }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control form-control-sm" value="{{ $pengguna->nama }}" required style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Email</label>
                    <input type="email" name="email" class="form-control form-control-sm" value="{{ $pengguna->email }}" required style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="kata_sandi" class="form-control form-control-sm" style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Departemen</label>
                    <input type="text" name="departemen" class="form-control form-control-sm" value="{{ $pengguna->profile->departemen ?? '' }}" style="border-radius:8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:0.8rem; font-weight:600;">Peran</label>
                    <select name="peran" class="form-select form-select-sm" required style="border-radius:8px;">
                        <option value="user" {{ $pengguna->peran === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ $pengguna->peran === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-10 py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-primary-glow" style="background:linear-gradient(135deg, var(--accent-blue), #60a5fa);"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
