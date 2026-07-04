<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'artikel_analisis';

    protected $fillable = [
        'judul',
        'ringkasan',
        'konten',
        'penulis_id',
        'status',
        'diterbitkan_pada'
    ];

    protected $casts = [
        'diterbitkan_pada' => 'datetime'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }
}
