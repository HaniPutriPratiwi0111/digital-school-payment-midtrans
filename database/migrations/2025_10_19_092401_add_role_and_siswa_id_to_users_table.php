<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom 'role' yang menyebabkan error di seeder
            $table->string('role')->default('Orang Tua')->after('email'); 
            
            // Kolom 'siswa_id' untuk Walimurid
            $table->unsignedBigInteger('siswa_id')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'siswa_id']);
        });
    }
};