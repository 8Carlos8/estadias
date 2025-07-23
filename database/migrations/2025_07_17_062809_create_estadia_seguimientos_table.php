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
        Schema::create('estadia_seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estadia_id')->constrained('estadias')->onDelete('cascade');
            $table->enum('etapa', ['solicitud','presentacion','firma_director','aceptacion','registro_final','inicio','finalizacion']);
            $table->enum('estatus', ['pendiente','completado','rechazado','en revisión']);
            $table->text('comentario')->nullable();
            $table->dateTime('fecha_actualizacion');
            $table->foreignId('actualizado_por')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estadia_seguimientos');
    }
};
