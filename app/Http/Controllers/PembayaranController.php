<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\CalonSiswa; // PENTING: Tambahkan Model Calon Siswa
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 

class PembayaranController extends Controller
{
    /**
     * Menampilkan daftar riwayat pembayaran (untuk Bendahara/Admin).
     */
    public function index()
    {
        // Load relasi siswa aktif dan calon siswa
        $query = Pembayaran::with([
            'tagihan' => function ($q) {
                $q->with(['siswa.kelas', 'calonSiswa']);
            }
        ])->orderByDesc('tanggal_bayar');

        // Logic filter untuk Orang Tua/Siswa Aktif diabaikan untuk fokus pada Bendahara view
        // Jika perlu, tambahkan logika Auth::check() di sini.

        $pembayarans = $query->paginate(10);
        
        return view('pembayaran.index', compact('pembayarans'));
    }

    public function create()
    {
        $tagihans = Tagihan::whereIn('status', ['Belum Bayar', 'Lunas Partial'])
            ->with([
                'siswa.kelas.jenjang', // pastikan relasi jenjang & kelas dipanggil
                'jenisPembayaran',
                'calonSiswa'
            ])
            ->get()
            ->map(function($tagihan) {
                $totalSudahBayar = $tagihan->pembayarans()->sum('total_bayar');
                $sisa = $tagihan->total_tagihan - $totalSudahBayar;

                // Nama siswa / calon siswa
                $namaSiswa = $tagihan->siswa ? $tagihan->siswa->nama_siswa : ($tagihan->calonSiswa->nama ?? 'Calon Siswa');

                // Kelas & Jenjang
                $kelas = $tagihan->siswa->kelas->nama_kelas ?? '-';
                $tingkat = $tagihan->siswa->kelas->tingkat ?? '-';
                $jenjang = $tagihan->siswa->kelas->jenjang->nama_jenjang ?? '-';

                // Jenis pembayaran
                $jenis = $tagihan->jenisPembayaran->nama_jenis ?? 'Tagihan';

                // Format tampilan dropdown
                $tagihan->nama_tagihan = "{$namaSiswa} [{$jenjang} / {$tingkat} - {$kelas}] - ( Total: Rp " 
                                        . number_format($sisa,0,',','.') 
                                        . " - {$jenis} )";
                $tagihan->sisa = $sisa;

                return $tagihan;
            });

            $jenjangs = \App\Models\MasterJenjang::all(); // pastikan model Jenjang tersedia

        return view('pembayaran.create', compact('tagihans', 'jenjangs'));

    }
    
    public function store(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|exists:tagihans,id',
            'total_bayar' => 'required|numeric|min:1',
            'metode_pembayaran' => 'required|string|in:Tunai,Transfer Manual',
        ]);
        
        DB::beginTransaction();
        try {
            $tagihan = Tagihan::findOrFail($request->id_tagihan);
            $totalSudahBayar = $tagihan->pembayarans()->sum('total_bayar');
            $sisaTagihan = $tagihan->total_tagihan - $totalSudahBayar;

            if ($request->total_bayar > $sisaTagihan) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Jumlah bayar melebihi sisa tagihan.');
            }

            // 1. Buat Entri Pembayaran
            Pembayaran::create([
                'id_tagihan' => $tagihan->id,
                'id_user' => Auth::id(), // User Admin/Bendahara yang mencatat
                'kode_transaksi' => 'TUNAI-' . time() . '-' . $tagihan->id, 
                'tanggal_bayar' => Carbon::now()->format('Y-m-d'),
                'metode_pembayaran' => $request->metode_pembayaran, 
                'total_bayar' => $request->total_bayar,
            ]);

            // 2. Hitung ulang status tagihan
            $totalSudahBayar += $request->total_bayar;
            $newStatus = match (true) {
                $totalSudahBayar >= $tagihan->total_tagihan => 'Lunas',
                $totalSudahBayar > 0 => 'Lunas Partial',
                default => 'Belum Bayar',
            };
            
            // 3. Update Status Tagihan
            $tagihan->update(['status' => $newStatus]);
            
            DB::commit();
            return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil dicatat. Status tagihan: ' . $newStatus);

        } catch (\Exception $e) {
            DB::rollBack(); 
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
    
    public function show(Pembayaran $pembayaran)
    {
        // Memuat relasi calonSiswa juga
        $pembayaran->load([
            'tagihan' => function($q) {
                $q->with(['siswa.kelas', 'calonSiswa']);
            }
        ]);

        return view('pembayaran.show', compact('pembayaran'));
    }
    
    public function destroy(Pembayaran $pembayaran)
    {
        DB::beginTransaction();
        try {
            $tagihan = $pembayaran->tagihan;

            // Hapus pembayaran
            $pembayaran->delete();

            // Hitung ulang total yang sudah dibayar
            $totalSudahBayar = $tagihan->pembayarans()->sum('total_bayar');

            // Tentukan status tagihan baru
            $newStatus = match (true) {
                $totalSudahBayar >= $tagihan->total_tagihan => 'Lunas',
                $totalSudahBayar > 0 => 'Lunas Partial',
                default => 'Belum Bayar',
            };

            $tagihan->update(['status' => $newStatus]);
            
            // PENTING: Jika tagihan pendaftaran dibatalkan, kembalikan status calon siswa ke 'Menunggu'
            if ($tagihan->calonSiswa) {
                 $tagihan->calonSiswa->update(['payment_status' => 'Menunggu']);
            }

            DB::commit();
            return back()->with('success', 'Pembayaran berhasil dihapus. Status tagihan kembali menjadi ' . $newStatus);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pembayaran: ' . $e->getMessage());
        }
    }
}