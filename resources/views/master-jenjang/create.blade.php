@extends('layouts.app')

@section('title', isset($jenjang) ? 'Edit Jenjang' : 'Tambah Jenjang')

@section('content')
@can('master-jenjang.create')
<div class="row justify-content-center">
    <div class="col-12"> {{-- ubah dari col-md-6 ke col-12 supaya penuh --}}
        <div class="card">
            <div class="card-header">
                <div class="header-title">
                    <h4 class="card-title">{{ isset($jenjang) ? 'Edit' : 'Tambah' }} Data Jenjang</h4>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ isset($jenjang) ? route('master-jenjang.update', $jenjang) : route('master-jenjang.store') }}" method="POST">
                    @csrf
                    @if(isset($jenjang))
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="nama_jenjang">Nama Jenjang</label>
                        <input type="text" name="nama_jenjang" id="nama_jenjang" 
                            class="form-control @error('nama_jenjang') is-invalid @enderror" 
                            value="{{ old('nama_jenjang', $jenjang->nama_jenjang ?? '') }}" required>
                        
                        {{-- Small helper text selalu tampil --}}
                        <small class="form-text text-muted">Masukkan jenjang sekolah (contoh: SD, SMP, SMA, dst)</small>

                        {{-- Validasi error --}}
                        @error('nama_jenjang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary">{{ isset($jenjang) ? 'Update' : 'Simpan' }}</button>
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
