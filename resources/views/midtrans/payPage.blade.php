@extends('layouts.app')
@section('title', 'Pembayaran Online')
@section('content')

@php
    // --- Tentukan Nama Pemilik Tagihan ---
    $nama_pemilik_tagihan = $tagihan->calon_siswa_id 
        ? ($tagihan->calonSiswa->nama_calon ?? 'Calon Siswa Baru')
        : ($tagihan->siswa->nama_siswa ?? 'Siswa Aktif');

    // --- Tentukan Deskripsi Tagihan ---
    if ($tagihan->calon_siswa_id) {
        // Jika tagihan pendaftaran
        $deskripsi_tagihan = 'Biaya Pendaftaran: ' . ($tagihan->jenisPembayaran->nama_jenis ?? 'Formulir');
        $tagihan_label = 'Jenis Tagihan';
    } else {
        // Jika tagihan reguler siswa
        $deskripsi_tagihan = $tagihan->bulan_tagihan 
            ? \Carbon\Carbon::create(null, $tagihan->bulan_tagihan)->monthName . ' ' . ($tagihan->tahun_tagihan ?? '-')
            : ($tagihan->jenisPembayaran->nama_jenis ?? 'Tagihan Umum');
        $tagihan_label = $tagihan->bulan_tagihan ? 'Tagihan Bulan' : 'Jenis Tagihan';
    }
@endphp

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Pembayaran Online</h4>
                {{-- Menggunakan nama yang sudah di-generate --}}
                <span class="badge bg-primary">{{ $nama_pemilik_tagihan }}</span>
            </div>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                {{-- Menggunakan label yang fleksibel --}}
                                <th width="40%">{{ $tagihan_label }}</th> 
                                <td>: {{ $deskripsi_tagihan }}</td>
                            </tr>
                            <tr>
                                <th>Total Tagihan Awal</th>
                                <td>: Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Sisa Tagihan Dibayar</th>
                                <td>: Rp {{ number_format($sisa_tagihan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Status Terkini</th>
                                <td>
                                    : <span class="badge bg-{{ $tagihan->status == 'Lunas' ? 'success' : 'warning' }}">
                                        {{ ucfirst($tagihan->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Metode Pembayaran</th>
                                <td>: Midtrans (Online)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>

                {{-- Detail Item Tagihan (Ini tetap sama dan sudah benar) --}}
                <h5 class="mt-4">Detail Item Tagihan</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Deskripsi</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tagihan->detailTagihans as $detail)
                                <tr>
                                    <td>{{ $detail->deskripsi }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total Harus Dibayar (Sisa)</th>
                                <th class="text-end text-danger">Rp {{ number_format($sisa_tagihan, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="text-center mt-4">
                    <button id="pay-button" class="btn btn-lg btn-success">
                        <i class="fa fa-money-bill"></i> Bayar Sekarang
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Skrip Midtrans (Ini sudah benar) --}}
<script type="text/javascript"
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script type="text/javascript">
document.getElementById('pay-button').addEventListener('click', function () {
    window.snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) {
            Swal.fire({
                icon: 'success',
                title: 'Pembayaran Berhasil!',
                text: 'Transaksi Anda telah diselesaikan.'
            }).then(() => {
                // Perlu disesuaikan jika 'pembayaran.index' hanya untuk siswa aktif. 
                // Jika route ini adalah halaman list tagihan, maka ini OK.
                window.location.href = "{{ route('pembayaran.index') }}"; 
            });
        },
        onPending: function(result) {
            Swal.fire({
                icon: 'info',
                title: 'Menunggu Pembayaran',
                text: 'Silakan selesaikan pembayaran Anda.'
            }).then(() => {
                window.location.href = "{{ route('pembayaran.index') }}";
            });
        },
        onError: function(result) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat memproses pembayaran.'
            });
        },
        onClose: function() {
            Swal.fire({
                icon: 'warning',
                title: 'Dibatalkan',
                text: 'Anda menutup popup tanpa menyelesaikan pembayaran.'
            });
        }
    });
});
</script>

@endsection