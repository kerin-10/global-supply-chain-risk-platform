<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'profil_pengguna';

    protected $fillable = [
        'pengguna_id',
        'telepon',
        'departemen',
        'biodata'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
