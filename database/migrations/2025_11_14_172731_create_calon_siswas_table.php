<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calon_siswas', function (Blueprint $table) {
            $table->id();

            // === RELASI & IDENTITAS AKUN WALI MURID ===
            // Relasi ke tabel Users (akun Wali Murid)
            $table->foreignId('id_user_wali')->nullable()->constrained('users')->onDelete('cascade'); 
            
            // Data wali & Email (Email ini adalah username login wali)
            $table->string('nama_wali_murid');
            $table->string('email'); // <=== TIDAK ADA nullable() -> WAJIB DIISI DI CONTROLLER
            $table->string('telp_wali_murid');
            $table->boolean('is_keluarga')->default(false);


            // === RELASI AKADEMIK ===
            $table->foreignId('id_jenjang')->constrained('master_jenjangs')->onDelete('restrict');
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajarans')->onDelete('restrict');

            
            // === IDENTITAS CALON SISWA (DIISI DI FORM) ===
            $table->string('nama_siswa'); // TIDAK ADA nullable()
            $table->string('tempat_lahir'); // Hapus nullable()
            $table->date('tanggal_lahir'); // Hapus nullable()
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']); // Hapus nullable()
            $table->string('agama'); // Hapus nullable()
            
            // $table->string('nisn')->unique(); // <--- Dihapus, NISN baru dibuat saat sudah menjadi Siswa
            
            
            // === TRANSAKSI PENDAFTARAN ===
            $table->string('midtrans_order_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->enum('payment_status', ['Menunggu', 'Lunas', 'Gagal'])->default('Menunggu');

            // === APPROVAL ADMIN ===
            $table->enum('approval_status', ['Diajukan', 'Disetujui', 'Ditolak'])->default('Diajukan');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calon_siswas');
    }
};