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
        $aInstit=[
            'PM' => env('PM_INSTITUICAO') ? env('PM_INSTITUICAO') : 1,
            'CM' => env('CM_INSTITUICAO') ? env('CM_INSTITUICAO') : 2,
            'SA' => env('SA_INSTITUICAO') ? env('SA_INSTITUICAO') : 7,
            'PR' => env('PR_INSTITUICAO') ? env('PR_INSTITUICAO') : 6
        ];
        $url   = str_replace('-', '', $_SERVER['HTTP_HOST']);
        $codigoInstituicao = env('CODIGOINSTITUICAO');

        // Quebra a URL por ponto
        $partes = explode('.', $url);
        // Verifica se tem pelo menos 3 partes (subdomínio.dominio.tld)
        if (count($partes) >= 3) {
            $instit = strtoupper((substr($partes[1],0,2)));
            $codigoInstituicao = array_key_exists("$instit",$aInstit) ? $aInstit["$instit"] : $codigoInstituicao;
        }
        // Definindo a constante para a requisição
        define('CODIGOINSTITUICAO', $codigoInstituicao);

        // Compartilha com Inertia
        Inertia::share([
            'instituicao' => cache()->remember(
                'instituicao_' . CODIGOINSTITUICAO,
                3600,
                fn () => DbConfig::where('codigo', CODIGOINSTITUICAO)->select('codigo', 'nomeinst', 'ender', 'munic', 'uf', 'cep', 'bairro', 'numero', 'logo')->first()
            )
        ]);

        return $next($request);
    }
}
