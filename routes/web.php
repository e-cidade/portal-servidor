<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ContraChequeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DependentesController;
use App\Http\Controllers\FeriasController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

Route::get('/contracheque-validar/{token}', [ContraChequeController::class, 'validar'])
    ->name('contracheque.validar');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('dependentes', [DependentesController::class, 'index'])->middleware(['auth', 'verified'])->name('dependentes');
Route::get('contracheque', [ContraChequeController::class, 'index'])->middleware(['auth', 'verified'])->name('contracheque');
Route::post('contracheque', [ContraChequeController::class, 'consulta'])->middleware(['auth', 'verified'])->name('contracheque');
Route::post('folha/tipos', [ContraChequeController::class, 'tipoFolha'])->middleware(['auth', 'verified'])->name('folha.tipos');
Route::get('contracheque/imprimir', [ContraChequeController::class, 'gerarPDFContraCheque'])->middleware(['auth', 'verified'])->name('relatorio.contracheque');
Route::get('ferias', [FeriasController::class, 'index'])->middleware(['auth', 'verified'])->name('ferias');
Route::get('rendimentos', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('rendimentos');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
