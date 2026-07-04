<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositiveWord extends Model
{
    protected $table = 'kata_positif';

    protected $fillable = ['kata'];
}
