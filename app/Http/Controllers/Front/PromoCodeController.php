<?php

namespace App\Http\Controllers\Front;
use DB;
use Session;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Promocode;
use App\Models\CartCoupon;
use App\Models\OrderVendor;
use Illuminate\Http\Request;
use App\Models\{AddonOption, CartProduct, ClientCurrency, PromoCodeDetail};
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class PromoCodeController extends Controller{
    use ApiResponser;
    protected $user;


    public function postProductPromoCodeCheck(Request $request){
        try {
            $user = Auth::user();
            $langId = Session::has('customerLanguage') ? Session::get('customerLanguage') : 1;
            $promo_codes = new \Illuminate\Database\Eloquent\Collection;
            $product_id = $request->product_id;
            $firstOrderCheck = 0;
            $is_from_cart = $request->is_cart ? $request->is_cart :0;
            $now = Carbon::now()->toDateTimeString();
            $now = convertDateTimeInClientTimeZone($now);
            $promo_code_details = PromoCodeDetail::where('refrence_id', $product_id)->pluck('promocode_id');
            $result1 = Promocode::whereDate('expiry_date', '>=', $now)->where('restriction_on', 0)->where(function ($query) use ($promo_code_details,$firstOrderCheck) {
                $query->where(function ($query2) use ($promo_code_details) {
                    $query2->where('restriction_type', 1);
                    if (!empty($promo_code_details->toArray())) {
                        $query2->whereNotIn('id', $promo_code_details->toArray());
                    }
                });

                $query->orWhere(function ($query1) use ($promo_code_details) {
                    $query1->where('restriction_type', 0);
                    if (!empty($promo_code_details->toArray())) {
                        $query1->whereIn('id', $promo_code_details->toArray());
                    } else {
                        $query1->where('id', 0);
                    }
                });
            });
            // if($firstOrderCheck){
            //     $result1->where('first_order_only', 0);
            // }
            // if($is_from_cart != 1){
            //     $result1->where(['promo_visibility' => 'public']);
            // }
            $result1 = $result1->where('is_deleted', 0)->first();
            if(isset($result1) && $result1->id){
                return $this->successResponse($result1, '', 200);
            }
            return $this->errorResponse('', 400);


        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function postPromoCodeList(Request $request){
        try {
            $user = Auth::user();
            $langId = Session::has('customerLanguage') ? Session::get('customerLanguage') : 1;
            $promo_codes = new \Illuminate\Database\Eloquent\Collection;
            $vendor_id = $request->vendor_id;
            $firstOrderCheck = 0;
            $is_from_cart = $request->is_cart ? $request->is_cart :0;

            $total_minimum_spend = $request->amount;
            $validator = $this->validatePromoCodeList($request);
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            if(!$vendor){
                return response()->json(['error' => 'Invalid vendor id.'], 404);
            }
            if( Auth::user()){
                $userOrder = auth()->user()->orders->first();
                if($userOrder){
                    $firstOrderCheck = 1;
                }
            }
            $curId = Session::get('customerCurrency') ;
            $customerCurrency = ClientCurrency::where('currency_id', $curId)->first();

            $doller_compare = $customerCurrency ?  $customerCurrency->doller_compare : 1 ;
            // $order_vendor_coupon_list = OrderVendor::whereNotNull('coupon_id')->where('user_id', $user->id)->get([DB::raw('coupon_id'),  DB::raw('sum(coupon_id) as total')]);
            $now = Carbon::now()->toDateTimeString();
            $now = convertDateTimeInClientTimeZone($now);
            $product_ids = Product::where('vendor_id', $request->vendor_id)->pluck("id");
            $cart_products = CartProduct::with(['product.variant' => function($q){
                $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
                $q->groupBy('product_id');
            },
            'addon.set' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'addon.option' => function ($qry) use ($langId) {
                $qry->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                $qry->select('addon_options.id', 'addon_options.price', 'apt.title', 'addon_options.addon_id', 'apt.language_id');
                $qry->where('apt.language_id', $langId)->groupBy(['addon_options.id', 'apt.language_id']);
            }
            ])->where('vendor_id', $request->vendor_id)->where('cart_id', $request->cart_id)->get();
          // $total_minimum_spend = 0;
            foreach ($cart_products as $cart_product) {
                $total_price = 0;
                if(isset($cart_product->pvariant) && !empty($cart_product->pvariant->actual_price))
                {
                    $total_price = $cart_product->pvariant->actual_price ?? 0;
                }

                $total_minimum_spend += $total_price * $cart_product->quantity;

                $product_addon_price = 0;
                if (!empty($cart_product->addon)) {
                    foreach ($cart_product->addon as $ck => $addons) {
                        $opt_quantity_price = 0;
                        $opt_price_in_currency = $addons->option ? $addons->option->price : 0;
                        $addon_option = AddonOption::where(['addon_id'=>$addons->addon_id,'id'=>$addons->option_id]);
                        if($addon_option->exists()){
                            $addon_price = $addon_option->first()->price * $cart_product->quantity;
                        }
                        $product_addon_price += $addon_price??0;
                    }
                }
                $total_minimum_spend += $product_addon_price;
            }
            if ($product_ids) {
                if(isset($request->cart_product_ids) && !empty($request->cart_product_ids))
                {
                    foreach($request->cart_product_ids as $c_product_id)
                    {
                        $promo_code_details = PromoCodeDetail::where('refrence_id', $c_product_id)->pluck('promocode_id');
                        $result1 = Promocode::whereDate('expiry_date', '>=', $now)->where('restriction_on', 0)->where(function ($query) use ($promo_code_details,$firstOrderCheck) {
                            $query->where(function ($query2) use ($promo_code_details) {
                                $query2->where('restriction_type', 1);
                                if (!empty($promo_code_details->toArray())) {
                                    $query2->whereNotIn('id', $promo_code_details->toArray());
                                }
                            });

                            $query->orWhere(function ($query1) use ($promo_code_details) {
                                $query1->where('restriction_type', 0);
                                if (!empty($promo_code_details->toArray())) {
                                    $query1->whereIn('id', $promo_code_details->toArray());
                                } else {
                                    $query1->where('id', 0);
                                }
                            });
                        });
                        if($firstOrderCheck){
                            $result1->where('first_order_only', 0);
                        }
                        if($is_from_cart != 1){
                            $result1->where(['promo_visibility' => 'public']);
                        }
                        $result1 = $result1->where('is_deleted', 0)->get();
                        $promo_codes = $promo_codes->merge($result1);
                    }
                }
                $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->where('refrence_id', $vendor_id)->pluck('promocode_id');
                $result2 = Promocode::where('restriction_on', 1)->where(function ($query) use ($vendor_promo_code_details) {
                    $query->where(function ($query2) use ($vendor_promo_code_details) {
                        $query2->where('restriction_type', 1);
                        if (!empty($vendor_promo_code_details->toArray())) {
                            $query2->whereNotIn('id', $vendor_promo_code_details->toArray());
                        }
                    });

                    $query->orWhere(function ($query1) use ($vendor_promo_code_details) {
                        $query1->where('restriction_type', 0);
                        if (!empty($vendor_promo_code_details->toArray())) {
                            $query1->whereIn('id', $vendor_promo_code_details->toArray());
                        } else {
                            $query1->where('id', 0);
                        }
                    });

                });

                if($firstOrderCheck){
                    $result2->where('first_order_only', 0);
                }
                if($is_from_cart != 1){
                    $result2->where(['promo_visibility' => 'public']);
                }
                $result2 = $result2->where('is_deleted', 0)->whereDate('expiry_date', '>=', $now)->get();
                $promo_codes = $promo_codes->merge($result2);
            }
            $total_minimum_spend = $total_minimum_spend * $doller_compare;
            foreach ($promo_codes as $key => $promo_code) {
                $minimum_spend = 0;
                if (isset( $promo_code->minimum_spend)) {
                    $minimum_spend =  $promo_code->minimum_spend * $doller_compare;
                }

                $maximum_spend = 0;
                if (isset($promo_code->maximum_spend)) {
                    $maximum_spend = $promo_code->maximum_spend * $doller_compare;
                }
                if($total_minimum_spend < $minimum_spend || $total_minimum_spend > $maximum_spend){
                    $promo_codes->forget($key);
                }
            }
            return $this->successResponse($promo_codes, '', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function postVerifyPromoCode(Request $request){
        try {
            $user = Auth::user();
            $validator = $this->validatePromoCode();
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            if(!$vendor){
                return response()->json(['error' => 'Invalid vendor id.'], 404);
            }
            $cart_detail = Cart::where('id', $request->cart_id)->first();
            if(!$cart_detail){
                return $this->errorResponse('Invalid Cart Id', 422);
            }
            $promo_code = Promocode::where('id', $request->coupon_id)->first();

            if(!$promo_code){
                return $this->errorResponse('Invalid Promocode Id', 422);
            }elseif(isset($request->amount) && $request->amount < $promo_code->minimum_spend){
                return $this->errorResponse('Add item worth '.(int)($promo_code->minimum_spend - $request->amount).' to apply this offer.', 422);
            }

            $order_vendor_user_promo_count = OrderVendor::where(['coupon_id' => $request->coupon_id])->count();
            if($order_vendor_user_promo_count >= $promo_code->limit_total){
                return $this->errorResponse(__('Coupon Code limit has been reached.'), 422);
            }

            $order_vendor_user_promo_count = OrderVendor::where(['user_id' => $user->id, 'coupon_id' => $request->coupon_id])->count();
            if($order_vendor_user_promo_count >= $promo_code->limit_per_user){
                return $this->errorResponse('Coupon code already used', 422);
            }

            $cart_coupon_detail = CartCoupon::where('cart_id', $request->cart_id)->where('vendor_id', $request->vendor_id)->where('coupon_id', $request->coupon_id)->first();
            if($cart_coupon_detail){
                return $this->errorResponse('Coupon code already used', 422);
            }
            $cart_coupon_detail2 = CartCoupon::where('cart_id', $request->cart_id)->where('coupon_id', $request->coupon_id)->first();
            if($cart_coupon_detail2){
                return $this->errorResponse('Coupon code already used other vendor.', 422);
            }
            if($cart_detail->first_order_only == 1){
                $orders_count = Order::where('user_id', $user->id)->count();
                if($orders_count > 0){
                    return $this->errorResponse('Coupon Code apply only first order.', 422);
                }
            }

            $cart_coupon = new CartCoupon();
            $cart_coupon->cart_id = $request->cart_id;
            $cart_coupon->vendor_id = $request->vendor_id;
            $cart_coupon->coupon_id = $request->coupon_id;
            $cart_coupon->save();
            return $this->successResponse($cart_coupon, 'Promotion Code Used Successfully.', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function postRemovePromoCode(Request $request){
        try {
            $cart_detail = Cart::where('id', $request->cart_id)->first();
            if(!$cart_detail){
                return $this->errorResponse('Invalid Cart Id', 422);
            }
            $cart_detail = Promocode::where('id', $request->coupon_id)->first();
            if(!$cart_detail){
                return $this->errorResponse('Invalid Promocode Id', 422);
            }
            CartCoupon::where('cart_id', $request->cart_id)->where('coupon_id', $request->coupon_id)->delete();
            return $this->successResponse(null, 'Promotion Code Removed Successfully.', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function validatePromoCodeList($request){
        return Validator::make($request->all(), [
            'vendor_id' => 'required',
        ]);
    }

    public function validatePromoCode(){
        return Validator::make(request()->all(), [
            'cart_id' => 'required',
            'vendor_id' => 'required',
            'coupon_id' => 'required',
        ]);
    }

    public function validate_code(Request $request){
        try {
            // dd($request->all());
            $user = Auth::user();
            // $promo_codes = new \Illuminate\Database\Eloquent\Collection;
            $vendor_id = $request->vendor_id;
            $total_minimum_spend = $request->amount;
            $validator = $this->validatePromoCodeList($request);
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            if(!$vendor){
                return response()->json(['error' => 'Invalid vendor id.'], 404);
            }
            // $order_vendor_coupon_list = OrderVendor::whereNotNull('coupon_id')->where('user_id', $user->id)->get([DB::raw('coupon_id'),  DB::raw('sum(coupon_id) as total')]);
            $now = Carbon::now()->toDateTimeString();
            $product_ids = Product::where('vendor_id', $request->vendor_id)->pluck("id");
            if($product_ids){
                $checkRefferalCode = $this->checkRefferalCode($request->promocode, $now);
                if (!empty($checkRefferalCode)) {
                    $promo_detail = $checkRefferalCode;
                }else{
                    $promo_code_details = PromoCodeDetail::whereIn('refrence_id', $product_ids->toArray())->pluck('promocode_id');
                    $promo_detail = Promocode::where(['name' => $request->promocode])->whereDate('expiry_date', '>=', $now)->where('restriction_on', 0)->where(function ($query) use ($promo_code_details) {
                        $query->where(function ($query2) use ($promo_code_details) {
                            $query2->where('restriction_type', 1);
                            if (!empty($promo_code_details->toArray())) {
                                $query2->whereNotIn('id', $promo_code_details->toArray());
                            }
                        }
                        );
                        $query->orWhere(function ($query1) use ($promo_code_details) {
                            $query1->where('restriction_type', 0);
                            if (!empty($promo_code_details->toArray())) {
                                $query1->whereIn('id', $promo_code_details->toArray());
                            } else {
                                $query1->where('id', 0);
                            }
                        }
                        );
                    })->where('is_deleted', 0)->first();
                    if (!$promo_detail) {
                        $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->where('refrence_id', $request->vendor_id)->pluck('promocode_id');
                        $promo_detail = Promocode::where(['name' => $request->promocode])->where('restriction_on', 1)->where(function ($query) use ($vendor_promo_code_details) {
                            $query->where(function ($query2) use ($vendor_promo_code_details) {
                                $query2->where('restriction_type', 1);
                                if (!empty($vendor_promo_code_details->toArray())) {
                                    $query2->whereNotIn('id', $vendor_promo_code_details->toArray());
                                }
                            }
                            );
                            $query->orWhere(function ($query1) use ($vendor_promo_code_details) {
                                $query1->where('restriction_type', 0);
                                if (!empty($vendor_promo_code_details->toArray())) {
                                    $query1->whereIn('id', $vendor_promo_code_details->toArray());
                                } else {
                                    $query1->where('id', 0);
                                }
                            }
                            );
                        })->where('is_deleted', 0)->whereDate('expiry_date', '>=', $now)->first();
                    }
                    if(!$promo_detail){
                        return $this->errorResponse(__('Invalid Promocode'), 422);
                    }
                    if($total_minimum_spend < $promo_detail->minimum_spend){
                        return $this->errorResponse(__('Cart amount is less than required amount'), 422);
                    }
                    if($total_minimum_spend > $promo_detail->maximum_spend){
                        return $this->errorResponse(__('Cart amount is greater than required amount'), 422);
                    }
                }
            }

            // $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->where('refrence_id', $vendor_id)->pluck('promocode_id')->toArray();
            // $promo_result = Promocode::where(['name' => $request->promocode])->whereIn('id', $vendor_promo_code_details)->where('restriction_on', 1)->where('is_deleted', 0)->whereDate('expiry_date', '>=', $now)->first();
            if(!empty($promo_detail)){
                return $this->successResponse($promo_detail, '', 200);
            } else {
                return $this->errorResponse("Invalid promocode", 422);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function coupon_code_list($product_id, $vendor_id) {
        $now = Carbon::now()->toDateTimeString();
        $now = convertDateTimeInClientTimeZone($now);
        $promocode_product = $promocode_vendor = [];
        if( !empty($product_id) ) {
            $promocode_product = Promocode::select('promocodes.name', 'promocodes.short_desc', 'promo_types.title as promo_type_title', 'promocodes.amount', 'promocodes.promo_type_id')->whereDate('expiry_date', '>=', $now)->join('promocode_details', 'promocode_details.promocode_id', 'promocodes.id')->join('promo_types', 'promo_types.id', 'promocodes.promo_type_id')->where(['promocodes.promo_visibility' => 'public'])->where('promocodes.restriction_on', '0')->where('promocode_details.refrence_id', $product_id)->get()->toArray();
        }
        if( !empty($vendor_id) ) {
            $promocode_vendor = Promocode::select('promocodes.name', 'promocodes.short_desc', 'promo_types.title as promo_type_title', 'promocodes.amount', 'promocodes.promo_type_id')->whereDate('expiry_date', '>=', $now)->join('promocode_details', 'promocode_details.promocode_id', 'promocodes.id')->join('promo_types', 'promo_types.id', 'promocodes.promo_type_id')->where(['promocodes.promo_visibility' => 'public'])->where('promocodes.restriction_on', '1')->where('promocode_details.refrence_id', $vendor_id)->get()->toArray();
        }

        return array_merge($promocode_product, $promocode_vendor);
    }

    public function checkRefferalCode($promocode, $now){
        if(!empty($promocode)){
            $promo_detail = Promocode::where(['name' => $promocode])->whereDate('expiry_date', '>=', $now)->where('restriction_on', 0)->first();
            if(!empty($promo_detail) && $promo_detail->promo_type == 1){
                return $promo_detail;
            }else{
                return '';
            }
        }
        return '';
    }
}
