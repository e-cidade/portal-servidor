<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Stancl\Tenancy\Events\TenancyEnded;
use Stancl\Tenancy\Events\TenancyInitialized;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Log::info('AppServiceProvider booting');

        if (app()->environment('production')) {
            $this->app['request']->server->set('HTTPS', 'on');
            URL::forceScheme('https');
        }

        // Remember central default connection so we can restore it when tenancy ends
        $centralDefault = config('database.default');
        Config::set('database.central_default', $centralDefault);

        // Se o tenant não for encontrado pelo domínio, segue com a conexão padrão do .env
        InitializeTenancyByDomain::$onFail = function ($e, $request, $next) {
            Log::info('[tenancy] Tenant não encontrado, usando conexão padrão do .env', [
                'host' => $request->getHost(),
            ]);

            if (!defined('CODIGOINSTITUICAO')) {
                define('CODIGOINSTITUICAO', env('CODIGOINSTITUICAO'));
            }

            Inertia::share([
                'instituicao' => CODIGOINSTITUICAO,
            ]);

            DB::connection()->statement('SET search_path TO public, pessoal, protocolo, cadastro, configuracoes ');

            return $next($request);
        };

        // Permite acesso pelos domínios centrais (ex.: localhost) usando a conexão padrão
        PreventAccessFromCentralDomains::$abortRequest = function ($request, $next) {
            return $next($request);
        };

        // Configure tenant DB only AFTER tenancy is initialized
        Event::listen(TenancyInitialized::class, function (TenancyInitialized $event) {
            Log::info('TenancyInitialized event fired');
            $tenant = $event->tenancy->tenant;

            // Pega o domínio relativo à URL da requisição
            $domain = $tenant->domains()->where('domain', request()->getHost())->first();

            Log::info('AppServiceProvider Domain', ['domain' => $domain?->domain]);

            $codigoInstituicao = $domain?->getAttribute('instituicao') ?? $tenant->getAttribute('db_instit');

            $dbName = $tenant->getAttribute('db_name') ?? $tenant->getAttribute('db_database') ?? env('DB_DATABASE');
            $dbHost = $tenant->getAttribute('db_host') ?? env('DB_HOST', '127.0.0.1');
            $dbPort = $tenant->getAttribute('db_port') ?? env('DB_PORT', '5432');

            define('CODIGOINSTITUICAO', $codigoInstituicao);

            Inertia::share([
                'instituicao' => CODIGOINSTITUICAO,
            ]);

            $dbUser = env('DB_USERNAME', 'dbportal');
            $dbPass = env('DB_PASSWORD', '');
            Log::info('[tenancy] Details resolved', [
                'tenant_id' => $tenant?->getTenantKey(),
                'db_name' => $dbName,
                'db_host' => $dbHost,
                'db_port' => $dbPort
            ]);

            Log::info('[tenancy] TenancyInitialized: switching to tenant connection', [
                'tenant_id' => $tenant?->getTenantKey(),
                'db' => $dbName,
                'host' => $dbHost,
            ]);

            try {
                DB::purge('tenant');
            } catch (\Throwable $e) {
                // Conexão pode não existir ainda
            }

            Config::set('database.connections.tenant', [
                'driver' => 'pgsql',
                'host' => $dbHost,
                'port' => $dbPort,
                'database' => $dbName,
                'username' => $dbUser,
                'password' => $dbPass,
                'charset' => env('DB_CHARSET', 'utf8'),
                'prefix' => '',
                'prefix_indexes' => true,
                'search_path' => 'public',
                'sslmode' => 'prefer',
            ]);

            Config::set('database.default', 'tenant');

            DB::purge('tenant');
            DB::reconnect('tenant');
            DB::setDefaultConnection('tenant');
            DB::connection('tenant')->statement('SET search_path TO public, pessoal, protocolo, cadastro, configuracoes ');
        });


        Event::listen(TenancyEnded::class, function () {
            $central = config('database.central_default') ?: 'pgsql';
            Config::set('database.default', $central);
            DB::setDefaultConnection($central);
            try {
                DB::purge('tenant');
            } catch (\Throwable $e) {
                // Ignore disconnection errors
            }
        });


    }
}
