<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    protected $table = 'log_permintaan_api';

    protected $fillable = [
        'nama_api',
        'endpoint',
        'status_respons',
        'waktu_respons_ms',
        'diminta_pada'
    ];

    protected $casts = [
        'status_respons' => 'integer',
        'waktu_respons_ms' => 'integer',
        'diminta_pada' => 'datetime'
    ];
}
