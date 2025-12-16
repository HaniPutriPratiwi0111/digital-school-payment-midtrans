<?php

namespace App\Http\Controllers;

use App\Models\DetailTagihan;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class DetailTagihanController extends Controller
{
    // Index: Biasanya dilihat melalui show Tagihan, bukan halaman terpisah.
    public function index()
    {
        return redirect()->route('tagihan.index');
    }

    // Create: Biasanya tidak diperlukan karena DetailTagihan dibuat saat Tagihan di-generate.
    
    // Store: Untuk menambah komponen tagihan secara manual.
    public function store(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|exists:tagihans,id',
            'nominal_unit' => 'required|numeric|min:0',
            // ... validasi lain
        ]);
        
        $detail = DetailTagihan::create($request->all());
        $detail->tagihan->updateTotalTagihan(); // Anda perlu buat method ini di Model Tagihan
        
        return redirect()->route('tagihan.show', $detail->id_tagihan)->with('success', 'Detail tagihan berhasil ditambahkan.');
    }
    
    public function show(DetailTagihan $detailTagihan)
    {
        $detailTagihan->load('tagihan');
        return view('detail-tagihan.show', compact('detailTagihan'));
    }

    public function edit(DetailTagihan $detailTagihan)
    {
        $detailTagihan->load('tagihan');
        return view('detail-tagihan.edit', compact('detailTagihan'));
    }

    public function update(Request $request, DetailTagihan $detailTagihan)
    {
        // ... validasi
        $detailTagihan->update($request->all());
        $detailTagihan->tagihan->updateTotalTagihan(); // Perbarui Total Tagihan Induk
        
        return redirect()->route('tagihan.show', $detailTagihan->id_tagihan)->with('success', 'Detail tagihan berhasil diperbarui.');
    }

    public function destroy(DetailTagihan $detailTagihan)
    {
        $id_tagihan = $detailTagihan->id_tagihan;
        $detailTagihan->delete();
        Tagihan::find($id_tagihan)->updateTotalTagihan(); // Perbarui Total Tagihan Induk
        
        return redirect()->route('tagihan.show', $id_tagihan)->with('success', 'Detail tagihan berhasil dihapus.');
    }
}