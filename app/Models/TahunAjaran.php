<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TahunAjaran extends Model
{
    protected $table = 'tahun_ajarans';
    protected $fillable = ['nama_tahun', 'is_aktif']; // Contoh: 2025/2026
    public function aturNominals()
    {
        return $this->hasMany(AturNominal::class, 'id_tahun_ajaran');
    }
    // PERBAIKAN: Tambahkan relasi ke Tagihan
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'id_tahun_ajaran');
    }

    public function siswaKelasTahun()
    {
        return $this->hasMany(SiswaKelasTahun::class, 'id_tahun_ajaran');
    }

}