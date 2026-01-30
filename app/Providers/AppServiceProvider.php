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

        // Configure tenant DB only AFTER tenancy is initialized
        Event::listen(TenancyInitialized::class, function (TenancyInitialized $event) {
            Log::info('TenancyInitialized event fired');
            $tenant = $event->tenancy->tenant;

            $dbName = $tenant->getAttribute('db_name') ?? $tenant->getAttribute('db_database') ?? env('DB_DATABASE');
            $dbHost = $tenant->getAttribute('db_host') ?? env('DB_HOST', '127.0.0.1');
            $dbPort = $tenant->getAttribute('db_port') ?? env('DB_PORT', '5432');

            define('CODIGOINSTITUICAO', $tenant->getAttribute('db_instit'));

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
