<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Models\TahunAjaran;
use App\Models\SiswaKelasTahun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\CalonSiswa;
use App\Models\MasterJenjang;

class SiswaController extends Controller
{
    private const DEFAULT_PASSWORD = 'password12345';

    // =================== CRUD SISWA BIASA ===================
    public function index(Request $request)
    {
        // 1. Set default tahun ajaran aktif jika tidak ada filter
        $tahunAktif = TahunAjaran::where('is_aktif', 1)->first();

        if (!$request->has('id_tahun_ajaran') && $tahunAktif) {
            $request->merge(['id_tahun_ajaran' => $tahunAktif->id]);
        }

        // 2. Set default jenjang dan kelas jika belum ada filter (first load)
        if (!$request->has('id_jenjang') && !$request->has('id_kelas')) {
            $jenjangSD = MasterJenjang::where('nama_jenjang', 'SD')->first();

            if ($jenjangSD) {
                $kelasDefault = Kelas::where('id_jenjang', $jenjangSD->id)
                    ->where('tingkat', 1)
                    ->orderBy('nama_kelas')
                    ->first();

                if ($kelasDefault) {
                    $request->merge([
                        'id_jenjang' => $jenjangSD->id,
                        'id_kelas'   => $kelasDefault->id
                    ]);
                }
            }
        }

        // 3. Query siswa dengan filter tahun ajaran, jenjang, kelas yang ketat
        $query = Siswa::with([
            'kelas.jenjang',
            'user',
            'siswaKelasTahun.kelas.jenjang',
            'siswaKelasTahun.tahunAjaran'
        ]);

        if ($request->id_tahun_ajaran) {
            $query->whereHas('siswaKelasTahun', function ($q) use ($request) {
                $q->where('id_tahun_ajaran', $request->id_tahun_ajaran);
            });
        }

        if ($request->id_jenjang) {
            $query->whereHas('siswaKelasTahun.kelas', function ($q) use ($request) {
                $q->where('id_jenjang', $request->id_jenjang);
            });
        }

        if ($request->id_kelas) {
            $query->whereHas('siswaKelasTahun', function ($q) use ($request) {
                $q->where('id_kelas', $request->id_kelas);
            });
        }

        $siswas = $query
            ->orderBy('nama_siswa')
            ->paginate(10)
            ->withQueryString();

        // 4. Data dropdown kelas, hanya kelas sesuai jenjang filter
        $kelasAll = Kelas::with('jenjang')
            ->when($request->id_jenjang, function ($q) use ($request) {
                $q->where('id_jenjang', $request->id_jenjang);
            })
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        // 5. Ambil data lain untuk dropdown
        $tahunAjarans = TahunAjaran::all();
        $jenjangs = MasterJenjang::all();

        return view('siswa.index', compact(
            'siswas',
            'tahunAjarans',
            'jenjangs',
            'kelasAll',
            'tahunAktif'
        ));
    }

    public function create()
    {
        $kelasAll = Kelas::with('jenjang')->get(); // <-- gunakan nama ini
        $jenjangs = MasterJenjang::all();
        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();
        $tahunAjarans = TahunAjaran::all();

        return view('siswa.create', compact('kelasAll', 'jenjangs', 'tahunAjaranAktif', 'tahunAjarans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|string|unique:siswas,nisn',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|max:50',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'nama_wali_murid' => 'required|string|max:255',
            'telp_wali_murid' => 'required|string|max:20',
            'is_keluarga' => 'nullable|boolean',
            'id_kelas' => 'required|exists:kelas,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id', // <- wajib
        ]);

        DB::beginTransaction();
        try {
            // Buat user wali murid
            $user = User::create([
                'name' => $request->nama_wali_murid,
                'nisn' => $request->nisn,
                'email' => null,
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]);

            $roleOrtu = Role::where('name', 'Orang Tua')->first();
            if ($roleOrtu) $user->assignRole($roleOrtu);

            // Buat Siswa
            $siswa = Siswa::create([
                'id_user' => $user->id,
                'id_tahun_ajaran' => $request->id_tahun_ajaran, // <- ambil dari form
                'nama_siswa' => $request->nama_siswa,
                'nisn' => $request->nisn,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'nama_wali_murid' => $request->nama_wali_murid,
                'telp_wali_murid' => $request->telp_wali_murid,
                'is_keluarga' => $request->boolean('is_keluarga'),
                'status_aktif' => 'Aktif',
            ]);

            // Relasi kelas & tahun ajaran
            SiswaKelasTahun::create([
                'id_siswa' => $siswa->id,
                'id_kelas' => $request->id_kelas,
                'id_tahun_ajaran' => $request->id_tahun_ajaran,
            ]);

            DB::commit();

            return redirect()->route('siswa.index')
                ->with('success', 'Siswa berhasil ditambahkan! Akun wali murid dibuat dengan password default: ' . self::DEFAULT_PASSWORD);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan siswa: ' . $e->getMessage());
        }
    }

    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::with('jenjang')->get();
        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();

        // Ganti 'kelasTahun' menjadi 'siswaKelasTahun'
        $siswa->load('user', 'siswaKelasTahun');

        return view('siswa.edit', compact('siswa', 'kelas', 'tahunAjaranAktif'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'nullable|unique:siswas,nisn,' . $siswa->id,
            'id_kelas' => 'required|exists:kelas,id',
            'nama_wali_murid' => 'required|string',
            'telp_wali_murid' => 'required|string',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'is_keluarga' => 'nullable|boolean',
        ]);

        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();
        if (!$tahunAjaranAktif) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        DB::beginTransaction();
        try {
            // Update User
            $siswa->user->update([
                'name' => $request->nama_siswa,
                'nisn' => $request->nisn,
            ]);

            // Update Siswa
            $siswa->update([
                'nama_siswa' => $request->nama_siswa,
                'nisn' => $request->nisn,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'nama_wali_murid' => $request->nama_wali_murid,
                'telp_wali_murid' => $request->telp_wali_murid,
                'is_keluarga' => $request->has('is_keluarga') ? 1 : 0,
            ]);

            // Update relasi kelas tahun ajaran sesuai model SiswaKelasTahun
            $kelasTahun = SiswaKelasTahun::firstOrCreate(
                ['id_siswa' => $siswa->id, 'id_tahun_ajaran' => $tahunAjaranAktif->id],
                ['id_kelas' => $request->id_kelas]
            );
            $kelasTahun->update(['id_kelas' => $request->id_kelas]);

            DB::commit();
            return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Siswa $siswa)
    {
        DB::beginTransaction();
        try {
            $siswa->user()->delete();
            $siswa->delete();
            DB::commit();
            return redirect()->route('siswa.index')->with('success', 'Siswa dan akun wali murid berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // =================== CALON SISWA ===================
    public function createFromCalon($calonId)
    {
        $calon = CalonSiswa::findOrFail($calonId);

        $jenjangs = MasterJenjang::all();

        $kelasAll = Kelas::with('jenjang')->get();

        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();
        $tahunAjarans = TahunAjaran::all();

        return view('siswa.create', compact(
            'calon',
            'jenjangs',
            'kelasAll',
            'tahunAjaranAktif',
            'tahunAjarans'
        ));
    }

    public function storeFromCalon(Request $request)
    {
        $request->validate([
            'nama_siswa' => 'required|string|max:255',
            'nisn' => 'required|unique:siswas,nisn|string|max:255',
            'id_kelas' => 'required|exists:kelas,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
            'nama_wali_murid' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'telp_wali_murid' => 'required|string',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'is_keluarga' => 'nullable|boolean',
            'calon_id' => 'required|exists:calon_siswas,id'
        ]);

        $calon = CalonSiswa::findOrFail($request->calon_id);
        $tahunAjaranAktif = TahunAjaran::where('is_aktif', 1)->first();
        if (!$tahunAjaranAktif) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        DB::beginTransaction();
        try {
            // Buat User wali murid pakai email calon
            $user = User::create([
                'name' => $request->nama_wali_murid,
                'nisn' => $request->nisn,
                'email' => $request->email,
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]);

            $roleOrtu = Role::where('name', 'Orang Tua')->first();
            if ($roleOrtu) $user->assignRole($roleOrtu);

            // Update id_user_wali di calon siswa
            $calon->id_user_wali = $user->id;
            $calon->approval_status = 'Disetujui';
            $calon->save();

            // Buat Siswa
            $siswa = Siswa::create([
                'id_user' => $user->id,
                'nama_siswa' => $request->nama_siswa,
                'nisn' => $request->nisn,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'nama_wali_murid' => $request->nama_wali_murid,
                'telp_wali_murid' => $request->telp_wali_murid,
                'is_keluarga' => $request->boolean('is_keluarga'),
                'status_aktif' => 'Aktif',
                'id_jenjang' => $calon->id_jenjang, // 🔥 pastikan jenjang ikut tersimpan
            ]);

            // Relasi kelas & tahun ajaran
            SiswaKelasTahun::create([
                'id_siswa' => $siswa->id,
                'id_kelas' => $request->id_kelas,
                'id_tahun_ajaran' => $tahunAjaranAktif->id,
            ]);

            DB::commit();
            return redirect()->route('siswa.index')
                ->with('success', 'Calon siswa berhasil menjadi siswa aktif! Akun orang tua dibuat dengan password default: ' . self::DEFAULT_PASSWORD);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan siswa: ' . $e->getMessage());
        }
    }

    // =================== NAIK KELAS ===================
    public function naikKelas(Siswa $siswa)
    {
        $tahunAktif = TahunAjaran::where('is_aktif', 1)->first();

        if (!$tahunAktif) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif! Buat tahun ajaran aktif dulu.');
        }

        // Ambil kelas aktif siswa sekarang (kelas di tahun ajaran sebelumnya)
        $kelasAktif = $siswa->siswaKelasTahun()
            ->where('id_tahun_ajaran', '<', $tahunAktif->id)
            ->orderByDesc('id_tahun_ajaran')
            ->first();

        if (!$kelasAktif) {
            return back()->with('error', 'Siswa belum memiliki kelas aktif di tahun ajaran sebelumnya.');
        }

        $kelasSekarang = $kelasAktif->kelas;

        // Tentukan kelas selanjutnya
        $kelasBerikutnya = Kelas::where('id_jenjang', $kelasSekarang->id_jenjang)
            ->where('tingkat', $kelasSekarang->tingkat + 1)
            ->first();

        if (!$kelasBerikutnya) {
            return back()->with('error', 'Tidak ada kelas tingkat selanjutnya.');
        }

        // Cek apakah siswa sudah punya relasi di tahun ajaran aktif
        $sudahAda = SiswaKelasTahun::where('id_siswa', $siswa->id)
            ->where('id_tahun_ajaran', $tahunAktif->id)
            ->first();

        if ($sudahAda) {
            return back()->with('error', 'Siswa sudah memiliki kelas di tahun ajaran aktif.');
        }

        // Simpan relasi baru
        SiswaKelasTahun::create([
            'id_siswa' => $siswa->id,
            'id_kelas' => $kelasBerikutnya->id,
            'id_tahun_ajaran' => $tahunAktif->id,
        ]);

        return back()->with('success', 'Siswa berhasil naik kelas!');
    }

    // =================== FORM NAIK KELAS ===================
    public function formNaikKelas()
    {
        $tahunAktif = TahunAjaran::where('is_aktif', 1)->first();
        if (!$tahunAktif) return back()->with('error', 'Tidak ada tahun ajaran aktif.');

        $kelas = Kelas::with('jenjang')
            ->orderBy('id_jenjang')
            ->orderBy('tingkat')
            ->get();

        $tahunSebelumnya = TahunAjaran::where('is_aktif', 0)
            ->orderBy('id', 'desc')
            ->first();

        $jenjangs = MasterJenjang::all(); // <-- gunakan model yang benar

        return view('siswa.naik_kelas', compact('kelas', 'tahunAktif', 'tahunSebelumnya', 'jenjangs'));
    }

    // =================== PROSES NAIK KELAS MASSAL ===================
    public function prosesNaikKelas(Request $request)
    {
        $request->validate([
            'id_kelas_asal' => 'required|exists:kelas,id',
            // Pastikan input array 'naik_kelas' dan 'tinggal_kelas' diterima
            'naik_kelas' => 'nullable|array',
            'tinggal_kelas' => 'nullable|array',
        ]);
        
        $tahunAktif = TahunAjaran::where('is_aktif', 1)->first();
        if (!$tahunAktif) {
            return redirect()->route('siswa.index')->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        DB::beginTransaction();
        try {
            $kelasAsal = Kelas::findOrFail($request->id_kelas_asal);
            $tahunSebelumnya = TahunAjaran::where('is_aktif', 0)->orderBy('id', 'desc')->first();

            // 🛑 TAHAP 1: MEMBUAT PETA SISWA BARU (Siswa ID => Kelas Baru ID)
            $siswaTinggalKelas = $request->input('tinggal_kelas', []);
            $siswaNaikPerKelas = $request->input('naik_kelas', []);

            // Inisialisasi peta Siswa ID => Kelas Baru ID
            $mapSiswaKelasBaru = [];
            
            // 1a. Proses Siswa yang Tinggal Kelas
            // Mereka akan masuk ke kelas asal ($kelasAsal->id) di tahun ajaran aktif
            foreach ($siswaTinggalKelas as $idSiswa) {
                // Pastikan $idSiswa adalah string karena input HTML cenderung mengirim string
                $mapSiswaKelasBaru[(int)$idSiswa] = $kelasAsal->id;
            }

            // 1b. Proses Siswa yang Naik Kelas
            foreach ($siswaNaikPerKelas as $idKelasTujuan => $listIdSiswa) {
                foreach ($listIdSiswa as $idSiswa) {
                    // Pastikan tidak menimpa jika siswa sudah ditandai tinggal kelas
                    if (!isset($mapSiswaKelasBaru[(int)$idSiswa])) {
                        $mapSiswaKelasBaru[(int)$idSiswa] = (int)$idKelasTujuan;
                    }
                }
            }
            // ----------------------------------------------------------------------
            
            // Ambil hanya ID siswa yang benar-benar dicentang/dipetakan
            $idsSiswaDiproses = array_keys($mapSiswaKelasBaru); 
            
            if (empty($idsSiswaDiproses)) {
                return redirect()->route('siswa.index')->with('warning', 'Tidak ada siswa yang dipilih untuk diproses (Naik atau Tinggal Kelas).');
            }

            // 🎯 PERBAIKAN KRUSIAL DI SINI: Ambil data Siswa yang akan di-proses.
            // Kita menggunakan model Siswa untuk mendapatkan data nama siswa
            $siswas = Siswa::whereIn('id', $idsSiswaDiproses)->get();
            
            if ($siswas->isEmpty()) {
                 return redirect()->route('siswa.index')->with('error', 'Data siswa tidak ditemukan untuk ID yang dipilih.');
            }

            $jumlahNaik = 0;
            $jumlahTinggal = 0;
            $jumlahLewat = 0;
            $siswaSudahAda = [];

            // 🛑 TAHAP 2: PROSES PERBARUAN DATA SISWA
            foreach ($siswas as $siswa) { // Iterasi objek Siswa, bukan SiswaKelasTahun lama
                $idSiswa = $siswa->id;
                $kelasBaruId = $mapSiswaKelasBaru[$idSiswa]; // Kelas Tujuan atau Kelas Asal (Tinggal)
                
                // Cek apakah siswa sudah punya relasi di tahun ajaran aktif
                $sudahAda = SiswaKelasTahun::where('id_siswa', $idSiswa)
                    ->where('id_tahun_ajaran', $tahunAktif->id)
                    ->exists();

                if ($sudahAda) {
                    $siswaSudahAda[] = $siswa->nama_siswa;
                    $jumlahLewat++;
                    continue; // lewati siswa ini
                }

                // Simpan relasi baru di tabel pivot SiswaKelasTahun
                SiswaKelasTahun::create([
                    'id_siswa' => $idSiswa,
                    'id_kelas' => $kelasBaruId,
                    'id_tahun_ajaran' => $tahunAktif->id,
                ]);

                // Update kelas aktif di tabel utama Siswa (penting untuk data realtime)
                $siswa->update(['id_kelas' => $kelasBaruId]);

                // Hitung statistik
                if ($kelasBaruId == $kelasAsal->id) {
                    $jumlahTinggal++; // ID Kelas Baru sama dengan ID Kelas Asal
                } else {
                    $jumlahNaik++; // ID Kelas Baru adalah ID Kelas Tujuan yang berbeda
                }
            }

            DB::commit();

            // Logika pesan sukses/warning
            $totalProses = $jumlahNaik + $jumlahTinggal;
            if ($totalProses === 0 && $jumlahLewat > 0) {
                // Kasus: Semua siswa sudah ada kelas sebelumnya
                $pesan = "Tidak ada siswa yang diproses karena semua siswa yang dicentang sudah memiliki kelas di tahun ajaran aktif: "
                         . implode(', ', $siswaSudahAda);
                $tipe = 'warning';
            } elseif ($totalProses > 0) {
                // Kasus: Ada siswa yang berhasil diproses
                $pesan = "$jumlahNaik siswa berhasil naik kelas ke kelas tujuan yang dipilih, $jumlahTinggal siswa tinggal kelas.";
                if (!empty($siswaSudahAda)) {
                    $pesan .= " ($jumlahLewat siswa dilewati karena sudah ada kelas di tahun aktif: " . implode(', ', $siswaSudahAda) . ")";
                }
                $tipe = 'success';
            } else {
                 // Kasus: Tidak ada siswa yang dicentang
                 $pesan = "Tidak ada siswa yang dipilih untuk diproses.";
                 $tipe = 'warning';
            }

            return redirect()->route('siswa.index')->with($tipe, $pesan);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log error lebih detail jika perlu
            // \Log::error('Gagal proses naik kelas: ' . $e->getMessage()); 
            return redirect()->route('siswa.index')->with('error', 'Gagal naik kelas: ' . $e->getMessage());
        }
    }

    // =================== DAFTAR SISWA UNTUK AJAX ===================
    public function daftarSiswa($id_kelas)
    {
        $tahunSebelumnya = TahunAjaran::where('is_aktif', 0)->orderBy('id','desc')->first();

        if (!$tahunSebelumnya) {
            return response()->json([], 200);
        }

        $siswas = SiswaKelasTahun::where('id_kelas', $id_kelas)
                    ->where('id_tahun_ajaran', $tahunSebelumnya->id)
                    ->with('siswa')  // pastikan relasi 'siswa' ada di model SiswaKelasTahun
                    ->get()
                    ->map(function($item){
                        return [
                            'id' => $item->siswa->id,
                            'nama_siswa' => $item->siswa->nama_siswa,
                        ];
                    });

        return response()->json($siswas);
    }

    public function kelasDenganSiswa(Request $request, $id_tahun)
    {
        $idJenjang = $request->query('jenjang'); // Nilai yang diterima (bisa ID atau string slug/nama)

        // 1. Jika nilai yang diterima bukan numerik, cari ID Jenjang dari MasterJenjang
        if ($idJenjang && !is_numeric($idJenjang)) {
            // Asumsi: Anda mengirim 'SD_ID' atau semacamnya dari form
            $masterJenjang = MasterJenjang::where('id', $idJenjang)
                ->orWhere('nama_jenjang', $idJenjang)
                ->orWhere('slug', $idJenjang)
                ->first();
            
            if ($masterJenjang) {
                $idJenjang = $masterJenjang->id; // Ganti $idJenjang dengan ID numeriknya
            } else {
                // Jika tidak ditemukan, set ke null untuk tidak memfilter
                $idJenjang = null;
            }
        }
        
        // 2. Lanjutkan query
        $kelasQuery = Kelas::whereHas('siswaKelasTahun', function($q) use ($id_tahun) {
            $q->where('id_tahun_ajaran', $id_tahun);
        });

        if($idJenjang){
            $kelasQuery->where('id_jenjang', $idJenjang); 
        }

        $kelas = $kelasQuery->select('id', 'nama_kelas', 'tingkat')->get();

        return response()->json($kelas);
    }

}
