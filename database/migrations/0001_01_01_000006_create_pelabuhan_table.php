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
        Schema::create('pelabuhan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode_pelabuhan')->nullable();
            $table->double('lintang');
            $table->double('bujur');
            $table->foreignId('negara_id')->constrained('negara')->onDelete('cascade');
            $table->string('kode_negara', 2);
            $table->string('wilayah')->nullable();
            $table->string('nomor_wpi')->nullable(); // World Port Index
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelabuhan');
    }
};
