<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $table = 'daftar_pantau';

    protected $fillable = [
        'pengguna_id',
        'negara_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'negara_id');
    }
}
