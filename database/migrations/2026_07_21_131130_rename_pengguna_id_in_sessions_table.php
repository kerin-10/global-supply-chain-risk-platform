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
        Schema::table('sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sessions', 'pengguna_id') && !Schema::hasColumn('sessions', 'user_id')) {
                $table->renameColumn('pengguna_id', 'user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sessions', 'user_id') && !Schema::hasColumn('sessions', 'pengguna_id')) {
                $table->renameColumn('user_id', 'pengguna_id');
            }
        });
    }
};
