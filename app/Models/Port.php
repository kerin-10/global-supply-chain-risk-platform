<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $table = 'pelabuhan';

    protected $fillable = [
        'nama',
        'kode_pelabuhan',
        'lintang',
        'bujur',
        'negara_id',
        'kode_negara',
        'wilayah',
        'nomor_wpi'
    ];

    protected $casts = [
        'lintang' => 'double',
        'bujur' => 'double',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'negara_id');
    }

    public function congestions()
    {
        return $this->hasMany(PortCongestion::class, 'pelabuhan_id')->orderBy('dilaporkan_pada', 'desc');
    }

    public function latestCongestion()
    {
        return $this->hasOne(PortCongestion::class, 'pelabuhan_id')->latestOfMany();
    }
}
