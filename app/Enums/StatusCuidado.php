<?php

namespace App\Enums;

enum StatusCuidado: string
{
    case Realizado = 'realizado';
    case Pendente = 'pendente';
    case NaoRealizado = 'nao_realizado';

    public function label(): string
    {
        return match ($this) {
            self::Realizado => 'Feito',
            self::Pendente => 'Pendente',
            self::NaoRealizado => 'Não foi feito',
        };
    }

    public function dica(): string
    {
        return match ($this) {
            self::Realizado => 'O cuidado já aconteceu',
            self::Pendente => 'Está agendado para acontecer',
            self::NaoRealizado => 'Estava previsto, mas não aconteceu (explique nas observações)',
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
