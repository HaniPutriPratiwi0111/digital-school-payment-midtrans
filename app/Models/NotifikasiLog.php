<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiLog extends Model
{
    protected $table = 'notifikasi_logs';
    
    protected $fillable = [
        'id_siswa', 
        'id_tagihan', 
        'tipe_notifikasi', // Pengingat Tagihan, Konfirmasi Pembayaran, Info Lain
        'isi_pesan', 
        'status_kirim', // Pending, Sukses, Gagal
        'waktu_kirim'
    ]; 
    
    protected $casts = [
        'waktu_kirim' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan');
    }
}
