@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title', '<i class="fas fa-users-cog me-2" style="color:var(--accent-blue);"></i>Manajemen Pengguna')

@section('content')
<div class="glass-card mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 m-0"><i class="fas fa-list me-2" style="color:var(--accent-blue);"></i>Daftar Pengguna Platform</h6>
        <a href="{{ route('admin.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.75rem;">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
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
                            <!-- UBAH PERAN FORM -->
                            <form action="{{ route('admin.pengguna.ubah-peran', $pengguna->id) }}" method="POST" class="d-inline-flex gap-1 align-items-center">
                                @csrf
                                @method('PATCH')
                                <select name="peran" onchange="this.form.submit()" class="form-select form-select-sm" style="font-size:0.75rem; border-radius:6px; width:100px; background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass);">
                                    <option value="user" {{ $pengguna->peran === 'user' ? 'selected' : '' }}>User</option>
                                    <option value="admin" {{ $pengguna->peran === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </form>

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
@endsection
