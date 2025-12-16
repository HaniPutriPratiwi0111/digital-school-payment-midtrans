@extends('layouts.app')
@section('title', 'Tambah Data Siswa')
@section('content')

@php
    if (!isset($calon) || $calon == null) {
        $calon = (object)[
            'id' => '',
            'nama_siswa' => '',
            'nisn' => '',
            'jenis_kelamin' => '',
            'agama' => '',
            'tempat_lahir' => '',
            'tanggal_lahir' => null,
            'nama_wali_murid' => '',
            'telp_wali_murid' => '',
            'is_keluarga' => 0,
        ];
    }

    $tahunAjaranAktif = isset($tahunAjarans) ? $tahunAjarans->firstWhere('is_aktif', 1) : null;
@endphp

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Form Tambah Siswa</h4></div>
            <div class="card-body">

                @include('layouts.alerts') {{-- Bisa pakai partial alerts --}}

                <form action="{{ $calon->id ? route('siswa.store_from_calon') : route('siswa.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="calon_id" value="{{ $calon->id }}">
                    <input type="hidden" name="id_tahun_ajaran" value="{{ $tahunAjaranAktif->id ?? '' }}">

                    <h5>Data Sekolah</h5><hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Jenjang</label>
                            <select name="id_jenjang" id="id_jenjang" class="form-control" required>
                                <option value="">-- Pilih Jenjang --</option>
                                @foreach($jenjangs as $jenjang)
                                    <option value="{{ $jenjang->id }}"
                                        {{ old('id_jenjang', $calon->id_jenjang ?? '') == $jenjang->id ? 'selected' : '' }}>
                                        {{ $jenjang->nama_jenjang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group form-check mt-4">
                            <input type="hidden" name="is_keluarga" value="0">
                            <input type="checkbox" name="is_keluarga" id="is_keluarga" class="form-check-input" value="1"
                                {{ old('is_keluarga', $calon->is_keluarga) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label">Siswa mendapat Diskon Keluarga</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Kelas</label>
                            <select name="id_kelas" id="id_kelas" class="form-control" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasAll as $k)
                                    <option value="{{ $k->id }}" data-jenjang="{{ $k->id_jenjang }}"
                                        {{ old('id_kelas') == $k->id ? 'selected' : '' }}>
                                        {{ $k->jenjang->nama_jenjang ?? '' }} - {{ $k->tingkat }} {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>NISN</label>
                            <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" 
                                value="{{ old('nisn', $calon->nisn ?? '') }}" 
                                required inputmode="numeric" pattern="\d{10}" maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            @error('nisn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h5>Data Siswa</h5><hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_siswa" class="form-control" value="{{ old('nama_siswa', $calon->nama_siswa) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $calon->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $calon->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $calon->tempat_lahir) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                value="{{ old('tanggal_lahir', $calon->tanggal_lahir ? \Carbon\Carbon::parse($calon->tanggal_lahir)->format('Y-m-d') : '') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Agama</label>
                            <select name="agama" class="form-control" required>
                                <option value="">-- Pilih Agama --</option>
                                @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                                    <option value="{{ $agama }}" {{ old('agama', $calon->agama) == $agama ? 'selected' : '' }}>
                                        {{ $agama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h5>Data Wali Murid</h5><hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nama Wali Murid</label>
                            <input type="text" name="nama_wali_murid" class="form-control" value="{{ old('nama_wali_murid', $calon->nama_wali_murid) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>No Telepon Wali</label>
                            <input type="text" name="telp_wali_murid" class="form-control @error('telp_wali_murid') is-invalid @enderror" 
                                value="{{ old('telp_wali_murid', $calon->telp_wali_murid) }}" 
                                required inputmode="numeric" pattern="\d{10,13}" minlength="10" maxlength="13"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            @error('telp_wali_murid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Simpan Data Siswa</button>
                    <a href="{{ $calon->id ? route('pendaftar.index') : route('siswa.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const jenjangSelect = document.getElementById('id_jenjang');
    const kelasSelect = document.getElementById('id_kelas');

    // Ambil placeholder asli
    const placeholder = kelasSelect.options[0].cloneNode(true);

    // Simpan semua kelas (tanpa placeholder)
    const kelasOptions = Array.from(kelasSelect.options).slice(1);

    function filterKelas() {
        const selectedJenjang = jenjangSelect.value;

        // Reset dropdown (pakai placeholder asli)
        kelasSelect.innerHTML = '';
        kelasSelect.appendChild(placeholder.cloneNode(true));

        if (!selectedJenjang) return;

        kelasOptions.forEach(opt => {
            if (opt.dataset.jenjang === selectedJenjang) {
                kelasSelect.appendChild(opt.cloneNode(true));
            }
        });
    }

    // Event saat jenjang berubah
    jenjangSelect.addEventListener('change', filterKelas);

    // Untuk reload / validation error
    if (jenjangSelect.value) {
        filterKelas();
    }
});
</script>

@endsection
