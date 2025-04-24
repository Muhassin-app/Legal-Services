<?php

namespace App\Http\Controllers\Godpanel;

use App\Models\LumenClient;
use DB;
use App\Http\Controllers\Controller;
use App\Models\{BillingPlan, BillingPlanType, BillingTimeframe, BillingPricing, BillingSubscription, Client, BillingPaymentTransation, ClientPreferenceAdditional};
use App\Http\Controllers\Client\BaseController;
use App\Http\Traits\BillingPlanManager;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class DashBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('godpanel/dashboard');
        
    }

    public function dashboard()
    {
        $onboardclients = Client::where('status', 1)->where('is_deleted',0)->where('is_blocked', 0)->count();
        $allclients = Client::select(DB::Raw("GROUP_CONCAT(id) as ids"))->where('status', 1)->where('is_deleted',0)->where('is_blocked', 0)->first()->ids??0;

        $activeSubs  = BillingSubscription::join('clients', 'clients.id', '=', 'billing_subscriptions.client_id')
                                                    ->where('clients.status', 1)->where('clients.is_deleted',0)->where('clients.is_blocked', 0)
                                                    ->where(function ($q) {
                                                        $q->where('end_date', '>=', date('Y-m-d',time()))
                                                              ->orWhereNull('end_date');
                                                    })->count();
        
        $expSofSubs  = BillingSubscription::join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
                                            ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')
                                            ->where('billing_plans.plan_type', 1);
                                            if(@$allclients && !empty($allclients)){
                                                $expSofSubs = $expSofSubs->whereRaw("client_id in ('".$allclients."') and billing_subscriptions.id in (select MAX(id) from billing_subscriptions GROUP BY client_id) and date(end_date)< date(NOW())");
                                            }
                                            $expSofSubs = $expSofSubs->count();

        $expHosSubs  = BillingSubscription::join('billing_pricings', 'billing_pricings.id', '=', 'billing_subscriptions.billing_price_id')
                                            ->join('billing_plans', 'billing_plans.id', '=', 'billing_pricings.billing_plan_id')
                                            ->where('billing_plans.plan_type', 2);
                                            if(@$allclients && !empty($allclients)){
                                                $expHosSubs = $expHosSubs->whereRaw("client_id in ('".$allclients."') and billing_subscriptions.id in (select MAX(id) from billing_subscriptions GROUP BY client_id) and date(end_date)< date(NOW())");
                                                }
                                            $expHosSubs = $expHosSubs->count();

        $clientwithnosubs = Client::where('status', 1)->where('is_deleted',0)->where('is_blocked', 0)->whereRaw("(select count(*) from billing_subscriptions where billing_subscriptions.client_id = clients.id)=0")->count();
        
        return view('godpanel/dashboard')->with(['onboardclients'=>$onboardclients, 'activeSubs'=>$activeSubs, 'expSofSubs'=>$expSofSubs, 'expHosSubs'=>$expHosSubs, 'clientwithnosubs'=>$clientwithnosubs]);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function lumen()
    {
        $clients = LumenClient::get();

        return view('godpanel/lumen',compact('clients'));
    }
    public function lumenClientSave(Request $request)
{
    try {
        // Validation rules (same as before)

        // Validate the input data
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if a record with the same code already exists
        $existingClient = LumenClient::where('code', $request->input('code'))->first();

        if ($existingClient) {
            // Client with the same code already exists
            return redirect()->back()
                ->with('error', 'A client with the same code already exists.');
        }

        // Create a new LumenClient model and save it
        $model = new LumenClient();
        $model->domain = $request->input('domain');
        $model->code = $request->input('code');
        $model->database_name = $request->input('database_name');
        $model->lumen_access_token = $request->input('lumen_access_token_v1');
        $model->save();

        // Success message
        return redirect()->route('lumen') // Change to the appropriate route
            ->with('success', 'Lumen client saved successfully.');

    } catch (\Exception $e) {
        // Error message
        return redirect()->back()
            ->with('error', 'An error occurred while saving the Lumen client: ' . $e->getMessage());
    }
}

public function enableLumenService(Request $request)
{
    $api_domain = ClientPreferenceAdditional::where('key_name', 'lumen_domain_url')->first();
    $client = Client::find($request->client_id);

    $data = [
        'client_id' => $request->client_id,
        'is_lumen_enabled' => $request->is_lumen,
        'code' => $client->code,
        'custom_domain' => $client->custom_domain,
        'database_name' => $client->database_name,
        'name' => $client->name ?? 'lumen',
        'email' => $client->email,
        'password' => rand(11111111, 9999999),
        'is_lumen_key_expired' => $client->is_lumen_key_expired
    ];

   
    $headers = [
        'Content-Type' => 'application/json',
        'X-API-Key' => $client->lumen_access_token ?? '12345abcd',
        'code' => $client->code
    ];


    if (isset($api_domain)) {
     

        $response = Http::withHeaders($headers)->post($api_domain->key_value . '/api/v1/createLumenClient', $data);

        if ($response->status() === 200) {
            $responseData = $response->json();
            
            // Extract the API key from the response and save it in the database
            if (isset($responseData['api_key'])) {
                $client->lumen_access_token = $responseData['api_key'];
                $client->is_lumen_enabled = $request->is_lumen;
                $client->is_lumen_key_expired = 0;
                $client->lumen_timestamp = Carbon::now();
                $client->save();
            }
        } else {
            $responseData = null;
        }
    } else {
        $responseData = null;
    }

   

    return response()->json([
        'message' => 'lumen updated successfully',
        'data' => $data ?? '',
        'api_response' => $responseData ?? '',
    ], 200);
}

public function enableCampaignService(Request $request)
{
    $api_domain = ClientPreferenceAdditional::where('key_name', 'lumen_domain_url')->first();
    $client = Client::find($request->client_id);

    if($request->has('campaign_service'))

    $data = [
        'campaign_service' => $request->campaign_service,
        'code' => $client->code,
    ];
  

    $headers = [
        'Content-Type' => 'application/json',
        'X-API-Key' => $client->lumen_access_token ?? '12345abcd',
        'code' => $client->code
    ];


    if (isset($api_domain)) {
        
        $response = Http::withHeaders($headers)->post($api_domain->key_value . '/api/v1/createLumenClient', $data);

        if ($response->status() === 200) {
            $responseData = $response->json();
            

                $client->campaign_service = $request->campaign_service;
                $client->save();
            
        } else {
            $responseData = null;
        }
    } else {
        $responseData = null;
    }



    return response()->json([
        'message' => 'service updated successfully',
        'data' => $data ?? '',
        'api_response' => $responseData ?? '',
    ], 200);
}

    
}
