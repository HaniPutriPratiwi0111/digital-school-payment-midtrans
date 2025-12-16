@extends('layouts.app')
@section('title', 'Daftar Aturan Nominal')

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Daftar Pengaturan Nominal Pembayaran</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#duplicateRulesModal">
                        <i class="fas fa-copy"></i> Duplikasi Aturan Tahun
                    </button>
                    <a href="{{ route('atur-nominal.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Aturan Baru
                    </a>
                </div>
            </div>

            <div class="card-body p-3">
                {{-- Filter --}}
                <form id="filterForm" method="GET" action="{{ route('atur-nominal.index') }}" class="d-flex gap-2 mb-3">
                    
                    {{-- Dropdown Tahun Ajaran --}}
                    <select name="tahun_ajaran" class="form-select w-auto" id="tahunAjaranFilterSelect">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach ($tahun_ajarans as $ta)
                            <option value="{{ $ta->id }}" 
                                {{ (request('tahun_ajaran') ?? $tahun_ajaran_aktif->id) == $ta->id ? 'selected' : '' }}>
                                {{ $ta->nama_tahun }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Dropdown Jenis Pembayaran --}}
                    <select name="jenis" class="form-select w-auto" id="jenisFilterSelect">
                        <option value="">Semua Jenis Pembayaran</option>
                        @foreach ($jenis_pembayarans as $jenis)
                            <option value="{{ $jenis->nama_jenis }}" 
                                {{ request('jenis') == $jenis->nama_jenis ? 'selected' : '' }}>
                                {{ $jenis->nama_jenis }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Tombol Filter disembunyikan (auto-submit) --}}
                    <button type="submit" class="btn btn-secondary btn-sm d-none">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th>Tahun Ajaran</th>
                                <th>Jenis Pembayaran</th>
                                <th>Jenjang/Tingkat</th>
                                <th>Bulan Berlaku</th>
                                <th>Nominal Normal</th>
                                <th>Nominal Keluarga</th>
                                <th style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nominals as $nominal)
                            <tr>
                                <td>{{ $nominals->firstItem() + $loop->index }}</td>
                                <td>{{ $nominal->tahunAjaran->nama_tahun ?? 'N/A' }}</td>
                                <td>{{ $nominal->jenisPembayaran->nama_jenis ?? 'N/A' }}</td>
                                <td>{{ $nominal->jenjang->nama_jenjang ?? 'N/A' }} / {{ $nominal->tingkat ?? 'Semua' }}</td>
                                <td>
                                    @if($nominal->bulan_berlaku)
                                        @php
                                            $bulan = \Carbon\Carbon::create(null, $nominal->bulan_berlaku)
                                                ->locale('id')->isoFormat('MMMM');
                                        @endphp
                                        <span class="badge bg-primary">{{ $bulan }} (Bulanan)</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Semua Bulan</span>
                                    @endif
                                </td>
                                <td>Rp. {{ number_format($nominal->nominal_normal, 0, ',', '.') }}</td>
                                <td>
                                    @if ($nominal->nominal_keluarga > 0)
                                        <span class="badge bg-success">Rp. {{ number_format($nominal->nominal_keluarga, 0, ',', '.') }}</span>
                                    @else
                                        <span class="badge bg-secondary">TIDAK ADA</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('atur-nominal.edit', $nominal) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr class="no-data-row">
                                <td colspan="8" class="text-center">Tidak ada data nominal yang ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination Custom --}}
            {{-- ... (Bagian pagination tetap sama) ... --}}
            <div class="card-footer d-flex justify-content-between align-items-center">
                {{-- Previous --}}
                @if ($nominals->onFirstPage())
                    <span class="btn btn-secondary disabled">Previous</span>
                @else
                    <a href="{{ $nominals->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="btn btn-primary">Previous</a>
                @endif

                {{-- Info Halaman --}}
                <span class="fw-bold">
                    Halaman {{ $nominals->currentPage() }} dari {{ $nominals->lastPage() }}
                </span>

                {{-- Next --}}
                @if ($nominals->hasMorePages())
                    <a href="{{ $nominals->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="btn btn-primary">Next</a>
                @else
                    <span class="btn btn-secondary disabled">Next</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal Duplikasi --}}
{{-- ... (Bagian modal tetap sama) ... --}}
<div class="modal fade" id="duplicateRulesModal" tabindex="-1" aria-labelledby="duplicateRulesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('atur-nominal.duplicate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Duplikasi Aturan ke Tahun Ajaran Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        Duplikasi akan menyalin semua aturan dari Tahun Ajaran lama ke Tahun Ajaran baru. 
                        Hanya aturan yang <strong>belum ada</strong> di tahun baru yang akan disalin.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">Duplikasi Dari Tahun Ajaran:</label>
                        <select name="id_tahun_ajaran_lama" class="form-select" required>
                            <option value="">-- Pilih Tahun Ajaran Lama --</option>
                            @foreach ($tahun_ajarans as $ta)
                                <option value="{{ $ta->id }}">{{ $ta->nama_tahun }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Duplikasi Ke Tahun Ajaran:</label>
                        <select name="id_tahun_ajaran_baru" class="form-select" required>
                            <option value="">-- Pilih Tahun Ajaran Baru --</option>
                            @foreach ($tahun_ajarans as $ta)
                                <option value="{{ $ta->id }}">{{ $ta->nama_tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-arrow-right"></i> Duplikasi Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- Script auto-submit --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['jenisFilterSelect', 'tahunAjaranFilterSelect'].forEach(id => {
        const selectElement = document.getElementById(id);
            if (selectElement) {
            selectElement.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
            });
        }});
    });
</script>
@endpush