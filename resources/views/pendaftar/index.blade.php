@extends('layouts.app')

@section('title', 'Pendaftar Baru')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                     Data Pendaftar Baru
                </h4>
            </div>

            <div class="card-body p-3">
                {{-- Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                {{-- Table --}}
                <div class="table-responsive card-body p-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Wali Murid</th>
                                <th>Telp Wali</th>
                                <th>Status Pembayaran</th>
                                <th>Status Approval</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendaftar as $s)
                            <tr>
                                <td>{{ $pendaftar->firstItem() + $loop->index }}</td>
                                <td>{{ $s->nama_siswa }}</td>
                                <td>{{ $s->nama_wali_murid }}</td>
                                <td>{{ $s->telp_wali_murid }}</td>
                                <td>
                                    @if ($s->payment_status == 'Lunas')
                                        <span class="badge bg-success">Lunas</span>
                                    @elseif ($s->payment_status == 'Gagal')
                                        <span class="badge bg-danger">Gagal</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Menunggu</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($s->approval_status == 'Disetujui')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif ($s->approval_status == 'Ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-secondary">Diajukan</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($s->payment_status == 'Lunas' && $s->approval_status == 'Diajukan')
                                        <a href="{{ route('siswa.create-from-calon', $s->id) }}"
                                            class="btn btn-sm btn-success">
                                            Approve Siswa
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada pendaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Custom Pagination --}}
            <div class="card-footer d-flex justify-content-between align-items-center">
                {{-- Previous --}}
                @if ($pendaftar->onFirstPage())
                    <span class="btn btn-secondary disabled">Previous</span>
                @else
                    <a href="{{ $pendaftar->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="btn btn-primary">Previous</a>
                @endif

                {{-- Info Halaman --}}
                <span class="fw-bold">
                    Halaman {{ $pendaftar->currentPage() }} dari {{ $pendaftar->lastPage() }}
                </span>

                {{-- Next --}}
                @if ($pendaftar->hasMorePages())
                    <a href="{{ $pendaftar->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="btn btn-primary">Next</a>
                @else
                    <span class="btn btn-secondary disabled">Next</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
