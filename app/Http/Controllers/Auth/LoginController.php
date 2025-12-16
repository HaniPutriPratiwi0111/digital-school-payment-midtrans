<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showAdminLogin()
    {
        return view('auth.login-admin');
    }

    public function showWaliLogin()
    {
        return view('auth.login-wali');
    }

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->hasRole('Super Administrator')) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasRole('Bendahara')) {
                return redirect()->route('bendahara.dashboard');
            }

            Auth::logout();
            return back()->withErrors(['email' => 'Anda tidak memiliki akses ke halaman admin.']);
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    public function loginWali(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        // cek apakah input email atau nisn
        $loginField = null;
        $loginValue = null;

        if (!empty($request->email)) {
            $loginField = 'email';
            $loginValue  = $request->email;
        } elseif (!empty($request->nisn)) {
            $loginField = 'nisn';
            $loginValue  = $request->nisn;
        } else {
            return back()->withErrors(['login' => 'Harap isi Email atau NISN']);
        }

        // cari user
        $user = \App\Models\User::where($loginField, $loginValue)->first();

        if (!$user) {
            return back()->withErrors([
                'login' => ucfirst($loginField) . ' tidak ditemukan'
            ]);
        }

        // cek password
        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'login' => 'Password salah'
            ]);
        }

        // cek role wali (dukung beberapa penamaan)
        if (!$user->hasAnyRole(['Orang Tua', 'Wali', 'Orangtua'])) {
            return back()->withErrors([
                'login' => 'Akun ini tidak memiliki akses sebagai Wali Murid.'
            ]);
        }

        // Lolos semua → login
        Auth::login($user);

        return redirect()->route('wali.dashboard')
            ->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.wali');
    }
}
