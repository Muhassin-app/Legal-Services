<?php

namespace App\Http\Controllers\Front;

use Auth;
use Session;
use Timezonelist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,PaymentTrait};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Front\FrontController;
use App\Models\{User, UserAddress, ClientPreference, Client, ClientCurrency, SubscriptionPlansUser, SubscriptionFeaturesListUser, SubscriptionInvoicesUser, SubscriptionInvoiceFeaturesUser, Payment, PaymentOption};
use App\Models\AdditionalAttribute;
use App\Models\AdditionalAttributeProduct;
use App\Models\Category;
use App\Models\SubscriptionInvoiceCategoriesUser;
use App\Models\SubscriptionPlanUserDetail;
use App\Models\OrderLongTermServices;
use App\Models\Order;
use App\Models\OrderVendor;
use App\Models\OrderProduct;
use App\Models\Vendor;
use App\Models\OrderLongTermServiceSchedule;

class UserSubscriptionController extends FrontController
{
    use ApiResponser,PaymentTrait;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $preferences = ClientPreference::where(['id' => 1])->first();
        if((isset($preferences->subscription_mode)) && ($preferences->subscription_mode == 0)){
            abort(404);
        }
    }

    /**
     * get user subscriptions.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubscriptionPlans(Request $request, $domain = '')
    {
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        $currency_id = Session::get('customerCurrency');
        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
        $sub_plans = SubscriptionPlansUser::with('features.feature', 'subscriptionCategory.category')->where('status', '1')->orderBy('id', 'asc');
        // $sub_meal_plans = SubscriptionPlansUser::with('subscriptionCategory.category')->where('status', '1')->orderBy('id', 'asc')->get();
        $featuresList = SubscriptionFeaturesListUser::where('status', 2)->get();
        $active_subscription = SubscriptionInvoicesUser::with([
            'plan',
            'features.feature'
        ])->where('user_id', Auth::user()->id)
            ->where('status_id', 2)
            ->orderBy('end_date', 'desc')
            ->first();
        // $active_subscription_plan_ids = array();
        // foreach($active_subscription as $subscription){
        // $active_subscription_plan_ids[] = $active_subscription->subscription_id;
        // }
        $allPlans = $sub_plans->get();
        if ($allPlans) {
            foreach ($allPlans as $sub) {
                $subFeaturesList = array();
                if ($sub->features->isNotEmpty()) {
                    foreach ($sub->features as $feature) {
                        $title = $feature->feature->title;
                        if ($feature->feature_id == 2) {
                            $title = $feature->percent_value . $title;
                        }
                        $subFeaturesList[] = $title;
                    }
                    unset($sub->features);
                }
                $sub->features = $subFeaturesList;

                $category = '';
                if (! empty($sub->subscriptionCategory)) {
                    $planCategoryList = [];
                    foreach ($sub->subscriptionCategory as $category) {
                        $title = $category->category->slug;
                        $planCategoryList[] = $title;
                    }
                    unset($sub->subscriptionCategory);
                    $category = implode(', ', $planCategoryList);
                }
                $sub->subscriptionCategory = $category;
            }
        }
        return view('frontend.account.userSubscriptions')->with([
            'navCategories' => $navCategories,
            'subscription_plans' => $sub_plans,
            'subscription' => $active_subscription,
            'clientCurrency' => $clientCurrency
        ]);
    }

    /**
     * select user subscription.
     *
     * @return \Illuminate\Http\Response
     */
    public function selectSubscriptionPlan(Request $request, $domain = '', $slug = '')
    {
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        $currency_id = Session::get('customerCurrency');
        $currencySymbol = Session::get('currencySymbol');
        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
        $sub_plan = SubscriptionPlansUser::with('features.feature')->where('slug', $slug)->where('status', '1')->first();
        if($sub_plan){
            $subFeaturesList = '<ul class="list-unstyled">';
            if($sub_plan->features->isNotEmpty()){
                foreach($sub_plan->features as $feature){
                    $title = $feature->feature->title;
                    if($feature->feature_id == 2){
                        $title = $feature->percent_value . $title;
                    }
                    $subFeaturesList = $subFeaturesList.'<li class="d-block"><i class="fa fa-check"></i><span class="ml-1">'.$title.'</span></li>';
                }
                unset($sub_plan->features);
            }
            $subFeaturesList = $subFeaturesList.'<ul>';
            $sub_plan->features = $subFeaturesList;
            $sub_plan->price = $sub_plan->price * $clientCurrency->doller_compare;
        }
        else{
            return response()->json(["status"=>"Error", "message" => __("Subscription plan not active")]);
        }
        $code = $this->paymentOptionArray('Subscription');
        $ex_codes = array('cod');
        $payment_options = PaymentOption::select('id', 'code', 'title', 'credentials')->whereIn('code', $code)->where('status', 1)->get();
        foreach ($payment_options as $k => $payment_option) {
            if( (in_array($payment_option->code, $ex_codes)) || (!empty($payment_option->credentials)) ){
                $payment_option->slug = strtolower(str_replace(' ', '_', $payment_option->title));
                if($payment_option->code == 'stripe'){
                    $payment_option->title = 'Credit/Debit Card (Stripe)';
                }elseif($payment_option->code == 'kongapay'){
                    $payment_option->title = 'Pay Now';
                }elseif($payment_option->code == 'mvodafone'){
                    $payment_option->title = 'Vodafone M-PAiSA';
                }elseif($payment_option->code == 'offline_manual'){
                    $json = json_decode($payment_option->credentials);
                    $payment_option->title = $json->manule_payment_title;
                }elseif($payment_option->code == 'mycash'){
                    $payment_option->title = __('Digicel MyCash');
                }elseif($payment_option->code == 'windcave'){
                    $payment_option->title = __('Windcave (Debit/Credit card)');
                }elseif($payment_option->code == 'stripe_ideal'){
                    $payment_option->title = __('iDEAL');
                }elseif($payment_option->code == 'authorize_net'){
                    $payment_option->title = __('Credit/Debit Card');
                }elseif($payment_option->code == 'obo'){
                    $payment_option->title = __("Momo, Airtel Money by O'Pay");
                }elseif($payment_option->code == 'livee'){
                    $payment_option->title = __("Livees");
                }
                $payment_option->title = __($payment_option->title);
                unset($payment_option->credentials);
            }
            else{
                unset($payment_options[$k]);
            }
        }
        return response()->json(["status"=>"Success", "sub_plan" => $sub_plan, "payment_options" => $payment_options, "currencySymbol"=>$currencySymbol]);
    }

    /**
     * check if user has any active subscription.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkActiveSubscription(Request $request, $domain = '', $slug = '')
    {
        $now = Carbon::now()->toDateString();
        $userActiveSubscription = SubscriptionInvoicesUser::with(['plan'])
                                ->whereNull('cancelled_at')
                                ->where('user_id', Auth::user()->id)
                                ->where('end_date', '>=', $now )
                                ->orderBy('end_date', 'desc')->first();
        if( ($userActiveSubscription) && isset($userActiveSubscription->plan) && ($userActiveSubscription->plan->slug != $slug) ){
            return $this->errorResponse(__('You cannot buy two subscriptions at the same time'), 402);
        }
        return $this->successResponse('', 'Processing...');
    }

    /**
     * buy user subscription.
     *
     * @return \Illuminate\Http\Response
     */
    public function purchaseSubscriptionPlan(Request $request, $domain = '', $slug = '')
    {
        $currency_id = Session::get('customerCurrency')??63;
        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();

        $dollar_compare =  !empty($clientCurrency)?$clientCurrency->doller_compare:1;
        if( (isset($request->user_id)) && (!empty($request->user_id)) ){
            $user = User::find($request->user_id);
        }else{
            $user = Auth::user();
        }
        $subscription_plan = SubscriptionPlansUser::with('features.feature')->where('slug', $slug)->where('status', '1')->first();
        $last_subscription = SubscriptionInvoicesUser::with(['plan', 'features.feature'])
            ->where('user_id', $user->id)
            ->where('subscription_id', $subscription_plan->id)
            ->orderBy('end_date', 'desc')->first();
        if( ($user) && ($subscription_plan) ){
            $subscription_invoice = new SubscriptionInvoicesUser;
            $subscription_invoice->user_id = $user->id;
            $subscription_invoice->subscription_id = $subscription_plan->id;
            $subscription_invoice->slug = strtotime(Carbon::now()).'_'.$slug;
            $subscription_invoice->payment_option_id = $request->payment_option_id;
            // $subscription_invoice->status_id = 2;
            $subscription_invoice->frequency = $subscription_plan->frequency;
            $subscription_invoice->payment_option_id = $request->payment_option_id;
            $subscription_invoice->transaction_reference = $request->transaction_id;
            $subscription_invoice->strip_subscriber_id = $request->strip_subscriber_id;
            $now = Carbon::now();
            $current_date = $now->toDateString();
            $start_date = $current_date;
            $next_date = NULL;
            $end_date = NULL;

            if($last_subscription){
                if($last_subscription->end_date >= $current_date){
                    $start_date = Carbon::parse($last_subscription->end_date)->addDays(1)->toDateString();
                }
            }
            if($subscription_plan->frequency == 'weekly'){
                $end_date = Carbon::parse($start_date)->addDays(6)->toDateString();
            }elseif($subscription_plan->frequency == 'monthly'){
                $end_date = Carbon::parse($start_date)->addMonths(1)->subDays(1)->toDateString();
            }elseif($subscription_plan->frequency == 'yearly'){
                $end_date = Carbon::parse($start_date)->addYears(1)->subDays(1)->toDateString();
            }
            $next_date = Carbon::parse($end_date)->addDays(1)->toDateString();
            $subscription_invoice->start_date = $start_date;
            $subscription_invoice->next_date = $next_date;
            $subscription_invoice->end_date = $end_date;
            $subscription_invoice->subscription_amount = $request->amount / $dollar_compare;
            $subscription_invoice->save();
            $subscription_invoice_id = $subscription_invoice->id;
            if($subscription_invoice_id){
                $payment = new Payment;
                $payment->user_id = $user->id;
                $payment->balance_transaction = $request->amount / $dollar_compare;
                $payment->transaction_id = $request->transaction_id;
                $payment->user_subscription_invoice_id = $subscription_invoice_id;
                $payment->payment_option_id = $request->payment_option_id;
                $payment->date = Carbon::now()->format('Y-m-d');
                $payment->type = 'subscription';
                $payment->save();
                if($request->type_id == SubscriptionPlansUser::SUBSCRIPTION_USER){
                    $subscription_invoice_features = array();
                    foreach($subscription_plan->features as $feature){
                        $features_array = array(
                            'user_id' => $user->id,
                            'subscription_id' => $subscription_plan->id,
                            'subscription_invoice_id' => $subscription_invoice_id,
                            'feature_id' => $feature->feature_id,
                            'feature_title' => $feature->feature->title,
                            'percent_value' => null,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        );
                        if($feature->feature_id == 2){
                            $features_array['percent_value'] = $feature->percent_value;
                        }
                        $subscription_invoice_features[] = $features_array;
                    }
                    if(!empty($subscription_invoice_features)){
                        SubscriptionInvoiceFeaturesUser::insert($subscription_invoice_features);
                    }
                }elseif ($request->type_id == SubscriptionPlansUser::SUBSCRIPTION_MEAL){
                    $subscription_invoice_category = [];
                    foreach ($subscription_plan->subscriptionCategory as $category) {
                        $subscription_invoice_category[] = [
                            'subscription_invoice_id' => $subscription_invoice_id,
                            'category_id' => $category->category->id,
                            'category_title' => $category->category->slug,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                    }
                    if (! empty($subscription_invoice_category)) {
                        SubscriptionInvoiceCategoriesUser::insert($subscription_invoice_category);
                    }
                    try {
                        $addressId = '';
                        $start_date = null;
                        $autorenew = false;
                        if($request->has('mealSubscriptionForm')){
                            $mealSubscription = json_decode($request->mealSubscriptionForm, true);
                            $addressId = $mealSubscription['address_id'] ?? '';
                            $start_date = $mealSubscription['subscription_start_date'];
                            $autorenew = $mealSubscription['autorenew'] == 'on' ? true : false;
                            $mealSubscriptiondata = [
                                'subscription_invoice_id' => $subscription_invoice_id,
                                'delivery_method' => $mealSubscription['delivery_method'],
                                'meal_timing' => $mealSubscription['meal_timing'],
                                'meal_package' => $mealSubscription['meal_package'],
                                'start_date' => $mealSubscription['subscription_start_date'],
                                'delivery_instruction' => $mealSubscription['delivery_instructions'],
                                'days' => json_encode(explode(',', $request->days)),
                                'address_id' => $addressId,
                                'autorenew' => $mealSubscription['autorenew'] == 'on' ? '1' : '0',
                                'credit_left' => $subscription_plan->order_limit,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ];
                            SubscriptionPlanUserDetail::insert($mealSubscriptiondata);
                        }

                        /* Create order for meal subscription */

                        $order = new Order();
                        $order->user_id = $request->user_id;
                        $order->order_number = generateOrderNo();
                        $order->address_id = $addressId;
                        $order->payment_option_id = $request->payment_option_id;
                        $order->is_postpay = (isset($request->is_postpay)) ? $request->is_postpay : 0;
                        $order->payment_status = 1;
                        $order->save();
                        $mealVendor = Vendor::whereHas('getAllCategory', function($q){
                            $q->whereHas('category', function($q){
                                $q->where('slug', 'mealCategory');
                            });
                        })->get();
                        $mealVendor = $mealVendor->first();
                        $product = $mealVendor->products->first();

                        $OrderVendor = new OrderVendor();
                        $OrderVendor->status = 0;
                        $OrderVendor->user_id = $order->user_id;
                        $OrderVendor->order_id = $order->id;
                        $OrderVendor->vendor_id = $mealVendor->id;
                        $OrderVendor->save();

                        $order_product = new OrderProduct();
                        $order_product->order_id = $order->id;
                        $order_product->price = $subscription_plan->price;
                        $order_product->markup_price = $subscription_plan->price;
                        $order_product->vendor_id = $mealVendor->id;
                        $order_product->product_id = $product->id;
                        $order_product->product_name = $product->title ?? $product->sku;
                        $order_product->image = $product->pimage->first() ? $product->pimage->first()->path : '';
                        $order_product->quantity = 1;
                        $order_product->save();

                        if($autorenew){
                            $days = 0;
                            switch ($subscription_plan->frequency){
                                case 'weekly':
                                    $days = 7;
                                    break;
                                case 'monthly':
                                    $days = 30;
                                    break;
                                case 'yearly':
                                    $days = 365;
                                    break;

                            }
                            $service_end_date = Carbon::parse($start_date)->addDays($days);
                            $serviceDay = Carbon::parse($start_date)->dayName;
                            $LongTermSericeData = [
                                'order_product_id' => $order_product->id,
                                'user_id' => $user->id,
                                'service_quentity' => 1,
                                'service_day' => $serviceDay,
                                'service_date' => $start_date,
                                'service_start_date' => $start_date,
                                'service_period' => $subscription_plan->frequency,
                                'service_end_date' => $service_end_date,
                                'service_product_id' => $product->id,
                                'status' => 0
                            ];
                            $OrderLongTermServices = OrderLongTermServices::create($LongTermSericeData);


                            $RecurringServiceSchedule = [];
                            for ($x = 0; $x < $days; $x++) {
                                if($x)
                                    $newDate = date('Y-m-d', strtotime('+1 day'));
                                else
                                    $newDate = date('Y-m-d');
                                $RecurringServiceSchedule [] = [
                                    'order_vendor_product_id' => $order_product->id,
                                    'schedule_date'           => $newDate,
                                    'type'                    => 2,
                                    'order_number'            => $order->order_number,
                                    'order_long_term_services_id' => $OrderLongTermServices->id
                                ];
                            }
                            OrderLongTermServiceSchedule::insert($RecurringServiceSchedule);
                        }
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }

                $message = __('Your subscription has been activated successfully.');
                Session::put('success', $message);
                return $this->successResponse('', $message);
            }
            else{
                return $this->errorResponse(__('Error in purchasing subscription.'), 402);
            }
        }
        else{
            return $this->errorResponse(__('Invalid Data'), 402);
        }
    }

    /**
     * cancel user subscription.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelSubscriptionPlan(Request $request, $domain = '', $slug = '')
    {
        $active_subscription = SubscriptionInvoicesUser::with('plan')
                            ->where('slug', $slug)
                            ->where('user_id', Auth::user()->id)
                            ->orderBy('end_date', 'desc')->first();
        if($active_subscription){
            $active_subscription->cancelled_at = $active_subscription->end_date;
            $active_subscription->updated_at = Carbon::now()->toDateTimeString();
            $active_subscription->save();
            return redirect()->back()->with('success', 'Your '.$active_subscription->plan->title.' subscription has been cancelled successfully');
        }
        else{
            return redirect()->back()->with('error', __('Unable to cancel subscription'));
        }
    }

    public function mealSubscription(Request $request, $domain = '', $slug)
    {
        $subscription_plan = SubscriptionPlansUser::with('subscriptionCategory.category')->where('slug', $slug)
            ->where('status', '1')
            ->first();
        $attributesProducts = AdditionalAttributeProduct::whereHas('additionalAttribute', function ($q) {
            $q->where('type_id', 1)->where('service_type', '=', 'pick_drop');
        })->where('reference_id', $subscription_plan->id)->get();

        $getAdmin = User::where('is_superadmin', 1)->first();

        $additionalAttributes = AdditionalAttribute::where('type_id', 1)->where('service_type', '=', 'pick_drop')
            ->where('user_id', $getAdmin->id)
            ->get();
        $attributesProducts = AdditionalAttributeProduct::with('additionalAttribute')->where('user_id', $getAdmin->id)
            ->where('reference_id', $subscription_plan->id)
            ->get();
        $categories = Category::with('translation_one')->select('id', 'slug')
            ->where('deleted_at', NULL)
            ->whereIn('type_id', [
            '1',
            '6',
            '8',
            '9',
            '11'
        ])
            ->where('is_core', 1)
            ->where('status', 1)
            ->get();
        $currency_id = Session::get('customerCurrency') ?? 63;
        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
        $user_allAddresses = UserAddress::where('user_id', Auth::user()->id)->where('status', 1)
            ->orderBy('is_primary', 'Desc')
            ->get();
        $subPlanCategory = $subscription_plan->subscriptionCategory()->with('products');

        return view('frontend.account.buyMealSubscription')->with([
            'subscription_plan' => $subscription_plan,
            'attributeProduct' => $attributesProducts,
            'additionalAttributes' => $additionalAttributes,
            'categories' => $categories,
            'subPlanCategory' => $subPlanCategory,
            'clientCurrency' => $clientCurrency,
            'addresses' => $user_allAddresses
        ]);
    }

    public function subscriptionCredit(Request $request){
        $now = Carbon::now()->toDateString();
        $userActiveSubscription = SubscriptionInvoicesUser::whereHas('plan', function($q){
            $q->where('type_id', 2);
        })->with('subscriptionDetail')->whereNull('cancelled_at')
            ->where('user_id', Auth::user()->id)
            ->where('end_date', '>=', $now)
            ->orderBy('end_date', 'desc')
            ->first();
        // if (empty($userActiveSubscription)) {
        //     return redirect()->back()->with('error', __('You don\'t have subscribed to subscription'));
        // }
        return view('frontend.account.subscriptionCredit')->with([
            'subscription_plan' => $userActiveSubscription,
        ]);
    }

    public function deleteSubscriptionPlan(Request $request, $domain = '', $slug = '')
    {
        $active_subscription = SubscriptionInvoicesUser::with('plan')
        ->where('strip_subscriber_id', $request->strip_subscriber_id)
        ->where('user_id', $request->user_id)
        ->orderBy('end_date', 'desc')->delete();
        return redirect()->back()->with('success', 'Your '.$active_subscription->plan->title.' subscription has been deleted successfully');
    }

    public function updateSubscriptionPlan(Request $request, $domain = '', $slug = '')
    {
        $active_subscription = SubscriptionInvoicesUser::with('plan')
                            ->where('strip_subscriber_id', $request->strip_subscriber_id)
                            ->where('user_id', $request->user_id)
                            ->orderBy('end_date', 'desc')->first();
        if($active_subscription){
            //$active_subscription->cancelled_at = $active_subscription->end_date;
            $active_subscription->status_id = $request->status_id;

            $active_subscription->updated_at = Carbon::now()->toDateTimeString();
            $active_subscription->save();
            return redirect()->back()->with('success', 'Your '.$active_subscription->plan->title.' subscription has been updated successfully');
        }

    }
}
