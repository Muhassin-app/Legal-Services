<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB, Log;
class LoyaltyCard extends Model
{
    use HasFactory;

	protected $fillable = ['name','description','image','minimum_points','per_order_minimum_amount','per_order_points','per_purchase_minimum_amount','amount_per_loyalty_point','redeem_points_per_primary_currency','status','loyalty_check'];

    public static function getLoyaltyPoint($minimum_points, $payable_amount){
    	$per_order_points = 0;
        $loyalty_card_id = 0;
        $balanced_points = 0;
        $order_loyalty_points_earned_detail = Order::where('user_id', auth()->user()->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
        if ($order_loyalty_points_earned_detail) {
            $balanced_points = ($order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used);
        }
        $result = LoyaltyCard::where('minimum_points','<=', $balanced_points)->where('status', '=', '0')->orderBy('minimum_points', 'DESC')->first();
        if(empty($result)){
            $result = LoyaltyCard::where('amount_per_loyalty_point','<=', $payable_amount)->where('status', '=', '0')->orderBy('minimum_points', 'ASC')->first();
        }

    	if(!empty($result)){
            if($result->amount_per_loyalty_point > 0){
                $amount_per_loyalty_point = ($payable_amount / $result->amount_per_loyalty_point);
    		$per_order_points = $result->per_order_points + $amount_per_loyalty_point;
            $loyalty_card_id = $result->id;
            }
    	}
        $data = ['per_order_points' => $per_order_points, 'loyalty_card_id' => $loyalty_card_id];
    	return $data;
    }


    public static function canLoyaltyPointUse($payable_amount){
        $balanced_points = 0;
        $order_loyalty_balance = Order::where('user_id', auth()->user()->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();
        if($order_loyalty_balance){
    	    $balanced_points = ($order_loyalty_balance->sum_of_loyalty_points_earned - $order_loyalty_balance->sum_of_loyalty_points_used);
        }
        $loyalty_card_id = 0;
    	$result = LoyaltyCard::where('minimum_points','>=', $balanced_points)->where('status', '=', '0')->orderBy('minimum_points', 'DESC')->first();
    	if($result){
            if($result->amount_per_loyalty_point > 0){
                $amount_per_loyalty_point = ($payable_amount / $result->amount_per_loyalty_point);
    		    $per_order_points = $result->per_order_points+$amount_per_loyalty_point;
                $loyalty_card_id = $result->id;
            }
    	}
        $data = ['per_order_points' => $per_order_points, 'loyalty_card_id' => $loyalty_card_id];
    	return $data;
    }

	public function getImageAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');
      return $values;
    }

    public static function getLoyaltyName($minimum_points){

    	$result = LoyaltyCard::where('minimum_points','<=', $minimum_points)->where('status', '=', '0')->orderBy('id', 'DESC')->first();
        if($result){
            return $result->name;
        }
    	return "NO";
    }
}
