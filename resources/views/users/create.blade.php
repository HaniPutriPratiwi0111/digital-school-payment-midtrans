@extends('layouts.app')
@section('title', 'Tambah User')
@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Form Tambah Akun User</h4>
            </div>

            <div class="card-body">
                @include('layouts.alerts')

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label class="form-label">Email (Username Login)</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   minlength="8"
                                   maxlength="64"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <small class="text-muted">
                            Password minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.
                        </small>

                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   minlength="8"
                                   maxlength="64"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

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
                                           {{ old('roles', 'walimurid') == $role->name ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        {{ ucfirst($role->name) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <button class="btn btn-primary">Simpan User</button>
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
