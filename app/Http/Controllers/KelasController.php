<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MasterJenjang;
use App\Models\Guru;
use Illuminate\Http\Request;


class KelasController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter dari request
        $idJenjang   = $request->input('id_jenjang');
        $tingkat     = $request->input('tingkat');
        $idWaliKelas = $request->input('id_wali_kelas');

        // Query Kelas dengan eager loading
        $query = Kelas::with(['jenjang', 'waliKelas'])->orderBy('id_jenjang')->orderBy('tingkat');

        // Filter jenjang
        if ($idJenjang) {
            $query->where('id_jenjang', $idJenjang);
        }

        // Filter tingkat
        if ($tingkat) {
            $query->where('tingkat', $tingkat);
        }

        // Filter wali kelas
        if ($idWaliKelas) {
            $query->where('id_wali_kelas', $idWaliKelas);
        }

        $kelas = $query->paginate(10)->withQueryString(); // withQueryString supaya pagination tetap bawa filter

        // Untuk dropdown filter
        $jenjangs = MasterJenjang::orderBy('nama_jenjang')->get();
        $tingkats = Kelas::select('tingkat')->distinct()->orderBy('tingkat')->pluck('tingkat');
        $gurus    = Guru::orderBy('nama')->get();

        return view('kelas.index', compact('kelas', 'jenjangs', 'tingkats', 'gurus'));
    }

    public function create()
    {
        $jenjangs = MasterJenjang::all();
        // MENGIMPLEMENTASIKAN SARAN: Eager loading 'kelasDiwalikan' pada Guru untuk efisiensi
        // Ini berguna jika nanti di view create/edit kelas ditampilkan daftar kelas yang sudah diwalikan.
        $gurus = Guru::with('kelasDiwalikan')->get(); 
        return view('kelas.create', compact('jenjangs', 'gurus'));
    }

    public function store(Request $request)
    {
        // Validasi input dasar
        $request->validate([
            'id_jenjang' => 'required|exists:master_jenjangs,id',
            'tingkat' => 'required|integer|min:1',
            'nama_kelas' => 'required|string|max:255',
            // Pastikan guru yang dipilih belum menjadi wali kelas di kelas lain
            'id_guru_wali_kelas' => 'nullable|exists:gurus,id|unique:kelas,id_guru_wali_kelas', 
        ]);

        // Cek apakah kombinasi jenjang + tingkat + nama_kelas sudah ada
        $exists = Kelas::where('id_jenjang', $request->id_jenjang)
            ->where('tingkat', $request->tingkat)
            ->where('nama_kelas', $request->nama_kelas)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['nama_kelas' => 'Kelas dengan jenjang, tingkat, dan nama yang sama sudah ada.']);
        }

        // Simpan data jika tidak ada duplikat
        Kelas::create($request->only(['id_jenjang', 'tingkat', 'nama_kelas', 'id_guru_wali_kelas']));

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }


    /**
     * Catatan: Variabel di-resolve sebagai $kela (Route Model Binding).
     */
    public function show(Kelas $kela)
    {
        // Eager loading jenjang, waliKelas, dan siswas sudah benar
        $kela->load(['jenjang', 'waliKelas', 'siswas']);
        return view('kelas.show', compact('kela'));
    }
    
    /**
     * Catatan: Variabel di-resolve sebagai $kela (Route Model Binding).
     */
    public function edit(Kelas $kela)
    {
        $jenjangs = MasterJenjang::all();

        // Ambil guru yang belum menjadi wali kelas atau guru yang saat ini wali kelas di kelas ini
        $gurus = Guru::with('kelasDiwalikan')->get()->filter(function($guru) use ($kela) {
            return !$guru->kelasDiwalikan->count() || $guru->id == $kela->id_guru_wali_kelas;
        });

        return view('kelas.edit', compact('kela', 'jenjangs', 'gurus'));
    }

    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'id_jenjang' => 'required|exists:master_jenjangs,id',
            'tingkat' => 'required|integer|min:1',
            'nama_kelas' => 'required|string|max:255',
            'id_guru_wali_kelas' => 'nullable|exists:gurus,id|unique:kelas,id_guru_wali_kelas,' . $kela->id,
        ]);

        // Cek jika kelas sudah punya siswa, jangan ubah jenjang / tingkat
        if ($kela->siswas()->exists()) {
            if ($request->id_jenjang != $kela->id_jenjang || $request->tingkat != $kela->tingkat) {
                return redirect()->back()
                    ->withInput()
                    ->with('warning', 'Jenjang dan Tingkat tidak dapat diubah karena kelas ini sudah memiliki siswa.');
            }
        }

        // Cek relasi wali kelas
        if ($request->id_guru_wali_kelas && $request->id_guru_wali_kelas != $kela->id_guru_wali_kelas) {
            $guru = Guru::find($request->id_guru_wali_kelas);
            if ($guru && $guru->kelasDiwalikan()->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('warning', 'Guru ini sudah menjadi wali kelas di kelas lain. Silakan pilih guru lain.');
            }
        }

        // Update data yang aman
        $kela->update([
            'nama_kelas' => $request->nama_kelas,
            'id_guru_wali_kelas' => $request->id_guru_wali_kelas,
            'id_jenjang' => $kela->siswas()->exists() ? $kela->id_jenjang : $request->id_jenjang,
            'tingkat' => $kela->siswas()->exists() ? $kela->tingkat : $request->tingkat,
        ]);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    /**
     * Catatan: Variabel di-resolve sebagai $kela (Route Model Binding).
     */
    public function destroy(Kelas $kela)
    {
        // Cek apakah kelas memiliki relasi dengan data Siswa
        if ($kela->siswas()->exists()) {
            
            $jumlahSiswa = $kela->siswas()->count();
            $message = "Maaf, kelas {$kela->tingkat}{$kela->nama_kelas} tidak bisa dihapus karena masih digunakan oleh {$jumlahSiswa} siswa.";
            
            // Menggunakan 'warning' agar notifikasi tampil sebagai peringatan (lebih soft daripada 'error')
            return redirect()->route('kelas.index')->with('warning', $message);
        }
        
        // Jika aman, hapus kelas
        $kela->delete();
        
        // Pesan sukses yang lebih spesifik
        return redirect()->route('kelas.index')->with('success', "Kelas {$kela->tingkat}{$kela->nama_kelas} berhasil dihapus.");
    }
    
}