@extends('layouts.app')

@section('title', 'Buat Role Baru')

@section('content')
<div class="row">
    <div class="col-lg-12">
        {{-- Menampilkan alert seperti pesan sukses/error --}}
        @include('layouts.alerts')
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="header-title">
                    <h4 class="card-title">Buat Role Baru & Atur Hak Akses</h4>
                </div>
                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ri-arrow-left-line"></i> Kembali ke Daftar Role
                </a>
            </div>
            
            <div class="card-body">
                
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    
                    {{-- Bagian 1: Nama Role --}}
                    <div class="mb-4 p-4 border border-primary rounded-3 bg-light">
                        <h5 class="mb-3 text-primary">Informasi Role Utama</h5>
                        <div class="form-group">
                            <label for="role_name" class="form-label fw-bold">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="role_name" class="form-control" placeholder="Contoh: Bendahara " value="{{ old('name') }}" required>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    
                    {{-- Bagian 2: Atur Hak Akses (Permissions) --}}
                    <div class="mt-4 p-4 border rounded-3">
                        <h5 class="mb-3 text-primary">Pengaturan Hak Akses (Permissions)</h5>
                        <p class="text-muted border-bottom pb-3">Berikan izin atau kewenangan yang akan dimiliki oleh Role ini. Izin dikelompokkan berdasarkan modul.</p>

                        {{-- 🛠️ LOGIKA MAPPING DESKRIPSI AKSI --}}
                        @php
                            // Mendefinisikan kamus terjemahan untuk aksi teknis ke deskriptif
                            $actionLabels = [
                                'index'   => 'Melihat Daftar',
                                'create'  => 'Membuat Data Baru',
                                'store'   => 'Menyimpan Data Baru',
                                'show'    => 'Melihat Detail',
                                'edit'    => 'Mengubah Data',
                                'update'  => 'Memperbarui Data',
                                'destroy' => 'Menghapus Data',
                                'export'  => 'Export Data ke File',
                                // Untuk aksi spesifik lain
                                'log'     => 'Melihat Log Notifikasi',
                            ];
                        @endphp

                        <div class="permission-groups">
                            @foreach($permissionGroups as $groupName => $permissions)
                                <div class="card mb-3 shadow-sm border-secondary">
                                    <div class="card-header bg-secondary bg-opacity-10">
                                        <h6 class="mb-0 text-dark fw-bold">{{ $groupName }}</h6>
                                    </div>
                                    <div class="card-body row">
                                        @foreach($permissions as $permission)
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                            name="permissions[]" 
                                                            value="{{ $permission->name }}" 
                                                            id="perm-{{ $permission->id }}">
                                                    
                                                    @php
                                                        // Mengambil kata aksi (misal: 'index' dari 'master-jenjang.index')
                                                        $technicalAction = explode('.', $permission->name)[1] ?? 'index';
                                                        
                                                        // Menentukan label deskriptif
                                                        $displayLabel = $actionLabels[$technicalAction] ?? Str::title($technicalAction);
                                                    @endphp

                                                    {{-- Tampilan Label yang Deskriptif --}}
                                                    <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                        <strong class="text-dark">{{ $displayLabel }}</strong> 
                                                        <span class="text-muted small">({{ $permission->name }})</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-success mt-4">
                            <i class="ri-save-line"></i> Simpan Role & Hak Akses
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection