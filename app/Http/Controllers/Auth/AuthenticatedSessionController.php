<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Configuracoes\DbConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Stancl\Tenancy\Tenancy;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        // Garantir que o tenant já está carregado e, se estiver, expor dados úteis à view
        $tenancy = app(Tenancy::class);
        $tenant = null;
        $tenantData = null;
        $instituicao = null;

        if ($tenancy && $tenancy->initialized) {
            $tenant = $tenancy->tenant;
            $tenantData = $tenant?->getAttribute('data') ?? [];

            // Buscar dados da instituição a partir do banco do tenant
            try {
                // Garanta o uso explícito da conexão do tenant nesta página
                $query = DbConfig::on('tenant')->select('codigo', 'nomeinst', 'ender', 'munic', 'uf', 'cep', 'bairro', 'numero', 'logo');
                if (defined('CODIGOINSTITUICAO')) {
                    $instituicao = (clone $query)->where('codigo', CODIGOINSTITUICAO)->first();
                }
                if (!$instituicao) {
                    $instituicao = $query->orderBy('codigo')->first();
                }
            } catch (\Throwable $e) {
                // Mantém null se algo ocorrer antes da conexão do tenant estar pronta
                $instituicao = new DbConfig();
                $instituicao->codigo = 1;
                $instituicao->nomeinst = 'Modelo';
                $instituicao->ender = 'ender';
            }
        }

        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            // Props adicionais úteis no contexto do tenant
            'tenant' => $tenant ? [
                'id' => $tenant->getTenantKey(),
                'data' => $tenantData,
            ] : null,
            'instituicao' => $instituicao,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
