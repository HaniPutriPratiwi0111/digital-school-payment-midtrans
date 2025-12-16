<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_siswa')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('id_jenis_pembayaran')->constrained('jenis_pembayarans')->onDelete('restrict');
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajarans')->onDelete('restrict');
            
            // Kolom tracing (Sumber Aturan)
            $table->foreignId('id_atur_nominal')->nullable()->constrained('atur_nominals')->onDelete('restrict'); 
            
            // Informasi Tagihan
            $table->unsignedSmallInteger('bulan_tagihan')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();

            // LOG HARGA UTAMA: Harga Normal (dari AturNominal)
            $table->decimal('nominal_tagihan', 15, 2); 
            
            // LOG KEPUTUSAN HARGA
            $table->decimal('nominal_diskon', 15, 2)->default(0); // <-- INI YANG HARUS DITAMBAHKAN!
            $table->boolean('is_harga_keluarga_applied')->default(false); 
            
            // Total tagihan: Harga Final (nominal_tagihan - nominal_diskon)
            $table->decimal('total_tagihan', 15, 2)->default(0); 

            $table->enum('status', [
                'Belum Bayar',
                'Lunas Partial',
                'Lunas',
                'Batal'
            ])->default('Belum Bayar');

            $table->string('midtrans_order_id')->nullable()->unique();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
