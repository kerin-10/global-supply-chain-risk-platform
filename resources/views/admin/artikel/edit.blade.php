@extends('layouts.app')
@section('title', 'Edit Artikel')
@section('page-title')
    <i class="fas fa-edit me-2" style="color:var(--accent-purple);"></i> Edit Artikel
@endsection

@push('styles')
<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-container {
        font-family: 'Inter', sans-serif;
        font-size: 0.88rem;
        background: var(--bg-card);
        color: var(--text-primary);
        border: 1px solid var(--border-glass) !important;
        border-radius: 0 0 10px 10px;
        min-height: 250px;
    }
    .ql-toolbar {
        background: rgba(139,92,246,0.05);
        border: 1px solid var(--border-glass) !important;
        border-radius: 10px 10px 0 0;
    }
</style>
@endpush

@section('content')
<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 m-0"><i class="fas fa-edit me-2" style="color:var(--accent-purple);"></i>Edit Artikel Insight</h6>
        <a href="{{ route('admin.artikel.daftar') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.75rem;">
            <i class="fas fa-arrow-left me-1"></i>Batal & Kembali
        </a>
    </div>

    <form action="{{ route('admin.artikel.update', $artikel->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3 mb-3">
            <div class="col-md-8">
                <label class="form-label" style="font-weight:600; color:var(--text-primary);">Judul Artikel</label>
                <input type="text" name="judul" class="form-control" value="{{ $artikel->judul }}" required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px;">
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-weight:600; color:var(--text-primary);">Kategori</label>
                <input type="text" name="kategori" class="form-control" value="{{ $artikel->kategori }}" style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px;">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">URL Gambar Sampul (Opsional)</label>
            <input type="url" name="gambar_url" class="form-control" value="{{ $artikel->gambar_url }}" style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px; font-size:0.85rem;">
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">Ringkasan Singkat</label>
            <textarea name="ringkasan" class="form-control" rows="3" required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px; font-size:0.85rem;">{{ $artikel->ringkasan }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">Isi Konten Artikel</label>
            <input type="hidden" name="konten" id="kontenInput" required>
            <div id="editor-container">{!! $artikel->konten !!}</div>
        </div>

        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-6">
                <label class="form-label" style="font-weight:600; color:var(--text-primary);">Status Publikasi</label>
                <select name="status" class="form-select" required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px; font-size:0.85rem;">
                    <option value="Draft" {{ $artikel->status === 'Draft' ? 'selected' : '' }}>Draft (Simpan sebagai draf)</option>
                    <option value="Published" {{ $artikel->status === 'Published' ? 'selected' : '' }}>Published (Rilis langsung ke publik)</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary-glow w-100 mt-2" style="background:linear-gradient(135deg, var(--accent-purple), #8b5cf6);">
            <i class="fas fa-save me-1"></i>Simpan Perubahan
        </button>
    </form>
</div>
@endsection

@push('scripts')
<!-- Quill JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Tulis isi analisis lengkap di sini...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // Populate hidden input before submit
    var form = document.querySelector('form');
    form.onsubmit = function() {
        var kontenInput = document.querySelector('#kontenInput');
        kontenInput.value = quill.root.innerHTML;
    };
</script>
@endpush
