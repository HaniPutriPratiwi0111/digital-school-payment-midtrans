@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Form Edit Akun User: {{ $user->name }}</h4>
            </div>

            <div class="card-body">
                @include('layouts.alerts')

                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- PENTING: Untuk method UPDATE --}}

                    {{-- Nama Lengkap --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            {{-- Nilai diambil dari $user atau old() --}}
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label">Email (Username Login)</label>
                        {{-- Email juga diambil dari $user --}}
                        <input type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr>
                    <h5 class="mt-4 mb-3">Ubah Password (Kosongkan jika tidak ingin diubah)</h5>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            {{-- Tidak ada "required" di sini, karena sifatnya opsional --}}
                            <input type="password"
                                   name="password"
                                   id="password_edit"
                                   minlength="8"
                                   maxlength="64"
                                   class="form-control @error('password') is-invalid @enderror">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_edit">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <small class="text-muted">
                            Kosongkan jika tidak ingin mengubah password. Jika diisi, minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.
                        </small>

                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            {{-- Tidak ada "required" di sini --}}
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation_edit"
                                   minlength="8"
                                   maxlength="64"
                                   class="form-control @error('password_confirmation') is-invalid @enderror">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation_edit">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr>

                    {{-- Role --}}
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <div>
                            @foreach ($roles as $role)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                        type="radio"
                                        name="roles"
                                        value="{{ $role->name }}"
                                        {{-- Cek apakah role ini adalah role user saat ini, atau gunakan old() --}}
                                        {{ (old('roles', $user->roles->pluck('name')->first() ?? 'walimurid') == $role->name) ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        {{ ucfirst($role->name) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <button class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Toggle Password --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Memastikan id yang digunakan di tombol dan input cocok dengan id baru:
    // password_edit dan password_confirmation_edit
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            const icon = btn.querySelector('i');
            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isPassword);
            icon.classList.toggle('bi-eye-slash', isPassword);
        });
    });
});
</script>
@endpush
@endsection