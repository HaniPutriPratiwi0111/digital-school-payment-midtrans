<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihans';
    
// File: app/Models/Tagihan.php (Perubahan di $fillable)

    protected $fillable = [
        'id_siswa',
        'calon_siswa_id',
        'id_tahun_ajaran',
        'id_jenis_pembayaran',
        'bulan_tagihan',
        'tanggal_jatuh_tempo',
        'total_tagihan',
        'status',
        'midtrans_order_id',
        
        // START: Kolom BARU yang harus ditambahkan
        'nominal_tagihan', // <-- Harga Normal (dari migration)
        'nominal_diskon', // <-- Harus ditambah ke migration juga!
        'id_atur_nominal',
        'is_harga_keluarga_applied', // <-- Dari migration
        // END: Kolom BARU yang harus ditambahkan
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
    ];

    public function calonSiswa()
    {
        return $this->belongsTo(CalonSiswa::class, 'calon_siswa_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran');
    }

    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'id_jenis_pembayaran');
    }
    
    public function detailTagihans()
    {
        return $this->hasMany(DetailTagihan::class, 'id_tagihan');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'id_tagihan');
    }

    public function kelasTagihan()
    {
        return $this->hasOne(SiswaKelasTahun::class, 'id_siswa')
            ->where('id_tahun_ajaran', $this->id_tahun_ajaran)
            ->with('kelas.jenjang', 'tahunAjaran');
    }
}
