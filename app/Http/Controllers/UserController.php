<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // ============================
    // ADMIN / MANAGE USER
    // ============================
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filter berdasarkan nama
        if ($request->filled('search_name')) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }

        // Filter berdasarkan role
        if ($request->filled('search_role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->search_role);
            });
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all(); // untuk dropdown filter di view

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'roles' => 'required|string|exists:roles,name',
        ], [
            // ===============================================
            // PESAN CUSTOM LENGKAP UNTUK MENIMPA SEMUA ATURAN
            // (Array Pesan Kustom)
            // ===============================================
            
            // Pesan untuk field 'name'
            'name.required'       => 'Kolom Nama Lengkap wajib diisi.',
            'name.string'         => 'Kolom Nama Lengkap harus berupa teks.',

            // Pesan untuk field 'email'
            'email.required'      => 'Kolom Email wajib diisi.',
            'email.email'         => 'Format Email tidak valid.',
            'email.unique'        => 'Email sudah terdaftar.',

            // Pesan untuk field 'password'
            'password.required'   => 'Kata Sandi wajib diisi.',
            'password.confirmed'  => 'Konfirmasi Kata Sandi tidak cocok.',
            
            // Pesan untuk aturan Password::class (MENIMPA)
            'password.mixed'      => 'Kata Sandi harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
            'password.symbols'    => 'Kata Sandi harus mengandung setidaknya satu simbol.',
            'password.numbers'    => 'Kata Sandi harus mengandung setidaknya satu angka.',
            'password.min'        => 'Kata Sandi minimal harus 8 karakter.',
            
            // Pesan untuk field 'roles'
            'roles.required'      => 'Role wajib dipilih.',
            'roles.exists'        => 'Role yang dipilih tidak valid.',
        ], [
            // ===============================================
            // Array Atribut (WAJIB DITAMBAHKAN)
            // ===============================================
            'attributes' => [
                'password' => 'Kata Sandi',
                'name' => 'Nama Lengkap',
                'email' => 'Email',
                'roles' => 'Role',
            ],
        ]);
        
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->roles);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Menggunakan pesan validasi yang sama
        $validationMessages = [
            'name.required' => 'Kolom Nama Lengkap wajib diisi.',
            'email.required' => 'Kolom Email wajib diisi.',
            'email.email' => 'Format Email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi Kata Sandi tidak cocok.',
            'password.min' => 'Kata Sandi minimal harus 8 karakter.', // Diperbaiki dari 6 ke 8
            'roles.array' => 'Role harus berbentuk array.',
            'roles.required' => 'Role wajib dipilih.', // Ditambahkan required untuk roles
        ];
        
        // Aturan Password di Update: nullable, dan hanya enforce aturan kuat jika diisi
        $passwordRules = [
            'nullable',
            'confirmed',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols(),
        ];

        // Jika password diisi, kita perlu aturan yang lebih ketat
        if ($request->filled('password')) {
            $passwordRules = [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ];
        } else {
            // Jika password kosong, kita hanya butuh nullable dan confirmed
            $passwordRules = ['nullable', 'confirmed'];
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email','max:255', Rule::unique('users')->ignore($user->id)],
            'password' => $passwordRules,
            'roles' => 'required|string|exists:roles,name', // Diubah dari nullable|array ke required|string
        ], $validationMessages); // Menggunakan pesan yang telah didefinisikan di atas

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Menggunakan syncRoles hanya butuh string atau array (tergantung implementasi Anda)
        $user->syncRoles([$request->roles]); 

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    // ============================
    // PROFILE SETTINGS (LOGIN USER)
    // ============================
    public function editProfile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // **PENTING: Pastikan form di view (profile.edit) memiliki enctype="multipart/form-data"**
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email','max:255', Rule::unique('users')->ignore($user->id)],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Upload avatar jika ada
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            // Path harus konsisten dengan cara penyimpanan, yaitu di 'public/avatars'
            if ($user->avatar && Storage::disk('public')->exists('avatars/'.$user->avatar)) {
                Storage::disk('public')->delete('avatars/'.$user->avatar);
            }
            
            $file = $request->file('avatar');
            $filename = time().'_'.$file->getClientOriginalName();
            
            // Simpan file ke storage/app/public/avatars
            $file->storeAs('avatars', $filename, 'public'); 
            
            $user->avatar = $filename;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Password berhasil diperbarui!');
    }

    // ============================
    // HEADER BACKGROUND (LOGIN USER)
    // ============================
    public function updateHeader(Request $request)
    {
        $request->validate([
            'header_bg' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        $user = Auth::user();

        if ($request->hasFile('header_bg')) {
            $file = $request->file('header_bg');
            $filename = time().'_'.$file->getClientOriginalName();

            // Hapus header lama jika ada
            if ($user->header_bg && Storage::disk('public')->exists('header-bg/'.$user->header_bg)) {
                Storage::disk('public')->delete('header-bg/'.$user->header_bg);
            }

            // Simpan ke storage/app/public/header-bg
            $file->storeAs('header-bg', $filename, 'public');

            $user->header_bg = $filename;
            $user->save();
        }

        return back()->with('success', 'Header berhasil diupdate!');
    }

}