<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Formulir Pendaftaran Calon Siswa</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container my-5" style="max-width: 900px;">

    <h2 class="fw-bold mb-4 text-center">
        Pendaftaran Calon Siswa Baru
    </h2>

    {{-- INFO SUDAH PERNAH DAFTAR --}}
    <div class="alert alert-info">
        <strong>Sudah pernah mendaftar?</strong>
        <p class="mb-2">
            Jika Anda sudah mengisi formulir pendaftaran tetapi belum menyelesaikan pembayaran,
            silakan lanjutkan melalui tombol berikut.
        </p>
        <a href="{{ route('pembayaran.lanjut') }}" class="btn btn-outline-primary btn-sm">
            Lanjutkan Pembayaran
        </a>
    </div>

    {{-- ERROR SESSION --}}
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- MASTER DATA ERROR --}}
    @if(isset($error_message) && $error_message)
        <div class="card shadow-sm p-4 text-center">
            <h4 class="text-danger">Pendaftaran Belum Dibuka</h4>
            <p class="lead mb-1">{{ $error_message }}</p>
            <small>Silakan hubungi Administrator sekolah.</small>
        </div>

    @else

    {{-- FORM --}}
    <form action="{{ route('pendaftaran.store') }}" method="POST">
        @csrf

        <input type="hidden" name="id_jenis_pembayaran_pendaftaran" value="{{ $jenis_pendaftaran->id ?? '' }}">
        <input type="hidden" name="id_tahun_ajaran" value="{{ $tahun_ajaran_aktif->id ?? '' }}">

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                {{-- IDENTITAS SISWA --}}
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    Identitas Calon Siswa
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_siswa"
                               class="form-control @error('nama_siswa') is-invalid @enderror"
                               value="{{ old('nama_siswa', $calon->nama_siswa ?? '') }}" required>
                        @error('nama_siswa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenjang Tujuan </label>
                        <select class="form-select" name="id_jenjang" required>
                            <option value="">-- Pilih Jenjang --</option>
                            @foreach ($jenjangs as $jenjang)
                                <option value="{{ $jenjang->id }}"
                                    {{ old('id_jenjang') == $jenjang->id ? 'selected' : '' }}>
                                    {{ $jenjang->nama_jenjang }}
                                </option>
                            @endforeach
                        </select>
                        <small>Silahkan pilih jenjang sekolah tujuan anda mendaftar</small>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir"
                               class="form-control @error('tempat_lahir') is-invalid @enderror"
                               value="{{ old('tempat_lahir', $calon->tempat_lahir ?? '') }}">
                        @error('tempat_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir"
                               class="form-control @error('tanggal_lahir') is-invalid @enderror"
                               value="{{ old('tanggal_lahir', $calon->tanggal_lahir ?? '') }}">
                        @error('tanggal_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin')=='Laki-laki'?'selected':'' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin')=='Perempuan'?'selected':'' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Agama</label>
                        <select name="agama" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            @foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $agama)
                                <option value="{{ $agama }}" {{ old('agama')==$agama?'selected':'' }}>
                                    {{ $agama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- DATA WALI --}}
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    Data Wali Murid & Akun Login
                </h5>

                <div class="mb-3">
                    <label class="form-label">Nama Wali Murid</label>
                    <input type="text" name="nama_wali_murid" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Wali Murid</label>
                    <input type="email" name="email" class="form-control" required>
                    <div class="form-text">
                        Email ini akan digunakan sebagai <strong>username login</strong>.
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Nomor Telepon Wali Murid</label>
                    <input type="tel" name="telp_wali_murid" class="form-control" required>
                </div>

                {{-- SAUDARA --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Memiliki saudara kandung aktif di sekolah?
                    </label>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_keluarga" value="1" required>
                        <label class="form-check-label">Ya, Ada</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_keluarga" value="0" required>
                        <label class="form-check-label">Tidak Ada</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    Daftar & Lanjutkan Pembayaran
                </button>

            </div>
        </div>
    </form>
    @endif

</div>

{{-- MIDTRANS --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}">
</script>

@if(isset($snapToken))
<script>
window.snap.pay('{{ $snapToken }}', {
    onSuccess: () => window.location.href = "/pendaftaran/sukses/{{ $orderId }}",
    onPending: () => window.location.href = "/pendaftaran/sukses/{{ $orderId }}",
    onError: () => window.location.href = "/pendaftaran/sukses/{{ $orderId }}",
    onClose: () => window.location.href = "/pendaftaran/sukses/{{ $orderId }}"
});
</script>
@endif

</body>
</html>
