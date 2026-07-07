@extends('layouts.app')

@section('title', 'Artikel Analisis')

@section('page-title')
<i class="fas fa-newspaper me-2" style="color:#3b82f6;"></i>
Artikel Analisis
@endsection

@section('content')

<div class="glass-card">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-book-open me-2"></i>
            Daftar Artikel Analisis
        </h4>

        <span class="badge bg-primary">
            {{ $artikelList->total() }} Artikel
        </span>
    </div>

    <div class="row">

        @forelse($artikelList as $artikel)

        <div class="col-md-6 col-lg-4 mb-4">

            <div class="card bg-dark border-primary h-100">

                <div class="card-body">

                    <h5 class="card-title">
                        {{ $artikel->judul }}
                    </h5>

                    <p class="text-secondary">

                        {{ Str::limit(strip_tags($artikel->ringkasan),120) }}

                    </p>

                </div>

                <div class="card-footer bg-transparent">

                    <small class="text-secondary">

                        {{ $artikel->diterbitkan_pada }}

                    </small>

                    <br>

                    <a href="{{ route('articles.show',$artikel->id) }}"
                       class="btn btn-primary btn-sm mt-2">

                        Baca Selengkapnya

                    </a>

                </div>

            </div>

        </div>

        @empty

        <div class="col-12">

            <div class="alert alert-warning">

                Belum ada artikel.

            </div>

        </div>

        @endforelse

    </div>

    <div class="mt-3">

        {{ $artikelList->links() }}

    </div>

</div>

@endsection