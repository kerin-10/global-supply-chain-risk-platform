<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortCongestion extends Model
{
    protected $table = 'kemacetan_pelabuhan';

    protected $fillable = [
        'pelabuhan_id',
        'waktu_tunda_jam',
        'tingkat_kemacetan',
        'deskripsi_status',
        'dilaporkan_pada'
    ];

    protected $casts = [
        'waktu_tunda_jam' => 'double',
        'dilaporkan_pada' => 'datetime',
    ];

    public function port()
    {
        return $this->belongsTo(Port::class, 'pelabuhan_id');
    }
}
