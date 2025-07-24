<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pemeriksaan_peserta_parameter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pemeriksaan_id');
            $table->unsignedBigInteger('pemeriksaan_peserta_id');
            $table->unsignedBigInteger('pemeriksaan_parameter_id');
            $table->decimal('nilai', 10, 2);
            $table->enum('trend', ['stabil', 'penurunan', 'kenaikan'])->default('stabil');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('pemeriksaan_id')->references('id')->on('pemeriksaan')->onDelete('cascade');
            $table->foreign('pemeriksaan_peserta_id')->references('id')->on('pemeriksaan_peserta')->onDelete('cascade');
            $table->foreign('pemeriksaan_parameter_id')->references('id')->on('pemeriksaan_parameter')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_peserta_parameter');
    }
}; 