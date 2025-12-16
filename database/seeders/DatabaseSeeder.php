<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Jalankan Seeder Permissions dulu
        $this->call(PermissionsSeeder::class);

        // ==============================================
        // 2️⃣ Super Administrator
        // Kelola: calon siswa, tahun ajaran, jenjang, kelas, siswa, manajemen user, role & permission
        // ==============================================
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password12345'),
            ]
        );

        $roleSuper = Role::firstOrCreate(['name' => 'Super Administrator']);
        $adminPermissions = Permission::whereIn('name', [
            // Master Data
            'master-jenjang.index','master-jenjang.create','master-jenjang.edit','master-jenjang.destroy',
            'tahun-ajaran.index','tahun-ajaran.create','tahun-ajaran.edit','tahun-ajaran.destroy',
            'kelas.index','kelas.create','kelas.edit','kelas.destroy',
            'siswa.index','siswa.create','siswa.edit','siswa.destroy',

            // User & Role
            'user.index','user.create','user.edit','user.destroy',
            'role.index','role.create','role.edit','role.destroy',
        ])->pluck('name');

        $roleSuper->syncPermissions($adminPermissions);
        $admin->assignRole($roleSuper);

        // ==============================================
        // 3️⃣ Bendahara
        // Kelola: jenis pembayaran, atur nominal, tagihan, pembayaran
        // ==============================================
        $bendahara = User::firstOrCreate(
            ['email' => 'bendahara@gmail.com'],
            [
                'name' => 'Bendahara',
                'password' => Hash::make('password12345'),
            ]
        );

        $roleBendahara = Role::firstOrCreate(['name' => 'Bendahara']);
        $bendaharaPermissions = Permission::whereIn('name', [
            // Keuangan
            'jenis-pembayaran.index','jenis-pembayaran.create','jenis-pembayaran.edit','jenis-pembayaran.destroy',
            'atur-nominal.index','atur-nominal.create','atur-nominal.edit','atur-nominal.destroy',

            // Tagihan
            'tagihan.index','tagihan.show','tagihan.create','tagihan.edit','tagihan.destroy',
            'detail-tagihan.index','detail-tagihan.create','detail-tagihan.edit','detail-tagihan.destroy',

            // Pembayaran
            'pembayaran.index','pembayaran.create','pembayaran.show',
            'midtrans.payPage','midtrans.pay'
        ])->pluck('name');

        $roleBendahara->syncPermissions($bendaharaPermissions);
        $bendahara->assignRole($roleBendahara);

        // ==============================================
        // 4️⃣ Role Orang Tua
        // Kelola: tagihan anak
        // ==============================================
        $roleOrtu = Role::firstOrCreate(['name' => 'Orang Tua']);
        $roleOrtu->givePermissionTo([
            'tagihan.index',        // Melihat daftar tagihan anak
            'tagihan.anak',         // Melihat tagihan anak spesifik
            'tagihan.show-wali',    // Melihat detail tagihan anak
            'midtrans.payPage',     // View halaman pay.blade.php
        ]);
    }
}
