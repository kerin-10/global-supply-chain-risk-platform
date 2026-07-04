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
        Schema::create('log_permintaan_api', function (Blueprint $table) {
            $table->id();
            $table->string('nama_api');
            $table->text('endpoint');
            $table->integer('status_respons');
            $table->integer('waktu_respons_ms');
            $table->timestamp('diminta_pada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_permintaan_api');
    }
};
