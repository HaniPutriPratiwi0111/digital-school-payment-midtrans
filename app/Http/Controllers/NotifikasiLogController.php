<?php

namespace App\Http\Controllers;

use App\Models\NotifikasiLog;
use Illuminate\Http\Request;

class NotifikasiLogController extends Controller
{
    public function index()
    {
        $logs = NotifikasiLog::with('siswa', 'tagihan')->orderByDesc('waktu_kirim')->paginate(20);
        return view('notifikasi-log.index', compact('logs'));
    }
    
    public function show(NotifikasiLog $notifikasiLog)
    {
        $notifikasiLog->load('siswa', 'tagihan');
        return view('notifikasi-log.show', compact('notifikasiLog'));
    }
    
    public function destroy(NotifikasiLog $notifikasiLog)
    {
        $notifikasiLog->delete();
        return redirect()->route('notifikasi-index')->w-logith('success', 'Log notifikasi berhasil dihapus.');
    }
}