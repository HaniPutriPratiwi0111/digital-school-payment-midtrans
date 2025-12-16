@extends('layouts.app')
@section('title', 'Master Tahun Ajaran')

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="card-title">Daftar Tahun Ajaran</h4>
                <a href="{{ route('tahun-ajaran.create') }}" class="btn btn-primary">Tambah Tahun Ajaran</a>
            </div>

            <div class="card-body p-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Tahun</th>
                            <th>Status Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tahun_ajarans as $ta)
                        <tr>
                            <td>{{ $tahun_ajarans->firstItem() + $loop->index }}</td>
                            <td>{{ $ta->nama_tahun }}</td>
                            <td>
                                @if($ta->is_aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('tahun-ajaran.edit', $ta) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('tahun-ajaran.destroy', $ta) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data tahun ajaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION CUSTOM --}}
            <div class="card-footer d-flex justify-content-between align-items-center">
                {{-- Tombol Previous --}}
                @if ($tahun_ajarans->onFirstPage())
                    <span class="btn btn-secondary disabled">Previous</span>
                @else
                    <a href="{{ $tahun_ajarans->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" 
                       class="btn btn-primary">Previous</a>
                @endif

                {{-- Info Halaman --}}
                <span class="fw-bold">
                    Halaman {{ $tahun_ajarans->currentPage() }} dari {{ $tahun_ajarans->lastPage() }}
                </span>

                {{-- Tombol Next --}}
                @if ($tahun_ajarans->hasMorePages())
                    <a href="{{ $tahun_ajarans->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" 
                       class="btn btn-primary">Next</a>
                @else
                    <span class="btn btn-secondary disabled">Next</span>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
