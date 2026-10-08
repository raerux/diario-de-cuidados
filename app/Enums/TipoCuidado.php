<?php

namespace App\Enums;

enum TipoCuidado: string
{
    case Medicacao = 'medicacao';
    case Alimentacao = 'alimentacao';
    case Hidratacao = 'hidratacao';
    case Higiene = 'higiene';
    case SinaisVitais = 'sinais_vitais';
    case Mobilidade = 'mobilidade';
    case Curativo = 'curativo';
    case Consulta = 'consulta';
    case Sono = 'sono';
    case Outro = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::Medicacao => 'Medicação',
            self::Alimentacao => 'Alimentação',
            self::Hidratacao => 'Hidratação',
            self::Higiene => 'Higiene',
            self::SinaisVitais => 'Sinais vitais',
            self::Mobilidade => 'Mobilidade e exercício',
            self::Curativo => 'Curativo',
            self::Consulta => 'Consulta ou exame',
            self::Sono => 'Sono e repouso',
            self::Outro => 'Outro',
        };
    }

    /** Texto de ajuda exibido no formulário. */
    public function dica(): string
    {
        return match ($this) {
            self::Medicacao => 'Remédio dado, dose e horário',
            self::Alimentacao => 'Refeição e quanto foi aceito',
            self::Hidratacao => 'Água, sucos, chás',
            self::Higiene => 'Banho, troca de fralda, higiene bucal',
            self::SinaisVitais => 'Pressão, temperatura, glicemia, saturação',
            self::Mobilidade => 'Caminhada, fisioterapia, mudança de posição',
            self::Curativo => 'Troca de curativo, cuidados com a pele',
            self::Consulta => 'Médico, exames, teleconsulta',
            self::Sono => 'Como dormiu, cochilos, agitação',
            self::Outro => 'Qualquer outro cuidado',
        };
    }

    /** @return array<string, string> valor => rótulo, para selects e filtros */
    public static function opcoes(): array
    {
        $opcoes = [];
        foreach (self::cases() as $caso) {
            $opcoes[$caso->value] = $caso->label();
        }

        return $opcoes;
    }
}
