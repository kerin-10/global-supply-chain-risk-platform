@extends('layouts.app')
@section('title', 'Buat Artikel Baru')
@section('page-title', '<i class="fas fa-plus-circle me-2" style="color:var(--accent-purple);"></i>Buat Artikel Baru')

@section('content')
<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 m-0"><i class="fas fa-edit me-2" style="color:var(--accent-purple);"></i>Tulis Artikel Insight Baru</h6>
        <a href="{{ route('admin.artikel.daftar') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.75rem;">
            <i class="fas fa-arrow-left me-1"></i>Batal & Kembali
        </a>
    </div>

    <form action="{{ route('admin.artikel.simpan') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">Judul Artikel</label>
            <input type="text" name="judul" class="form-control" placeholder="Masukkan judul analisis yang menarik..." required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px;">
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">Ringkasan Singkat</label>
            <textarea name="ringkasan" class="form-control" rows="3" placeholder="Tulis ringkasan singkat artikel dalam 1-2 kalimat..." required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px; font-size:0.85rem;"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">Isi Konten Artikel (Mendukung HTML)</label>
            <textarea name="konten" class="form-control" rows="10" placeholder="Tulis isi analisis lengkap di sini. Gunakan tag HTML seperti <p>, <ul>, <li>, <strong> untuk pemformatan..." required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px; font-size:0.88rem; font-family:monospace;"></textarea>
        </div>

        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-6">
                <label class="form-label" style="font-weight:600; color:var(--text-primary);">Status Publikasi</label>
                <select name="status" class="form-select" required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px; font-size:0.85rem;">
                    <option value="Draft">Draft (Simpan sebagai draf)</option>
                    <option value="Published">Published (Rilis langsung ke publik)</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary-glow w-100" style="background:linear-gradient(135deg, var(--accent-purple), #8b5cf6);">
            <i class="fas fa-save me-1"></i>Simpan & Publikasikan Artikel
        </button>
    </form>
</div>
@endsection
