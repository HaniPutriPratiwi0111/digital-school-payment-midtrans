@extends('layouts.app')
@section('title', 'Atur Nominal Pembayaran Baru')
@section('content')
<div class="row justify-content-center">
    <div class="col-12"> {{-- Full-width card --}}
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Form Pengaturan Nominal Pembayaran</h4></div>
            <div class="card-body">
                <form id="form-nominal" action="{{ route('atur-nominal.store') }}" method="POST">
                    @csrf
                    
                    {{-- Bagian 1: Identifikasi Aturan --}}
                    <h5 class="mb-3 text-primary">I. Kunci Aturan (Unik)</h5>
                    <div class="row">
                        {{-- Tahun Ajaran --}}
                        <div class="col-md-4 mb-3">
                            <label for="id_tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select name="id_tahun_ajaran" class="form-select @error('id_tahun_ajaran') is-invalid @enderror" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach($tahun_ajarans as $ta)
                                    <option value="{{ $ta->id }}" {{ old('id_tahun_ajaran') == $ta->id ? 'selected' : '' }}>
                                        {{ $ta->nama_tahun }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tahun_ajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Jenis Pembayaran --}}
                        <div class="col-md-4 mb-3">
                            <label for="id_jenis_pembayaran" class="form-label">Jenis Pembayaran <span class="text-danger">*</span></label>
                            <select name="id_jenis_pembayaran" id="id_jenis_pembayaran" class="form-select @error('id_jenis_pembayaran') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Pembayaran --</option>
                                @foreach($jenis_pembayarans as $jenis)
                                    <option value="{{ $jenis->id }}" {{ old('id_jenis_pembayaran') == $jenis->id ? 'selected' : '' }}>
                                        {{ $jenis->nama_jenis }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_jenis_pembayaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Bulan Berlaku --}}
                        <div class="col-md-4 mb-3">
                            <label for="bulan_berlaku" class="form-label">Bulan Berlaku (Opsional)</label>
                            <select name="bulan_berlaku" id="bulan_berlaku" class="form-select @error('bulan_berlaku') is-invalid @enderror">
                                <option value="">Pilih Bulan</option>
                                @foreach(range(1, 12) as $bulan)
                                    <option value="{{ $bulan }}" {{ old('bulan_berlaku') == $bulan ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $bulan)->locale('id')->isoFormat('MMMM') }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Biarkan kosong jika berlaku untuk semua bulan di Tahun Ajaran ini.</small>
                            @error('bulan_berlaku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    {{-- Bagian 2: Target Siswa --}}
                    <h5 class="mb-3 text-primary">II. Target Jenjang dan Tingkat</h5>
                    <div class="row">
                        {{-- Jenjang --}}
                        <div class="col-md-6 mb-3">
                            <label for="id_jenjang" class="form-label">Jenjang <span class="text-danger">*</span></label>
                            <select name="id_jenjang" class="form-select @error('id_jenjang') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenjang --</option>
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
                        <div class="col-md-6 mb-3" id="tingkat-group"> 
                            <label for="tingkat" class="form-label">Tingkat (Opsional)</label>
                            <input type="number" name="tingkat" id="tingkat" class="form-control @error('tingkat') is-invalid @enderror" value="{{ old('tingkat') }}" min="1" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            <small class="form-text text-muted">Biarkan kosong jika aturan berlaku untuk semua tingkat di jenjang yang dipilih.</small>
                            @error('tingkat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    {{-- Bagian 3: Nominal Harga --}}
                    <h5 class="mb-3 text-primary">III. Nominal Harga (IDR)</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nominal_normal" class="form-label">Nominal Harga Normal <span class="text-danger">*</span></label>
                            <input type="number" name="nominal_normal" id="nominal_normal" class="form-control @error('nominal_normal') is-invalid @enderror" value="{{ old('nominal_normal') }}" required min="0" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            @error('nominal_normal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nominal_keluarga" class="form-label">Nominal Diskon Keluarga (Opsional)</label>
                            <input type="number" name="nominal_keluarga" id="nominal_keluarga" class="form-control @error('nominal_keluarga') is-invalid @enderror" value="{{ old('nominal_keluarga') }}" min="0" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            <small class="form-text text-muted">Diisi jika ada diskon untuk siswa bersaudara.</small>
                            @error('nominal_keluarga')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> Simpan Aturan</button>
                    <a href="{{ route('atur-nominal.index') }}" class="btn btn-secondary mt-3">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const nominalNormal = document.getElementById('nominal_normal');
const nominalKeluarga = document.getElementById('nominal_keluarga');

// Tambahkan elemen peringatan
const warningEl = document.createElement('small');
warningEl.style.color = 'red';
warningEl.id = 'warning-diskon';
nominalKeluarga.parentNode.appendChild(warningEl);

function validateDiskon() {
    let normal = parseFloat(nominalNormal.value) || 0;
    let diskon = parseFloat(nominalKeluarga.value) || 0;

    // Hanya angka
    nominalKeluarga.value = nominalKeluarga.value.replace(/[^0-9]/g, '');

    if(diskon > normal){
        warningEl.textContent = "⚠ Nominal diskon tidak boleh lebih besar dari harga normal!";
        return false;
    } else {
        warningEl.textContent = "";
        return true;
    }
}

// Realtime validasi saat input
nominalKeluarga.addEventListener('input', validateDiskon);

// Cegah input baru jika sudah melebihi nominal_normal
nominalKeluarga.addEventListener('keydown', function(e) {
    let normal = parseFloat(nominalNormal.value) || 0;
    let diskon = parseFloat(nominalKeluarga.value) || 0;

    // Izinkan tombol backspace, delete, arah, tab
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
    if(allowedKeys.includes(e.key)) return;

    // Jika sudah mencapai atau melebihi normal, hentikan input baru
    if(diskon >= normal) {
        e.preventDefault(); // Stop penambahan angka
    }
});

// Validasi saat submit
document.querySelector('form').addEventListener('submit', function(e){
    if(!validateDiskon()){
        e.preventDefault();
        nominalKeluarga.focus();
    }
});
</script>
@endsection
