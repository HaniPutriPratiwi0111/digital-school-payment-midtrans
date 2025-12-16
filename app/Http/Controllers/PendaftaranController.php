<?php

namespace App\Http\Controllers;

use App\Models\CalonSiswa;
use App\Models\Tagihan;
use App\Models\DetailTagihan;
use App\Models\JenisPembayaran;
use App\Models\TahunAjaran;
use App\Models\MasterJenjang;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Midtrans\Config;
use Midtrans\Snap;

class PendaftaranController extends Controller
{
    // PASSWORD DEFAULT WALI MURID
    private const DEFAULT_PASSWORD = 'password12345'; // Konstanta untuk password default

    public function index()
    {
        // Ambil data calon siswa yang belum disetujui / masih diajukan
        $pendaftar = CalonSiswa::where('approval_status', '!=', 'Disetujui')
                        ->latest()
                        ->paginate(10);

        return view('pendaftar.index', compact('pendaftar'));
    }

    public function showRegistrationForm()
    {
        $error_message = null;

        // 1. Cek Jenis Pembayaran Pendaftaran
        $jenis_pendaftaran = JenisPembayaran::where('nama_jenis', 'like', '%pendaftaran%')->first();
        if (!$jenis_pendaftaran) {
            $error_message = 'Pendaftaran saat ini belum dibuka karena informasi jenis pembayaran pendaftaran belum tersedia';
        }

        // 2. Cek Tahun Ajaran Aktif
        $tahun_ajaran_aktif = TahunAjaran::where('is_aktif', 1)->first();
        if (!$tahun_ajaran_aktif) {
            $error_message .= ($error_message ? ' dan ' : '') . 'Tahun ajaran aktif juga belum ditentukan.';
        }

        // 3. Ambil Jenjang
        $jenjangs = MasterJenjang::all();

        if ($error_message) {
            // Hanya kirimkan data yang relevan saat ada error
            return view('public.pendaftaran', [
                'error_message' => $error_message,
                'jenis_pendaftaran' => null,
                'tahun_ajaran_aktif' => null,
                'jenjangs' => $jenjangs,
            ]);
        }

        return view('public.pendaftaran', [
            'jenis_pendaftaran' => $jenis_pendaftaran,
            'tahun_ajaran_aktif' => $tahun_ajaran_aktif,
            'jenjangs' => $jenjangs,
            'error_message' => null
        ]);
    }

    public function store(Request $request)
    {
        $email_user = $request->email;

        $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nama_wali_murid' => 'required|string|max:255',
            'telp_wali_murid' => 'required|string|min:10|max:15',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'is_keluarga' => 'required|boolean',
            'id_jenjang' => 'required|exists:master_jenjangs,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
            'id_jenis_pembayaran_pendaftaran' => 'required|exists:jenis_pembayarans,id',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'agama' => 'required|string|max:50',
        ]);

        // Cek data duplikat calon siswa
        $duplicate = CalonSiswa::where('nama_siswa', $request->nama_siswa)
            ->where('tanggal_lahir', $request->tanggal_lahir)
            ->where('tempat_lahir', $request->tempat_lahir)
            ->first();

        if ($duplicate) {
            if ($duplicate->payment_status == 'Menunggu' || $duplicate->payment_status == 'Pending') {
                return redirect()->route('pendaftaran.sukses', ['order_id' => $duplicate->midtrans_order_id])
                    ->with('warning', 'Pendaftaran sudah ada dan menunggu pembayaran.');
            }
            return back()->with('error', 'Data pendaftaran sudah terdaftar dan selesai. Hubungi admin jika ada kesalahan.');
        }

        DB::beginTransaction();

        try {
            // Ambil nominal
            $aturNominal = JenisPembayaran::find($request->id_jenis_pembayaran_pendaftaran)
                ->aturNominals()
                ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                ->where('id_jenjang', $request->id_jenjang)
                ->first();

            if (!$aturNominal) {
                throw new \Exception('Nominal pembayaran pendaftaran tidak ditemukan untuk jenjang & tahun ajaran ini.');
            }

            // Pilih nominal sesuai status keluarga
            $nominal = $request->is_keluarga
                ? ($aturNominal->nominal_keluarga ?? $aturNominal->nominal_normal)
                : $aturNominal->nominal_normal;

            $orderId = 'REG-' . strtoupper(uniqid());

            // Buat / ambil akun user wali
            $user_wali = User::firstOrCreate(
                ['email' => $email_user],
                [
                    'name' => $request->nama_siswa, // <<< ubah ke nama siswa
                    'password' => Hash::make(self::DEFAULT_PASSWORD),
                ]
            );

            $user_wali->assignRole('Orang Tua');

            // Simpan calon siswa
            $calon = CalonSiswa::create([
                'id_jenjang' => $request->id_jenjang,
                'id_tahun_ajaran' => $request->id_tahun_ajaran,
                'nama_siswa' => $request->nama_siswa,
                'nama_wali_murid' => $request->nama_wali_murid,
                'email' => $request->email,
                'telp_wali_murid' => $request->telp_wali_murid,
                'is_keluarga' => $request->is_keluarga,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'id_user_wali' => $user_wali->id,
                'amount' => $nominal,
                'payment_status' => 'Menunggu',
                'approval_status' => 'Diajukan',
                'midtrans_order_id' => $orderId,
            ]);

            $tagihan = Tagihan::create([
                'calon_siswa_id' => $calon->id,
                'id_tahun_ajaran' => $request->id_tahun_ajaran,
                'id_jenis_pembayaran' => $request->id_jenis_pembayaran_pendaftaran,
                'total_tagihan' => $nominal,
                'nominal_tagihan' => $nominal, // 🆕 wajib diisi
                'status' => 'Belum Bayar',
                'midtrans_order_id' => $orderId
            ]);

            // Detail tagihan
            DetailTagihan::create([
                'id_tagihan' => $tagihan->id,
                'id_jenis_pembayaran' => $request->id_jenis_pembayaran_pendaftaran,
                'deskripsi' => 'Biaya Pendaftaran',
                'nominal_unit' => $nominal,
                'qty' => 1,
                'subtotal' => $nominal,
            ]);

            DB::commit();

            return redirect()->route('pendaftaran.sukses', ['order_id' => $orderId])
                ->with('success', 'Pendaftaran berhasil. Silakan selesaikan pembayaran.')
                ->with('default_password', self::DEFAULT_PASSWORD);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function success($order_id)
    {
        // Ambil data calon siswa berdasarkan order_id
        $calon = CalonSiswa::where('midtrans_order_id', $order_id)->firstOrFail();

        // Ambil info akun wali
        $user_wali = User::find($calon->id_user_wali);

        // ============================================
        // 🔥 FIX TERPENTING: Fallback Customer Details
        // ============================================

        $customer_name = $calon->nama_wali_murid 
            ?? optional($user_wali)->name 
            ?? 'Orang Tua';

        $customer_email = optional($user_wali)->email 
            ?? $calon->email
            ?? 'noemail@domain.com';   // fallback wajib agar Midtrans tidak error

        $customer_phone = $calon->telp_wali_murid 
            ?? '081234567890';         // fallback agar tidak NULL

        // ============================================
        // 🔥 Jika salah satu data ini null → Midtrans error
        // Sekarang *SELALU* terisi dan aman
        // ============================================

        $snapToken = null;

        if ($calon->payment_status == 'Menunggu' || $calon->payment_status == 'Pending') {

            // Konfigurasi Midtrans
            Config::$serverKey     = config('midtrans.server_key');
            Config::$isProduction  = config('midtrans.is_production');
            Config::$isSanitized   = true;
            Config::$is3ds         = true;

            // ============================================
            // 🔥 PARAMETER MIDTRANS SUDAH 100% AMAN
            // ============================================
            $params = [
                'transaction_details' => [
                    'order_id'     => $calon->midtrans_order_id,
                    'gross_amount' => (int) $calon->amount,
                ],
                'customer_details' => [
                    'first_name' => $customer_name,
                    'email'      => $customer_email,
                    'phone'      => $customer_phone,
                ],
            ];

            // Generate Snap Token
            try {
                $snapToken = Snap::getSnapToken($params);
            } catch (\Exception $e) {
                // Jika Midtrans error, jangan crash halaman
                $snapToken = null;
            }
        }

        return view('public.pendaftaran_sukses', compact('calon', 'user_wali', 'snapToken'));
    }


}