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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tagihan')->constrained('tagihans')->onDelete('restrict');
            $table->foreignId('id_user')->nullable()->constrained('users')->onDelete('set null')->after('id_tagihan');
            
            $table->string('kode_transaksi')->unique();
            $table->date('tanggal_bayar');
            $table->enum('metode_pembayaran', ['Midtrans', 'Tunai']);
            $table->decimal('total_bayar', 15, 2);
            $table->string('midtrans_transaction_id')->nullable();
            
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};

