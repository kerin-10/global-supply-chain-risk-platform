@extends('layouts.app')
@section('title', 'Buat Artikel Baru')
@section('page-title')
    <i class="fas fa-plus-circle me-2" style="color:var(--accent-purple);"></i> Buat Artikel Baru
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
        <h6 class="fw-700 m-0"><i class="fas fa-edit me-2" style="color:var(--accent-purple);"></i>Tulis Artikel Insight Baru</h6>
        <a href="{{ route('admin.artikel.daftar') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:0.75rem;">
            <i class="fas fa-arrow-left me-1"></i>Batal & Kembali
        </a>
    </div>

    <form id="artikelForm" action="{{ route('admin.artikel.simpan') }}" method="POST">
        @csrf
        <div class="row g-3 mb-3">
            <div class="col-md-8">
                <label class="form-label" style="font-weight:600; color:var(--text-primary);">Judul Artikel</label>
                <input type="text" name="judul" class="form-control" placeholder="Masukkan judul analisis yang menarik..." required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px;">
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-weight:600; color:var(--text-primary);">Kategori</label>
                <input type="text" name="kategori" class="form-control" placeholder="Misal: Geopolitik, Cuaca..." style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px;">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">URL Gambar Sampul (Opsional)</label>
            <input type="url" name="gambar_url" class="form-control" placeholder="https://example.com/image.jpg" style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px; font-size:0.85rem;">
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">Ringkasan Singkat</label>
            <textarea name="ringkasan" class="form-control" rows="3" placeholder="Tulis ringkasan singkat artikel dalam 1-2 kalimat..." required style="background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-glass); border-radius:10px; font-size:0.85rem;"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-weight:600; color:var(--text-primary);">Isi Konten Artikel</label>
            <input type="hidden" name="konten" id="kontenInput">
            <div id="editor-container"></div>
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

        <button type="submit" class="btn btn-primary-glow w-100 mt-2" style="background:linear-gradient(135deg, var(--accent-purple), #8b5cf6);">
            <i class="fas fa-save me-1"></i>Simpan & Publikasikan Artikel
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
    var form = document.querySelector('#artikelForm');
    form.onsubmit = function(e) {
        var kontenInput = document.querySelector('#kontenInput');
        var html = quill.root.innerHTML;
        if (html === '<p><br></p>') {
            html = ''; // Biarkan kosong agar backend memunculkan error validasi
        }
        kontenInput.value = html;
    };
</script>
@endpush
