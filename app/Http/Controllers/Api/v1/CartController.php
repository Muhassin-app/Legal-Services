<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use Carbon\Carbon;
use App\Models\{Order,Wallet};
use App\Models\Country;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,ProductTrait,CartManager,DispatcherSlot};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\DunzoController;
use App\Http\Controllers\AhoyController;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Api\v1\PromoCodeController;
use App\Http\Controllers\Front\CartController as FrontCartController;
use App\Http\Controllers\Front\LalaMovesController;
use App\Http\Controllers\Front\QuickApiController;
use App\Http\Controllers\ShiprocketController;
use App\Http\Controllers\D4BDunzoController;
use App\Http\Traits\Borzoe;
use App\Models\{AddonOption, User, Product, Cart, ProductFaq,ProductVariantSet, CartProductPrescription, ProductVariant, CartProduct, CartCoupon, ClientCurrency, Brand, CartAddon, UserDevice, AddonSet, BookingOption, CartDeliveryFee, Client as ModelsClient, UserAddress, ClientPreference, LuxuryOption, Vendor, LoyaltyCard, SubscriptionInvoicesUser, VendorDineinCategory, VendorDineinTable, VendorDineinCategoryTranslation, VendorDineinTableTranslation, OrderVendor, OrderProductAddon, OrderTax, OrderProduct, OrderProductPrescription, VendorOrderStatus, VendorSlot,CategoryKycDocuments,CaregoryKycDoc, CartBookingOption, CartRentalProtection, ClientDocument, VerificationOption, TaxRate,VendorMinAmount, WebStylingOption, ProcessorProduct,OrderFiles, ProductBookingOption, ProductRentalProtection, RentalProtection};

use GuzzleHttp\Client as GCLIENT;
use Log;
use App\Models\Client;
//use App\Http\Traits\MpesaStkpush;

class CartController extends BaseController
{
    use ApiResponser,ProductTrait,CartManager,DispatcherSlot, Borzoe;

    private $field_status = 2;

    public function index(Request $request)
    {

        try {

            $user = Auth::user();

            if (!$user->id) {
                $cart = Cart::where('unique_identifier', $user->system_user)->with(['editingOrder','OrderFiles']);
            } else {
                $cart = Cart::where('user_id', $user->id)->with(['editingOrder','OrderFiles']);
            }

            $cart = $cart->first();
            // return $cart;
            $cartData = [];
       
            if ($cart) {

                $cartData = $this->getCart($cart, $user->language, $user->currency, $request->header('type')??$request->get('type'),$request->code);
               
                if(isset($cart->editingOrder) && !empty($cart->editingOrder) && !empty($cartData))
                {
                    $editlimit_datetime = Carbon::now()->toDateTimeString();
                    $order_edit_before_hours = getAdditionalPreference(['order_edit_before_hours'])['order_edit_before_hours'];
                    $editlimit_datetime = Carbon::now()->addHours($order_edit_before_hours)->toDateTimeString();
                    $cartData->cart_error_message = '';
                    if((strtotime($cart->editingOrder->scheduled_date_time) - strtotime($editlimit_datetime)) < 0){
                        $cartData->cart_error_message = __("Order can only be edited before Time limit of ".$order_edit_before_hours." Hours from Scheduled date. Please discard order editing.");
                    }
                    $VendorOrderStatus = VendorOrderStatus::where('order_id', $cart->editingOrder->id)->whereNotIn('order_status_option_id', [1, 2])->count();
                    if($VendorOrderStatus > 0){
                        $cartData->cart_error_message = __("You can not edit this order. Either order is in processed or in processing. Please discard order editing.");
                    }
                }


                $age_restriction = CartProduct::where('cart_id',$cart->id)->whereHas('product',function($q){
                                $q->where('age_restriction',1);
                            })->count();
                $passbase_check = VerificationOption::where(['code' => 'passbase','status' => 1])->first();
                $passbase['check'] = 0;
                $passbase['status'] = "";
                if($passbase_check && $age_restriction)
                {
                    $passbase['check'] = 1;
                    if(is_null($user->passbase_verification)){
                        $passbase['status'] = 'not_created';
                    }else{
                        $passbase['status'] = $user->passbase_verification->status;
                    }

                    $cartData->passbase_check = $passbase['check']??0;
                    $cartData->passbase_status= $passbase['status']??'';
                }

                $user_wallet = Wallet::where('holder_id',$user->id)->first();
                if($user_wallet){
                    $cartData->wallet_amount_availabe = $user_wallet->balance??0;
                    if($cart->total_payable_amount > $user_wallet->balance)
                    {
                        $cartData->remaining_amount_available = 0.00;
                        $cartData->wallet_amount_will_be_used = $user_wallet->balance;
                    }
                    
                    if($cart->total_payable_amount <= $user_wallet->balance)
                    {
                        $cartData->remaining_amount_available =  $user_wallet->balance - $cart->total_payable_amount;
                        $cartData->wallet_amount_will_be_used = $cart->total_payable_amount;
                    }
                }
                $client_documents = ClientDocument::where('client_id',Auth::user()->id)->whereNull('order_id')->where('cart_id',$cart->id)->get();
                if($client_documents){
                    $cartData->client_documents = $client_documents;
                }
              
            
                // $cartData_id = CartProduct::where('cart_id', $cart->id)->pluck('id');
                // $cart_product = CartProduct::whereIn('id', $cartData_id)->first();
                // if(!empty($cart_product->user_product_order_form)){
                //     $cartData->product_from_submitted = 1;
                // }else{
                //     $cartData->product_from_submitted = 0;
                // }
                return $this->successResponse($cartData);
            }
            

            return $this->successResponse($cartData);
        } catch (Exception $e) {
            return $this->successResponse([]);
        }
    }

    /**   check auth and system user to add product in cart    */
    public function userCheck()
    {
        $user = Auth::user();
        if ($user->id && $user->id > 0) {
            $user_id = $user->id;
        } else {
            if (empty($user->system_user)) {
                return response()->json(['error' => 'System id should not be empty.'], 404);
            }
            $user = User::where('system_id', Auth::user()->system_user)->first();
            $val = Auth::user()->system_user;
            if (!$user) {
                $user = new User;
                $user->name = "System User";
                $user->email = $val . "@email.com";
                $user->password = Hash::make($val);
                $user->system_id = $val;
                $user->save();
            }
            $user_id = $user->id;
        }
        return $user_id;
    }

    /**     * Add product In Cart    *           */
    public function add(Request $request)
    {
        try {
            $preference = ClientPreference::first();
            $luxury_option = LuxuryOption::where('title', $request->type)->first();
            $user = Auth::user();
            $langId = $user->language;
            $user_id = $user->id;
            $client_timezone = DB::table('clients')->first('timezone');
            $timezone = $user->timezone ? $user->timezone :  ($client_timezone->timezone ?? 'Asia/Kolkata' );
            $unique_identifier = '';
            if (!$user_id) {
                if (empty($user->system_user)) {
                    return $this->errorResponse(__('System id should not be empty.'), 404);
                }
                $unique_identifier = $user->system_user;
            }
            $product = Product::where('sku', $request->sku)->first();

            if (!$product) {
                return $this->errorResponse(__('Invalid product.'), 404);
            }
            $productVariant = ProductVariant::where('product_id', $product->id)->where('id', $request->product_variant_id)->first();

            if (!$productVariant) {
                return $this->errorResponse(__('Invalid product variant.'), 404);
            }
            // $cartData = Cart::with('cartProducts')->where('user_id',$user->id)->first();
            // if($cartData->cartProducts->first()){
            //     return response()->json([
            //         "status" => "Error",
            //         'message' => 'One service available at a time',
            //     ], 404);
            // }
            $client_currency = ClientCurrency::where('is_primary', '=', 1)->first();
            $cart_detail = [
                'is_gift' => 0,
                'status' => '0',
                'item_count' => 0,
                'user_id' => $user->id,
                'created_by' => $user->id,
                'unique_identifier' => $unique_identifier,
                'currency_id' => $client_currency->currency_id,
                'scheduled_date_time' =>  $request->has('scheduled_date_time') ? $request->scheduled_date_time : null,
                'schedule_type' =>  $request->has('schedule_type') ? $request->schedule_type : null,
                'scheduled_slot' =>  $request->has('schedule_slot') ? $request->schedule_slot : null
            ];
            if (!empty($user_id)) {
                $cart_detail = Cart::updateOrCreate(['user_id' => $user->id], $cart_detail);

            } else {
                $cart_detail = Cart::updateOrCreate(['unique_identifier' => $unique_identifier], $cart_detail);


            }
            $already_added_product_in_cart = CartProduct::where(["product_id" => $request->product_id, 'cart_id' => $cart_detail->id])->first();
            $already_added_product_variant_in_cart = CartProduct::where(["variant_id" => $request->product_variant_id, 'cart_id' => $cart_detail->id])->first();
            $totalQuantity = (!empty($already_added_product_variant_in_cart) ? $already_added_product_variant_in_cart->quantity : 0) + $request->quantity;

            // if($totalQuantity > $productVariant->quantity ){
            //     return response()->json(['error' => __('You can not add more product.')], 404);
            // }

            $additionalPreference = getAdditionalPreference(['is_service_product_price_from_dispatch']);
            if( (@$luxury_option->id == 6) && ($additionalPreference['is_service_product_price_from_dispatch'] ==1) ){
                $CartProduct = CartProduct::where(['cart_id' => $cart_detail->id])->select('id','dispatch_agent_id')->first();
                $dispatcherAgentData= $request->has('dispatcherAgentData') ? $request->dispatcherAgentData : [];
                $request_dispatch_agent_id = isset($dispatcherAgentData['agent_id'] ) ?  $dispatcherAgentData['agent_id']: "";

                if($CartProduct &&  ( $CartProduct->dispatch_agent_id != $request_dispatch_agent_id)){
                    return $this->errorResponse('Please select the service of same provider', 404);
                }
            }

            if(@$luxury_option->id == 4) {
                if($already_added_product_variant_in_cart)
                {
                    return response()->json([
                        "status" => "Error",
                        'message' => 'Product already exists in the cart',
                    ], 404);
                }
            }

            $order_edit_qty = (!empty($already_added_product_in_cart) && !empty($already_added_product_in_cart->order_quantity))?$already_added_product_in_cart->order_quantity:0;
            if($product->is_long_term_service !=1){
                if ($product->category->categoryDetail->type_id == 8) {
                } else {
                    if ( ($product->sell_when_out_of_stock == 0) && (($productVariant->quantity + $order_edit_qty) < $request->quantity && $product->has_inventory == 1) ) {
                        return $this->errorResponse('You Can not order more than ' . $productVariant->quantity . ' quantity.', 404);
                    }
                }
            }
            $service_start_date   = '';
            $start_date_time  = $request->has('start_date_time') ? $request->start_date_time : null;
            $isLongTermService =0;
            if( $request->has('service_start_time')){
                $isLongTermService  =1;

                $time = '1998-01-14 '.$request->service_start_time; /**only need time */
                $service_start_time = Carbon::parse($time, $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                $start_date_time = $service_start_time ; /** we user start_date_time for long term order timing */
                $service_start_date = carbon::now()->setTimezone('UTC')->format('Y-m-d H:i:s');
            }


            $addonSets = $addon_ids = $addon_options = array();
            if ($request->has('addon_ids')) {
                $addon_ids = $request->addon_ids;
            }
            if ($request->has('addon_options')) {
                $addon_options = $request->addon_options;
            }
            // if($request->has('start_date_time')){
            //     $start_date_time= $request->start_date_time;
            // }
            if($request->has('end_date_time')){
                $end_date_time= $request->end_date_time;
            }
            if($request->has('total_booking_time')){
                $total_booking_time= $request->total_booking_time;
            }
            if($request->has('additional_increments_hrs_min')){
                $additional_increments_hrs_min=$request->additional_increments_hrs_min;
            }
            foreach ($addon_options as $key => $opt) {
                $addonSets[$addon_ids[$key]][] = $opt;
            }

            foreach ($addonSets as $key => $value) {
                $addon = AddonSet::join('addon_set_translations as ast', 'ast.addon_id', 'addon_sets.id')
                    ->select('addon_sets.id', 'addon_sets.min_select', 'addon_sets.max_select', 'ast.title')
                    ->where('ast.language_id', $langId)
                    ->where('addon_sets.status', '!=', '2')
                    ->where('addon_sets.id', $key)->first();

                if (!$addon) {
                    return $this->errorResponse(__('Invalid addon or delete by admin. Try again with remove some.'), 404);
                }
                if ($addon->min_select > count($value)) {
                    return response()->json([
                        "status" => "Error",
                        'message' => 'Select minimum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
                if ($addon->max_select < count($value)) {
                    return response()->json([
                        "status" => "Error",
                        'message' => 'You can select maximum ' . $addon->min_select . ' options of ' . $addon->title,
                        'data' => $addon
                    ], 404);
                }
            }

            if($request->has('rental_protection')){
                $saveProtections = [];
                foreach($request->rental_protection as $protectionId){
                    $rentalProtection = RentalProtection::find($protectionId);
                    if($rentalProtection){
                        $saveProtections[] = [
                            'cart_id' => $cart_detail->id,
                            'rental_protection_id' => $rentalProtection->id,
                            'product_id' => $product->id,
                        ];
                    }
                }
                if(!empty($saveProtections)){
                    CartRentalProtection::insert($saveProtections);
                }
            }

            /** delete is long term is added from cart */
            if($isLongTermService || ($isLongTermService ==1) ){
                if(CartProduct::where('cart_id', $cart_detail->id)->count() > 1 ){
                    return $this->errorResponse(['error' => __('You can only buy Single Long Term Serivce !'), 'alert' => '1'], 404);
                }
                CartProduct::where('cart_id', $cart_detail->id)->delete();
            }
            $checkVendorId = CartProduct::where('cart_id', $cart_detail->id)->where('vendor_id', '!=', $product->vendor_id)->first();

            if ($luxury_option) {
                $checkCartLuxuryOption = CartProduct::where('luxury_option_id', '!=', $luxury_option->id)->where('cart_id', $cart_detail->id)->first();
                if ($checkCartLuxuryOption) {
                    return $this->errorResponse(['error' => __('You are adding products in different mods'), 'alert' => '1'], 404);
                }
                if ($luxury_option->id == 2 || $luxury_option->id == 3) {
                    if ($checkVendorId) {
                        return $this->errorResponse(['error' => __('Your cart has existing items from another vendor'), 'alert' => '1'], 404);
                    }
                }
            }


            if ( (isset($preference->isolate_single_vendor_order)) && ($preference->isolate_single_vendor_order == 1) ) {
                if ($checkVendorId) {
                    return $this->errorResponse(['error' => __('Your cart has existing items from another vendor'), 'alert' => '1'], 400);
                }
            }

            if ((isset($preference->isolate_single_vendor_order)) && ($preference->isolate_single_vendor_order == 1)) {
                if ($checkVendorId) {
                    CartProduct::where('cart_id', $cart_detail->id)->delete();
                }
            }

            if ($cart_detail->id > 0) {
                $oldquantity = $isnew = 0;
                $cart_product_detail = [
                    'status'  => '0',
                    'is_tax_applied'  => '1',
                    'created_by'  => $user_id,
                    'product_id' => $product->id,
                    'cart_id'  => $cart_detail->id,
                    'quantity'  => $request->quantity,
                    'vendor_id'  => $product->vendor_id,
                    'variant_id'  => $request->product_variant_id,
                    'currency_id' => $client_currency->currency_id,
                    'luxury_option_id' => $luxury_option ? $luxury_option->id : 1,
                    'start_date_time'=>$start_date_time ?? null,
                    'end_date_time'=>$end_date_time ?? null,
                    'total_booking_time'=>$total_booking_time ?? null,
                    'additional_increments_hrs_min'=>$additional_increments_hrs_min ?? null,
                    'service_day'         => $request->has('service_day') ? $request->service_day : null,
                    'service_date'        => $request->has('service_date') ? $request->service_date : null,
                    'service_period'      => $request->has('service_period') ? $request->service_period : null,
                    'service_start_date'  => @$service_start_date,
                    'schedule_slot'       => $request->has('schedule_slot') ? $request->schedule_slot : null,
                    'dispatch_agent_id'   => $request->has('dispatch_agent_id') ? $request->dispatch_agent_id : null,
                    'schedule_type'       => $request->has('schedule_type') ? $request->schedule_type : null,
                    'scheduled_date_time' => $request->has('scheduled_date_time') ? $request->scheduled_date_time : null,
                ];


            //Recurring Booking
            $recurring_days = '';
            if($product->is_recurring_booking == 1){

                if (empty($request->recurringformPost)) {
                    return $this->errorResponse(__('Recurring booking type not be empty.'), 404);
                }


                $cartRecurringCall = new FrontCartController();
                $recurringformPost = $cartRecurringCall->recurringCalculationFunction($request);
                $action = '5';
                //Check if recurring_booking_type,recurring_week_day,recurring_week_type,recurring_day_data,recurring_booking_time coulmn exists in table

                   $start_date             = $recurringformPost->startDate;
                   $end_date               = $recurringformPost->endDate;
                   $recurring_days  = @$recurringformPost->selectedCustomdates??null;
                   $weekTypes  = @$recurringformPost->weekTypes??null;
                   $action = $recurringformPost->action??5;
                   $schedule_time = $recurringformPost->schedule_time??null;


                //In case of on recurringformPost
                if (!empty($request->recurringformPost)) {
                    $cart_product_detail['recurring_booking_type'] = $action;
                    $cart_product_detail['recurring_week_day'] = $weekTypes;
                    $cart_product_detail['recurring_week_type'] = $weekTypes;
                    $cart_product_detail['recurring_day_data'] = $recurring_days;
                    $cart_product_detail['recurring_booking_time'] = $schedule_time;
                }

            }


                if($request->has('dispatcherAgentData') && !empty($request->dispatcherAgentData)){

                    $dataTime = Carbon::parse($request->dispatcherAgentData['onDemandBookingdate'], $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                    $slot = @$request->dispatcherAgentData['slot'] ?  @$request->dispatcherAgentData['slot'] : Carbon::parse($request->dispatcherAgentData['onDemandBookingdate'], $timezone)->setTimezone('UTC')->format('H:i:s');
                    $cart_product_detail['schedule_type'] = 'schedule';
                    $cart_product_detail['scheduled_date_time'] = @$dataTime;
                    $cart_product_detail['schedule_slot'] = @$slot ?? null;
                    $cart_product_detail['dispatch_agent_price'] = @$request->dispatcherAgentData['agent_price']??null;
                    $cart_product_detail['dispatch_agent_id'] = @$request->dispatcherAgentData['agent_id']??null;
                }

                // Check for schedule type consistency
                // $existingCartProducts = CartProduct::where('cart_id', $cart_detail->id)->get();
                // if ($existingCartProducts->count() > 0) {
                //     $existingProduct = $existingCartProducts->first();
                //     $isExistingScheduled = !empty($existingProduct->schedule_type); // schduled product
                //     $isNewScheduled = !empty($cart_product_detail['scheduled_date_time']); //scq    
                //     // If one is scheduled and other is not, return error
                //     if (!$isExistingScheduled && $isNewScheduled) {
                //         return $this->errorResponse(['error' => __('You can only add either scheduled or non-scheduled services at a time'), 'alert' => '1'], 404);
                //     }
                // }

                $cartProduct = CartProduct::where('cart_id', $cart_detail->id)
                    ->where('product_id', $product->id)
                    ->where('variant_id', $productVariant->id)->first();
                if (!$cartProduct) {
                    $isnew = 1;
                } else {
                    $checkaddonCount = CartAddon::where('cart_product_id', $cartProduct->id)->count();
                    if (count($addon_ids) != $checkaddonCount) {
                        $isnew = 1;
                    } else {
                        foreach ($addon_options as $key => $opts) {
                            $cart_addon = CartAddon::where('cart_product_id', $cartProduct->id)
                                ->where('addon_id', $addon_ids[$key])
                                ->where('option_id', $opts)->first();
                            if (!$cart_addon) {
                                $isnew = 1;
                            }
                        }
                    }
                }
             
                // $exists = $cart_detail->cartProducts->contains('schedule_type', 'appointment');
            
                // if ($exists) {
                //     return response()->json([
                //         'status' => 400,  
                //         'msg' => 'You can only add either a scheduled service or an express way service at a time.',
                //     ]);
                // }
                if ($isnew == 1) {
                    $cartProduct = CartProduct::create($cart_product_detail);
                    if (!empty($addon_ids) && !empty($addon_options)) {
                        $saveAddons = array();
                        foreach ($addon_options as $key => $opts) {
                            $saveAddons[] = [
                                'option_id' => $opts,
                                'cart_id' => $cart_detail->id,
                                'addon_id' => $addon_ids[$key],
                                'cart_product_id' => $cartProduct->id,
                            ];
                        }
                        if (!empty($saveAddons)) {
                            CartAddon::insert($saveAddons);
                        }
                    }
                } else {
                    $cartProduct->quantity = $cartProduct->quantity + $request->quantity;
                    $cartProduct->save();
                }
            }
            $cartData = $this->getCart($cart_detail, $user->language, $user->currency, $request->type);
            if ($cartData && !empty($cartData)) {
                $cartData->cart_product_id = $cartProduct->id;
                $product_total_quantity_in_cart = CartProduct::where(['cart_id'=>$cartProduct->cart_id,'product_id'=> $product->id])->sum('quantity');
                $cartData->product_total_qty_in_cart = intval($product_total_quantity_in_cart);
                return $this->successResponse($cartData);
            } else {
                return $this->successResponse($cartData);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get Last added product variant
     *
     * @return \Illuminate\Http\Response
     */
    public function getLastAddedProductVariant(Request $request)
    {
        try{
            $cartProduct = CartProduct::with('addon')
                ->where('cart_id', $request->cart_id)
                ->where('product_id', $request->product_id)
                ->orderByDesc('created_at')->first();

            return $this->successResponse($cartProduct, '', 200);
        }
        catch(\Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Get current product variants with different addons
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductVariantWithDifferentAddons(Request $request)
    {
        try{
            $langId = Session::get('customerLanguage');
            $cur_ids = Session::get('customerCurrency');
            if(isset($cur_ids) && !empty( $cur_ids)){
                $clientCurrency = ClientCurrency::where('currency_id','=', $cur_ids)->first();
            }else{
                $clientCurrency = ClientCurrency::where('is_primary','=', 1)->first();
            }

            $cartProducts = CartProduct::with(['product.translation' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId)->groupBy('product_translations.language_id');
            },
            'product.media.image',
            'pvariant.media.pimage.image',
            'vendor.slot.day', 'vendor.slotDate',
            ])
            ->where('cart_id', $request->cart_id)
            ->where('product_id', $request->product_id)
            ->select('*','id as add_on_set_and_option')->orderByDesc('created_at')->get();

            $multiplier = $clientCurrency ? $clientCurrency->doller_compare : 1;
            foreach ($cartProducts as $key => $cart) {
                $cart->is_vendor_closed = 0;
                $cart->variant_multiplier = $multiplier;
                $variant_price = ($cart->pvariant) ? ($cart->pvariant->price * $multiplier) : 0;

                $product = $cart->product;
                $product->translation_title = ($product->translation->isNotEmpty()) ? $product->translation->first()->title : $product->sku;

                if($cart->pvariant && $cart->pvariant->media->isNotEmpty()){
                    $image_fit = $cart->pvariant->media->first()->pimage->image->path['image_fit'];
                    $image_path = $cart->pvariant->media->first()->pimage->image->path['image_path'];
                    $product->product_image = $image_fit . '300/300' . $image_path;
                }elseif($product->media->isNotEmpty()){
                    $image_fit = $product->media->first()->image->path['image_fit'];
                    $image_path = $product->media->first()->image->path['image_path'];
                    $product->product_image = $image_fit . '300/300' . $image_path;
                }else{
                    $product->product_image = $this->loadDefaultImage();
                }

                $addon_set = $cart->add_on_set_and_option;
                foreach ($addon_set as $skey => $set) {
                    $set->addon_set_translation_title = ($set->translation->isNotEmpty()) ? $set->translation->first()->title : $set->title;
                    foreach ($set->options as $okey => $option) {
                        $option->option_translation_title = ($option->translation->isNotEmpty()) ? $option->translation->first()->title : $option->title;
                        $opt_price_in_doller_compare = $option->price * $multiplier;
                        $variant_price = $variant_price + $opt_price_in_doller_compare;
                    }
                }
                $cart->variant_price = $variant_price;
                $cart->addon_set = $addon_set;
                $cart->total_variant_price = decimal_format($cart->quantity * $variant_price);

                if($cart->vendor->show_slot == 0){
                    if( ($cart->vendor->slotDate->isEmpty()) && ($cart->vendor->slot->isEmpty()) ){
                        $cart->is_vendor_closed = 1;
                    }else{
                        $cart->is_vendor_closed = 0;
                    }
                }
                unset($cartProducts[$key]->add_on_set_and_option);
            }

            return $this->successResponse($cartProducts, '', 200);
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     *    update quantity in cart
     **/
    public function updateQuantity(Request $request)
    {
        $user = Auth::user();
        if ($request->quantity < 1) {
            return response()->json(['error' => __('Quantity should not be less than 1')], 422);
        }
        $cart = Cart::where('user_id', $user->id)->where('id', $request->cart_id)->first();
        if (!$cart) {
            return response()->json(['error' => __('User cart not exist.')], 404);
        }
        $cartProduct = CartProduct::where('cart_id', $cart->id)->where('id', $request->cart_product_id)->first();
        if (!$cartProduct) {
            return response()->json(['error' => __('Product not exist in cart.')], 404);
        }
        $variant_id = $cartProduct->variant_id;
        $productDetail = Product::with([
            'variant' => function ($sel) use ($variant_id) {
                $sel->where('id', $variant_id);
                $sel->groupBy('product_id');
            }
        ])->find($cartProduct->product_id);
        if($productDetail->variant[0]->quantity < $request->quantity){
            return response()->json(['error' => __('You can not add more product.')], 404);
        }
        $cartProduct->quantity = $request->quantity;
        $cartProduct->save();
        $totalProducts = CartProduct::where('cart_id', $cart->id)->sum('quantity');
        $cart->item_count = $totalProducts;
        $cart->save();
        $cartData = $this->getCart($cart, $user->language, $user->currency, $request->type);
        return response()->json([
            'data' => $cartData,
        ]);
    }

    /**
     *    update cart product checked/unchecked in cart
     **/
    public function updateCartCheckedStatus(Request $request)
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->where('id', $request->cart_id)->first();
        if (!$cart) {
            return response()->json(['error' => __('User cart not exist.')], 404);
        }
        $cartProduct = CartProduct::where('cart_id', $request->cart_id)->where('id', $request->cart_product_id)->first();
        if (!$cartProduct) {
            return response()->json(['error' => __('Product not exist in cart.')], 404);
        }
        $cartProduct->is_cart_checked = $request->is_cart_checked;
        $cartProduct->save();
        $cartData = $this->getCart($cart, $user->language, $user->currency, $request->type);
        return response()->json([
            'data' => $cartData,
        ]);
    }

    public function getItemCount(Request $request)
    {
        $cart = Cart::where('user_id', Auth::user()->id)->where('id', $request->cart_id)->first();
        if (!$cart) {
            return response()->json(['error' => __('User cart not exist.')], 404);
        }
        $totalProducts = CartProduct::where('cart_id', $cart->id)->sum('quantity');
        $cart->item_count = $totalProducts;
        $cart->save();
        return response()->json([
            'total_item' => $cart->item_count,
        ]);
    }

    public function removeItem(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $cart = Cart::where('id', $request->cart_id);
        if (!$user_id || $user_id < 1) {
            if (empty($user->system_user)) {
                return $this->errorResponse('System id should not be empty.', 404);
            }
            $cart = $cart->where('unique_identifier', $user->system_user);
        } else {
            $cart = $cart->where('user_id', $user->id);
        }
        $cart = $cart->first();
        if (!$cart) {
            return response()->json(['error' => __('Cart not exist')], 404);
        }
        $cartProduct = CartProduct::where('cart_id', $cart->id)->where('id', $request->cart_product_id)->first();
        if (!$cartProduct) {
            return response()->json(['error' => __('Product not exist in cart.')], 404);
        }
        $cartProductBid_number = @$cartProduct->bid_number??null;
        $cartProductVendor_id = @$cartProduct->vendor_id??null;
        $cartProduct->delete();
        $totalProducts = CartProduct::where('cart_id', $cart->id)->sum('quantity');
        CartRentalProtection::where('cart_id', $cart->id)->delete();
        CartBookingOption::where('cart_id', $cart->id)->delete();
        if (!$totalProducts || $totalProducts < 1) {
            $cart->delete();

            if(@$cartProductBid_number)
            {
                CartProduct::where('vendor_id',$cartProductVendor_id)->update(['bid_number'=>null,'bid_discount'=>null]);
            }


            return response()->json([
                "message" => __("Product removed from cart successfully."),
                'data' => array(),
            ]);
        }
        $cart->item_count = $totalProducts;
        $cart->save();
        $cartData = $this->getCart($cart, $user->language, $user->currency, $request->type);
        return response()->json([
            "message" => __("Product removed from cart successfully."),
            'data' => $cartData,
        ]);
    }

    /**         *       Empty cart       *          */
    public function emptyCart($cartId = 0)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $cart = Cart::where('id', '>', 0);
        if (!$user_id || $user_id < 1) {
            if (empty($user->system_user)) {
                return $this->errorResponse('System id should not be empty.', 404);
            }
            $cart = $cart->where('unique_identifier', $user->system_user);
        } else {
            $cart = $cart->where('user_id', $user_id);
        }
        $cart->delete();
        ClientDocument::where('client_id',$user_id)->whereNull('order_id')->delete();
        return response()->json(['message' => __('Empty cart successfully.')]);
    }


    public function getCart($cart, $langId = '1', $currency = '1', $type = 'delivery',$code = 'D')
    {
     try{
        $container_charges_tax = 0;
        $deliver_fee_charges_tax = 0;
        $total_service_fee_tax = 0;
        $total_fixed_fee_tax = 0;
        $total_service_fee = 0;
        $deliver_fee_charges = 0;
        $total_markup_fee_tax = 0;
        $total_taxable_amount = 0;
        $preferences = ClientPreference::first();
        $additionalPreferences = (object)getAdditionalPreference(['is_tax_price_inclusive','is_service_product_price_from_dispatch']);
        $clientCurrency = ClientCurrency::where('currency_id', $currency)->first();
        if (!$cart) {
            return false;
        }
        $nowdate = Carbon::now()->toDateTimeString();
        $nowdate = convertDateTimeInClientTimeZone($nowdate);
        $is_service_product_price_from_dispatch = 0;
        if(($additionalPreferences->is_service_product_price_from_dispatch == 1) && ( $type == 'on_demand')){
            $is_service_product_price_from_dispatch =1;
        }
        $vondorCnt = 0;
        $address = [];
        $category_array = [];
        $latitude = '';
        $longitude = '';
        $address_id = 0;
        $delivery_status = 1;
        $cartID = $cart->id;

        $upSell_products = collect();
        $crossSell_products = collect();
        $delifproductnotexist = CartProduct::where('cart_id', $cartID)->doesntHave('product')->delete();

        $cartData = CartProduct::with([
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
                $q->groupBy('product_id');
            },
            'vendorProducts' => function ($qry) use ($cartID) {
                $qry->where('cart_id', $cartID);
            },
            'vendorProducts.addon.set' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.product.productcategory'=> function ($q1)  {
                $q1->select('id', 'type_id');
            },
            'vendorProducts.product.categoryName' => function ($q) use ($langId) {
                $q->select('category_id', 'name');
                $q->where('language_id', $langId);
            },
            'vendorProducts.addon.option' => function ($qry) use ($langId) {
                $qry->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                $qry->select('addon_options.id', 'addon_options.price', 'apt.title', 'addon_options.addon_id', 'apt.language_id');
                $qry->where('apt.language_id', $langId)->groupBy(['addon_options.id', 'apt.language_id']);
            }, 'vendorProducts.product.taxCategory.taxRate',
            'vendorProducts.product.cartRentalProtections',
            'vendorProducts.product.bookingOptions.bookingOption',
            'vendorProducts.product.cartBookingOptions.bookingOption',
        ])->whereHas('product', function ($q) {
            $q->where('is_live', 1);
        });

        $cartData = $cartData->select('vendor_id','cart_id', 'vendor_dinein_table_id','dispatch_agent_id', 'is_cart_checked')->where('status', [0, 1])->where('cart_id', $cartID)->groupBy('vendor_id')->orderBy('created_at', 'asc')->get();


        $taxes=TaxRate::all();
        $taxRates=array();
        foreach($taxes as $tax){
            $taxRates[$tax->id]=['tax_rate'=>$tax->tax_rate,'tax_amount'=>$tax->tax_amount];
        }

        $client_timezone = DB::table('clients')->first('timezone');
        $user_timezone = $client_timezone->timezone ?? 'Asia/Kolkata';
        $loyalty_amount_saved = 0;
        $subscription_features = array();
        $user_subscription = null;
        if ($cart->user_id) {
            $now = Carbon::now()->toDateTimeString();
            $user_subscription = SubscriptionInvoicesUser::with('features')
                ->select('id', 'user_id', 'subscription_id')
                ->where('user_id', $cart->user_id)
                ->where('end_date', '>', $now)
                ->orderBy('end_date', 'desc')->first();
            // if ($user_subscription) {
            //     foreach ($user_subscription->features as $feature) {
            //         $subscription_features[] = $feature->feature_id;
            //     }
            // }
            $user = User::find($cart->user_id);
            $user_timezone =  $user->timezone ?? $user_timezone;

            $cart->scheduled_date_time = !empty($cart->scheduled_date_time) ? convertDateTimeInTimeZone($cart->scheduled_date_time, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $cart->schedule_pickup = !empty($cart->schedule_pickup) ? convertDateTimeInTimeZone($cart->schedule_pickup, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $cart->schedule_dropoff = !empty($cart->schedule_dropoff) ? convertDateTimeInTimeZone($cart->schedule_dropoff, $user->timezone, 'Y-m-d\TH:i') : NULL;
            $address = UserAddress::where('user_id', $cart->user_id)->where('is_primary', 1)->first();
            $address_id = ($address) ? $address->id : 0;
        }
        if($type != 'delivery'){
            $loggedin_user = Auth::user();
            $latitude = $loggedin_user->latitude ?? '';
            $longitude = $loggedin_user->longitude ?? '';
        }else{
            $latitude = ($address) ? $address->latitude : '';
            $longitude = ($address) ? $address->longitude : '';
        }
        $total_payable_amount = $total_subscription_discount = $total_discount_amount = $total_discount_percent = $total_taxable_amount = 0.00;
        $total_tax = $total_paying = $total_disc_amount = 0.00;
        $item_count = 0;
        $total_delivery_amount = 0;
        $total_fixed_fee_amount = 0;
        $total_markup_amount = 0;
        $order_sub_total = 0;
        $deliver_fee_charges = 0;
        $total_fixed_fee_tax = 0;
        $total_markup_fee_tax = 0;
        $totalDeliveryCharges = 0;
        $is_long_term = 0;
        // pr($cartData);
        if ($cartData) {
            $cart_dinein_table_id = NULL;
            $action = $type;
            $vendor_details = [];
            $tax_details = [];
            $is_vendor_closed = 0;
            $delay_date = 0;
            $pickup_delay_date = 0;
            $dropoff_delay_date = 0;
            $total_addon_price = 0;
            $total_service_fee = 0;
            $product_out_of_stock = 0;
            $PromoFreeDeliver = 0;
            $coupon_apply_price=0;
            $PromoDelete = 0;
            $couponApplied = 0;
            $total_container_charges = 0 ;
            $rentalProtection = 0;
            $bookingOption = 0 ;
            $securityAmount = 0;
            $deliveryfeeOnCoupon = 0;
            $totalFreeDeliveryCharges = 0;
            $total_markup_charges = 0 ;
            $deliver_fee_charges = 0;
            $total_fixed_fee_tax = 0;

            $delivery_slot_amount = 0;

            foreach ($cartData as $ven_key => $vendorData) {
                
                $scheduledDateTime = dateTimeInUserTimeZone($vendorData->scheduled_date_time, $user_timezone);
                $vendorData->scheduled_date_time = date('Y-m-d',strtotime($scheduledDateTime));
                $slotsRes = getShowSlot($vendorData->scheduled_date_time,$vendorData->vendor_id,'delivery',"60",0,'',$cartID);

                $slots = (array)$slotsRes['slots'];
                // this variable for get slot from dispatc
                $slotsdate = $slotsRes['date'];
                $slotcount =count((array)$slots);
                $vendorData->slotsdate = $slotsdate;
                $vendorData->slots = $slots;
                $vendorData->slotsCnt =  $slotcount;
                // $vendorData->delay_date = date('Y-m-d');


                $opt_quantity_price_new = 0.00;
                $deliver_fee_charges = 0;
                $total_fixed_fee_tax = 0;
                $total_markup_fee_tax = 0;
                $PromoFreeDeliver = 0;
                $total_fixed_fee_amount =$total_fixed_fee_amount+ $vendorData->vendor->fixed_fee_amount;
                $is_promo_code_available = 0;
                $vendor_products_total_amount = $codeApplied = $is_percent = $proSum = $proSumDis = $taxable_amount = $subscription_discount = $discount_amount = $discount_percent = $deliver_charge = $delivery_fee_charges = 0.00;
                $delivery_count = 0;

                $vendor_latitude = $vendorData->vendor->latitude ?? 30.71728880;
                $vendor_longitude =  $vendorData->vendor->longitude ?? 76.80350870;

                $slotsDate = findSlot('',$vendorData->vendor->id,$type,'webFormet',$cartID);
                // $vendorData->delaySlot = $slotsDate;
                $vendorData->delaySlot = (($slotsDate)? ( $slotsDate['datetime']?  $slotsDate['datetime'] : '' ):'');
                $vendorStartDate =  (($slotsDate)? ( $slotsDate['date'] ?  $slotsDate['date'] : '' ):'');
                $vendorStartTime =  (($slotsDate)? ( $slotsDate['time'] ?  $slotsDate['time'] : '' ):'');

                $vendorData->vendor->closed_store_order_scheduled = $vendorData->vendor->closed_store_order_scheduled;
                $vendorData->vendor->fixed_fee_amount;
                if ($action != 'delivery') {
                    $vendor_details['vendor_address'] = $vendorData->vendor->select('id', 'latitude', 'longitude', 'address')->where('id', $vendorData->vendor_id)->first();
                    if ($action == 'dine_in') {
                        $vendor_tables = VendorDineinTable::where('vendor_id', $vendorData->vendor_id)->with('category')->get();
                        foreach ($vendor_tables as $vendor_table) {
                            $vendor_table->qr_url = url('/vendor/' . $vendorData->vendor->slug . '/?id=' . $vendorData->vendor_id . '&name=' . $vendorData->vendor->name . '&table=' . $vendor_table->id);
                        }
                        $vendor_details['vendor_tables'] = $vendor_tables;
                    //    return $vendor_details['vendor_tables'];
                    }
                }
                else {
                    if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)) {

                        if ($address_id > 0) {
                            $serviceArea = $vendorData->vendor->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                                $query->select('vendor_id')
                                    ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                            })->where('id', $vendorData->vendor_id)->get();

                        }
                    }
                }

                if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) && !empty($latitude) && !empty($longitude)) {
                    if (!empty($latitude) && !empty($longitude)) {
                        if(($preferences->slots_with_service_area == 1) && ($vendorData->vendor->show_slot == 0)){
                            $serviceArea = $vendorData->vendor->where(function($query) use ($latitude, $longitude) {
                                $query->whereHas('slot.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                    $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                })
                                ->orWhereHas('slotDate.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                    $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                });
                            })->where('id', $vendorData->vendor_id)->get();
                        }
                    }
                }

                $ttAddon = $payable_amount = $is_coupon_applied = $coupon_removed = $only_products_amount= 0;
                $coupon_removed_msg = '';
                $deliver_charge = 0;
                $deliveryCharges = 0;
                $deliveryCharges_real = 0;
                $delivery_fee_charges = 0.00;
                $couponData = $couponProducts = array();

                $if_previousdeliveryfee_added = 0;
                $vendorTotalDeliveryFee = 0;

                $previousdeliveryfee = 0;
                //get Coupon Discount for product case
                $coupon_product_ids = [];
                $coupon_vendor_ids = [];
                $coupon_product_discount = 0;
                $in_or_not = 0;
                $rate = 0;
                if (isset($vendorData->coupon) && !empty($vendorData->coupon) && isset($vendorData->coupon->promo) && !empty($vendorData->coupon->promo)){
                    if($vendorData->coupon->promo->restriction_on == 0)
                    {
                        $coupon_product_ids = $vendorData->coupon->promo->details->pluck('refrence_id')->toArray();
                        $in_or_not = $vendorData->coupon->promo->restriction_type;
                    }
                    elseif($vendorData->coupon->promo->restriction_on == 1){
                        $coupon_vendor_ids = $vendorData->coupon->promo->details->pluck('refrence_id')->toArray();
                        $in_or_not = $vendorData->coupon->promo->restriction_type;
                    }
                }
                $total_cashback = 0;
                foreach ($vendorData->vendorProducts as $pkey => $prod) {
                    $total_cashback += $prod->product->cashback;
                    $fields = [];
                    foreach ($prod->product->ProductAttribute as $productAttribute) {
                        if ($productAttribute->attributeOption()->exists()) {
                            if(!empty($title = $productAttribute->attributeOption->title)){
                                $fields[$productAttribute->key_name] = $title;
                            }else{
                                $fields[$productAttribute->key_name] = $productAttribute->key_value;
                            }
                        }
                    }
                    $prod->product->transmission = $fields['Transmission'] ?? '';
                    $prod->product->fuel_type = $fields['Fuel Type'] ?? '';
                    $prod->product->Seats = $fields['Seats'] ?? '' .' Seats';
                    $prod->product->cabins = $fields['Cabins'] ?? '' .' Cabins';
                    $prod->product->baths = $fields['Baths'] ?? '' .'Baths';
                    $prod->stock_out = 1;
                    if($prod->pvariant['quantity'] > 0){
                        $prod->stock_out=0;
                    }
                    $rentalProtection += $prod->product->cartRentalProtections->rentalProtection->price ?? 0;
                    $bookingOption += $prod->product->cartBookingOptions->bookingOption->price ?? 0;
                    $securityAmount += $prod->product->security_amount ?? 0;

                    //mohit sir branch code updated by sohail farm meat
                    if ($action == 'takeaway') {
                        $processorProduct = ProcessorProduct::where('product_id', $prod->product_id)->first();
                        $prod->is_processor_enable = (isset($processorProduct->is_processor_enable) && $processorProduct->is_processor_enable == 1)? true : false;
                        $prod->processor_name = !empty($processorProduct->name)? $processorProduct->name : '';
                        $prod->processor_date = !empty($processorProduct->date)? $processorProduct->date : '';
                        $prod->address = !empty($processorProduct->address)? $processorProduct->address : '';
                    }else{
                        $prod->is_processor_enable = false;
                        $prod->processor_name = '';
                        $prod->processor_date = '';
                    }
                    //till here


                    $prod->is_recurring_booking = 0;
                    //if we required any additional price * multiply (Right now its for reccuring)
                    $prod->recurring_date_count = 1;

                    if($prod->product->is_recurring_booking ==1){
                        $prod->is_recurring_booking   = 1;
                       // $prod->recurring_booking_time = convertDateTimeInTimeZone($prod->recurring_booking_time, $user_timezone, 'H:i');
                        $cnt = @count(explode(",",$prod->recurring_day_data));
                        $prod->recurring_date_count = $cnt != 0 ? $cnt : 1;
                        $is_recurring_booking        = 1;
                    }

                    $is_slot_from_dispatch = $prod->product->is_slot_from_dispatch ;
                    $show_dispatcher_agent = $prod->product->is_show_dispatcher_agent  ;
                    $last_mile_check       = $prod->product->Requires_last_mile  ;
                    $getSlotingDate        = $prod->scheduled_date_time ;
                    $cateTypeId = @$prod->product->productcategory ? @$prod->product->productcategory->type_id : '';
                    $prod->dispatchAgents = [];
                    if(($cateTypeId ==  12) && ($is_slot_from_dispatch == 1) && ( $last_mile_check ==1) ){
                        $Dispatch =  $this->getDispatchAppointmentDomain();
                        $dispatchAgents = [];

                        if($Dispatch){
                            $location[] = array(
                                'latitude' =>   $vendor_longitude,
                                'longitude' =>  $vendor_longitude
                            );
                            $dispatchData=[
                                'service_key'      => $Dispatch->appointment_service_key,
                                'service_key_code' => $Dispatch->appointment_service_key_code,
                                'service_key_url'  => $Dispatch->appointment_service_key_url,
                                'service_type'     => 'appointment',
                                'tags'             => $prod->product->tags,
                                'latitude'         => $vendor_latitude,
                                'longitude'        => $vendor_longitude,
                                'service_time'     => $prod->product->minimum_duration_min,
                                'schedule_date'    => $getSlotingDate,
                                'slot_start_time'  => $vendorStartTime
                            ];

                            $dispatchAgents = $this->getSlotFeeDispatcher($dispatchData);
                        }
                        $prod->dispatchAgents =  $dispatchAgents;
                        $prod->vendorStartDate = $vendorStartDate;
                    }
                    $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
                    if(@$getAdditionalPreference['is_rental_weekly_monthly_price']){
                        $rental_price = 0;
                        if(@$prod->start_date_time && @$prod->end_date_time){
                            $start_date_time  = new Carbon($prod->start_date_time);
                            $end_date_time  = new Carbon($prod->end_date_time);
                            $prod->days = $start_date_time->diff($end_date_time)->days + 1;
                            $rental_price = $prod->pvariant ? $prod->pvariant->price : 0;
                            if(isset($prod->pvariant->month_price) && !empty($prod->pvariant->month_price)  && isset($prod->pvariant->week_price)){

                                if($prod->days >= 7 && $prod->days < 30){
                                    $rental_price = $prod->pvariant->week_price;
                                }elseif($prod->days >= 30){
                                    $rental_price = $prod->pvariant->month_price;
                                }
                            }
                            $prod->price = $rental_price;
                            $rental_price = $rental_price *$prod->days;
                        }
                    }


                    if(isset($prod->product) && !empty($prod->product)){
                      //  pr($prod->product);
                        if($prod->product->is_long_term_service ==1)
                        {
                            $vendorData->is_long_term_service = 1;
                            $LongTermProducts = $prod->product->LongTermProducts;
                            $is_long_term = 1;
                            if($prod->product->ServicePeriod){
                                $prod->product->ServicePeriods = $prod->product->ServicePeriod->pluck('service_period')->toArray();
                            }
                            if($prod->start_date_time !=''){
                                $prod->service_start_time = convertDateTimeInTimeZone($prod->start_date_time, $user->timezone, 'H:i');
                            }
                            $product_id = $LongTermProducts->product_id;
                            $url_slug   = $LongTermProducts->product->url_slug;
                            $vendor_slug=  $vendorData->vendor->slug;
                            unset($LongTermProducts->product);
                            $LongTermProducts->product   = $this->getProduct($product_id,$vendor_slug,$url_slug,$user,$langId);

                            $prod->product->long_term_products=$LongTermProducts;
                        }

                        if($prod->product->pharmacy_check == 1){
                            $productPrescription = CartProductPrescription::where('cart_id', $cartID)->where('product_id', $prod->product->id)->get()->toArray();
                            $uploadedPrescriptions = [];
                            if(!empty($productPrescription)){
                                foreach($productPrescription as $prescriptions){
                                    $prescriptions['prescription']['prescription_id'] = $prescriptions['id'];
                                    $uploadedPrescriptions[] = $prescriptions['prescription'];
                                }
                            }
                            $prod->product->uploaded_prescriptions = $uploadedPrescriptions;
                        }
                        if($prod->product->sell_when_out_of_stock == 0 && $prod->product->has_inventory == 1){
                            $quantity_check = productvariantQuantity($prod->variant_id);
                            if($quantity_check < $prod->quantity ){
                                $delivery_status=0;
                                $product_out_of_stock = 1;
                            }
                        }
                        $prod->product_out_of_stock =  $product_out_of_stock;
                        $prod->scheduled_date_time =  (($prod->scheduled_date_time !=null)?date('Y-m-d',strtotime(dateTimeInUserTimeZone($prod->scheduled_date_time,$user_timezone))):null);


                        $price_in_currency = $price_in_doller_compare = $pro_disc = $quantity_price = 0;

                        $variantsData = $taxData = $vendorAddons = array();
                        $divider = (empty($prod->doller_compare) || $prod->doller_compare < 0) ? 1 : $prod->doller_compare;
                        $price_in_currency = $prod->pvariant ? $prod->pvariant->price : 0;

                        if(@$prod->pvariant->month_price && $prod->pvariant->week_price &&  @$getAdditionalPreference['is_rental_weekly_monthly_price']){
                            $schedule_days = $prod->additional_increments_hrs_min / 24;
                            if($schedule_days >= 7 && $schedule_days < 30){
                                $price_in_currency = $prod->pvariant->week_price;
                            }elseif($schedule_days >= 30){
                                $price_in_currency = $prod->pvariant->month_price;
                            }
                        }

                         //  GET PRICE from driver
                        if($is_service_product_price_from_dispatch ==1){
                            $price_in_currency = isset($prod->dispatch_agent_price) ? $prod->dispatch_agent_price : 0 ;
                        }
                        $total_markup_charges += $prod->pvariant->markup_price??0;
                        $price_in_doller_compare = $price_in_currency * $clientCurrency->doller_compare;
                        $container_charges_in_currency = $prod->pvariant->container_charges??0.00;
                        $container_charges_in_doller_compare = $prod->pvariant->container_charges??0.00;

                        $quantity_price = $price_in_doller_compare * $prod->quantity;

                        $quantity_price =  (($quantity_price)*($prod->recurring_date_count));

                        $quantity_container_charges = $container_charges_in_doller_compare * $prod->quantity;
                        $quantity_container_charges = decimal_format($quantity_container_charges);
                        $item_count = $item_count + $prod->quantity;

                        // Check if is_cart_checked is 1 then add $vendor_products_total_amount
                        if($prod->is_cart_checked == 1){
                            $proSum = $proSum + $quantity_price + $quantity_container_charges;
                        }
                        $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price;

                        $total_container_charges = $total_container_charges + $quantity_container_charges;
                        $prod->luxury_option_id= $prod->luxury_option_id??'';
                        if (isset($prod->pvariant->image->imagedata) && !empty($prod->pvariant->image->imagedata)) {
                            $prod->cartImg = $prod->pvariant->image->imagedata;
                        } else {
                            $prod->cartImg = (isset($prod->product->media[0]) && !empty($prod->product->media[0])) ? $prod->product->media[0]->image : '';
                        }
                        $prod->faq_count = 0;
                        if( $preferences->product_order_form ==1 ){
                            $prod->faq_count =  ProductFaq::where('product_id',$prod->product->id)->count();
                        }
                        $prod->category_id = $prod->product->category_id;
                        $prod->category_kyc_count = 0;
                        if( $preferences->category_kyc_documents ==1 ){
                            if(  !in_array( $prod->product->category_id, $category_array)){
                                $category_array[] = $prod->product->category_id;
                            }
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
                        //Check product promo code is valid for this product

                        $checkProductPromoCodeController = new PromoCodeController();
                        $productPromoRequest = new Request();
                        $productPromoRequest->setMethod('POST');
                        $productPromoRequest->request->add(['cart_id' => $cartID, 'product_id' => $prod->product_id]);
                        $productPromoCodeResponse = $checkProductPromoCodeController->postProductPromoCodeCheck($productPromoRequest)->getData();
                        if($productPromoCodeResponse->status == 'Success'){
                            $coupon_apply_price+=$price_in_currency * $prod->quantity;
                        }
                        if(($in_or_not == 0 && in_array($prod->product_id,$coupon_product_ids))
                            || ($in_or_not == 1 && !in_array($prod->product_id,$coupon_product_ids))
                            || ($in_or_not == 0 && in_array($vendorData->vendor_id, $coupon_vendor_ids))
                            || ($in_or_not == 1 && !in_array($vendorData->vendor_id, $coupon_vendor_ids))
                            ){
                                $coupon_product_discount = $coupon_product_discount + $quantity_price + $quantity_container_charges;
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

                            $addon_price = 0;
                            // $prod->addon;
                            if (!empty($prod->addon->toArray())) {
                                // return $prod->addon;
                                foreach ($prod->addon as $ck => $addons) {
                                    //return $addons->set;
                                    $opt_quantity_price = 0;
                                    $opt_price_in_currency = $addons->option ? $addons->option->price : 0;
                                    $addon_option=AddonOption::where(['addon_id'=>$addons->addon_id,'id'=>$addons->option_id]);
                                    $addon_title='';
                                    if($addon_option->exists()){
                                        $addon_title=$addon_option->first()->title;
                                        $addon_price=$addon_option->first()->price * $prod->quantity;
                                    }
                                    $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                                    $opt_quantity_price = $opt_price_in_doller_compare * $prod->quantity;
                                    $vendorAddons[$ck]['quantity'] = $prod->quantity;
                                    $vendorAddons[$ck]['addon_id'] = $addons->addon_id;
                                    $vendorAddons[$ck]['option_id'] = $addons->option_id;
                                    $vendorAddons[$ck]['price'] = $opt_price_in_currency;
                                    $vendorAddons[$ck]['addon_title'] = $addons->option->title ?? '';
                                    $vendorAddons[$ck]['quantity_price'] = $opt_quantity_price ;
                                    $vendorAddons[$ck]['option_title'] = $addons->option ? $addons->option->title : $addon_title;
                                    $total_addon_price+=$vendorAddons[$ck]['price_in_cart'] = $addons->option->price ?? $addon_price;
                                    $vendorAddons[$ck]['cart_product_id'] = $addons->cart_product_id;
                                    $vendorAddons[$ck]['multiplier'] = $clientCurrency->doller_compare;
                                    $ttAddon = $ttAddon + $opt_quantity_price;
                                    // $order_sub_total = $order_sub_total + $opt_quantity_price + $prod->pvariant->price;
                                    $order_sub_total = $order_sub_total + $opt_quantity_price ;
                                    $opt_quantity_price_new += $opt_quantity_price;
                                    $quantity_price = $quantity_price + $opt_quantity_price;

                                    if(($in_or_not == 0 && in_array($prod->product_id,$coupon_product_ids))
                                        || ($in_or_not == 1 && !in_array($prod->product_id,$coupon_product_ids))
                                        || ($in_or_not == 0 && in_array($vendorData->vendor_id, $coupon_vendor_ids))
                                        || ($in_or_not == 1 && !in_array($vendorData->vendor_id, $coupon_vendor_ids))
                                        ){
                                            $coupon_apply_price+=$opt_price_in_currency;
                                            $coupon_product_discount = $coupon_product_discount + $opt_quantity_price;
                                    }
                                }
                                $order_sub_total = $order_sub_total + $prod->pvariant->price* $prod->quantity;
                            }else{
                                $order_sub_total = round($order_sub_total  + $prod->pvariant->price*$prod->quantity,2);
                            }
                            $variantsData['discount_amount'] = $pro_disc;
                            $variantsData['coupon_applied'] = $codeApplied;
                            $variantsData['quantity_price'] = $quantity_price;
                            $variantsData['quantity_container_charges'] = $quantity_container_charges;

                            $only_products_amount += $quantity_price;

                            $payable_amount = $payable_amount + $quantity_price;

                            // Check if is_cart_checked is 1 then add $quantity_price in payable amount
                            if($prod->is_cart_checked == 1){
                                // $payable_amount = $payable_amount + $quantity_price + $quantity_container_charges;
                                $payable_amount = $payable_amount + $quantity_container_charges;

                            }

                            if (!empty($prod->product->taxCategory) && count($prod->product->taxCategory->taxRate) > 0) {
                                foreach ($prod->product->taxCategory->taxRate as $tckey => $tax_value) {
                                    $rate = $tax_value->tax_rate;
                                    $tax_amount = ($price_in_doller_compare * $rate) / 100;

                                     //Find vendor Product Discount here
                                     $productPriceAfterVendorDiscount  = productPriceAfterVendorDiscount($prod,$quantity_price,$clientCurrency->doller_compare,$cart);
                                    // $quantity_price = $quantity_price - $productPriceAfterVendorDiscount['vendor_discount_amount'];
                                     if ($productPriceAfterVendorDiscount['deliveryfeeOnCoupon'] == 1) {
                                        $deliveryfeeOnCoupon = 1;
                                    }
                                    if(!$additionalPreferences->is_tax_price_inclusive){
                                        $product_tax = ($quantity_price) * $rate / 100;
                                    }else{
                                        $product_tax = (($quantity_price)  * $rate) / (100 + $rate);
                                    }
                                    $taxData[$tckey]['rate'] = $rate;
                                    $taxData[$tckey]['tax_amount'] = decimal_format($tax_amount);
                                    $taxData[$tckey]['product_tax'] = decimal_format($product_tax);
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
                            }else{
                                 //Find vendor Product Discount here
                                 $productPriceAfterVendorDiscount  = productPriceAfterVendorDiscount($prod,$quantity_price,$clientCurrency->doller_compare,$cart);
                                 $quantity_price = $quantity_price - $productPriceAfterVendorDiscount['vendor_discount_amount'];
                                 if ($productPriceAfterVendorDiscount['deliveryfeeOnCoupon'] == 1) {
                                    $deliveryfeeOnCoupon = 1;
                                }
                            }
                            $prod->taxdata = $taxData;
                            // if($prod->product->)
                            if ( (in_array($action,['delivery','on_demand']) )  && ( $is_service_product_price_from_dispatch !=1 )) {

                                $checkLastMile = 0;
                                $product_tags = '';
                                $NumberOfroutes= 1;
                                //if recurring product
                                $NumberOfroutes = ($prod->recurring_date_count);

                                if (!empty($prod->product->Requires_last_mile) && ($prod->product->Requires_last_mile == 1) ) {
                                    $checkLastMile = 1;
                                    $product_tags = $prod->product->tags;
                                } /** check lont term product product last mile  */
                                else if(($prod->product->is_long_term_service ==1) && !empty($prod->product->LongTermProduct) && $prod->product->LongTermProduct->first()->Requires_last_mile ==1){

                                    $checkLastMile = 1;
                                    $product_tags = $prod->product->LongTermProduct->first()->tags;
                                    $NumberOfroutes = $prod->LongTermProducts ? $prod->LongTermProducts->quantity : 1;
                                }


                            if ($checkLastMile )
                            {



                                    $deliveries = $this->getDeliveryOptions($vendorData,$preferences,$payable_amount,$address, $product_tags,$NumberOfroutes);
                                    $deliveryDuration = 0;
                                    if(isset($deliveries[0]))
                                    {
                                        if($code){
                                            $new = array_filter($deliveries, function ($var) use ($code) {
                                                return ($var['code'] == $code);
                                            });
                                            foreach($new as $rate){
                                                $deliveryCharges = $rate['rate'];
                                                $deliveryDuration = $rate['duration'];
                                            }
                                            if($deliveryCharges)
                                            {
                                                $deliveryCharges = $rate['rate'];
                                                $deliveryDuration = $rate['duration'];
                                            }else{
                                                $deliveryCharges = $deliveries[0]['rate'];
                                                $deliveryDuration = $deliveries[0]['duration'];
                                                $code = $deliveries[0]['code'];
                                            }

                                        }else{
                                            $deliveryCharges = $deliveries[0]['rate'];
                                            $deliveryDuration = $deliveries[0]['duration'];
                                            $code = $deliveries[0]['code'];
                                        }


                                        if($prod->product->individual_delivery_fee == 1) {

                                            $quantity_deliveryCharges = $deliveryCharges*$prod->quantity;
                                            $vendorTotalDeliveryFee = $vendorTotalDeliveryFee + $quantity_deliveryCharges;
                                            CartProduct::where('cart_id', $cart->id)->where('vendor_id', $vendorData->vendor->id)->where('product_id', $prod->product->id)->update(['product_delivery_fee'=>$quantity_deliveryCharges]);
                                            $prod->product_delivery_fee = $quantity_deliveryCharges;
                                        }else{

                                            if($if_previousdeliveryfee_added == 0 && $deliveryCharges > 0){
                                                $vendorTotalDeliveryFee = $vendorTotalDeliveryFee + $deliveryCharges;
                                                $if_previousdeliveryfee_added = 1;
                                            }
                                        }

                                        $deliveryCharges_real = $deliveryCharges_real + $vendorTotalDeliveryFee;
                                        $selType = CartDeliveryFee::where(['cart_id'=>$cartID,'vendor_id'=>$vendorData->vendor_id])->first();
                                        $vendorData->delivery_types = $deliveries;
                                        $vendorData->sel_types = (($selType)?$selType->shipping_delivery_type.'_'.$selType->courier_id:$code);
                                    }

                                    if(isset($vendorTotalDeliveryFee) && !empty($vendorTotalDeliveryFee)){
                                        $dtype = explode('_',$code);
                                        CartDeliveryFee::updateOrCreate(['cart_id' => $cart->id, 'vendor_id' => $vendorData->vendor->id],['delivery_fee' => $vendorTotalDeliveryFee, 'delivery_duration' => $deliveryDuration,'shipping_delivery_type' => $dtype[0]??'D','courier_id'=>$dtype[1]??'0']);
                                    }

                                    // $deliver_charge = $this->getDeliveryFeeDispatcher($vendorData->vendor_id);
                                    // if (!empty($deliver_charge) && $delivery_count == 0) {
                                    //     $delivery_count = 1;
                                    //     $prod->deliver_charge = number_format($deliver_charge, 2, '.', '');
                                    //     $payable_amount = $payable_amount + $deliver_charge;
                                    //     $order_sub_total = $order_sub_total + $deliver_charge;
                                    //     $delivery_fee_charges = $deliver_charge;
                                    // }


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
                // Add Delivery Slot Price In total amount
                if($prod->delivery_date != '' && $prod->slot_price != '' && $prod->slot_id != ''){
                    $payable_amount = $payable_amount + decimal_format($prod->slot_price);

                }
                if (isset($vendorData->coupon) && !empty($vendorData->coupon) ) {
                    if (isset($vendorData->coupon->promo) && !empty($vendorData->coupon->promo)) {
                        if($vendorData->coupon->promo->first_order_only==1){
                            if(Auth::user()){
                                $userOrder = auth()->user()->orders->first();
                                if($userOrder){
                                    $cart->coupon()->delete();
                                    $vendorData->coupon()->delete();
                                    unset($vendorData->coupon);
                                    $PromoDelete =1;
                                }
                            }
                        }
                        if ($PromoDelete !=1) {
                            if(!($vendorData->coupon->promo->expiry_date >= $nowdate) ){
                                $cart->coupon()->delete();
                                $vendorData->coupon()->delete();
                                unset($vendorData->coupon);
                                $PromoDelete =1;
                            }
                        }
                        if ( $PromoDelete !=1) {
                            $minimum_spend = 0;
                            if (isset($vendorData->coupon->promo->minimum_spend)) {
                                $minimum_spend = $vendorData->coupon->promo->minimum_spend * $clientCurrency->doller_compare;
                            }

                            $maximum_spend = 0;
                            if (isset($vendorData->coupon->promo->maximum_spend)) {
                                $maximum_spend = $vendorData->coupon->promo->maximum_spend * $clientCurrency->doller_compare;
                            }
                            if( ($minimum_spend <= $payable_amount ) && ($maximum_spend >= $payable_amount)    )
                            {
                                $dis_amt =0;
                                if ($vendorData->coupon->promo->promo_type_id == 2) {
                                    $dis_amt = $total_discount_percent = $vendorData->coupon->promo->amount;

                                   // $payable_amount -= $total_discount_percent;
                                    $discount_amount = $total_discount_percent;
                                } else {
                                    $gross_coupon_amount = $payable_amount;
                                    if($vendorData->coupon->promo->restriction_on == 0 ){
                                        $gross_coupon_amount = $coupon_apply_price;
                                    }
                                    if ($vendorData->coupon->promo->allow_free_delivery == 1) {
                                        $deliveryfeeOnCoupon = 1;
                                    }
                                    $dis_amt = $percentage_amount = ($gross_coupon_amount * $vendorData->coupon->promo->amount / 100);
                                    // $payable_amount -= $percentage_amount;
                                    $discount_amount = $percentage_amount;
                                }
                                $couponData['coupon_id'] =  $vendorData->coupon->promo->id;
                                $couponData['name'] =  $vendorData->coupon->promo->name;
                                $couponData['dis_amount'] =  $dis_amt;
                                $couponData['disc_type'] = ($vendorData->coupon->promo->promo_type_id == 1) ? 'Percent' : 'Amount';
                                $couponData['expiry_date'] =  $vendorData->coupon->promo->expiry_date;
                                $couponData['allow_free_delivery'] =  $vendorData->coupon->promo->allow_free_delivery;
                                $couponData['minimum_spend'] =  $vendorData->coupon->promo->minimum_spend;
                                $couponData['first_order_only'] = $vendorData->coupon->promo->first_order_only;
                                $couponData['restriction_on'] = ($vendorData->coupon->promo->restriction_on == 1) ? 'Vendor' : 'Product';
                                $is_coupon_applied = 1;
                                $couponApplied = 1;
                            }
                            else{
                                $cart->coupon()->delete();
                                $vendorData->coupon()->delete();
                                unset($vendorData->coupon);
                                $PromoDelete =1;
                            }
                        }
                        if ( $PromoDelete !=1) {
                            if($vendorData->coupon->promo->allow_free_delivery ==1   ){
                                $PromoFreeDeliver = 1;
                                $discount_amount = $discount_amount +  $vendorTotalDeliveryFee;
                            }
                        }
                    }
                  /*  if($rate > 0 && $discount_amount > 0 ){
                        $discount = ($discount_amount * $rate) / 100;
                        $taxable_amount -= $discount;
                    }*/
                }

                $vendorData->isDeliverable = 1;
                if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1)&&($action == 'delivery')) {
                    if (isset($serviceArea)) {
                        if ($serviceArea->isEmpty()) {
                            $vendorData->isDeliverable = 0;
                            $delivery_status = 0;
                            $deliver_charge = 0;
                            $vendorTotalDeliveryFee = 0;
                            $vendorData->delivery_types = '';
                        }
                    }

                 if (!isset($serviceArea)) {
                    $vendorData->isDeliverable = 0;
                    $delivery_status = 0;
                    $deliver_charge = 0;
                    $vendorTotalDeliveryFee = 0;
                    $vendorData->delivery_types = '';
                 }
            } else{

                if (isset($serviceArea)) {
                    if ($serviceArea->isEmpty()) {
                        $vendorData->isDeliverable = 0;
                        $delivery_status = 0;
                        // $deliver_charge = 0;
                        // $vendorTotalDeliveryFee = 0;
                        // $vendorData->delivery_types = '';
                    }
                }
             }


                $payable_amount = $payable_amount + $vendorTotalDeliveryFee ;

                $deliver_charge = $vendorTotalDeliveryFee * $clientCurrency->doller_compare;
                $vendorData->proSum = $proSum;
                $vendorData->addonSum = $ttAddon;
                $vendorData->promo_free_delivery = $PromoFreeDeliver;
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
                    $amount_for_service =  $only_products_amount;
                    $vendor_service_fee_percentage_amount = (($amount_for_service) * $vendorData->vendor->service_fee_percent) / 100 ;
                    $payable_amount = $payable_amount + $vendor_service_fee_percentage_amount;
                }

                if($vendorData->vendor->service_charge_amount > 0){
                     $vendor_service_fee_percentage_amount = $vendorData->vendor->service_charge_amount;
                     $payable_amount = $payable_amount + $vendor_service_fee_percentage_amount;
                 }
                //end applying service fee on vendor products total
                $total_service_fee = $total_service_fee + $vendor_service_fee_percentage_amount;
                if(@$getAdditionalPreference['is_rental_weekly_monthly_price']){
                    $rental_price = $rental_price + $total_service_fee ;
                }

                $vendorData->service_fee_percentage_amount = number_format($vendor_service_fee_percentage_amount, 2, '.', '');
                $vendorData->vendor_gross_total = $payable_amount;
                $vendorData->discount_amount = $discount_amount;
                $vendorData->discount_percent = $discount_percent;
                $vendorData->taxable_amount = decimal_format($taxable_amount);
                $vendorData->payable_amount = $payable_amount - $discount_amount;
                $total_paying = $total_paying + $payable_amount ;
                $total_taxable_amount = $total_taxable_amount + $taxable_amount;
                $total_disc_amount = $total_disc_amount + $discount_amount;
                $total_discount_percent = $total_discount_percent + $discount_percent;
                $vendorData->vendor->is_vendor_closed = $is_vendor_closed;
                if (!empty($vendorData->coupon->promo)) {
                    unset($vendorData->coupon->promo);
                }


                // if (in_array(1, $subscription_features)) {
                //     $subscription_discount = $subscription_discount + $deliver_charge;
                // }
                // $total_subscription_discount = $total_subscription_discount + $subscription_discount;
                if($is_service_product_price_from_dispatch !=1){ // no need to check slot and web styling
                    if (($vendorData->vendor->show_slot == 0)  ) {
                        if (($vendorData->vendor->slotDate->isEmpty()) && ($vendorData->vendor->slot->isEmpty())) {
                            $vendorData->vendor->is_vendor_closed = 1;
                            if ($delivery_status != 0) {
                                $delivery_status = 0;
                            }
                        } else {
                            $vendorData->vendor->is_vendor_closed = 0;
                        }
                    }
                    // $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();
                    // if(isset($set_template)  && $set_template->template_id != 9){
                    //     if($vendorData->vendor->$action == 0){
                    //         $vendorData->is_vendor_closed = 1;
                    //         $delivery_status = 0;
                    //     }
                    // }
                }

                $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);

                if($getAdditionalPreference['is_price_by_role'] == 1){
                    $role_id = (Auth::user() != null) ? Auth::user()->role_id : 1;
                    $vendor_min_amount_data = VendorMinAmount::where('vendor_id',$vendorData->vendor->id)
                    ->where('role_id', $role_id)->first();
                    $vendorData->vendor->order_min_amount = empty($vendor_min_amount_data)?0:$vendor_min_amount_data->order_min_amount;
                }

                if((float)($vendorData->vendor->order_min_amount) > $payable_amount){  # if any vendor total amount of order is less then minimum order amount
                    $delivery_status = 0;
                }
                $promoCodeController = new PromoCodeController();
                $promoCodeRequest = new Request();
                $promoCodeRequest->setMethod('POST');
                $promoCodeRequest->request->add(['vendor_id' => $vendorData->vendor_id, 'cart_id' => $cartID ,'is_cart' => 1 ]);
                $promoCodeResponse = $promoCodeController->postPromoCodeList($promoCodeRequest)->getData();
                if($promoCodeResponse->status == 'Success'){
                    if($promoCodeResponse->data){
                        $is_promo_code_available = 1;
                    }
                }
                $vendorData->is_promo_code_available = $is_promo_code_available;

                if($deliveryfeeOnCoupon == 1){
                    $totalFreeDeliveryCharges +=$vendorTotalDeliveryFee;
                    $vendorTotalDeliveryFee = 0;
                }

                $totalDeliveryCharges+=$vendorTotalDeliveryFee;

            //All other tax calculations
            if(!empty($taxRates)){
                $delivery_charges_tax_rate = 0;
            if($vendorData->vendor->delivery_charges_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->delivery_charges_tax_id])){
                    $delivery_charges_tax_rate=$taxRates[$vendorData->vendor->delivery_charges_tax_id]['tax_rate'];
                }
            }

            $fixed_fee_tax_rate = 0;
            if($vendorData->vendor->fixed_fee_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->fixed_fee_tax_id])){
                     $fixed_fee_tax_rate=$taxRates[$vendorData->vendor->fixed_fee_tax_id]['tax_rate'];
                }
            }


            $service_charges_tax_rate = 0;
            if($vendorData->vendor->service_charges_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->service_charges_tax_id])){
                    $service_charges_tax_rate=$taxRates[$vendorData->vendor->service_charges_tax_id]['tax_rate'];
                }
            }

            $markup_price_tax_rate = 0;
            if($vendorData->vendor->markup_price_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->markup_price_tax_id])){
                    $markup_price_tax_rate=$taxRates[$vendorData->vendor->markup_price_tax_id]['tax_rate'];
                }
            }

            $container_charges_tax_rate = 0;
            if($vendorData->vendor->container_charges_tax_id!=null){
                if(isset($taxRates[$vendorData->vendor->container_charges_tax_id])){
                    $container_charges_tax_rate=$taxRates[$vendorData->vendor->container_charges_tax_id]['tax_rate'];
                }
            }


            if(!$additionalPreferences->is_tax_price_inclusive)
            {
                if($vendorData->vendor->container_charges_tax)
                $container_charges_tax =  $total_container_charges * $container_charges_tax_rate/100;

                if($vendorData->vendor->delivery_charges_tax)
                $deliver_fee_charges_tax +=  $deliveryCharges * $delivery_charges_tax_rate/100;

                if($vendorData->vendor->service_charges_tax)
                $total_service_fee_tax +=  $vendor_service_fee_percentage_amount * $service_charges_tax_rate/100;

                if($vendorData->vendor->fixed_fee_tax)
                $total_fixed_fee_tax +=  $total_fixed_fee_amount * $fixed_fee_tax_rate/100;

                if($vendorData->vendor->add_markup_price)
                $total_markup_fee_tax +=  $total_markup_charges * $markup_price_tax_rate/100;
            // if($vendorData->vendor->delivery_charges_tax)
            // $deliver_fee_charges +=  $deliveryCharges_real * $delivery_charges_tax_rate/100;

            // if($vendorData->vendor->service_charges_tax)
            // $total_service_fee +=  $vendor_service_fee_percentage_amount * $service_charges_tax_rate/100;

            }else{

                if($vendorData->vendor->delivery_charges_tax)
                $deliver_fee_charges_tax += ($deliveryCharges * $delivery_charges_tax_rate)/(100 + $delivery_charges_tax_rate);

                if($vendorData->vendor->service_charges_tax)
                $total_service_fee_tax +=   ($vendor_service_fee_percentage_amount * $service_charges_tax_rate)/(100 + $service_charges_tax_rate);

                if($vendorData->vendor->fixed_fee_tax)
                $total_fixed_fee_tax =  ($total_fixed_fee_amount * $fixed_fee_tax_rate)/(100 + $fixed_fee_tax_rate);

                if($vendorData->vendor->add_markup_price)
                $total_markup_fee_tax +=  ($total_markup_charges * $markup_price_tax_rate)/(100 + $markup_price_tax_rate);

                if($vendorData->vendor->delivery_charges_tax)
                $deliver_fee_charges_tax +=  $deliveryCharges_real * $delivery_charges_tax_rate/100;

            }

            } //End Tax Code

            // Add Delivery Slot Price In total amount
            if($prod->delivery_date != '' && $prod->slot_price != '' && $prod->slot_id != ''){
                $delivery_slot_amount += decimal_format($prod->slot_price);
            }

            }//End cart Vendor loop
            ++$vondorCnt;
        }

        // calculate subscription discount
        $subscription_discount =0;
        if ($user_subscription) {

            foreach ($user_subscription->features as $feature) {
                if ($feature->feature_id == 1) {
                    $subscription_discount = $subscription_discount + $total_delivery_amount;
                }
                elseif ($feature->feature_id == 2) {
                    $off_percentage_discount = ($feature->percent_value * ($total_paying - $total_delivery_amount) / 100);
                    $subscription_discount = $subscription_discount + $off_percentage_discount;
                }
            }
        }

        $total_subscription_discount = $total_subscription_discount + $subscription_discount;

        $cart_product_luxury_id = CartProduct::where('cart_id', $cartID)->select('luxury_option_id', 'vendor_id','additional_increments_hrs_min')->first();
        if (isset($cart_product_luxury_id) && isset($cart_product_luxury_id->luxury_option_id)) {
            if ($cart_product_luxury_id->luxury_option_id == 2 || $cart_product_luxury_id->luxury_option_id == 3) {
                $vendor_address = Vendor::where('id', $cart_product_luxury_id->vendor_id)->select('address')->first();
                $cart->address = $vendor_address->address;
            }
        }
        if ($total_subscription_discount > 0) {
            $total_disc_amount = $total_disc_amount + $total_subscription_discount;
            $cart->total_subscription_discount = $total_subscription_discount * $clientCurrency->doller_compare;
        }

        if($cartData->count() == '1'){
            $vendorId = $cartData[0]->vendor_id;
            //type must be a : delivery , takeaway,dine_in
            $duration = Vendor::where('id',$vendorId)->select('slot_minutes','closed_store_order_scheduled')->first();
            $slotsDate = findSlot('',$vendorId,$type,'api');
            $slots = showSlot($slotsDate,$vendorId,$type,$duration->slot_minutes, 1,'',$cartID);
            $cart->slots = $slots;
            
            foreach ($cartData as $ven_key => $vendorData) {
                $vendorData->visibleSlots = $vendorData['vendorProducts'][0]['schedule_slot'];
            }
            if($preferences->business_type == 'laundry'){
                $dropoff_slots = showSlot($slotsDate,$vendorId,$type,$duration->slot_minutes, 2,'',$cartID);
                $cart->dropoff_slots = $dropoff_slots;
            }else{
                $cart->dropoff_slots = [];
            }
            if(count($slots)>0){
                $cart->closed_store_order_scheduled = $duration->closed_store_order_scheduled ?? 0;
             }else{
                $cart->closed_store_order_scheduled = 0;
            }
        }else{
            $duration = (object)['closed_store_order_scheduled'=>'0'];
            $slots = [];
            $dropoff_slots = [];
            $cart->slots = [];
            $cart->closed_store_order_scheduled = 0;
        }
        $cart->category_kyc_count = 0;
        $cart->without_category_kyc = 0;
        $cart->category_ids = '';
        if( $preferences->category_kyc_documents ==1 ){

            $category_query =  CategoryKycDocuments::whereHas('categoryMapping',function($q) use($category_array){
                $q->whereIn('category_id',$category_array);
            });

            $category_kyc_document_ids =  $category_query->pluck('id');
            $category_kyc_document_ids = $category_kyc_document_ids->isNotEmpty() ? $category_kyc_document_ids->toArray() : [];

            $category_kyc_count =  $category_query->count();

            $is_alrady_submit = CaregoryKycDoc::whereIn('category_kyc_document_id', $category_kyc_document_ids)->where('cart_id',$cartID)->count();
            if( $category_kyc_count  > 0 && ($is_alrady_submit  !=  $category_kyc_count )){
                $cart->category_kyc_count = $category_kyc_count;
                $cart->category_ids = implode( ',',$category_array);
            }

            $ALLcategory_kyc_documents =CategoryKycDocuments::whereHas('categoryMapping',function($q) use($category_array){
                $q->whereIn('category_id',$category_array);
            })->with('primary')->get();
            foreach ($ALLcategory_kyc_documents as $vendor_registration_document) {
                if($vendor_registration_document->is_required == 1){

                    $check = CaregoryKycDoc::where(['cart_id'=>$cartID,'category_kyc_document_id'=>$vendor_registration_document->id])->first();
                    if($check)
                    {
                        $cart->without_category_kyc = 1;
                    }
                }
            }
        }else{
            $cart->without_category_kyc = 1;
        }
        $other_taxes_string='tax_fixed_fee:'.$total_fixed_fee_tax.',tax_service_charges:'.$total_service_fee_tax.',tax_delivery_charges:'.$deliver_fee_charges_tax.',tax_markup_fee:'.$total_markup_fee_tax.',product_tax_fee:'.$total_taxable_amount.',container_charges_tax:'.$container_charges_tax;

        $userCart = Cart::find($cartID);
        $userCart->total_other_taxes  = $other_taxes_string;
        $userCart->save();
        // add delivery fee charges as other tax as per web code.
        $cart->other_taxes = $deliver_fee_charges;
        $fixedFeeNomenclatures = $this->fixedFee($langId).' tax';

        $cart->specific_taxes = array(
            ['label' => $fixedFeeNomenclatures, 'value' => decimal_format($total_fixed_fee_tax)],
            ['label' => 'Service fee tax', 'value' => decimal_format($total_service_fee_tax)],
            ['label' => 'Deliver fee tax', 'value' => decimal_format($deliver_fee_charges_tax)],
            ['label' => 'Markup fee tax', 'value' => decimal_format($total_markup_fee_tax)],
            ['label' => 'Container fee tax', 'value' => decimal_format($container_charges_tax)],
            ['label' => "Total ".@$taxData[0]['identifier']." amount", 'value' => decimal_format($total_taxable_amount)]
        );


        $cart->total_service_fee = decimal_format($total_service_fee);
        $cart->total_container_charges = decimal_format($total_container_charges);
        $cart->total_markup_charges = decimal_format($total_markup_charges);
        $cart->total_tax = decimal_format($total_fixed_fee_tax + $total_service_fee_tax + $deliver_fee_charges_tax + $total_markup_fee_tax + $container_charges_tax + $total_taxable_amount);
        $cart->tax_details = $tax_details;
        $cart->total_taxable_amount = decimal_format($total_taxable_amount);
        $cart->total_delivery_fee = $totalDeliveryCharges;
        $cart->total_fixed_fee_amount = $total_fixed_fee_amount;
        $cart->gross_paybale_amount = $order_sub_total;

        $cart->total_addon_price = $total_addon_price;
        $cart->total_discount_amount = $total_disc_amount * $clientCurrency->doller_compare;
        $cart->total_cashback= @$total_cashback;
        $cart->products = $cartData;
        $cart->item_count = $item_count;
        $cart->is_long_term_added = $is_long_term;

        $cart->delivery_slot_amount = $delivery_slot_amount;

        $temp_total_paying = $total_paying  + $total_tax - $total_disc_amount;
        if ($cart->user_id > 0) {
            //$loyalty_amount_saved = $this->getLoyaltyPoints($cart->user_id, $clientCurrency->doller_compare);
            $loyaltyCheck = $this->getOrderLoyalityAmount($user,$clientCurrency);
            $loyalty_amount_saved = $loyaltyCheck->loyalty_amount_saved;
            // if($total_paying > $cart->loyalty_amount){
            //    $cart->loyalty_amount = 0.00;
            // }
            // $cart->wallet = $this->getWallet($cart->user_id, $clientCurrency->doller_compare, $currency);
        }

        if ($loyalty_amount_saved  >= $temp_total_paying) {
            if($temp_total_paying > 0) {
                $loyalty_amount_saved = $temp_total_paying;
            } else {
                $loyalty_amount_saved = 0;
            }
            $cart->total_payable_amount = 0.00;
        } else {
            $cart->total_payable_amount = ($total_paying  + $cart->total_tax) -   ($loyalty_amount_saved);
        }
        /* if($total_taxable_amount>0){
            $cart->total_payable_amount = $cart->total_payable_amount +$total_taxable_amount;
        } */

        // add other taxes amount as well in total payable amount.

        if($cart->other_taxes>0){
            $cart->total_payable_amount = $cart->total_payable_amount + $cart->other_taxes;
        }
        if($cart->total_fixed_fee_amount){
            $cart->total_payable_amount = $cart->total_payable_amount +$cart->total_fixed_fee_amount;
        }
        // if(!empty($total_container_charges)){
        //     $cart->total_payable_amount  += $total_container_charges;
        // }

        if(@$rental_price){
            $cart->total_payable_amount = $rental_price;
        }
        // if(!empty($total_service_fee)){
        //     $cart->total_payable_amount  += $total_service_fee;
        // }

        // if(!empty($totalDeliveryCharges)){
        //     $cart->total_payable_amount  += $totalDeliveryCharges;
        // }

        if($delivery_status == 0 && @$duration->closed_store_order_scheduled == 1) {
            $cart->deliver_status = 1;
        }else{
            $cart->deliver_status = $delivery_status;
        }
        if($cart->deliver_status == 0){
            $cart->cart_error_message = __("We cannot deliver this product");
        }
        $cart->loyalty_amount = $loyalty_amount_saved;

        if (isset($cart_product_luxury_id) && isset($cart_product_luxury_id->luxury_option_id) && $cart_product_luxury_id->luxury_option_id ==4) {
            if($cart_product_luxury_id->additional_increments_hrs_min && $prod->pvariant->incremental_price_per_min){
                $additional_price = ($cart_product_luxury_id->additional_increments_hrs_min / $prod->pvariant->incremental_price_per_min);
                $cart->total_payable_amount = number_format((float)$cart->total_payable_amount + $additional_price, 2, '.', '');
                $cart->additional_price = $additional_price;
            }
        }
        else{
            $cart->total_payable_amount= number_format((float)$cart->total_payable_amount, 2, '.', '');
        }


        $cart->total_payable_amount += ($securityAmount + $rentalProtection + $bookingOption - $total_disc_amount);
        //$cart->total_payable_amount= number_format((float)$cart->total_payable_amount, 2, '.', '');

        //mohit sir branch code updated by sohail farm meat
        $pendingAmount = 0;
        $advancePayableAmount = 0;
        $totalAmount = 0;
        $getAdditionalPreference = getAdditionalPreference(['advance_booking_amount', 'advance_booking_amount_percentage']);
        if($action == 'takeaway' && !empty($getAdditionalPreference['advance_booking_amount']) && !empty($getAdditionalPreference['advance_booking_amount_percentage']) && ($getAdditionalPreference['advance_booking_amount_percentage'] > 0) && ($getAdditionalPreference['advance_booking_amount_percentage'] < 101) )
        {
            $advancePayableAmount = ($cart->total_payable_amount * $getAdditionalPreference['advance_booking_amount_percentage']) / 100;
            $pendingAmount = $cart->total_payable_amount - $advancePayableAmount;
            $totalAmount = $cart->total_payable_amount;
            $cart->total_payable_amount = $advancePayableAmount;
        }

        $wallet_amount_used = 0;
        if (isset($user)) {
            if ($user->balanceFloat > 0) {
                $wallet_amount_used = $user->balanceFloat;
                if ($clientCurrency) {
                    $wallet_amount_used = $user->balanceFloat * $clientCurrency->doller_compare;
                }
                if ($wallet_amount_used > $cart->total_payable_amount) {
                    $wallet_amount_used = $cart->total_payable_amount;
                }
                $cart->total_payable_amount = $cart->total_payable_amount - $wallet_amount_used;
                $cart->wallet_amount_used = $wallet_amount_used;
            }
        }
        // $cart->total_payable_amount= number_format((float)$cart->total_payable_amount, 2, '.', '');
        $cart->total_amount= number_format((float)$totalAmount, 2, '.', '');
        $cart->advance_payable_amount= number_format((float)$advancePayableAmount, 2, '.', '');
        $cart->pending_amount= number_format((float)$pendingAmount, 2, '.', '');
        //till here

        $cart->vendor_details = $vendor_details;
        $cart->cart_dinein_table_id = $cart_dinein_table_id;
        $cart->upSell_products = ($upSell_products) ? $upSell_products->first() : collect();
        $cart->crossSell_products = ($crossSell_products) ? $crossSell_products->first() : collect();
        $cart->delay_date =  $delay_date??0;
        $cart->pickup_delay_date =  $pickup_delay_date??0;
        $cart->dropoff_delay_date =  $dropoff_delay_date??0;
        if($preferences->business_type == 'laundry'){
            $cart->same_day_delivery_for_schedule =  $preferences->same_day_delivery_for_schedule;
            $cart->off_scheduling_at_cart =  $preferences->off_scheduling_at_cart;
        }

        $total_payable_amount_calc_tip = $cart->total_payable_amount - $total_taxable_amount;

        $cart->tip = array(
            ['label' => '5%', 'value' => decimal_format(0.05 * $total_payable_amount_calc_tip)],
            ['label' => '10%', 'value' => decimal_format(0.1 * $total_payable_amount_calc_tip)],
            ['label' => '15%', 'value' => decimal_format(0.15 * $total_payable_amount_calc_tip)]
        );

        if(@$rental_price > 0){
            $cart->total_payable_amount = $rental_price;
        }
        $cart->product_from_submitted =1;
        foreach ($cartData as  $vendorData) {
            foreach($vendorData->vendorProducts as $vendorProduct){
             
                 $product=CartProduct::where('cart_id', $vendorProduct->cart_id)->where('product_id',$vendorProduct->product_id)->first();
                 if($product->user_product_order_form == null){
                    $cart->product_from_submitted =0;
                 }
            }
        }

        return $cart;
        }catch(\Exception $ex){
            return [];
        }
    }

    public function uploadPrescriptions(Request $request){
        $user = Auth::user();
        $user_id = $user->id;
        if ($user) {
            $cart = Cart::select('id')->where('status', '0')->where('user_id', $user_id)->first();
            foreach ($request->prescriptions as $prescription) {
                $cart_product_prescription = new CartProductPrescription();
                $cart_product_prescription->cart_id = $cart->id;
                $cart_product_prescription->vendor_id = $request->vendor_id;
                $cart_product_prescription->product_id = $request->product_id;
                $cart_product_prescription->prescription = Storage::disk('s3')->put('prescription', $prescription, 'public');
                $cart_product_prescription->save();
            }
        }
        return response()->json(['status' => 'success', 'message' => "Prescription upload successfully"]);
    }

    public function deleteProductPrescription(Request $request){
        if(!empty($request->prescription_id)){
            CartProductPrescription::where('id', $request->prescription_id)->delete();
            return response()->json(['status' => 'success', 'message' => "Prescription remove successfully"]);
        }
    }

    public function checkScheduleSlots(Request $request)
    {
        $slot = [];
        $vendorId = $request->vendor_id??0;
        $delivery = $request->delivery??'delivery';
        $cartId = $request->cart_id??0;
        //type must be a : delivery , takeaway,dine_in
        $duration = Vendor::where('id',$vendorId)->select('slot_minutes')->first();
        $duration = $duration->slot_minutes??'';

        $slots = showSlot($request->date,$vendorId,$delivery,$duration, 1, 'pickup',$cartId); // Added 1 for pickup

        if(count($slots)<=0){
            $slot = [];
        }else{
            $slot = $slots;
        }

        $cart = Cart::where('user_id',Auth::user()->id)->first();
        if($cart){
            if($cart->cartProducts->count() >0){
           return response()->json(['status' => 400 ,  'msg'=>'You have already added product in cart, please complete the order first.','product_exist'=>1]);
            }
        }

        $data = ['data'=>$slots];

        return response()->json($data);
    }

    /**
    * GET Request
    * To Get Drop Off Slots
    * Added By Ovi
    */
    public function checkScheduleDropoffSlots(Request $request)
    {
        $slot = [];
        $vendorId = $request->vendor_id??0;
        $delivery = $request->delivery??'delivery';
        $duration = Vendor::where('id',$vendorId)->select('slot_minutes')->first();
        $slots = showSlot($request->date,$vendorId,$delivery,$duration->slot_minutes, 2, 'dropoff');
        if(count($slots)<=0){
            $slot = [];
        }else{
            $slot = $slots;
        }

        return response()->json($slot);
    }


    public function getDeliveryFeeDispatcher($vendor_id, $dispatcher_tags='')
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $customer = User::find(Auth::id());
                $cus_address = UserAddress::where('user_id', Auth::id())->orderBy('is_primary', 'desc')->first();
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
                    $postdata =  ['locations' => $location, 'agent_tag' => (!empty($dispatcher_tags)?$dispatcher_tags:'')];
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
                        $response_array[] = array('delivery_fee' => $response['total'], 'total_duration' => $response['total_duration']);
                        return $response_array;
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

    public function addVendorTableToCart(Request $request, $domain = '')
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if ($user) {
                $cart = Cart::select('id')->where('status', '0')->where('user_id', $user->id)->firstOrFail();
                $cartData = CartProduct::where('cart_id', $cart->id)->where('vendor_id', $request->vendor_id)->update(['vendor_dinein_table_id' => $request->table]);
                DB::commit();
                return response()->json(['status' => 'Success', 'message' => 'Table has been selected']);
            } else {
                return response()->json(['status' => 'Error', 'message' => 'Invalid user']);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['status' => 'Error', 'message' => $ex->getMessage()]);
        }
    }

    public function updateSchedule(Request $request, $domain = '')
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            if ($user) {
                if(isset($request->slot)){
                    $fslot = explode(' - ',$request->slot);
                    $fslot = $fslot[0];
                }

                if(isset($request->dropoff_scheduled_slot)){
                    $dslot = explode(' - ', $request->dropoff_scheduled_slot);
                    $dslot = $dslot[0];
                }
                if ($request->task_type == 'now') {
                    $request->schedule_dt = Carbon::now()->format('Y-m-d H:i:s');
                } else {
                    if(isset($request->schedule_dt) && !empty($request->schedule_dt)){
                        $request->schedule_dt = (isset($fslot)) ? $request->schedule_dt.'T'.$fslot : $request->schedule_dt;
                        $request->schedule_dt = Carbon::parse($request->schedule_dt, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                    }
                }


                if(isset($request->schedule_pickup) && !empty($request->schedule_pickup))    # for pickup laundry
                $request->schedule_pickup = Carbon::parse((isset($fslot)) ? $request->schedule_pickup.'T'.$fslot : $request->schedule_pickup, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');

                if(isset($request->schedule_dropoff) && !empty($request->schedule_dropoff))  # for pickup laundry
                $request->schedule_dropoff = Carbon::parse((isset($fslot)) ? $request->schedule_dropoff.'T'.$dslot : $request->schedule_dropoff, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');

                Cart::where('status', '0')->where('user_id', $user->id)->update(['specific_instructions' => $request->specific_instructions ?? null,
                'schedule_type' => $request->task_type??null,
                'scheduled_date_time' => $request->schedule_dt??null,
                'scheduled_slot' => $request->slot??null,
                'dropoff_scheduled_slot' => $request->dropoff_scheduled_slot??null,
                'comment_for_pickup_driver' => $request->comment_for_pickup_driver??null,
                'comment_for_dropoff_driver' => $request->comment_for_dropoff_driver??null,
                'comment_for_vendor' => $request->comment_for_vendor??null,
                'schedule_pickup' => $request->schedule_pickup??null,
                'schedule_dropoff' => $request->schedule_dropoff??null]);
                DB::commit();
                return response()->json(['status' => 'Success', 'message' => 'Cart has been scheduled']);
            } else {
                return response()->json(['status' => 'Error', 'message' => 'Invalid user']);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['status' => 'Error', 'message' => $ex->getMessage()]);
        }
    }

    public function checkSlotOrders(Request $request)
    {
        // Get Logged in user
       $user = Auth::user();

       $client_timezone = DB::table('clients')->first('timezone');
       $timezone = (!empty($user->timezone))?$user->timezone:$client_timezone->timezone;

       $schedule_datetime = $request->schedule_datetime;
       $schedule_slot     = $request->schedule_slot;
       $vendor_id         = $request->vendor_id;

        // Get current vendor
        $vendor = Vendor::find($vendor_id);
        $orders_per_slot = $vendor->orders_per_slot;
        $orderCount = 0;
        // Get Vendor orders
        $orderVendors = OrderVendor::where('vendor_id', $vendor->id)->get();

        foreach($orderVendors as $orderVendor){
            // Get orders of current vendor where scheduled_slot and schedule_pickup_datetime is same as received from frontend.
            $order = Order::where('id', $orderVendor->order_id)->where('scheduled_slot', $schedule_slot)->first();

            $if_order_scheduled = 0;
            if($order){
                $schedule_pickup = Carbon::parse($order->scheduled_date_time);
                $schedule_pickup_final = convertDateTimeInTimeZone($schedule_pickup, $timezone, 'Y-m-d');
                // dump($schedule_pickup_final);

                if($schedule_pickup_final == $schedule_datetime){
                    // Increment orderCount and return this count to front end for validation
                    $orderCount++;
                    $if_order_scheduled = 1;
                }
            }

            if($orderVendor->schedule_slot == $schedule_slot && $if_order_scheduled == 0){
                $schedule_pickup = Carbon::parse($orderVendor->scheduled_date_time);
                $schedule_pickup_final = convertDateTimeInTimeZone($schedule_pickup, $timezone, 'Y-m-d');

                if($schedule_pickup_final == $schedule_datetime){
                    // Increment orderCount and return this count to front end for validation
                    $orderCount++;
                }
            }
        }

        // Return JSON Response
        return response()->json(['status' => 'Success',
            'orderCount' => $orderCount,
            'orders_per_slot' => $orders_per_slot,
        ], 200);
    }

    public function checkIsolateSingleVendor(Request $request, $domain = '')
    {
        try {
            $preference = ClientPreference::first();
            $user = Auth::user();
            if ($user->id && $user->id > 0) {
                $cart_detail = Cart::where('user_id', $user->id)->first();
            } else {
                $cart_detail = Cart::where('unique_identifier', $user->system_user)->first();
            }
            if ((isset($preference->isolate_single_vendor_order)) && ($preference->isolate_single_vendor_order == 1) && (!empty($cart_detail))) {
                $checkVendorId = CartProduct::where('vendor_id', '!=', $request->vendor_id)->where('cart_id', $cart_detail->id)->first();
                return response()->json(['status' => 'Success', 'otherVendorExists' => ($checkVendorId ? 1 : 0), 'isSingleVendorEnabled' => 1]);
            } else {
                return response()->json(['status' => 'Success', 'otherVendorExists' => 0, 'isSingleVendorEnabled' => 0]);
            }
        } catch (\Exception $ex) {
            return response()->json(['status' => 'Error', 'message' => $ex->getMessage()]);
        }
    }


    public function updateProductSchedule(Request $request, $domain = '')
    {
        DB::beginTransaction();
        try{
            $user = Auth::user();
            if ($user) {
                if($request->task_type == 'now'){
                    $request->schedule_dt = Carbon::now()->format('Y-m-d H:i:s');
                }else{
                    $request->schedule_dt = Carbon::parse($request->schedule_dt, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                }
                CartProduct::where('id', $request->cart_product_id)->update(['schedule_type' => $request->task_type, 'scheduled_date_time' => $request->schedule_dt,'schedule_slot' => $request->schedule_time]);
                DB::commit();
                return response()->json(['status'=>'Success', 'message'=>'Cart has been scheduled']);
            }
            else{
                return response()->json(['status'=>'Error', 'message'=>'Invalid user']);
            }
        }
        catch(\Exception $ex){
            DB::rollback();
            return response()->json(['status'=>'Error', 'message'=>$ex->getMessage()]);
        }
    }

    # repeat order vendor wise

    public function repeatOrder($domain = '', Request $request){

        $order_vendor_id = $request->order_vendor_id;
        $cart_id = $request->cart_id;
        $getallproduct = OrderProduct::where('order_vendor_id',$order_vendor_id)->get();

        if(isset($cart_id) && !empty($cart_id)){
            CartProduct::where('cart_id', $cart_id)->delete();
            CartCoupon::where('cart_id', $cart_id)->delete();
            CartAddon::where('cart_id', $cart_id)->delete();
        }

        foreach($getallproduct as $data){
            $request->request->add(['vendor_id' => $data->vendor_id,
                'sku' => $data->product->sku,
                'quantity' => $data->quantity,
                'product_variant_id' => $data->variant_id
            ]);
            if(isset($getallproduct->order) && !empty($getallproduct->order))
            $type = LuxuryOption::where('id',$getallproduct->order->luxury_option_id)->value('title');


            $request->type = $type ?? 'delivery';

            $addonID = OrderProductAddon::where('order_product_id',$data->id)->pluck('addon_id');
            $addonoptID = OrderProductAddon::where('order_product_id',$data->id)->pluck('option_id');

            if(count($addonID))
            $request->request->add(['addon_ids' => $addonID->toArray()]);

            if(count($addonoptID))
            $request->request->add(['addon_options' => $addonoptID->toArray()]);

            $this->add($request);

        }

       return response()->json(['status' => 'success', 'message' => 'Order added to cart.']);


    }


    //Fetch all delivery fee option
    public function getDeliveryOptions($vendorData, $preferences, $payable_amount, $address, $dispatcher_tags='', $totalRoute = '1')
    {

        $option = array();
        $delivery_count = 0;
        $delivery_duration = 0;
        try {
            if($vendorData->vendor_id)
            {
                Session()->put('vid',$vendorData->vendor_id);

        if($preferences->static_delivey_fee != 1)
        {
            //Dispatcher Delivery changes code
            $deliver_response_array = $this->getDeliveryFeeDispatcher($vendorData->vendor_id, $dispatcher_tags);
            if (!empty($deliver_response_array[0])){
                $deliver_charge = (!empty($deliver_response_array[0]['delivery_fee']))?number_format(($deliver_response_array[0]['delivery_fee']*$totalRoute), 2, '.', ''):'0.00';
                $delivery_duration = (!empty($deliver_response_array[0]['total_duration']))?number_format($deliver_response_array[0]['total_duration'], 0, '.', ''):'0.00';

                if ($deliver_charge > 0) {
                        $option[] = array(
                        'type'=>'D',
                        'courier_name'=>__('Dispatcher'),
                        'rate' => $deliver_charge,
                        'courier_company_id' => 0,
                        'etd' => 0,
                        'duration' => $delivery_duration,
                        'etd_hours' => 0,
                        'estimated_delivery_days' => 0,
                        'code' => 'D_0'
                    );
                }

            }

                //Borzoe Delivery changes code
                $borzoe_deliver_fee = $this->borzoeDelivery($vendorData->vendor_id);
                $deliverFee = json_decode($borzoe_deliver_fee);
                $borzoe_deliver_fee = $deliverFee->order->payment_amount;
                if ($borzoe_deliver_fee > 0) {
                    $borzoe_deliver_fee = decimal_format($borzoe_deliver_fee);
                    $optionBorzoeApi[] = array(
                        'type' => 'B',
                        'courier_name' => __('Borzoe'),
                        'rate' => $borzoe_deliver_fee,
                        'courier_company_id' => 0,
                        'etd' => 0,
                        'etd_hours' => 0,
                        'duration' => 0,
                        'estimated_delivery_days' => 0,
                        'code' => 'B_0'
                    );
                    $option = array_merge($option, $optionBorzoeApi);
                }
                //End Borzoe Delivery changes code


        //Lalamove Delivery changes code
        $lalamove = new LalaMovesController();
        $deliver_lalmove_fee = $lalamove->getDeliveryFeeLalamove($vendorData->vendor_id);
        if($deliver_lalmove_fee>0)
        {
            $deliver_charge_lalamove = number_format($deliver_lalmove_fee, 2, '.', '');

            $optionLala[] = array(
                'type'=>'L',
                'courier_name'=>__('Lalamove'),
                'rate' => $deliver_charge_lalamove,
                'courier_company_id' => 0,
                'etd' => 0,
                'etd_hours' => 0,
                'duration' => 0,
                'estimated_delivery_days' => 0,
                'code' => 'L_0'
            );
            $option = array_merge($option,$optionLala);
        }

        //End Lalamove Delivery changes code

        //d4bdunzo Delivery changes code
        $d4bdunzo = new D4BDunzoController();
        $deliver_d4bdunzo_data= $d4bdunzo->quote($vendorData->vendor_id);
        if($deliver_d4bdunzo_data['estimated_price']>0)
        {
            $deliver_charge_d4bdunzo = decimal_format($deliver_d4bdunzo_data['estimated_price']);
            $optionD4Dunzo[] = array(
                'type'=>'D4',
                'courier_name'=>__('D4B Dunzo'),
                'rate' => $deliver_charge_d4bdunzo,
                'duration' => $deliver_d4bdunzo_data['eta']['pickup'] +  $deliver_d4bdunzo_data['eta']['dropoff'],
                'courier_company_id' => 0,
                'etd' => 0,
                'etd_hours' => 0,
                'estimated_delivery_days' => 0,
                'code' => 'D4_0'
            );
            $option = array_merge($option,$optionD4Dunzo);
        }
        //End d4bdunzo Delivery changes code


        //Kwik Delivery changes code
        $kwick = new QuickApiController();
        $deliver_fee = $kwick->getDeliveryFeeKwikApi($vendorData->vendor_id);
        if($deliver_fee>0)
        {
            $deliver_fee = decimal_format($deliver_fee);

            $optionKwikApi[] = array(
                'type'=>'K',
                'courier_name'=>__('KwikApi'),
                'rate' => $deliver_fee,
                'courier_company_id' => 0,
                'etd' => 0,
                'etd_hours' => 0,
                'duration' => 0,
                'estimated_delivery_days' => 0,
                'code' => 'K_0'
            );
            $option = array_merge($option,$optionKwikApi);
        }
        //End Kwik Delivery changes code


        if($vendorData->vendor->shiprocket_pickup_name){
            //getShiprocketFee Delivery changes code
            $ship = new ShiprocketController();
            $deliver_ship_fee = $ship->getCourierService($vendorData->vendor_id);
            if($deliver_ship_fee)
            {
                $option = array_merge($option,$deliver_ship_fee);
            }
        }

            //getDunzo Delivery fee changes code
            $dunzo = new DunzoController();
            if($dunzo->status){
                $deliver_dunzo_fee = $dunzo->getQuotations($vendorData->vendor_id,$address);
                if($deliver_dunzo_fee>0)
                {
                    $deliver_charge_dunzo = number_format($deliver_dunzo_fee, 2, '.', '');
                    $optionDunzo[] = array(
                        'type'=>'DU',
                        'courier_name'=>__('Dunzo'),
                        'rate' => $deliver_charge_dunzo,
                        'courier_company_id' => 0,
                        'etd' => 0,
                        'etd_hours' => 0,
                        'duration' => 0,
                        'estimated_delivery_days' => 0,
                        'code' => 'DU_0'
                    );
                    $option = array_merge($option,$optionDunzo);
                }
            }

            if(isset($vendorData->vendor->ahoy_location)){
                //getAhoy (Masa) Delivery fee changes code
                $ahoy = new AhoyController();
                if($ahoy->status){
                    $deliver_ahoy_fee = $ahoy->getPreOrderFee($vendorData->vendor_id,$address);
                    if($deliver_ahoy_fee>0)
                    {
                        $deliver_charge_ahoy = number_format($deliver_ahoy_fee, 2, '.', '');
                        $optionAhoy[] = array(
                            'type'=>'M',
                            'courier_name'=>__('Ahoy'),
                            'rate' => $deliver_charge_ahoy,
                            'courier_company_id' => 0,
                            'etd' => 0,
                            'etd_hours' => 0,
                            'duration' => 0,
                            'estimated_delivery_days' => 0,
                            'code' => 'M_0'
                        );
                        $option = array_merge($option,$optionAhoy);
                    }
                }
            }


        }elseif($preferences->static_delivey_fee == 1 &&  $vendorData->vendor->order_amount_for_delivery_fee != 0){
            # for static fees

                if( $payable_amount >= (float)($vendorData->vendor->order_amount_for_delivery_fee)){
                    $deliveryCharges = number_format($vendorData->vendor->delivery_fee_maximum, 2, '.', '');
                }elseif($payable_amount < (float)($vendorData->vendor->order_amount_for_delivery_fee)){
                    $deliveryCharges = number_format($vendorData->vendor->delivery_fee_minimum, 2, '.', '');
                }

                $option[] = array(
                    'type'=>'D',
                    'courier_name'=>__('Static'),
                    'rate' => $deliveryCharges,
                    'courier_company_id' => 0,
                    'etd' => 0,
                    'etd_hours' => 0,
                    'duration' => 0,
                    'estimated_delivery_days' => 0,
                    'code' => 'D_0'
                );

            }//End statis fe code

            }
        } catch (\Exception $e) {
        }
        return $option;
    }
    public function updateCartProductFaq(Request $request){
        Log::info(2222222222);
        Log::info(['request' => $request->all()]);
        // pr($request->all());
        $user = Auth::user();
        if (!$user->id) {
            $cart = Cart::where('unique_identifier', $user->system_user);
        } else {
            $cart = Cart::where('user_id', $user->id);
        }
        $cart = $cart->first();

        $user_product_order_form = [];

        $cartData_id = CartProduct::where('cart_id', $cart->id)->where('product_id', $request->product_id)->pluck('id');

        if ($request->has('user_product_order_form')) {
            Log::info(['user_product_order_form' => 'user_product_order_form']);
            foreach ($request->user_product_order_form as $index => $faq) {
                Log::info(['faq' => $faq]);
                if (isset($faq['answer']) && $faq['answer'] instanceof \Illuminate\Http\UploadedFile) {
                 
                    $filename = time() . "_" . $faq['answer']->getClientOriginalName();
                    
                    $filePath = 'productfaq/' . $filename;
                    Storage::disk('s3')->put($filePath, file_get_contents($faq['answer']), 'public');
                    Log::info(['filePath' => $filePath]);
                    $faq['answer'] = Storage::disk('s3')->url($filePath);
                }
                $user_product_order_form[] = $faq;
            }
        }

        $user_product_order_form_json = json_encode($user_product_order_form);
        Log::info(['user_product_order_form_json' => $user_product_order_form_json]);
        CartProduct::whereIn('id', $cartData_id)->update(['user_product_order_form' => $user_product_order_form_json]);

        return response()->json(['status'=>'Success', 'message'=>__('Product form Submit successfully.')]);
    }

    public function updateCartCategoryKyc(Request $request){
        $user = Auth::user();
        if (!$user->id) {
            $cart = Cart::where('unique_identifier', $user->system_user);
        } else {
            $cart = Cart::where('user_id', $user->id);
        }
        $cart = $cart->first();

        $rules=[];
        $category_ids = explode(",",$request->category_ids);

        $category_kyc_documents =CategoryKycDocuments::whereHas('categoryMapping',function($q) use($category_ids){
            $q->whereIn('category_id',$category_ids);
        })->with('primary')->get();
        foreach ($category_kyc_documents as $vendor_registration_document) {
            if($vendor_registration_document->is_required == 1){
                $check = CaregoryKycDoc::where(['cart_id'=>$cart->id,'category_kyc_document_id'=>$vendor_registration_document->id])->first();
                if(isset($vendor_registration_document->primary) && !empty($vendor_registration_document->primary) && !$check )
                {
                    $rules[$vendor_registration_document->primary->slug] = 'required';
                }
            }
        }

        $validation  = Validator::make($request->all(), $rules)->validate();



        //pr($category_ids);
        $user_product_order_form = null;

        foreach ($category_ids as $category_id){
            $category_kyc_documents = CategoryKycDocuments::whereHas('categoryMapping',function($q) use($category_id){
                $q->where('category_id',$category_id);
        })->with('primary')->get();

            //  pr($category_kyc_documents->first());

            if ($category_kyc_documents->count() > 0) {
                foreach ($category_kyc_documents as $vendor_registration_document) {
                    $doc_name = str_replace(" ", "_", $vendor_registration_document->primary->slug);
                    if ($vendor_registration_document->file_type != "Text" && $vendor_registration_document->file_type != "selector") {
                        $check = CaregoryKycDoc::where(['cart_id'=>$cart->id,'category_kyc_document_id'=>$vendor_registration_document->id])->first();
                        if ($request->hasFile($doc_name) && !$check) {
                            $vendor_docs =  new CaregoryKycDoc();
                            $vendor_docs->user_id = $user->id;
                            $vendor_docs->category_kyc_document_id = $vendor_registration_document->id;
                            $filePath = 'category_kyc_document' . '/' . Str::random(40);
                            $file = $request->file($doc_name);
                            $vendor_docs->file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                            $vendor_docs->cart_id = $cart->id;
                            $vendor_docs->save();
                        }
                    }
                }
            }
        }
        // $rest = CaregoryKycDoc::where(['cart_id'=>$cart->id])->get();
        // pr($rest );
        return response()->json([
            'status' => 'success',
            'message' => 'document submit Successfully!',
        ]);

    }

     # upload image/pdf for order
    public function uploadOrderFile(Request $request)
    {

        // $validator = Validator::make($request->all(), [
        //     'files.*' => 'mimes:jpeg,bmp,png,gif,svg,pdf',
        //     'cart_id' => 'required'
        // ]);
        //try {
           // pr($request->all());
            $OrderFiles =  OrderFiles::SaveFiles($request);
        //    foreach ($request->instructions_files as $instructions_file) {
        //         $file = $request->file('instructions_file');
        //         $file_url = Storage::disk('s3')->put('orderFile', $instructions_file,'public');

        //         $OrderFile = new OrderFiles();
        //         $OrderFile->cart_id =$request->cart_id;
        //         $OrderFile->file =$file_url;
        //         $OrderFile->save();
        //     }
            $OrderFiles =  OrderFiles::where('cart_id',$request->cart_id)->get();
            return response()->json(['status'=>'Success', 'message'=>'Success','data'=>$OrderFiles]);
        // } catch (\Exception $e) {
        //     $data = [];
        //     $data['status'] = 400;
        //     $data['message'] =  $e->getMessage();
        //     return $data;
        // }

    }

     # REMOVE image/pdf for order
     public function RemoveOrderFile(Request $request)
     {
        $OrderFiles =  OrderFiles::where('id',$request->order_file_id)->delete();
        return response()->json(['status'=>'Success', 'message'=>'Success']);
     }

     public function updateCartWalletAmount(Request $request)
    {
        try {
            $cart = Cart::findOrFail($request->cart_id);
            $cart->update(['pay_via_wallet' => $request->paid_via_wallet]);

            return response()->json([
                "message" => __("Wallet amount added successfully."),
                'data' => array(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => __('User cart not exist.')], 404);
        }
    }
     public function getRentalProtection(Request $request){
        $protection = ProductRentalProtection::with('rentalProtection')->where('product_id', $request->product_id);
        $addon = AddonSet::with('option', 'translation')->where('vendor_id', $request->vendor_id)->where('status',1)->get();
        $included = clone $protection;
        $data['included'] = $protection->where('type_id', 1)->get();
        $data['excluded'] = $included->where('type_id', 2)->get();
        $data['addons'] = $addon;
        return response()->json(['status'=>'Success', 'data' => $data]);
     }

     public function addBookingOptionToCart(Request $request){
        $bookingOption = BookingOption::where('id', $request->booking_option_id)->firstOrFail();

        if(empty($bookingOption)){
            return response()->json(['status'=>'error', 'message' => 'Booking Option not found'], 404);
        }

        $cartBookingOption = CartBookingOption::where([
            'product_id' => $request->product_id,
            'booking_option_id' =>$request->booking_option_id,
            'cart_id' => $request->cart_id
        ])->first();

        if(!empty($cartBookingOption)){
            $cartBookingOption->delete();
            return response()->json(['status'=>'Success', 'data' => 'Booking Option has been deleted successfully'], 200);
        }

        $cartBookingOption = new CartBookingOption();
        $cartBookingOption->product_id = $request->product_id;
        $cartBookingOption->booking_option_id = $bookingOption->id;
        $cartBookingOption->cart_id = $request->cart_id;
        $cartBookingOption->save();
        return response()->json(['status'=>'Success', 'data' => 'Booking Option has been updated successfully'], 200);
     }
}
