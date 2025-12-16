<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJenjang extends Model
{
    protected $table = 'master_jenjangs';
    protected $fillable = ['nama_jenjang']; // Contoh: SD, SMP

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'id_jenjang');
    }
    
    public function aturNominals()
    {
        return $this->hasMany(AturNominal::class, 'id_jenjang');
    }
}
