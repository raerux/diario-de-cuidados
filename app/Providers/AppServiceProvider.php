<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // URLs em português: /idosos/create vira /idosos/novo, /edit vira /editar
        Route::resourceVerbs([
            'create' => 'novo',
            'edit' => 'editar',
        ]);

        // Fuso horário do sistema (config/cuidados.php ou APP_TIMEZONE no .env)
        $fuso = config('cuidados.timezone');
        if ($fuso) {
            config(['app.timezone' => $fuso]);
            date_default_timezone_set($fuso);
        }

        // Datas por extenso em português ("quarta-feira, 7 de outubro")
        Carbon::setLocale(config('app.locale'));

        Paginator::defaultView('partials.paginacao');
        Paginator::defaultSimpleView('partials.paginacao');
    }
}
