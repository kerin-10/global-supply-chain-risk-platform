@extends('layouts.app')
@section('title', 'Artikel Analisis')
@section('page-title')
    <i class="fas fa-newspaper me-2" style="color:#3B82F6;"></i>Artikel Analisis
@endsection

@push('styles')
<style>
.article-card {
    background: rgba(16, 22, 40, 0.7);
    border: 1px solid rgba(59, 130, 246, 0.15);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.article-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
    border-color: rgba(59, 130, 246, 0.4);
}
.article-img-wrapper {
    position: relative;
    height: 200px;
    overflow: hidden;
}
.article-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.article-card:hover .article-img {
    transform: scale(1.05);
}
.article-category {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(10, 14, 30, 0.8);
    backdrop-filter: blur(4px);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.3);
}
.article-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.article-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 0.75rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.article-summary {
    font-size: 0.85rem;
    color: #94a3b8;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex: 1;
}
.article-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid rgba(59, 130, 246, 0.1);
    font-size: 0.8rem;
    color: #64748b;
}
.read-more-btn {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s;
    text-decoration: none;
    text-align: center;
    display: block;
    margin-top: 1rem;
}
.read-more-btn:hover {
    background: linear-gradient(135deg, #3b82f6, #06b6d4);
    color: white;
}
</style>
@endpush

@section('content')
<!-- Search & Filter Bar -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="glass-card" style="padding:1.25rem 1.5rem;">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label style="font-size:0.78rem;color:var(--text-muted);" class="mb-1">Cari Artikel</label>
                    <input type="text" id="cariArtikel" class="form-control form-control-sm" placeholder="Ketik judul, ringkasan, atau isi..." style="border-radius:8px;">
                </div>
                <div class="col-md-4">
                    <label style="font-size:0.78rem;color:var(--text-muted);" class="mb-1">Kategori</label>
                    <select id="filterKategori" class="form-select form-select-sm" style="border-radius:8px;">
                        <option value="">Semua Kategori</option>
                        <option value="Geopolitik">Geopolitik</option>
                        <option value="Cuaca & Iklim">Cuaca & Iklim</option>
                        <option value="Teknologi">Teknologi</option>
                        <option value="Ekonomi">Ekonomi</option>
                        <option value="Pasar Global">Pasar Global</option>
                        <option value="Keamanan">Keamanan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button onclick="muatArtikel(1)" class="btn btn-primary-glow w-100" style="font-size:0.82rem;">
                        <i class="fas fa-search me-1"></i>Cari Artikel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Articles Container -->
<div class="row g-4" id="articles-container">
    <!-- Dinamis terisi via API -->
</div>

<!-- Pagination Container -->
<div class="d-flex justify-content-center mt-5" id="pagination-container">
    <!-- Dinamis terisi via API -->
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    muatArtikel(1);

    document.getElementById('cariArtikel').addEventListener('input', debounce(() => {
        muatArtikel(1);
    }, 400));

    document.getElementById('filterKategori').addEventListener('change', () => {
        muatArtikel(1);
    });
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

async function muatArtikel(page = 1) {
    const cari = document.getElementById('cariArtikel').value;
    const kategori = document.getElementById('filterKategori').value;
    const container = document.getElementById('articles-container');
    const pagination = document.getElementById('pagination-container');

    container.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;color:var(--accent-blue) !important;"></div>
            <div style="font-size:0.8rem;color:var(--text-muted);" class="mt-2">Memuat artikel analisis…</div>
        </div>
    `;
    pagination.innerHTML = '';

    try {
        const res = await axios.get('/api/v1/articles', {
            params: {
                page: page,
                cari: cari,
                kategori: kategori
            }
        });

        const paginatedData = res.data.data || {};
        const artikelList = paginatedData.data || [];

        let html = '';
        artikelList.forEach(art => {
            const img = art.gambar_url || 'https://images.unsplash.com/photo-1586528116311-ad8ed7c50a63?q=80&w=800&auto=format&fit=crop';
            const cat = art.kategori || 'Umum';
            const date = art.diterbitkan_pada ? new Date(art.diterbitkan_pada).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
            const author = art.author ? art.author.nama : 'Admin';
            const detailUrl = `/artikel/${art.id}`;

            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="article-card">
                        <div class="article-img-wrapper">
                            <img src="${img}" alt="${art.judul}" class="article-img">
                            <span class="article-category">
                                <i class="fas fa-tag me-1" style="font-size:0.65rem;"></i>${cat}
                            </span>
                        </div>
                        <div class="article-content">
                            <h5 class="article-title">${art.judul}</h5>
                            <p class="article-summary">${stripHtml(art.ringkasan).substring(0, 140)}...</p>
                            
                            <div class="mt-auto">
                                <div class="article-meta">
                                    <span><i class="fas fa-user-circle me-1"></i>${author}</span>
                                    <span><i class="fas fa-calendar-alt me-1"></i>${date}</span>
                                </div>
                                <a href="${detailUrl}" class="read-more-btn">
                                    Baca Selengkapnya <i class="fas fa-arrow-right ms-1" style="font-size:0.75rem;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html || `
            <div class="col-12">
                <div class="glass-card text-center py-5">
                    <i class="fas fa-newspaper fa-3x mb-3" style="color:var(--border-glass);"></i>
                    <h5 class="text-muted">Tidak ada artikel ditemukan.</h5>
                </div>
            </div>
        `;

        if (paginatedData.last_page > 1) {
            renderPagination(paginatedData, pagination);
        }

    } catch (e) {
        container.innerHTML = `
            <div class="col-12 text-center py-5 text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <div>Gagal memuat artikel: ${e.message}</div>
            </div>
        `;
    }
}

function stripHtml(html) {
   let tmp = document.createElement("DIV");
   tmp.innerHTML = html;
   return tmp.textContent || tmp.innerText || "";
}

function renderPagination(data, container) {
    let html = '<ul class="pagination pagination-sm mb-0">';
    
    // Previous
    if (data.prev_page_url) {
        html += `<li class="page-item"><button class="page-link" onclick="muatArtikel(${data.current_page - 1})"><i class="fas fa-chevron-left"></i></button></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>`;
    }

    // Page Numbers
    for (let i = 1; i <= data.last_page; i++) {
        if (i === data.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            if (i === 1 || i === data.last_page || Math.abs(data.current_page - i) <= 2) {
                html += `<li class="page-item"><button class="page-link" onclick="muatArtikel(${i})">${i}</button></li>`;
            } else if (i === 2 || i === data.last_page - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
    }

    // Next
    if (data.next_page_url) {
        html += `<li class="page-item"><button class="page-link" onclick="muatArtikel(${data.current_page + 1})"><i class="fas fa-chevron-right"></i></button></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>`;
    }

    html += '</ul>';
    container.innerHTML = html;
}
</script>
@endpush