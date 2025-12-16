@extends('layouts.app')
@section('title', 'Detail Jenjang')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Detail Jenjang: {{ $jenjang->nama_jenjang }}</h4></div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><th>ID</th><td>{{ $jenjang->id }}</td></tr>
                    <tr><th>Nama Jenjang</th><td>{{ $jenjang->nama_jenjang }}</td></tr>
                    <tr><th>Dibuat Pada</th><td>{{ $jenjang->created_at->format('d/m/Y H:i') }}</td></tr>
                </table>
                {{-- PERBAIKAN: Pembatasan Tombol Edit --}}
                @can('master-jenjang')
                <a href="{{ route('master-jenjang.edit', $jenjang) }}" class="btn btn-warning">Edit Data</a>
                @endcan
                <a href="{{ route('master-jenjang.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection