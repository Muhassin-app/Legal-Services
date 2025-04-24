<?php

namespace App\Http\Traits;
use DB;
use Illuminate\Support\Collection;
use App\Models\{BillingPlan, BillingPlanType, BillingTimeframe, BillingPricing, Client};


trait BillingPlanManager{

  
  public  function getClientList($type='')
  {
    if($type==''):
        $clientlist = Client::orderBy('name', 'asc')->where('status', 1)->where('is_deleted', 0)->get()->pluck('name','id');
    else:
        $clientlist = Client::orderBy('name', 'asc')->where('status', 1)->where('is_deleted', 0)->where('client_type', $type)->get()->pluck('name','id');
    endif;
    return $clientlist;
  }

  public function getBillingPlanTypeList()
  {
    $billingplantypelist = BillingPlanType::orderBy('title', 'desc')->get()->pluck('title','id');
    return $billingplantypelist;
  }

  public function gettValidityTypeList()
  {
    $days_month_year = ['day'=>'day', 'month'=>'month', 'year'=>'year'];
    return $days_month_year;
  }

  public static  function gettClientTypeList()
  {
    $client_type = ['1'=>'Live', '2'=>'Demo'];
    return $client_type;
  }

  public function gettPaymentStatusList()
  {
    $days_month_year = ['0'=>'Unpaid', '1'=>'Paid'];
    return $days_month_year;
  }

  public function getPaymentMethodList()
  {
    $payment_method = ['Stripe'=>'Stripe', 'Off The Platform'=>'Off The Platform', 'Free'=>'Free'];
    return $payment_method;
  }

  public function getBillingPlanList($plantype='')
  {
    if($plantype==''):
      $billingplanlist = BillingPlan::select('id', DB::Raw("CONCAT(title, ' (', (select title from billing_plan_types where billing_plan_types.id=billing_plans.plan_type), ')') as title"))->orderBy('title', 'desc')->get()->pluck('title','id');
    else:
      $billingplanlist = BillingPlan::where('plan_type', $plantype)->orderBy('title', 'desc')->get()->pluck('title','id');
    endif;
    return $billingplanlist;
  }

  public function getBillingTimeframeList()
  {
    $billingtimeframelist = BillingTimeframe::orderBy('title', 'desc')->get()->pluck('title','id');
    return $billingtimeframelist;
  }

  public function getBillingPricingListjson($customised = '')
  {
    $pricingarray = array();
    $billingpricings = BillingPricing::with('billingplan:id,title,status','billingtimeframe:id,title,status')->select('id', 'price', 'old_price', 'billing_plan_id', 'billing_timeframe_id')->whereHas('billingplan', function($q){
        $q->where('status','1');
    })->whereHas('billingtimeframe', function($q) use ($customised){
        if($customised!=''):
          $q->where('is_custom', $customised);
        endif;
        $q->where('status','1');
    })->where('status',1)->get();
    foreach($billingpricings as $billingpricing):
       $pricingarray[$billingpricing->billingplan->id][] = array('title'=>$billingpricing->billingtimeframe->title, 'id'=>$billingpricing->id, 'price'=>$billingpricing->price, 'old_price'=>$billingpricing->old_price);
    endforeach;
    return json_encode($pricingarray);
  }

}