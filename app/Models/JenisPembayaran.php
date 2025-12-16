<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPembayaran extends Model
{
    protected $table = 'jenis_pembayarans';
    protected $fillable = ['nama_jenis']; // Contoh: SPP Bulanan, Uang Masuk

    public function aturNominals()
    {
        return $this->hasMany(AturNominal::class, 'id_jenis_pembayaran');
    }
    
    public function detailTagihans()
    {
        return $this->hasMany(DetailTagihan::class, 'id_jenis_pembayaran');
    }
}
