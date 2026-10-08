<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idosos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->date('data_nascimento');
            $table->string('sexo', 20)->nullable();
            $table->string('cpf', 11)->nullable()->unique();
            $table->string('tipo_sanguineo', 3)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('endereco')->nullable();
            $table->string('contato_emergencia_nome', 150)->nullable();
            $table->string('contato_emergencia_telefone', 20)->nullable();
            $table->text('condicoes_saude')->nullable();
            $table->text('alergias')->nullable();
            $table->text('medicamentos_continuos')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idosos');
    }
};
