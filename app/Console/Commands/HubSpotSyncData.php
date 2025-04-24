<?php

namespace App\Console\Commands;

use App\Http\Traits\ThirdPartyTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Client as ClientData;
use App\Models\ClientPreferenceAdditional;
use App\Http\Traits\ValidatorTrait;

use Log;
use Config;
use Carbon\Carbon;

class HubSpotSyncData extends Command
{
    use ThirdPartyTrait;
    use ValidatorTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:hubspot_sync_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto sync Hubspot customer';

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
      
        $intervalTime = Carbon::now();
        $clients = ClientData::all();
        foreach ($clients as $client) {
            $database_name = 'royo_' . $client->database_name;
            $this->info("select database start: {$database_name}!");
            $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
            $db = DB::select($query, [$database_name]);
           
            if ($db) {
                $default = [
                    'prefix' => '',
                    'engine' => null,
                    'strict' => false,
                    'charset' => 'utf8mb4',
                    'host' => '192.168.99.124',
                    'port' => '3306',
                    'prefix_indexes' => true,
                    'database' => $database_name,
                    'username' => $client->database_username,
                    'password' => $client->database_password,
                    'collation' => 'utf8mb4_unicode_ci',
                    'driver' => env('DB_CONNECTION', 'mysql'),
                ];
                Config::set("database.connections.$database_name", $default);
                // DB::setDefaultConnection($database_name);
               
                //$client_preferences = ClientPreference::on($database_name)->first();
                $arr = ['hubspot_access_token','is_hubspot_enable','hubspot_last_update'];
                $ClientPreference = ClientPreferenceAdditional::on($database_name)->getQuery();
                $ClientData = ClientData::on($database_name);
                $User = User::on($database_name)->getQuery();
                $ClientPreferenceAdditional =clone $ClientPreference;

                $client_preferences = $ClientPreference->whereIn('key_name',$arr)->where(['client_code' => $client->code])->get();
                $hubspot_access_token = $client_preferences->where('key_name','hubspot_access_token')->first();
                $is_hubspot_enable = $client_preferences->where('key_name','is_hubspot_enable')->first();
                $hubspot_last_update = $client_preferences->where('key_name','hubspot_last_update')->first();
                
                $post_data = [
                    'hubspot_last_update' =>@$hubspot_last_update->key_value,
                    'is_hubspot_enable' =>@$is_hubspot_enable->key_value,
                    'hub_key' =>@$hubspot_access_token->key_value,
                    'db_name' => $database_name,
                    'ClientPreferenceAdditional'=>$ClientPreferenceAdditional,
                    'ClientData'=>$ClientData,
                    'User'=> $User
                ];
             
                if($post_data['is_hubspot_enable'] == '1' && $post_data['hub_key'] != '') {
                    $this->hubSpotSync($post_data);
                }
                DB::disconnect($database_name);
            } else {
                DB::disconnect($database_name);
            }
        }
    }
}
