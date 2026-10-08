<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioInicialSeeder extends Seeder
{
    public const EMAIL = 'admin@exemplo.com';

    public const SENHA = 'senha123';

    /**
     * Cria o primeiro acesso ao sistema. Se ele já existir, não mexe na senha.
     */
    public function run(): void
    {
        if (User::query()->where('email', self::EMAIL)->exists()) {
            return;
        }

        (new User)->forceFill([
            'name' => 'Administrador',
            'email' => self::EMAIL,
            'password' => Hash::make(self::SENHA),
            'email_verified_at' => now(),
        ])->save();
    }
}
