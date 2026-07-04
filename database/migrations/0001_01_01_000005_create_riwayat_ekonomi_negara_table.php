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
        Schema::create('riwayat_ekonomi_negara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negara_id')->constrained('negara')->onDelete('cascade');
            $table->integer('tahun');
            $table->double('pdb')->default(0); // GDP
            $table->double('inflasi')->default(0);
            $table->bigInteger('populasi')->default(0);
            $table->double('nilai_ekspor')->nullable();
            $table->double('nilai_impor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_ekonomi_negara');
    }
};
