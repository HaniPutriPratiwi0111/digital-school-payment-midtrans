@extends('layouts.app')
@section('title', 'Master Jenis Pembayaran')

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="card-title">Daftar Kategori Pembayaran</h4>
                <a href="{{ route('jenis-pembayaran.create') }}" class="btn btn-primary">Tambah Kategori</a>
            </div>
            <div class="card-body p-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jenis_pembayarans as $jenis)
                        <tr>
                            {{-- Nomor Urut disesuaikan untuk pagination --}}
                            <td>{{ $jenis_pembayarans->firstItem() + $loop->index }}</td>
                            <td>{{ $jenis->nama_jenis }}</td>
                            <td>
                                <a href="{{ route('jenis-pembayaran.edit', $jenis) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('jenis-pembayaran.destroy', $jenis) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hati-hati! Ini akan mempengaruhi data nominal dan tagihan. Lanjutkan?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data Kategori Pembayaran yang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

{{-- START: Custom Pagination --}}
            {{-- UBAH KONDISI DI SINI --}}
            {{-- Kita cek apakah ada data (count() > 0) atau apakah ada banyak halaman (hasPages()) --}}
            @if ($jenis_pembayarans->count() > 0 || $jenis_pembayarans->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center">
                    {{-- Previous Button --}}
                    @if ($jenis_pembayarans->onFirstPage())
                        <span class="btn btn-secondary disabled">Previous</span>
                    @else
                        <a href="{{ $jenis_pembayarans->previousPageUrl() }}" class="btn btn-primary">Previous</a>
                    @endif

                    {{-- Page Info --}}
                    <span class="fw-bold">
                        Halaman {{ $jenis_pembayarans->currentPage() }} dari {{ $jenis_pembayarans->lastPage() }}
                    </span>

                    {{-- Next Button --}}
                    {{-- Next akan didisable jika hanya ada 1 halaman --}}
                    @if ($jenis_pembayarans->hasMorePages())
                        <a href="{{ $jenis_pembayarans->nextPageUrl() }}" class="btn btn-primary">Next</a>
                    @else
                        <span class="btn btn-secondary disabled">Next</span>
                    @endif
                </div>
            @endif
            {{-- END: Custom Pagination --}}

        </div>
    </div>
</div>
@endsection