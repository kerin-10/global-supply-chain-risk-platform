<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pengguna';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'kata_sandi',
        'peran',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the password for the user (overridden for kata_sandi).
     */
    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    /**
     * Get user profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'pengguna_id');
    }

    /**
     * Get watchlist.
     */
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class, 'pengguna_id');
    }

    /**
     * Get countries in watchlist.
     */
    public function favoriteCountries()
    {
        return $this->belongsToMany(Country::class, 'daftar_pantau', 'pengguna_id', 'negara_id');
    }

    /**
     * Get articles written by user.
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'penulis_id');
    }
}
