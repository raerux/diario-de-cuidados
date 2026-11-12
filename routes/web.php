<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CadastroController;
use App\Http\Controllers\CuidadoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IdosoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

// Endereço principal: apresentação do sistema para quem não está conectado,
// tela "Hoje" para quem está.
Route::get('/', function () {
    return auth()->check()
        ? app()->call(DashboardController::class)
        : view('inicio');
})->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/cadastrar', [CadastroController::class, 'create'])->name('cadastro');
    Route::post('/cadastrar', [CadastroController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('cadastro.store');

    Route::get('/entrar', [AuthController::class, 'create'])->name('login');
    Route::post('/entrar', [AuthController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/sair', [AuthController::class, 'destroy'])->name('logout');

    Route::get('idosos/{idoso}/relatorio', RelatorioController::class)->name('idosos.relatorio');
    Route::resource('idosos', IdosoController::class)
        ->parameters(['idosos' => 'idoso']);

    Route::patch('cuidados/{cuidado}/concluir', [CuidadoController::class, 'concluir'])
        ->name('cuidados.concluir');
    Route::resource('cuidados', CuidadoController::class)
        ->parameters(['cuidados' => 'cuidado']);

    Route::resource('usuarios', UsuarioController::class)
        ->parameters(['usuarios' => 'usuario'])
        ->except('show');
});
