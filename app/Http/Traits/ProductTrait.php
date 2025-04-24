<?php
namespace App\Http\Traits;

use App\Models\{Product,ClientPreference};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use GuzzleHttp\Client as Guzzle;
use Log,DB;
trait ProductTrait{

    public function getProduct($product_id,$vendor_slug,$url_slug,$user='',$langId)
    {
      
       $with_array = [
            'product_availability',
            'variant' => function ($sel) {
                $sel->groupBy('product_id');
            },
            
            'variant.set' => function ($sel) {
                $sel->select('product_variant_id', 'variant_option_id');
            },
            'variant.media.pimage.image', 'vendor', 'media.image',
             'translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                $q->where('language_id', $langId);
            },
            'addOn' => function ($q1) use ($langId) {
                $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                $q1->where('set.status', 1)->where('ast.language_id', $langId);
            },
            'variantSet' => function ($z) use ($langId, $product_id) {
                $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title', 'vr.position');
                $z->where('vt.language_id', $langId);
                $z->where('product_variant_sets.product_id', $product_id);
                $z->where('vr.status', 1);
                $z->orderBy('vr.position');
            },
            'variantSet.option2' => function ($zx) use ($langId, $product_id) {
                $zx->where('vt.language_id', $langId)
                    ->where('product_variant_sets.product_id', $product_id);
            },
            'addOn.setoptions' => function ($q2) use ($langId) {
                $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                $q2->where('apt.language_id', $langId);
            },
            'category.categoryDetail.allParentsAccount','ServicePeriod', 'productVariantByRoles',
        ];

        if( checkTableExists('product_attributes') ) {
            $with_array[] = 'ProductAttribute';
            $with_array[] = 'ProductAttribute.attribute';
            $with_array[] = 'ProductAttribute.attributeOption';
            $with_array[] = 'ProductAttribute.attribute';
        }
        $product = Product::with($with_array);
            if($user){
                $product = $product->with('inwishlist', function ($query) use($user) {
                    $query->where('user_wishlists.user_id', $user->id);
                });
            }
            // $product = $product->with('related');

            // $product = $product->select('id', 'sku', 'inquiry_only', 'url_slug', 'weight', 'weight_unit', 'vendor_id', 'has_variant', 'has_inventory', 'averageRating','sell_when_out_of_stock','minimum_order_count','batch_count','additional_increments_min','minimum_duration_min','buffer_time_duration_min','minimum_duration','additional_increments','buffer_time_duration','tags','is_long_term_service','service_duration', 'returnable' , 'replaceable' , 'return_days', 'same_day_delivery', 'next_day_delivery','hyper_local_delivery','is_recurring_booking','calories' );
            $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
            if(@$getAdditionalPreference['is_rental_weekly_monthly_price']){
                $product = $product->with(['OrderProduct' => function($q) {
                    $q->select('end_date_time', 'product_id', 'start_date_time');
                    $q->whereDate('end_date_time', '>', now());
                }]);
            }
            $product = $product->select('id', 'sku', 'inquiry_only', 'url_slug', 'weight', 'weight_unit', 'vendor_id', 'has_variant', 'has_inventory', 'averageRating','sell_when_out_of_stock','minimum_order_count','batch_count','additional_increments_min','category_id','minimum_duration_min','buffer_time_duration_min','minimum_duration','additional_increments','buffer_time_duration','tags','is_long_term_service','service_duration', 'returnable' , 'replaceable' , 'return_days', 'same_day_delivery', 'next_day_delivery','hyper_local_delivery','is_recurring_booking', 'security_amount','captain_name','calories', 'captain_profile', 'captain_description');
          
            $product = $product->whereHas('vendor',function($q) use($vendor_slug){
                    $q->where('slug',$vendor_slug);
                })->where('url_slug', $url_slug)
                ->where('is_live', 1)
                ->firstOrFail();
           
        return $product;
    }

    public function getProductPriceFromDispatcher($date , $productVariantSku,$lat='',$long='',$slot='')
    {
        $returnResponse['data'] = array();
        $dispatch_domain_ondemand = $this->getDispatchOnDemandDomain();
        if ($dispatch_domain_ondemand && $dispatch_domain_ondemand != false ) {
            $DatabaseName = DB::connection()->getDatabaseName();
            $end_time   =  $start_time = date('H:i',strtotime($date));
            
            if($slot!=''){
                $sl = explode('-', $slot);
                $start_time =  @$sl[0] ?? $date;
                $end_time   = @$sl[1] ?? $date;
            }
            $latitude = ($lat && $lat!='') ? $lat : $dispatch_domain_ondemand->Default_Default_latitude;
            $longitude = ($long && $long!='') ? $long :  $dispatch_domain_ondemand->Default_longitude;
            $postdata =  [
                'product_variant_sku'  =>  $DatabaseName.'_'.$productVariantSku,
                'schedule_date' => $date,
                'start_time'    => $start_time ,
                'end_time'      => $end_time  ,
                'latitude'      => $latitude,
                'longitude'     => $longitude
            ];
            $client = new Guzzle([
                'headers' => [
                    'personaltoken' => $dispatch_domain_ondemand->dispacher_home_other_service_key,
                    'shortcode'     => $dispatch_domain_ondemand->dispacher_home_other_service_key_code,
                    'content-type'  => 'application/json'
                ]
            ]);

            $url = $dispatch_domain_ondemand->dispacher_home_other_service_key_url;
            $res = $client->post(
                $url . '/api/getProductPrice',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);
            if(isset( $response['data']))
            $returnResponse['data']  = $response['data'];
        }
        return $returnResponse;

    }
    public function getAgentProductPriceFromDispatcher( $productVariantSku,$agent_id) // for update order by freelancer
    {
        $returnResponse['data'] = array();
        $dispatch_domain_ondemand = $this->getDispatchOnDemandDomain();
        if ($dispatch_domain_ondemand && $dispatch_domain_ondemand != false ) {
            $DatabaseName = DB::connection()->getDatabaseName();
         
            $postdata =  [
                'product_variant_sku'  =>  $DatabaseName.'_'.$productVariantSku,
                'agent_id' => $agent_id,
            ];
            $client = new Guzzle([
                'headers' => [
                    'personaltoken' => $dispatch_domain_ondemand->dispacher_home_other_service_key,
                    'shortcode'     => $dispatch_domain_ondemand->dispacher_home_other_service_key_code,
                    'content-type'  => 'application/json'
                ]
            ]);

            $url = $dispatch_domain_ondemand->dispacher_home_other_service_key_url;
            $res = $client->post(
                $url . '/api/getProductPriceByAgent',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);
            if(isset( $response['data']))
            $returnResponse['data']  = $response['data'];
        }
        return $returnResponse;

    }
    public function getGerenalSlotFromDispatcher($date)
    {
        $returnResponse = array();
        $dispatch_domain_ondemand = $this->getDispatchOnDemandDomain();
        if ($dispatch_domain_ondemand && $dispatch_domain_ondemand != false ) {
          
            $postdata =  [
                'date'  => $date
            ];
            $client = new Guzzle([
                'headers' => [
                    'personaltoken' => $dispatch_domain_ondemand->dispacher_home_other_service_key,
                    'shortcode'     => $dispatch_domain_ondemand->dispacher_home_other_service_key_code,
                    'content-type'  => 'application/json'
                ]
            ]);

            $url = $dispatch_domain_ondemand->dispacher_home_other_service_key_url;
            $res = $client->post(
                $url . '/api/get/general_slot',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);
            if(isset( $response['data']))
            $returnResponse  = $response['data'];
        }
        return $returnResponse;

    }

    
     public function getDispatchOnDemandDomain()
     {
         $preference = ClientPreference::first();
         if ($preference->need_dispacher_home_other_service == 1 && !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_code) && !empty($preference->dispacher_home_other_service_key_url)) {
             return $preference;
         } else {
             return false;
         }
     }
}
