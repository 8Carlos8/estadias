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
        Schema::create('documento_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estadia_id')->constrained('estadias')->onDelete('cascade');
            $table->string('nombre', 255);
            $table->string('ruta', 255);
            $table->datetime('fecha_subida');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agregar_documento_extras');
    }
};
