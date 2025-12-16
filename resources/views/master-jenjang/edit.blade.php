@extends('layouts.app')

@section('title', 'Edit Jenjang')

@section('content')
@can('master-jenjang.edit')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="header-title">
                    <h4 class="card-title">Edit Data Jenjang</h4>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('master-jenjang.update', $jenjang) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="nama_jenjang">Nama Jenjang</label>
                        <input type="text" name="nama_jenjang" id="nama_jenjang"
                            class="form-control @error('nama_jenjang') is-invalid @enderror"
                            value="{{ old('nama_jenjang', $jenjang->nama_jenjang) }}" required>

                        {{-- Helper text --}}
                        <small class="form-text text-muted">Masukkan jenjang sekolah (contoh: SD, SMP, SMA, dst)</small>

                        {{-- Error message --}}
                        @error('nama_jenjang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('master-jenjang.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@else
    <div class="alert alert-danger">Anda tidak memiliki izin untuk mengakses halaman ini.</div>
@endcan
@endsection
