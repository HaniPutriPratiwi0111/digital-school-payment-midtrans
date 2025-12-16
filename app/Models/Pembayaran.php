<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayarans';
    
    protected $fillable = [
        'id_tagihan', 
        'id_user', 
        'kode_transaksi', 
        'tanggal_bayar', 
        'metode_pembayaran', 
        'total_bayar', 
        'midtrans_transaction_id'
    ];
    
    protected $casts = [
        'tanggal_bayar' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user'); 
    }

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan');
    }
}
