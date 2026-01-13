<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {--fresh} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Roda migrations para todos os tenants usando conexões dinâmicas.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function getTanants()
    {
        $path = base_path('tenants' ); // ou 'tenants.txt' ou 'storage/app/tenants' se estiver em storage
        $tenants = [];

        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                [$dns, $dbname, $dbhost] = explode(',', trim($line));
                $tenant = new \stdClass();
                $tenant->db_name = $dbname;
                $tenant->db_host = $dbhost;
                $tenants[] = $tenant;
            }
        }

        return $tenants;
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants = $this->getTanants();
        foreach ($tenants as $tenant) {

            $connectionName = 'tenant_' . $tenant->db_name;

            Config::set("database.connections.{$connectionName}", [
                'driver' => 'pgsql',
                'host' => $tenant->db_host,
                'database' => $tenant->db_name,
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8',
                'collation' => 'pt_BR',
                'prefix' => '',
                'strict' => true,
                'sslmode' => 'prefer',
            ]);

            DB::purge($connectionName);
            DB::reconnect($connectionName);

            $this->info("Migrando banco de dados para: {$tenant->db_name}");

            $params = [
                '--database' => $connectionName,
                '--force' => true,
            ];

            if ($this->option('fresh')) {
                Artisan::call('migrate:fresh', $params);
            } else {
                Artisan::call('migrate', $params);
            }

            $this->line(Artisan::output());

            if ($this->option('seed')) {
                Artisan::call('db:seed', [
                    '--database' => $connectionName,
                    '--force' => true,
                ]);
                $this->line(Artisan::output());
            }
        }

        $this->info("Migrations concluídas para todos os tenants.");
    }
}
