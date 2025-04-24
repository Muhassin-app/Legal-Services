<?php

namespace App\Console\Commands;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\UserVendor;
use App\Models\UserDevice;
use App\Models\User;
use App\Models\NotificationTemplate;
use GuzzleHttp\Client;
use App\Models\Client as CP;
use App\Models\{ ClientPreference, ClientPreferenceAdditional, MargProduct, OrderLongTermServiceSchedule,UserAddress,Product,Vendor,OrderVendor, VendorMargConfig};
use Log;
use Carbon\Carbon;
use App\Models\Order;
use App\Http\Traits\MargTrait;
use App\Libraries\DecryptLogic;



class MargApiProductUpdateCron extends Command
{
    use MargTrait;

    protected $DecryptLogic;

    public function __construct(DecryptLogic $DecryptLogic)
    {
        $this->DecryptLogic = $DecryptLogic;
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:sycn_product_from_marg_api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sycn product quantity from marg api';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * Sycn product quantity and add new product code from marg api
         */
        
         try {

            $clients = CP::where('status', 1)->get();
            foreach ($clients as $key => $client) {
               
                //Connect client connection
                $database_name  = 'royo_' . $client->database_name;
                $header         = $client->database_name;


                $result = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$database_name]);
                if (empty($result)) {
                    continue;
                }


                $default = [
                    'driver' => env('DB_CONNECTION', 'mysql'),
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'database' => $database_name,
                    'username' => $client->database_username,
                    'password' => $client->database_password,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => false,
                    'engine' => null
                ];
                Config::set("database.connections.$database_name", $default);
                DB::setDefaultConnection($database_name);

                $preference = ClientPreference::first();

                $marg_vendor_products = MargProduct::groupBy('vendor_id')->get();

                foreach ($marg_vendor_products as $key => $marg_vendor_product) {

                    // $hub_key = @getAdditionalPreference(['marg_access_token','is_marg_enable','marg_decrypt_key', 'marg_company_code','marg_date_time','marg_company_url']);
                    $hub_key = VendorMargConfig::where('vendor_id',$marg_vendor_product->vendor_id)->first();

                    if(isset($hub_key) && $hub_key['is_marg_enable'] == 1){
                        $decryptionKey  = $hub_key['marg_decrypt_key'];
                        $MargID  = $hub_key['marg_access_token'];
                        $CompanyCode  = $hub_key['marg_company_code'];
                        $url  = $hub_key['marg_company_url'];
                        $margDateTime = $hub_key['marg_date_time']??'';

                        $MargMST2017 = $url."/api/eOnlineData/MargMST2017";
                        $reqData = ["CompanyCode" => $CompanyCode,"MargID" => $MargID,"Datetime" => $margDateTime, "index" => 0];
                    
                    }else{
                        continue;
                    }

                    // $marg_order =  Order::whereHas('ordervendor',function($q)use($marg_vendor_product){
                    //     $q->where('vendor_id',$marg_vendor_product->vendor_id);
                    // })->where('marg_status', '=',null)->
                    // where('marg_max_attempt', '>',2)->first();
            
                    // if(!empty($marg_order))
                    // { 
                    //     continue;
                    // }

                    $hub_key->update([
                        'marg_date_time' => $hub_key['marg_date_time']??''
                    ]);
                    // Get the encrypted data from the request
                    $encryptedData = $this->getData($MargMST2017, $reqData);
                    // Decrypt the data using the DLL wrapper
                    $decryptedData = $this->DecryptLogic->Decrypt($encryptedData, $decryptionKey);
                    $collectionData = collect( json_decode($decryptedData));

                
                    //    dd($collectionData["Details"]->pro_N);
                    if(!empty($collectionData["Details"]->pro_N)){

                        foreach($collectionData["Details"]->pro_N as $key => $product){
                            $detail = $this->addProduct($product,$marg_vendor_product->vendor_id);
                        }
                    }
                }
                \DB::disconnect($database_name);
            }
        }catch (Exception $ex) {
            return $ex->getMessage();
        }
        return 0;

    }
}
