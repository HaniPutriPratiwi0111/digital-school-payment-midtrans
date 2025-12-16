@extends('layouts.app')
@section('title', 'Tambah Kelas')
@section('content')
<div class="row">
    <div class="col-12"> {{-- Card full --}}
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form action="{{ route('kelas.store') }}" method="POST">
                    @csrf

                    {{-- Jenjang --}}
                    <div class="form-group">
                        <label for="id_jenjang">Jenjang <span class="text-danger">*</span></label>
                        <select name="id_jenjang" id="id_jenjang" class="form-control @error('id_jenjang') is-invalid @enderror" required>
                            <option value="">Pilih Jenjang</option>
                            @foreach($jenjangs as $jenjang)
                                <option value="{{ $jenjang->id }}" {{ old('id_jenjang') == $jenjang->id ? 'selected' : '' }}>
                                    {{ $jenjang->nama_jenjang }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_jenjang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tingkat --}}
                    <div class="form-group">
                        <label for="tingkat">Tingkat / Kelas <span class="text-danger">*</span></label>
                        <select name="tingkat" id="tingkat" class="form-control @error('tingkat') is-invalid @enderror" required>
                            <option value="">-- Pilih Tingkat --</option>
                        </select>
                        @error('tingkat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nama Kelas --}}
                    <div class="form-group">
                        <label for="nama_kelas">Nama Kelas (Contoh: A, B, C) <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="nama_kelas" 
                               class="form-control @error('nama_kelas') is-invalid @enderror" 
                               value="{{ old('nama_kelas') }}" 
                               required>
                        @error('nama_kelas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Wali Kelas --}}
                    <div class="form-group">
                        <label for="id_guru_wali_kelas">Wali Kelas (Guru)</label>
                        <select name="id_guru_wali_kelas" class="form-control @error('id_guru_wali_kelas') is-invalid @enderror">
                            <option value="">-- Tidak Ada Wali Kelas --</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}" {{ old('id_guru_wali_kelas') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_guru_wali_kelas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Hanya guru yang belum menjadi wali kelas di kelas lain yang akan muncul.
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenjangSelect = document.getElementById('id_jenjang');
    const tingkatSelect = document.getElementById('tingkat');

    const tingkatMap = {
        'SD': [1,2,3,4,5,6],
        'SMP': [7,8,9],
        'SMA': [10,11,12]
    };

    function updateTingkat() {
        const jenjangText = jenjangSelect.selectedOptions[0]?.text;
        tingkatSelect.innerHTML = '<option value="">-- Pilih Tingkat --</option>';

        if(tingkatMap[jenjangText]){
            tingkatMap[jenjangText].forEach(t => {
                const opt = document.createElement('option');
                opt.value = t;
                opt.textContent = t;
                // set old value jika ada
                if("{{ old('tingkat') }}" == t) opt.selected = true;
                tingkatSelect.appendChild(opt);
            });
        }
    }

    jenjangSelect.addEventListener('change', updateTingkat);

    // jalankan saat halaman load untuk old value
    if(jenjangSelect.value) updateTingkat();
});
</script>
@endsection
