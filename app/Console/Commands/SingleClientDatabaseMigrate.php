<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class SingleClientDatabaseMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:migration {--db=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will Migrate the single database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $database_name = $this->option('db');
        $clients = Client::get();
        $database_name = 'royo_' . $database_name;
        $this->info("migrate database start: {$database_name}!");
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
        $db = DB::select($query, [$database_name]);
        if ($db) {
            $default = [
                'prefix' => '',
                'engine' => null,
                'strict' => false,
                'charset' => 'utf8mb4',
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT'),
                'prefix_indexes' => true,
                'database' => $database_name,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'collation' => 'utf8mb4_unicode_ci',
                'driver' => env('DB_CONNECTION', 'mysql'),
            ];
            Config::set("database.connections.$database_name", $default);
            Artisan::call('migrate', ['--database' => $database_name]);
            DB::disconnect($database_name);
            $this->info("migrate database end: {$database_name}!");
        }else{
            DB::disconnect($database_name);
            $this->info("migrate database end: {$database_name}!");
        } 
    }
}
