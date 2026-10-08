<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Datas
{
    /** "Hoje", "Ontem", "Amanhã" ou "segunda-feira, 5 de outubro". */
    public static function rotuloDia(CarbonInterface $data): string
    {
        if ($data->isToday()) {
            return 'Hoje';
        }

        if ($data->isYesterday()) {
            return 'Ontem';
        }

        if ($data->isTomorrow()) {
            return 'Amanhã';
        }

        $formato = $data->year === now()->year ? 'l, j \d\e F' : 'l, j \d\e F \d\e Y';

        return $data->translatedFormat($formato);
    }

    public static function extenso(CarbonInterface $data): string
    {
        return $data->translatedFormat('l, j \d\e F \d\e Y');
    }
}
