@extends('layouts.app')

@section('title', 'Dashboard Wali Murid')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>Halo, {{ Auth::user()->name ?? 'Pengguna' }}! 
                        Anda login sebagai {{ Auth::user()->getRoleNames()->first() ?? 'Wali Murid' }}.</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Cek tagihan terbaru anak Anda dan pantau status pembayaran dengan lebih mudah.
                    </p>

                    {{-- AKSES CEPAT --}}
                    {{-- <div class="card modern-card mb-4 shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="text-primary fw-bold mb-3">
                                Akses Cepat Transaksi Anak
                            </h5> --}}

                            

                            {{-- <div class="mt-2">
                                <a href="{{ route('tagihan.anak') }}" class="btn btn-primary modern-btn me-3">
                                    <i class="bi bi-receipt me-1"></i> Lihat Tagihan Anak
                                </a>

                                <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-primary modern-btn">
                                    <i class="bi bi-wallet2 me-1"></i> Riwayat Pembayaran
                                </a>
                            </div>
                        </div>
                    </div> --}}

                    {{-- INFO --}}
                    {{-- <div class="alert info-alert mt-3 shadow-sm border-0" style="background:#e9f5ff;">
                        <h5 class="fw-bold mb-2">📢 Pemberitahuan Penting</h5>
                        <p class="mb-0">
                            Pastikan data kontak Anda selalu diperbarui agar tidak melewatkan informasi penting terkait sekolah dan pembayaran.
                        </p>
                    </div> --}}

                </div>

                {{-- FOOTER --}}
                {{-- <div class="card-footer text-muted">
                    Dashboard Wali Murid IT Baitul Ilmi
                </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection