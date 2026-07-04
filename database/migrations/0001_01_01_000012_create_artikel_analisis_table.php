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
        Schema::create('artikel_analisis', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('ringkasan');
            $table->longText('konten');
            $table->foreignId('penulis_id')->constrained('pengguna')->onDelete('cascade');
            $table->string('status')->default('Draft'); // Draft, Diterbitkan
            $table->timestamp('diterbitkan_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel_analisis');
    }
};
