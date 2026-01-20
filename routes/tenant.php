<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ContraChequeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DependentesController;
use App\Http\Controllers\FeriasController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Todas as rotas abaixo são executadas no contexto do tenant, pois este
| arquivo é carregado dentro do grupo protegido pelos middlewares de tenancy
| (InitializeTenancyByDomain e PreventAccessFromCentralDomains) definido em
| routes/web.php.
|
*/

// Página inicial (login)
Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

// Validação pública de contracheque por token (não exige auth)
Route::get('/contracheque-validar/{token}', [ContraChequeController::class, 'validar'])
    ->name('contracheque.validar');

// Rotas protegidas
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dependentes', [DependentesController::class, 'index'])->name('dependentes');

    Route::get('contracheque', [ContraChequeController::class, 'index'])->name('contracheque');
    Route::post('contracheque', [ContraChequeController::class, 'consulta'])->name('contracheque');
    Route::post('folha/tipos', [ContraChequeController::class, 'tipoFolha'])->name('folha.tipos');
    Route::get('contracheque/imprimir', [ContraChequeController::class, 'gerarPDFContraCheque'])->name('relatorio.contracheque');

    Route::get('ferias', [FeriasController::class, 'index'])->name('ferias');
    Route::get('rendimentos', [DashboardController::class, 'index'])->name('rendimentos');
});
