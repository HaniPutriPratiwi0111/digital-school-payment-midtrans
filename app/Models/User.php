<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'nisn',
        'password',
        'siswa_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::created(function ($user) {
            if (!empty($user->role)) {
                $user->assignRole($user->role);
            }
        });
    }

    public function guru()
    {
        return $this->hasOne(Guru::class, 'id_user');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'id_user');
    }

    // app/Models/User.php
    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'id_siswa', 'siswa_id'); 
        // pastikan 'siswa_id' sesuai kolom di tabel tagihan yang menunjuk ke siswa
    }
}
