<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class SiswaMobileController extends Controller
{
public function index(Request $request)
{
    $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();
    $idTahunAjaran = $request->id_tahun_ajaran ?? ($tahunAjaranAktif->id ?? null);

    $query = Siswa::with('kelas.jenjang') // tetap pakai relasi lama supaya nama kelas muncul
        ->where('status_aktif', 'Aktif');

    // FILTER TAHUN AJARAN via pivot table SiswaKelasTahun
    if ($idTahunAjaran) {
        $query->whereHas('siswaKelasTahun', function ($q) use ($idTahunAjaran) {
            $q->where('id_tahun_ajaran', $idTahunAjaran);
        });
    }

    // FILTER KELAS
    if ($request->filled('id_kelas')) {
        $query->whereHas('siswaKelasTahun', function ($q) use ($request) {
            $q->where('id_kelas', $request->id_kelas);
        });
    }

    return $query->orderBy('nama_siswa')->get();
}

public function show($id)
{
    return Siswa::with(['kelas.jenjang', 'tagihans'])->findOrFail($id);
}
}
