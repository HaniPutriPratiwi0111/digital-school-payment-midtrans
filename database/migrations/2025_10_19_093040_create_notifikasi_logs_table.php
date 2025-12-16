<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('notifikasi_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('id_tagihan')->nullable()->constrained('tagihans')->onDelete('set null');
            
            $table->enum('tipe_notifikasi', ['Pengingat Tagihan', 'Konfirmasi Pembayaran', 'Info Lain']);
            $table->string('isi_pesan', 500);
            $table->enum('status_kirim', ['Pending', 'Sukses', 'Gagal']);
            $table->timestamp('waktu_kirim');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi_logs');
    }
};
