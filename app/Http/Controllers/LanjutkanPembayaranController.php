<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CalonSiswa;
use App\Models\User;
use Midtrans\Config;
use Midtrans\Snap;

class LanjutkanPembayaranController extends Controller
{
    public function index()
    {
        return view('public.lanjutkan-pembayaran');
    }

    public function check(Request $request)
    {
        $calon = null;

        // OPSI 1: Email / No HP
        if ($request->filled('email_or_hp')) {
            $calon = CalonSiswa::where('email', $request->email_or_hp)
                ->orWhere('telp_wali_murid', $request->email_or_hp)
                ->first();
        }

        // OPSI 2: Lupa data
        if (!$calon && $request->filled('nama_siswa')) {
            $calon = CalonSiswa::where('nama_siswa', 'LIKE', "%{$request->nama_siswa}%")
                ->where('nama_wali_murid', 'LIKE', "%{$request->nama_ortu}%")
                ->whereDate('tanggal_lahir', $request->tanggal_lahir)
                ->first();
        }

        if (!$calon) {
            return back()->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        if ($calon->payment_status === 'Lunas') {
            return redirect()->route('login.wali')
                ->with('success', 'Pembayaran sudah lunas. Silakan login.');
        }

        // ==============================
        // Generate Snap Token Midtrans
        // ==============================
        $user_wali = User::find($calon->id_user_wali);

        $customer_name  = $calon->nama_wali_murid ?? optional($user_wali)->name ?? 'Orang Tua';
        $customer_email = optional($user_wali)->email ?? $calon->email ?? 'noemail@domain.com';
        $customer_phone = $calon->telp_wali_murid ?? '081234567890';

        $snapToken = null;

        if ($calon->payment_status == 'Menunggu' || $calon->payment_status == 'Pending') {

            Config::$serverKey     = config('midtrans.server_key');
            Config::$isProduction  = config('midtrans.is_production');
            Config::$isSanitized   = true;
            Config::$is3ds         = true;

            $params = [
                'transaction_details' => [
                    'order_id'     => $calon->midtrans_order_id,
                    'gross_amount' => (int) $calon->amount,
                ],
                'customer_details' => [
                    'first_name' => $customer_name,
                    'email'      => $customer_email,
                    'phone'      => $customer_phone,
                ],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
            } catch (\Exception $e) {
                $snapToken = null; // jika Midtrans error, jangan crash
            }
        }

        return view('public.pendaftaran_sukses', compact('calon', 'user_wali', 'snapToken'));
    }
}
