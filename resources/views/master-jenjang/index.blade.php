@extends('layouts.app')

@section('title', 'Master Jenjang')

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                    <h4 class="card-title">Daftar Jenjang</h4>
                </div>

                {{-- Tombol Tambah hanya muncul kalau user punya izin create --}}
                @can('master-jenjang.create')
                    <a href="{{ route('master-jenjang.create') }}" class="btn btn-primary">Tambah Jenjang</a>
                @endcan
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Jenjang</th>
                                @canany(['master-jenjang.edit', 'master-jenjang.destroy'])
                                    <th>Aksi</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jenjangs as $jenjang)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $jenjang->nama_jenjang }}</td>
                                    <td>
                                        {{-- <a href="{{ route('master-jenjang.show', $jenjang) }}" class="btn btn-sm btn-info">Detail</a> --}}

                                        @can('master-jenjang.edit')
                                            <a href="{{ route('master-jenjang.edit', $jenjang) }}" class="btn btn-sm btn-warning">Edit</a>
                                        @endcan

                                        @can('master-jenjang.destroy')
                                            <form action="{{ route('master-jenjang.destroy', $jenjang) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                {{ $jenjangs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
