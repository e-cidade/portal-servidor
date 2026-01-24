<?php

namespace App\Http\Middleware;

use App\Models\Configuracoes\DbConfig;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SetCodigoInstituicao
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $aInstit = [
            'PM' => env('PM_INSTITUICAO') ? env('PM_INSTITUICAO') : 1,
            'CM' => env('CM_INSTITUICAO') ? env('CM_INSTITUICAO') : 2,
            'SA' => env('SA_INSTITUICAO') ? env('SA_INSTITUICAO') : 7,
            'PR' => env('PR_INSTITUICAO') ? env('PR_INSTITUICAO') : 6,
        ];

        // Usa o host da requisição em vez de acessar superglobal diretamente
        $host = str_replace('-', '', $request->getHost());
        $codigoInstituicao = env('CODIGOINSTITUICAO');

        // Quebra a URL por ponto
        $partes = explode('.', $host);
        // Verifica se tem pelo menos 3 partes (subdomínio.dominio.tld)
        if (count($partes) >= 3) {
            $instit = strtoupper(substr($partes[1], 0, 2));
            $codigoInstituicao = array_key_exists($instit, $aInstit) ? $aInstit[$instit] : $codigoInstituicao;
        }

        // Define a constante apenas se ainda não foi definida
        if (!defined('CODIGOINSTITUICAO')) {
            define('CODIGOINSTITUICAO', $codigoInstituicao);
        }

        // Se estivermos logado, limpamos o cache do usuário para garantir que ele venha do banco do tenant
        if (auth()->check()) {
            auth()->forgetUser();
        }

        $instituicao = null;
        // Só consulta o banco se a tenancy já estiver inicializada
        try {
            $tenancy = app(\Stancl\Tenancy\Tenancy::class);
            if ($tenancy && $tenancy->initialized) {
                $tenantId = optional($tenancy->tenant)->getTenantKey() ?? 'no-tenant';
                $cacheKey = 'instituicao_' . $tenantId . '_' . CODIGOINSTITUICAO;
                $instituicao = cache()->remember(
                    $cacheKey,
                    900,
                    function () {
                        $query = DbConfig::select('codigo', 'nomeinst', 'ender', 'munic', 'uf', 'cep', 'bairro', 'numero', 'logo');
                        $byCode = $query->where('codigo', CODIGOINSTITUICAO)->first();
                        if ($byCode) {
                            return $byCode;
                        }
                        // Fallback: pega a primeira instituição disponível do tenant
                        return DbConfig::select('codigo', 'nomeinst', 'ender', 'munic', 'uf', 'cep', 'bairro', 'numero', 'logo')
                            ->orderBy('codigo')
                            ->first();
                    }
                );
            }
        } catch (\Throwable $e) {
            // Se algo der errado antes de inicializar a tenancy, seguimos sem quebrar a requisição

            $instituicao = null;
        }

        // Compartilha com Inertia (null quando ainda não houver contexto de tenant)
        Inertia::share([
            'instituicao' => $instituicao,
        ]);
        // Compartilha também com Blade, para telas não-Inertia (ex.: login Blade)
        view()->share('instituicao', $instituicao);

        return $next($request);
    }
}
