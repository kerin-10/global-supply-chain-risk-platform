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
        Schema::table('negara', function (Blueprint $table) {
            $table->double('cuaca_kelembaban')->nullable()->after('cuaca_risiko_badai');
            $table->double('cuaca_suhu_terasa')->nullable()->after('cuaca_kelembaban');
            $table->double('cuaca_tekanan_udara')->nullable()->after('cuaca_suhu_terasa');
            $table->double('cuaca_jarak_pandang')->nullable()->after('cuaca_tekanan_udara');
            $table->double('cuaca_tutupan_awan')->nullable()->after('cuaca_jarak_pandang');
            $table->integer('cuaca_kode_cuaca')->nullable()->after('cuaca_tutupan_awan');
            $table->string('cuaca_deskripsi')->nullable()->after('cuaca_kode_cuaca');
            $table->string('bendera_url')->nullable()->after('cuaca_deskripsi');
            $table->double('luas_wilayah')->nullable()->after('bendera_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('negara', function (Blueprint $table) {
            $table->dropColumn([
                'cuaca_kelembaban',
                'cuaca_suhu_terasa',
                'cuaca_tekanan_udara',
                'cuaca_jarak_pandang',
                'cuaca_tutupan_awan',
                'cuaca_kode_cuaca',
                'cuaca_deskripsi',
                'bendera_url',
                'luas_wilayah'
            ]);
        });
    }
};
