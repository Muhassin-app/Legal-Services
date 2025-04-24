<?php

namespace App\Console\Commands;
use DB;
use Config;
use Exception;
use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\MargController;
use App\Libraries\DecryptLogic;

class FetchMargData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected   $signature = 'fetch:margdata';
    protected   $MargController;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for insert data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DecryptLogic $DecryptLogic){
        parent::__construct();

        $this->MargController = new MargController($DecryptLogic);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(){
        $clients = Client::get();
        foreach ($clients as $key => $client) {
            $database_name = 'royo_' . $client->database_name;
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
                DB::setDefaultConnection($database_name);
                // dump(DB::connection()->getDatabaseName());
                $flag = $this->MargController->syncmarg();
                DB::disconnect($database_name);
            }else{
                DB::disconnect($database_name);
            }
        }
    }
}
