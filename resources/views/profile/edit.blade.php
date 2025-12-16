@extends('layouts.app')
@section('title', 'Settings Profile')

@section('title', 'Profile Settings')

@section('content')
<div class="container">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Card Profile --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5>Update Profile</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="avatar" class="form-label">Foto Profil</label><br>
                    @if($user->avatar)
                        <img src="{{ asset('storage/avatars/'.$user->avatar) }}" alt="avatar" class="img-thumbnail mb-2" width="120">
                    @endif
                    <input type="file" class="form-control" name="avatar">
                    @error('avatar') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    {{-- Card Password --}}
    <div class="card">
        <div class="card-header">
            <h5>Ganti Password</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('profile.updatePassword') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="current_password" class="form-label">Password Lama</label>
                    <input type="password" class="form-control" name="current_password">
                    @error('current_password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password Baru</label>
                    <input type="password" class="form-control" name="password">
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" name="password_confirmation">
                </div>

                <button type="submit" class="btn btn-warning">Ganti Password</button>
            </form>
        </div>
    </div>
</div>
@endsection