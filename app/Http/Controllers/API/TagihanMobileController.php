<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Siswa;
use App\Models\TahunAjaran; 
use App\Models\AturNominal; 
use App\Models\Kelas;    
use App\Models\JenisPembayaran; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class TagihanMobileController extends Controller
{
    /**
     * Menampilkan daftar tagihan dengan filter.
     * Konsisten dengan web: menggunakan kelasAktif dan filter Tahun Ajaran.
     */
    public function index(Request $request)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();
        
        // Tentukan Tahun Ajaran yang digunakan untuk filter
        $selectedTahunId = $request->id_tahun_ajaran ?? ($tahunAjaranAktif->id ?? null);

        $query = Tagihan::with([
            // 🚨 PERBAIKAN: Gunakan kelasAktif untuk relasi Siswa
            'siswa.kelasAktif.kelas.jenjang', 
            'jenisPembayaran', 
            'tahunAjaran'
        ])
        ->when($selectedTahunId, function ($q) use ($selectedTahunId) {
            $q->where('id_tahun_ajaran', $selectedTahunId);
        });

        // 🌟 Filter Kelas Opsional
        if ($request->has('id_kelas')) {
            $query->whereHas('siswa.kelasAktif', function($q) use ($request) {
                $q->where('id_kelas', $request->id_kelas);
            });
        }

        // 🌟 Filter Status Opsional
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Gunakan Paginasi seperti di Web (default 10)
        $perPage = $request->get('per_page', 10);

        return $query->latest()->paginate($perPage);
    }

    /**
     * Menampilkan detail tagihan tunggal.
     */
    public function show($id)
    {
        // 🚨 PERBAIKAN: Tambahkan 'siswa.kelasAktif.kelas.jenjang' untuk detail siswa
        return Tagihan::with([
            'siswa.kelasAktif.kelas.jenjang', 
            'jenisPembayaran', 
            'tahunAjaran'
        ])->findOrFail($id);
    }

    /**
     * Menyimpan tagihan tunggal. Logika nominal sama dengan Web.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_siswa' => 'required|exists:siswas,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
            'id_jenis_pembayaran' => 'required|exists:jenis_pembayarans,id',
            'bulan_tagihan' => 'nullable|integer|min:1|max:12',
            // 'tahun_tagihan' dihilangkan agar konsisten dengan web (cukup id_tahun_ajaran)
            'tanggal_jatuh_tempo' => 'required|date',
            'status' => 'nullable|string|in:Belum Bayar,Lunas Partial,Lunas,Batal'
        ]);

        try {
            // 🚨 PERBAIKAN: Gunakan kelasAktif
            $siswa = Siswa::with('kelasAktif.kelas.jenjang')->find($request->id_siswa);

            if (!$siswa || !$siswa->kelasAktif || !$siswa->kelasAktif->kelas || !$siswa->kelasAktif->kelas->jenjang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kelas aktif atau jenjang siswa tidak lengkap.'
                ], 422);
            }
            
            $kelasAktif = $siswa->kelasAktif->kelas;
            $jenjangId = $kelasAktif->jenjang->id;
            $tingkat = $kelasAktif->tingkat;

            $isKeluarga = $siswa->is_keluarga == true;
            
            // 🌟 Cek Duplikasi Tagihan Bulanan (Sama seperti di Web)
            if ($request->bulan_tagihan) {
                $existingTagihan = Tagihan::where('id_siswa', $siswa->id)
                    ->where('id_jenis_pembayaran', $request->id_jenis_pembayaran)
                    ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                    ->where('bulan_tagihan', $request->bulan_tagihan)
                    ->first();

                if ($existingTagihan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tagihan untuk bulan/tahun ini sudah ada untuk siswa ini.'
                    ], 422);
                }
            }

            // Cari Aturan Nominal
            $aturan = AturNominal::where('id_jenis_pembayaran', $request->id_jenis_pembayaran)
                ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                ->where('id_jenjang', $jenjangId)
                ->where(function($q) use ($tingkat) {
                    $q->where('tingkat', $tingkat)
                    ->orWhereNull('tingkat');
                })
                ->orderByRaw("tingkat IS NULL")
                ->first();

            if (!$aturan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aturan nominal untuk siswa ini belum ditentukan.'
                ], 422);
            }

            // Hitung nominal final & diskon (Logika Diskon Keluarga sama dengan Web)
            $nominalNormal = $aturan->nominal_normal ?? 0;
            $nominalKeluarga = $aturan->nominal_keluarga ?? 0;

            $nominalFinal = $nominalNormal;
            if ($isKeluarga && !is_null($nominalKeluarga) && $nominalKeluarga > 0) {
                $nominalFinal = $nominalKeluarga;
            }

            $nominalDiskon = $nominalNormal - $nominalFinal;

            $tagihan = Tagihan::create([
                'id_siswa' => $siswa->id,
                'id_tahun_ajaran' => $request->id_tahun_ajaran,
                'id_jenis_pembayaran' => $request->id_jenis_pembayaran,
                'id_atur_nominal' => $aturan->id,
                'bulan_tagihan' => $request->bulan_tagihan ?? null,
                // 'tahun_tagihan' dihilangkan agar sama dengan Web
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'nominal_tagihan' => $nominalFinal,
                'nominal_diskon' => $nominalDiskon,
                'is_harga_keluarga_applied' => $isKeluarga,
                'total_tagihan' => $nominalFinal,
                'status' => $request->status ?? 'Belum Bayar',
                'midtrans_order_id' => 'INV-' . strtoupper(uniqid()),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tagihan tunggal berhasil ditambahkan!',
                'data' => $tagihan
            ], 201);

        } catch (\Throwable $e) {
            // Catat Error untuk debugging
            // \Illuminate\Support\Facades\Log::error("API Store Tagihan Error: " . $e->getMessage()); 
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Menyimpan tagihan massal. Logika nominal dan siswa sama dengan Web.
     */
    public function storeMassal(Request $request)
    {
        $request->validate([
            'id_kelas'            => 'required|exists:kelas,id',
            'id_jenis_pembayaran' => 'required|exists:jenis_pembayarans,id',
            'id_tahun_ajaran'     => 'required|exists:tahun_ajarans,id', // Wajib ada
            'bulan_tagihan'       => 'nullable|integer|min:1|max:12',
            // 'tahun_tagihan' dihilangkan
            'tanggal_jatuh_tempo' => 'required|date',
        ]);

        $idKelas = $request->id_kelas;
        $idTahunAjaran = $request->id_tahun_ajaran;
        $idJenisPembayaran = $request->id_jenis_pembayaran;

        // 🚨 PERBAIKAN: Ambil Siswa melalui relasi kelasAktif agar sesuai Tahun Ajaran
        $siswas = Siswa::whereHas('kelasAktif', function($q) use ($idTahunAjaran, $idKelas){
                $q->where('id_tahun_ajaran', $idTahunAjaran)
                  ->where('id_kelas', $idKelas);
            })
            ->with('kelasAktif.kelas.jenjang')
            ->where('status_aktif', 'Aktif')
            ->get();

        if ($siswas->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada siswa aktif di kelas ini.'], 422);
        }

        $tagihans = [];
        $errors = [];
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($siswas as $siswa) {

                if (!$siswa->kelasAktif || !$siswa->kelasAktif->kelas || !$siswa->kelasAktif->kelas->jenjang) {
                     $errors[] = "Kelas/jenjang tidak ditemukan untuk {$siswa->nama_siswa}.";
                     continue;
                }
                
                $kelasAktif = $siswa->kelasAktif->kelas;
                $jenjang = $kelasAktif->jenjang;
                $tingkat = $kelasAktif->tingkat;

                // 🌟 Cek Duplikasi (Sama seperti di Web)
                if ($request->bulan_tagihan) {
                    $existingTagihan = Tagihan::where('id_siswa', $siswa->id)
                        ->where('id_jenis_pembayaran', $idJenisPembayaran)
                        ->where('id_tahun_ajaran', $idTahunAjaran)
                        ->where('bulan_tagihan', $request->bulan_tagihan)
                        ->first();

                    if ($existingTagihan) {
                        $errors[] = "Tagihan bulan ini sudah ada untuk {$siswa->nama_siswa}.";
                        continue;
                    }
                }

                // Cari Aturan Nominal
                $aturan = AturNominal::where('id_jenis_pembayaran', $idJenisPembayaran)
                    ->where('id_tahun_ajaran', $idTahunAjaran)
                    ->where('id_jenjang', $jenjang->id)
                    ->where(function ($q) use ($tingkat) {
                        $q->where('tingkat', $tingkat)->orWhereNull('tingkat');
                    })
                    ->orderByRaw("tingkat IS NULL")
                    ->first();

                if (!$aturan) {
                    $errors[] = "Aturan nominal tidak ditemukan untuk {$siswa->nama_siswa}.";
                    continue;
                }

                $isKeluarga = $siswa->is_keluarga == true;
                $nominalNormal = $aturan->nominal_normal;
                $nominalKeluarga = $aturan->nominal_keluarga ?? 0;

                $nominalFinal = $nominalNormal;
                if ($isKeluarga && $nominalKeluarga > 0) {
                    $nominalFinal = $nominalKeluarga;
                }
                $diskon = $nominalNormal - $nominalFinal;

                $tagihans[] = Tagihan::create([
                    'id_siswa'                  => $siswa->id,
                    'id_jenis_pembayaran'       => $idJenisPembayaran,
                    'id_tahun_ajaran'           => $idTahunAjaran,
                    'id_atur_nominal'           => $aturan->id,
                    'bulan_tagihan'             => $request->bulan_tagihan,
                    // 'tahun_tagihan' dihilangkan
                    'tanggal_jatuh_tempo'       => $request->tanggal_jatuh_tempo,
                    'nominal_tagihan'           => $nominalFinal,
                    'nominal_diskon'            => $diskon,
                    'is_harga_keluarga_applied' => $isKeluarga,
                    'total_tagihan'             => $nominalFinal,
                    'status'                    => 'Belum Bayar',
                    'midtrans_order_id'         => 'INV-' . strtoupper(uniqid()),
                ]);
                $count++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menyimpan tagihan massal: ' . $e->getMessage()
            ], 500);
        }

        $message = "Tagihan massal berhasil dibuat untuk $count siswa.";
        if (!empty($errors)) {
            $message .= " Peringatan: " . implode(' ', $errors);
        }

        return response()->json([
            'success' => true,  
            'message' => $message,
            'data' => $tagihans
        ], 201);
    }
    
    // ==========================================================
    // FUNGSI GET NOMINAL (AJAX) - Disinkronkan dengan Web
    // ==========================================================
    public function getNominal($id_siswa, $id_jenis, $id_tahun)
    {
        // 🚨 PERBAIKAN: Gunakan kelasAktif
        $siswa = Siswa::with('kelasAktif.kelas.jenjang')->find($id_siswa);

        if (!$siswa || !$siswa->kelasAktif || !$siswa->kelasAktif->kelas || !$siswa->kelasAktif->kelas->jenjang) {
            return response()->json(['nominal' => null, 'diskon' => null, 'error' => 'Data kelas siswa tidak ditemukan.']);
        }

        $kelasAktif = $siswa->kelasAktif->kelas;
        $id_jenjang = $kelasAktif->jenjang->id;
        $tingkat = $kelasAktif->tingkat;

        $aturan = AturNominal::where('id_jenis_pembayaran', $id_jenis)
            ->where('id_tahun_ajaran', $id_tahun)
            ->where('id_jenjang', $id_jenjang)
            ->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)->orWhereNull('tingkat');
            })
            ->orderByRaw("tingkat IS NULL")
            ->first();

        if (!$aturan) {
            return response()->json(['nominal' => null, 'diskon' => null, 'error' => 'Aturan nominal tidak ditemukan.']);
        }

        $isKeluarga = $siswa->is_keluarga == true;
        $nominalNormal = $aturan->nominal_normal;
        $nominalKeluarga = $aturan->nominal_keluarga ?? 0;
        
        $nominalFinal = $nominalNormal;
        if ($isKeluarga && $nominalKeluarga > 0) {
            $nominalFinal = $nominalKeluarga;
        }

        $nominalDiskon = $nominalNormal - $nominalFinal;

        return response()->json([
            'nominal' => $nominalFinal,
            'diskon' => $nominalDiskon
        ]);
    }

    // ==========================================================
    // FUNGSI GET NOMINAL MASSAL (AJAX) - Disinkronkan dengan Web
    // ==========================================================
    public function getNominalMassal($id_kelas, $id_jenis, $id_tahun)
    {
        // 🚨 PERBAIKAN: Ambil data kelas dari model Kelas, lalu cari aturan nominalnya.
        $kelas = Kelas::with('jenjang')->find($id_kelas);

        if (!$kelas || !$kelas->jenjang) {
            return response()->json(['nominal' => 0, 'diskon' => 0, 'error' => 'Kelas atau jenjang tidak valid']);
        }

        $jenjang = $kelas->jenjang;
        $tingkat = $kelas->tingkat;

        $aturan = AturNominal::where('id_jenis_pembayaran', $id_jenis)
            ->where('id_tahun_ajaran', $id_tahun)
            ->where('id_jenjang', $jenjang->id)
            ->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)->orWhereNull('tingkat');
            })
            ->orderByRaw("tingkat IS NULL")
            ->first();

        if (!$aturan) {
            return response()->json(['nominal' => 0, 'diskon' => 0, 'error' => 'Aturan nominal tidak ditemukan.']);
        }

        // Output sama dengan di Web: nominal normal dan diskon keluarga (potongan)
        $nominalNormal = $aturan->nominal_normal;
        $nominalKeluarga = $aturan->nominal_keluarga ?? 0;
        $diskon = $nominalNormal - $nominalKeluarga;

        return response()->json([
            'nominal' => $nominalNormal,
            'diskon' => $diskon, // Ini adalah POTONGAN untuk siswa keluarga
            'error' => null
        ]);
    }
    
    // ==========================================================
    // FUNGSI GET TAGIHAN BY SISWA (Detail tagihan per siswa)
    // ==========================================================
    public function getTagihansBySiswa($id_siswa)
    {
        // 🚨 PERBAIKAN: Gunakan kelasAktif
        $siswa = Siswa::with('kelasAktif.kelas.jenjang')->find($id_siswa);

        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $tagihans = Tagihan::with(['jenisPembayaran', 'tahunAjaran', 'siswa.kelasAktif.kelas.jenjang'])
            ->where('id_siswa', $id_siswa)
            ->orderByDesc('created_at')
            ->get();


        return response()->json([
            'status' => 'success',
            'siswa' => $siswa,
            'tagihans' => $tagihans
        ]);
    }

    // ... (metode update, destroy, getMasterData)
    // Metode-metode ini tidak perlu diubah karena hanya CRUD sederhana
    public function update(Request $request, $id)
    {
        $tagihan = Tagihan::findOrFail($id);

        $tagihan->update([
            'total_tagihan' => $request->total_tagihan ?? $tagihan->total_tagihan,
            'status' => $request->status ?? $tagihan->status
        ]);

        return response()->json(['success' => true, 'data' => $tagihan]);
    }

    public function destroy($id)
    {
        Tagihan::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

// ==========================================================
    // FUNGSI GET MASTER DATA (PERBAIKAN KRITIS UNTUK ANDROID)
    // ==========================================================
    public function getMasterData()
    {
        $kelas = Kelas::with('jenjang')->get()->toArray();
        $jenisPembayaran = JenisPembayaran::all()->toArray();
        $tahunAjaran = TahunAjaran::all()->toArray();
        $siswa = Siswa::where('status_aktif', 'Aktif')->get()->toArray();

        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();

        return response()->json([
            'status' => 'success',
            'kelas' => $kelas,
            'jenisPembayaran' => $jenisPembayaran,
            'tahun_ajaran' => $tahunAjaran,
            'tahun_ajaran_aktif' => $tahunAjaranAktif, // 🔥 TAMBAHAN
            'siswa' => $siswa,
        ]);
    }
}