<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran Siswa Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-lg mx-auto" style="max-width: 700px;">
            <div class="card-header bg-primary text-white text-center">
                <h1 class="h4 mb-0">Formulir Pendaftaran Siswa Baru</h1>
            </div>
            <div class="card-body p-4">
                
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                <form action="{{ route('calon_siswa.store') }}" method="POST">
                    @csrf

                    {{-- Data Akademik (Hidden) --}}
                    <input type="hidden" name="id_tahun_ajaran" value="{{ $tahun_ajaran_aktif->id }}">

                    <h5 class="mt-3 mb-3 text-primary">Informasi Calon Siswa</h5>
                    
                    {{-- Input Jenjang Pendidikan Tujuan --}}
                    <div class="mb-3">
                        <label for="id_jenjang" class="form-label">Jenjang Pendidikan Tujuan <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_jenjang') is-invalid @enderror" id="id_jenjang" name="id_jenjang" required>
                            <option value="">Pilih Jenjang</option>
                            @foreach($jenjangs as $jenjang)
                                <option value="{{ $jenjang->id }}" {{ old('id_jenjang') == $jenjang->id ? 'selected' : '' }}>
                                    {{ $jenjang->nama_jenjang }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_jenjang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Input Nama Siswa --}}
                    <div class="mb-3">
                        <label for="nama_siswa" class="form-label">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_siswa') is-invalid @enderror" id="nama_siswa" name="nama_siswa" value="{{ old('nama_siswa') }}" required>
                        @error('nama_siswa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Input Tempat Lahir --}}
                    <div class="mb-3">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}">
                        @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Input Tanggal Lahir --}}
                    <div class="mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                        @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Input Jenis Kelamin --}}
                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Input Agama --}}
                    <div class="mb-3">
                        <label for="agama" class="form-label">Agama</label>
                        <input type="text" class="form-control @error('agama') is-invalid @enderror" id="agama" name="agama" value="{{ old('agama') }}">
                        @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <h5 class="mt-4 mb-3 text-primary">Informasi Wali Murid</h5>
                    
                    {{-- Input Nama Wali Murid --}}
                    <div class="mb-3">
                        <label for="nama_wali_murid" class="form-label">Nama Wali Murid <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_wali_murid') is-invalid @enderror" id="nama_wali_murid" name="nama_wali_murid" value="{{ old('nama_wali_murid') }}" required>
                        @error('nama_wali_murid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Input Telepon Wali Murid --}}
                    <div class="mb-3">
                        <label for="telp_wali_murid" class="form-label">Nomor HP/Telepon Wali Murid <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('telp_wali_murid') is-invalid @enderror" id="telp_wali_murid" name="telp_wali_murid" value="{{ old('telp_wali_murid') }}" required>
                        @error('telp_wali_murid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Input Email Wali Murid --}}
                    <div class="mb-3">
                        <label for="email_wali" class="form-label">Email Wali Murid <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email_wali') is-invalid @enderror" id="email_wali" name="email_wali" value="{{ old('email_wali') }}" required>
                        <small class="text-muted">Email ini akan digunakan untuk notifikasi dan login.</small>
                        @error('email_wali')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <hr>

                    {{-- KRITIS: Input Status Keluarga (Diskon Harga) --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Keluarga</label>
                        <div class="form-check">
                            {{-- is_keluarga = 0 (Harga Normal) --}}
                            <input class="form-check-input" type="radio" name="is_keluarga" id="is_keluarga_no" value="0" {{ old('is_keluarga', 0) == 0 ? 'checked' : '' }} required>
                            <label class="form-check-label" for="is_keluarga_no">
                                Calon Siswa **TIDAK** memiliki saudara yang masih aktif bersekolah. (Harga Normal)
                            </label>
                        </div>
                        <div class="form-check">
                            {{-- is_keluarga = 1 (Harga Diskon) --}}
                            <input class="form-check-input" type="radio" name="is_keluarga" id="is_keluarga_yes" value="1" {{ old('is_keluarga') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_keluarga_yes">
                                Calon Siswa **MEMILIKI** saudara yang masih aktif bersekolah. (Berpotensi mendapat Diskon Biaya Pendaftaran)
                            </label>
                        </div>
                    </div>
                    
                    <hr>
                    
                    {{-- START: INPUT JENIS PEMBAYARAN BARU --}}
                    <h5 class="mt-4 mb-3 text-primary">Pilihan Pembayaran</h5>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Metode Pembayaran Awal <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input @error('metode_pembayaran') is-invalid @enderror" type="radio" name="metode_pembayaran" id="metode_online" value="online" {{ old('metode_pembayaran', 'online') == 'online' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="metode_online">
                                **Pembayaran Online (Midtrans/Payment Gateway)**. Anda akan langsung diarahkan ke halaman pembayaran (VA, Kartu, E-Wallet).
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input @error('metode_pembayaran') is-invalid @enderror" type="radio" name="metode_pembayaran" id="metode_manual" value="manual" {{ old('metode_pembayaran') == 'manual' ? 'checked' : '' }}>
                            <label class="form-check-label" for="metode_manual">
                                **Pembayaran Transfer Manual**. *Invoice* akan dibuat, dan Anda dapat transfer/bayar ke rekening sekolah tanpa Midtrans.
                            </label>
                        </div>
                        @error('metode_pembayaran')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <hr>
                    {{-- END: INPUT JENIS PEMBAYARAN BARU --}}

                    <div class="alert alert-info mt-4 text-center">
                        <h5 class="mb-0">Total Biaya Pendaftaran Awal: **Rp {{ number_format($nominalPendaftaran, 0, ',', '.') }}**</h5>
                        <p class="mb-0 text-muted">Nominal final akan dihitung berdasarkan Jenjang dan Status Keluarga saat data disubmit.</p>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Daftar & Lanjutkan Pembayaran</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>