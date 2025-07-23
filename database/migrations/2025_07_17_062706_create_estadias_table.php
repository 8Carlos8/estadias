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
        Schema::create('estadias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('users')->onDelete('cascade');
            $table->string('empresa', 150);
            $table->string('asesor_externo', 150);
            $table->string('proyecto_nombre', 255);
            $table->integer('duracion_semanas');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('apoyo', 255);
            $table->enum('estatus', ['solicitada', 'en revisión', 'aceptada', 'concluida']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estadias');
    }
};
