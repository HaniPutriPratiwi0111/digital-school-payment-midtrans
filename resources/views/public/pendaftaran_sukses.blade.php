<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pendaftaran Berhasil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>
<div class="container min-vh-100 d-flex align-items-center">
    <div class="card mx-auto p-4 shadow-sm" style="max-width:450px; border-radius:16px;">
        
        <div class="text-center mb-3">
            <h3 class="text-success mb-2">Pendaftaran Berhasil!</h3>
            <p class="text-muted mb-0">Data pendaftaran {{ $calon->nama_siswa }} telah kami terima.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-2 text-center">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning py-2 text-center">{!! session('warning') !!}</div>
        @endif

        {{-- INFO AKUN WALI --}}
        <h5 class="mt-3 mb-2">Informasi Login Akun Orang Tua</h5>
        <div class="bg-light p-3 rounded mb-3">
            <p class="mb-1"><strong>Username (Email):</strong> <span class="fw-bold text-primary">{{ $user_wali->email ?? $calon->email ?? 'N/A' }}</span></p>
            <p class="mb-0"><strong>Password Default:</strong> <span class="fw-bold text-danger">{{ session('default_password') ?? 'password12345' }}</span></p>
        </div>

        {{-- INFO PEMBAYARAN --}}
        <div class="alert {{ $calon->payment_status == 'Lunas' ? 'alert-success' : 'alert-info' }} text-center">
            <p class="mb-1">Status Pembayaran: <span class="fw-bold text-danger">{{ $calon->payment_status }}</span></p>
            <p class="fw-bold">
                Biaya Pendaftaran: Rp {{ number_format($calon->amount, 0, ',', '.') }}
            </p>
                <small>
                    ⚠️ PERHATIAN: Anda wajib menyelesaikan pembayaran pendaftaran agar dapat login ke Dashboard Orang Tua Murid.
                </small>
        </div>

        {{-- TOMBOL BAYAR / LOGIN --}}
        @if(($calon->payment_status=='Menunggu'||$calon->payment_status=='Pending') && $snapToken)
            <button id="pay-button" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-wallet me-2"></i> Bayar Sekarang
            </button>
            <p class="text-center text-muted small mb-0">Klik tombol di atas untuk melanjutkan pembayaran via Midtrans.</p>
        @elseif($calon->payment_status=='Lunas')
            <a href="{{ route('login.wali') }}" class="btn btn-success w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Lanjut Login Orang Tua Murid
            </a>
            <p class="text-center text-muted small mb-0">Gunakan username & password di atas.</p>
        @endif

        @if(($calon->payment_status=='Menunggu'||$calon->payment_status=='Pending') && !$snapToken)
            <div class="alert alert-warning text-center mb-0">
                <i class="fas fa-exclamation-triangle me-1"></i> Token pembayaran belum tersedia. Silakan refresh halaman atau hubungi admin.
            </div>
        @endif

    </div>
</div>


{{-- Skrip Midtrans Snap dan JavaScript --}}
@if (($calon->payment_status == 'Menunggu' || $calon->payment_status == 'Pending') && $snapToken)
    {{-- Pastikan menggunakan client_key yang benar dari config --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        document.getElementById('pay-button').onclick = function(){
            // Trigger snap popup.
            snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    /* You may want to redirect or update UI here */
                    window.location.reload();
                },
                onPending: function(result){
                    /* You may want to redirect or update UI here */
                    window.location.reload();
                },
                onError: function(result){
                    /* You may want to redirect or update UI here */
                    alert("Pembayaran gagal. Silakan coba lagi atau hubungi Admin."); // Note: Midtrans standard function, we use alert here minimally.
                },
                onClose: function(){
                    // User closed the popup without finishing the payment
                    console.log('Customer closed the popup without finishing the payment');
                }
            });
        };
    </script>
@endif

</body>
</html>