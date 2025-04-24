<?php

namespace App\Http\Controllers\Client;

use DB;
use Session, DataTables;
use \DateTimeZone;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Http\Traits\StripeSubscription;
use App\Models\{AdditionalAttribute, AdditionalAttributeProduct, Category, Client, ClientPreference, SmsProvider, Currency, Language, Country, User, SubscriptionPlansUser, SubscriptionPlanFeaturesUser, ShowSubscriptionPlanOnSignup, SubscriptionFeaturesListUser, SubscriptionInvoicesUser, Order, OrderVendor, PaymentOption, SubscriptionPlanUserCategory};
use Carbon\Carbon;
use App\Models\ClientCurrency;

class SubscriptionPlansUserController extends BaseController
{
    use ApiResponser, StripeSubscription;
    private $folderName = '/subscriptions/image';
    public function __construct()
    {
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/subscriptions/image';
    }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }

    /**
     * Get user subscriptions
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSubscriptionPlans(Request $request, $domain = '')
    {
        $sub_plans = SubscriptionPlansUser::with(['features.feature'])->orderBy('id', 'asc')->get();
        $featuresList = SubscriptionFeaturesListUser::where('status', 1)->get();
        $user_subscriptions = SubscriptionInvoicesUser::groupBy('user_id')->get();
        $showSubscriptionPlan = ShowSubscriptionPlanOnSignup::find(1);
        $subscribed_users_count = $user_subscriptions->count();
        $active_users = User::where('status', 1)->count();
        $subscribed_users_percentage = ($subscribed_users_count / $active_users) * 100;
        $subscribed_users_percentage = number_format($subscribed_users_percentage, 2);
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
        $additionalAttributes = AdditionalAttribute::where('type_id', 1)->where('service_type','=', 'pick_drop')->where('user_id', auth()->user()->id)->get();
        if($sub_plans){
            foreach($sub_plans as $plan){
                $features = '';
                if($plan->features->isNotEmpty()){
                    $planFeaturesList = array();
                    foreach($plan->features as $feature){
                        $title = $feature->feature->title;
                        if($feature->feature_id == 2){
                            $title = $feature->percent_value . $title;
                        }
                        $planFeaturesList[] = $title;
                    }
                    unset($plan->features);
                    $features = implode(', ', $planFeaturesList);
                }
                $plan->features = $features;
                
                $category= '';
                if(!empty($plan->subscriptionCategory)){
                    $planCategoryList = [];
                    foreach($plan->subscriptionCategory as $category){
                        $title = $category->category->slug;
                        $planCategoryList[] = $title;
                    }
                    unset($plan->subscriptionCategory);
                    $category = implode(', ', $planCategoryList);
                }
                $plan->subscriptionCategory = $category;
            }
        }
        return view('backend/subscriptions/subscriptionPlansUser')->with(['features'=>$featuresList, 'showSubscriptionPlan'=>$showSubscriptionPlan, 'subscription_plans'=>$sub_plans, 'subscribed_users_count'=>$subscribed_users_count, 'subscribed_users_percentage'=>$subscribed_users_percentage, 'categories' => $categories,'additionalAttributes' => $additionalAttributes]);
    }

    /**
     * save user subscription
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveSubscriptionPlan(Request $request, $domain = '', $slug='')
    {
        $message = 'added';
        $rules = array(
            'title' => 'required|string|max:50',
            'type_id' => 'required',
            'price' => 'required',
//             'features' => 'required',
            // 'period' => 'required',
            // 'sort_order' => 'required'
        );
        if(!empty($slug)){
            $plan = SubscriptionPlansUser::where('slug', $slug)->firstOrFail();
            $rules['title'] = $rules['title'].',id,'.$plan->id;
            $message = 'updated';
        }

        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $stripe_creds = PaymentOption::select('credentials', 'test_mode')->where(['code'=>'stripe','status'=>1])->whereNotNull('credentials')->first();
        
        if(!empty($slug)){
            if(!empty($request->features))
                $subFeatures = SubscriptionPlanFeaturesUser::where('subscription_plan_id', $plan->id)->whereNotIn('feature_id', $request->features)->delete();
            if(!empty($request->categories))
                SubscriptionPlanUserCategory::where('subscription_id', $plan->id)->whereNotIn('category_id', $request->categories)->delete();
        }else{
            $plan = new SubscriptionPlansUser;
            $plan->slug = uniqid();
        }
        if($stripe_creds){
            $creds_arr = json_decode($stripe_creds->credentials);
            $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
            $request->merge(['apiKey'=>$api_key]);
            $res =   $this->createSubscriptionOnStripe($request) ;
            if($res && isset($res->id)){
                $plan->strip_plan_id =  $res->id;
            }
        }

        $plan->title = $request->title;
        $plan->price = $request->price;
        // $plan->period = $request->period;
        $plan->frequency = $request->frequency;
        // $plan->sort_order = $request->sort_order;
        $plan->status = ($request->has('status') && $request->status == 'on') ? '1' : '0';
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $plan->image = Storage::disk('s3')->put($this->folderName, $file,'public');
        }
        if( ($request->has('description')) && (!empty($request->description)) ){
            $plan->description = $request->description;
        }
        if($request->has('order_limit') && !empty($request->order_limit)){
            $plan->order_limit = $request->order_limit;
        }
        $plan->type_id = $request->type_id;
        $plan->save();
        $planId = $plan->id;
        if( ($request->has('features')) && (!empty($request->features)) ){
            $plan->subFeatures()->sync($request->features);
            foreach($request->features as $key => $val){

                if($val == 2){
                    $plan->subFeatures()->updateExistingPivot(['feature_id' => $val], ['percent_value' => $request->percent_value]);
                }

                // if(!empty($slug)){
                //     $subFeature = SubscriptionPlanFeaturesUser::where('subscription_plan_id', $planId)->where('feature_id', $val)->first();
                //     if($subFeature){
                //         continue;
                //     }else{
                //         $subFeature = new SubscriptionPlanFeaturesUser();
                //         $subFeature->subscription_plan_id = $planId;
                //         $subFeature->feature_id = $val;
                //         if($val == 2){
                //             $subFeature->percent_value = $request->percent_value;
                //         }
                //     }
                //     $subFeature->save();
                // }
            }
        }
         
        if($request->has('categories') && !empty($request->categories)){
            foreach ($request->categories as $category) {
                $exists = $plan->subscriptionCategory()->where('category_id', $category)->first();
                if(!empty($exists))
                    continue;
                $subscriptionCategory = new SubscriptionPlanUserCategory();
                $subscriptionCategory->category_id = $category;
                $subscriptionCategory->subscription_id = $plan->id;
                $subscriptionCategory->save();
            }
        }
        
        if($request->has('meal_timing') && !empty($request->meal_timing) || $request->has('meal_package') && !empty($request->meal_package)){
            $additioanlAttribute = AdditionalAttribute::with('primary')->get();
            if ($additioanlAttribute->count() > 0) {
                foreach ($additioanlAttribute as $attribute) {
                    $doc_name = str_replace(" ", "_", $attribute->primary->slug);
                    if ($attribute->field_type != "textbox" && $attribute->field_type != "selector" && $attribute->field_type != "checkbox") {
                        if ($request->hasFile($doc_name)) {
                            $attributeProduct = new AdditionalAttributeProduct();
                            $attributeProduct->user_id = Auth::id();
                            $attributeProduct->additional_attribute_id = $attribute->id;
                            $attributeProduct->reference_id = $plan->id;
                            $filePath = $this->folderName . '/' . Str::random(40);
                            $file = $request->file($doc_name);
                            $attributeProduct->product_data = Storage::disk('s3')->put($filePath, $file, 'public');
                            $attributeProduct->save();
                        }
                    } elseif ($attribute->field_type == "checkbox") {
                        if ($request->has($doc_name)) {
                            foreach ($request->$doc_name as $field => $value) {
                                $attributeProduct = new AdditionalAttributeProduct();
                                $attributeProduct->user_id = Auth::id();
                                $attributeProduct->reference_id = $plan->id;
                                $attributeProduct->additional_attribute_id = $attribute->id;
                                $attributeProduct->product_data = $field;
                                $attributeProduct->save();
                            }
                        }
                    } else {
                        if (! empty($request->$doc_name)) {
                            $attributeProduct = new AdditionalAttributeProduct();
                            $attributeProduct->user_id = Auth::id();
                            $attributeProduct->additional_attribute_id = $attribute->id;
                            $attributeProduct->reference_id = $plan->id;
                            $attributeProduct->product_data = $request->$doc_name;
                            $attributeProduct->save();
                        }
                    }
                }
            }
        }
        return redirect()->back()->with('success', 'Subscription has been '.$message.' successfully.');
    }

    /**
     * edit user subscription
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function editSubscriptionPlan(Request $request, $domain = '', $slug='')
    {
        $plan = SubscriptionPlansUser::where('slug', $slug)->firstOrFail();
        $planFeatures = SubscriptionPlanFeaturesUser::select('feature_id', 'percent_value')->where('subscription_plan_id', $plan->id)->get();
        $featuresList = SubscriptionFeaturesListUser::where('status', 1)->get();
        $subPlanFeaturesIds = array();
        if(!empty($planFeatures)){
            foreach($planFeatures as $feature){
                $subPlanFeaturesIds[] = $feature->feature_id;
            }
        }
        $additionalAttributes = AdditionalAttribute::where('type_id', 1)->where('service_type','=', 'pick_drop')->where('user_id', auth()->user()->id)->get();
        $attributeProduct = AdditionalAttributeProduct::with('additionalAttribute')->where('user_id', auth()->user()->id)->where('reference_id', $plan->id)->get();
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
        
        $subPlanCategoryIds = $plan->subscriptionCategory()->pluck('category_id')->toArray();
        $returnHTML = view('backend.subscriptions.edit-subscriptionPlanUser')->with(['features'=>$featuresList, 'plan' => $plan, 'planFeatures' => $planFeatures, 'subPlanFeaturesIds'=>$subPlanFeaturesIds, 'subPlanCategoryIds' => $subPlanCategoryIds, 'categories' => $categories, 'additionalAttributes' => $additionalAttributes, 'attributeProduct' => $attributeProduct])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }

    public function showSubscriptionPlanCustomer(Request $request){
        
        $showSubscriptionPlan = ShowSubscriptionPlanOnSignup::firstOrNew(array('id' => 1));
        $showSubscriptionPlan->id = 1;
        if($request->showSubscriptionType == 'show_plan_customer'){
            $showSubscriptionPlan->show_plan_customer = $request->status;
        }
        if($request->showSubscriptionType == 'every_sign_up'){
            $showSubscriptionPlan->every_sign_up = $request->status;
        }
        if($request->showSubscriptionType == 'every_app_open'){
            $showSubscriptionPlan->every_app_open = $request->status;
        }
        $showSubscriptionPlan->save();
        return response()->json(array('success' => true, 'message'=>'Show subscription status has been updated.'));
    }

    /**
     * update user subscription status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateSubscriptionPlanStatus(Request $request, $domain = '', $slug='')
    {
        $subscription = SubscriptionPlansUser::where('slug', $slug)->firstOrFail();
        $subscription->status = $request->status;
        $subscription->save();
        return response()->json(array('success' => true, 'message'=>'Subscription status has been updated.'));
    }

    /**
     * update user subscription
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteSubscriptionPlan(Request $request, $domain = '', $slug='')
    {
        try {
            $subscription = SubscriptionPlansUser::where('slug', $slug)->firstOrFail();
            $subscription->delete();
            return redirect()->back()->with('success', 'Subscription has been deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Subscription cannot be deleted.');
        }
    }

    //Customer Subscription Report
    public function userSubscriptionReport(Request $request, $domain = '')
    {
        $admin_subs_discount = OrderVendor::whereIn('order_status_option_id', array(1,2,4,5,6));
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $admin_subs_discount->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $admin_subs_discount = $admin_subs_discount->sum('subscription_discount_admin');

        $vendor_subs_discount = OrderVendor::whereIn('order_status_option_id', array(1,2,4,5,6));
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendor_subs_discount->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendor_subs_discount = $vendor_subs_discount->sum('subscription_discount_vendor');
        return view('backend/accounting/usersubscriptions')->with(['admin_subs_discount'=>$admin_subs_discount, 'vendor_subs_discount'=>$vendor_subs_discount]);
    }


    public function subscriptionfilter(Request $request){
        try {
            $user = Auth::user();
            $search_value = $request->get('search');
            $timezone = $user->timezone ? $user->timezone : 'Asia/Kolkata';
   
            $vendor_orders_query = OrderVendor::with(['orderDetail.user', 'vendor'])->whereIn('order_status_option_id', array(1,2,4,5,6))
                ->whereHas('orderDetail', function($q){
                    $q->where('subscription_discount', '>', 0);
                });

            if (!empty($request->get('date_filter'))) {
                $date_date_filter = explode(' to ', $request->get('date_filter'));
                $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
                $from_date = $date_date_filter[0];
                $vendor_orders_query = $vendor_orders_query->between($from_date." 00:00:00", $to_date." 23:59:59");
            }
            
            $vendor_orders = $vendor_orders_query->orderBy('id', 'desc');
            return Datatables::of($vendor_orders)
                ->addColumn('admin_subscription_amount', function($vendor_orders) {
                    return decimal_format($vendor_orders->subscription_discount_admin??0);
                })
                ->addColumn('vendor_subscription_amount', function($vendor_orders) {
                    return decimal_format($vendor_orders->subscription_discount_vendor??0);
                })
                ->addColumn('total_subscription_amount', function($vendor_orders) {
                    return decimal_format(($vendor_orders->subscription_discount_vendor + $vendor_orders->subscription_discount_admin)??0);
                })
                ->addColumn('order_number', function($vendor_orders) {
                    return $vendor_orders->orderDetail ? $vendor_orders->orderDetail->order_number : '';
                })
                ->addColumn('vendor_view_url', function($vendor_orders) {
                    if(!empty($vendor_orders->order_id) && !empty($vendor_orders->vendor_id)){
                        return route('vendor.catalogs', [$vendor_orders->vendor_id]);
                    }else{
                        return '#';
                    }
                })
                ->addColumn('view_url', function($vendor_orders) {
                    if(!empty($vendor_orders->order_id) && !empty($vendor_orders->vendor_id)){
                        return route('order.show.detail', [$vendor_orders->order_id, $vendor_orders->vendor_id]);
                    }else{
                        return '#';
                    }
                })
                ->addColumn('customer', function($vendor_orders) {
                    return $vendor_orders->user ? $vendor_orders->user->name : '';
                })
                ->addColumn('vendor_name',function($vendor_orders){
                    return $vendor_orders->vendor ? __($vendor_orders->vendor->name) : '';
                })
                ->addIndexColumn()
                ->filter(function ($instance) use ($request) {
                    if (!empty($request->get('search'))) {
                        $search = $request->get('search');
                        $instance->where(function($query) use($search){
                            $query->whereHas('orderDetail', function($q) use($search){
                                $q->where('order_number', 'LIKE', '%'.$search.'%');
                            })
                            ->orWhereHas('user', function($q) use($search){
                                $q->where('name', 'LIKE', '%'.$search.'%');
                            })
                            ->orWhereHas('vendor', function($q) use($search){
                                $q->where('name', 'LIKE', '%'.$search.'%');
                            });
                        });
                    }
                })->make(true);
        } catch (Exception $e) {

        }
    }
}
