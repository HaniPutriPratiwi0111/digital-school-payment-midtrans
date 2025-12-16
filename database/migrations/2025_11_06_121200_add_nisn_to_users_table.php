<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // Pastikan kolom email nullable (kalau belum)
            $table->string('email')->nullable()->change();

            // Tambah kolom NISN setelah email
            if (!Schema::hasColumn('users', 'nisn')) {
                $table->string('nisn')->nullable()->unique()->after('email');
            }
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nisn')) {
                $table->dropColumn('nisn');
            }
        });
    }
};
