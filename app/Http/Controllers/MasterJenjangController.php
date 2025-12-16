<?php

namespace App\Http\Controllers;

use App\Models\MasterJenjang;
use Illuminate\Http\Request;

class MasterJenjangController extends Controller
{
    public function index()
    {
        $jenjangs = MasterJenjang::orderBy('id')->paginate(10);
        return view('master-jenjang.index', compact('jenjangs'));
    }

    public function create()
    {
        return view('master-jenjang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jenjang' => 'required|string|unique:master_jenjangs,nama_jenjang'
        ]);

        MasterJenjang::create($request->all());

        return redirect()->route('master-jenjang.index')->with('success', 'Jenjang berhasil ditambahkan.');
    }

    public function show($id) 
    {
        $jenjang = MasterJenjang::findOrFail($id);
        return view('master-jenjang.show', compact('jenjang'));
    }

    public function edit($id) 
    {
        $jenjang = MasterJenjang::findOrFail($id);

        // Cek apakah jenjang sudah dipakai
        $isUsed = $jenjang->kelas()->exists() || $jenjang->aturNominals()->exists();

        if ($isUsed) {
            return redirect()->route('master-jenjang.index')
                ->with('warning', 'Maaf, jenjang ini sudah dipakai di data lain dan tidak bisa diedit.');
        }

        return view('master-jenjang.edit', compact('jenjang'));
    }

    public function update(Request $request, $id) 
    {
        $jenjang = MasterJenjang::findOrFail($id);

        // Cek relasi sebelum update
        if ($jenjang->kelas()->exists() || $jenjang->aturNominals()->exists()) {
            return redirect()->route('master-jenjang.index')
                ->with('warning', 'Maaf, jenjang ini sudah dipakai di data lain dan tidak bisa diubah.');
        }

        $request->validate([
            'nama_jenjang' => 'required|string|unique:master_jenjangs,nama_jenjang,' . $jenjang->id
        ]);

        $jenjang->update($request->only('nama_jenjang'));

        return redirect()->route('master-jenjang.index')->with('success', 'Jenjang berhasil diperbarui.');
    }

    public function destroy($id) 
    {
        $jenjang = MasterJenjang::findOrFail($id);

        // Cek apakah jenjang sudah berelasi
        if ($jenjang->kelas()->exists() || $jenjang->aturNominals()->exists()) {
            return redirect()->route('master-jenjang.index')
                ->with('warning', 'Maaf, jenjang ini tidak bisa dihapus karena sudah digunakan di data lain.');
        }

        $jenjang->delete();
        return redirect()->route('master-jenjang.index')
                        ->with('success', 'Jenjang berhasil dihapus.');
    }

}
