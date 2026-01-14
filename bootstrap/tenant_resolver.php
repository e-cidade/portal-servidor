<?php

use Illuminate\Support\Str;

const CACHE_TENANTS_PHP = __DIR__  . '/cache/tenants.php';

function resolveTenantDatabase()
{


    if (php_sapi_name() === 'cli') {
        return env('DB_DATABASE', 'forge');
    }

    $host   = str_replace('-', '', $_SERVER['HTTP_HOST']);
    $cliente =  explode( '.', $host);
    $tenants = getTenants();
    if(count($cliente) > 1){
        foreach ($tenants as $tenant) {
            if ($tenant[0] === $cliente[1]) {
                return  $tenant[1];
            }
        }

    }
    return env('DB_DATABASE', 'forge');
}

function getTenants()
{
    $tenantsLocation = __DIR__ . '/../tenants';
    if (!file_exists($tenantsLocation)) {
        return [];
    }

    $lines = file($tenantsLocation, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $databaseNames = [];

    foreach ($lines as $line) {
        // Split the line by comma and take the first part (tenant name)
        $parts = explode(',', $line);
        $databaseNames[] = $parts;
    }

    return $databaseNames;
}

function getTenantDatabaseIP(string $tenant)
{
    $tenantsLocation = __DIR__ . '/../tenants';
    if (!file_exists($tenantsLocation)) {
        return env('DB_HOST', '127.0.0.1');
    }

    $lines = file($tenantsLocation, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $parts = explode(',', $line);
        if (count($parts) >= 3 && $parts[1] === $tenant) {
            return $parts[2];
        }
    }
    return env('DB_HOST', '127.0.0.1');
}

function createDataBasesCacheFile(array $dataBases) {

    $fileContent = '<?php' . PHP_EOL;
    $fileContent .= PHP_EOL;
    $fileContent .= 'return [' . PHP_EOL;

    foreach ($dataBases as $name) {
        $fileContent .= "    '$name'," . PHP_EOL;
    }

    $fileContent .= '];' . PHP_EOL;

    file_put_contents(CACHE_TENANTS_PHP, $fileContent);
}
