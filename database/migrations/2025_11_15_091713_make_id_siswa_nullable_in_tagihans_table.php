<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            // Pastikan kolom id_siswa sudah ada sebelum diubah
            // Jika id_siswa adalah foreign key, gunakan ->nullable()->change()
            $table->foreignId('id_siswa')->nullable()->change(); 
            // Jika bukan foreign key dan hanya integer:
            // $table->unsignedBigInteger('id_siswa')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            // Untuk mengembalikan perubahan (jika dibutuhkan)
            $table->foreignId('id_siswa')->nullable(false)->change(); 
        });
    }
};