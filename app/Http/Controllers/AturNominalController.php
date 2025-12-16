<?php

namespace App\Http\Controllers;

use App\Models\AturNominal;
use App\Models\JenisPembayaran;
use App\Models\TahunAjaran;
use App\Models\MasterJenjang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; // Import Rule untuk validasi dinamis

class AturNominalController extends Controller
{
    /**
     * Menampilkan daftar aturan nominal.
     */
    public function index(Request $request)
    {
        // Ambil tahun ajaran aktif (terbaru)
        $tahun_ajaran_aktif = TahunAjaran::orderByDesc('id')->first();

        $nominals = AturNominal::with(['jenisPembayaran', 'tahunAjaran', 'jenjang'])
            ->when($request->jenis, function ($query, $jenis) {
                $query->whereHas('jenisPembayaran', function ($q) use ($jenis) {
                    $q->where('nama_jenis', 'LIKE', '%' . $jenis . '%'); 
                });
            })
            ->when($request->tahun_ajaran ?? $tahun_ajaran_aktif->id, function ($query, $tahunAjaranId) {
                $query->where('id_tahun_ajaran', $tahunAjaranId);
            })
            ->orderByDesc('id_tahun_ajaran')
            ->paginate(10);

        $jenis_pembayarans = JenisPembayaran::all();
        $tahun_ajarans = TahunAjaran::all();

        return view('atur-nominal.index', compact('nominals', 'jenis_pembayarans', 'tahun_ajarans', 'tahun_ajaran_aktif'));
    }

    /**
     * Menampilkan form untuk membuat aturan nominal baru.
     */
    public function create()
    {
        // Mendapatkan ID Jenis Pembayaran Pendaftaran untuk kebutuhan validasi di sisi Blade/JS
        $id_jenis_pendaftaran = JenisPembayaran::where('nama_jenis', 'LIKE', '%Pendaftaran%')->value('id');

        $data = [
            'jenis_pembayarans' => JenisPembayaran::all(),
            'tahun_ajarans' => TahunAjaran::all(),
            'jenjangs' => MasterJenjang::all(),
            'id_jenis_pendaftaran' => $id_jenis_pendaftaran, // ⬅️ Diteruskan ke Blade
        ];
        return view('atur-nominal.create', $data);
    }

    /**
     * Menyimpan aturan nominal baru ke database.
     */
    public function store(Request $request)
    {
        // Cek ID Jenis Pembayaran Pendaftaran yang sebenarnya
        $jenisPendaftaran = JenisPembayaran::where('nama_jenis', 'LIKE', '%Pendaftaran%')->first();
        $id_jenis_pendaftaran = $jenisPendaftaran ? $jenisPendaftaran->id : null;
        $isPendaftaran = $request->input('id_jenis_pembayaran') == $id_jenis_pendaftaran;

        $validationRules = [
            'id_jenis_pembayaran' => 'required|exists:jenis_pembayarans,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
            'id_jenjang' => 'required|exists:master_jenjangs,id',
            'tingkat' => ['nullable','integer','min:1'],
            'nominal_normal' => 'required|numeric|min:0',
            'nominal_keluarga' => 'nullable|numeric|min:0|lte:nominal_normal', // ⬅️ BATASI DISKON
        ];

        // 🎯 LOGIKA VALIDASI BULAN BERLAKU DINAMIS
        if ($isPendaftaran) {
            // Pendaftaran: Bulan wajib diisi (untuk setiap entri bulan)
            $validationRules['bulan_berlaku'] = 'required|integer|min:1|max:12';
        } else {
            // Non-Pendaftaran: Bulan opsional/nullable 
            $validationRules['bulan_berlaku'] = 'nullable|integer|min:1|max:12';
        }



        $request->validate($validationRules);
        
        // Ambil semua data
        $data = $request->except(['nominal', 'is_keluarga_price']);
        
        // 🎯 LOGIKA PEMBERSIHAN DATA SEBELUM SIMPAN
        
        // 1. Pastikan kolom nominal_keluarga menjadi null jika input kosong
        $data['nominal_keluarga'] = $request->nominal_keluarga ?? null;

        // 2. Jika jenisnya Pendaftaran, paksa Tingkat menjadi NULL (karena Pendaftaran berlaku untuk Jenjang, bukan Tingkat)
        if ($isPendaftaran) {
            $data['tingkat'] = null;
            // Pendaftaran WAJIB punya bulan berlaku (sudah divalidasi 1-12)
        } else {
            // 3. Jika jenisnya Non-Pendaftaran DAN bulan_berlaku kosong/null, 
            //    set ke 0 (mengartikan berlaku untuk semua bulan/biaya tunggal).
            if (empty($request->bulan_berlaku)) {
                $data['bulan_berlaku'] = 0; 
            }
            // Jika Non-Pendaftaran DAN bulan_berlaku terisi (1-12), nilainya sudah benar di $data.
        }
        
        try {
            AturNominal::create($data);
            return redirect()->route('atur-nominal.index')->with('success', 'Nominal berhasil diatur.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap error jika terjadi UNIQUE constraint violation
            if ($e->getCode() == 23000) { 
                return redirect()->back()->withInput()->with('error', 'Gagal: Aturan nominal untuk kombinasi tersebut sudah ada. Cek Tahun Ajaran, Jenis Pembayaran, Jenjang, Tingkat, dan Bulan Berlaku.');
            }
            // Jika error lain, tetap throw error (atau tambahkan penanganan yang lebih spesifik)
            throw $e; 
        }
    }

    /**
     * Menampilkan detail aturan nominal (Opsional).
     */
    public function show(AturNominal $aturNominal)
    {
        $aturNominal->load(['jenisPembayaran', 'tahunAjaran', 'jenjang']);
        return view('atur-nominal.show', compact('aturNominal'));
    }

    /**
     * Menampilkan form edit aturan nominal.
     */
    public function edit(AturNominal $aturNominal)
    {
        $id_jenis_pendaftaran = JenisPembayaran::where('nama_jenis', 'LIKE', '%Pendaftaran%')->value('id');
        
        $data = [
            'aturan' => $aturNominal,
            'jenis_pembayarans' => JenisPembayaran::all(),
            'tahun_ajarans' => TahunAjaran::all(),
            'jenjangs' => MasterJenjang::all(),
            'id_jenis_pendaftaran' => $id_jenis_pendaftaran, // ⬅️ Tambahan untuk Blade
        ];
        return view('atur-nominal.edit', $data);
    }

    /**
     * Memperbarui aturan nominal.
     */
    public function update(Request $request, AturNominal $aturNominal)
    {
        // Cek ID Jenis Pembayaran Pendaftaran yang sebenarnya
        $jenisPendaftaran = JenisPembayaran::where('nama_jenis', 'LIKE', '%Pendaftaran%')->first();
        $id_jenis_pendaftaran = $jenisPendaftaran ? $jenisPendaftaran->id : null;
        $isPendaftaran = $request->input('id_jenis_pembayaran') == $id_jenis_pendaftaran;
        
        $validationRules = [
            'id_jenis_pembayaran' => 'required|exists:jenis_pembayarans,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
            'id_jenjang' => 'required|exists:master_jenjangs,id',
            'tingkat' => ['nullable','integer','min:1'],
            'nominal_normal' => 'required|numeric|min:0',
            'nominal_keluarga' => 'nullable|numeric|min:0|lte:nominal_normal', // ⬅️ BATASI DISKON
        ];

        // LOGIKA VALIDASI BULAN BERLAKU DINAMIS
        if ($isPendaftaran) {
            $validationRules['bulan_berlaku'] = 'required|integer|min:1|max:12';
        } else {
            $validationRules['bulan_berlaku'] = 'nullable|integer|min:1|max:12';
        }

        $request->validate($validationRules);
        
        $data = $request->except(['nominal', 'is_keluarga_price']);
        
        // 1. Pastikan kolom nominal_keluarga menjadi null jika input kosong
        $data['nominal_keluarga'] = $request->nominal_keluarga ?? null;

        // 2. Jika jenisnya Pendaftaran, paksa Tingkat menjadi NULL (Tambahan)
        if ($isPendaftaran) {
            $data['tingkat'] = null; // ⬅️ PERBAIKAN: Pastikan tingkat di-null-kan untuk Pendaftaran
            // Pendaftaran WAJIB punya bulan berlaku (sudah divalidasi 1-12)
        } else {
            // 3. Jika jenisnya Non-Pendaftaran DAN bulan_berlaku kosong/null, 
            //    set ke 0 (mengartikan berlaku untuk semua bulan/biaya tunggal).
            if (empty($request->bulan_berlaku)) {
                $data['bulan_berlaku'] = 0; 
            }
            // Jika Non-Pendaftaran DAN bulan_berlaku terisi (1-12), nilainya sudah benar di $data.
        }
        
        try {
            $aturNominal->update($data);
            return redirect()->route('atur-nominal.index')->with('success', 'Nominal berhasil diperbarui.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { 
                return redirect()->back()->withInput()->with('error', 'Gagal: Aturan nominal untuk kombinasi tersebut sudah ada. Cek Tahun Ajaran, Jenis Pembayaran, Jenjang, Tingkat, dan Bulan Berlaku.');
            }
            throw $e; 
        }
    }

    /**
     * Menghapus aturan nominal.
     */
    public function destroy(AturNominal $aturNominal)
    {
        $aturNominal->delete();
        return redirect()->route('atur-nominal.index')->with('success', 'Nominal berhasil dihapus.');
    }

    /**
     * Menduplikasi aturan nominal dari satu tahun ajaran ke tahun ajaran lain.
     */
    public function duplicateRules(Request $request)
    {
        // Menggunakan DB Transaction untuk memastikan semua duplikasi berhasil atau tidak sama sekali
        DB::beginTransaction();

        try {
            $request->validate([
                'id_tahun_ajaran_lama' => 'required|exists:tahun_ajarans,id',
                'id_tahun_ajaran_baru' => 'required|exists:tahun_ajarans,id|different:id_tahun_ajaran_lama',
            ]);

            $idLama = $request->id_tahun_ajaran_lama;
            $idBaru = $request->id_tahun_ajaran_baru;

            $oldRules = AturNominal::where('id_tahun_ajaran', $idLama)->get();
            $duplicatesCount = 0;

            foreach ($oldRules as $rule) {
                // Cek apakah aturan untuk tahun baru sudah ada (berdasarkan kunci unik)
                $exists = AturNominal::where('id_jenis_pembayaran', $rule->id_jenis_pembayaran)
                    ->where('id_tahun_ajaran', $idBaru)
                    ->where('id_jenjang', $rule->id_jenjang)
                    // Menggunakan where('tingkat', $rule->tingkat) akan menangani NULL dan non-NULL dengan benar
                    ->where('tingkat', $rule->tingkat) 
                    ->where('bulan_berlaku', $rule->bulan_berlaku)
                    ->exists();

                if (!$exists) {
                    $newRule = $rule->replicate(); // Duplikasi objek rule (semua atribut kecuali ID)
                    $newRule->id_tahun_ajaran = $idBaru; // Ganti tahun ajaran ke yang baru
                    $newRule->save();
                    $duplicatesCount++;
                }
            }

            DB::commit(); // Commit transaksi jika semua berhasil

            return redirect()->route('atur-nominal.index')
                ->with('success', "✅ Berhasil menduplikasi **$duplicatesCount** aturan dari Tahun Ajaran lama ke Tahun Ajaran baru.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Rollback jika validasi gagal
            return redirect()->back()->withErrors($e->errors())->withInput()->with('error', 'Gagal memproses duplikasi. Pastikan input sudah benar.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback jika terjadi error lain
            // Log error untuk debugging di sisi server
            \Log::error("Error Duplikasi Aturan Nominal: " . $e->getMessage()); 
            return redirect()->back()->with('error', 'Terjadi kesalahan saat duplikasi aturan. Tidak ada data yang tersimpan. Cek log server.')->withInput();
        }
    }
}