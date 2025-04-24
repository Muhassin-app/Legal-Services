<?php

namespace App\Console\Commands;

use Log;
use Config;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\{Client, ClientPreference, User, Vendor, ServiceArea};

class ServiceAreaActiveForVendorSlot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service_area:active_for_vendor_slot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto reject order after a fixed interval of time';

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
        $clients = Client::select('database_name', 'sub_domain')->get();
        foreach ($clients as $client) {
            $database_name = 'royo_' . $client->database_name;
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

                $now = Carbon::now()->toDateTimeString();
                $preferences = ClientPreference::first();

                if ($preferences) {
                    if ($preferences->is_hyperlocal == 1) {
                        if ($preferences->slots_with_service_area == 1) {
                            
                            $vendors = Vendor::with(['slot.day', 'slot.geos.serviceArea', 'slotDate.geos.serviceArea'])->select('id', 'name', 'banner', 'address', 'order_pre_time', 'order_min_amount', 'logo', 'slug', 'latitude', 'longitude', 'show_slot');

                            $vendors = $vendors->where(function($query) {
                                $query->whereHas('slot.geos')
                                ->orWhereHas('slotDate.geos');
                            });

                            $vendors = $vendors->where('show_slot', 0)->where('cron_for_service_area', 1)->where('status', 1)->get();

                            foreach($vendors as $vendor){
                                $active_service_areas = array();
                                if($vendor->slotDate->isNotEmpty()){
                                    foreach($vendor->slotDate as $slotDate){
                                        $service_area_ids = $slotDate->geos->pluck('service_area_id')->toArray();
                                        array_push($active_service_areas , ...$service_area_ids);
                                    }
                                }
                                if($vendor->slot->isNotEmpty()){
                                    foreach($vendor->slot as $slot){
                                        $service_area_ids = $slot->geos->pluck('service_area_id')->toArray();
                                        array_push($active_service_areas , ...$service_area_ids);
                                    }
                                }
                                $active_service_areas = array_unique($active_service_areas);
                                ServiceArea::whereIn('id', $active_service_areas)->where('vendor_id', $vendor->id)->where('area_type', 1)->update(['is_active_for_vendor_slot' => 1]);
                                ServiceArea::whereNotIn('id', $active_service_areas)->where('vendor_id', $vendor->id)->where('area_type', 1)->update(['is_active_for_vendor_slot' => 0]);
                            }
                        }
                    }
                }
                
                 
                DB::disconnect($database_name);
            } else {
                DB::disconnect($database_name);
            }
        }
    }
}
