<?php

namespace App\Http\Controllers; 

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\CalonSiswa; 
use App\Models\JenisPembayaran;
use App\Models\MasterJenjang;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\SiswaKelasTahun; 
use App\Models\AturNominal;    
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Pagination\LengthAwarePaginator;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        // DEFAULTS & INPUT FILTER
        $jenjangSD = MasterJenjang::where('nama_jenjang', 'SD')->first();
        
        // Cari Kelas '1 A' yang terkait dengan Jenjang SD (ID Jenjang SD)
        // Asumsi: Nama kelas mengandung '1 A' ATAU tingkat adalah '1' dan nama kelas adalah 'A'
        $kelas1A = null;
        if ($jenjangSD) {
            $kelas1A = Kelas::where('id_jenjang', $jenjangSD->id)
                            ->where('tingkat', '1') // Anggap 1 adalah tingkat kelas
                            ->where('nama_kelas', 'A') // Anggap A adalah nama kelas
                            ->first();

            // Alternatif pencarian jika struktur kelas berbeda (misalnya: 'SD - 1 A')
            if (!$kelas1A) {
                 $kelas1A = Kelas::where('id_jenjang', $jenjangSD->id)
                                 ->where(function($query) {
                                     $query->where('tingkat', '1')->where('nama_kelas', 'A');
                                 })->first();
            }
        }
        
        // Tentukan filter yang akan digunakan, ambil dari request atau set default
        $jenjangDefaultId = $jenjangSD ? $jenjangSD->id : null;
        $kelasDefaultId = $kelas1A ? $kelas1A->id : null;

        // Jika user tidak memilih filter, gunakan default
        $jenjangFilter = $request->has('jenjang') ? $request->input('jenjang') : $jenjangDefaultId;
        $kelasFilter = $request->has('kelas') ? $request->input('kelas') : $kelasDefaultId;

        // Jika user memilih 'Semua Jenjang' (value="0") atau 'Semua Kelas' (value="0"), set filter menjadi null
        if ($jenjangFilter === '0') $jenjangFilter = null;
        if ($kelasFilter === '0') $kelasFilter = null;
        
        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();

        // Fungsi bantu bikin paginator kosong
        $emptyPaginator = function() {
            return new LengthAwarePaginator([], 0, 10);
        };

        // Jika Tahun Ajaran belum ada
        if (!$tahunAjaranAktif) {
            return view('tagihan.index', [
                'siswas' => $emptyPaginator(),
                'tagihans' => $emptyPaginator(),
                'tahunAjaran' => TahunAjaran::all(),
                'jenisPembayaran' => JenisPembayaran::all(),
                'kelas' => Kelas::all(),
                'jenjangs' => MasterJenjang::all(),
                'siswaModal' => collect(),
                'errorMessage' => 'Tahun Ajaran belum diinput oleh Administrator. Silakan buat Tahun Ajaran terlebih dahulu.',
                'jenjangFilter' => $jenjangFilter,
                'kelasFilter' => $kelasFilter
            ]);
        }

        // Jika Jenjang belum ada
        if (MasterJenjang::count() == 0) {
            return view('tagihan.index', [
                'siswas' => $emptyPaginator(),
                'tagihans' => $emptyPaginator(),
                'tahunAjaran' => TahunAjaran::all(),
                'jenisPembayaran' => JenisPembayaran::all(),
                'kelas' => Kelas::all(),
                'jenjangs' => MasterJenjang::all(),
                'siswaModal' => collect(),
                'errorMessage' => 'Data Jenjang belum diinput.'
            ]);
        }

        // Jika Kelas belum ada
        if (Kelas::count() == 0) {
            return view('tagihan.index', [
                'siswas' => $emptyPaginator(),
                'tagihans' => $emptyPaginator(),
                'tahunAjaran' => TahunAjaran::all(),
                'jenisPembayaran' => JenisPembayaran::all(),
                'kelas' => Kelas::all(),
                'jenjangs' => MasterJenjang::all(),
                'siswaModal' => collect(),
                'errorMessage' => 'Data Kelas belum diinput.'
            ]);
        }
        
        $siswasQuery = Siswa::with('kelasAktif.kelas.jenjang')
            ->where('status_aktif', 'Aktif')
            ->whereHas('kelasAktif', function($q) use ($tahunAjaranAktif) {
                $q->where('id_tahun_ajaran', $tahunAjaranAktif->id);
            });

        // Filter kelas
        if ($kelasFilter) {
            $siswasQuery->whereHas('kelasAktif', function($q) use ($kelasFilter) {
                $q->where('id_kelas', $kelasFilter);
            });
        }

        // Filter jenjang
        if ($jenjangFilter) {
            $siswasQuery->whereHas('kelasAktif.kelas', function($q) use ($jenjangFilter) {
                $q->where('id_jenjang', $jenjangFilter);
            });
        }

        $siswas = $siswasQuery->orderBy('nama_siswa')->paginate(10)->withQueryString(); 
        // Tambahkan withQueryString() agar pagination membawa parameter filter
        // TAGIHAN LIST
        $tagihans = Tagihan::with(['siswa', 'jenisPembayaran', 'tahunAjaran'])
            ->latest()
            ->paginate(10); 

        // DATA TAMBAHAN 
        $tahunAjaran = TahunAjaran::all();
        $jenisPembayaran = JenisPembayaran::all();
        $kelas = Kelas::with('jenjang')->get();
        $jenjangs = MasterJenjang::all();

        // Siswa modal 
        $siswaModal = Siswa::with('kelasAktif.kelas.jenjang')
            ->where('status_aktif', 'Aktif')
            ->whereHas('kelasAktif', function($q) use ($tahunAjaranAktif) {
                $q->where('id_tahun_ajaran', $tahunAjaranAktif->id);
            })
            ->orderBy('nama_siswa')
            ->get();

        return view('tagihan.index', compact(
            'siswas',
            'tagihans',
            'tahunAjaran',
            'jenisPembayaran',
            'kelas',
            'jenjangs',
            'siswaModal',
            'jenjangFilter', 
            'kelasFilter' 
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_siswa' => 'required|exists:siswas,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
            'id_jenis_pembayaran' => 'required|exists:jenis_pembayarans,id',
            'bulan_tagihan' => 'nullable|integer|min:1|max:12',
            'tanggal_jatuh_tempo' => 'required|date',
            'status' => 'required|string|in:Belum Bayar,Lunas Partial,Lunas,Batal'
        ]);

        $siswa = Siswa::with(['kelasAktif.kelas.jenjang'])->findOrFail($request->id_siswa);
        $isKeluarga = $siswa->is_keluarga == true;

        if (!$siswa->kelasAktif || !$siswa->kelasAktif->kelas) {
            return back()->with('error', 'Kelas aktif untuk siswa tidak ditemukan.');
        }

        $kelasAktif = $siswa->kelasAktif->kelas;
        $jenjangId = $kelasAktif->jenjang->id;
        $tingkat = $kelasAktif->tingkat;

        // Pengecekan duplikasi tagihan bulanan
        if ($request->bulan_tagihan) {
            $existingTagihan = Tagihan::where('id_siswa', $siswa->id)
                ->where('id_jenis_pembayaran', $request->id_jenis_pembayaran)
                ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                ->where('bulan_tagihan', $request->bulan_tagihan)
                ->first();

            if ($existingTagihan) {
                return back()->with('error', 'Tagihan untuk bulan/tahun ini sudah ada untuk siswa ini.');
            }
        }

        $aturan = AturNominal::where('id_jenis_pembayaran', $request->id_jenis_pembayaran)
            ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
            ->where('id_jenjang', $jenjangId)
            ->where(function($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)->orWhereNull('tingkat');
            })
            ->first();

        if (!$aturan) {
            return back()->with('error', 'Aturan nominal tidak ditemukan untuk jenjang siswa.');
        }

        $nominalNormal    = $aturan->nominal_normal;
        $nominalKeluarga = $aturan->nominal_keluarga;

        $nominalFinal = $nominalNormal;
        if ($isKeluarga && !is_null($nominalKeluarga) && $nominalKeluarga > 0) {
            $nominalFinal = $nominalKeluarga;
        }

        $nominalDiskon = $nominalNormal - $nominalFinal;

        Tagihan::create([
            'id_siswa' => $siswa->id,
            'id_tahun_ajaran' => $request->id_tahun_ajaran,
            'id_jenis_pembayaran' => $request->id_jenis_pembayaran,
            'id_atur_nominal' => $aturan->id,
            'bulan_tagihan' => $request->bulan_tagihan,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'nominal_tagihan' => $nominalFinal,
            'nominal_diskon' => $nominalDiskon,
            'is_harga_keluarga_applied' => $isKeluarga,
            'total_tagihan' => $nominalFinal,
            'status' => $request->status,
            'midtrans_order_id' => 'INV-' . strtoupper(uniqid()),
        ]);

        return back()->with('success', 'Tagihan berhasil ditambahkan!');
    }


    public function tagihanAnak()
    {
        $user = auth()->user();

        if ($user->hasRole('Siswa')) {
            $siswaIds = [$user->siswa_id];
        } else {
            $siswaIds = $user->siswa->pluck('id')->toArray();
        }

        $tagihans = Tagihan::with(['siswa', 'jenisPembayaran'])
            ->whereIn('id_siswa', $siswaIds)
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->paginate(10); 

        return view('tagihan.anak', compact('tagihans'));

    }

    public function show($id, Request $request)
    {
        if (!Auth::user()->hasAnyRole(['Super Administrator', 'Bendahara'])) {
            abort(403);
        }

        $siswa = Siswa::with('kelasAktif.kelas.jenjang')->findOrFail($id);

        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();

        // Ambil tahun ajaran dari query string, atau default ke aktif jika ada
        $selectedTahunId = $request->tahun_ajaran_id ?? ($tahunAjaranAktif->id ?? null);

        $tagihans = Tagihan::with(['jenisPembayaran', 'tahunAjaran'])
            ->where('id_siswa', $siswa->id)
            ->when($selectedTahunId, function($q) use ($selectedTahunId) {
                $q->where('id_tahun_ajaran', $selectedTahunId);
            })
            ->orderByDesc('created_at')
            ->get();

        $jenisPembayaran = JenisPembayaran::all();
        $tahunAjaran = TahunAjaran::all();

        // Pastikan variabel $selectedTahunId selalu dikirim ke view
        return view('tagihan.show', compact(
            'siswa', 'tagihans', 'jenisPembayaran', 'tahunAjaran', 'selectedTahunId'
        ));
    }

    public function edit(Tagihan $tagihan)
    {
        $tahunAjaran = TahunAjaran::all();
        $jenisPembayaran = JenisPembayaran::all();
        return view('tagihan.edit', compact('tagihan', 'tahunAjaran', 'jenisPembayaran'));
    }

    public function update(Request $request, Tagihan $tagihan)
    {
        $request->validate([
            'id_jenis_pembayaran' => 'required|exists:jenis_pembayarans,id',
            'id_tahun_ajaran'     => 'required|exists:tahun_ajarans,id',
            'bulan_tagihan'       => 'nullable|integer|min:1|max:12',
            // 'tahun_tagihan'       => 'nullable|integer',
            'tanggal_jatuh_tempo' => 'required|date',

            'total_tagihan'       => 'required|numeric|min:0',
            'nominal_diskon'      => 'nullable|numeric|min:0',

            'status' => 'required|string|in:Belum Bayar,Lunas Partial,Lunas,Batal',
        ]);

        $tagihan->update([
            'id_jenis_pembayaran' => $request->id_jenis_pembayaran,
            'id_tahun_ajaran'     => $request->id_tahun_ajaran,
            'bulan_tagihan'       => $request->bulan_tagihan,
            // 'tahun_tagihan'       => $request->tahun_tagihan,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,

            'total_tagihan'       => $request->total_tagihan,
            'nominal_tagihan'     => $request->total_tagihan,
            'nominal_diskon'      => $request->nominal_diskon ?? 0,

            'status'              => $request->status,
        ]);

        return redirect()->route('tagihan.show', $tagihan->id_siswa)
                        ->with('success', 'Tagihan berhasil diperbarui!');
    }

    public function destroy(Tagihan $tagihan)
    {
        $tagihan->delete();
        return back()->with('success', 'Tagihan berhasil dihapus!');
    }

    public function showWali(Tagihan $tagihan)
    {
        if (!Auth::user()->hasRole('Orang Tua') && !Auth::user()->hasRole('Siswa')) {
            abort(403, 'Akses ditolak. Hanya wali murid atau siswa yang dapat melihat tagihan ini.');
        }

        $siswa_ids = collect();

        try {
            $siswa_ids = $siswa_ids->merge(Auth::user()->siswa()->pluck('id'));
        } catch (\Exception $e) {}

        if (Auth::user()->hasRole('Siswa')) {
            $siswa_ids = $siswa_ids->merge(Siswa::where('id_user', Auth::id())->pluck('id'));
        }

        $allowed_ids = $siswa_ids->unique()->toArray();

        if (!in_array($tagihan->id_siswa, $allowed_ids)) {
            return redirect()->route('tagihan.anak')->with('error', 'Tagihan ini bukan milik Anda.');
        }

        return redirect()->route('midtrans.payPage', $tagihan->id);
    }

    public function getPaymentToken(Request $request, $orderId)
    {
        // ASUMSI: Konfigurasi Midtrans sudah dilakukan
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $tagihan = Tagihan::where('midtrans_order_id', $orderId)->first();

        if (!$tagihan) {
            return response()->json(['error' => 'Tagihan tidak ditemukan'], 404);
        }
        
        if ($tagihan->status === 'Lunas') {
            return response()->json(['error' => 'Tagihan sudah lunas'], 400);
        }

        // Tentukan detail siswa/calon siswa
        $student = $tagihan->siswa ?? $tagihan->calonSiswa;
        if (!$student) {
            return response()->json(['error' => 'Detail siswa/pendaftar tidak ditemukan'], 404);
        }

        $sisaTagihan = $tagihan->total_tagihan - $tagihan->pembayarans()->sum('total_bayar');
        if ($sisaTagihan <= 0) {
            return response()->json(['error' => 'Tagihan sudah lunas (kesalahan data status)'], 400);
        }
        
        // 1. DETAIL TRANSAKSI
        $transaction_details = [
            'order_id' => $tagihan->midtrans_order_id,
            'gross_amount' => $sisaTagihan,
        ];

        // 2. ITEM DETAIL
        $item_details[] = [
            'id' => $tagihan->id,
            'price' => $sisaTagihan,
            'quantity' => 1,
            'name' => 'Pembayaran ' . ($tagihan->jenisPembayaran ? $tagihan->jenisPembayaran->name : 'Tagihan'),
        ];
        
        // 3. CUSTOMER DETAIL
        // Ambil data yang paling relevan dari CalonSiswa atau Siswa
        $customer_details = [
            'first_name' => $student->nama_siswa,
            'email' => $student->email_wali ?? 'no-email@example.com',
            'phone' => $student->telp_wali_murid ?? '08123456789',
        ];
        
        $payload = [
            'transaction_details' => $transaction_details,
            'item_details' => $item_details,
            'customer_details' => $customer_details,
        ];
        
        try {
            $snapToken = Snap::getSnapToken($payload);
            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error("Midtrans Snap Token Error: " . $e->getMessage());
            return response()->json(['error' => 'Gagal mendapatkan token pembayaran: ' . $e->getMessage()], 500);
        }
    }

    public function storeMassal(Request $request)
    {
        $request->validate([
            'id_kelas'            => 'required|exists:kelas,id',
            'id_jenis_pembayaran' => 'required|exists:jenis_pembayarans,id',
            'id_tahun_ajaran'     => 'required|exists:tahun_ajarans,id',
            'bulan_tagihan'       => 'nullable|integer|min:1|max:12',
            'tanggal_jatuh_tempo' => 'required|date',
        ]);

        $idKelas = $request->id_kelas;
        $idTahunAjaran = $request->id_tahun_ajaran;
        $idJenisPembayaran = $request->id_jenis_pembayaran;

        $siswas = Siswa::whereHas('kelasAktif', function($q) use ($idTahunAjaran, $idKelas){
                $q->where('id_tahun_ajaran', $idTahunAjaran)
                  ->where('id_kelas', $idKelas);
            })
            ->with('kelasAktif.kelas.jenjang')
            ->where('status_aktif', 'Aktif')
            ->get();

        if ($siswas->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa aktif pada kelas ini.');
        }

        $count = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($siswas as $siswa) {

                if (!$siswa->kelasAktif || !$siswa->kelasAktif->kelas || !$siswa->kelasAktif->kelas->jenjang) {
                    $errors[] = "Kelas atau jenjang tidak ditemukan untuk {$siswa->nama_siswa}.";
                    continue;
                }

                $kelasAktif = $siswa->kelasAktif->kelas;
                $jenjang = $kelasAktif->jenjang;
                $tingkat = $kelasAktif->tingkat;

                // Cek duplikasi
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

                $aturan = AturNominal::where('id_jenis_pembayaran', $idJenisPembayaran)
                    ->where('id_tahun_ajaran', $idTahunAjaran)
                    ->where('id_jenjang', $jenjang->id)
                    ->where(function ($q) use ($tingkat) {
                        $q->where('tingkat', $tingkat)->orWhereNull('tingkat');
                    })
                    ->first();

                if (!$aturan) {
                    $errors[] = "Aturan nominal tidak ditemukan untuk {$siswa->nama_siswa}.";
                    continue;
                }

                $nominal = $aturan->nominal_normal;
                if ($siswa->is_keluarga && $aturan->nominal_keluarga > 0) {
                    $nominal = $aturan->nominal_keluarga;
                }
                $diskon = $aturan->nominal_normal - $nominal;

                Tagihan::create([
                    'id_siswa'                  => $siswa->id,
                    'id_jenis_pembayaran'       => $idJenisPembayaran,
                    'id_tahun_ajaran'           => $idTahunAjaran,
                    'id_atur_nominal'           => $aturan->id,
                    'bulan_tagihan'             => $request->bulan_tagihan,
                    'tanggal_jatuh_tempo'       => $request->tanggal_jatuh_tempo,
                    'nominal_tagihan'           => $nominal,
                    'nominal_diskon'            => $diskon,
                    'is_harga_keluarga_applied' => $siswa->is_keluarga,
                    'total_tagihan'             => $nominal,
                    'status'                    => 'Belum Bayar',
                    'midtrans_order_id'         => 'INV-' . strtoupper(uniqid()),
                ]);

                $count++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan tagihan massal: ' . $e->getMessage());
        }

        if (!empty($errors)) {
            return back()->with('warning', "Berhasil membuat $count tagihan." . implode('', $errors));
        }

        return back()->with('success', "Tagihan massal berhasil dibuat untuk $count siswa.");
    }

    // Nominal tunggal
    public function getNominal($id_siswa, $id_jenis, $id_tahun)
    {
        $siswa = Siswa::with('kelasAktif.kelas.jenjang')->find($id_siswa);

        if (!$siswa || !$siswa->kelasAktif || !$siswa->kelasAktif->kelas || !$siswa->kelasAktif->kelas->jenjang) {
            return response()->json(['nominal' => null, 'diskon' => null]);
        }

        $kelas = $siswa->kelasAktif->kelas;
        $jenjangId = $kelas->jenjang->id;
        $tingkat = $kelas->tingkat;

        $aturan = AturNominal::where('id_jenis_pembayaran', $id_jenis)
            ->where('id_tahun_ajaran', $id_tahun)
            ->where('id_jenjang', $jenjangId)
            ->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)->orWhereNull('tingkat');
            })
            ->orderByRaw("tingkat IS NULL")
            ->first();

        if (!$aturan) {
            return response()->json(['nominal' => null, 'diskon' => null]);
        }

        $nominalFinal = $aturan->nominal_normal;
        if ($siswa->is_keluarga && $aturan->nominal_keluarga > 0) {
            $nominalFinal = $aturan->nominal_keluarga;
        }

        $nominalDiskon = $aturan->nominal_normal - $nominalFinal;

        return response()->json([
            'nominal' => $nominalFinal,
            'diskon' => $nominalDiskon
        ]);
    }

    // Nominal massal
    public function getNominalMassal($id_kelas, $id_jenis, $id_tahun)
    {
        $kelas = Kelas::with('jenjang')->find($id_kelas);

        if (!$kelas || !$kelas->jenjang) {
            return response()->json([
                'nominal' => 0,
                'diskon' => 0,
                'error' => 'Kelas atau jenjang tidak valid'
            ]);
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
            return response()->json([
                'nominal' => 0,
                'diskon' => 0,
                'error' => 'Aturan nominal tidak ditemukan.'
            ]);
        }

        $nominalNormal = $aturan->nominal_normal;
        $nominalKeluarga = $aturan->nominal_keluarga ?? 0;
        $diskon = $nominalNormal - $nominalKeluarga;

        return response()->json([
            'nominal' => $nominalNormal,
            'diskon' => $diskon, // potongan bagi siswa yang punya keluarga
            'error' => null
        ]);
    }


}