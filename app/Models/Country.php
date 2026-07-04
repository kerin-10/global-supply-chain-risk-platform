<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'negara';

    protected $fillable = [
        'nama',
        'kode_iso2',
        'kode_iso3',
        'wilayah',
        'ibu_kota',
        'kode_mata_uang',
        'nama_mata_uang',
        'bahasa',
        'populasi',
        'pdb',
        'inflasi',
        'nilai_ekspor',
        'nilai_impor',
        'lintang',
        'bujur',
        'cuaca_suhu',
        'cuaca_curah_hujan',
        'cuaca_kecepatan_angin',
        'cuaca_risiko_badai',
        'sinkronisasi_terakhir_pada'
    ];

    protected $casts = [
        'populasi' => 'integer',
        'pdb' => 'double',
        'inflasi' => 'double',
        'nilai_ekspor' => 'double',
        'nilai_impor' => 'double',
        'lintang' => 'double',
        'bujur' => 'double',
        'cuaca_suhu' => 'double',
        'cuaca_curah_hujan' => 'double',
        'cuaca_kecepatan_angin' => 'double',
        'cuaca_risiko_badai' => 'double',
        'sinkronisasi_terakhir_pada' => 'datetime',
    ];

    public function economicHistories()
    {
        return $this->hasMany(CountryEconomicHistory::class, 'negara_id');
    }

    public function ports()
    {
        return $this->hasMany(Port::class, 'negara_id');
    }

    public function currentRiskScore()
    {
        return $this->hasOne(RiskScore::class, 'negara_id');
    }

    public function riskScoreHistories()
    {
        return $this->hasMany(RiskScoreHistory::class, 'negara_id')->orderBy('dihitung_pada', 'desc');
    }

    public function newsCaches()
    {
        return $this->hasMany(NewsCache::class, 'negara_id')->orderBy('diterbitkan_pada', 'desc');
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class, 'negara_id');
    }

    public function watchers()
    {
        return $this->belongsToMany(User::class, 'daftar_pantau', 'negara_id', 'pengguna_id');
    }
}
