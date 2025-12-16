@extends('layouts.app')
@section('title', 'Master Data Guru')
@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="card-title">Daftar Guru</h4>
                <a href="{{ route('guru.create') }}" class="btn btn-primary">Tambah Guru</a>
            </div>
            <div class="card-body p-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NUPTK</th>
                            <th>Wali Kelas</th>
                            <th>Email Login</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gurus as $guru)
                        <tr>
                            <td>{{ $loop->iteration + $gurus->firstItem() - 1 }}</td> {{-- Perbaikan: Nomor urut berdasarkan pagination --}}
                            <td>{{ $guru->nama }}</td>
                            <td>{{ $guru->nuptk ?? '-' }}</td>
                            {{-- Mengambil data kelasDiwalikan dengan aman --}}
                            <td>{!! $guru->kelasDiwalikan ? $guru->kelasDiwalikan->tingkat . ' ' . $guru->kelasDiwalikan->nama_kelas : '<span class="badge bg-secondary">Tidak Ada</span>' !!}</td>
                            <td>{{ $guru->user->email }}</td>
                            <td>
                                <a href="{{ route('guru.show', $guru) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('guru.edit', $guru) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('guru.destroy', $guru) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus guru {{ $guru->nama }} dan akunnya? Tindakan ini tidak dapat dibatalkan.')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Tambahkan Pagination Links --}}
            @if ($gurus->lastPage() > 1)
            <div class="card-footer">
                {{ $gurus->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection