<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str; 

class RoleController extends Controller
{
    public function __construct()
    {
        // Gunakan permission yang lebih spesifik jika sudah dibuat, misal: 'role.index'
        $this->middleware('permission:role.index'); 
    }

// ----------------------------------------------------------------------------------
// HELPER FUNCTION: Mengelompokkan Permissions
// ----------------------------------------------------------------------------------

    /**
     * Mengambil dan mengelompokkan semua permissions untuk form
     * berdasarkan resource (jenjang, kelas, user, dll.).
     * @return array
     */
    private function getGroupedPermissions()
    {
        // Ambil semua permissions
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            // Asumsi format permission name: resource.action (misal: user.create, jenjang.index)
            $parts = explode('.', $permission->name);
            
            // Resource: 'user', 'jenjang', etc.
            $resource = $parts[0] ?? 'Lain-lain'; 
            
            // Ubah nama resource untuk tampilan yang lebih baik
            $groupName = match ($resource) {
                'role' => 'Role & Permissions',
                'dashboard' => 'Dashboard',
                'user' => 'Manajemen User',
                'guru' => 'Guru',
                'jenjang' => 'Jenjang',
                'tahun-ajaran' => 'Tahun Ajaran',
                'kelas' => 'Kelas',
                'siswa' => 'Siswa',
                'jenis-pembayaran' => 'Jenis Pembayaran',
                'atur-nominal' => 'Atur Nominal',
                'tagihan' => 'Transaksi Tagihan',
                'detail-tagihan' => 'Transaksi Detail Tagihan',
                'pembayaran' => 'Transaksi Pembayaran',
                'notifikasi-log' => 'Notifikasi Log',
                default => Str::title(str_replace('-', ' ', $resource)), // Menggunakan Str::title
            };

            // Simpan permission ke dalam grup
            if (!isset($grouped[$groupName])) {
                $grouped[$groupName] = [];
            }
            $grouped[$groupName][] = $permission;
        }

        return $grouped;
    }


// ----------------------------------------------------------------------------------
// FUNGSI CRUD Role 
// ----------------------------------------------------------------------------------

    // Daftar Role
    public function index()
    {
        // Ambil data roles beserta relasi permissions, 10 per halaman
        $roles = Role::with('permissions')->orderBy('name')->paginate(10);

        return view('roles.index', compact('roles'));
    }

    // Form tambah
    public function create()
    {
        $permissionGroups = $this->getGroupedPermissions();
        return view('roles.create', compact('permissionGroups'));
    }

    // Simpan role baru dan permission yang dipilih
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        
        $role = Role::create(['name' => $request->name]);
        
        // Simpan permissions (Jika ada checkbox yang dicheck)
        $role->syncPermissions($request->permissions ?? []); 
        
        return redirect()->route('roles.index')->with('success', 'Role berhasil ditambahkan');
    }

    // Form edit & Atur Permission
    public function edit(Role $role)
    {
        // Ambil semua permissions yang sudah dikelompokkan
        $permissionGroups = $this->getGroupedPermissions();
        
        // Ambil permissions yang dimiliki oleh role ini (untuk pre-checking checkbox)
        $rolePermissions = $role->permissions->pluck('name')->toArray(); 

        // Kirim data Role, semua Permission yang dikelompokkan, dan Permission yang dimiliki Role
        return view('roles.edit', compact('role', 'permissionGroups', 'rolePermissions'));
    }

    // Update role (nama) dan sinkronisasi permission
    public function update(Request $request, Role $role)
    {
        // 1. Validasi dan Update Nama Role
        $request->validate(['name' => 'required|unique:roles,name,' . $role->id]);
        $role->update(['name' => $request->name]);
        
        // 2. Sinkronisasi Hak Akses (Permissions)
        // $request->permissions berisi array nama Permission yang dicheck
        $role->syncPermissions($request->permissions ?? []); 

        return redirect()->route('roles.index')->with('success', 'Role dan hak akses berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return back()->with('success', 'Role berhasil dihapus');
    }

}