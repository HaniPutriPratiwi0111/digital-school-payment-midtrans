<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            
            // ====================================
            // DASHBOARD
            // ====================================
            ['name' => 'dashboard.index',   'group' => 'Dashboard', 'description' => 'Melihat halaman utama dashboard'],

            // ====================================
            // MASTER DATA
            // ====================================
            // Master Jenjang (master-jenjang)
            ['name' => 'master-jenjang.index',    'group' => 'Master Jenjang', 'description' => 'Melihat daftar jenjang'],
            ['name' => 'master-jenjang.create',   'group' => 'Master Jenjang', 'description' => 'Menambah jenjang baru'],
            ['name' => 'master-jenjang.edit',     'group' => 'Master Jenjang', 'description' => 'Mengubah data jenjang'],
            ['name' => 'master-jenjang.destroy',  'group' => 'Master Jenjang', 'description' => 'Menghapus data jenjang'],

            // Tahun Ajaran
            ['name' => 'tahun-ajaran.index',   'group' => 'Tahun Ajaran', 'description' => 'Melihat daftar tahun ajaran'],
            ['name' => 'tahun-ajaran.create',  'group' => 'Tahun Ajaran', 'description' => 'Menambah tahun ajaran baru'],
            ['name' => 'tahun-ajaran.edit',    'group' => 'Tahun Ajaran', 'description' => 'Mengubah data tahun ajaran'],
            ['name' => 'tahun-ajaran.destroy', 'group' => 'Tahun Ajaran', 'description' => 'Menghapus tahun ajaran'],

            // Kelas
            ['name' => 'kelas.index',   'group' => 'Kelas', 'description' => 'Melihat daftar kelas'],
            ['name' => 'kelas.create',  'group' => 'Kelas', 'description' => 'Menambah kelas baru'],
            ['name' => 'kelas.edit',    'group' => 'Kelas', 'description' => 'Mengubah data kelas'],
            ['name' => 'kelas.destroy', 'group' => 'Kelas', 'description' => 'Menghapus data kelas'],

            // Guru
            ['name' => 'guru.index',   'group' => 'Guru', 'description' => 'Melihat daftar guru'],
            ['name' => 'guru.create',  'group' => 'Guru', 'description' => 'Menambah guru baru'],
            ['name' => 'guru.edit',    'group' => 'Guru', 'description' => 'Mengubah data guru'],
            ['name' => 'guru.destroy', 'group' => 'Guru', 'description' => 'Menghapus data guru'],

            // Siswa
            ['name' => 'siswa.index',   'group' => 'Siswa', 'description' => 'Melihat daftar siswa'],
            ['name' => 'siswa.create',  'group' => 'Siswa', 'description' => 'Menambah siswa baru'],
            ['name' => 'siswa.edit',    'group' => 'Siswa', 'description' => 'Mengubah data siswa'],
            ['name' => 'siswa.destroy', 'group' => 'Siswa', 'description' => 'Menghapus data siswa'],

            // ====================================
            // KEUANGAN
            // ====================================
            // Jenis Pembayaran
            ['name' => 'jenis-pembayaran.index',   'group' => 'Jenis Pembayaran', 'description' => 'Melihat daftar jenis pembayaran'],
            ['name' => 'jenis-pembayaran.create',  'group' => 'Jenis Pembayaran', 'description' => 'Menambah jenis pembayaran baru'],
            ['name' => 'jenis-pembayaran.edit',    'group' => 'Jenis Pembayaran', 'description' => 'Mengubah data jenis pembayaran'],
            ['name' => 'jenis-pembayaran.destroy', 'group' => 'Jenis Pembayaran', 'description' => 'Menghapus jenis pembayaran'],

            // Atur Nominal
            ['name' => 'atur-nominal.index',   'group' => 'Atur Nominal', 'description' => 'Melihat daftar nominal biaya'],
            ['name' => 'atur-nominal.create',  'group' => 'Atur Nominal', 'description' => 'Menambah nominal baru'],
            ['name' => 'atur-nominal.edit',    'group' => 'Atur Nominal', 'description' => 'Mengubah nominal biaya'],
            ['name' => 'atur-nominal.destroy', 'group' => 'Atur Nominal', 'description' => 'Menghapus nominal biaya'],

            // Tagihan (TAMBAHKAN tagihan.show dan tagihan.anak)
            ['name' => 'tagihan.index',   'group' => 'Tagihan', 'description' => 'Melihat daftar tagihan'],
            ['name' => 'tagihan.show',    'group' => 'Tagihan', 'description' => 'Melihat detail tagihan'],  // <-- TAMBAHKAN INI
            ['name' => 'tagihan.show-wali', 'group' => 'Tagihan', 'description' => 'Melihat detail tagihan anak (khusus wali)'], // ✅ Tambah ini
            ['name' => 'tagihan.anak',    'group' => 'Tagihan', 'description' => 'Melihat tagihan anak/siswa sendiri'],  // <-- TAMBAHKAN INI (khusus untuk Orang Tua/Siswa)
            ['name' => 'tagihan.create',  'group' => 'Tagihan', 'description' => 'Menambah tagihan baru'],
            ['name' => 'tagihan.edit',    'group' => 'Tagihan', 'description' => 'Mengubah data tagihan'],
            ['name' => 'tagihan.destroy', 'group' => 'Tagihan', 'description' => 'Menghapus data tagihan'],

            // Detail Tagihan (Semua aksi CRUD penuh)
            ['name' => 'detail-tagihan.index',   'group' => 'Detail Tagihan', 'description' => 'Melihat detail tagihan'],
            ['name' => 'detail-tagihan.create',  'group' => 'Detail Tagihan', 'description' => 'Menambah detail tagihan'],
            ['name' => 'detail-tagihan.edit',    'group' => 'Detail Tagihan', 'description' => 'Mengubah detail tagihan'],
            ['name' => 'detail-tagihan.destroy', 'group' => 'Detail Tagihan', 'description' => 'Menghapus detail tagihan'],
            
            // Pembayaran (tunai dan midtrans)
            ['name' => 'pembayaran.index',   'group' => 'Pembayaran', 'description' => 'Melihat daftar pembayaran'],
            ['name' => 'pembayaran.create',  'group' => 'Pembayaran', 'description' => 'Menambah data pembayaran tunai'],
            ['name' => 'pembayaran.show',    'group' => 'Pembayaran', 'description' => 'Melihat detail pembayaran'],
            ['name' => 'pembayaran.midtrans', 'group' => 'Pembayaran', 'description' => 'Melakukan pembayaran online via Midtrans'],
            ['name' => 'pembayaran.midtrans.callback', 'group' => 'Pembayaran', 'description' => 'Menerima notifikasi pembayaran Midtrans'],

            // 🟩 Tambahkan ini (biar match sama route `midtrans.payPage`)
            ['name' => 'midtrans.payPage', 'group' => 'Pembayaran', 'description' => 'Menampilkan halaman pembayaran midtrans'],
            ['name' => 'midtrans.pay', 'group' => 'Pembayaran', 'description' => 'Memproses transaksi Midtrans'],

            // ====================================
            // SISTEM & PENGATURAN
            // ====================================
            // User
            ['name' => 'user.index',   'group' => 'User', 'description' => 'Melihat daftar user'],
            ['name' => 'user.create',  'group' => 'User', 'description' => 'Menambah user baru'],
            ['name' => 'user.edit',    'group' => 'User', 'description' => 'Mengubah data user'],
            ['name' => 'user.destroy', 'group' => 'User', 'description' => 'Menghapus user'],

            // Role Management
            ['name' => 'role.index',   'group' => 'Role Management', 'description' => 'Melihat daftar Role'],
            ['name' => 'role.create',  'group' => 'Role Management', 'description' => 'Menambah Role baru'],
            ['name' => 'role.edit',    'group' => 'Role Management', 'description' => 'Mengubah Role dan Hak Akses'],
            ['name' => 'role.destroy', 'group' => 'Role Management', 'description' => 'Menghapus Role'],

            // Notifikasi Log (Hanya index dan destroy)
            ['name' => 'notifikasi-log.index', 'group' => 'Notifikasi Log', 'description' => 'Melihat log dan notifikasi sistem'],
            ['name' => 'notifikasi-log.destroy', 'group' => 'Notifikasi Log', 'description' => 'Menghapus log notifikasi'],
        ];

        foreach ($permissions as $permission) { 
            Permission::firstOrCreate([
                'name' => $permission['name']
            ], [
                'group' => $permission['group'],
                'description' => $permission['description']
            ]);
        }
    }
}