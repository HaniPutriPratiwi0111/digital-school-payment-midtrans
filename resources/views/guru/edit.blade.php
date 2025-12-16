@extends('layouts.app')
@section('title', 'Edit Data Guru')
@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Form Edit Guru dan Akun Login</h4></div>
            <div class="card-body">
                <form action="{{ route('guru.update', $guru) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <h5>Data Pribadi Guru</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $guru->nama) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="nuptk">NUPTK</label>
                            <input type="text" name="nuptk" class="form-control" value="{{ old('nuptk', $guru->nuptk) }}">
                        </div>
                    </div>
                    <h5 class="mt-4">Data Akun Login</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="email">Email (Username Login)</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $guru->user->email) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="password">Password (Kosongkan jika tidak ingin diubah)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Data Guru</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection