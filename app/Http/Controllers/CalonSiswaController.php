<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\MasterJenjang; 
use App\Models\TahunAjaran; 
use App\Models\Kelas; 
use App\Models\AturNominal; 
use App\Models\Pembayaran; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalonSiswaController extends Controller
{
    // KRITIS: Definisikan konstanta untuk ID Jenis Pembayaran Pendaftaran
    private const ID_JENIS_PENDAFTARAN = 1; 

    // --- FUNGSI PEMBANTU (Helper Function) ---
    /**
     * Mencari nominal pendaftaran berdasarkan jenjang, TA, dan status keluarga.
     * @param int $idJenjang
     * @param int $idTahunAjaran
     * @param int $isKeluarga (0 = Normal, 1 = Keluarga/Diskon)
     * @return int|null Nominal harga atau null jika tidak ditemukan.
     */
    private function getNominalPendaftaran(int $idJenjang, int $idTahunAjaran, int $isKeluarga)
    {
        $currentMonth = Carbon::now()->monthName; 

        // Query Dasar: Filter Jenis (via konstanta), TA, Jenjang, dan Harga Keluarga
        $baseQuery = AturNominal::where('id_jenis_pembayaran', self::ID_JENIS_PENDAFTARAN) 
                              ->where('id_jenjang', $idJenjang)
                              ->where('id_tahun_ajaran', $idTahunAjaran)
                              ->where('is_keluarga_price', $isKeluarga); // <-- KUNCI FILTER

        // 1. Coba ambil nominal yang BULAN BERLAKU-nya sesuai dengan bulan saat ini
        $nominal = (clone $baseQuery)->where('bulan_berlaku', $currentMonth)->first();

        if ($nominal) {
            return $nominal->nominal;
        }

        // 2. Jika tidak ditemukan, ambil nominal pertama (default) untuk konfigurasi ini
        $nominalDefault = (clone $baseQuery)->first();

        if ($nominalDefault) {
            return $nominalDefault->nominal;
        }

        // 3. Jika TIDAK DITEMUKAN, kembalikan NULL
        return null;
    }
    // ------------------------------------------

    /**
     * Menampilkan formulir pendaftaran siswa baru.
     */
    public function register()
    {
        $jenjangs = MasterJenjang::all();
        $tahun_ajaran_aktif = TahunAjaran::where('is_aktif', true)->first();
        
        if (!$tahun_ajaran_aktif) {
            return view('errors.custom', ['message' => 'Tidak ada Tahun Ajaran Aktif. Pendaftaran ditutup.']);
        }

        $defaultJenjang = MasterJenjang::first(); 
        $nominalPendaftaran = 0; 

        if ($defaultJenjang && $tahun_ajaran_aktif) {
            // Panggil helper dengan isKeluarga = 0 (Harga Normal) untuk display
            $nominalPendaftaran = $this->getNominalPendaftaran(
                $defaultJenjang->id, 
                $tahun_ajaran_aktif->id,
                0 // Harga Normal
            );
            
            if ($nominalPendaftaran === null) {
                 $nominalPendaftaran = 0; 
                 session()->flash('error', 'PERINGATAN: Biaya pendaftaran untuk jenjang default belum dikonfigurasi. Pendaftaran tidak dapat diproses.');
            }
        } else {
            session()->flash('error', 'PERINGATAN: Tidak ada Jenjang Pendidikan yang terdaftar.');
        }

        return view('calon_siswa.register', compact('jenjangs', 'tahun_ajaran_aktif', 'nominalPendaftaran'));
    }

    /**
     * Memproses data formulir pendaftaran dan membuat tagihan.
     */
    public function store(Request $request)
    {
        // Tambahkan validasi untuk is_keluarga dan metode_pembayaran
        $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'id_jenjang' => 'required|exists:master_jenjangs,id',
            'email_wali' => 'required|email|unique:calon_siswas,email_wali',
            'nama_wali_murid' => 'required|string|max:255',
            'telp_wali_murid' => 'required|string|max:15',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id', 
            'is_keluarga' => 'sometimes|boolean', 
            'metode_pembayaran' => 'required|in:online,manual', // <-- VALIDASI BARU
        ]);

        $isKeluarga = $request->input('is_keluarga', 0); 
        $metodePembayaran = $request->input('metode_pembayaran'); // <-- DATA BARU
        
        // A. Coba ambil biaya pendaftaran sesuai Pilihan User (Termasuk Diskon)
        $biayaPendaftaran = $this->getNominalPendaftaran(
            $request->id_jenjang, 
            $request->id_tahun_ajaran, 
            $isKeluarga 
        );

        // B. Fallback: Jika User minta harga keluarga (1) tapi tidak ada, coba ambil harga normal (0)
        if (($biayaPendaftaran === null || $biayaPendaftaran == 0) && $isKeluarga == 1) {
             $biayaPendaftaran = $this->getNominalPendaftaran(
                $request->id_jenjang, 
                $request->id_tahun_ajaran, 
                0 // Harga Normal (Fallback)
             );
        }

        if ($biayaPendaftaran === null || $biayaPendaftaran == 0) {
            // C. Jika tetap tidak ditemukan, hentikan proses.
            Log::error("Pendaftaran Gagal: Nominal belum dikonfigurasi untuk Jenjang {$request->id_jenjang}");
            return redirect()->back()->with('error', 'Gagal memproses pendaftaran. Biaya pendaftaran belum dikonfigurasi di sistem admin.')->withInput();
        }
        
        try {
            DB::beginTransaction();

            // 1. Buat Data Calon Siswa
            $calonSiswa = CalonSiswa::create([
                'id_jenjang' => $request->id_jenjang,
                'id_tahun_ajaran' => $request->id_tahun_ajaran,
                'nama_siswa' => $request->nama_siswa,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'nama_wali_murid' => $request->nama_wali_murid,
                'telp_wali_murid' => $request->telp_wali_murid,
                'email_wali' => $request->email_wali,
                'amount' => $biayaPendaftaran, // Nominal Dinamis
                'payment_status' => 'Menunggu',
                'approval_status' => 'Diajukan', 
                'metode_pembayaran' => $metodePembayaran, // <-- DATA BARU DISIMPAN
                // Jika Anda ingin menyimpan status keluarga:
                // 'is_keluarga' => $isKeluarga, 
            ]);

            // 2. Buat Tagihan Pendaftaran
            $orderId = 'CS-' . $calonSiswa->id . '-' . time();
            $tagihan = Tagihan::create([
                'calon_siswa_id' => $calonSiswa->id,
                'id_tahun_ajaran' => $calonSiswa->id_tahun_ajaran,
                'id_jenis_pembayaran' => self::ID_JENIS_PENDAFTARAN, 
                'tanggal_jatuh_tempo' => now()->addDays(2), 
                'total_tagihan' => $biayaPendaftaran,
                'status' => 'Belum Bayar',
                'midtrans_order_id' => $orderId,
                'metode_pembayaran' => $metodePembayaran, // Simpan metode pembayaran di Tagihan juga (opsional, tapi disarankan)
            ]);
            
            // 3. Update Calon Siswa dengan Midtrans Order ID
            $calonSiswa->update(['midtrans_order_id' => $orderId]);

            DB::commit();

            // Redirect ke halaman status pembayaran. Halaman status akan menentukan Midtrans/Manual
            return redirect()->route('calon_siswa.status', [
                'email' => $calonSiswa->email_wali,
                'order_id' => $orderId
            ])->with('success', 'Pendaftaran berhasil. Silakan lanjutkan ke pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Pendaftaran Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses pendaftaran.')->withInput();
        }
    }

    // --- Bagian showStatus, indexAdmin ---
    public function showStatus(Request $request)
    {
        // ... (Kode showStatus)
    }
    
    public function indexAdmin()
    {
        $applicants = CalonSiswa::with('jenjang', 'tahunAjaran', 'tagihan')
            ->where('payment_status', '=', 'Lunas')
            ->where('approval_status', '=', 'Diajukan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $kelas = Kelas::all(); 
        
        return view('admin.calon_siswa.index', compact('applicants', 'kelas'));
    }

    /**
     * Logika untuk Menyetujui Pendaftar dan memindahkannya ke tabel Siswa.
     */
    public function approve(Request $request, CalonSiswa $calonSiswa)
    {
        $request->validate([
            'nisn' => 'required|string|max:15|unique:siswas,nisn', 
            'id_kelas' => 'required|exists:kelas,id', 
        ]);

        if ($calonSiswa->payment_status !== 'Lunas') {
            return redirect()->back()->with('error', 'Pembayaran pendaftar ini belum lunas.');
        }

        try {
            DB::beginTransaction();
            
            // Dapatkan Tagihan Pendaftaran
            $tagihanPendaftaran = $calonSiswa->tagihan;

            // 1. Catat Pembayaran ke tabel 'pembayarans' (Pencatatan Akuntansi)
            if ($tagihanPendaftaran && $tagihanPendaftaran->status === 'Lunas') {
                // Pastikan belum ada catatan pembayaran untuk tagihan ini (mencegah duplikasi)
                $isPaymentRecorded = Pembayaran::where('id_tagihan', $tagihanPendaftaran->id)->exists();

                if (!$isPaymentRecorded) {
                    Pembayaran::create([
                        'id_tagihan' => $tagihanPendaftaran->id,
                        'id_siswa' => null, // Calon Siswa belum memiliki ID Siswa aktif
                        'id_admin' => auth()->id() ?? 1, // ID Admin yang melakukan approval
                        'nominal_bayar' => $tagihanPendaftaran->total_tagihan,
                        'tanggal_bayar' => Carbon::now()->toDateString(),
                        'metode_pembayaran' => 'Midtrans (Pendaftaran)', 
                    ]);
                }
            }
            
            // 2. Tambahkan Siswa ke Tabel 'siswas'
            $userIdWali = 1; // Ganti ini jika Anda memiliki logic pembuatan user
            Siswa::create([
                'id_user' => $userIdWali, 
                'id_kelas' => $request->id_kelas,
                'nisn' => $request->nisn, 
                'nama_siswa' => $calonSiswa->nama_siswa,
                'tempat_lahir' => $calonSiswa->tempat_lahir,
                'tanggal_lahir' => $calonSiswa->tanggal_lahir,
                'jenis_kelamin' => $calonSiswa->jenis_kelamin,
                'agama' => $calonSiswa->agama,
                'status_aktif' => 'Aktif', 
                'nama_wali_murid' => $calonSiswa->nama_wali_murid,
                'telp_wali_murid' => $calonSiswa->telp_wali_murid,
                // ASUMSI: Mengambil is_keluarga dari CalonSiswa. 
                // Jika kolom 'is_keluarga' tidak ada di calon_siswas, ganti dengan 'is_keluarga' => 0 atau 1 dari request.
                'is_keluarga' => $calonSiswa->is_keluarga ?? 0, 
            ]);
            
            // 3. Update Status Approval di tabel calon_siswas
            $calonSiswa->approval_status = 'Disetujui';
            $calonSiswa->save();

            DB::commit();
            return redirect()->route('admin.calon-siswa.index')->with('success', 'Pendaftar berhasil diaktifkan menjadi Siswa Aktif. NIS/NISN: ' . $request->nisn);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approve Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengaktifkan siswa: ' . $e->getMessage());
        }
    }
}