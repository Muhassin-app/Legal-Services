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
class SyncToDispatcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $order_panel_id;
    protected $categories;
    protected $RoyoDatabaseName;
    protected $dispatcher_service_key_url;
    protected $dispatcher_service_code;
    public function __construct($order_panel_id,$RoyoDatabaseName, $categories,$dispatcher_service_key_url,$dispatcher_service_code)
    {
     
        $this->order_panel_id = $order_panel_id;
        $this->RoyoDatabaseName = $RoyoDatabaseName;
        $this->categories = $categories;
        $this->dispatcher_service_key_url = $dispatcher_service_key_url;
        $this->dispatcher_service_code    = $dispatcher_service_code;
       
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = $this->dispatcher_service_key_url.'/api/sync-category-product';
        $postData = ['databaseName'=> $this->RoyoDatabaseName,'data' => $this->categories, 'order_panel_id' => $this->order_panel_id,'dispatcher_service_key_url'=>$this->dispatcher_service_key_url,'dispatcher_service_code'=>$this->dispatcher_service_code]; 
    
        $headers = [
            'shortcode' => $this->dispatcher_service_code
        ];

        $response = Http::withHeaders($headers)->post($url, $postData);
      
        $statusCode = $response->getStatusCode();
        if($statusCode == 200) {
            return true;
        }
        return true;
        
    }
   
}
