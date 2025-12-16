@extends('layouts.app')

@section('title', 'Role & Permissions')

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title">
                    <h4 class="card-title">Daftar Role dan Hak Akses</h4>
                </div>
                @can('role.create') 
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Role
                </a>
                @endcan
            </div>
            
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th>Nama Role</th>
                                <th>Hak Akses</th>
                                @if (Auth::user()->can('role.edit') || Auth::user()->can('role.destroy'))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $role)
                            <tr>
                                {{-- Nomor urut --}}
                                <td>{{ $roles->firstItem() + $loop->index }}</td>

                                {{-- Nama Role --}}
                                <td>{{ $role->name }}</td>

                                {{-- Jumlah Permission --}}
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $role->permissions->count() }} Permissions
                                    </span>
                                </td>
                                
                                {{-- Aksi --}}
                                @if (Auth::user()->can('role.edit') || Auth::user()->can('role.destroy'))
                                <td class="text-nowrap">
                                    @can('role.edit')
                                    <a href="{{ route('roles.edit', $role->id) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit Role & Hak Akses">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @endcan
                                    
                                    @can('role.destroy')
                                    @if ($role->name !== 'Super Admin')
                                    <form action="{{ route('roles.destroy', $role->id) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus role \'{{ $role->name }}\'? Ini akan melepaskan hak akses dari semua pengguna yang memilikinya.')"
                                                title="Hapus Role">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </td>
                                @endif
                            </tr>

                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada role yang ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- PAGINATION SELALU TAMPIL --}}
            @if ($roles->total() > 0)
            <div class="card-footer d-flex justify-content-between align-items-center">

                {{-- Tombol Previous --}}
                @if ($roles->onFirstPage())
                    <span class="btn btn-secondary disabled">Previous</span>
                @else
                    <a href="{{ $roles->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                       class="btn btn-primary">Previous</a>
                @endif

                {{-- Info Halaman --}}
                <span class="fw-bold">
                    Halaman {{ $roles->currentPage() }} dari {{ $roles->lastPage() }}
                </span>

                {{-- Tombol Next --}}
                @if ($roles->hasMorePages())
                    <a href="{{ $roles->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                       class="btn btn-primary">Next</a>
                @else
                    <span class="btn btn-secondary disabled">Next</span>
                @endif

            </div>
            @endif

        </div>
    </div>
</div>
@endsection
