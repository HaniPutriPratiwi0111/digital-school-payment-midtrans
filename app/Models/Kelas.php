<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['id_jenjang', 'tingkat', 'nama_kelas', 'id_guru_wali_kelas'];

    public function jenjang()
    {
        // Relasi ke MasterJenjang
        return $this->belongsTo(MasterJenjang::class, 'id_jenjang');
    }

    public function waliKelas()
    {
        // Relasi ke Guru yang menjadi wali kelas
        return $this->belongsTo(Guru::class, 'id_guru_wali_kelas');
    }

    public function siswas()
    {
        // Relasi ke Siswa
        return $this->hasMany(Siswa::class, 'id_kelas');
    }

    public function siswaKelasTahun()
    {
        return $this->hasMany(SiswaKelasTahun::class, 'id_kelas');
    }

}
