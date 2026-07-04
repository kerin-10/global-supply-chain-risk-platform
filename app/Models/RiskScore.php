<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskScore extends Model
{
    protected $table = 'skor_risiko';

    protected $fillable = [
        'negara_id',
        'risiko_cuaca',
        'risiko_inflasi',
        'risiko_nilai_tukar',
        'risiko_sentimen_berita',
        'total_risiko',
        'tingkat_risiko'
    ];

    protected $casts = [
        'risiko_cuaca' => 'double',
        'risiko_inflasi' => 'double',
        'risiko_nilai_tukar' => 'double',
        'risiko_sentimen_berita' => 'double',
        'total_risiko' => 'double'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'negara_id');
    }
}
