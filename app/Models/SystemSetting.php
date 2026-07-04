<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'pengaturan_sistem';

    protected $fillable = [
        'kunci',
        'nilai',
        'deskripsi'
    ];

    /**
     * Get a setting value.
     */
    public static function getVal(string $kunci, $default = null)
    {
        $setting = self::where('kunci', $kunci)->first();
        return $setting ? $setting->nilai : $default;
    }

    /**
     * Set a setting value.
     */
    public static function setVal(string $kunci, $nilai, ?string $deskripsi = null)
    {
        return self::updateOrCreate(
            ['kunci' => $kunci],
            ['nilai' => $nilai, 'deskripsi' => $deskripsi]
        );
    }
}
