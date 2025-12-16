@extends('layouts.app')
@section('title', 'Tagihan Anak Saya')

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                    <h4 class="card-title">Daftar Tagihan untuk: {{ Auth::user()->name }}</h4>
                </div>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenis Pembayaran</th>
                                <th>Tagihan</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tagihans as $tagihan)
                            <tr class="align-middle">
                                <td class="text-center">{{ $loop->iteration + ($tagihans->currentPage()-1) * $tagihans->perPage() }}</td>
                                <td>{{ $tagihan->jenisPembayaran->nama_jenis ?? 'N/A' }}</td>
                                <td class="text-end">Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</td>
                                <td>{{ optional($tagihan->tanggal_jatuh_tempo)->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-center">
                                    @php
                                        $warna = match($tagihan->status) {
                                            'Lunas' => 'success',
                                            'Lunas Partial' => 'warning',
                                            'Belum Bayar' => 'danger',
                                            'Batal' => 'secondary',
                                            default => 'danger'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $warna }}">
                                        {{ $tagihan->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $isDisabled = in_array($tagihan->status, ['Lunas']);
                                    @endphp

                                    <a href="{{ $isDisabled ? '#' : route('midtrans.payPage', $tagihan->id) }}"
                                    class="btn btn-sm btn-primary {{ $isDisabled ? 'disabled' : '' }}"
                                    style="{{ $isDisabled ? 'pointer-events: none; opacity: .6;' : '' }}"
                                    aria-disabled="{{ $isDisabled ? 'true' : 'false' }}">
                                        <i class="bi bi-credit-card"></i> Bayar Tagihan
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Tidak ada tagihan untuk saat ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINATION CUSTOM --}}
                @if ($tagihans->total() > 0)
                <div class="card-footer d-flex justify-content-between align-items-center mt-3">

                    {{-- Tombol Previous --}}
                    @if ($tagihans->onFirstPage())
                        <span class="btn btn-secondary disabled">Previous</span>
                    @else
                        <a href="{{ $tagihans->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                        class="btn btn-primary">
                        Previous
                        </a>
                    @endif

                    {{-- Info Halaman --}}
                    <span class="fw-bold">
                        Halaman {{ $tagihans->currentPage() }} dari {{ $tagihans->lastPage() }}
                    </span>

                    {{-- Tombol Next --}}
                    @if ($tagihans->hasMorePages())
                        <a href="{{ $tagihans->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                        class="btn btn-primary">
                        Next
                        </a>
                    @else
                        <span class="btn btn-secondary disabled">Next</span>
                    @endif

                </div>
                @endif


            </div>
        </div>
    </div>
</div>
@endsection
