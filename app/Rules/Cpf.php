<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida um CPF pelos dígitos verificadores.
 * Aceita com ou sem máscara (000.000.000-00 ou 00000000000).
 */
class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::valido((string) $value)) {
            $fail('O :attribute informado não é válido.');
        }
    }

    public static function valido(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($posicao = 9; $posicao < 11; $posicao++) {
            if ((int) $cpf[$posicao] !== self::digito(substr($cpf, 0, $posicao))) {
                return false;
            }
        }

        return true;
    }

    /** Gera um CPF válido (usado nos dados de exemplo e nos testes). */
    public static function gerar(): string
    {
        do {
            $base = '';
            for ($i = 0; $i < 9; $i++) {
                $base .= random_int(0, 9);
            }
        } while (preg_match('/^(\d)\1{8}$/', $base));

        $base .= self::digito($base);
        $base .= self::digito($base);

        return $base;
    }

    public static function formatar(?string $cpf): ?string
    {
        if ($cpf === null || strlen($cpf) !== 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3).'.'.substr($cpf, 3, 3).'.'.substr($cpf, 6, 3).'-'.substr($cpf, 9, 2);
    }

    private static function digito(string $numeros): int
    {
        $peso = strlen($numeros) + 1;
        $soma = 0;

        foreach (str_split($numeros) as $numero) {
            $soma += (int) $numero * $peso--;
        }

        $resto = $soma % 11;

        return $resto < 2 ? 0 : 11 - $resto;
    }
}
