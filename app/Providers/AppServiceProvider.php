<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
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
            $dbUser = $tenant->getAttribute('db_username') ?? $tenant->getAttribute('db_user') ?? env('DB_USERNAME', 'root');
            $dbPass = $tenant->getAttribute('db_password') ?? $tenant->getAttribute('db_pass') ?? env('DB_PASSWORD', '');

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


        });

        // Make tenant the default connection for the lifetime of this request
        Config::set('database.default', 'tenant');

        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');

        // Ensure search_path for Postgres schemas on tenant connection
        try {
            // Include all common schemas used by the app, including configuracoes where DbConfig lives
            DB::connection('tenant')->statement('SET search_path TO public, pessoal, protocolo, cadastro, configuracoes ');

            // Force clear any cached default connection in PDO
            DB::purge('pgsql');
            // Garante que o usuário autenticado seja recarregado do banco do tenant
            if (auth()->check()) {
                auth()->forgetUser();
            }
        } catch (\Throwable $e) {

        }

        // Optionally reset to central connection when tenancy ends (for console, jobs, etc.)
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
