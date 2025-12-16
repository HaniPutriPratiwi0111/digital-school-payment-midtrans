<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AturNominal extends Model
{    
    protected $table = 'atur_nominals';
    
    protected $fillable = [
        'id_jenis_pembayaran', 
        'id_tahun_ajaran', 
        'id_jenjang', 
        'tingkat', 
        'bulan_berlaku', 
        // ⚠️ KOLOM BARU ⚠️
        'nominal_normal',   // Harga untuk siswa biasa
        'nominal_keluarga'  // Harga diskon untuk keluarga
    ];

    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'id_jenis_pembayaran');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran');
    }

    public function jenjang()
    {
        return $this->belongsTo(MasterJenjang::class, 'id_jenjang');
    }
}
