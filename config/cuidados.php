<?php

return [

    /*
    | Fuso horário usado em todo o sistema (datas e horas dos cuidados).
    | Altere com APP_TIMEZONE no .env, por exemplo America/Manaus.
    */
    'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),

    /* Quantos registros aparecem por página nas listagens. */
    'por_pagina' => 20,

    /*
    | Faixas usuais dos sinais vitais. Valores fora delas aparecem destacados
    | no sistema. São referências gerais para adultos: ajuste com a equipe de
    | saúde que acompanha cada idoso.
    */
    'faixas' => [
        'pressao_sistolica' => ['min' => 90, 'max' => 139, 'nome' => 'Pressão máxima', 'unidade' => 'mmHg'],
        'pressao_diastolica' => ['min' => 60, 'max' => 89, 'nome' => 'Pressão mínima', 'unidade' => 'mmHg'],
        'frequencia_cardiaca' => ['min' => 50, 'max' => 100, 'nome' => 'Batimentos', 'unidade' => 'bpm'],
        'temperatura' => ['min' => 35.0, 'max' => 37.7, 'nome' => 'Temperatura', 'unidade' => '°C'],
        'glicemia' => ['min' => 70, 'max' => 180, 'nome' => 'Glicemia', 'unidade' => 'mg/dL'],
        'saturacao' => ['min' => 92, 'max' => 100, 'nome' => 'Saturação', 'unidade' => '%'],
    ],

];
