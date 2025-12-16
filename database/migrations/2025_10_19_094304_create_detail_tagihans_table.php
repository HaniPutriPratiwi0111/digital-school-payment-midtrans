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
        Schema::create('detail_tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tagihan')->constrained('tagihans')->onDelete('cascade');
            $table->foreignId('id_jenis_pembayaran')->constrained('jenis_pembayarans')->onDelete('restrict');
            
            $table->string('deskripsi')->nullable();
            $table->decimal('nominal_unit', 15, 2);
            $table->unsignedSmallInteger('qty')->default(1);
            $table->decimal('subtotal', 15, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_tagihans');
    }
};
