<?php

namespace App\Http\Controllers\Api\v1;
use Auth;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Promocode;
use App\Models\CartCoupon;
use App\Models\CartProduct;
use Illuminate\Http\Request;
use App\Models\{AddonOption, ClientCurrency, OrderVendor, PromoCodeDetail};
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PromoCodeController extends Controller{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function postProductPromoCodeCheck(Request $request){
        try {
            $user = Auth::user();
            $langId = $user->language;
            $promo_codes = new \Illuminate\Database\Eloquent\Collection;
            $product_id = $request->product_id;
            $firstOrderCheck = 0;
            $is_from_cart = $request->is_cart ? $request->is_cart :0;
            $now = Carbon::now()->toDateTimeString();
            $now = convertDateTimeInClientTimeZone($now);
            $promo_code_details = PromoCodeDetail::where('refrence_id', $product_id)->pluck('promocode_id');
            $result1 = Promocode::where('expiry_date', '>=', $now)->where('restriction_on', 0)->where(function ($query) use ($promo_code_details,$firstOrderCheck) {
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
            $langId = $user->language;
            $promo_codes = new \Illuminate\Database\Eloquent\Collection;
            $vendor_id = $request->vendor_id;
            $validator = $this->validatePromoCodeList($request);
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            if(!$vendor){
                return response()->json(['error' => __('Invalid vendor id.')], 404);
            }
            $is_from_cart = $request->is_cart ? $request->is_cart :0;
            $firstOrderCheck = 0;
            if( Auth::user()){
                $userOrder = auth()->user()->orders->first();
                if($userOrder){
                    $firstOrderCheck = 1;
                }
            }
            $clientCurrency = ClientCurrency::where('currency_id', $user->currency )->first();
            $now = Carbon::now()->toDateTimeString();
            $product_ids = Product::where('vendor_id', $request->vendor_id)->pluck("id")->toArray();
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
            $total_minimum_spend = 0;
            $cart_product_ids =[];
            foreach ($cart_products as $cart_product) {
                $total_price = 0;
                if(isset($cart_product->product->variant) && !empty($cart_product->product->variant->first()))
                {
                    $total_price = $cart_product->product->variant->first()->price ?? 0;
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
                        $product_addon_price += $addon_price;
                    }
                }
                $total_minimum_spend += $product_addon_price;
                $cart_product_ids[] = $cart_product->product_id;
            }
            if ($product_ids) {
                if(!empty($cart_product_ids)){
                    $product_ids = array_intersect($cart_product_ids,$product_ids);
                }
                $promo_code_details = PromoCodeDetail::whereIn('refrence_id', $product_ids)->pluck('promocode_id');
                $result1 = Promocode::whereDate('expiry_date', '>=', $now)->where('restriction_on', 0)->where(function ($query) use ($promo_code_details ) {
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
                $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->where('refrence_id', $vendor_id)->pluck('promocode_id');
                $result2 = Promocode::where('restriction_on', 1)->where(function ($query) use ($vendor_promo_code_details ) {
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
            $doller_compare = $clientCurrency ?  $clientCurrency->doller_compare : 1 ;
            foreach ($promo_codes as $key => $promo_code) {
                $minimum_spend = 0;
                if (isset( $promo_code->minimum_spend)) {
                    $minimum_spend =  $promo_code->minimum_spend * $doller_compare;
                }

                $maximum_spend = 0;
                if (isset($promo_code->maximum_spend)) {
                    $maximum_spend = $promo_code->maximum_spend * $doller_compare;
                }
                if($total_minimum_spend < $minimum_spend){
                    $promo_codes->forget($key);
                }
                if($total_minimum_spend > $maximum_spend){
                    $promo_codes->forget($key);
                }
            }

            return $this->successResponse(array_values($promo_codes->toArray()), '', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage( ), $e->getCode());
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
                return response()->json(['error' => __('Invalid vendor id.')], 404);
            }
            $cart_detail = Cart::where('id', $request->cart_id)->first();
            if(!$cart_detail){
                return $this->errorResponse(__('Invalid Cart Id'), 422);
            }
            $cart_detail = Promocode::where('id', $request->coupon_id)->first();
            if(!$cart_detail){
                return $this->errorResponse(__('Invalid Promocode Id'), 422);
            }
            $promo_code = Promocode::where('id', $request->coupon_id)->first();

            if(!$promo_code){
                return $this->errorResponse('Invalid Promocode Id', 422);
            }elseif(isset($request->amount) && $request->amount < $promo_code->minimum_spend){
                return $this->errorResponse('Add item worth '.(int)($promo_code->minimum_spend - $request->amount).' to apply this offer.', 422);
            }
            $order_vendor_user_promo_count = OrderVendor::where(['user_id' => $user->id, 'coupon_id' => $request->coupon_id])->count();
            if($order_vendor_user_promo_count >= $promo_code->limit_per_user){
                return $this->errorResponse('Coupon Code already applied.', 422);
            }
            $order_vendor_user_promo_count = OrderVendor::where(['coupon_id' => $request->coupon_id])->count();
            if($order_vendor_user_promo_count >= $promo_code->limit_total){
                return $this->errorResponse(__('Coupon Code limit has been reached.'), 422);
            }

            $order_vendor_user_promo_count = OrderVendor::where(['coupon_id' => $request->coupon_id])->count();
            if($order_vendor_user_promo_count >= $promo_code->limit_total){
                return $this->errorResponse(__('Coupon Code limit has been reached.'), 422);
            }

            $cart_coupon_detail = CartCoupon::where('cart_id', $request->cart_id)->where('vendor_id', $request->vendor_id)->where('coupon_id', $request->coupon_id)->first();
            if($cart_coupon_detail){
                return $this->errorResponse(__('Coupon Code already applied.'), 422);
            }
            $cart_coupon_detail2 = CartCoupon::where('cart_id', $request->cart_id)->where('coupon_id', $request->coupon_id)->first();
            if($cart_coupon_detail2){
                return $this->errorResponse(__('Coupon Code already applied other vendor.'), 422);
            }
            $cart_coupon = new CartCoupon();
            $cart_coupon->cart_id = $request->cart_id;
            $cart_coupon->vendor_id = $request->vendor_id;
            $cart_coupon->coupon_id = $request->coupon_id;
            $cart_coupon->save();
            return $this->successResponse($cart_coupon, __('Promotion Code Used Successfully'), 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function postRemovePromoCode(Request $request){
        try {
            $validator = $this->validatePromoCode();
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $cart_detail = Cart::where('id', $request->cart_id)->first();
            if(!$cart_detail){
                return $this->errorResponse(__('Invalid Cart Id'), 422);
            }
            $cart_detail = Promocode::where('id', $request->coupon_id)->first();
            if(!$cart_detail){
                return $this->errorResponse(__('Invalid Promocode Id'), 422);
            }
            CartCoupon::where('cart_id', $request->cart_id)->where('coupon_id', $request->coupon_id)->delete();
            return $this->successResponse(null, __('Promotion Code Removed Successfully'), 201);
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

    public function validate_promo_code(Request $request){
        try {
            $user = Auth::user();
            $validator = $this->validatePromoCodeList($request);
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            if(!$vendor){
                return response()->json(['error' => __('Invalid vendor id.')], 404);
            }
           
            $promo_code = Promocode::where('name', $request->promocode)->first();

            if(!$promo_code){
                return $this->errorResponse('Invalid Promocode Id', 422);
            }elseif(isset($request->amount) && $request->amount < $promo_code->minimum_spend){
                return $this->errorResponse('Add item worth '.(int)($promo_code->minimum_spend - $request->amount).' to apply this offer.', 422);
            }
            $order_vendor_user_promo_count = OrderVendor::where(['user_id' => $user->id, 'coupon_code' => $request->promocode])->count();
            if($order_vendor_user_promo_count >= $promo_code->limit_per_user){
                return $this->errorResponse('Coupon Code already applied.', 422);
            }

            $order_vendor_user_promo_count = OrderVendor::where(['coupon_code' => $request->promocode])->count();
            if($order_vendor_user_promo_count >= $promo_code->limit_total){
                return $this->errorResponse(__('Coupon Code limit has been reached.'), 422);
            }

            $now = Carbon::now()->toDateTimeString();
            $product_ids = Product::where('vendor_id', $request->vendor_id)->pluck("id");
            if($product_ids){
                $promo_code_details = PromoCodeDetail::whereIn('refrence_id', $product_ids->toArray())->pluck('promocode_id');
                    $promo_detail = Promocode::where(['name' => $request->promocode])->whereDate('expiry_date', '>=', $now)->where('restriction_on', 0)->where(function($query) use($promo_code_details){
                        $query->where(function ($query2) use ($promo_code_details) {
                            $query2->where('restriction_type', 1);
                            if (!empty($promo_code_details->toArray())) {
                                $query2->whereNotIn('id', $promo_code_details->toArray());
                            }
                        });
                        $query->orWhere(function($query1) use($promo_code_details){
                            $query1->where('restriction_type' , 0);
                            if (!empty($promo_code_details->toArray())) {
                                $query1->whereIn('id', $promo_code_details->toArray());
                            } else {
                                $query1->where('id', 0);
                            }
                        });
                    })->where('is_deleted', 0)->first();
                if (!$promo_detail) {
                    $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->where('refrence_id', $request->vendor_id)->pluck('promocode_id');
                    $promo_detail = Promocode::where(['name' => $request->promocode])->where('restriction_on', 1)->where(function($query) use($vendor_promo_code_details){
                        $query->where(function ($query2) use ($vendor_promo_code_details) {
                            $query2->where('restriction_type', 1);
                            if (!empty($vendor_promo_code_details->toArray())) {
                                $query2->whereNotIn('id', $vendor_promo_code_details->toArray());
                            }
                        });
                        $query->orWhere(function($query1) use($vendor_promo_code_details){
                            $query1->where('restriction_type' , 0);
                            if (!empty($vendor_promo_code_details->toArray())) {
                                $query1->whereIn('id', $vendor_promo_code_details->toArray());
                            } else {
                                $query1->where('id', 0);
                            }
                        });
                    })->where('is_deleted', 0)->whereDate('expiry_date', '>=', $now)->first();
                }
            }

            if(!$promo_detail){
                return $this->errorResponse(__('Invalid Promocode'), 422);
            }
            // promocode validation from cart 
            if(isset($request->cart_id)){
            $cart_detail = Cart::where('id', $request->cart_id)->first();
            if(!$cart_detail){
                return $this->errorResponse(__('Invalid Cart Id'), 422);
            }
            $cart_coupon_detail = CartCoupon::where('cart_id', $request->cart_id)->where('vendor_id', $request->vendor_id)->where('coupon_id', $promo_detail->id)->first();
            if($cart_coupon_detail){
                return $this->errorResponse(__('Coupon Code already applied.'), 422);
            }
            $cart_coupon_detail2 = CartCoupon::where('cart_id', $request->cart_id)->where('coupon_id', $promo_detail->id)->first();
            if($cart_coupon_detail2){
                return $this->errorResponse(__('Coupon Code already applied other vendor.'), 422);
            }
            $cart_products = CartProduct::with(['product.variant' => function ($q) {
                $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
                $q->groupBy('product_id');
            }])->where('vendor_id', $request->vendor_id)->where('cart_id', $request->cart_id)->get();
            $total_minimum_spend = 0;
            foreach ($cart_products as $cart_product) {
                $total_price = 0;
                if (isset($cart_product->product->variant) && !empty($cart_product->product->variant->first())) {
                    $total_price = $cart_product->product->variant->first()->price ?? 0;
                }
                $total_minimum_spend += $total_price * $cart_product->quantity;
            }
            if($total_minimum_spend < $promo_detail->minimum_spend){
                return $this->errorResponse(__('Cart amount is less than required amount'), 422);
            }
            if($total_minimum_spend > $promo_detail->maximum_spend){
                return $this->errorResponse(__('Cart amount is greater than required amount'), 422);
            }
            $cart_coupon = new CartCoupon();
            $cart_coupon->cart_id = $request->cart_id;
            $cart_coupon->vendor_id = $request->vendor_id;
            $cart_coupon->coupon_id = $promo_detail->id;
            $cart_coupon->save();
            return $this->successResponse($promo_detail, __('Promotion Code Used Successfully'), 201);
        }else{
            if(isset($request->amount)){
                if($request->amount < $promo_detail->minimum_spend){
                    return $this->errorResponse(__('Cart amount is less than required amount'), 422);
                }
                if($request->amount > $promo_detail->maximum_spend){
                    return $this->errorResponse(__('Cart amount is greater than required amount'), 422);
                }
            // validation from ride booking
            if ($promo_detail->promo_type_id == 2) {
                $promo_detail['new_amount'] = $promo_detail->amount;
                if ($promo_detail['new_amount'] < 0)
                    $promo_detail['new_amount'] = 0.00;
            }
            if ($promo_detail->promo_type_id == 1) {
                $promo_detail['new_amount'] = ($request->amount* ($promo_detail->amount / 100));
                if ($promo_detail['new_amount'] < 0)
                    $promo_detail['new_amount'] = 0.00;
            }
            return $this->successResponse($promo_detail, __('Promotion Code Used Successfully'), 201);
        }else{
            return $this->errorResponse(__('Invaild cart amount'), 422);
        }
    }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function vendorPromoCodeList(Request $request){
        try {
            $vendor_id = $request->vendor_id;
            $validator = $this->validatePromoCodeList($request);
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }
            $vendor = Vendor::where('id', $request->vendor_id)->first();
            if(!$vendor){
                return response()->json(['error' => __('Invalid vendor id.')], 404);
            }
            $now = Carbon::now()->toDateTimeString();

            $vendor_promo_code_details = PromoCodeDetail::whereHas('promocode')->where('refrence_id', $vendor_id)->pluck('promocode_id');
            $promo_codes = Promocode::where('restriction_on', 1)->where(function ($query) use ($vendor_promo_code_details) {
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
            })->where('is_deleted', 0)->whereDate('expiry_date', '>=', $now)->where(['promo_visibility' => 'public'])->get();

            return $this->successResponse($promo_codes, '', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

}
