<?php

namespace App\Http\Controllers\Front;

use DB;
use Auth;
use Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GCLIENT;
use App\Http\Traits\{ApiResponser,CartManager,ProductTrait,OrderTrait};
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Front\{FrontController, LalaMovesController, OrderController, PromoCodeController};
use App\Models\{AddonSet, Cart, CartAddon, CartProduct, CartCoupon, CartDeliveryFee, TempCart, TempCartAddon, TempCartProduct, TempCartCoupon, TempCartDeliveryFee, User, Product, ClientCurrency, ClientLanguage, CartProductPrescription, ProductVariantSet, Country, UserAddress, Client, ClientPreference, Vendor, Order, OrderProduct, OrderProductAddon, OrderProductPrescription, VendorOrderStatus, OrderVendor,PaymentOption, OrderTax, LuxuryOption, UserWishlist, SubscriptionInvoicesUser, LoyaltyCard, VendorDineinCategory, VendorDineinTable, VendorDineinCategoryTranslation, VendorDineinTableTranslation, VendorSlot, UserDevice, NotificationTemplate};
use Log;
class TempCartController extends FrontController
{
    use ApiResponser,CartManager,ProductTrait,OrderTrait;

    /**
     * get products from cart
     *
     * @return \Illuminate\Http\Response
     */
    public function getCartProducts($domain = '')
    {
        $cart_details = [];
        $user = Auth::user();
        $curId = Session::get('customerCurrency');
        $langId = Session::get('customerLanguage');
        if ($user) {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
        } else {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
        }
        if ($cart) {
            $cart_details = $this->getCart($cart);
        }
        if ($cart_details && !empty($cart_details)) {
            return response()->json([
                'data' => $cart_details,
            ]);
        }
        return response()->json([
            'message' => "No product found in cart",
            'data' => $cart_details,
        ]);
    }

    /**
     * Get Cart Items
     *
     */
    public function getCart($cart, $langId = '1', $currency = '1', $type = 'delivery')
    {
        $additionalPreference =getAdditionalPreference(['is_service_product_price_from_dispatch']);
        $is_service_product_price_from_dispatch = $additionalPreference['is_service_product_price_from_dispatch'];
        $preferences = ClientPreference::first();
        $clientCurrency = ClientCurrency::where('currency_id', $currency)->first();
        if (!$cart) {
            return false;
        }
        $vondorCnt = 0;
        $address = [];
        $latitude = '';
        $longitude = '';
        $address_id = 0;
        $delivery_status = 1;
        $cartID = $cart->id;
        $upSell_products = collect();
        $crossSell_products = collect();
        $delifproductnotexist = TempCartProduct::where('cart_id', $cartID)->doesntHave('product')->delete();
        $cartData = TempCartProduct::with([
            'vendor', 'coupon' => function ($qry) use ($cartID) {
                $qry->where('cart_id', $cartID);
            }, 'coupon.promo.details', 'vendorProducts.pvariant.media.image', 'vendorProducts.product.media.image',
            'vendorProducts.pvariant.vset.variantDetail.trans' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.pvariant.vset.optionData.trans' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.product.translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                $q->where('language_id', $langId);
            },
            'vendorProducts' => function ($qry) use ($cartID) {
                $qry->where('cart_id', $cartID);
            },
            'vendorProducts.addon.set' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.addon.option' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            }, 'vendorProducts.product.taxCategory.taxRate',
        ])->select('vendor_id', 'vendor_dinein_table_id')->where('cart_id', $cartID)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();
        $loyalty_amount_saved = 0;
        $subscription_features = array();
        if ($cart->user_id) {
            $now = Carbon::now()->toDateTimeString();
            $user_subscription = SubscriptionInvoicesUser::with('features')
                ->select('id', 'user_id', 'subscription_id')
                ->where('user_id', $cart->user_id)
                ->where('end_date', '>', $now)
                ->orderBy('end_date', 'desc')->first();
            if ($user_subscription) {
                foreach ($user_subscription->features as $feature) {
                    $subscription_features[] = $feature->feature_id;
                }
            }
            $user = User::find($cart->user_id);
            $cart->scheduled_date_time = !empty($cart->scheduled_date_time) ? convertDateTimeInTimeZone($cart->scheduled_date_time, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $cart->schedule_pickup = !empty($cart->schedule_pickup) ? convertDateTimeInTimeZone($cart->schedule_pickup, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $cart->schedule_dropoff = !empty($cart->schedule_dropoff) ? convertDateTimeInTimeZone($cart->schedule_dropoff, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $address = UserAddress::where('user_id', $cart->user_id)->where('is_primary', 1)->first();
            $address_id = ($address) ? $address->id : 0;
        }
        $latitude = ($address) ? $address->latitude : '';
        $longitude = ($address) ? $address->longitude : '';
        $total_payable_amount = $total_subscription_discount = $total_discount_amount = $total_discount_percent = $total_taxable_amount = 0.00;
        $total_tax = $total_paying = $total_disc_amount = 0.00;
        $item_count = 0;
        $total_delivery_amount = 0;
        $order_sub_total = 0;
        if ($cartData) {
            $cart_dinein_table_id = NULL;
            $action = $type;
            $vendor_details = [];
            $tax_details = [];
            $is_vendor_closed = 0;
            $delay_date = 0;
            $pickup_delay_date = 0;
            $dropoff_delay_date = 0;
            $total_service_fee = 0;
            $product_out_of_stock = 0;
            foreach ($cartData as $ven_key => $vendorData) {
                $is_promo_code_available = 0;
                $vendor_products_total_amount = $codeApplied = $is_percent = $proSum = $proSumDis = $taxable_amount = $subscription_discount = $discount_amount = $discount_percent = $deliver_charge = $delivery_fee_charges = 0.00;
                $delivery_count = 0;

                $cart_dinein_table_id = $vendorData->vendor_dinein_table_id;

                if ($action != 'delivery') {
                    $vendor_details['vendor_address'] = $vendorData->vendor->select('id', 'latitude', 'longitude', 'address')->where('id', $vendorData->vendor_id)->first();
                    if ($action == 'dine_in') {
                        $vendor_tables = VendorDineinTable::where('vendor_id', $vendorData->vendor_id)->with('category')->get();
                        foreach ($vendor_tables as $vendor_table) {
                            $vendor_table->qr_url = url('/vendor/' . $vendorData->vendor->slug . '/?id=' . $vendorData->vendor_id . '&name=' . $vendorData->vendor->name . '&table=' . $vendor_table->id);
                        }
                        $vendor_details['vendor_tables'] = $vendor_tables;
                    }
                } else {
                    if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)) {
                        if ($address_id > 0) {

                            if (!empty($latitude) && !empty($longitude)) {
                                $serviceArea = $vendorData->vendor->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                                    $query->select('vendor_id')
                                    ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                                })->where('id', $vendorData->vendor_id)->get();
                            }
                        }
                    }
                }

                $ttAddon = $payable_amount = $is_coupon_applied = $coupon_removed = 0;
                $coupon_removed_msg = '';
                $deliver_charge = 0;
                $delivery_fee_charges = 0.00;
                $couponData = $couponProducts = array();
                if (!empty($vendorData->coupon->promo) && ($vendorData->coupon->vendor_id == $vendorData->vendor_id)) {
                    $now = Carbon::now()->toDateTimeString();
                    $minimum_spend = 0;
                    if (isset($vendorData->coupon->promo->minimum_spend)) {
                        $minimum_spend = $vendorData->coupon->promo->minimum_spend * $clientCurrency->doller_compare;
                    }
                    if ($vendorData->coupon->promo->expiry_date < $now) {
                        $coupon_removed = 1;
                        $coupon_removed_msg = 'Coupon code is expired.';
                    } else {
                        $couponData['coupon_id'] =  $vendorData->coupon->promo->id;
                        $couponData['name'] =  $vendorData->coupon->promo->name;
                        $couponData['disc_type'] = ($vendorData->coupon->promo->promo_type_id == 1) ? 'Percent' : 'Amount';
                        $couponData['expiry_date'] =  $vendorData->coupon->promo->expiry_date;
                        $couponData['allow_free_delivery'] =  $vendorData->coupon->promo->allow_free_delivery;
                        $couponData['minimum_spend'] =  $vendorData->coupon->promo->minimum_spend;
                        $couponData['first_order_only'] = $vendorData->coupon->promo->first_order_only;
                        $couponData['restriction_on'] = ($vendorData->coupon->promo->restriction_on == 1) ? 'Vendor' : 'Product';

                        $is_coupon_applied = 1;
                        if ($vendorData->coupon->promo->promo_type_id == 1) {
                            $is_percent = 1;
                            $discount_percent = round($vendorData->coupon->promo->amount);
                        } else {
                            $discount_amount = $vendorData->coupon->promo->amount * $clientCurrency->doller_compare;
                        }
                        if ($vendorData->coupon->promo->restriction_on == 0) {
                            foreach ($vendorData->coupon->promo->details as $key => $value) {
                                $couponProducts[] = $value->refrence_id;
                            }
                        }
                    }
                }

                foreach ($vendorData->vendorProducts as $pkey => $prod) {
                   // pr($prod->toArray());
                    if(isset($prod->product) && !empty($prod->product)){
                        if($is_service_product_price_from_dispatch !=1){ // no need to check slot and web styling 
                            if($prod->product->sell_when_out_of_stock == 0){
                                $quantity_check = productvariantQuantity($prod->variant_id);
                                if($quantity_check < $prod->quantity ){
                                    $delivery_status=0;
                                    $product_out_of_stock = 1;
                                }
                            }
                        }
                        $prod->product_out_of_stock =  $product_out_of_stock;

                        $price_in_currency = $price_in_doller_compare = $pro_disc = $quantity_price = 0;
                        $variantsData = $taxData = $vendorAddons = array();
                        $divider = (empty($prod->doller_compare) || $prod->doller_compare < 0) ? 1 : $prod->doller_compare;
                        $price_in_currency = $prod->pvariant ? $prod->pvariant->price : 0;
                        if( ( $is_service_product_price_from_dispatch ==1 )){
                            $price_in_currency = isset($prod->dispatch_agent_price) ? $prod->dispatch_agent_price : 0 ;
                        }
                        $price_in_doller_compare = $price_in_currency * $clientCurrency->doller_compare;
                        $quantity_price = $price_in_doller_compare * $prod->quantity;
                        $item_count = $item_count + $prod->quantity;
                        $proSum = $proSum + $quantity_price;
                        if($prod->is_payment_done !=1){
                            $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price;
                        }
                        if (isset($prod->pvariant->image->imagedata) && !empty($prod->pvariant->image->imagedata)) {
                            $prod->cartImg = $prod->pvariant->image->imagedata;
                        } else {
                            $prod->cartImg = (isset($prod->product->media[0]) && !empty($prod->product->media[0])) ? $prod->product->media[0]->image : '';
                        }

                        if($prod->product->delay_hrs_min != 0){
                            if($prod->product->delay_hrs_min > $delay_date)
                            $delay_date = $prod->product->delay_hrs_min;
                        }
                        if($prod->product->pickup_delay_hrs_min != 0){
                            if($prod->product->pickup_delay_hrs_min > $delay_date)
                            $pickup_delay_date = $prod->product->pickup_delay_hrs_min;
                        }

                        if($prod->product->dropoff_delay_hrs_min != 0){
                            if($prod->product->dropoff_delay_hrs_min > $delay_date)
                            $dropoff_delay_date = $prod->product->dropoff_delay_hrs_min;
                        }

                        if ($prod->pvariant) {
                            $variantsData['price']              = $price_in_currency;
                            $variantsData['id']                 = $prod->pvariant->id;
                            $variantsData['sku']                = ucfirst($prod->pvariant->sku);
                            $variantsData['title']              = $prod->pvariant->title;
                            $variantsData['barcode']            = $prod->pvariant->barcode;
                            $variantsData['product_id']         = $prod->pvariant->product_id;
                            $variantsData['multiplier']         = $clientCurrency->doller_compare;
                            $variantsData['gross_qty_price']    = $price_in_doller_compare * $prod->quantity;
                            if (!empty($vendorData->coupon->promo) && ($vendorData->coupon->promo->restriction_on == 0) && in_array($prod->product_id, $couponProducts)) {
                                $pro_disc = $discount_amount;
                                if ($minimum_spend <= $quantity_price) {
                                    if ($is_percent == 1) {
                                        $pro_disc = ($quantity_price * $discount_percent) / 100;
                                    }
                                    $quantity_price = $quantity_price - $pro_disc;
                                    $proSumDis = $proSumDis + $pro_disc;
                                    if ($quantity_price < 0) {
                                        $quantity_price = 0;
                                    }
                                    $codeApplied = 1;
                                } else {
                                    $variantsData['coupon_msg'] = "Spend Minimum " . $minimum_spend . " to apply this coupon";
                                    $variantsData['coupon_not_appiled'] = 1;
                                }
                            }

                            $variantsData['discount_amount'] = $pro_disc;
                            $variantsData['coupon_applied'] = $codeApplied;
                            $variantsData['quantity_price'] = $quantity_price;
                            if($prod->is_payment_done !=1){
                                $payable_amount = $payable_amount + $quantity_price;
                            }
                            if (!empty($prod->product->taxCategory) && count($prod->product->taxCategory->taxRate) > 0) {
                                foreach ($prod->product->taxCategory->taxRate as $tckey => $tax_value) {
                                    $rate = round($tax_value->tax_rate);
                                    $tax_amount = ($price_in_doller_compare * $rate) / 100;
                                    $product_tax = $quantity_price * $rate / 100;
                                    $taxData[$tckey]['rate'] = $rate;
                                    $taxData[$tckey]['tax_amount'] = $tax_amount;
                                    $taxData[$tckey]['product_tax'] = $product_tax;
                                    $taxable_amount = $taxable_amount + $product_tax;
                                    $taxData[$tckey]['sku'] = ucfirst($prod->pvariant->sku);
                                    $taxData[$tckey]['identifier'] = $tax_value->identifier;
                                    $tax_details[] = array(
                                        'rate' => $rate,
                                        'tax_amount' => $tax_amount,
                                        'identifier' => $tax_value->identifier,
                                        'sku' => ucfirst($prod->pvariant->sku),
                                    );
                                }
                            }
                            $prod->taxdata = $taxData;
                            if ($action == 'delivery') {
                                if (!empty($prod->product->Requires_last_mile) && ($prod->product->Requires_last_mile == 1)) {
                                    $deliver_charge = $this->getDeliveryFeeDispatcher($vendorData->vendor_id);
                                    if (!empty($deliver_charge) && $delivery_count == 0) {
                                        $delivery_count = 1;
                                        $prod->deliver_charge = decimal_format($deliver_charge);
                                        $payable_amount = $payable_amount + $deliver_charge;
                                        $order_sub_total = $order_sub_total + $deliver_charge;
                                        $delivery_fee_charges = $deliver_charge;
                                    }
                                }
                            }
                            if (!empty($prod->addon)) {
                                foreach ($prod->addon as $ck => $addons) {
                                    $opt_quantity_price = 0;
                                    $opt_price_in_currency = $addons->option ? $addons->option->price : 0;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                    $opt_quantity_price = $opt_price_in_doller_compare * $prod->quantity;
                                    $vendorAddons[$ck]['quantity'] = $prod->quantity;
                                    $vendorAddons[$ck]['addon_id'] = $addons->addon_id;
                                    $vendorAddons[$ck]['option_id'] = $addons->option_id;
                                    $vendorAddons[$ck]['price'] = $opt_price_in_currency;
                                    $vendorAddons[$ck]['addon_title'] = $addons->set->title;
                                    $vendorAddons[$ck]['quantity_price'] = $opt_quantity_price;
                                    $vendorAddons[$ck]['option_title'] = $addons->option ? $addons->option->title : 0;
                                    $vendorAddons[$ck]['price_in_cart'] = $addons->option->price;
                                    $vendorAddons[$ck]['cart_product_id'] = $addons->cart_product_id;
                                    $vendorAddons[$ck]['multiplier'] = $clientCurrency->doller_compare;
                                    $ttAddon = $ttAddon + $opt_quantity_price;
                                    $payable_amount = $payable_amount + $opt_quantity_price;
                                    $order_sub_total = $order_sub_total + $opt_quantity_price;
                                }
                            }
                            unset($prod->addon);
                            unset($prod->pvariant);
                        }
                        $variant_options = [];
                        if ($prod->pvariant) {
                            foreach ($prod->pvariant->vset as $variant_set_option) {
                                $variant_options[] = array(
                                    'option' => $variant_set_option->optionData->trans->title,
                                    'title' => $variant_set_option->variantDetail->trans->title,
                                );
                            }
                        }
                        $prod->variants = $variantsData;
                        $prod->variant_options = $variant_options;
                        $prod->product_addons = $vendorAddons;

                        $product = Product::with([
                            'variant' => function ($sel) {
                                $sel->groupBy('product_id');
                            },
                            'variant.media.pimage.image', 'upSell', 'crossSell', 'vendor', 'media.image', 'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                                $q->where('language_id', $langId);
                            }])->select('id', 'sku', 'inquiry_only', 'url_slug', 'weight', 'weight_unit', 'vendor_id', 'has_variant', 'has_inventory', 'averageRating','minimum_order_count','batch_count')
                            ->where('url_slug', $prod->product->url_slug)
                            ->first();
                        $doller_compare = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
                        $up_prods = $this->metaProduct($langId, $doller_compare, 'upSell', $product->upSell);
                        if($up_prods){
                            $upSell_products->push($up_prods);
                        }
                        $cross_prods = $this->metaProduct($langId, $doller_compare, 'crossSell', $product->crossSell);
                        if($cross_prods){
                            $crossSell_products->push($cross_prods);
                        }
                    }
                }

                $couponApplied = 0;
                if (!empty($vendorData->coupon->promo) && ($vendorData->coupon->promo->restriction_on == 1)) {
                    $minimum_spend = $vendorData->coupon->promo->minimum_spend * $clientCurrency->doller_compare;
                    if ($minimum_spend < $proSum) {
                        if ($is_percent == 1) {
                            $discount_amount = ($proSum * $discount_percent) / 100;
                        }
                        $couponApplied = 1;
                    } else {
                        $vendorData->coupon_msg = "To apply coupon minimum spend should be greater than " . $minimum_spend . '.';
                        $vendorData->coupon_not_appiled = 1;
                    }
                }


                $deliver_charge = $deliver_charge * $clientCurrency->doller_compare;
                $vendorData->proSum = $proSum;
                $vendorData->addonSum = $ttAddon;
                $vendorData->deliver_charge = $deliver_charge;
                $total_delivery_amount += $deliver_charge;
                $vendorData->coupon_apply_on_vendor = $couponApplied;
                $vendorData->is_coupon_applied = $is_coupon_applied;
                if (empty($couponData)) {
                    $vendorData->couponData = NULL;
                } else {
                    $vendorData->couponData = $couponData;
                }
                $vendor_service_fee_percentage_amount = 0;
                if($vendorData->vendor->service_fee_percent > 0){
                    $vendor_service_fee_percentage_amount = ($vendor_products_total_amount * $vendorData->vendor->service_fee_percent) / 100 ;
                     $payable_amount = $payable_amount + $vendor_service_fee_percentage_amount;
                }
                $total_service_fee = $total_service_fee + $vendor_service_fee_percentage_amount;
                $vendorData->service_fee_percentage_amount = decimal_format($vendor_service_fee_percentage_amount);
                $vendorData->vendor_gross_total = $payable_amount;
                $vendorData->discount_amount = $discount_amount;
                $vendorData->discount_percent = $discount_percent;
                $vendorData->taxable_amount = $taxable_amount;
                $vendorData->payable_amount = $payable_amount - $discount_amount;
                $vendorData->isDeliverable = 1;
                $total_paying = $total_paying + $payable_amount;
                $total_tax = $total_tax + $taxable_amount;
                $total_disc_amount = $total_disc_amount + $discount_amount;
                $total_discount_percent = $total_discount_percent + $discount_percent;
                $vendorData->vendor->is_vendor_closed = $is_vendor_closed;
                if (!empty($vendorData->coupon->promo)) {
                    unset($vendorData->coupon->promo);
                }

                if (in_array(1, $subscription_features)) {
                    $subscription_discount = $subscription_discount + $deliver_charge;
                }
                $total_subscription_discount = $total_subscription_discount + $subscription_discount;
                if($is_service_product_price_from_dispatch !=1){ // no need to check slot and web styling 
                    if (isset($serviceArea)) {
                        if ($serviceArea->isEmpty()) {
                            $vendorData->isDeliverable = 0;
                            $delivery_status = 0;
                        }
                    }
                    if ($vendorData->vendor->show_slot == 0) {
                        if (($vendorData->vendor->slotDate->isEmpty()) && ($vendorData->vendor->slot->isEmpty())) {
                            $vendorData->vendor->is_vendor_closed = 1;
                            if ($delivery_status != 0) {
                                $delivery_status = 0;
                            }
                        } else {
                            $vendorData->vendor->is_vendor_closed = 0;
                        }
                    }
                }

                if($vendorData->vendor->$action == 0){
                    $vendorData->is_vendor_closed = 1;
                    $delivery_status = 0;
                }

                $order_sub_total = $order_sub_total + $vendor_products_total_amount;
                if($is_service_product_price_from_dispatch !=1){ // no need to check slot and web styling 
                    if((float)($vendorData->vendor->order_min_amount) > $payable_amount){  # if any vendor total amount of order is less then minimum order amount
                        $delivery_status = 0;
                    }
                }
                $promoCodeController = new PromoCodeController();
                $promoCodeRequest = new Request();
                $promoCodeRequest->setMethod('POST');
                $promoCodeRequest->request->add(['vendor_id' => $vendorData->vendor_id, 'cart_id' => $cartID]);
                $promoCodeResponse = $promoCodeController->postPromoCodeList($promoCodeRequest)->getData();
                if($promoCodeResponse->status == 'Success'){
                    if($promoCodeResponse->data){
                        $is_promo_code_available = 1;
                    }
                }
                $vendorData->is_promo_code_available = $is_promo_code_available;
            }
            ++$vondorCnt;
        }//End cart Vendor loop

        $cart_product_luxury_id = TempCartProduct::where('cart_id', $cartID)->select('luxury_option_id', 'vendor_id')->first();
        if ($cart_product_luxury_id) {
            if ($cart_product_luxury_id->luxury_option_id == 2 || $cart_product_luxury_id->luxury_option_id == 3) {
                $vendor_address = Vendor::where('id', $cart_product_luxury_id->vendor_id)->select('address')->first();
                $cart->address = $vendor_address->address;
            }
        }
        if (!empty($subscription_features)) {
            $total_disc_amount = $total_disc_amount + $total_subscription_discount;
            $cart->total_subscription_discount = $total_subscription_discount * $clientCurrency->doller_compare;
        }

        if($cartData->count() == '1'){
            $vendorId = $cartData[0]->vendor_id;
            //type must be a : delivery , takeaway,dine_in
            $duration = Vendor::where('id',$vendorId)->select('slot_minutes')->first();
            $slots = showSlotTemp('',$vendorId, $cart->user_id, 'delivery',$duration->slot_minutes);
            $cart->slots = $slots;
           // $cart->vendor_id =  $vendorId;
        }else{
            $slots = [];
            $cart->slots = [];
            //$cart->vendor_id =  0;
        }

        $cart->total_service_fee = decimal_format($total_service_fee);
        $cart->total_tax = $total_tax;
        $cart->tax_details = $tax_details;
        // $cart->gross_paybale_amount = $total_paying;
        // if( $cart->vendor_wallet_amount_used > 0){
            
        // }
        $cart->gross_paybale_amount = $order_sub_total;
        $cart->total_discount_amount = $total_disc_amount * $clientCurrency->doller_compare;
        $cart->products = $cartData;
        $cart->item_count = $item_count;
        $temp_total_paying = $total_paying  + $total_tax - $total_disc_amount;
        // if ($cart->user_id > 0) {
        //     $loyalty_amount_saved = $this->getLoyaltyPoints($cart->user_id, $clientCurrency->doller_compare);
        // }
        // if ($loyalty_amount_saved  >= $temp_total_paying) {
        //     $loyalty_amount_saved = $temp_total_paying;
        //     $cart->total_payable_amount = 0.00;
        // } else {
            $cart->total_payable_amount = $total_paying  + $total_tax - $total_disc_amount - $loyalty_amount_saved;
        // }
        $cart->wallet_amount_used =  $cart->vendor_wallet_amount_used;
        // $wallet_amount_used = 0;
        // if (isset($user)) {
        //     if ($user->balanceFloat > 0) {
        //         $wallet_amount_used = $user->balanceFloat;
        //         if ($clientCurrency) {
        //             $wallet_amount_used = $user->balanceFloat * $clientCurrency->doller_compare;
        //         }
        //         if ($wallet_amount_used > $cart->total_payable_amount) {
        //             $wallet_amount_used = $cart->total_payable_amount;
        //         }
        //         $cart->total_payable_amount = $cart->total_payable_amount - $wallet_amount_used;
        //         $cart->wallet_amount_used = $wallet_amount_used;
        //     }
        // } 
        $cart->deliver_status = $delivery_status;
        $cart->loyalty_amount = $loyalty_amount_saved;
        $cart->tip = array(
            ['label' => '5%', 'value' => decimal_format(0.05 * $cart->total_payable_amount)],
            ['label' => '10%', 'value' => decimal_format(0.1 * $cart->total_payable_amount)],
            ['label' => '15%', 'value' => decimal_format(0.15 * $cart->total_payable_amount)]
        );
        $cart->vendor_details = $vendor_details;
        $cart->cart_dinein_table_id = $cart_dinein_table_id;
        $cart->upSell_products = ($upSell_products) ? $upSell_products->first() : collect();
        $cart->crossSell_products = ($crossSell_products) ? $crossSell_products->first() : collect();
        $cart->delay_date =  $delay_date??0;
        $cart->pickup_delay_date =  $pickup_delay_date??0;
        $cart->dropoff_delay_date =  $dropoff_delay_date??0;
        return $cart;
    }

     /**
     * Get Last added product variant
     *
     * @return \Illuminate\Http\Response
     */
    public function getLastAddedProductVariant(Request $request, $domain='')
    {
        try{
            $cartProduct = CartProduct::with('addon')
                ->where('cart_id', $request->cart_id)
                ->where('product_id', $request->product_id)
                ->orderByDesc('created_at')->first();

            return $this->successResponse($cartProduct, '', 200);
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Update Quantity
     *
     * @return \Illuminate\Http\Response
     */
    public function updateQuantity(Request $request, $domain = '')
    {
        try{
            if ($request->quantity < 1) {
                return $this->errorResponse(__('Quantity should not be less than 1'), 422);
            }
            $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
            $currId = ClientCurrency::where(['is_primary' => 1])->value('currency_id');
            $cart = TempCart::with(['address','currency','coupon.promo'])->where('user_id', $request->user_id)->where('id', $request->cart_id)->first();
            if (!$cart) {
                return $this->errorResponse(__('User cart not exist.'), 404);
            }
            $cartProduct = TempCartProduct::where('cart_id', $cart->id)->where('id', $request->cart_product_id)->first();
            if (!$cartProduct) {
                return $this->errorResponse(__('Product does not exist in cart.'), 404);
            }
            $cartProduct->quantity = $request->quantity;
            $cartProduct->save();
            $totalProducts = TempCartProduct::where('cart_id', $cart->id)->sum('quantity');
            $cart->item_count = $totalProducts;
            $cart->save();
            
            $cartData = $this->getCart($cart, $langId, $currId, '');
            return $this->successResponse($cartData, 'Cart updated successfully', 200);
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Delete Cart Product
     *
     * @return \Illuminate\Http\Response
     */
    // public function deleteCartProduct(Request $request, $domain = '')
    // {
    //     TempCartProduct::where('id', $request->cartproduct_id)->delete();
    //     TempCartCoupon::where('vendor_id', $request->vendor_id)->delete();
    //     TempCartAddon::where('cart_product_id', $request->cartproduct_id)->delete();
    //     return response()->json(['status' => 'success', 'message' => __('Product removed from cart successfully.') ]);
    // }

    /**
     * Empty Cart
     *
     * @return \Illuminate\Http\Response
     */
    public function emptyCartData(Request $request, $domain = '')
    {
        try{
            $cart_id = $request->cart_id;
            if (($cart_id != '') && ($cart_id > 0)) {
                TempCart::where('id', $cart_id)->delete();
                TempCartProduct::where('cart_id', $cart_id)->delete();
                TempCartCoupon::where('cart_id', $cart_id)->delete();
                TempCartAddon::where('cart_id', $cart_id)->delete();
                TempCartDeliveryFee::where('cart_id', $cart_id)->delete();
                return $this->successResponse('', 'Cart has been deleted successfully.', 200);
            } else {
                return $this->errorResponse('Cart cannot be deleted.', 422);
            }
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }


    /**
     * Delete Cart Product
     *
     * @return \Illuminate\Http\Response
     */
    public function removeItem(Request $request, $domain='')
    {
        try{
            $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
            $currId = ClientCurrency::where(['is_primary' => 1])->value('currency_id');
            $cart = TempCart::with(['address','currency','coupon.promo'])->where('id', $request->cart_id)->where('user_id', $request->user_id)->first();
            if (!$cart) {
                return $this->errorResponse(__('Cart not exist'), 404);
            }
            
            $totalProductCount = TempCartProduct::where('cart_id', $cart->id)->count();
            if ($totalProductCount < 2) {
                return $this->errorResponse(__('Cart can not be empty.'), 404);
            }        
            $cartProduct = TempCartProduct::where('cart_id', $cart->id)->where('id', $request->cart_product_id)->first();
            if (!$cartProduct) {
                return $this->errorResponse(__('Product does not exist in cart.'), 404);
            }
            $cartProduct->delete();
            $totalProducts = TempCartProduct::where('cart_id', $cart->id)->sum('quantity');
            $cart->item_count = $totalProducts;
            $cart->save();
            $cartData = $this->getCart($cart, $langId, $currId, '');
            return $this->successResponse($cartData, __("Product removed from cart successfully."), 200);
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }


    

    /**
     * get Cart Data
     *
     * @return \Illuminate\Http\Response
     */
    public function getCartData(Request $request, $domain = '')
    {
        $cart_details = [];
        $user = Auth::user();
        $curId = Session::get('customerCurrency');
        $langId = Session::get('customerLanguage');
        $address_id = 0;
        if ($user) {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time','schedule_pickup','schedule_dropoff','scheduled_slot')->with('coupon.promo')->where('status', '0')->where('user_id', $user->id)->first();
        } else {
            $cart = Cart::select('id', 'is_gift', 'item_count', 'schedule_type', 'scheduled_date_time','schedule_pickup','schedule_dropoff','scheduled_slot')->with('coupon.promo')->where('status', '0')->where('unique_identifier', session()->get('_token'))->first();
        }

        if (isset($request->address_id) && !empty($request->address_id)) {
            $address_id = $request->address_id;
            $address = UserAddress::where('user_id', $user->id)->update(['is_primary' => 0]);
            $address = UserAddress::where('user_id', $user->id)->where('id', $address_id)->update(['is_primary' => 1]);
        }
        
        if ($cart) {
            $cart_details = $this->getCart($cart, $address_id,$request->code);
        }
        $client_preference_detail = ClientPreference::first();

        return response()->json(['status' => 'success', 'cart_details' => $cart_details, 'client_preference_detail' => $client_preference_detail]);
    }


    # get delivery fee from dispatcher
    public function getDeliveryFeeDispatcher($vendor_id)
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $customer = User::find(Auth::id());
                $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
                if ($cus_address) {
                    $tasks = array();
                    $vendor_details = Vendor::find($vendor_id);
                    $location[] = array(
                        'latitude' => $vendor_details->latitude ?? 30.71728880,
                        'longitude' => $vendor_details->longitude ?? 76.80350870
                    );
                    $location[] = array(
                        'latitude' => $cus_address->latitude ?? 30.717288800000,
                        'longitude' => $cus_address->longitude ?? 76.803508700000
                    );
                    $postdata =  ['locations' => $location];
                    $client = new GClient([
                        'headers' => [
                            'personaltoken' => $dispatch_domain->delivery_service_key,
                            'shortcode' => $dispatch_domain->delivery_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);
                    $url = $dispatch_domain->delivery_service_key_url;
                    $res = $client->post(
                        $url . '/api/get-delivery-fee',
                        ['form_params' => ($postdata)]
                    );
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['message'] == 'success') {
                        return $response['total'];
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
    # check if last mile delivery on
    public function checkIfLastMileOn()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }

    // add ones add in cart for ondemand
    public function postAddToCartAddons(Request $request, $domain = '')
    {
        try {
            $user = Auth::user();
             $addon_ids = $request->addonID;
             $addon_options_ids = $request->addonoptID;
             $langId = Session::get('customerLanguage');

           $addonSets = $addon_ids = $addon_options = array();
            if($request->has('addonID')){
                $addon_ids = $request->addonID;
            }
            if($request->has('addonoptID')){
                $addon_options = $request->addonoptID;
            }
            foreach($addon_options as $key => $opt){
                $addonSets[$addon_ids[$key]][] = $opt;
            }

            if($request->has('addonoptID')){
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                ->where('ast.language_id', $langId)
                ->where('addon_sets.status', '!=', '2')
                ->where('addon_sets.id', $request->addonID[0])->first();
                if (!$addon) {
                    return response()->json(['error' => 'Invalid addon or delete by admin. Try again with remove some.'], 404);
                }
                if ($addon->min_select > count($request->addonID)) {
                    return response()->json([
                        'error' => 'Select minimum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
                if ($addon->max_select < count($request->addonID)) {
                    return response()->json([
                        'error' => 'You can select maximum ' . $addon->max_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
            }

            if(isset($addon_ids) && !empty($addon_ids[0]))
                CartAddon::where('cart_id',$request->cart_id)->where('cart_product_id',$request->cart_product_id)->where('addon_id',$addon_ids[0])->delete();
            else
                CartAddon::where('cart_id',$request->cart_id)->where('cart_product_id',$request->cart_product_id)->delete();

            if (count($addon_options) > 0) {
                $saveAddons = array();
                foreach ($addon_options as $key => $opts) {
                    $saveAddons[] = [
                    'option_id' => $opts,
                    'cart_id' => $request->cart_id,
                    'addon_id' => $addon_ids[$key],
                    'cart_product_id' => $request->cart_product_id,
                ];
                }
                CartAddon::insert($saveAddons);
            }

            return response()->json(['status' => 'success', 'message' => 'Addons Added Successfully!']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * send modified order notification to customer
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEditedOrderPushNotification($user_ids, $orderData)
    {
       // Log::info("sendEditedOrderPushNotification");

        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();
     
        //   // Log::info($devices);
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
      
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            $notification_content = NotificationTemplate::where('slug', 'order-modified-customer')->first();
          
            if ($notification_content) {
                $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => route('order.index'),
                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'data' => $orderData,
                        'type' => "order_modified"
                    ],
                    "priority" => "high"
                ];
               $res  = sendFcmCurlRequest($data,$client_preferences->fcm_server_key);
            }
        }
    }

    /**
     * submit cart if order edit is done
     *
     * @return \Illuminate\Http\Response
     */
    public function submitCart(Request $request, $domain = '')
    {
        try{
            $cart_id = $request->cart_id;
            if (($cart_id != '') && ($cart_id > 0)) {
                $cart = TempCart::with(['address','currency','coupon.promo'])->where('id', $cart_id)->first();
                $cart->update(['is_submitted' => 1]);
                $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
                $currId = ClientCurrency::where(['is_primary' => 1])->value('currency_id');
                $cartData = $this->getCart($cart, $langId, $currId, '');

                // Send notification to customer
                $order_vendor = OrderVendor::select('order_id', 'vendor_id')->where('id', $cart->order_vendor_id)->first();
                $order_id = $order_vendor->order_id;
                $vendor_id = $order_vendor->vendor_id;
                $orderController = new OrderController();
                $vendor_order_detail = $orderController->minimize_orderDetails_for_notification($order_id, $vendor_id);
                $this->sendEditedOrderPushNotification([$cart->user_id], $vendor_order_detail);
                

                return $this->successResponse($cartData, 'Order has been submitted successfully.', 200);
            } else {
                return $this->errorResponse('Order cannot be submitted.', 422);
            }
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    ////////////////// Put order in Cart /////////////////////
    public function getProductsInCart(Request $request, $domain='')
    {
        try{
            $order_vendor_id = $request->order_vendor_id;
            $order_vendor_product_id = $request->order_vendor_product_id ?? '';
           // pr( $order_vendor_product_id );
            $getallproduct = OrderVendor::with(['products' => function ($q) use ($order_vendor_product_id) {
                if($order_vendor_product_id){
                    $q->where('id', $order_vendor_product_id);
                }
            },'orderDetail','products.addon'])->where('id', $order_vendor_id)->first();
            $GetVendorReturnAmount = $this->GetVendorReturnAmount($request, $getallproduct->orderDetail);
   
            if(!$getallproduct){
                return $this->errorResponse('order Not flund',401); 
            }
            if(!$request->has('user_id')){
                $request->request->add(['user_id' => $getallproduct->user_id]);
            }
            $is_payment_done = 1;
            if ($getallproduct->orderDetail->payment_option_id == 1 && ($getallproduct->orderDetail->payable_amount >0)) {
                $is_payment_done = 0;
            }
            $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
            $currId = ClientCurrency::where(['is_primary' => 1])->value('currency_id');
            $cart = TempCart::where('status', '0')->where('user_id', $request->user_id)->where('order_vendor_id', $order_vendor_id)->where('is_approved', '!=', 1)->first();
            if(!$cart){
                foreach($getallproduct->products as $data){
                 
                    $request->request->add([
                        'set_temp_cart' => 1,
                        'vendor_id' => $data->vendor_id,
                        'product_id' => $data->product_id,
                        'quantity' => $data->quantity,
                        'variant_id' => $data->variant_id,
                        'dispatch_agent_id' => $data->dispatch_agent_id,
                        'dispatch_agent_price' => $data->price,
                        'is_payment_done' => $is_payment_done,
                        'vendor_wallet_amount_used' => $GetVendorReturnAmount['vendor_wallet_amount'],
                        'order_payable_amount' => $getallproduct->orderDetail->payable_amount,
                      

                    ]);
                   
                    $addonID = OrderProductAddon::where('order_product_id',$data->id)->pluck('addon_id');
                    $addonoptID = OrderProductAddon::where('order_product_id',$data->id)->pluck('option_id');
                    if(count($addonID)){
                        $request->request->add(['addonID' => $addonID->toArray()]);
                    }
                    if(count($addonoptID)){
                        $request->request->add(['addonoptID' => $addonoptID->toArray()]);
                    }
                    $this->postAddToTempCart($request);
                }
            }
            $cart = TempCart::with(['address','currency','coupon.promo'])->where('status', '0')->where('user_id', $request->user_id)->where('order_vendor_id', $order_vendor_id)->orderBy('id', 'desc')->first();
            $cartData = $this->getCart($cart, $langId, $currId, '');

            return $this->successResponse($cartData, 'Order added to cart.', 201);
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    public function postAddToTempCart(Request $request, $domain='')
    {
        $preference = ClientPreference::first();
        $luxury_option = LuxuryOption::where('title', 'delivery')->first();
        try {
            $cart_detail = [];
            $user_id = $request->user_id;
            $address_id = $request->address_id ?? 0;
            $order_vendor_id = $request->order_vendor_id ?? 0;
            $user = User::find($user_id);
            // $addon_ids = $request->addonID;
            // $addon_options_ids = $request->addonoptID;
            $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
            // $new_session_token = session()->get('_token');
            $client_currency = ClientCurrency::where('is_primary', '=', 1)->first();
            $user_id = $user ? $user->id : '';
            $variant_id = $request->variant_id;
            if ($user) {
                $cart_detail['user_id'] = $user_id;
                $cart_detail['created_by'] = $user_id;
            }
            $cart_detail = [
                'is_gift' => 1,
                'status' => '0',
                'item_count' => 0,
                'currency_id' => $client_currency->currency_id,
                'unique_identifier' => '', //!$user ? $new_session_token : '',
                'address_id' => $address_id,
                'order_vendor_id' => $order_vendor_id,
                'vendor_wallet_amount_used' => $request->vendor_wallet_amount_used,
                'order_payable_amount'      => $request->order_payable_amount
            ];
            if ($user) {
                $cart_detail = TempCart::updateOrCreate([
                    'user_id' => $user->id,
                    'is_submitted' => 0,
                    'is_approved' => 0,
                ], $cart_detail);
                $already_added_product_in_cart = TempCartProduct::where(["product_id" => $request->product_id, 'cart_id' => $cart_detail->id])->first();
            } else {
                return $this->errorResponse(__('Invalid user data'), 422);
            }
            $productDetail = Product::with([
                'variant' => function ($sel) use($variant_id) {
                    $sel->where('id', $variant_id);
                    $sel->groupBy('product_id');
                }
            ])->find($request->product_id);

            # if product type is not equal to on demand
            if(($productDetail->category->categoryDetail->type_id != 8)  && ($productDetail->sell_when_out_of_stock) == 0 && (!isset($request->set_temp_cart))){
                if(!empty($already_added_product_in_cart)){
                    if($productDetail->variant[0]->quantity <= $already_added_product_in_cart->quantity){
                        return $this->errorResponse(__('Maximum quantity already added in your cart s'), 422);
                    }
                    if($productDetail->variant[0]->quantity <= ($already_added_product_in_cart->quantity + $request->quantity)){
                        $request->quantity = $productDetail->variant[0]->quantity - $already_added_product_in_cart->quantity;
                    }
                }
                if($productDetail->variant[0]->quantity < $request->quantity){
                    if($productDetail->variant[0]->quantity <= 0){
                        $productDetail->variant[0]->quantity = 1;
                    }
                     $request->quantity = $productDetail->variant[0]->quantity;
                }
            }


            $addonSets = $addon_ids = $addon_options = array();
            if($request->has('addonID')){
                $addon_ids = $request->addonID;
            }
            if($request->has('addonoptID')){
                $addon_options = $request->addonoptID;
            }
            foreach($addon_options as $key => $opt){
                $addonSets[$addon_ids[$key]][] = $opt;
            }
            foreach($addonSets as $key => $value){
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                            ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                            ->where('ast.language_id', $langId)
                            ->where('addon_sets.status', '!=', '2')
                            ->where('addon_sets.id', $key)->first();
                if(!$addon){
                    return $this->errorResponse(__('Invalid addon or delete by admin. Try again with remove some.'), 422);
                }
                if($addon->min_select > count($value)){
                    return $this->errorResponse('Select minimum ' . $addon->min_select .' options of ' .$addon->title, 422);
                }
                if($addon->max_select < count($value)){
                    return $this->errorResponse('You can select maximum ' . $addon->min_select .' options of ' .$addon->title, 422);
                }
            }
            $oldquantity = $isnew = 0;
            $cart_product_detail = [
                'status'  => '0',
                'is_tax_applied'  => '1',
                'created_by'  => $user_id,
                'cart_id'  => $cart_detail->id,
                'quantity'  => $request->quantity,
                'vendor_id'  => $request->vendor_id,
                'product_id' => $request->product_id,
                'variant_id'  => $request->variant_id,
                'currency_id' => $client_currency->currency_id,
                'luxury_option_id' => ($luxury_option) ? $luxury_option->id : 0,
                'is_payment_done' => $request->is_payment_done ?? 0,
                'dispatch_agent_id' => $request->dispatch_agent_id,
                'dispatch_agent_price' => $request->dispatch_agent_price
            ];

            $checkVendorId = TempCartProduct::where('cart_id', $cart_detail->id)->where('vendor_id', '!=', $request->vendor_id)->first();

            if ($luxury_option) {
                $checkCartLuxuryOption = TempCartProduct::where('luxury_option_id', '!=', $luxury_option->id)->where('cart_id', $cart_detail->id)->first();
                if ($checkCartLuxuryOption) {
                    TempCartProduct::where('cart_id', $cart_detail->id)->delete();
                }
                if ($luxury_option->id == 2 || $luxury_option->id == 3) {
                    if ($checkVendorId) {
                        TempCartProduct::where('cart_id', $cart_detail->id)->delete();
                    }else{
                        $checkVendorTableAdded = TempCartProduct::where('cart_id', $cart_detail->id)->where('vendor_id', $request->vendor_id)->whereNotNull('vendor_dinein_table_id')->first();
                        $cart_product_detail['vendor_dinein_table_id'] = ($checkVendorTableAdded) ? $checkVendorTableAdded->vendor_dinein_table_id : NULL;
                    }
                }
            }
            if ( (isset($preference->isolate_single_vendor_order)) && ($preference->isolate_single_vendor_order == 1) ) {
                if ($checkVendorId) {
                    TempCartProduct::where('cart_id', $cart_detail->id)->delete();
                }
            }

            $cartProduct = TempCartProduct::where('product_id', $request->product_id)->where('variant_id', $request->variant_id)->where('cart_id', $cart_detail->id)->first();
            if(!$cartProduct){
                $isnew = 1;
            }else{
                $checkaddonCount = TempCartAddon::where('cart_product_id', $cartProduct->id)->count();
                if(count($addon_ids) != $checkaddonCount){
                    $isnew = 1;
                }else{
                    foreach ($addon_options as $key => $opts) {
                        $cart_addon = TempCartAddon::where('cart_product_id', $cartProduct->id)
                                    ->where('addon_id', $addon_ids[$key])
                                    ->where('option_id', $opts)->first();

                        if(!$cart_addon){
                            $isnew = 1;
                        }
                    }
                }
            }

            if($isnew == 1){
                $cartProduct = TempCartProduct::create($cart_product_detail);
                if(!empty($addon_ids) && !empty($addon_options)){
                    $saveAddons = array();
                    foreach ($addon_options as $key => $opts) {
                        $saveAddons[] = [
                            'option_id' => $opts,
                            'cart_id' => $cart_detail->id,
                            'addon_id' => $addon_ids[$key],
                            'cart_product_id' => $cartProduct->id,
                        ];
                    }
                    if(!empty($saveAddons)){
                        TempCartAddon::insert($saveAddons);
                    }
                }
            }else{
                $cartProduct->quantity = $cartProduct->quantity + $request->quantity;
                $cartProduct->save();
            }

            return $this->successResponse('', __('Product Added Successfully!'), 201);
        }
        catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * get Product detail by id
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductById(Request $request, $domain='', $pid)
    {
        try{
            // $user = Auth::user();
            $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
            // $userid = $user->id;
            $product = Product::with(['category.categoryDetail', 'category.categoryDetail.translation' => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                            ->where('category_translations.language_id', $langId);
                        },
                        'variant' => function($v){
                            $v->select('id', 'sku', 'product_id', 'title', 'quantity','price','barcode','tax_category_id')
                            ->groupBy('product_id'); // return first variant
                        },
                        'variant.media.pimage.image', 'vendor', 'media.image', 'related', 'upSell', 'crossSell',
                        'addOn' => function($q1) use($langId){
                            $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                            $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                            $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                            $q1->where('set.status', 1)->where('ast.language_id', $langId);
                        },
                        'variantSet' => function($z) use($langId){
                            $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                            $z->join('variant_translations as vt','vt.variant_id','vr.id');
                            $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                            $z->where('vt.language_id', $langId);
                        },
                        'variantSet.options' => function($zx) use($langId, $pid){
                            $zx->join('variant_option_translations as vt','vt.variant_option_id','variant_options.id')
                            ->select('variant_options.*', 'vt.title', 'pvs.product_variant_id', 'pvs.variant_type_id')
                            ->where('pvs.product_id', $pid)
                            ->where('vt.language_id', $langId);
                        }, 
                        'translation' => function($q) use($langId){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                            $q->where('language_id', $langId);
                        },
                        'addOn.setoptions' => function($q2) use($langId){
                            $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                            $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                            $q2->where('apt.language_id', $langId)->groupBy(['addon_options.id', 'apt.language_id']);
                        },
                        ])->select('id', 'sku', 'url_slug', 'weight', 'weight_unit', 'vendor_id', 'is_new', 'is_featured', 'is_physical', 'has_inventory', 'has_variant', 'sell_when_out_of_stock', 'requires_shipping', 'Requires_last_mile', 'averageRating','minimum_order_count','batch_count')
                        ->where('id', $pid)
                        ->first();

            if(!$product){
                return $this->errorResponse(__('No record found.'), 422);
            }
            $additionalPreference =  getAdditionalPreference(['is_service_product_price_from_dispatch']);
            $agent_id = $request->agent_id;
            if(($additionalPreference['is_service_product_price_from_dispatch'] ==1 )&& ( $request->product_price_from_dispatch ==1)){
                $is_service_product_price_from_dispatch = $additionalPreference['is_service_product_price_from_dispatch'];
            }
            
            $product->vendor->is_vendor_closed = 0;
            if( $is_service_product_price_from_dispatch !=1){ // no need to check slot 

                if($product->vendor->show_slot == 0){
                    if( ($product->vendor->slotDate->isEmpty()) && ($product->vendor->slot->isEmpty()) ){
                        $product->vendor->is_vendor_closed = 1;
                    }else{
                        $product->vendor->is_vendor_closed = 0;
                        if($product->vendor->slotDate->isNotEmpty()){
                            $product->vendor->opening_time = Carbon::parse($product->vendor->slotDate->first()->start_time)->format('g:i A');
                            $product->vendor->closing_time = Carbon::parse($product->vendor->slotDate->first()->end_time)->format('g:i A');
                        }elseif($product->vendor->slot->isNotEmpty()){
                            $product->vendor->opening_time = Carbon::parse($product->vendor->slot->first()->start_time)->format('g:i A');
                            $product->vendor->closing_time = Carbon::parse($product->vendor->slot->first()->end_time)->format('g:i A');
                        }
                    }
                }
            }

            $product->is_wishlist = $product->category->categoryDetail->show_wishlist;
            $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
            
            $addonList = array();
            foreach ($product->addOn as $key => $value) {
                foreach ($value->setoptions as $k => $v) {
                    if($v->price == 0){
                        $v->is_free = true;
                    }else{
                        $v->is_free = false;
                    }
                    $v->multiplier = $clientCurrency->doller_compare;
                }
            }
            $data_image = array();
            /*  if variant has image return variant images else product images  */
            $variant_id = 0;
            foreach ($product->variant as $key => $value) {
              
                $value->multiplier = $clientCurrency->doller_compare;
                if($is_service_product_price_from_dispatch ==1){
                    $actual_price =0.0;
                    $Agent_price =  $this->getAgentProductPriceFromDispatcher(  $value->sku,$agent_id);
                    if($Agent_price){
                        $actual_price = $Agent_price['data'] ? $Agent_price['data']['price'] : 0.0;
                    }
                   $value['price'] =  $actual_price; 
                }
                $variant_id = $value->id;
                if($product->sell_when_out_of_stock == 1){
                    $value->stock_check = '1';
                }elseif($value->quantity > 0){
                    $value->stock_check = '1';
                }else{
                    $value->stock_check = 0;
                }
                if($value->media && count($value->media) > 0){
                    foreach ($value->media as $media_key => $media_value) {
                        $data_image[$media_key]['product_variant_id'] = $media_value->product_variant_id;
                        $data_image[$media_key]['media_id'] = $media_value->product_image_id;
                        $data_image[$media_key]['is_default'] = 0;
                        $data_image[$media_key]['image'] = $media_value->pimage->image;
                    }
                }else{
                    foreach ($product->media as $media_key => $media_value) {
                        $data_image[$media_key]['product_id'] = $media_value->product_id;
                        $data_image[$media_key]['media_id'] = $media_value->media_id;
                        $data_image[$media_key]['is_default'] = $media_value->is_default;
                        $data_image[$media_key]['image'] = $media_value->image;
                    }
                }
            }
            if($product->variantSet){
                foreach ($product->variantSet as $set_key => $set_value) {
                    foreach ($set_value->options as $opt_key => $opt_value) {
                        $opt_value->value = $opt_value->product_variant_id == $variant_id ? true : false;
                    }
                }
            }
            $product->product_media = $data_image;
            $response['products'] = $product;
            $response['relatedProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'relate', $product->related);
            $response['upSellProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'upSell', $product->upSell);
            $response['crossProducts'] = $this->metaProduct($langId, $clientCurrency->doller_compare, 'cross', $product->crossSell);
            /* group by in query return data only for key - 0 so using 0 */
            if(isset($product->variant[0]->media) && !empty($product->variant[0]->media)){
                unset($product->variant[0]->media);
            }
            $product->is_service_product_price_from_dispatch = (int)$is_service_product_price_from_dispatch;
            unset($product->related);
            unset($product->media);
            unset($product->upSell);
            unset($product->crossSell);
            $response['products'] = $product;
            return $this->successResponse($response, '', 200);
        }
        catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get cart product details with addon for edit
     *
     * @return \Illuminate\Http\Response
     */
    public function getCartProductDetailWithAddons(Request $request, $domain = '')
    {
        try{
            $cart_id = $request->cart_id;
            $cart_product_id = $request->cart_product_id;

            $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
            $currId = ClientCurrency::where(['is_primary' => 1])->value('currency_id');
            $cart = TempCart::with(['address','currency','coupon.promo'])->where('id', $cart_id)->first();
            if (!$cart) {
                return $this->errorResponse(__('User cart not exist.'), 404);
            }
            $cartProduct = TempCartProduct::with(['addon'])
            ->where('cart_id', $cart_id)
            ->where('id', $cart_product_id)->first();
            if (!$cartProduct) {
                return $this->errorResponse(__('Product does not exist in cart.'), 404);
            }

            $product_id = $cartProduct->product_id;
            $product = $this->getProductById($request, '', $product_id)->getData();
            $product_detail = null;
            if($product->status == 'Success'){
                $product_detail = $product->data;
            }

            $data['cart_product_detail'] = $cartProduct;
            $data['product_detail'] = $product_detail->products;
            
            return $this->successResponse($data, '', 200);
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Update cart product with Addons & Quantity
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProductAddonsAndQuantity(Request $request, $domain = '')
    {
        try{
            $user_id = $request->user_id;
            $cart_id = $request->cart_id;
            $quantity = $request->quantity;
            $cart_product_id = $request->cart_product_id;

            $addonSets = $addon_ids = $addon_options = array();
            if($request->has('addonID')){
                $addon_ids = $request->addonID;
            }
            if($request->has('addonoptID')){
                $addon_options = $request->addonoptID;
            }

            if ($quantity < 1) {
                return $this->errorResponse(__('Quantity should not be less than 1'), 422);
            }
            $langId = ClientLanguage::where(['is_primary' => 1, 'is_active' => 1])->value('language_id');
            $currId = ClientCurrency::where(['is_primary' => 1])->value('currency_id');
            $cart = TempCart::with(['address','currency','coupon.promo'])->where('user_id', $user_id)->where('id', $cart_id)->first();
            if (!$cart) {
                return $this->errorResponse(__('User cart not exist.'), 404);
            }
            $cartProduct = TempCartProduct::where('cart_id', $cart_id)->where('id', $cart_product_id)->first();
            if (!$cartProduct) {
                return $this->errorResponse(__('Product does not exist in cart.'), 404);
            }
            $cartProduct->quantity = $quantity;
            $cartProduct->save();

            
            foreach($addon_options as $key => $opt){
                $addonSets[$addon_ids[$key]][] = $opt;
            }
            foreach($addonSets as $key => $value){
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                            ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                            ->where('ast.language_id', $langId)
                            ->where('addon_sets.status', '!=', '2')
                            ->where('addon_sets.id', $key)->first();
                if(!$addon){
                    return $this->errorResponse(__('Invalid addon or delete by admin. Try again with remove some.'), 422);
                }
                if($addon->min_select > count($value)){
                    return $this->errorResponse('Select minimum ' . $addon->min_select .' options of ' .$addon->title, 422);
                }
                if($addon->max_select < count($value)){
                    return $this->errorResponse('You can select maximum ' . $addon->min_select .' options of ' .$addon->title, 422);
                }
            }
            $productAddonIds = [];
            foreach ($addon_options as $key => $opts) {
                $checkaddon = TempCartAddon::updateOrCreate(
                    ['cart_id' => $cart->id, 'cart_product_id' => $cartProduct->id, 'addon_id' => $addon_ids[$key],'option_id' => $opts]
                );
                $productAddonIds[] = $checkaddon->id;
            }
            TempCartAddon::where(['cart_id' => $cart_id, 'cart_product_id' => $cartProduct->id])
            ->whereNotIn('id', $productAddonIds)->delete();
            
            $totalProducts = TempCartProduct::where('cart_id', $cart_id)->sum('quantity');
            $cart->item_count = $totalProducts;
            $cart->save();
            
            $cartData = $this->getCart($cart, $langId, $currId, '');
            return $this->successResponse($cartData, 'Cart updated successfully', 200);
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }


    public function vendorProductsSearchResults(Request $request, $domain = '')
    {
       // return 1;
        try {
            $keyword = $request->input('keyword');
            $vid = $request->input('vendor');

            $limit = $request->has('limit') ? $request->limit : 10;
            $page = $request->has('page') ? $request->page : 1;

            $clientLanguage = ClientLanguage::where('is_primary', 1)->first();
            $langId = $clientLanguage ? $clientLanguage->language_id : 1;

            $response = array();
            
            $products = Product::with(['media.image',
            'translation' => function($q) use($langId, $keyword){
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                if($keyword){
                    $q->where(function ($q1) use ($keyword) {
                        $q1->where('title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('body_html', 'LIKE', '%' . $keyword . '%');
                    });
                }
                $q->groupBy('product_id');
            }])
            ->select('id', 'sku', 'title', 'description', 'category_id', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only');
            if($keyword){
                $products = $products->where(function ($q) use ($keyword, $langId) {
                    $q->where(function ($q1) use ($keyword) {
                        $q1->where('sku', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('url_slug', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('title', 'LIKE', '%' . $keyword . '%');
                    });
                    $q->orWhereHas('translation', function ($q1) use ($keyword, $langId) {
                        $q1->where(function ($q2) use ($keyword) {
                            $q2->where('title', 'LIKE', '%' . $keyword . '%');
                        });
                    });
                });                
            }
            
            $products = $products->where('is_live', 1)
                ->where('vendor_id', $vid)
                ->whereNull('products.deleted_at')
                ->paginate($limit, $page);
            foreach ($products as $product) {
                // $product->response_type = 'product';
                $product->image_url = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
                $response[] = $product;
            }
            return $this->successResponse($response);
        }
        catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function AgentProductsSearchResults(Request $request, $domain = '')
    {
        // $orderController = new OrderController();
        // $vendor_order_detail = $orderController->minimize_orderDetails_for_notification('80', '9');
       
        // pr($this->sendEditedOrderPushNotification(['2'], $vendor_order_detail));
       // return 1;
      // pr($request->all());
        try {
            $keyword = $request->input('keyword');
            $vid = $request->input('vendor');
            $productSku = $request->input('productSku')?? [];
            $agent_id = $request->input('agent_id');
            $limit = $request->has('limit') ? $request->limit : 10;
            $page  = $request->has('page') ? $request->page : 1;
          
            $clientLanguage = ClientLanguage::where('is_primary', 1)->first();
            $langId = $clientLanguage ? $clientLanguage->language_id : 1;
            if($productSku){
                $productSku = explode(',',$productSku);
            }

            $response = array();
             
            $products = Product::with(['media.image','variants' => function($q) use($productSku){
                $q->whereIn('sku', $productSku);
            },
            'translation' => function($q) use($langId, $keyword){
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                if($keyword){
                    $q->where(function ($q1) use ($keyword) {
                        $q1->where('title', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('body_html', 'LIKE', '%' . $keyword . '%');
                    });
                }
                $q->groupBy('product_id');
            }])
            ->select('id', 'sku', 'title', 'description', 'category_id', 'requires_shipping', 'sell_when_out_of_stock', 'url_slug', 'weight_unit', 'weight', 'vendor_id', 'has_variant', 'has_inventory', 'Requires_last_mile', 'averageRating', 'inquiry_only');
            if($keyword){
                $products = $products->where(function ($q) use ($keyword, $langId) {
                    $q->where(function ($q1) use ($keyword) {
                        $q1->where('sku', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('url_slug', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('title', 'LIKE', '%' . $keyword . '%');
                    });
                    $q->orWhereHas('translation', function ($q1) use ($keyword, $langId) {
                        $q1->where(function ($q2) use ($keyword) {
                            $q2->where('title', 'LIKE', '%' . $keyword . '%');
                        });
                    });
                });                
            }
            $products = $products->whereHas('variants' , function($q) use($productSku){
                $q->whereIn('sku', $productSku);
            });
            $products = $products->where('is_live', 1)
                //->whereIn('sku', $productSku)
                ->whereNull('products.deleted_at')
                ->paginate($limit, $page);
            foreach ($products as $product) {
                // foreach ($product->variants as $variant) {
                    
                //     $Agent_price =  $this->getAgentProductPriceFromDispatcher(  $variant->sku,$agent_id);
                //     if($Agent_price){
                //         $actual_price = $Agent_price['data'] ? $Agent_price['data']['price'] : 0.0;
                //         $variant['agent_price'] =  $actual_price;
                //     }

                // }
                $product->image_url = ($product->media->isNotEmpty()) ? $product->media->first()->image->path['image_fit'] . '300/300' . $product->media->first()->image->path['image_path'] : '';

                $response[] = $product;
            }
           
            return $this->successResponse($response);
        }
        catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Get Cart Items
     *
     */
    public function getCartForApproval($cart, $order, $langId = '1', $currency = '1', $type = 'delivery')
    {
        $additionalPreference =  getAdditionalPreference(['is_service_product_price_from_dispatch']);
     
        if(($additionalPreference['is_service_product_price_from_dispatch'] ==1 )&& ( $order->luxury_option_id ==6)){ // luxury_option_id for ondemand 
            $is_service_product_price_from_dispatch = $additionalPreference['is_service_product_price_from_dispatch'];
        }
        $preferences = ClientPreference::first();
        $clientCurrency = ClientCurrency::where('currency_id', $currency)->first();
        if (!$cart) {
            return false;
        }
        $vondorCnt = 0;
        $address = [];
        $latitude = '';
        $longitude = '';
        $address_id = 0;
        $delivery_status = 1;
        $cartID = $cart->id;
        $upSell_products = collect();
        $crossSell_products = collect();
        $delifproductnotexist = TempCartProduct::where('cart_id', $cartID)->doesntHave('product')->delete();
        $cartData = TempCartProduct::with([
            'vendor', 'coupon' => function ($qry) use ($cartID) {
                $qry->where('cart_id', $cartID);
            }, 'coupon.promo.details', 'vendorProducts.pvariant.media.image', 'vendorProducts.product.media.image',
            'vendorProducts.pvariant.vset.variantDetail.trans' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.pvariant.vset.optionData.trans' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.product.translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                $q->where('language_id', $langId);
            },
            'vendorProducts' => function ($qry) use ($cartID) {
                $qry->where('cart_id', $cartID);
            },
            'vendorProducts.addon.set' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.addon.option' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            }, 'vendorProducts.product.taxCategory.taxRate',
        ])->select('vendor_id', 'vendor_dinein_table_id')->where('cart_id', $cartID)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();
        $loyalty_amount_saved = 0;
        $subscription_features = array();
        if ($cart->user_id) {
            $now = Carbon::now()->toDateTimeString();
            $user_subscription = SubscriptionInvoicesUser::with('features')
                ->select('id', 'user_id', 'subscription_id')
                ->where('user_id', $cart->user_id)
                ->where('end_date', '>', $now)
                ->orderBy('end_date', 'desc')->first();
            if ($user_subscription) {
                foreach ($user_subscription->features as $feature) {
                    $subscription_features[] = $feature->feature_id;
                }
            }
            $user = User::find($cart->user_id);
            $cart->scheduled_date_time = !empty($cart->scheduled_date_time) ? convertDateTimeInTimeZone($cart->scheduled_date_time, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $cart->schedule_pickup = !empty($cart->schedule_pickup) ? convertDateTimeInTimeZone($cart->schedule_pickup, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $cart->schedule_dropoff = !empty($cart->schedule_dropoff) ? convertDateTimeInTimeZone($cart->schedule_dropoff, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $address = UserAddress::where('user_id', $cart->user_id)->where('is_primary', 1)->first();
            $address_id = ($address) ? $address->id : 0;
        }
        $latitude = ($address) ? $address->latitude : '';
        $longitude = ($address) ? $address->longitude : '';
        $total_payable_amount = $total_subscription_discount = $total_discount_amount = $total_discount_percent = $total_taxable_amount = 0.00;
        $total_tax = $total_paying = $total_disc_amount = 0.00;
        $item_count = 0;
        $total_delivery_amount = 0;
        $order_sub_total = 0;
        if ($cartData) {
            $cart_dinein_table_id = NULL;
            $action = $type;
            $vendor_details = [];
            $tax_details = [];
            $is_vendor_closed = 0;
            $delay_date = 0;
            $pickup_delay_date = 0;
            $dropoff_delay_date = 0;
            $total_service_fee = 0;
            $product_out_of_stock = 0;
            foreach ($cartData as $ven_key => $vendorData) {
                $is_promo_code_available = 0;
                $vendor_products_total_amount = $codeApplied = $is_percent = $proSum = $proSumDis = $taxable_amount = $subscription_discount = $discount_amount = $discount_percent = $deliver_charge = $delivery_fee_charges = 0.00;
                $delivery_count = 0;

                $cart_dinein_table_id = $vendorData->vendor_dinein_table_id;

                if (($action != 'delivery') && (@$is_service_product_price_from_dispatch!=1))  {
                    $vendor_details['vendor_address'] = $vendorData->vendor->select('id', 'latitude', 'longitude', 'address')->where('id', $vendorData->vendor_id)->first();
                    if ($action == 'dine_in') {
                        $vendor_tables = VendorDineinTable::where('vendor_id', $vendorData->vendor_id)->with('category')->get();
                        foreach ($vendor_tables as $vendor_table) {
                            $vendor_table->qr_url = url('/vendor/' . $vendorData->vendor->slug . '/?id=' . $vendorData->vendor_id . '&name=' . $vendorData->vendor->name . '&table=' . $vendor_table->id);
                        }
                        $vendor_details['vendor_tables'] = $vendor_tables;
                    }
                } else {
                    if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)) {
                        if ($address_id > 0) {

                            if (!empty($latitude) && !empty($longitude)) {
                                $serviceArea = $vendorData->vendor->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                                    $query->select('vendor_id')
                                    ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                                })->where('id', $vendorData->vendor_id)->get();
                            }
                        }
                    }
                }

                $ttAddon = $payable_amount = $is_coupon_applied = $coupon_removed = 0;
                $coupon_removed_msg = '';
                $deliver_charge = 0;
                $delivery_fee_charges = 0.00;
                $couponData = $couponProducts = array();
                if (!empty($vendorData->coupon->promo) && ($vendorData->coupon->vendor_id == $vendorData->vendor_id)) {
                    $now = Carbon::now()->toDateTimeString();
                    $minimum_spend = 0;
                    if (isset($vendorData->coupon->promo->minimum_spend)) {
                        $minimum_spend = $vendorData->coupon->promo->minimum_spend * $clientCurrency->doller_compare;
                    }
                    if ($vendorData->coupon->promo->expiry_date < $now) {
                        $coupon_removed = 1;
                        $coupon_removed_msg = 'Coupon code is expired.';
                    } else {
                        $couponData['coupon_id'] =  $vendorData->coupon->promo->id;
                        $couponData['name'] =  $vendorData->coupon->promo->name;
                        $couponData['disc_type'] = ($vendorData->coupon->promo->promo_type_id == 1) ? 'Percent' : 'Amount';
                        $couponData['expiry_date'] =  $vendorData->coupon->promo->expiry_date;
                        $couponData['allow_free_delivery'] =  $vendorData->coupon->promo->allow_free_delivery;
                        $couponData['minimum_spend'] =  $vendorData->coupon->promo->minimum_spend;
                        $couponData['first_order_only'] = $vendorData->coupon->promo->first_order_only;
                        $couponData['restriction_on'] = ($vendorData->coupon->promo->restriction_on == 1) ? 'Vendor' : 'Product';

                        $is_coupon_applied = 1;
                        if ($vendorData->coupon->promo->promo_type_id == 1) {
                            $is_percent = 1;
                            $discount_percent = round($vendorData->coupon->promo->amount);
                        } else {
                            $discount_amount = $vendorData->coupon->promo->amount * $clientCurrency->doller_compare;
                        }
                        if ($vendorData->coupon->promo->restriction_on == 0) {
                            foreach ($vendorData->coupon->promo->details as $key => $value) {
                                $couponProducts[] = $value->refrence_id;
                            }
                        }
                    }
                }

                foreach ($vendorData->vendorProducts as $pkey => $prod) {
                    if(isset($prod->product) && !empty($prod->product)){

                        if($prod->product->sell_when_out_of_stock == 0){
                            $quantity_check = productvariantQuantity($prod->variant_id);
                            if($quantity_check < $prod->quantity ){
                                $delivery_status=0;
                                $product_out_of_stock = 1;
                            }
                        }
                        $prod->product_out_of_stock =  $product_out_of_stock;

                        $price_in_currency = $price_in_doller_compare = $pro_disc = $quantity_price = 0;
                        $variantsData = $taxData = $vendorAddons = array();
                        $divider = (empty($prod->doller_compare) || $prod->doller_compare < 0) ? 1 : $prod->doller_compare;
                        $pvariantprice =  $prod->pvariant ? $prod->pvariant->price : 0;


                        $price_in_currency =   $pvariantprice;
                        $price_in_doller_compare = $price_in_currency * $clientCurrency->doller_compare;
                        if( $is_service_product_price_from_dispatch==1){
                            $price_in_currency =   $price_in_doller_compare =  $prod->dispatch_agent_price;
                        }
                      
                        $quantity_price = $price_in_doller_compare * $prod->quantity;
                        $item_count = $item_count + $prod->quantity;
                        $proSum = $proSum + $quantity_price;
                        $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price;
                       
                        if (isset($prod->pvariant->image->imagedata) && !empty($prod->pvariant->image->imagedata)) {
                            $prod->cartImg = $prod->pvariant->image->imagedata;
                        } else {
                            $prod->cartImg = (isset($prod->product->media[0]) && !empty($prod->product->media[0])) ? $prod->product->media[0]->image : '';
                        }

                        if($prod->product->delay_hrs_min != 0){
                            if($prod->product->delay_hrs_min > $delay_date)
                            $delay_date = $prod->product->delay_hrs_min;
                        }
                        if($prod->product->pickup_delay_hrs_min != 0){
                            if($prod->product->pickup_delay_hrs_min > $delay_date)
                            $pickup_delay_date = $prod->product->pickup_delay_hrs_min;
                        }

                        if($prod->product->dropoff_delay_hrs_min != 0){
                            if($prod->product->dropoff_delay_hrs_min > $delay_date)
                            $dropoff_delay_date = $prod->product->dropoff_delay_hrs_min;
                        }

                        if ($prod->pvariant) {
                            $variantsData['price']              = $price_in_currency;
                            $variantsData['id']                 = $prod->pvariant->id;
                            $variantsData['sku']                = ucfirst($prod->pvariant->sku);
                            $variantsData['title']              = $prod->pvariant->title;
                            $variantsData['barcode']            = $prod->pvariant->barcode;
                            $variantsData['product_id']         = $prod->pvariant->product_id;
                            $variantsData['multiplier']         = $clientCurrency->doller_compare;
                            $variantsData['gross_qty_price']    = $price_in_doller_compare * $prod->quantity;
                            if (!empty($vendorData->coupon->promo) && ($vendorData->coupon->promo->restriction_on == 0) && in_array($prod->product_id, $couponProducts)) {
                                $pro_disc = $discount_amount;
                                if ($minimum_spend <= $quantity_price) {
                                    if ($is_percent == 1) {
                                        $pro_disc = ($quantity_price * $discount_percent) / 100;
                                    }
                                    $quantity_price = $quantity_price - $pro_disc;
                                    $proSumDis = $proSumDis + $pro_disc;
                                    if ($quantity_price < 0) {
                                        $quantity_price = 0;
                                    }
                                    $codeApplied = 1;
                                } else {
                                    $variantsData['coupon_msg'] = "Spend Minimum " . $minimum_spend . " to apply this coupon";
                                    $variantsData['coupon_not_appiled'] = 1;
                                }
                            }

                            $variantsData['discount_amount'] = $pro_disc;
                            $variantsData['coupon_applied'] = $codeApplied;
                            $variantsData['quantity_price'] = $quantity_price;
                            $payable_amount = $payable_amount + $quantity_price;
                            if (!empty($prod->product->taxCategory) && count($prod->product->taxCategory->taxRate) > 0) {
                                foreach ($prod->product->taxCategory->taxRate as $tckey => $tax_value) {
                                    $rate = round($tax_value->tax_rate);
                                    $tax_amount = ($price_in_doller_compare * $rate) / 100;
                                    $product_tax = $quantity_price * $rate / 100;
                                    $taxData[$tckey]['rate'] = $rate;
                                    $taxData[$tckey]['tax_amount'] = $tax_amount;
                                    $taxData[$tckey]['product_tax'] = $product_tax;
                                    $taxable_amount = $taxable_amount + $product_tax;
                                    $taxData[$tckey]['sku'] = ucfirst($prod->pvariant->sku);
                                    $taxData[$tckey]['identifier'] = $tax_value->identifier;
                                    $tax_details[] = array(
                                        'rate' => $rate,
                                        'tax_amount' => $tax_amount,
                                        'identifier' => $tax_value->identifier,
                                        'sku' => ucfirst($prod->pvariant->sku),
                                    );
                                }
                            }
                            $prod->taxdata = $taxData;
                            if (($action == 'delivery') &&( $is_service_product_price_from_dispatch!=1)) {
                                if (!empty($prod->product->Requires_last_mile) && ($prod->product->Requires_last_mile == 1)) {
                                    $deliver_charge = $this->getDeliveryFeeDispatcher($vendorData->vendor_id);
                                    if (!empty($deliver_charge) && $delivery_count == 0) {
                                        $delivery_count = 1;
                                        $prod->deliver_charge = decimal_format($deliver_charge);
                                        $payable_amount = $payable_amount + $deliver_charge;
                                        $order_sub_total = $order_sub_total + $deliver_charge;
                                        $delivery_fee_charges = $deliver_charge;
                                    }
                                }
                            }
                            if (!empty($prod->addon)) {
                                foreach ($prod->addon as $ck => $addons) {
                                    $opt_quantity_price = 0;
                                    $opt_price_in_currency = $addons->option ? $addons->option->price : 0;
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                    $opt_quantity_price = $opt_price_in_doller_compare * $prod->quantity;
                                    $vendorAddons[$ck]['quantity'] = $prod->quantity;
                                    $vendorAddons[$ck]['addon_id'] = $addons->addon_id;
                                    $vendorAddons[$ck]['option_id'] = $addons->option_id;
                                    $vendorAddons[$ck]['price'] = $opt_price_in_currency;
                                    $vendorAddons[$ck]['addon_title'] = $addons->set->title;
                                    $vendorAddons[$ck]['quantity_price'] = $opt_quantity_price;
                                    $vendorAddons[$ck]['option_title'] = $addons->option ? $addons->option->title : 0;
                                    $vendorAddons[$ck]['price_in_cart'] = $addons->option->price;
                                    $vendorAddons[$ck]['cart_product_id'] = $addons->cart_product_id;
                                    $vendorAddons[$ck]['multiplier'] = $clientCurrency->doller_compare;
                                    $ttAddon = $ttAddon + $opt_quantity_price;
                                    $payable_amount = $payable_amount + $opt_quantity_price;
                                    $order_sub_total = $order_sub_total + $opt_quantity_price;
                                }
                            }
                            unset($prod->addon);
                            unset($prod->pvariant);
                        }
                        $variant_options = [];
                        if ($prod->pvariant) {
                            foreach ($prod->pvariant->vset as $variant_set_option) {
                                $variant_options[] = array(
                                    'option' => $variant_set_option->optionData->trans->title,
                                    'title' => $variant_set_option->variantDetail->trans->title,
                                );
                            }
                        }
                        $prod->variants = $variantsData;
                        $prod->variant_options = $variant_options;
                        $prod->product_addons = $vendorAddons;

                        $product = Product::with([
                            'variant' => function ($sel) {
                                $sel->groupBy('product_id');
                            },
                            'variant.media.pimage.image', 'upSell', 'crossSell', 'vendor', 'media.image', 'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                                $q->where('language_id', $langId);
                            }])->select('id', 'sku', 'inquiry_only', 'url_slug', 'weight', 'weight_unit', 'vendor_id', 'has_variant', 'has_inventory', 'averageRating','minimum_order_count','batch_count')
                            ->where('url_slug', $prod->product->url_slug)
                            ->first();
                        $doller_compare = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
                        $up_prods = $this->metaProduct($langId, $doller_compare, 'upSell', $product->upSell);
                        if($up_prods){
                            $upSell_products->push($up_prods);
                        }
                        $cross_prods = $this->metaProduct($langId, $doller_compare, 'crossSell', $product->crossSell);
                        if($cross_prods){
                            $crossSell_products->push($cross_prods);
                        }
                    }
                }

                $couponApplied = 0;
                if (!empty($vendorData->coupon->promo) && ($vendorData->coupon->promo->restriction_on == 1)) {
                    $minimum_spend = $vendorData->coupon->promo->minimum_spend * $clientCurrency->doller_compare;
                    if ($minimum_spend < $proSum) {
                        if ($is_percent == 1) {
                            $discount_amount = ($proSum * $discount_percent) / 100;
                        }
                        $couponApplied = 1;
                    } else {
                        $vendorData->coupon_msg = "To apply coupon minimum spend should be greater than " . $minimum_spend . '.';
                        $vendorData->coupon_not_appiled = 1;
                    }
                }


                $deliver_charge = $deliver_charge * $clientCurrency->doller_compare;
                $vendorData->proSum = $proSum;
                $vendorData->addonSum = $ttAddon;
                $vendorData->deliver_charge = $deliver_charge;
                $total_delivery_amount += $deliver_charge;
                $vendorData->coupon_apply_on_vendor = $couponApplied;
                $vendorData->is_coupon_applied = $is_coupon_applied;
                if (empty($couponData)) {
                    $vendorData->couponData = NULL;
                } else {
                    $vendorData->couponData = $couponData;
                }
                $vendor_service_fee_percentage_amount = 0;
                if($vendorData->vendor->service_fee_percent > 0){
                    $vendor_service_fee_percentage_amount = ($vendor_products_total_amount * $vendorData->vendor->service_fee_percent) / 100 ;
                     $payable_amount = $payable_amount + $vendor_service_fee_percentage_amount;
                }
                $total_service_fee = $total_service_fee + $vendor_service_fee_percentage_amount;
                $vendorData->service_fee_percentage_amount = decimal_format($vendor_service_fee_percentage_amount);
                $vendorData->vendor_gross_total = $payable_amount;
                $vendorData->discount_amount = $discount_amount;
                $vendorData->discount_percent = $discount_percent;
                $vendorData->taxable_amount = $taxable_amount;
                $vendorData->payable_amount = $payable_amount - $discount_amount;
               
                $vendorData->isDeliverable = 1;
                $total_paying = $total_paying + $payable_amount;
                $total_tax = $total_tax + $taxable_amount;
                $total_disc_amount = $total_disc_amount + $discount_amount;
                $total_discount_percent = $total_discount_percent + $discount_percent;
                $vendorData->vendor->is_vendor_closed = $is_vendor_closed;
                if (!empty($vendorData->coupon->promo)) {
                    unset($vendorData->coupon->promo);
                }

                if (in_array(1, $subscription_features)) {
                    $subscription_discount = $subscription_discount + $deliver_charge;
                }
                $total_subscription_discount = $total_subscription_discount + $subscription_discount;
                if (isset($serviceArea)) {
                    if ($serviceArea->isEmpty()) {
                        $vendorData->isDeliverable = 0;
                        $delivery_status = 0;
                    }
                }
                if ($vendorData->vendor->show_slot == 0) {
                    if (($vendorData->vendor->slotDate->isEmpty()) && ($vendorData->vendor->slot->isEmpty())) {
                        $vendorData->vendor->is_vendor_closed = 1;
                        if ($delivery_status != 0) {
                            $delivery_status = 0;
                        }
                    } else {
                        $vendorData->vendor->is_vendor_closed = 0;
                    }
                }
                if(($vendorData->vendor->$action == 0)  &&( $is_service_product_price_from_dispatch!=1)){
                    $vendorData->is_vendor_closed = 1;
                    $delivery_status = 0;
                }

                $order_sub_total = $order_sub_total + $vendor_products_total_amount;

                if((float)($vendorData->vendor->order_min_amount) > $payable_amount){  # if any vendor total amount of order is less then minimum order amount
                    $delivery_status = 0;
                }
                $promoCodeController = new PromoCodeController();
                $promoCodeRequest = new Request();
                $promoCodeRequest->setMethod('POST');
                $promoCodeRequest->request->add(['vendor_id' => $vendorData->vendor_id, 'cart_id' => $cartID]);
                $promoCodeResponse = $promoCodeController->postPromoCodeList($promoCodeRequest)->getData();
                if($promoCodeResponse->status == 'Success'){
                    if($promoCodeResponse->data){
                        $is_promo_code_available = 1;
                    }
                }
                $vendorData->is_promo_code_available = $is_promo_code_available;
            }
            ++$vondorCnt;
        }//End cart Vendor loop

        $cart_product_luxury_id = TempCartProduct::where('cart_id', $cartID)->select('luxury_option_id', 'vendor_id')->first();
        if ($cart_product_luxury_id) {
            if ($cart_product_luxury_id->luxury_option_id == 2 || $cart_product_luxury_id->luxury_option_id == 3) {
                $vendor_address = Vendor::where('id', $cart_product_luxury_id->vendor_id)->select('address')->first();
                $cart->address = $vendor_address->address;
            }
        }
        if (!empty($subscription_features)) {
            $total_disc_amount = $total_disc_amount + $total_subscription_discount;
            $cart->total_subscription_discount = $total_subscription_discount * $clientCurrency->doller_compare;
        }

        if($cartData->count() == '1' && ($is_service_product_price_from_dispatch!=1) ){
            $vendorId = $cartData[0]->vendor_id;
            //type must be a : delivery , takeaway,dine_in
            $duration = Vendor::where('id',$vendorId)->select('slot_minutes')->first();
            $slots = showSlotTemp('',$vendorId, $cart->user_id, 'delivery',$duration->slot_minutes);
            $cart->slots = $slots;
           // $cart->vendor_id =  $vendorId;
        }else{
            $slots = [];
            $cart->slots = [];
            //$cart->vendor_id =  0;
        }

        $cart->total_service_fee = decimal_format($total_service_fee);
        $cart->total_tax = $total_tax;
        $cart->tax_details = $tax_details;
        // $cart->gross_paybale_amount = $total_paying;
        $cart->gross_paybale_amount = $order_sub_total;
        $cart->total_discount_amount = $total_disc_amount * $clientCurrency->doller_compare;
        $cart->products = $cartData;
        $cart->item_count = $item_count;
        $temp_total_paying = $total_paying  + $total_tax - $total_disc_amount;
        // if ($cart->user_id > 0) {
        //     $loyalty_amount_saved = $this->getLoyaltyPoints($cart->user_id, $clientCurrency->doller_compare);
        // }
        // if ($loyalty_amount_saved  >= $temp_total_paying) {
        //     $loyalty_amount_saved = $temp_total_paying;
        //     $cart->total_payable_amount = 0.00;
        // } else {
            $cart->total_payable_amount = $total_paying  + $total_tax - $total_disc_amount - $loyalty_amount_saved;
        // }
        $wallet_amount_used = 0;
        if (isset($user)) {
            $cart->user_wallet_balance = $user->balanceFloat; 
            // if ( ($user->balanceFloat > 0) && ($user->balanceFloat >= $cart->total_payable_amount) ) {
            //     $wallet_amount_used = $user->balanceFloat;
            //     if ($clientCurrency) {
            //         $wallet_amount_used = $user->balanceFloat * $clientCurrency->doller_compare;
            //     }
            //     if ($wallet_amount_used > $cart->total_payable_amount) {
            //         $wallet_amount_used = $cart->total_payable_amount;
            //     }
            //     $cart->total_payable_amount = $cart->total_payable_amount - $wallet_amount_used;
            //     $cart->wallet_amount_used = $wallet_amount_used;
            // }
        }
        $cart->wallet_amount_used = $wallet_amount_used;
        $cart->difference_to_be_paid = $cart->total_payable_amount - $order->payable_amount;
        $cart->deliver_status = $delivery_status;
        $cart->loyalty_amount = $loyalty_amount_saved;
        $cart->tip = array(
            ['label' => '5%', 'value' => decimal_format(0.05 * $cart->difference_to_be_paid)],
            ['label' => '10%', 'value' => decimal_format(0.1 * $cart->difference_to_be_paid)],
            ['label' => '15%', 'value' => decimal_format(0.15 * $cart->difference_to_be_paid)]
        );
        $cart->vendor_details = $vendor_details;
        $cart->cart_dinein_table_id = $cart_dinein_table_id;
        $cart->upSell_products = ($upSell_products) ? $upSell_products->first() : collect();
        $cart->crossSell_products = ($crossSell_products) ? $crossSell_products->first() : collect();
        $cart->delay_date =  $delay_date??0;
        $cart->pickup_delay_date =  $pickup_delay_date??0;
        $cart->dropoff_delay_date =  $dropoff_delay_date??0;
        return $cart;
    }

}
