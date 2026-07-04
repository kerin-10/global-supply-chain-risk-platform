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
        Schema::create('arsip_berita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negara_id')->constrained('negara')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->text('konten')->nullable();
            $table->text('tautan_url');
            $table->string('sumber')->nullable();
            $table->timestamp('diterbitkan_pada');
            $table->string('sentimen')->default('Netral'); // Positif, Netral, Negatif
            $table->integer('skor_sentimen_positif')->default(0);
            $table->integer('skor_sentimen_negatif')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_berita');
    }
};
