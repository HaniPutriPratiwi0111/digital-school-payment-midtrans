<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // Ubah ENUM menjadi string dengan panjang 50
            $table->string('metode_pembayaran', 50)->change(); 
        });
    }

    public function down(): void
    {
        // Kembalikan ke ENUM jika rollback (undo migrate) diperlukan
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->enum('metode_pembayaran', ['Midtrans', 'Tunai'])->change(); 
        });
    }
};