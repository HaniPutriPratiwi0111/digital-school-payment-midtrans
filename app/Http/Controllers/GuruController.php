<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; 

class GuruController extends Controller
{
    public function index()
    {
        $gurus = Guru::with('user', 'kelasDiwalikan')->orderBy('nama')->paginate(10);
        return view('guru.index', compact('gurus'));
    }

    public function create()
    {
        return view('guru.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nuptk' => 'nullable|unique:gurus,nuptk', 
            'nip' => 'nullable|unique:gurus,nip',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan', 
            'agama' => 'required|string|max:50', 
            'tempat_lahir' => 'nullable|string|max:100', 
            'tanggal_lahir' => 'nullable|date', 
            'foto' => 'nullable|image|max:2048',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat Akun User (Role Guru)
            $user = User::create([
                'name' => $request->nama, 
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'guru',
            ]);

            // 2. Upload Foto (Opsional)
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                // Simpan foto di storage/app/public/guru_photos
                $fotoPath = $request->file('foto')->store('guru_photos', 'public');
            }

            // 3. Buat Data Guru (Menggunakan array eksplisit/only untuk keamanan mass assignment)
            Guru::create($request->only([
                'nama', 
                'nuptk', 
                'nip', // <== PERBAIKAN: Tambah NIP
                'jenis_kelamin', 
                'agama', 
                'tempat_lahir', 
                'tanggal_lahir',
            ]) + [
                'id_user' => $user->id,
                'foto' => $fotoPath, // <== Tambah path foto
            ]);
            
            DB::commit();
            return redirect()->route('guru.index')->with('success', 'Data guru dan akun berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Hapus file yang mungkin sudah terlanjur diupload jika transaksi gagal
            if (isset($fotoPath) && $fotoPath) {
                 Storage::disk('public')->delete($fotoPath);
            }
            \Log::error('Gagal menyimpan guru: ' . $e->getMessage()); 
            return redirect()->back()->with('error', 'Gagal menyimpan data guru. Silakan coba lagi.')->withInput();
        }
    }

    public function show(Guru $guru)
    {
        // Eager loading relasi sudah benar
        $guru->load(['user', 'kelasDiwalikan']);
        return view('guru.show', compact('guru'));
    }
    
    public function edit(Guru $guru)
    {
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nuptk' => 'nullable|unique:gurus,nuptk,' . $guru->id,
            'nip' => 'nullable|unique:gurus,nip,' . $guru->id, // <== PERBAIKAN: Tambah Validasi NIP
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|max:50',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'foto' => 'nullable|image|max:2048', // <== DIUNCOMMENT
            // Pengecualian ID user saat ini
            'email' => 'required|email|unique:users,email,' . $guru->id_user, 
        ]);

        DB::beginTransaction();
        try {
            // 1. Update Akun User
            $guru->user->update([
                'name' => $request->nama, 
                'email' => $request->email,
            ]);

            // 2. Handle Foto Update (Opsional)
            $fotoPath = $guru->foto;
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($guru->foto) {
                    Storage::disk('public')->delete($guru->foto);
                }
                // Upload foto baru
                $fotoPath = $request->file('foto')->store('guru_photos', 'public');
            }

            // 3. Update Data Guru
            $guru->update($request->only([
                'nama', 
                'nuptk', 
                'nip', // <== PERBAIKAN: Tambah NIP
                'jenis_kelamin', 
                'agama', 
                'tempat_lahir', 
                'tanggal_lahir',
            ]) + ['foto' => $fotoPath]); // <== Tambah path foto
            
            DB::commit();
            return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal update guru: ' . $e->getMessage()); // <== PERBAIKAN: Log error
            return redirect()->back()->with('error', 'Gagal memperbarui data guru. Silakan coba lagi.');
        }
    }

    public function destroy(Guru $guru)
    {
        // Pengecekan relasi sudah sangat baik (memanfaatkan relasi hasOne)
        $kelasDiwalikan = $guru->kelasDiwalikan; // Ambil relasi
        if ($kelasDiwalikan) { // Cek apakah ada kelas yang diwalikan
            // Memberikan pesan yang lebih informatif
            return redirect()->route('guru.index')->with('error', 'Guru tidak dapat dihapus karena masih menjadi Wali Kelas dari Kelas ' . $kelasDiwalikan->tingkat . ' ' . $kelasDiwalikan->nama_kelas . '.');
        }

        DB::beginTransaction();
        try {
            // Hapus file foto terkait (jika ada)
            if ($guru->foto) {
                Storage::disk('public')->delete($guru->foto);
            }
            
            // Hapus user terkait (akan menghapus user login)
            $guru->user()->delete(); 
            // Hapus Guru (ini akan melakukan Soft Delete)
            $guru->delete(); 
            
            DB::commit();
            return redirect()->route('guru.index')->with('success', 'Guru dan akun berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal delete guru: ' . $e->getMessage()); // <== PERBAIKAN: Log error
            return redirect()->back()->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }
    }
}