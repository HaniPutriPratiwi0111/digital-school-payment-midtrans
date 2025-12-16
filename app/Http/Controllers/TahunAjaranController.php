<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahun_ajarans = TahunAjaran::orderByDesc('nama_tahun')->paginate(10);
        return view('tahun-ajaran.index', compact('tahun_ajarans'));
    }

    public function create()
    {
        return view('tahun-ajaran.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tahun' => 'required|string|unique:tahun_ajarans,nama_tahun',
            'is_aktif' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_aktif')) {
            TahunAjaran::where('is_aktif', true)->update(['is_aktif' => false]);
        }
        
        TahunAjaran::create($request->all());
        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun Ajaran berhasil ditambahkan.');
    }

    public function show(TahunAjaran $tahunAjaran)
    {
        return view('tahun-ajaran.show', compact('tahunAjaran'));
    }
    
    public function edit(TahunAjaran $tahunAjaran)
    {
        // Cek jika sudah digunakan di fitur lain
        if ($tahunAjaran->aturNominals()->exists()) {
            return redirect()->route('tahun-ajaran.index')
                             ->with('warning', 'Tahun Ajaran ini sudah digunakan di pengaturan lain, sehingga tidak bisa diedit.');
        }

        return view('tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        // Cegah update jika sudah ada relasi
        if ($tahunAjaran->aturNominals()->exists()) {
            return redirect()->route('tahun-ajaran.index')
                             ->with('warning', 'Tahun Ajaran ini sudah digunakan di pengaturan lain, sehingga tidak bisa diperbarui.');
        }

        $request->validate([
            'nama_tahun' => 'required|string|unique:tahun_ajarans,nama_tahun,' . $tahunAjaran->id,
            'is_aktif' => 'nullable|boolean',
        ]);
        
        if ($request->boolean('is_aktif')) {
            TahunAjaran::where('is_aktif', true)
                        ->where('id', '!=', $tahunAjaran->id)
                        ->update(['is_aktif' => false]);
        }

        $tahunAjaran->update($request->all());
        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun Ajaran berhasil diperbarui.');
    }
    
    public function destroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->aturNominals()->exists()) {
            return redirect()->route('tahun-ajaran.index')
                             ->with('warning', 'Tidak dapat dihapus karena masih ada pengaturan nominal yang menggunakan Tahun Ajaran ini.');
        }

        $tahunAjaran->delete();
        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun Ajaran berhasil dihapus.');
    }

    public function setActive(TahunAjaran $tahunAjaran)
    {
        DB::beginTransaction();
        try {
            // 1. Nonaktifkan semua Tahun Ajaran lainnya
            TahunAjaran::where('is_aktif', true)->update(['is_aktif' => false]);
            
            // 2. Aktifkan Tahun Ajaran yang dipilih
            $tahunAjaran->is_aktif = true;
            $tahunAjaran->save();
            
            DB::commit();
            return redirect()->route('tahun-ajaran.index')->with('success', "Tahun Ajaran **{$tahunAjaran->nama_tahun}** berhasil diaktifkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal set Tahun Ajaran Aktif: " . $e->getMessage());
            return back()->with('error', 'Gagal mengaktifkan Tahun Ajaran.');
        }
    }
}
