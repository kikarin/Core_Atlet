<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('rencana_latihan_atlet', function (Blueprint $table) {
            $table->string('kehadiran')->nullable()->after('atlet_id');
        });
        Schema::table('rencana_latihan_pelatih', function (Blueprint $table) {
            $table->string('kehadiran')->nullable()->after('pelatih_id');
        });
        Schema::table('rencana_latihan_tenaga_pendukung', function (Blueprint $table) {
            $table->string('kehadiran')->nullable()->after('tenaga_pendukung_id');
        });
    }

    public function down(): void
    {
        Schema::table('rencana_latihan_atlet', function (Blueprint $table) {
            $table->dropColumn('kehadiran');
        });
        Schema::table('rencana_latihan_pelatih', function (Blueprint $table) {
            $table->dropColumn('kehadiran');
        });
        Schema::table('rencana_latihan_tenaga_pendukung', function (Blueprint $table) {
            $table->dropColumn('kehadiran');
        });
    }
};
