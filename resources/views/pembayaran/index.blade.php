@extends('layouts.app')
@section('title', 'Riwayat Pembayaran')
@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="card-title">Daftar Pembayaran</h4>
                <a href="{{ route('pembayaran.create') }}" class="btn btn-primary">Catat Pembayaran Tunai</a>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Transaksi</th>
                                <th>Pembayar (Siswa/Pendaftar)</th> 
                                <th>Tanggal Bayar</th>
                                <th>Metode</th>
                                <th>Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pembayarans as $pembayaran)
                            <tr>
                                <td>{{ $pembayarans->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('pembayaran.show', $pembayaran->id) }}" class="text-primary fw-bold">
                                        {{ $pembayaran->kode_transaksi }}
                                    </a>
                                </td>
                                <td>
                                    @if($pembayaran->tagihan->siswa)
                                        <span class="badge bg-success">Siswa Aktif</span>
                                        {{ $pembayaran->tagihan->siswa->nama_siswa }}
                                    @elseif($pembayaran->tagihan->calonSiswa)
                                        <span class="badge bg-warning text-dark">Pendaftaran</span>
                                        {{ $pembayaran->tagihan->calonSiswa->nama_siswa }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d-m-Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ str_contains($pembayaran->metode_pembayaran, 'Midtrans') || $pembayaran->midtrans_transaction_id ? 'info' : 'success' }}">
                                        {{ $pembayaran->metode_pembayaran }}
                                    </span>
                                </td>
                                <td>Rp. {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data pembayaran.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Custom Pagination ala Daftar Tagihan --}}
            <div class="card-footer d-flex justify-content-between align-items-center">
                {{-- Previous --}}
                @if ($pembayarans->onFirstPage())
                    <span class="btn btn-secondary disabled">Previous</span>
                @else
                    <a href="{{ $pembayarans->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="btn btn-primary">Previous</a>
                @endif

                {{-- Info Halaman --}}
                <span class="fw-bold">
                    Halaman {{ $pembayarans->currentPage() }} dari {{ $pembayarans->lastPage() }}
                </span>

                {{-- Next --}}
                @if ($pembayarans->hasMorePages())
                    <a href="{{ $pembayarans->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="btn btn-primary">Next</a>
                @else
                    <span class="btn btn-secondary disabled">Next</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
