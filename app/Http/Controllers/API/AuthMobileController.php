<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthMobileController extends Controller
{
    /**
     * LOGIN ADMIN / BENDAHARA via API
     */
// File: App\Http\Controllers\API\AuthMobileController.php

// ...

    /**
     * LOGIN ADMIN / BENDAHARA via API
     */
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $user = Auth::user();

        // *** PERUBAHAN DI SINI: Hanya izinkan role 'Bendahara' ***
        if (!$user->hasRole('Bendahara')) { 
            Auth::logout();
            return response()->json([
                'success' => false,
                // Mengubah pesan agar lebih spesifik untuk aplikasi mobile
                'message' => 'Akun ini tidak memiliki akses sebagai Bendahara mobile.' 
            ], 403);
        }
        // ********************************************************

        $token = $user->createToken('mobile_admin')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ]
        ]);
    }

    /**
     * LOGIN WALI via API (email atau NISN)
     */
    public function loginWali(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $loginField = null;
        $loginValue = null;

        if (!empty($request->email)) {
            $loginField = 'email';
            $loginValue = $request->email;
        } elseif (!empty($request->nisn)) {
            $loginField = 'nisn';
            $loginValue = $request->nisn;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Harap isi Email atau NISN'
            ], 422);
        }

        $user = User::where($loginField, $loginValue)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($loginField) . ' tidak ditemukan'
            ], 404);
        }

        if (!\Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah'
            ], 401);
        }

        if (!$user->hasAnyRole(['Orang Tua', 'Wali', 'Orangtua'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak memiliki akses sebagai Wali Murid.'
            ], 403);
        }

        Auth::login($user);
        $token = $user->createToken('mobile_wali')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Selamat datang, ' . $user->name . '!',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ]
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        Auth::logout(); // optional, untuk sinkron web dan API

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}
