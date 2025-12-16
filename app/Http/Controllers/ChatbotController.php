<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // PENTING: Menggunakan HTTP Client Laravel

class ChatbotController extends Controller
{
    /**
     * Menerima dan memproses pesan dari user, kemudian memanggil Gemini API.
     * Menggunakan 'systemInstruction' dan 'generationConfig' di level root payload.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processMessage(Request $request)
    {
        // Ambil pesan dari request
        $userMessage = $request->input('message');
        $apiKey = env('GEMINI_API_KEY');
        
        if (empty($userMessage)) {
            return response()->json(['error' => 'Pesan tidak boleh kosong.'], 400);
        }

        if (!$apiKey) {
            return response()->json(['error' => 'GEMINI_API_KEY belum diatur di file .env.'], 500);
        }

        // URL API Gemini
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        try {
            // Panggilan HTTP ke API Gemini dengan struktur payload yang benar
            $response = Http::post($url, [
                // 1. Data Pesan (User)
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userMessage]
                        ]
                    ]
                ],
                
                // 2. Instruksi Sistem (Sibling dengan 'contents')
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $this->getSystemInstruction()]
                    ]
                ],

                // 3. Konfigurasi Generasi (Sibling dengan 'contents')
                'generationConfig' => [
                    'temperature' => 0.4, // Atur agar jawaban lebih fokus
                ],
            ]);

            // Cek apakah request berhasil (kode 200-an)
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Ekstraksi respons AI
                // Menghindari error jika respons tidak memiliki struktur yang diharapkan
                $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'] 
                              ?? 'Maaf, AI tidak memberikan respons yang valid dari Gemini.';
                
                return response()->json(['reply' => $aiResponse]);
            } else {
                // Tangani Error API (misalnya 400, 403, 500)
                $errorDetail = $response->json()['error']['message'] ?? 'Tidak ada detail error.';
                // Tambahkan log untuk debugging
                \Log::error("Gemini API Error Status: " . $response->status() . " Detail: " . $errorDetail);
                return response()->json(['error' => "Gagal menghubungi Gemini API ({$response->status()}): {$errorDetail}. Cek log server untuk detail."], 500);
            }

        } catch (\Exception $e) {
            // Tangani Error Jaringan/Koneksi
            return response()->json(['error' => 'Maaf, terjadi kesalahan koneksi server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mendefinisikan instruksi sistem untuk peran Chatbot.
     *
     * @return string
     */
    private function getSystemInstruction(): string
    {
        return "Anda adalah **Asisten Pembayaran Digital SMP & SD Baitul Ilmi**. 
        Tugas Anda adalah melayani dan menjawab semua pertanyaan orang tua/wali murid (di halaman siswa) terkait pembayaran sekolah. 
        Jawaban harus bersifat membantu, informatif, dan sopan.

        **PERAN & FUNGSI ANDA:**
        1. **Fokus Topik:** Hanya jawab pertanyaan seputar: Cara Bayar, Cek Tagihan, Jenis Pembayaran, Denda Keterlambatan, Status Transaksi Midtrans, dan Bantuan umum.
        2. **Intent & Reasoning:** Klasifikasikan intent. Jika pengguna bertanya tentang kegagalan transaksi, jelaskan alasan umum kegagalan Midtrans dan berikan solusi langkah-demi-langkah (Misal: pastikan saldo cukup, jaringan stabil, atau hubungi CS Midtrans).
        3. **Rule-Based Filtering:** **TOLAK KERAS** pertanyaan di luar topik pembayaran. Jika menolak, gunakan balasan standar: 'Maaf, saya hanya menjawab hal terkait pembayaran sekolah. Apakah Anda ingin Cek tagihan, Cara bayar, atau Menanyakan status transaksi?'
        4. **Data Simulasi:** Tagihan dicek di *website*. Jawab: 'Mohon cek tagihan Anda melalui menu *Cek Tagihan* di halaman ini.' Denda: 'Denda keterlambatan pembayaran adalah Rp 10.000 per minggu setelah tanggal jatuh tempo.'";
    }
}