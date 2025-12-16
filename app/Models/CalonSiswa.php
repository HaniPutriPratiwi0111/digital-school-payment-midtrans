<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalonSiswa extends Model
{
    protected $table = 'calon_siswas';
    
    protected $fillable = [
        'id_jenjang',
        'id_tahun_ajaran',
        'nama_siswa',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'nama_wali_murid',
        'telp_wali_murid',
        'email',
        'is_keluarga',
        'midtrans_order_id',
        'amount',
        'payment_status',
        'approval_status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function jenjang()
    {
        return $this->belongsTo(MasterJenjang::class, 'id_jenjang');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran');
    }

    public function tagihan()
    {
        // Tagihan pendaftaran yang terkait
        return $this->hasOne(Tagihan::class, 'calon_siswa_id');
    }
}