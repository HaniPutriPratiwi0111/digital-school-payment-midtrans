@extends('layouts.app')
@section('title', 'Tambah Data Guru')
@section('content')
<div class="row">
    <div class="col-md-10"> {{-- Ubah menjadi col-md-10 agar form lebih lebar --}}
        @include('layouts.alerts') {{-- Pastikan alerts dipanggil di sini --}}
        <div class="card">
            <div class="card-header"><h4 class="card-title">Form Tambah Guru dan Akun Login</h4></div>
            <div class="card-body">
                <form action="{{ route('guru.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Bagian Data Pribadi Guru --}}
                    <h5>Data Pribadi Guru</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="nuptk">NUPTK (Opsional)</label>
                            <input type="text" name="nuptk" class="form-control @error('nuptk') is-invalid @enderror" value="{{ old('nuptk') }}">
                            @error('nuptk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="nip">NIP (Opsional)</label>
                            {{-- Kolom NIP yang baru ditambahkan ke model --}}
                            <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}"> 
                            @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="jenis_kelamin">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">Pilih...</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="agama">Agama</label>
                            <input type="text" name="agama" class="form-control @error('agama') is-invalid @enderror" value="{{ old('agama') }}" required>
                            @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="tempat_lahir">Tempat Lahir (Opsional)</label>
                            <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir') }}">
                            @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="tanggal_lahir">Tanggal Lahir (Opsional)</label>
                            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir') }}">
                            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="foto">Foto Profil (Opsional)</label>
                            <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror">
                            @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Bagian Data Akun Login --}}
                    <h5 class="mt-4">Data Akun Login</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="email">Email (Username Login)</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-3">Simpan Data Guru</button>
                    <a href="{{ route('guru.index') }}" class="btn btn-secondary mt-3">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection