<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']); // hapus unique
        });
    }

    public function down()
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->unique('midtrans_order_id'); // kalau rollback
        });
    }
};
