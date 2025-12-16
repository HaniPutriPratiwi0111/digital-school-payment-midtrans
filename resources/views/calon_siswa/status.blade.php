<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran Pendaftaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Midtrans Snap.js -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-lg mx-auto" style="max-width: 600px;">
            <div class="card-header bg-primary text-white text-center">
                <h1 class="h4 mb-0">Status Pembayaran Pendaftaran</h1>
            </div>
            <div class="card-body p-4 text-center">

                @if(!isset($calonSiswa))
                    <div class="alert alert-danger">Data pendaftaran tidak valid atau tidak ditemukan.</div>
                    <a href="{{ route('calon_siswa.register') }}" class="btn btn-secondary mt-3">Kembali ke Pendaftaran</a>
                @elseif($calonSiswa)
                    <h2 class="h5 mb-4">Pendaftar: {{ $calonSiswa->nama_siswa }}</h2>
                    <p>Jenjang Tujuan: **{{ $calonSiswa->jenjang->name ?? 'N/A' }}**</p>
                    <p>Order ID: <code>{{ $calonSiswa->midtrans_order_id }}</code></p>
                    
                    <hr>

                    @if($calonSiswa->payment_status === 'Lunas')
                        <div class="alert alert-success mt-4">
                            <h4 class="alert-heading">Pembayaran Berhasil!</h4>
                            <p class="lead">Biaya pendaftaran telah **LUNAS** (Rp {{ number_format($calonSiswa->amount, 0, ',', '.') }}).</p>
                            <hr>
                            <p class="mb-0">Terima kasih. **Mohon menunggu informasi selanjutnya** dari pihak admin/sekolah. Data Anda sudah masuk ke sistem persetujuan.</p>
                        </div>
                    @elseif($calonSiswa->payment_status === 'Menunggu')
                        <div class="alert alert-warning mt-4">
                            <h4 class="alert-heading">Menunggu Pembayaran</h4>
                            <p>Selesaikan pembayaran biaya pendaftaran sebesar **Rp {{ number_format($calonSiswa->amount, 0, ',', '.') }}**.</p>
                            
                            <button id="pay-button" class="btn btn-success btn-lg mt-3">Bayar Sekarang via Midtrans</button>
                        </div>
                        
                    @elseif($calonSiswa->payment_status === 'Gagal')
                        <div class="alert alert-danger mt-4">
                            <h4 class="alert-heading">Pembayaran Gagal/Expired</h4>
                            <p>Transaksi Anda gagal atau telah kedaluwarsa. Silakan lakukan pendaftaran baru atau hubungi admin.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const payButton = document.getElementById('pay-button');
            const orderId = '{{ $calonSiswa->midtrans_order_id ?? '' }}';

            if (payButton && orderId) {
                payButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    // Panggil API untuk mendapatkan token SNAP
                    fetch('{{ route("api.midtrans.token", ["orderId" => "ORDER_ID_PLACEHOLDER"]) }}'.replace('ORDER_ID_PLACEHOLDER', orderId))
                        .then(response => response.json())
                        .then(data => {
                            if (data.token) {
                                snap.pay(data.token, {
                                    onSuccess: function(result){
                                        alert("Pembayaran berhasil! Status Anda akan segera diperbarui. Kode Transaksi: " + result.transaction_id);
                                        window.location.reload(); 
                                    },
                                    onPending: function(result){
                                        alert("Pembayaran tertunda. Mohon selesaikan pembayaran Anda.");
                                    },
                                    onError: function(result){
                                        alert("Pembayaran gagal. Silakan coba lagi.");
                                    },
                                });
                            } else {
                                alert('Gagal mendapatkan token pembayaran: ' + data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching snap token:', error);
                            alert('Terjadi kesalahan saat memproses pembayaran. Cek konsol log.');
                        });
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>