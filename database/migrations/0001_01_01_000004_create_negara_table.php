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
        Schema::create('negara', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kode_iso2', 2)->unique();
            $table->string('kode_iso3', 3)->nullable();
            $table->string('wilayah')->nullable();
            $table->string('ibu_kota')->nullable();
            $table->string('kode_mata_uang', 3)->nullable();
            $table->string('nama_mata_uang')->nullable();
            $table->text('bahasa')->nullable();
            $table->bigInteger('populasi')->default(0);
            $table->double('pdb')->default(0); // GDP
            $table->double('inflasi')->default(0);
            $table->double('nilai_ekspor')->default(0);
            $table->double('nilai_impor')->default(0);
            $table->double('lintang')->nullable(); // Latitude
            $table->double('bujur')->nullable();  // Longitude
            $table->double('cuaca_suhu')->nullable();
            $table->double('cuaca_curah_hujan')->nullable();
            $table->double('cuaca_kecepatan_angin')->nullable();
            $table->double('cuaca_risiko_badai')->nullable();
            $table->timestamp('sinkronisasi_terakhir_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('negara');
    }
};
