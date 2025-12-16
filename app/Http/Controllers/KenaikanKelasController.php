<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelasTahun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KenaikanKelasController extends Controller
{
    /**
     * Tampilkan form untuk memilih Tahun Ajaran Lama dan Baru
     */
    public function index()
    {
        // Tahun ajaran yang sudah selesai/lama
        $tahunLama = TahunAjaran::orderByDesc('nama_tahun')->get();
        // Tahun ajaran yang baru/aktif
        $tahunBaru = TahunAjaran::orderByDesc('nama_tahun')->get();
        
        return view('kenaikan-kelas.index', compact('tahunLama', 'tahunBaru'));
    }

    /**
     * Tampilkan mapping kelas (Otomatis + Manual)
     */
    public function showMapping(Request $request)
    {
        $request->validate([
            'id_tahun_ajaran_lama' => 'required|exists:tahun_ajarans,id',
            'id_tahun_ajaran_baru' => 'required|exists:tahun_ajarans,id|different:id_tahun_ajaran_lama',
        ]);

        $idTahunLama = $request->id_tahun_ajaran_lama;
        $idTahunBaru = $request->id_tahun_ajaran_baru;

        // 1. Ambil semua kelas di Tahun Ajaran Lama
        $kelasLama = Kelas::whereHas('siswaKelasTahun', function($q) use ($idTahunLama) {
                $q->where('id_tahun_ajaran', $idTahunLama);
            })
            ->with(['jenjang'])
            ->get();
            
        // 2. Ambil semua kelas yang tersedia untuk tujuan mapping
        $kelasTersedia = Kelas::with(['jenjang'])->orderBy('tingkat')->get();

        $mapping = [];
        
        foreach ($kelasLama as $kelas) {
            // Logika Auto-Mapping: Naik satu tingkat
            $tingkatBaru = $kelas->tingkat + 1;
            
            // Coba cari kelas baru dengan tingkat + 1 dan jenjang yang sama
            $kelasBaruOtomatis = $kelasTersedia
                ->where('id_jenjang', $kelas->id_jenjang)
                ->where('tingkat', $tingkatBaru)
                ->where('nama_kelas', $kelas->nama_kelas) // Asumsi nama_kelas sama (misal: 1A ke 2A)
                ->first();

            $mapping[] = [
                'kelas_lama' => $kelas,
                'kelas_baru_otomatis' => $kelasBaruOtomatis,
                'id_kelas_baru_pilihan' => $kelasBaruOtomatis ? $kelasBaruOtomatis->id : null,
                'total_siswa' => SiswaKelasTahun::where('id_tahun_ajaran', $idTahunLama)
                                                ->where('id_kelas', $kelas->id)
                                                ->count()
            ];
        }

        return view('kenaikan-kelas.mapping', compact(
            'mapping', 'kelasTersedia', 'idTahunLama', 'idTahunBaru'
        ));
    }


    /**
     * Jalankan proses Naik Kelas Massal
     */
    public function runPromotion(Request $request)
    {
        $request->validate([
            'id_tahun_ajaran_lama' => 'required|exists:tahun_ajarans,id',
            'id_tahun_ajaran_baru' => 'required|exists:tahun_ajarans,id|different:id_tahun_ajaran_lama',
            // Pastikan mapping diterima dalam bentuk array
            'mapping' => 'required|array', 
            'mapping.*.id_kelas_lama' => 'required|exists:kelas,id',
            'mapping.*.id_kelas_baru' => 'nullable|exists:kelas,id', // Bisa null jika Lulus/Tidak Naik Kelas
        ]);

        $idTahunLama = $request->id_tahun_ajaran_lama;
        $idTahunBaru = $request->id_tahun_ajaran_baru;
        $mappingData = collect($request->mapping);

        $totalSiswaDiproses = 0;
        
        DB::beginTransaction();
        try {
            foreach ($mappingData as $map) {
                $idKelasLama = $map['id_kelas_lama'];
                $idKelasBaru = $map['id_kelas_baru']; // Bisa null

                // 1. Ambil semua siswa di kelas lama, tahun lama
                $siswaDiKelasLama = SiswaKelasTahun::where('id_tahun_ajaran', $idTahunLama)
                                                    ->where('id_kelas', $idKelasLama)
                                                    ->pluck('id_siswa');
                
                if ($siswaDiKelasLama->isEmpty()) continue;
                
                $totalSiswaDiproses += $siswaDiKelasLama->count();

                foreach ($siswaDiKelasLama as $idSiswa) {
                    // 2. Jika ada kelas baru (Naik Kelas/Pindah Kelas)
                    if ($idKelasBaru) {
                         // Cek apakah siswa sudah ada di tahun ajaran baru (antisipasi klik ganda)
                         $alreadyExists = SiswaKelasTahun::where('id_siswa', $idSiswa)
                                                            ->where('id_tahun_ajaran', $idTahunBaru)
                                                            ->exists();
                         
                         if (!$alreadyExists) {
                             // 3. Catat riwayat kelas baru
                             SiswaKelasTahun::create([
                                 'id_siswa' => $idSiswa,
                                 'id_kelas' => $idKelasBaru,
                                 'id_tahun_ajaran' => $idTahunBaru,
                             ]);
                             
                             // 4. Update status_aktif siswa ke 'Aktif'
                             Siswa::where('id', $idSiswa)->update(['status_aktif' => 'Aktif']);
                         }
                    } else {
                        // 5. Jika id_kelas_baru NULL (misal: Lulus/Tamat)
                        // Update status siswa menjadi 'Lulus'
                        Siswa::where('id', $idSiswa)->update(['status_aktif' => 'Lulus']);
                    }
                }
            }

            // Opsional: Set Tahun Ajaran Baru menjadi aktif
            TahunAjaran::where('is_aktif', true)->update(['is_aktif' => false]);
            TahunAjaran::where('id', $idTahunBaru)->update(['is_aktif' => true]);


            DB::commit();
            return redirect()->route('kenaikan-kelas.index')
                             ->with('success', "Proses Kenaikan Kelas Massal selesai! Total **$totalSiswaDiproses** siswa telah diproses.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menjalankan Kenaikan Kelas Massal: " . $e->getMessage());
            return back()->with('error', 'Gagal menjalankan proses kenaikan kelas massal. Error: Cek log server.');
        }
    }
    
    /**
     * Fitur tambahan: Ubah kelas satu siswa di tahun ajaran baru (sebelum atau sesudah promosi)
     */
    public function updateSiswaKelasIndividual(Request $request, Siswa $siswa)
    {
         $request->validate([
             'id_kelas' => 'required|exists:kelas,id',
             'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
         ]);

         // Cek apakah siswa sudah punya riwayat di TA tersebut
         $riwayat = SiswaKelasTahun::where('id_siswa', $siswa->id)
                                  ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                                  ->first();
         
         if ($riwayat) {
             $riwayat->update(['id_kelas' => $request->id_kelas]);
         } else {
             // Jika belum ada, buat baru (mungkin untuk siswa yang baru masuk di tengah tahun ajaran baru)
             SiswaKelasTahun::create([
                 'id_siswa' => $siswa->id,
                 'id_kelas' => $request->id_kelas,
                 'id_tahun_ajaran' => $request->id_tahun_ajaran,
             ]);
         }

         return back()->with('success', 'Kelas siswa berhasil diubah.');
    }
}