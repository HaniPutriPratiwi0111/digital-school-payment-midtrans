@extends('layouts.app')
@section('title', 'Detail Guru')
@section('content')
<div class="row">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Detail Guru: {{ $guru->nama }}</h4></div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><th>Nama</th><td>{{ $guru->nama }}</td></tr>
                    <tr><th>NUPTK</th><td>{{ $guru->nuptk }}</td></tr>
                    <tr><th>Wali Kelas</th><td>{!! $guru->kelasDiwalikan ? 'Kelas ' . $guru->kelasDiwalikan->tingkat . ' ' . $guru->kelasDiwalikan->nama_kelas : '<span class="badge bg-secondary">Bukan Wali Kelas</span>' !!}</td></tr>
                    <tr><th>Email Akun</th><td>{{ $guru->user->email }}</td></tr>
                    <tr><th>Role Akun</th><td><span class="badge bg-info">{{ strtoupper($guru->user->role) }}</span></td></tr>
                </table>
                <a href="{{ route('guru.edit', $guru) }}" class="btn btn-warning">Edit Data</a>
                <a href="{{ route('guru.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection