<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\NotifikasiLog;
use App\Models\CalonSiswa;
use App\Models\Siswa;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production'); 
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function payWithMidtrans(Tagihan $tagihan)
    {
        // Pakai order_id yang SUDAH dibuat saat pendaftaran atau pembuatan tagihan
        $order_id = $tagihan->midtrans_order_id;

        // Hitung sisa pembayaran
        $sisa_tagihan = $tagihan->total_tagihan - $tagihan->pembayarans()->sum('total_bayar');

        // Customer details aman (anti null)
        $customer_name =
            $tagihan->calonSiswa->nama_siswa
                ?? ($tagihan->siswa->nama_siswa ?? 'Customer');

        $customer_phone =
            $tagihan->calonSiswa->telp_wali_murid
                ?? ($tagihan->siswa->telp_wali_murid ?? '-');

        // FIX: definisikan customer_email
        $customer_email =
            $tagihan->calonSiswa->email
                ?? ($tagihan->siswa->user->email ?? 'noemail@domain.com');

        // Parameter Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $sisa_tagihan,
            ],
            'customer_details' => [
                'first_name' => $customer_name,
                'email'      => $customer_email,
                'phone'      => $customer_phone,
            ],
            'callbacks' => [
                'finish'    => route('midtrans.finish'),
                'unfinish'  => route('midtrans.unfinish'),
                'error'     => route('midtrans.error'),
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return view('midtrans.payPage', compact('tagihan', 'snapToken', 'sisa_tagihan'));
    }


    // CALLBACK ======================================

    public function finish(Request $request)
    {
        return redirect()
            ->route('tagihan.anak')
            ->with('success', 'Transaksi berhasil diproses. Silakan cek status tagihan.');
    }

    public function unfinish(Request $request)
    {
        return redirect()
            ->route('tagihan.anak')
            ->with('warning', 'Transaksi belum selesai. Silakan coba lagi.');
    }

    public function error(Request $request)
    {
        return redirect()
            ->route('tagihan.anak')
            ->with('error', 'Terjadi kesalahan saat memproses transaksi.');
    }

    // WEBHOOK ======================================

    public function handleMidtransNotification(Request $request)
    {
        $notifData = $request->all();
        Log::info('Midtrans Notification Received', $notifData);

        try {
            $order_id           = $notifData['order_id'];
            $status_code        = $notifData['status_code'];
            $gross_amount       = $notifData['gross_amount'];
            $transaction_status = $notifData['transaction_status'];
            $signature_key      = $notifData['signature_key'];
            $transaction_id     = $notifData['transaction_id'];
            $fraud_status       = $notifData['fraud_status'] ?? null;

            // SIGNATURE CHECK
            $serverKey = config('midtrans.server_key');
            $mySignature = hash('sha512', $order_id . $status_code . $gross_amount . $serverKey);

            if ($mySignature !== $signature_key) {
                Log::error("Signature mismatch for order_id: {$order_id}");
                return response('Signature Mismatch', 403);
            }

            $tagihan = Tagihan::where('midtrans_order_id', $order_id)->first();

            if (!$tagihan) {
                Log::warning("Tagihan tidak ditemukan: {$order_id}");
                return response('OK', 200);
            }

            if ($tagihan->calon_siswa_id) {
                $tagihan->load('calonSiswa');
            }

            if ($tagihan->status === 'Lunas' &&
                in_array($transaction_status, ['settlement', 'capture'])) {
                return response('OK', 200);
            }

            DB::beginTransaction();

            $exist = Pembayaran::where('midtrans_transaction_id', $transaction_id)->exists();

            $amountPaid = (int) round((float) $gross_amount);
            $totalSebelum = $tagihan->pembayarans()->sum('total_bayar');
            $sisa = $tagihan->total_tagihan - $totalSebelum;

            $finalStatus = $tagihan->status;

            // PEMBAYARAN SUKSES
            if (!$exist && ($transaction_status === 'settlement'
                || ($transaction_status === 'capture' && $fraud_status === 'accept'))) {

                $jumlahBayar = min($amountPaid, $sisa);

                if ($jumlahBayar > 0) {

                    Pembayaran::create([
                        'id_tagihan' => $tagihan->id,
                        'kode_transaksi' => $order_id,
                        'tanggal_bayar' => Carbon::parse($notifData['transaction_time'])->toDateString(),
                        'metode_pembayaran' => "Midtrans ({$notifData['payment_type']})",
                        'total_bayar' => $jumlahBayar,
                        'midtrans_transaction_id' => $transaction_id,
                    ]);

                    $sisaAkhir = $sisa - $jumlahBayar;

                    $finalStatus = $sisaAkhir <= 0 ? 'Lunas' : 'Lunas Partial';

                    if ($finalStatus === 'Lunas' && $tagihan->calonSiswa) {
                        $tagihan->calonSiswa->update(['payment_status' => 'Lunas']);
                    }
                }
            }

            // PENDING
            if ($transaction_status === 'pending') {
                $finalStatus = $tagihan->status;
            }

            // BATAL / EXPIRE
            if (in_array($transaction_status, ['deny', 'expire', 'cancel'])) {
                if (!in_array($tagihan->status, ['Lunas', 'Lunas Partial'])) {
                    $finalStatus = 'Batal';
                }

                if ($tagihan->calonSiswa &&
                    $tagihan->calonSiswa->payment_status !== 'Lunas') {
                    $tagihan->calonSiswa->update(['payment_status' => 'Gagal']);
                }
            }

            if ($tagihan->status !== $finalStatus) {
                $tagihan->update(['status' => $finalStatus]);
            }

            DB::commit();
            return response('OK', 200);

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error("Midtrans Webhook Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response('OK', 200);
        }
    }
}
