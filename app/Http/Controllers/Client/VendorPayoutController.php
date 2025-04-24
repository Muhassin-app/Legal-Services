<?php

namespace App\Http\Controllers\Client;
use DB;
use Auth;
use Session;
use DataTables;
use Carbon\Carbon;
use Omnipay\Omnipay;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,ToasterResponser,PaymentTrait};
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderVendorListExport;
use App\Http\Controllers\Client\{BaseController, StripeGatewayController, PagarmeController};
use App\Models\{Client, User, Vendor, OrderVendor, PaymentOption, PayoutOption, VendorConnectedAccount, VendorPayout, ClientCurrency};

class VendorPayoutController extends BaseController{
    use ApiResponser,ToasterResponser,PaymentTrait;
    public $gateway;
    public $currency;

    public function __construct(){
        
    }

    public function payoutConnectDetails($vendor)
    {
        $client = Client::with('country')->orderBy('id','asc')->first();
        if(isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain){
            $server_url =  "https://" . $client->custom_domain . '/';
        }else{
            $server_url =  "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . '/';
        }

        //stripe connected account details
        $codes = $this->paymentOptionArray('payout');
        $payout_creds = PayoutOption::whereIn('code', $codes)->where('status', 1)->get();
        if ($payout_creds) {
            foreach ($payout_creds as $creds) {
                $creds_arr = json_decode($creds->credentials);
                if($creds->code != 'cash'){
                    if ($creds->code == 'stripe') {
                        $creds->stripe_connect_url = '';
                        if( (isset($creds_arr->client_id)) && !empty($creds_arr->client_id) ){
                            $stripe_redirect_url = $server_url."client/verify/oauth/token/stripe";
                            $creds->stripe_connect_url = 'https://connect.stripe.com/oauth/v2/authorize?response_type=code&state='.$vendor.'&client_id='.$creds_arr->client_id.'&scope=read_write&redirect_uri='.$stripe_redirect_url;
                        }

                        // Check if vendor has connected account
                        $checkIfStripeAccountExists = VendorConnectedAccount::where(['vendor_id' => $vendor, 'payment_option_id' => $creds->id])->first();
                        if($checkIfStripeAccountExists && (!empty($checkIfStripeAccountExists->account_id))){
                            $creds->is_connected = 1;
                        }else{
                            $creds->is_connected = 0;
                        }
                        
                    }else if($creds->code == 'razorpay'){
                        $creds->is_connected = 0;
                        $vendors = Vendor::find($vendor);
                        if(@$vendors->vendor_bank_json->id)
                        {
                            $creds->is_connected = 1;
                        }
                    }

                    
                }
            }
            // dd($payout_creds->toArray());
        }

        // $ex_countries = ['INDIA'];

        // if((!empty($payout_creds->credentials)) && ($client_id != '') && (!in_array($client->country->name, $ex_countries))){
        //     $stripe_redirect_url = 'http://local.myorder.com/client/verify/oauth/token/stripe'; //$server_url."client/verify/oauth/token/stripe";
        //     $stripe_connect_url = 'https://connect.stripe.com/oauth/v2/authorize?response_type=code&state='.$id.'&client_id='.$client_id.'&scope=read_write&redirect_uri='.$stripe_redirect_url;
        // }else{
        //     $stripe_connect_url = route('create.custom.connected-account.stripe', $id);
        // }

        return $payout_creds;
    }

    public function createAccountDetails(Request $request)
    {
        $vendor         = $request->vendor;
        $payout_option  = $request->payout_option;
        $returnHTML     = '';
        if($payout_option == 'pagarme'){
            // $pagarController = new PagarmeController();
            // $banks_list      = $pagarController->getBankAccounts();
            // dd($banks_list);
            $returnHTML      =  view('backend.vendor.vendorPayout-modals')->with(['vendor'=>$vendor, 'payout_option'=> $payout_option])->render();
        }
        return $this->successResponse($returnHTML);
    }

    public function index(Request $request){
        $total_delivery_fees = OrderVendor::orderBy('id','desc');
        if (Auth::user()->is_superadmin == 0) {
            $total_delivery_fees = $total_delivery_fees->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_delivery_fees = $total_delivery_fees->sum('delivery_fee');

        $total_admin_commissions = OrderVendor::orderBy('id','desc');
        if (Auth::user()->is_superadmin == 0) {
            $total_admin_commissions = $total_admin_commissions->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_admin_commissions = $total_admin_commissions->sum(DB::raw('admin_commission_percentage_amount + admin_commission_fixed_amount'));

        $total_order_value = OrderVendor::orderBy('id','desc');
        if (Auth::user()->is_superadmin == 0) {
            $total_order_value = $total_order_value->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_order_value = $total_order_value->sum('payable_amount') - $total_delivery_fees;

        return view('backend.payment.vendor-payout')->with(['total_order_value' => decimal_format($total_order_value), 'total_admin_commissions' => decimal_format($total_admin_commissions)]);
    }

    public function filter(Request $request){
        $from_date = "";
        $to_date = "";
        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
            $from_date = $date_date_filter[0];
        }
        $vendors = Vendor::with(['orders' => function($query) use($from_date,$to_date) {
            if((!empty($from_date)) && (!empty($to_date))){
                $query->between($from_date." 00:00:00", $to_date." 23:59:59");
            }
        }])->where('status', '!=', '2')->orderBy('id', 'desc');

        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }

        $vendors = $vendors->get();
        foreach ($vendors as $vendor) {
            $vendor->total_paid = 0.00;
            // $vendor->url = route('vendor.show', $vendor->id);
            $vendor->view_url = route('vendor.show', $vendor->id);
            $vendor->delivery_fee = decimal_format($vendor->orders->sum('delivery_fee'));
            $vendor->payable_amount = decimal_format($vendor->orders->sum('payable_amount'));
            $vendor->order_value = decimal_format(($vendor->payable_amount - $vendor->delivery_fee));
            // $vendor->payment_method = decimal_format($vendor->orders->whereIn('payment_option_id', [2,3, 4])->sum('payable_amount'));
            // $vendor->promo_admin_amount = decimal_format($vendor->orders->where('coupon_paid_by', 1)->sum('discount_amount'));
            // $vendor->promo_vendor_amount = decimal_format($vendor->orders->where('coupon_paid_by', 0)->sum('discount_amount'));
            // $vendor->cash_collected_amount = decimal_format($vendor->orders->where('payment_option_id', 1)->sum('payable_amount'));
            $vendor->admin_commission_amount = decimal_format($vendor->orders->sum('admin_commission_percentage_amount') + $vendor->orders->sum('admin_commission_fixed_amount'));
            // $vendor->vendor_earning = decimal_format(($vendor->orders->sum('payable_amount') - $vendor->promo_vendor_amount - $vendor->promo_admin_amount - $admin_commission_amount));

            $is_stripe_connected = 0;
            $checkIfStripeAccountExists = VendorConnectedAccount::where('vendor_id', $vendor->id)->first();
            if($checkIfStripeAccountExists && (!empty($checkIfStripeAccountExists->account_id))){
                $is_stripe_connected = 1;
            }
            $vendor->is_stripe_connected = $is_stripe_connected;

            $vendor->vendor_earning = decimal_format(($vendor->order_value - $vendor->admin_commission_amount));
        }
        return Datatables::of($vendors)
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->collection = $instance->collection->filter(function ($row) use ($request){
                        if (Str::contains(Str::lower($row['name']), Str::lower($request->get('search')))){
                            return true;
                        }
                        return false;
                    });
                }
            })->make(true);
    }

    public function export() {
        return Excel::download(new OrderVendorListExport, 'vendor_list.xlsx');
    }


    public function vendorPayoutRequests(Request $request){

        $total_delivery_fees = $orderVendor  = OrderVendor::whereHas('orderDetail', function ($query) {
            $query->where('payment_status', 1);
        })->orderBy('id','desc');
        if (Auth::user()->is_superadmin == 0) {
            $total_delivery_fees = $total_delivery_fees->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_delivery_fees = $total_delivery_fees->sum('delivery_fee');

        $total_admin_commissions = $orderVendor;
        if (Auth::user()->is_superadmin == 0) {
            $total_admin_commissions = $total_admin_commissions->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_admin_commissions = $total_admin_commissions->sum(DB::raw('admin_commission_percentage_amount + admin_commission_fixed_amount'));

        $total_order_value = $orderVendor;
        if (Auth::user()->is_superadmin == 0) {
            $total_order_value = $total_order_value->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
      
        $total_order_value = $total_order_value->sum('payable_amount') - $total_delivery_fees;


        $total_promo_amount = OrderVendor::orderBy('id','desc')->where('order_status_option_id','!=',3);
        if (Auth::user()->is_superadmin == 0) {
            $total_promo_amount = $total_promo_amount->whereHas('vendor.permissionToUser', function ($query){
                $query->where('user_id', Auth::user()->id);
            });
        }
        $total_promo_amount = $total_promo_amount->where('coupon_paid_by', 0)->sum('discount_amount');

        $vendor_payouts = VendorPayout::orderBy('id','desc');
        if (Auth::user()->is_superadmin == 0) {
            $vendor_payouts = $vendor_payouts->whereHas('vendor.permissionToUser', function ($query)  {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendor_payouts = $vendor_payouts->where('status', 1)->sum('amount');

        $past_payout_value = $vendor_payouts;


        $pending_payouts = VendorPayout::where('status', 0);
        $completed_payouts = VendorPayout::whereIn('status', [1,2]);
        $pending_payout_value = $pending_payouts->sum('amount');
        $available_funds = $total_order_value - $total_admin_commissions - $total_promo_amount - $past_payout_value;
        $completed_payout_value = $completed_payouts->sum('amount');
        $pending_payout_count = $pending_payouts->count();
        $completed_payout_count = $completed_payouts->count();
        $payout_options = PayoutOption::where('status', 1)->get();
        $client_currency = ClientCurrency::with('currency')->where('is_primary', 1)->first();
        $currency_symbol = $client_currency->currency->symbol ?? '$';

        return view('backend.payment.vendorPayoutRequests')->with(['total_order_value' => decimal_format($total_order_value), 'total_admin_commissions' => decimal_format($total_admin_commissions),'total_available_value'=>decimal_format($available_funds) ,'pending_payout_value'=>decimal_format($pending_payout_value), 'completed_payout_value'=>decimal_format($completed_payout_value), 'pending_payout_count'=>$pending_payout_count, 'completed_payout_count'=>$completed_payout_count, 'payout_options'=>$payout_options, 'currency_symbol'=>$currency_symbol]);
    }

    public function vendorPayoutRequestsFilter(Request $request){
        $from_date = "";
        $to_date = "";
        $user = Auth::user();
        $status = $request->status;
        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
            $from_date = $date_date_filter[0];
        }
        $vendor_payouts = VendorPayout::with(['vendor', 'user', 'payoutOption'])->orderBy('id','desc');
        if($user->is_superadmin == 0){
            $vendor_payouts = $vendor_payouts->whereHas('vendor.permissionToUser', function ($query) use($user) {
                $query->where('user_id', $user->id);
            });
        }
        $vendor_payouts = $vendor_payouts->where('status', $status)->get();
        foreach ($vendor_payouts as $payout) {
            $payout->date = dateTimeInUserTimeZone($payout->created_at, $user->timezone);
            $payout->vendorName = @$payout->vendor->name;
            $payout->requestedBy = ucfirst(isset($payout->user) ? $payout->user->name : "");
            $payout->amount = decimal_format($payout->amount);
            $payout->type = $payout->payoutOption->title;
        }
        return Datatables::of($vendor_payouts)
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                // if (!empty($request->get('search'))) {
                //     $instance->collection = $instance->collection->filter(function ($row) use ($request){
                //         if (Str::contains(Str::lower($row['name']), Str::lower($request->get('search')))){
                //             return true;
                //         }
                //         return false;
                //     });
                // }
            })->make(true);
    }

    public function vendorPayoutRequestComplete(Request $request, $domain = ''){
        try{
            $user = Auth::user();
            $id = $request->payout_id;
            $payout_option_id = $request->payout_option_id;
            $payout = VendorPayout::where('id', $id)->first();
            $vendor_id = $payout->vendor_id;
            $request->request->add(['vendor_id' => $vendor_id]);

            $total_delivery_fees = OrderVendor::where('vendor_id', $vendor_id)->orderBy('id','desc');
            if ($user->is_superadmin == 0) {
                $total_delivery_fees = $total_delivery_fees->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $total_delivery_fees = $total_delivery_fees->sum('delivery_fee');

            $total_promo_amount = OrderVendor::where('vendor_id', $vendor_id)->orderBy('id','desc');
            if ($user->is_superadmin == 0) {
                $total_promo_amount = $total_promo_amount->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $total_promo_amount = $total_promo_amount->where('coupon_paid_by', 0)->sum('discount_amount');

            $total_admin_commissions = OrderVendor::where('vendor_id', $vendor_id)->orderBy('id','desc');
            if ($user->is_superadmin == 0) {
                $total_admin_commissions = $total_admin_commissions->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $total_admin_commissions = $total_admin_commissions->sum(DB::raw('admin_commission_percentage_amount + admin_commission_fixed_amount'));

            $total_order_value = OrderVendor::where('vendor_id', $vendor_id)->orderBy('id','desc');
            if ($user->is_superadmin == 0) {
                $total_order_value = $total_order_value->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $total_order_value = $total_order_value->sum('payable_amount') - $total_delivery_fees;

            $vendor_payouts = VendorPayout::where('vendor_id', $vendor_id)->orderBy('id','desc');
            if($user->is_superadmin == 0){
                $vendor_payouts = $vendor_payouts->whereHas('vendor.permissionToUser', function ($query) use($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $vendor_payouts = $vendor_payouts->where('status', 1)->sum('amount');

            $past_payout_value = $vendor_payouts;
            $available_funds = $total_order_value - $total_admin_commissions - $total_promo_amount - $past_payout_value;

            // Check if requested amount is valid
            if($request->amount > $available_funds){
                $toaster = $this->errorToaster('Error', __('Payout amount is greater than vendor available funds'));
                return Redirect()->back()->with('toaster', $toaster);
            }
            
            /////// Payout via stripe ///////
            if($payout_option_id == 2){
                $stripeController = new StripeGatewayController();
                $response = $stripeController->vendorPayoutViaStripe($request)->getData();
                if($response->status != 'Success'){
                    $toaster = $this->errorToaster('Error', __($response->message));
                    return Redirect()->back()->with('toaster', $toaster);
                }
                $request->request->add(['transaction_id' => $response->data]);
            }

            /////// Payout via pagarme ///////
            if($payout_option_id == 3){
                $pagarmeController = new PagarmeController();
                $response = $pagarmeController->vendorPayoutViaPagarme($request)->getData();
                if($response->status != 'Success'){
                    $toaster = $this->errorToaster('Error', __($response->message));
                    return Redirect()->back()->with('toaster', $toaster);
                }
                $request->request->add(['transaction_id' => $response->data]);
            }


            /////// Payout via Razorpay ///////
            if($payout_option_id == 4){
                $razorpayController = new RazorpayGatwayController();
                $request->request->add(['vid' => $vendor_id]);
                $response = $razorpayController->razorpay_complete_funds_request($request)->getData();
                if($response->status != '200'){
                    $toaster = $this->errorToaster('Error', __($response->message));
                    return Redirect()->back()->with('toaster', $toaster);
                }
                $request->request->add(['transaction_id' => $response->data->id]);
            }
            
            // update payout request
            $request->request->add(['status' => 1]);
            $this->updateVendorPayoutRequest($request, $payout);

            $toaster = $this->successToaster(__('Success'), __('Payout has been completed successfully'));
        }
        catch(Exception $ex){
            $toaster = $this->errorToaster(__('Errors'), $ex->message());
        }
        return Redirect()->back()->with('toaster', $toaster);
    }

    public function updateVendorPayoutRequest($request, $payout=''){
        try{
            DB::beginTransaction();
            $payout->transaction_id = $request->transaction_id;
            $payout->status = $request->status;
            $payout->update();
            DB::commit();
            return $this->successResponse('', __('Payout has been completed successfully'), 200);
        }
        catch(\Exception $ex){
            DB::rollback();
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }
}
