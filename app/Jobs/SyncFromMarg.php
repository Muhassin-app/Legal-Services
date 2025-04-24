<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Config;
use Log;
use App\Http\Traits\MargTrait;

class SyncFromMarg implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MargTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $data;

    public function __construct($data)
    {
           $this->data = $data;
            // foreach($data as $key => $product){
            //    $detail = $this->addProduct($product);
            // }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
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
                foreach($data as $key => $product){
                    Log::info($product->name);
                   $detail = $this->addProduct($product);
                }
                DB::disconnect($database_name);
            }else{
                DB::disconnect($database_name);
            }
        }
    }
   
}
