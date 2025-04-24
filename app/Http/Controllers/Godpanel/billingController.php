<?php

namespace App\Http\Controllers\Godpanel;

use DB;
use DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{BillingPlan, BillingPlanType, BillingTimeframe, BillingPricing, BillingSubscription, Client, BillingPaymentTransation};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Client\BaseController;
use App\Http\Traits\BillingPlanManager;

class billingController extends Controller
{
    use BillingPlanManager;
    
    private $folderName        = '/billingplan/image';
    private $receiptFolderName        = '/billingplan/receipt';

    public function index()
    {
        
    }


    //...........................................For Plan page----------------------------//
    public function getBillingPlans(Request $request)
    {
        $billingplans = BillingPlan::with('plantype:id,title')->select("id", "title", "slug", "image", "status", "description", "plan_type")->orderBy('title', 'asc')->paginate(10);
        $billingplanstype = BillingPlanManager::getBillingPlanTypeList();
        
        return view('godpanel/billingplan')->with(['billingplans'=>$billingplans, 'billingplanstype'=>$billingplanstype]);
    }


    public function saveBillingPlan(Request $request, $slug='')
    {
        $message = 'added';
        $rules = array(
            'title' => ['required', 'string', 'max:150', Rule::unique('billing_plans')->where('plan_type', $request->plan_type)],
            'plan_type' => 'required',
        );
        
        if(!empty($slug)){
            $plan = BillingPlan::where('slug', $slug)->first();
            if(empty($plan)){
                return redirect()->back()->withErrors(['error' => "Something went wrong."]);
            }
            $rules['title'] = array('required', Rule::unique('billing_plans')->where('plan_type', $request->plan_type)->ignore($plan->id));
            $message = 'updated';
        }else{
            $plan = new BillingPlan;
            $plan->slug = uniqid();
        }

        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        
        $plan->title = $request->title;
        $plan->plan_type = $request->plan_type;
        $plan->description = $request->description;
        $plan->status = ($request->has('status') && $request->status == 'on') ? '1' : '2';
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $plan->image = Storage::disk('s3')->put($this->folderName, $file,'public');
        }
       
        $plan->save();
        $planId = $plan->id;
        return redirect()->route('billingplans')->with('success', 'Plan has been '.$message.' successfully.');
    }


    public function editBillingPlan(Request $request, $slug='')
    {
        $billingplan = BillingPlan::where('slug', $slug)->first();
        
        $billingplanstype = BillingPlanManager::getBillingPlanTypeList();
        $returnHTML = view('godpanel.edit-billingplan')->with(['billingplan'=>$billingplan, 'billingplanstype' => $billingplanstype])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }

    
    public function updateBillingPlanStatus(Request $request, $slug='')
    {
        $billingplan = BillingPlan::where('slug', $slug)->first();
        if(empty($billingplan)):
            return response()->json(array('success' => false, 'message'=>'Something went wrong.'));
        else:
            $billingplan->status = $request->status;
            $billingplan->save();
            return response()->json(array('success' => true, 'message'=>'Plan status has been updated.'));
        endif;
    }

//-------------------Plan Ends here----------------------------------//


//...................For Timeframe page----------------------------//

public function getBillingTimeframe(Request $request)
    {
        $billingtimeframes = BillingTimeframe::select("id", "title", "slug", "status", 'standard_buffer_period', 'validity', 'validity_type',DB::Raw("(CASE WHEN is_custom = 1 THEN 'Yes' ELSE 'No' END) AS custome_text, (CASE WHEN is_lifetime = 1 THEN 'Yes' ELSE 'No' END) AS timelimit_text"))->orderBy('title', 'asc')->paginate(20);
        $validity_type = BillingPlanManager::gettValidityTypeList();
        return view('godpanel/billingtimeframe')->with(['billingtimeframes'=>$billingtimeframes, 'validity_type'=>$validity_type]);
    }


    public function saveBillingTimeframe(Request $request, $slug='')
    {
        $message = 'added';
        $rules = array(
            'title' => 'required|unique:billing_timeframes|string|max:150',
        );
        if(!empty($slug)){
            $timeframe = BillingTimeframe::where('slug', $slug)->first();
            if(empty($timeframe)){
                return redirect()->back()->withErrors(['error' => "Something went wrong."]);
            }
            $rules['title'] = array('required', Rule::unique('billing_timeframes')->ignore($timeframe->id));
            $message = 'updated';
        }else{
            $timeframe = new BillingTimeframe;
            $timeframe->slug = uniqid();
        }

        $timeframe->is_lifetime = ($request->has('is_lifetime') && $request->is_lifetime == 'on') ? '1' : '2';

        if($timeframe->is_lifetime ==2):
            $rules['standard_buffer_period'] = 'required';
        endif;

        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        
        $timeframe->title = $request->title;
        $timeframe->validity_type = $request->validity_type;
        $timeframe->standard_buffer_period = ($request->standard_buffer_period!='')?$request->standard_buffer_period:0;
        $timeframe->validity = ($request->validity!='')?$request->validity:0;
        $timeframe->status = ($request->has('status') && $request->status == 'on') ? '1' : '2';
        $timeframe->is_custom = ($request->has('is_custom') && $request->is_custom == 'on') ? '1' : '2';
        
        $timeframe->save();
        $timeframeId = $timeframe->id;
        return redirect()->route('billingtimeframes')->with('success', 'Timeframe has been '.$message.' successfully.');
    }


    public function editBillingTimeframe(Request $request, $slug='')
    {
        $billingtimeframe = BillingTimeframe::where('slug', $slug)->first();
        $validity_type = BillingPlanManager::gettValidityTypeList();
        $returnHTML = view('godpanel.edit-billingtimeframe')->with(['billingtimeframe'=>$billingtimeframe, 'validity_type'=>$validity_type])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }

    
    public function updateBillingTimeframeStatus(Request $request, $slug='')
    {
        $BillingTimeframe = BillingTimeframe::where('slug', $slug)->first();
        if(empty($BillingTimeframe)):
            return response()->json(array('success' => false, 'message'=>'Something went wrong.'));
        else:
            $BillingTimeframe->status = $request->status;
            $BillingTimeframe->save();
            return response()->json(array('success' => true, 'message'=>'Timeframe status has been updated.'));
        endif;
    }
    //-------------------timiframe Ends here----------------------------------//



    //...................For pricing page----------------------------//

    public function getBillingPricing(Request $request)
    {
        $billingpricings = BillingPricing::with('billingtimeframe:id,title')->select("billing_pricings.id", "price", "billing_pricings.slug", "billing_pricings.status", 'old_price', 'billing_plan_id', 'billing_timeframe_id', 'billing_plan_types.title as plantype', 'billing_plans.title as plan_name')
                                           ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')
                                           ->join('billing_plan_types', 'billing_plan_types.id', '=', 'billing_plans.plan_type')->orderBy('id', 'asc')->paginate(20);
        $billingplanlist = BillingPlanManager::getBillingPlanList();
        $billingtimeframelist = BillingPlanManager::getBillingTimeframeList();
        return view('godpanel/billingpricing')->with(['billingpricings'=>$billingpricings, 'billingplanlist'=>$billingplanlist, 'billingtimeframelist'=>$billingtimeframelist]);
    }


    public function saveBillingPricing(Request $request, $slug='')
    {
        $message = 'added';
        $billing_plan_id = $request->billing_plan_id;
        $billing_timeframe_id = $request->billing_timeframe_id;
        $rules = array(
            'billing_plan_id' => ['required', 'numeric', Rule::unique('billing_pricings')->where('billing_timeframe_id', $billing_timeframe_id)],
            'billing_timeframe_id' => 'required|numeric',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        );
        
        if(!empty($slug)){
            $bpricing = BillingPricing::where('slug', $slug)->first();
            if(empty($bpricing)){
                return redirect()->back()->withErrors(['error' => "Something went wrong."]);
            }
            
            $rules['billing_plan_id'] = array('required', 'numeric', Rule::unique('billing_pricings')->where('billing_timeframe_id', $billing_timeframe_id)->ignore($bpricing->id));;
            $message = 'updated';
        }else{
            $bpricing = new BillingPricing;
            $bpricing->slug = uniqid();
        }

        $messages = [
            'unique'    => 'Price for this combination is already added'
        ];

        $validation  = Validator::make($request->all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        
        $bpricing->old_price = (!empty($bpricing->price))?(($bpricing->price > $request->price || $bpricing->price < $request->price)?$bpricing->price:$bpricing->old_price):0;
        $bpricing->price = $request->price;
        $bpricing->billing_plan_id = $request->billing_plan_id;
        $bpricing->billing_timeframe_id = $request->billing_timeframe_id;
        $bpricing->status = ($request->has('status') && $request->status == 'on') ? '1' : '2';
        
        $bpricing->save();
        $bpricingId = $bpricing->id;
        return redirect()->route('billingpricing')->with('success', 'Pricing has been '.$message.' successfully.');
    }


    public function editBillingPricing(Request $request, $slug='')
    {
        $billingpricing       = BillingPricing::where('slug', $slug)->first();
        $billingplanlist      = BillingPlanManager::getBillingPlanList();
        $billingtimeframelist = BillingPlanManager::getBillingTimeframeList();
        $returnHTML = view('godpanel.edit-billingpricing')->with(['billingpricing'=>$billingpricing, 'billingplanlist'=>$billingplanlist, 'billingtimeframelist'=>$billingtimeframelist])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }


    public function updateBillingPricingStatus(Request $request, $slug='')
    {
        $BillingPricing = BillingPricing::where('slug', $slug)->first();
        if(empty($BillingPricing)):
            return response()->json(array('success' => false, 'message'=>'Something went wrong.'));
        else:
            $BillingPricing->status = $request->status;
            $BillingPricing->save();
            return response()->json(array('success' => true, 'message'=>'Pricing status has been updated.'));
        endif;
    }
    //-------------------pricing Ends here----------------------------------//

    
    //...................For Client Subscription page----------------------------//

    public function getDemoClientList()
    {
        $clients = Client::where('is_deleted', 0)->where('client_type', 2)->orderBy('created_at', 'DESC')->paginate(20);
        return view('godpanel/demo_clients')->with(['clients'=>$clients]);
    }

    public function getClientSubscription(Request $request)
    {
        $billingplanlist      = BillingPlanManager::getBillingPlanList();
        $billingtimeframelist = BillingPlanManager::getBillingTimeframeList();
        $paymentstatuslist    = BillingPlanManager::gettPaymentStatusList();
        $plantypelist         = BillingPlanManager::getBillingPlanTypeList();
        $clientlists          = BillingPlanManager::getClientList(1);
        
        return view('godpanel/clientsubscription')->with(['clientlists'=>$clientlists, 'plantypelists'=>$plantypelist, 'billingplanlist'=>$billingplanlist, 'billingtimeframelist'=>$billingtimeframelist, 'paymentstatuslist'=>$paymentstatuslist]);
    }


    public function addClientSubscription()
    {
        $billingsoftwareplanlist      = BillingPlanManager::getBillingPlanList(1);
        $billinghostingplanlist       = BillingPlanManager::getBillingPlanList(2);
        $billingplantypelist          = BillingPlanManager::getBillingPlanTypeList();
        $billingpricinglistjson       = BillingPlanManager::getBillingPricingListjson();
        $clientlist                   = BillingPlanManager::getClientList(1);
        
        return view('godpanel/addclientsubscription')->with(['billingsoftwareplanlist'=>$billingsoftwareplanlist, 'clientlist'=>$clientlist, 'billinghostingplanlist'=>$billinghostingplanlist, 'billingplantypelist'=>$billingplantypelist, 'billingpricinglistjson'=>$billingpricinglistjson]);
    }

    public function getclientbillingdetails(Request $request, $clientid, $plantype)
    {
        $clientdata = Client::select("code","start_billing_date", DB::Raw("DATE_FORMAT(start_billing_date, '%d-%b-%Y') as start_billing_date_text"))->where('id', $clientid)->first();
        if(empty($clientdata)):
            return response()->json(array('success' => false));
        else:
            $billingsubscriptiondata = BillingSubscription::select("billing_plan_title", "billing_timeframe_title", "billing_price", "end_date", "billing_pricings.billing_plan_id", "billing_price_id", DB::Raw("(CASE WHEN end_date IS NULL THEN 'Active' WHEN end_date >= date(NOW()) THEN 'Active' ELSE 'Expired' END) AS status_text, DATE_FORMAT(start_date, '%d-%b-%Y') as start_date, DATE_FORMAT(end_date, '%d-%b-%Y') as end_date_text, DATE_FORMAT(next_due_date, '%d-%b-%Y') as next_due_date, (CASE WHEN is_paid =1 THEN 'Paid' ELSE 'Unpaid' END) AS payment_text"))
            ->join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
            ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')->where('client_id', $clientid)->where('billing_plans.plan_type', $plantype)->orderBy('start_date', 'desc')->first();
            
            if(!empty($billingsubscriptiondata)):
                $billingsubscriptiondata->end_date_text = ($billingsubscriptiondata->end_date_text == null)?'':$billingsubscriptiondata->end_date_text;
                $billingsubscriptiondata->next_due_date = ($billingsubscriptiondata->next_due_date == null)?'':$billingsubscriptiondata->next_due_date;
            endif;
            $billingstartdate = ($clientdata->start_billing_date!=NULL)?date('d-m-Y', strtotime($clientdata->start_billing_date)):'';
            return response()->json(array('success' => true, 'billing_start_date'=>$billingstartdate, 'lastsubscriptiondata'=>(!empty($billingsubscriptiondata))?$billingsubscriptiondata:array()));
        endif;
    }


    public function saveClientSubscription(Request $request)
    {
        $rules = array(
            'client' => ['required', 'numeric'],
            'plan_type' => 'required|numeric',
            'software_plans' => 'required_if:plan_type,1',
            'hosting_plans' => 'required_if:plan_type,2',
            'pricing' => 'required',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        );
        

        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        if($request->plan_type == 1):
            $soft_host_planid = $request->software_plans;
        else:
            $soft_host_planid = $request->hosting_plans;
        endif;
        $plandata = BillingPlan::select("title")->where('id', $soft_host_planid)->first();

        $clientdata = Client::select("start_billing_date")->where('id', $request->client)->first();
        $billing_start_date = ($request->start_billing_date!='')?date('Y-m-d', strtotime($request->start_billing_date)):'';
        if(!empty($clientdata) && ($clientdata->start_billing_date == NULL || $clientdata->start_billing_date == '')):
            if($billing_start_date!=''):
                $updateclient = Client::where('id', $request->client)->update(['start_billing_date'=>$billing_start_date]);
            endif;
        endif;
        
        $client_subscription = BillingSubscription::select("billing_subscriptions.id", "end_date", "billing_price_id")
                               ->join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
                               ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')->where('billing_plans.plan_type', $request->plan_type)->where('client_id', $request->client)->orderBy('start_date', 'desc')->first();

        $pricing_timeframe_data = BillingPricing::with('billingtimeframe')->select('id', 'billing_timeframe_id')->has('billingtimeframe')->where('id', $request->pricing)->first();
       
        $billingtimeframe = $pricing_timeframe_data->billingtimeframe;
        if(!empty($pricing_timeframe_data) && !empty($plandata)):
            $clientsubs                          = new BillingSubscription;
            $clientsubs->slug                    = uniqid();
            $clientsubs->client_id               = $request->client;
            $clientsubs->billing_plan_title      = $plandata->title;
            $clientsubs->billing_timeframe_title = $billingtimeframe->title;
            $clientsubs->billing_price_id        = $request->pricing;
            $clientsubs->billing_price           = $request->price;
            $clientsubs->start_date              = (!empty($client_subscription))?(($client_subscription->end_date!= NULL && $client_subscription->end_date!='')?date("Y-m-d", strtotime('+ 1 day' , strtotime($client_subscription->end_date))):date('Y-m-d',time())):(($billing_start_date!='')?$billing_start_date:date('Y-m-d',time()));

            if($clientsubs->start_date == ''):
                return redirect()->back()->withInput()->withErrors(['error' => "Error, Subscription Start Date is not set."]);
            endif;
            if($billingtimeframe->is_lifetime == 2):
                $end_date           = date("Y-m-d", strtotime('+ '.$billingtimeframe->validity.' '.$billingtimeframe->validity_type , strtotime($clientsubs->start_date)));
                $clientsubs->end_date            = date("Y-m-d", strtotime('- 1 day' , strtotime($end_date)));
                $clientsubs->next_due_date       = date("Y-m-d", strtotime('+ '.$billingtimeframe->standard_buffer_period.' day' , strtotime($clientsubs->end_date)));
            endif;
            /* if(!empty($client_subscription)):
                $updateclientsubs = BillingSubscription::where('id', $client_subscription->id)->update(['end_date'=>date("Y-m-d", strtotime('- 1 day' , strtotime($clientsubs->start_date)))]);
            endif; */
            $clientsubs->save();
        else:
            return redirect()->back()->withInput()->withErrors(['error' => "Something went wrong."]);
        endif;
        return redirect()->route('clientsubscription')->with('success', 'Client Subscription added successfully.');
    }


    public function deleteClientSubscription(Request $request, $slug)
    {
        $subsdata = BillingSubscription::where('slug', $slug)->first();
        if(empty($subsdata)):
            return response()->json(array('success' => false));
        else:
            $billingsubscriptiondelete  = BillingSubscription::where('slug', $slug)->delete();
            return response()->json(array('success' => true, 'message'=>'Subscription deleted successfully.'));
        endif;
    }

    public function editClientSubscription(Request $request, $slug)
    {
        $subscriptiondata = BillingSubscription::with('client:id,name')->select("billing_subscriptions.id", "billing_subscriptions.slug", "billing_plan_title", "billing_timeframe_title", "billing_price", "start_date", "end_date", "next_due_date", "client_id", "billing_plans.plan_type")
                                                ->join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
                                                ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')
                                                ->join('billing_plan_types', 'billing_plan_types.id', '=', 'billing_plans.plan_type')
                                                ->has('client')->where('billing_subscriptions.slug', $slug)->first();
        $subscriptionnew  = BillingSubscription::join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
                                                ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')
                                                ->join('billing_plan_types', 'billing_plan_types.id', '=', 'billing_plans.plan_type')
                                                ->where('billing_subscriptions.id', '>', $subscriptiondata->id)->where('client_id', $subscriptiondata->client_id)->where('plan_type', $subscriptiondata->plan_type)->count();

        $subscriptiondata->end_date = ($subscriptiondata->end_date != NULL && $subscriptiondata->end_date!='')?date('d-m-Y',strtotime($subscriptiondata->end_date)):'';
        $subscriptiondata->next_due_date = ($subscriptiondata->next_due_date != NULL && $subscriptiondata->next_due_date!='')?date('d-m-Y',strtotime($subscriptiondata->next_due_date)):'';
        return view('godpanel/editclientsubscription')->with(['subscriptiondata'=>$subscriptiondata, 'subscriptionnew'=>$subscriptionnew]);
    }

    public function updateClientSubscription(Request $request, $slug)
    {
        $rules = array(
            'start_date' => 'required|date',
            'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        );
        

        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $clientsubs = BillingSubscription::where('slug', $slug)->first();

        if(empty($clientsubs)):
            return redirect()->route('clientsubscription')->with('error', 'Something went wrong.');
        endif;

        $clientsubs->billing_price = $request->price;
        $clientsubs->start_date = date('Y-m-d', strtotime($request->start_date));
        $clientsubs->end_date = ($request->end_date=='')?NULL:date('Y-m-d', strtotime($request->end_date));
        $clientsubs->next_due_date = ($request->next_due_date=='')?NULL:date('Y-m-d', strtotime($request->next_due_date));
        
        $clientsubs->save();
        
        return redirect()->route('clientsubscription')->with('success', 'Client Subscription updated successfully.');
    }

    public function editSubscriptionPayment(Request $request, $slug)
    {
        if(!empty($slug)):
            $subscriptiondata = BillingSubscription::select("billing_subscriptions.id", "billing_subscriptions.slug", "billing_plan_title", "is_paid", "billing_timeframe_title", "billing_price", "client_id", "billing_plan_types.title as plan_type", "clients.name as client_name", "billing_price_id", DB::Raw("DATE_FORMAT(start_date, '%d-%b-%Y') as start_date, DATE_FORMAT(end_date, '%d-%b-%Y') as end_date, DATE_FORMAT(next_due_date, '%d-%b-%Y') as next_due_date"))
                                ->join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
                                ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')
                                ->join('billing_plan_types', 'billing_plan_types.id', '=', 'billing_plans.plan_type')
                                ->join('clients', 'clients.id', '=', 'billing_subscriptions.client_id')
                                ->where('billing_subscriptions.slug', $slug)->first();
            $subspaymentdata  = BillingPaymentTransation::select("billing_subscription_id", "payment_method", "receipt", "paid_amount", DB::Raw("DATE_FORMAT(payment_date, '%d-%b-%Y') as payment_date"))->where("billing_subscription_id", $subscriptiondata->id)->first();
        endif; 
        $payment_methodlist   =  BillingPlanManager::getPaymentMethodList();
        $returnHTML           = view('godpanel.edit-clientsubscription_payment')->with(['subscriptiondata'=>$subscriptiondata, 'payment_methodlist'=>$payment_methodlist, 'subspaymentdata'=>$subspaymentdata])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }


    public function updateClientSubscriptionPayment(Request $request)
    {
        
        $rules = array(
            'payment_method' => 'required',
            'paid_amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'payment_date' => 'date|required_if:payment_method,Stripe,Off The Platform',
        );

        if(empty($request->receipt_file) && !$request->hasFile('receipt')):
            $rules['receipt'] = 'required_if:payment_method,Stripe,Off The Platform';
        endif;
        
        
        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }

        $subspayment                  = BillingPaymentTransation::where('billing_subscription_id',  $request->subsid)->first();
        if(empty($subspayment)):
            $subspayment              = new BillingPaymentTransation;
            $subspayment->billing_subscription_id = $request->subsid;
        endif;
        
        if ($request->hasFile('receipt')):
            $file = $request->file('receipt');
            $subspayment->receipt     = Storage::disk('s3')->put($this->receiptFolderName, $file,'public');
        else:
            $subspayment->receipt     =  (!empty($request->receipt_file))?$request->receipt_file:'';
        endif;

        $subspayment->payment_method  = $request->payment_method;
        $subspayment->paid_amount     = $request->paid_amount;

        if($request->payment_date!=''):
            $subspayment->payment_date    = date('Y-m-d',strtotime($request->payment_date));
        endif;
        $subspayment->save();

        $update = BillingSubscription::where('id', $request->subsid)->update(['is_paid'=>(!empty($request->chk_is_paid)?1:0)]);
        
        return redirect()->route('clientsubscription')->with('success', 'Subscription Payment updated successfully.');
    }
    
    public function filter(Request $request)
    {
        $search_value        = $request->get('search');
        $clientid            = $request->get('clientid');
        $subscriptionstatus  = $request->get('subscriptionstatus');
        $paymentstatus       = $request->get('paymentstatus');
        $plantype            = $request->get('plantype');
        
        $client_subscription = BillingSubscription::select("billing_subscriptions.id", "billing_subscriptions.slug", "billing_plan_title", "billing_timeframe_title", "billing_price", "billing_price_id", "client_id", "billing_plans.plan_type", "is_paid", "start_date", "end_date", "next_due_date", DB::Raw("(CASE WHEN end_date IS NULL THEN 'Active' WHEN end_date >= date(NOW()) THEN 'Active' ELSE 'Expired' END) AS status_text, (CASE WHEN is_paid =1 THEN 'Paid' ELSE 'Unpaid' END) AS payment_text, DATE_FORMAT(start_date, '%d-%b-%Y') as start_date_text, DATE_FORMAT(end_date, '%d-%b-%Y') as end_date_text, DATE_FORMAT(next_due_date, '%d-%b-%Y') as next_due_date_text, billing_plan_types.title as plan_type_text, clients.name AS client_name"))
                                                    ->join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
                                                    ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')
                                                    ->join('billing_plan_types', 'billing_plan_types.id', '=', 'billing_plans.plan_type')
                                                    ->join('clients', 'clients.id', '=', 'billing_subscriptions.client_id');
        
        if (!empty($request->get('clientid'))):
            $clientid = $request->get('clientid');
            $client_subscription = $client_subscription->where('client_id', $clientid);
        endif;

        if (!empty($request->get('plantype'))):
            $client_subscription = $client_subscription->where('billing_plans.plan_type', $plantype);
        endif;
        
        if($request->get('paymentstatus')!=''):
            if($request->get('paymentstatus') ==0):
                $client_subscription = $client_subscription->whereRaw("(is_paid ='0' or is_paid IS NULL)");
            elseif($request->get('paymentstatus') =="1"):
                $client_subscription = $client_subscription->where('is_paid', $paymentstatus);
            endif;
        endif;

        if (!empty($request->get('subscriptionstatus'))):
            if($request->get('subscriptionstatus') =="Active"):
                $client_subscription = $client_subscription->whereRaw("(end_date>='".date('Y-m-d',time())."' or end_date IS NULL)");
            else:
                $client_subscription = $client_subscription->whereRaw("(end_date<'".date('Y-m-d',time())."' and end_date IS NOT NULL)");
            endif;
        endif;

        if (!empty($request->get('search'))):
            $client_subscription = $client_subscription->whereRaw("(billing_plan_title like '%".$search_value."%' or 
                                                        billing_timeframe_title like '%".$search_value."%' or 
                                                        clients.name like '%".$search_value."%' or billing_price like '%".$search_value."%' or 
                                                        (CASE WHEN end_date IS NULL THEN 'Active' WHEN end_date >= date(NOW()) THEN 'Active' ELSE 'Expired' END) like '%".$search_value."%' or 
                                                        DATE_FORMAT(start_date, '%d-%b-%Y') like '%".$search_value."%' or 
                                                        DATE_FORMAT(end_date, '%d-%b-%Y') like '%".$search_value."%' or 
                                                        DATE_FORMAT(next_due_date, '%d-%b-%Y') like '%".$search_value."%' or 
                                                        billing_plan_types.title like '%".$search_value."%' or 
                                                        (CASE WHEN is_paid =1 THEN 'Paid' ELSE 'Unpaid' END) like '%".$search_value."%')");
        endif;

        //$client_subscription = $client_subscription->whereRaw("billing_subscriptions.id in (select MAX(billing_subscriptions.id) from billing_subscriptions inner join billing_pricings on billing_pricings.id=billing_subscriptions.billing_price_id inner join billing_plans on billing_plans.id=billing_pricings.billing_plan_id group by client_id, billing_plans.plan_type)");
        $client_subscription = $client_subscription->orderBy('id', 'DESC')->get();
        $count = 0;
        return Datatables::of($client_subscription)
            ->addColumn('action',function($client_subscription){
                $subscriptionnew  = BillingSubscription::join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
                                                        ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')
                                                        ->join('billing_plan_types', 'billing_plan_types.id', '=', 'billing_plans.plan_type')
                                                        ->where('billing_subscriptions.id', '>', $client_subscription->id)->where('client_id', $client_subscription->client_id)->where('plan_type', $client_subscription->plan_type)->count();
                return (($subscriptionnew==0 && $client_subscription->payment_text!="Paid")?'<a href="#delete" class="btn btn-outline-danger deletesubscrip" data-id="'.$client_subscription->slug.'" style="padding:0px;">&nbsp;<i class="mdi mdi-delete"></i>&nbsp;</a>&nbsp;|&nbsp;':'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;').'<a href="'.route('clientsubscription.edit', [$client_subscription->slug]).'" class="btn btn-outline-info" style="padding:0px;">&nbsp;<i class="mdi mdi-square-edit-outline"></i>&nbsp;</a>';
            })
            ->addColumn('payment_text',function($client_subscription){
                if($client_subscription->payment_text=='Paid'):
                    return '<span class="badge bg-success" style="color:#fff;"><i class="fas fa-check"></i> '.$client_subscription->payment_text.'</span>&nbsp;|&nbsp;<a href="#payment" data-id="'.$client_subscription->slug.'" class="btn btn-outline-success btn-rounded btn-sm waves-effect waves-light paymentbutton">View</a>';
                else:
                    return '<span class="badge bg-danger" style="color:#fff;"><i class="fas fa-times-circle"></i> '.$client_subscription->payment_text.'</span>&nbsp;|&nbsp;<a href="#payment" data-id="'.$client_subscription->slug.'" class="btn btn-outline-warning btn-rounded btn-sm waves-effect waves-light paymentbutton">Payment</a>';
                endif;
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'payment_text'])
        ->make(true);
    }
    //---------------------------------client subscription end here-------------------------------//
}
