<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            // Tambahkan kolom baru untuk FK ke CalonSiswa
            // Nullable karena tagihan SPP tetap merujuk ke id_siswa
            $table->foreignId('calon_siswa_id')->nullable()->after('id_siswa')->constrained('calon_siswas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('calon_siswa_id');
        });
    }
};