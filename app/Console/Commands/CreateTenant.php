<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    protected $signature = 'tenants:create {id} {db_name} {--host=} {--base-domain=}';

    protected $description = 'Create a tenant with a database name and attach a domain like portalservidor.{id}.contassconsultoria.com.br';

    public function handle(): int
    {
        $id = $this->argument('id');
        $dbName = $this->argument('db_name');
        $host = $this->option('host') ?: env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1'));
        $baseDomain = $this->option('base-domain')
            ?: env('TENANCY_BASE_DOMAIN')
            ?: env('APP_DOMAIN')
            ?: parse_url(env('APP_URL', ''), PHP_URL_HOST)
            ?: 'contassconsultoria.com.br';

        $domain = sprintf('portalservidor.%s.%s', $id, $baseDomain);

        // Create tenant with database info stored in data column
        $tenant = Tenant::create([
            'id' => $id,
            'data' => [
                'db_name' => $dbName,
                'db_host' => $host,
            ],
        ]);

        $tenant->domains()->create(['domain' => $domain]);

        $this->info("Tenant '{$id}' created with domain '{$domain}' and database '{$dbName}' (host: {$host}).");
        $this->info('Remember to create the database and run tenants:migrate if needed.');

        return self::SUCCESS;
    }
}
