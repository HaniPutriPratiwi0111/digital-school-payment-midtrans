<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaKelasTahun extends Model
{
    protected $table = 'siswa_kelas_tahun';

    protected $fillable = [
        'id_siswa',
        'id_kelas',
        'id_tahun_ajaran'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran');
    }
    
}
