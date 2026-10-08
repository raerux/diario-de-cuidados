<?php

namespace App\Enums;

enum Sexo: string
{
    case Feminino = 'feminino';
    case Masculino = 'masculino';
    case Outro = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::Feminino => 'Feminino',
            self::Masculino => 'Masculino',
            self::Outro => 'Outro',
        };
    }

    /** @return array<string, string> */
    public static function opcoes(): array
    {
        $opcoes = [];
        foreach (self::cases() as $caso) {
            $opcoes[$caso->value] = $caso->label();
        }

        return $opcoes;
    }
}
