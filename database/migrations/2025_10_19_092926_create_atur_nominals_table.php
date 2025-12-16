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
        Schema::create('atur_nominals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jenis_pembayaran')->constrained('jenis_pembayarans')->onDelete('cascade');
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajarans')->onDelete('cascade');
            
            $table->foreignId('id_jenjang')->constrained('master_jenjangs')->onDelete('restrict');
            $table->unsignedSmallInteger('tingkat')->nullable();
            $table->unsignedSmallInteger('bulan_berlaku')->nullable();
            
            // 🆕 KOLOM BARU UNTUK HARGA
            $table->decimal('nominal_normal', 15, 2); // Harga default/normal
            $table->decimal('nominal_keluarga', 15, 2)->nullable(); // Harga diskon, bisa null
            
            // Unique index disesuaikan
            $table->unique([
                'id_jenis_pembayaran', 
                'id_tahun_ajaran', 
                'id_jenjang', 
                'tingkat', 
                'bulan_berlaku'
            ], 'unique_aturan');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atur_nominals');
    }
};