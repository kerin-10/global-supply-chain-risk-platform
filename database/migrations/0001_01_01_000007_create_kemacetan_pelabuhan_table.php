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
        Schema::create('kemacetan_pelabuhan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelabuhan_id')->constrained('pelabuhan')->onDelete('cascade');
            $table->double('waktu_tunda_jam')->default(0); // Delay hours
            $table->string('tingkat_kemacetan')->default('Rendah'); // Rendah, Sedang, Tinggi
            $table->text('deskripsi_status')->nullable();
            $table->timestamp('dilaporkan_pada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kemacetan_pelabuhan');
    }
};
