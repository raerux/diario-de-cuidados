<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuidados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idoso_id')->constrained('idosos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('tipo', 30);
            $table->string('status', 20)->default('realizado');
            $table->dateTime('data_hora');
            $table->string('responsavel', 150);
            $table->text('descricao');

            // Medicação
            $table->string('medicamento', 150)->nullable();
            $table->string('dosagem', 100)->nullable();

            // Sinais vitais
            $table->unsignedSmallInteger('pressao_sistolica')->nullable();
            $table->unsignedSmallInteger('pressao_diastolica')->nullable();
            $table->unsignedSmallInteger('frequencia_cardiaca')->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->unsignedSmallInteger('glicemia')->nullable();
            $table->unsignedTinyInteger('saturacao')->nullable();

            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['idoso_id', 'data_hora']);
            $table->index(['status', 'data_hora']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuidados');
    }
};
