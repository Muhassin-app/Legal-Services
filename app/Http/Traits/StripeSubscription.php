<?php
namespace App\Http\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException; 
use Log;
use App\Models\{ClientCurrency};
trait StripeSubscription{
    
    /**
     * createSubscriptionOnStripe
     *
     * @param  mixed $request.* => name,amount,currencyinterval
     * @return void
     */
    public function createSubscriptionOnStripe($request) 
    {
    
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : 'USD';
        $interval_count['interval_count'] = 0;
        $plan_arr = [
            'product' => ['name' => $request->title],
            'amount'  =>  $request->price * 100, // Amount in cents
            'currency' => $currency,
            
        ];
        //$interval = config('constants.subIntervel.'. $request->frequency);
       if($request->frequency == 'weekly'){
            $interval = 'week';
            $plan_arr['interval'] = $interval;
       } elseif($request->frequency == 'monthly'){
            $interval = 'month';
            $plan_arr['interval'] = $interval;
       } elseif($request->frequency == 'yearly'){
            $interval = 'year';
            $plan_arr['interval'] = $interval;
       } elseif($request->frequency == 'quarter'){
            $interval = 'month';
            $plan_arr['interval'] = $interval;
            $plan_arr['interval_count'] = 3;
       }
        \Stripe\Stripe::setApiKey($request->apiKey);
        if(isset( $request->strip_plan_id) &&  $request->strip_plan_id !=''){ /// update plan on strip
          //$plan =  $this->updatePlanOnStripe($request,$plan_arr);
        }else{  // create plan for strip
            $plan = $this->createPlanOnStripe($plan_arr);
        }
       
        return $plan;
    }
    
    public function createPlanOnStripe($plan_arr) 
    {
        $plan = \Stripe\Plan::create($plan_arr);
        
        return $plan;
    }

    public function updatePlanOnStripe($request,$plan_arr) 
    {
   

        $plan = \Stripe\Plan::retrieve($request->strip_plan_id);
        //pr($plan);
        $plan_array['nickname'] = $plan_arr['product']['name'];
        $plan_array['amount'] = $plan_arr['amount'];
        $plan->save();
    }

    public function buyStripeSubscription($request) 
    {

    }

}
