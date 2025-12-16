<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Orang Tua</title>

<style>
/* ================= ROOT ================= */
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --text: #1f2937;
    --muted: #6b7280;
}

/* ================= RESET ================= */
* {
    box-sizing: border-box;
}

/* ================= BODY ================= */
body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Inter', system-ui, sans-serif;
    background: url('{{ asset('assets/images/baitulilmischool.png') }}') center / cover no-repeat;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

/* OVERLAY (FIX: TIDAK BLOCK KLIK) */
body::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 0;
    pointer-events: none; /* ⬅ WAJIB */
}

/* ================= LOGIN CARD ================= */
.login-container {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 460px;
    background: rgba(255,255,255,.96);
    padding: 28px 36px 28px;
    border-radius: 18px;
    box-shadow: 0 25px 60px rgba(0,0,0,.35);
    animation: fadeUp .4s ease;
}

/* ANIMATION */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ================= TEXT ================= */
h3 {
    font-size: 1.7rem;
    color: var(--text);
    margin: 0 0 6px;
    margin-bottom: 4px;
    text-align: center;
}

.subtitle {
    font-size: .9rem;
    color: var(--muted);
    margin-bottom: 20px;
    text-align: center;
}

/* ================= FORM ================= */
label {
    display: block;
    font-size: .8rem;
    font-weight: 600;
    margin-bottom: 4px;
    color: var(--text);
}

input {
    width: 100%;
    padding: 11px 14px;
    margin-bottom: 14px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    font-size: .9rem;
    transition: .25s;
}

input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,.18);
}

/* ================= TOGGLE LOGIN ================= */
.login-option {
    display: block;
    text-align: right;
    font-size: .8rem;
    color: var(--primary);
    cursor: pointer;
    margin-top: -6px;
    margin-bottom: 14px;
}

.login-option:hover {
    text-decoration: underline;
}

/* ================= BUTTON ================= */
button {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: none;
    background: var(--primary);
    color: #fff;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    transition: .3s;
}

button:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

/* ================= ALERT ================= */
.alert-payment {
    background: #fff7ed;
    border-left: 4px solid #f97316;
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: .85rem;
}

/* ================= FOOTER ================= */
.login-footer {
    margin-top: 18px;
    font-size: .85rem;
    text-align: center;
}

.login-footer p {
    margin: 6px 0;
}

.login-footer a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
}

/* ================= PASSWORD TOGGLE ================= */
.password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 42px; /* ruang buat icon */
}

.toggle-password {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    cursor: pointer;
    color: #9ca3af;
    transition: .2s;
}

.toggle-password:hover {
    color: var(--primary);
}

/* ================= MOBILE ================= */
@media (max-width: 480px) {
    .login-container {
        margin: 20px;
        padding: 28px;
    }
}
</style>
</head>

<body>

<div class="login-container">
    <h3>Login Orang Tua</h3>
    <p class="subtitle">Silakan masuk menggunakan NISN atau Email.</p>

    @if ($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:10px;margin-bottom:20px;">
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('payment_pending'))
        <div class="alert-payment">
            <b>Status Pembayaran: MENUNGGU</b><br>
            Biaya: Rp {{ number_format(session('amount'),0,',','.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.wali.post') }}">
        @csrf

        <div id="nisn-group">
            <label>NISN Siswa</label>
            <input type="text" name="nisn" placeholder="Masukkan NISN siswa">
        </div>

        <div id="email-group" style="display:none;">
            <label>Email</label>
            <input type="email" name="email" placeholder="Masukkan email">
        </div>

        <label>Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Masukkan password" required>

            <span class="toggle-password" id="togglePassword">
                <!-- EYE -->
                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 576 512" fill="currentColor">
                    <path d="M572.52 241.4C518.29 135.5 407.06 64 288 64S57.72 135.5 3.48 241.4a48.07 48.07 0 000 29.2C57.72 376.5 168.94 448 288 448s230.29-71.5 284.52-177.4a48.07 48.07 0 000-29.2zM288 400c-79.4 0-144-64.6-144-144s64.6-144 144-144 144 64.6 144 144-64.6 144-144 144zm0-240a96 96 0 1096 96 96.11 96.11 0 00-96-96z"/>
                </svg>

                <!-- EYE SLASH -->
                <svg id="eyeClose" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                    viewBox="0 0 640 512" fill="currentColor" style="display:none">
                    <path d="M633.8 458.1L36.6 5.8A16 16 0 0013.8 9.3L3.3 23.4a16 16 0 003.5 22.8l592.6 452.3a16 16 0 0022.8-3.5l10.5-14.1a16 16 0 00-3.5-22.8zM320 400c-40.1 0-76.7-16.5-103.3-43.1l-47.1 36.1C207.5 417.9 262.1 448 320 448c119.1 0 230.3-71.5 284.5-177.4a48.35 48.35 0 000-29.2 350.4 350.4 0 00-64.5-88.6l-51.7 39.6A246.4 246.4 0 01504 256c0 79.5-64.5 144-144 144zm-38.6-280.9A143.1 143.1 0 01320 112c79.4 0 144 64.6 144 144a142.6 142.6 0 01-7.2 45l64.6 49.5a246.5 246.5 0 0034.8-65.3 48.35 48.35 0 000-29.2C550.3 135.5 439.1 64 320 64a308.2 308.2 0 00-97.4 15.7z"/>
                </svg>
            </span>
        </div>

        <a class="login-option" id="toggle-login">Login menggunakan Email?</a>

        <button type="submit">Masuk</button>
    </form>

    <div class="login-footer">
        <p><a href="{{ route('login.admin') }}">Login Admin / Bendahara</a></p>
        <p>
            Belum punya akun?
            <a href="{{ route('pendaftaran.form') }}">Daftar Siswa Baru</a>
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ===== TOGGLE LOGIN (NISN / EMAIL) ===== */
    const nisnGroup  = document.getElementById('nisn-group');
    const emailGroup = document.getElementById('email-group');
    const toggleLink = document.getElementById('toggle-login');

    let isNisn = true;

    toggleLink.addEventListener('click', function (e) {
        e.preventDefault();

        nisnGroup.style.display  = isNisn ? 'none'  : 'block';
        emailGroup.style.display = isNisn ? 'block' : 'none';
        toggleLink.textContent   = isNisn
            ? 'Login menggunakan NISN?'
            : 'Login menggunakan Email?';

        isNisn = !isNisn;
    });

    /* ===== SHOW / HIDE PASSWORD (SVG ICON) ===== */
    const passwordInput = document.getElementById('password');
    const toggle        = document.getElementById('togglePassword');
    const eyeOpen       = document.getElementById('eyeOpen');
    const eyeClose      = document.getElementById('eyeClose');

    if (passwordInput && toggle && eyeOpen && eyeClose) {
        toggle.addEventListener('click', function () {
            const show = passwordInput.type === 'password';

            passwordInput.type = show ? 'text' : 'password';
            eyeOpen.style.display  = show ? 'none'  : 'block';
            eyeClose.style.display = show ? 'block' : 'none';
        });
    }

});
</script>

</body>
</html>
