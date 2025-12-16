<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use Illuminate\Http\Request;

class JenisPembayaranController extends Controller
{
    public function index()
    {
        $jenis_pembayarans = JenisPembayaran::orderBy('id')->paginate(10);
        return view('jenis-pembayaran.index', compact('jenis_pembayarans'));
    }

    public function create()
    {
        return view('jenis-pembayaran.create');
    }

    public function store(Request $request)
    {
        $request->validate(['nama_jenis' => 'required|string|unique:jenis_pembayarans,nama_jenis']);
        JenisPembayaran::create($request->all());
        return redirect()->route('jenis-pembayaran.index')->with('success', 'Jenis Pembayaran berhasil ditambahkan.');
    }
    
    public function show(JenisPembayaran $jenisPembayaran)
    {
        return view('jenis-pembayaran.show', compact('jenisPembayaran'));
    }

    public function edit(JenisPembayaran $jenisPembayaran)
    {
        return view('jenis-pembayaran.edit', compact('jenisPembayaran'));
    }

    public function update(Request $request, JenisPembayaran $jenisPembayaran)
    {
        $request->validate(['nama_jenis' => 'required|string|unique:jenis_pembayarans,nama_jenis,' . $jenisPembayaran->id]);
        $jenisPembayaran->update($request->all());
        return redirect()->route('jenis-pembayaran.index')->with('success', 'Jenis Pembayaran berhasil diperbarui.');
    }

    public function destroy(JenisPembayaran $jenisPembayaran)
    {
        if ($jenisPembayaran->aturNominals()->exists() || $jenisPembayaran->detailTagihans()->exists()) {
            return redirect()->route('jenis-pembayaran.index')->with('error', 'Tidak dapat dihapus karena memiliki data terkait.');
        }
        $jenisPembayaran->delete();
        return redirect()->route('jenis-pembayaran.index')->with('success', 'Jenis Pembayaran berhasil dihapus.');
    }
}