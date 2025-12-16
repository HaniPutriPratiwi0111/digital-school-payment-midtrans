@extends('layouts.app')
@section('title', 'Catat Pembayaran Tunai')
@section('content')
<div class="row justify-content-center">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Form Pencatatan Pembayaran Tunai</h4>
            </div>
            <div class="card-body">

                {{-- Error Laravel --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('pembayaran.store') }}" method="POST">
                    @csrf

                    {{-- Pilih Jenjang --}}
                    {{-- <div class="form-group mb-3">
                        <label for="jenjang">Pilih Jenjang</label>
                        <select id="jenjang" class="form-control select2">
                            <option value="">-- Semua Jenjang --</option>
                            @foreach($jenjangs as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jenjang }}</option>
                            @endforeach
                        </select>
                    </div> --}}

                    {{-- Pilih Tagihan --}}
                    <div class="form-group mb-3">
                        <label for="id_tagihan">Pilih Tagihan Siswa</label>
                        <select name="id_tagihan" id="id_tagihan" class="form-control select2 @error('id_tagihan') is-invalid @enderror" required>
                            <option value="">-- Cari Siswa / Tagihan --</option>
                            @foreach($tagihans as $tagihan)
                                @php
                                    $jenjangId = $tagihan->siswa->kelas->jenjang->id ?? '';
                                @endphp
                                <option value="{{ $tagihan->id }}" data-jenjang="{{ $jenjangId }}">
                                    {{ $tagihan->nama_tagihan }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_tagihan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal Bayar --}}
                    <div class="form-group mb-3">
                        <label for="tanggal_bayar">Tanggal Pembayaran</label>
                        <input type="date" 
                               name="tanggal_bayar" 
                               id="tanggal_bayar"
                               class="form-control @error('tanggal_bayar') is-invalid @enderror" 
                               value="{{ old('tanggal_bayar', \Carbon\Carbon::now()->toDateString()) }}" 
                               required>
                        @error('tanggal_bayar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div class="form-group mb-3">
                        <label for="metode_pembayaran">Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-control @error('metode_pembayaran') is-invalid @enderror" required>
                            <option value="Tunai" {{ old('metode_pembayaran') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                            <option value="Transfer" {{ old('metode_pembayaran') == 'Transfer' ? 'selected' : '' }}>Transfer (Manual)</option>
                        </select>
                        @error('metode_pembayaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Total Bayar --}}
                    <div class="form-group mb-3">
                        <label for="total_bayar">Jumlah Pembayaran</label>
                        <input type="number" 
                               name="total_bayar" 
                               id="total_bayar"
                               class="form-control @error('total_bayar') is-invalid @enderror" 
                               value="{{ old('total_bayar') }}" 
                               required 
                               min="1000">
                        @error('total_bayar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Masukkan jumlah yang dibayarkan. Sistem akan menyesuaikan status tagihan otomatis.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Catat Pembayaran
                    </button>
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary">Batal</a>
                </form>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('#jenjang').select2({ width: '100%' });
    $('#id_tagihan').select2({ width: '100%' });

    // Filter tagihan berdasarkan jenjang
    $('#jenjang').on('change', function() {
        let selectedJenjang = $(this).val();
        let allOptions = $('#id_tagihan option');

        // Kosongkan dropdown kecuali placeholder
        $('#id_tagihan').empty().append('<option value="">-- Cari Siswa / Tagihan --</option>');

        // Tambahkan opsi sesuai jenjang
        allOptions.each(function() {
            let jenjangOption = $(this).data('jenjang');
            if (!selectedJenjang || jenjangOption == selectedJenjang) {
                $('#id_tagihan').append($(this));
            }
        });

        // Reset Select2
        $('#id_tagihan').val('').trigger('change.select2');
    });
});
</script>
@endpush
@endsection
