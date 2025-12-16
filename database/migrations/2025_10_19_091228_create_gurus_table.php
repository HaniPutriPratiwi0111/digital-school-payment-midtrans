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
        // Pastikan tabel belum ada sebelum dibuat
        if (!Schema::hasTable('gurus')) {
            Schema::create('gurus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
                $table->string('nama');
                $table->string('nuptk')->unique()->nullable(); // Tambahan NUPTK
                $table->string('nip')->unique()->nullable();   // Tambahan NIP
                $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']); // Tambahan Jenis Kelamin
                $table->string('agama');
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->string('foto')->nullable();            // Foto (Opsional)
                
                // BARIS PENTING YANG MEMPERBAIKI ERROR
                $table->softDeletes(); 
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gurus');
    }
};