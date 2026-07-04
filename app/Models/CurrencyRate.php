<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $table = 'nilai_tukar_mata_uang';

    protected $fillable = [
        'mata_uang_asal',
        'mata_uang_tujuan',
        'nilai_tukar',
        'terakhir_diperbarui_pada'
    ];

    protected $casts = [
        'nilai_tukar' => 'double',
        'terakhir_diperbarui_pada' => 'datetime'
    ];
}
