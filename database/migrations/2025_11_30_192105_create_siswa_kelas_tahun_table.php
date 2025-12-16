<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('siswa_kelas_tahun', function (Blueprint $table) {
            $table->id();

            // Hubungkan siswa
            $table->foreignId('id_siswa')->constrained('siswas')->onDelete('cascade');

            // Kelas siswa untuk tahun ajaran tersebut
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('restrict');

            // Tahun ajaran seperti 2024, 2025 dst
            $table->foreignId('id_tahun_ajaran')->constrained('tahun_ajarans')->onDelete('restrict');
            
            // Status siswa pada tahun itu
            $table->enum('status', ['Aktif', 'Naik', 'Tinggal', 'Pindah', 'Lulus'])
                  ->default('Aktif');

            // menjaga agar siswa tidak tercatat 2x dalam kelas yang sama pada tahun yang sama
            $table->unique(['id_siswa', 'id_tahun_ajaran']);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siswa_kelas_tahun');
    }
};
