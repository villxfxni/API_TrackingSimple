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
        Schema::create('donaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();                      
            $table->foreignUuid('solicitud_id')                 
                  ->nullable()->constrained('solicitudes')
                  ->nullOnDelete();
            $table->foreignUuid('usuario_id')                   
                  ->nullable()->constrained('usuarios')
                  ->nullOnDelete();
            $table->string('titulo');
            $table->integer('cantidad')->nullable();
            $table->string('estado')->default('ofrecida')->index();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donacions');
    }
};
