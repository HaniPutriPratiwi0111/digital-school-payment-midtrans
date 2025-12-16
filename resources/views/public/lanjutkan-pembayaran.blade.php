<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lanjutkan Pembayaran Pendaftaran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8fafc;
        }

        .payment-card {
            max-width: 420px;
            margin: auto;
            border-radius: 16px;
        }

        .payment-title {
            font-weight: 600;
        }

        .divider-text {
            font-size: .85rem;
            color: #6b7280;
        }
    </style>
</head>
<body>

<div class="container min-vh-100 d-flex align-items-center">
    <div class="card payment-card p-4 shadow-sm w-100">

        <h5 class="payment-title text-center mb-2">
            Lanjutkan Pembayaran Pendaftaran
        </h5>
        <p class="text-center text-muted mb-4" style="font-size:.9rem">
            Masukkan email atau nomor HP yang digunakan saat pendaftaran
        </p>

        @if(session('error'))
            <div class="alert alert-danger text-center py-2">
                {{ session('error') }}
            </div>
        @endif

        {{-- OPSI UTAMA --}}
        <form method="POST" action="{{ route('pembayaran.check') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small">Email / Nomor HP</label>
                <input type="text" name="email_or_hp" class="form-control" placeholder="contoh@email.com / 08xxxx" required>
            </div>

            <button class="btn btn-primary w-100 py-2 fw-semibold">
                Lanjutkan Pembayaran
            </button>
        </form>

        {{-- DIVIDER --}}
        <div class="text-center my-4 divider-text">
            atau
        </div>

        {{-- OPSI LUPA DATA --}}
        <p class="text-center mb-2">
            <a class="text-decoration-none" data-bs-toggle="collapse" href="#lupaData">
                Lupa data pendaftaran?
            </a>
        </p>

        <div class="collapse" id="lupaData">
            <form method="POST" action="{{ route('pembayaran.check') }}">
                @csrf

                <div class="mb-2">
                    <input class="form-control" name="nama_siswa" placeholder="Nama Siswa" required>
                </div>

                <div class="mb-2">
                    <input class="form-control" name="nama_ortu" placeholder="Nama Orang Tua" required>
                </div>

                <div class="mb-3">
                    <input type="date" class="form-control" name="tanggal_lahir" required>
                </div>

                <button class="btn btn-outline-secondary w-100">
                    Cari Data Pendaftaran
                </button>
            </form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
