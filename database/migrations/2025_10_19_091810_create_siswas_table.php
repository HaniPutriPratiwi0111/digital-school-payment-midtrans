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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('restrict');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            
            $table->string('nisn')->unique()->nullable();
            $table->string('nama_siswa');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('agama');
            
            $table->string('nama_wali_murid');
            $table->string('telp_wali_murid');
            
            // LOGIKA DISKON KELUARGA
            $table->boolean('is_keluarga')->default(false); // TRUE jika mendapat potongan harga khusus
             $table->enum('status_aktif', ['Aktif', 'Lulus', 'Keluar'])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
