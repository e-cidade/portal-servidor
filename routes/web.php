<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


// Primeiro inicializamos a tenancy pelo domínio; somente depois aplicamos SetCodigoInstituicao
Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::middleware([
        HandleInertiaRequests::class,
    ])->group(function () {

        // Rotas específicas dos tenants
        require __DIR__ . '/tenant.php';

        require __DIR__ . '/settings.php';
        require __DIR__ . '/auth.php';
    });

});
