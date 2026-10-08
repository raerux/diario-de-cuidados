<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * php artisan migrate:fresh --seed
     *     cria o acesso inicial e os dados de exemplo.
     *
     * php artisan migrate:fresh --seeder=UsuarioInicialSeeder
     *     começa com o banco vazio, só com o acesso inicial.
     */
    public function run(): void
    {
        $this->call([
            UsuarioInicialSeeder::class,
            DadosExemploSeeder::class,
        ]);
    }
}
