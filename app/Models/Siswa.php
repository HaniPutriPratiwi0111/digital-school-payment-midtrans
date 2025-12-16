<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswas';
    
    protected $fillable = [
        'id_kelas', 
        'id_user', 
        'id_tahun_ajaran', // tambahkan ini
        'nisn', 
        'nama_siswa',
        'tempat_lahir', 
        'tanggal_lahir', 
        'jenis_kelamin', 
        'agama', 
        'status_aktif',
        'nama_wali_murid', 
        'telp_wali_murid', 
        'is_keluarga'
    ]; 


    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
    
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'id_siswa');
    }

    public function notifikasiLogs()
    {
        return $this->hasMany(NotifikasiLog::class, 'id_siswa');
    }

    public function siswaKelasTahun()
    {
        return $this->hasMany(SiswaKelasTahun::class, 'id_siswa');
    }

    // Relasi kelas aktif
    public function kelasAktif()
    {
        return $this->hasOne(SiswaKelasTahun::class, 'id_siswa')
            ->whereHas('tahunAjaran', fn($q) => $q->where('is_aktif', 1))
            ->with('kelas.jenjang', 'tahunAjaran');
    }

    public function semuaKelas()
    {
        return $this->hasMany(SiswaKelasTahun::class, 'id_siswa')->with('kelas.jenjang', 'tahunAjaran');
    }

}
