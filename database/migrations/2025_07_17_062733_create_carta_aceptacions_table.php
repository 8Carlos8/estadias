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
        Schema::create('carta_aceptacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estadia_id')->constrained('estadias')->onDelete('cascade');
            $table->date('fecha_recepcion');
            $table->string('ruta_documento', 255);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carta_aceptacions');
    }
};
