@extends('layouts.app')
@section('title', 'Edit Kelas')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                {{-- Alert jika ada pesan --}}
                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                @php
                    $readonly = $kela->siswas->count() > 0;
                @endphp

                <form action="{{ route('kelas.update', $kela) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Jenjang --}}
                    <div class="form-group">
                        <label for="id_jenjang">Jenjang</label>
                        <select class="form-control" @if($readonly) disabled @endif>
                            <option value="{{ $kela->id_jenjang }}" selected>{{ $kela->jenjang->nama_jenjang }}</option>
                        </select>
                        {{-- Hidden input supaya tetap terkirim --}}
                        <input type="hidden" name="id_jenjang" value="{{ $kela->id_jenjang }}">
                        @if($readonly)
                            <small class="text-muted">Jenjang tidak dapat diubah karena sudah memiliki siswa.</small>
                        @endif
                    </div>

                    {{-- Tingkat / Kelas --}}
                    <div class="form-group">
                        <label for="tingkat">Tingkat / Kelas</label>
                        <input type="number" name="tingkat" class="form-control" value="{{ old('tingkat', $kela->tingkat) }}" min="1" @if($readonly) readonly @endif>
                        @if($readonly)
                            <small class="text-muted">Tingkat tidak dapat diubah karena sudah memiliki siswa.</small>
                        @endif
                    </div>

                    {{-- Nama Kelas --}}
                    <div class="form-group">
                        <label for="nama_kelas">Nama Kelas (Contoh: A, B, C)</label>
                        <input type="text" name="nama_kelas" class="form-control" value="{{ old('nama_kelas', $kela->nama_kelas) }}" @if($readonly) readonly @endif>
                        @if($readonly)
                            <small class="text-muted">Nama kelas tidak dapat diubah karena sudah memiliki siswa.</small>
                        @endif
                    </div>

                    {{-- Wali Kelas --}}
                    <div class="form-group">
                        <label for="id_guru_wali_kelas">Wali Kelas (Guru)</label>
                        <select name="id_guru_wali_kelas" class="form-control">
                            <option value="">-- Tidak Ada Wali Kelas --</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}" 
                                    {{ old('id_guru_wali_kelas', $kela->id_guru_wali_kelas) == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->nama }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Hanya guru yang belum menjadi wali kelas di kelas lain yang bisa dipilih.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Batal</a>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
