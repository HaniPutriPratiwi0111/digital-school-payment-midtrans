<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTagihan extends Model
{
    protected $table = 'detail_tagihans';
    
    protected $fillable = [
        'id_tagihan', 
        'id_jenis_pembayaran', 
        'deskripsi', 
        'nominal_unit', 
        'qty', 
        'subtotal'
    ]; // Rincian Item Tagihan

    // public function tagihan()
    // {
    //     return $this->belongsTo(Tagihan::class, 'id_tagihan');
    // }

    // public function jenisPembayaran()
    // {
    //     return $this->belongsTo(JenisPembayaran::class, 'id_jenis_pembayaran');
    // }
}
