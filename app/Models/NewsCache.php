<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $table = 'arsip_berita';

    protected $fillable = [
        'negara_id',
        'judul',
        'deskripsi',
        'konten',
        'tautan_url',
        'sumber',
        'diterbitkan_pada',
        'sentimen',
        'skor_sentimen_positif',
        'skor_sentimen_negatif'
    ];

    protected $casts = [
        'diterbitkan_pada' => 'datetime',
        'skor_sentimen_positif' => 'integer',
        'skor_sentimen_negatif' => 'integer'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'negara_id');
    }
}
