@extends('layouts.app')
@section('title', 'Master Siswa')
@section('content')

<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card p-3">
            <form method="GET" class="row g-2 align-items-end">

                {{-- Tahun Ajaran --}}
                <div class="col-md-3">
                    <label>Tahun Ajaran</label>
                    <select name="id_tahun_ajaran" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($tahunAjarans as $tahun)
                            <option value="{{ $tahun->id }}" {{ request('id_tahun_ajaran') == $tahun->id ? 'selected' : '' }}>
                                {{ $tahun->nama_tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenjang --}}
                <div class="col-md-3">
                    <label>Jenjang</label>
                    <select name="id_jenjang" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Semua Jenjang --</option>
                        @foreach($jenjangs as $jenjang)
                            <option value="{{ $jenjang->id }}" {{ request('id_jenjang') == $jenjang->id ? 'selected' : '' }}>
                                {{ $jenjang->nama_jenjang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kelas --}}
                <div class="col-md-3">
                    <label>Kelas</label>
                    <select name="id_kelas" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($kelasAll as $k)
                            <option value="{{ $k->id }}" {{ request('id_kelas') == $k->id ? 'selected' : '' }}>
                                {{ $k->tingkat }} {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Reset --}}
                <div class="col-md-3">
                    <a href="{{ route('siswa.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

            </form>
        </div>
    </div>
</div>

            {{-- Pesan Sukses / Error / Warning --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif

{{-- ==================== TABEL SISWA ==================== --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Daftar Siswa</h4>
                <div>
                    <a href="{{ route('siswa.create') }}" class="btn btn-primary">Tambah Siswa</a>
                    <a href="{{ route('siswa.naikKelas.form') }}" class="btn btn-success me-2">Naik Kelas Massal</a>
                </div>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>NISN</th>
                                <th>Jenjang / Kelas</th>
                                <th>Tahun Ajaran</th>
                                <th>Keluarga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $tahunFilter = request('id_tahun_ajaran') ?? $tahunAktif->id;
                                $idKelasFilter = request('id_kelas');
                                $idJenjangFilter = request('id_jenjang');
                                $adaData = false; // flag untuk cek ada data tampil
                            @endphp

                            @forelse ($siswas as $siswa)
                                @php
                                    $filteredKelasTahun = $siswa->siswaKelasTahun
                                        ->where('id_tahun_ajaran', $tahunFilter);

                                    if ($idKelasFilter) {
                                        $filteredKelasTahun = $filteredKelasTahun->where('id_kelas', $idKelasFilter);
                                    } elseif ($idJenjangFilter) {
                                        $filteredKelasTahun = $filteredKelasTahun->filter(function($item) use ($idJenjangFilter) {
                                            return $item->kelas->id_jenjang == $idJenjangFilter;
                                        });
                                    }

                                    $pivot = $filteredKelasTahun->first();
                                @endphp

                                @if ($pivot)
                                    @php $adaData = true; @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $siswa->nama_siswa }}</td>
                                        <td>{{ $siswa->nisn }}</td>

                                        {{-- Jenjang / Kelas --}}
                                        <td>
                                            {{ $pivot->kelas->jenjang->nama_jenjang ?? '-' }} /
                                            {{ $pivot->kelas->tingkat ?? '-' }} {{ $pivot->kelas->nama_kelas ?? '-' }}
                                        </td>

                                        {{-- Tahun Ajaran --}}
                                        <td>{{ $pivot->tahunAjaran->nama_tahun ?? '-' }}</td>

                                        {{-- Keluarga --}}
                                        <td>
                                            <span class="badge {{ $siswa->is_keluarga ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $siswa->is_keluarga ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </td>

                                        {{-- Aksi --}}
                                        <td>
                                            <a href="{{ route('siswa.edit', $siswa) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('siswa.destroy', $siswa) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Hapus siswa dan akun walimurid?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr><td colspan="7" class="text-center">Tidak ada data siswa.</td></tr>
                            @endforelse

                            @if (!$adaData)
                                <tr><td colspan="7" class="text-center">Tidak ada data siswa.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINATION --}}
            <div class="card-footer d-flex justify-content-between align-items-center">
                
                {{-- Prev --}}
                @if ($siswas->onFirstPage())
                    <span class="btn btn-secondary disabled">Previous</span>
                @else
                    <a href="{{ $siswas->previousPageUrl() }}{{ strpos($siswas->previousPageUrl(), '?') ? '&' : '?' }}{{ http_build_query(request()->except('page')) }}" 
                       class="btn btn-primary">Previous</a>
                @endif

                <span class="fw-bold">Halaman {{ $siswas->currentPage() }} dari {{ $siswas->lastPage() }}</span>

                {{-- Next --}}
                @if ($siswas->hasMorePages())
                    <a href="{{ $siswas->nextPageUrl() }}{{ strpos($siswas->nextPageUrl(), '?') ? '&' : '?' }}{{ http_build_query(request()->except('page')) }}" 
                       class="btn btn-primary">Next</a>
                @else
                    <span class="btn btn-secondary disabled">Next</span>
                @endif

            </div>

        </div>
    </div>
</div>

@endsection
