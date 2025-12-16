<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guru extends Model
{
    use SoftDeletes;

    protected $table = 'gurus';
    
    // Pastikan semua kolom yang boleh diisi mass assignment ada di sini
    protected $fillable = [
        'id_user', 
        'nama', 
        'nuptk', 
        'nip', // <== TAMBAHAN: Agar NIP bisa diisi via mass assignment
        'jenis_kelamin', 
        'agama', 
        'tempat_lahir', 
        'tanggal_lahir', 
        'foto' 
    ]; 

    /**
     * Relasi ke User (Akun Login)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Relasi ke Kelas (Kelas yang dia walikan)
     */
    public function kelasDiwalikan()
    {
        return $this->hasOne(Kelas::class, 'id_guru_wali_kelas');
    }
}