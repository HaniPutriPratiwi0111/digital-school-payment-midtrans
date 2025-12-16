@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <div class="card shadow-lg p-4">
        <h3 class="mb-3">Status Pembayaran</h3>

        <div class="mb-3">
            <strong>Nama Siswa:</strong> {{ $calon->nama_siswa }}<br>
            <strong>Email:</strong> {{ $email_display }}<br>
            <strong>Order ID:</strong> {{ $calon->midtrans_order_id }}<br>
            <strong>Jumlah:</strong> Rp{{ number_format($calon->amount, 0, ',', '.') }}<br>
            <strong>Status:</strong>
            <span class="badge bg-{{ $calon->payment_status == 'Menunggu' ? 'warning' : 'success' }}">
                {{ $calon->payment_status }}
            </span>
        </div>

        @if($calon->payment_status == 'Menunggu')
            <button id="pay-button" class="btn btn-primary w-100">Bayar Sekarang</button>
        @else
            <div class="alert alert-success mt-3">Pembayaran berhasil!</div>
        @endif
    </div>
</div>

@if(isset($snapToken))
<script type="text/javascript">
    document.getElementById('pay-button').onclick = function () {
        window.snap.pay("{{ $snapToken }}", {
            onSuccess: function(result){
                location.reload();
            },
            onPending: function(result){
                console.log(result);
            },
            onError: function(result){
                console.log(result);
            },
            onClose: function(){
                alert('Anda menutup popup tanpa menyelesaikan pembayaran');
            }
        });
    };
</script>
@endif
@endsection