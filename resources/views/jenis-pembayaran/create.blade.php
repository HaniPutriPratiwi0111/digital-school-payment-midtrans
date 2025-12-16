@extends('layouts.app')
@section('title', 'Tambah Jenis Pembayaran')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('jenis-pembayaran.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="nama_jenis">Nama Jenis Pembayaran (Contoh: SPP Bulanan)</label>
                        <input type="text" name="nama_jenis" class="form-control" value="{{ old('nama_jenis') }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('jenis-pembayaran.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
