<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskScoreHistory extends Model
{
    protected $table = 'riwayat_skor_risiko';

    protected $fillable = [
        'negara_id',
        'risiko_cuaca',
        'risiko_inflasi',
        'risiko_nilai_tukar',
        'risiko_sentimen_berita',
        'total_risiko',
        'dihitung_pada'
    ];

    protected $casts = [
        'risiko_cuaca' => 'double',
        'risiko_inflasi' => 'double',
        'risiko_nilai_tukar' => 'double',
        'risiko_sentimen_berita' => 'double',
        'total_risiko' => 'double',
        'dihitung_pada' => 'datetime'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'negara_id');
    }
}
