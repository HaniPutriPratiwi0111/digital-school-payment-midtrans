@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Alerts --}}
    @include('layouts.alerts')
    @if(isset($errorMessage))
        <div class="alert alert-warning">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="card">
        {{-- Header Card --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Daftar Users</h4>
            <a href="{{ route('users.create') }}" class="btn btn-primary">Tambah User</a>         
        </div>
        
        {{-- Filter Role --}}
        <div class="card-body p-3">
            <form method="GET" id="roleFilterForm" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label for="search_role" class="form-label">Filter Role</label>
                        <select name="search_role" id="search_role" class="form-select">
                            <option value="">-- Semua Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ request('search_role') == $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabel Users --}}
        <div class="card-body p-3">
            <div class="table-responsive"> 
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama</th>
                            <th>Role</th>
                            <th style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                <td>{{ $user->name }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-info text-dark me-1">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">Edit</a>
                                </td>                            
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($users->lastPage() > 1)
        <div class="card-footer d-flex justify-content-between align-items-center">
            @if ($users->onFirstPage())
                <span class="btn btn-secondary disabled">Previous</span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="btn btn-primary">Previous</a>
            @endif

            <span class="fw-bold">
                Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}
            </span>

            @if ($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="btn btn-primary">Next</a>
            @else
                <span class="btn btn-secondary disabled">Next</span>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- Script untuk auto submit filter --}}
<script>
    document.getElementById('search_role').addEventListener('change', function() {
        document.getElementById('roleFilterForm').submit();
    });
</script>
@endsection
