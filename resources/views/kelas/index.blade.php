@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')

        <div class="card">

            {{-- Header Card --}}
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Daftar Kelas</h4>
                <a href="{{ route('kelas.create') }}" class="btn btn-primary">Tambah Kelas</a>
            </div>

            {{-- Body Card --}}
            <div class="card-body p-3">

                {{-- <div class="mb-3">
                    <span class="badge bg-success">Tahun Ajaran Aktif: {{ $tahunAktif->nama_tahun ?? '-' }}</span>
                </div> --}}

                {{-- Filter di bawah judul --}}
                <form method="GET" class="d-flex gap-2 mb-3">

                    {{-- Jenjang --}}
                    <select name="id_jenjang" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">-- Semua Jenjang --</option>
                        @foreach($jenjangs as $jenjang)
                            <option value="{{ $jenjang->id }}" {{ request('id_jenjang') == $jenjang->id ? 'selected' : '' }}>
                                {{ $jenjang->nama_jenjang }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Tingkat --}}
                    <select name="tingkat" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">-- Semua Tingkat --</option>
                        @foreach($tingkats as $tingkat)
                            <option value="{{ $tingkat }}" {{ request('tingkat') == $tingkat ? 'selected' : '' }}>
                                {{ $tingkat }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Reset --}}
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary ">Reset</a>
                </form>

                {{-- Tabel --}}
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Jenjang</th>
                                <th>Tingkat</th>
                                <th>Nama Kelas</th>
                                {{-- <th>Wali Kelas</th> --}}
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kelas as $kls)
                                <tr>
                                    <td>{{ $kelas->firstItem() + $loop->index }}</td>
                                    <td>{{ $kls->jenjang->nama_jenjang ?? '-' }}</td>
                                    <td>{{ $kls->tingkat }}</td>
                                    <td>{{ $kls->nama_kelas }}</td>
                                    {{-- <td>{{ $kls->waliKelas->nama ?? 'Belum Ditentukan' }}</td> --}}
                                    <td class="text-nowrap">
                                        <a href="{{ route('kelas.edit', $kls->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('kelas.destroy', $kls->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Yakin ingin menghapus kelas {{ $kls->jenjang->nama_jenjang ?? '' }} Tingkat {{ $kls->tingkat }} {{ $kls->nama_kelas }}?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data kelas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="card-footer d-flex justify-content-between align-items-center">
                @if ($kelas->onFirstPage())
                    <span class="btn btn-secondary disabled">Previous</span>
                @else
                    <a href="{{ $kelas->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="btn btn-primary">Previous</a>
                @endif

                <span class="fw-bold">
                    Halaman {{ $kelas->currentPage() }} dari {{ $kelas->lastPage() }}
                </span>

                @if ($kelas->hasMorePages())
                    <a href="{{ $kelas->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="btn btn-primary">Next</a>
                @else
                    <span class="btn btn-secondary disabled">Next</span>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
