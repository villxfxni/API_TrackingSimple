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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->uuid('id')->primary();                     
            $table->foreignUuid('solicitante_id')               
                  ->constrained('solicitantes')
                  ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('creado_por_usuario_id')       
                  ->nullable()->constrained('usuarios')
                  ->nullOnDelete();
            $table->string('tipo')->index();                    
            $table->string('estado')->default('abierta')->index();
            $table->text('descripcion')->nullable();
            $table->jsonb('detalle')->nullable();         
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds');
    }
};
