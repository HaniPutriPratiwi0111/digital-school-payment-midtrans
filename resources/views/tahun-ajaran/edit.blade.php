@extends('layouts.app')
@section('title', 'Edit Tahun Ajaran')
@section('content')
<div class="row justify-content-center">
    <div class="col-12"> {{-- full width --}}
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Tahun Ajaran</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('tahun-ajaran.update', $tahunAjaran) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="nama_tahun">Nama Tahun <span class="text-danger">*</span> (Contoh: 2025/2026)</label>
                        <input type="text" 
                               name="nama_tahun" 
                               id="nama_tahun"
                               class="form-control @error('nama_tahun') is-invalid @enderror" 
                               value="{{ old('nama_tahun', $tahunAjaran->nama_tahun) }}" 
                               required
                               pattern="\d{4}/\d{4}" 
                               title="Format harus 4 digit/4 digit, contoh: 2025/2026"
                               maxlength="9"
                               onkeypress="return /[0-9\/]/.test(event.key)">
                        @error('nama_tahun') 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                        <small class="form-text text-muted">Masukkan tahun ajaran dengan format: 2025/2026</small>
                    </div>
                    
                    <div class="form-group form-check">
                        <input type="checkbox" name="is_aktif" class="form-check-input" id="is_aktif" value="1" {{ old('is_aktif', $tahunAjaran->is_aktif) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_aktif">Set sebagai Tahun Ajaran Aktif</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('tahun-ajaran.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Mencegah input huruf, hanya angka dan /
    document.getElementById('nama_tahun').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9\/]/g, '');
    });
</script>
@endsection
