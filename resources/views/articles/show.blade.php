@extends('layouts.app')

@section('title','Detail Artikel')

@section('page-title')
<i class="fas fa-book me-2" style="color:#3b82f6;"></i>
Detail Artikel
@endsection

@section('content')

<div class="glass-card">

    <h2 class="mb-3">

        {{ $artikel->judul }}

    </h2>

    <div class="text-secondary mb-4">
    Penulis :
    {{ $artikel->author->nama ?? '-' }}

    |

    {{ optional($artikel->diterbitkan_pada)->format('d M Y') }}
</div>

    <div class="mb-5">

        {!! nl2br(e($artikel->konten)) !!}

    </div>

    <a href="{{ route('dashboard.articles') }}"
       class="btn btn-primary">

        ← Kembali

    </a>

</div>

@endsection