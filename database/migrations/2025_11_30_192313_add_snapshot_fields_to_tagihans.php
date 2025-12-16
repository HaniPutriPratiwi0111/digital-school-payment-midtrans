<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tagihans', function (Blueprint $table) {

            // Kelas pada saat tagihan dibuat (snapshot)
            $table->unsignedBigInteger('snapshot_kelas_id')->nullable()->after('id_siswa');

            // Bisa hubungan opsional
            $table->foreign('snapshot_kelas_id')->references('id')->on('kelas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropForeign(['snapshot_kelas_id']);
            $table->dropColumn('snapshot_kelas_id');
        });
    }
};
