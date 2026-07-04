<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryEconomicHistory extends Model
{
    protected $table = 'riwayat_ekonomi_negara';

    protected $fillable = [
        'negara_id',
        'tahun',
        'pdb',
        'inflasi',
        'populasi',
        'nilai_ekspor',
        'nilai_impor'
    ];

    protected $casts = [
        'tahun' => 'integer',
        'pdb' => 'double',
        'inflasi' => 'double',
        'populasi' => 'integer',
        'nilai_ekspor' => 'double',
        'nilai_impor' => 'double',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'negara_id');
    }
}
