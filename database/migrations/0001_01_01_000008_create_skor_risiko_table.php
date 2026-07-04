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
        Schema::create('skor_risiko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negara_id')->constrained('negara')->onDelete('cascade');
            $table->double('risiko_cuaca')->default(0);
            $table->double('risiko_inflasi')->default(0);
            $table->double('risiko_nilai_tukar')->default(0);
            $table->double('risiko_sentimen_berita')->default(0);
            $table->double('total_risiko')->default(0);
            $table->string('tingkat_risiko')->default('Rendah'); // Rendah, Sedang, Tinggi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skor_risiko');
    }
};
