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
        Schema::create('nilai_tukar_mata_uang', function (Blueprint $table) {
            $table->id();
            $table->string('mata_uang_asal', 3);
            $table->string('mata_uang_tujuan', 3);
            $table->double('nilai_tukar');
            $table->timestamp('terakhir_diperbarui_pada');
            $table->timestamps();

            $table->unique(['mata_uang_asal', 'mata_uang_tujuan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_tukar_mata_uang');
    }
};
