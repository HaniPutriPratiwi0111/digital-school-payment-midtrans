@extends('layouts.app')
@section('title', 'Detail Kelas')
@section('content')
<div class="row">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Detail Kelas: {{ $kela->tingkat }} {{ $kela->nama_kelas }}</h4></div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><th>Jenjang</th><td>{{ $kela->jenjang->nama_jenjang }}</td></tr>
                    <tr><th>Tingkat/Kelas</th><td>{{ $kela->tingkat }} {{ $kela->nama_kelas }}</td></tr>
                    <tr><th>Wali Kelas</th><td>{{ $kela->waliKelas->nama ?? 'Belum Ditentukan' }}</td></tr>
                    <tr><th>Jumlah Siswa</th><td>{{ $kela->siswas->count() }} Orang</td></tr>
                </table>

                <h5 class="mt-4">Daftar Siswa di Kelas Ini</h5>
                <table class="table table-striped">
                    <thead><tr><th>NISN</th><th>Nama Siswa</th><th>Nama Wali</th></tr></thead>
                    <tbody>
                        @foreach($kela->siswas as $siswa)
                            <tr>
                                <td>{{ $siswa->nisn }}</td>
                                <td><a href="{{ route('siswa.show', $siswa) }}">{{ $siswa->nama_siswa }}</a></td>
                                <td>{{ $siswa->nama_wali_murid }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href="{{ route('kelas.edit', $kela) }}" class="btn btn-warning">Edit Kelas</a>
                <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection