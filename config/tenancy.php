<?php

use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Middleware;

return [
    // List of central (non-tenant) domains. Requests to these domains won't initialize tenancy.
    'central_domains' => array_filter(array_map('trim', explode(',', env('CENTRAL_DOMAINS', 'contassconsultoria.com.br,localhost,127.0.0.1')))),

    // Tenancy bootstrappers configure things like the database connection, cache, filesystems, etc.
    'bootstrappers' => [
        // DatabaseTenancyBootstrapper::class,
        // You can add other bootstrappers here (CacheTenancyBootstrapper, FilesystemTenancyBootstrapper, etc.)
    ],

    // Use our custom Tenant model that includes HasDomains/HasDatabase
    'tenant_model' => App\Models\Tenant::class,

    // Tenant database connection name used by DatabaseTenancyBootstrapper
    'database' => [
        'default' => env('TENANCY_DEFAULT_CONNECTION', 'tenant'),
        'based_on' => null,
        'template_tenant_connection' => null,
        'managers' => [
            'db' => Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
        ],
        // How tenant database names are generated if you rely on HasDatabase::makeDatabaseName().
        // We'll leave it default and store explicit database names in each tenant's data when creating tenants.
    ],

    // Identification middleware (we'll use InitializeTenancyByDomain in routes)
    'middleware' => [
        'initialize_tenancy_by_domain' => Middleware\InitializeTenancyByDomain::class,
        'prevent_access_from_central_domains' => Middleware\PreventAccessFromCentralDomains::class,
    ],

    'tenant_database_connection_name' => 'tenant', // Nome da conexão que o tenancy vai criar
    'template_connection' => 'pgsql', // Conexão que serve de base (geralmente 'mysql' ou 'pgsql')

];
