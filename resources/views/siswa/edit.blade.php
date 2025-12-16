@extends('layouts.app')
@section('title', 'Edit Data Siswa')
@section('content')

@php
    // Safety check jika $siswa tidak ada
    if (!isset($siswa) || $siswa == null) {
        $siswa = (object)[
            'id' => '',
            'id_kelas' => '',
            'nisn' => '',
            'nama_siswa' => '',
            'jenis_kelamin' => '',
            'agama' => '',
            'tempat_lahir' => '',
            'tanggal_lahir' => null,
            'nama_wali_murid' => '',
            'telp_wali_murid' => '',
            'is_keluarga' => 0,
        ];
    }

    // Format tanggal untuk input type="date"
    $tanggal_lahir_formatted = $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('Y-m-d') : '';
@endphp

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Form Edit Data Siswa: {{ $siswa->nama_siswa }}</h4></div>
            <div class="card-body">

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('siswa.update', $siswa->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h5>Data Sekolah</h5><hr>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="id_kelas">Kelas</label>
                            <select name="id_kelas" class="form-control @error('id_kelas') is-invalid @enderror" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ old('id_kelas', $siswa->id_kelas) == $k->id ? 'selected' : '' }}>
                                        {{ $k->jenjang->nama_jenjang ?? '' }} - {{ $k->tingkat }} {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kelas') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="nisn">NISN</label>
                            <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror"
                                   value="{{ old('nisn', $siswa->nisn) }}" required
                                   inputmode="numeric" pattern="\d{10}" maxlength="10"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            @error('nisn') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4 form-group form-check mt-5">
                            <input type="hidden" name="is_keluarga" value="0">
                            <input type="checkbox" name="is_keluarga" id="is_keluarga" class="form-check-input" value="1"
                                   {{ old('is_keluarga', $siswa->is_keluarga) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label">Siswa mendapat Diskon Keluarga</label>
                        </div>
                    </div>

                    <h5>Data Siswa</h5><hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nama Lengkap Siswa</label>
                            <input type="text" name="nama_siswa" class="form-control @error('nama_siswa') is-invalid @enderror"
                                   value="{{ old('nama_siswa', $siswa->nama_siswa) }}" required>
                            @error('nama_siswa') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Agama</label>
                            <select name="agama" class="form-control @error('agama') is-invalid @enderror" required>
                                <option value="">-- Pilih Agama --</option>
                                @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                                    <option value="{{ $agama }}" {{ old('agama', $siswa->agama) == $agama ? 'selected' : '' }}>
                                        {{ $agama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('agama') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
                                   value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}" required>
                            @error('tempat_lahir') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                   value="{{ old('tanggal_lahir', $tanggal_lahir_formatted) }}" required>
                            @error('tanggal_lahir') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <h5>Data Wali Murid</h5><hr>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nama Wali Murid</label>
                            <input type="text" name="nama_wali_murid"
                                   class="form-control @error('nama_wali_murid') is-invalid @enderror"
                                   value="{{ old('nama_wali_murid', $siswa->nama_wali_murid) }}" required>
                            @error('nama_wali_murid') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label>No Telepon Wali</label>
                            <input type="text" name="telp_wali_murid"
                                   class="form-control @error('telp_wali_murid') is-invalid @enderror"
                                   value="{{ old('telp_wali_murid', $siswa->telp_wali_murid) }}" required
                                   inputmode="numeric" pattern="\d{10,13}" minlength="10" maxlength="13"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            @error('telp_wali_murid') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <button class="btn btn-primary mt-3" type="submit">Update Data Siswa</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Batal</a>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection
