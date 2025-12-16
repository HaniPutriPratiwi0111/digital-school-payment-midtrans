@extends('layouts.app')

@section('title', 'Edit Role: ' . $role->name)

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts') {{-- Menggunakan layout alerts Anda --}}
        
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                    <h4 class="card-title">Edit Role & Hak Akses: {{ $role->name }}</h4>
                </div>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Kembali ke Daftar Role</a>
            </div>
            
            <div class="card-body">
                
                {{-- ---------------------------------------------------------------- --}}
                {{-- BAGIAN 1: PERBARUI NAMA ROLE (Diambil dari contoh UI Anda) --}}
                {{-- ---------------------------------------------------------------- --}}
                <div class="mb-4 p-4 border rounded">
                    <h5 class="mb-3 text-primary">Perbarui Nama Role</h5>
                    
                    {{-- Form untuk mengupdate Nama Role saja, bisa dipecah untuk UI yang lebih baik --}}
                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="role_name" class="form-label">Nama Role <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="role_name" class="form-control" value="{{ old('name', $role->name) }}" required>
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" name="action" value="update_name" class="btn btn-primary">
                                    Perbarui Nama
                                </button>
                            </div>
                        </div>
                        
                        {{-- Penting: Jika Anda ingin menggunakan satu form untuk nama dan permission, hapus form ini --}}
                    </form>
                </div>
                
                {{-- ---------------------------------------------------------------- --}}
                {{-- BAGIAN 2: ATUR HAK AKSES (PERMISSIONS) --}}
                {{-- ---------------------------------------------------------------- --}}
                <div class="mt-4 p-4 border rounded">
                    <h5 class="mb-3 text-primary">Atur Hak Akses (Permissions)</h5>
                    <p class="text-muted">Centang izin yang dimiliki oleh Role ini. Ini akan mengganti hak akses yang lama.</p>

                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Hidden field untuk menjaga nama role tetap terkirim saat update permission --}}
                        <input type="hidden" name="name" value="{{ $role->name }}"> 
                        <input type="hidden" name="action" value="update_permissions">
                        
                        <div class="permission-groups">
                            @foreach($permissionGroups as $groupName => $permissions)
                                <div class="card mb-3 shadow-sm border-light">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 text-dark">{{ $groupName }}</h6>
                                    </div>
                                    <div class="card-body row">
                                        @foreach($permissions as $permission)
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->name }}" 
                                                           id="perm-{{ $permission->id }}"
                                                           {{-- Cek apakah permission ini dimiliki role --}}
                                                           {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                                    
                                                    <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                        {{-- Hanya tampilkan Aksi (index, create, edit, destroy, dll.) --}}
                                                        <strong>{{ Str::title(explode('.', $permission->name)[1] ?? 'Aksi') }}</strong>
                                                        <small class="text-muted">({{ $permission->name }})</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">Simpan Hak Akses</button>
                        </div>
                    </form>
                </div>
            </div>
            
            {{-- Card Footer Kosong atau bisa digunakan untuk navigasi/informasi lain --}}
            <div class="card-footer">
                
            </div>
        </div>
    </div>
</div>
@endsection