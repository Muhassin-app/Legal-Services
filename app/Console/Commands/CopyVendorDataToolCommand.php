<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Config;
use DB;
use App\Models\CopyTool;
use App\Http\Controllers\Client\ToolsController;
use App\Models\{Vendor, Product, AddonSet, Category, ProductVariant, CartProduct, UserWishlist, TaxCategory, VendorCategory, VendorSlot, VendorSlotDate, VendorDineinCategory, VendorDineinTable};
class CopyVendorDataToolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:catalog';
    protected   $toolController;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct( Vendor $vendor, Product $product, Client $client, AddonSet $addonSet, Category $category, VendorCategory $vendorCategory, VendorSlot $vendorSlot, VendorSlotDate $vendorSlotDate, VendorDineinCategory $vendorDineinCategory, VendorDineinTable $vendorDineinTable)
    {
        parent::__construct();
        $this->toolController  = new ToolsController($vendor, $product, $client, $addonSet, $category, $vendorCategory, $vendorSlot, $vendorSlotDate, $vendorDineinCategory, $vendorDineinTable);
        
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = Client::select('database_name', 'sub_domain')->first();
        $database_name = 'royo_' . $client->database_name;
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
        $db = DB::select($query, [
            $database_name
        ]);
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
                'driver' => env('DB_CONNECTION', 'mysql')
            ];
            Config::set("database.connections.$database_name", $default);
            DB::setDefaultConnection($database_name);
        }        
        $copyData = CopyTool::first();
        if(!empty($copyData)){
            $flag = $this->toolController->store($copyData->copy_to, $copyData->copy_from);
            if($flag){
                $copyData->delete();
            }
        }
    }
}
