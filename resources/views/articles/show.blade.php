@extends('layouts.app')
@section('title', $artikel->judul)
@section('page-title')
    <i class="fas fa-book-reader me-2" style="color:#06B6D4;"></i>Baca Artikel
@endsection

@push('styles')
<style>
.article-header-bg {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 2rem;
    height: 350px;
}
.article-header-bg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.article-header-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(10, 14, 30, 1) 0%, rgba(10, 14, 30, 0.4) 50%, rgba(10, 14, 30, 0.1) 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 2.5rem;
}
.article-detail-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #f1f5f9;
    margin-bottom: 1rem;
    line-height: 1.3;
    text-shadow: 0 4px 12px rgba(0,0,0,0.5);
}
.article-detail-meta {
    display: flex;
    gap: 1.5rem;
    font-size: 0.9rem;
    color: #cbd5e1;
}
.article-detail-category {
    background: rgba(59, 130, 246, 0.8);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
    backdrop-filter: blur(4px);
    margin-bottom: 1rem;
    align-self: flex-start;
}
.article-body {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #cbd5e1;
}
.article-body p {
    margin-bottom: 1.5rem;
}
.article-body h2, .article-body h3, .article-body h4, .article-body h5 {
    color: #f1f5f9;
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
}
.article-body ul, .article-body ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}
.article-body li {
    margin-bottom: 0.5rem;
}
.article-body strong {
    color: #f8fafc;
}

/* Sidebar terkait */
.related-card {
    background: rgba(16, 22, 40, 0.5);
    border: 1px solid rgba(59, 130, 246, 0.1);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
    display: flex;
    margin-bottom: 1rem;
}
.related-card:hover {
    background: rgba(16, 22, 40, 0.8);
    border-color: rgba(59, 130, 246, 0.3);
}
.related-img {
    width: 90px;
    height: 90px;
    object-fit: cover;
}
.related-content {
    padding: 0.75rem 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.related-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #e2e8f0;
    margin-bottom: 0.25rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.related-date {
    font-size: 0.7rem;
    color: #64748b;
}
</style>
@endpush

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="glass-card" style="padding: 1.5rem;">
            
            <a href="{{ route('articles.index') }}" class="btn btn-sm mb-3" style="color: #94a3b8; background: rgba(255,255,255,0.05); border-radius: 8px;">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Artikel
            </a>

            <div class="article-header-bg">
                <img src="{{ $artikel->gambar_url ?? 'https://images.unsplash.com/photo-1586528116311-ad8ed7c50a63?q=80&w=800&auto=format&fit=crop' }}" alt="{{ $artikel->judul }}">
                <div class="article-header-overlay">
                    <span class="article-detail-category">
                        <i class="fas fa-tag me-1"></i>{{ $artikel->kategori ?? 'Umum' }}
                    </span>
                    <h1 class="article-detail-title">{{ $artikel->judul }}</h1>
                    <div class="article-detail-meta">
                        <span><i class="fas fa-user-circle me-1" style="color: #3b82f6;"></i>Oleh: <strong>{{ $artikel->author->nama ?? 'Admin' }}</strong></span>
                        <span><i class="fas fa-calendar-alt me-1" style="color: #06b6d4;"></i>Dipublikasikan: <strong>{{ optional($artikel->diterbitkan_pada)->format('d M Y') }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="article-body px-2">
                {!! $artikel->konten !!}
            </div>
            
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="glass-card sticky-top" style="top: 80px; padding: 1.5rem;">
            <h5 class="fw-700 mb-4" style="border-bottom: 1px solid rgba(59, 130, 246, 0.2); padding-bottom: 0.75rem;">
                <i class="fas fa-fire me-2" style="color: #f59e0b;"></i>Artikel Terkait
            </h5>
            
            @forelse($artikelLainnya as $lain)
            <a href="{{ route('articles.show', $lain->id) }}" style="text-decoration: none;">
                <div class="related-card">
                    <img src="{{ $lain->gambar_url ?? 'https://images.unsplash.com/photo-1586528116311-ad8ed7c50a63?q=80&w=800&auto=format&fit=crop' }}" class="related-img" alt="{{ $lain->judul }}">
                    <div class="related-content">
                        <div class="related-title">{{ $lain->judul }}</div>
                        <div class="related-date"><i class="fas fa-clock me-1"></i>{{ optional($lain->diterbitkan_pada)->format('d M Y') }}</div>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-4 text-muted">
                <i class="fas fa-folder-open fa-2x mb-2" style="opacity: 0.5;"></i>
                <p class="mb-0" style="font-size: 0.85rem;">Tidak ada artikel lain.</p>
            </div>
            @endforelse
            
            <div class="mt-4 pt-4" style="border-top: 1px solid rgba(59, 130, 246, 0.2);">
                <div class="p-3" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1)); border-radius: 12px; border: 1px solid rgba(59,130,246,0.2);">
                    <h6 class="fw-700" style="color: #f1f5f9;"><i class="fas fa-envelope-open-text me-2" style="color: #3b82f6;"></i>Berlangganan Newsletter</h6>
                    <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 1rem;">Dapatkan insight eksklusif tentang risiko rantai pasok global langsung ke inbox Anda.</p>
                    <div class="input-group input-group-sm">
                        <input type="email" class="form-control" placeholder="Email Anda" style="background: rgba(10, 14, 30, 0.6); border: 1px solid rgba(59, 130, 246, 0.3); color: #f1f5f9;">
                        <button class="btn btn-primary" type="button" style="background: #3b82f6; border: none;">Kirim</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection