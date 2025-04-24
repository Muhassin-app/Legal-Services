<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Models\ClientCurrency;
use App\Models\PaymentOption;
use App\Models\Vendor;
use Illuminate\Contracts\Session\Session;
use Razorpay\Api\Api;
use Illuminate\Http\Request;
use Validator;

class RazorpayGatwayController extends Controller
{
    use ApiResponser;

    public $API_KEY;
    public $API_SECRET_KEY;
    public $test_mode;
    public $api;
    public $currency;

    public function __construct()
    {
        $razorpay_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'razorpay')->where('status', 1)->first();
        $creds_arr = json_decode($razorpay_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $api_secret_key = (isset($creds_arr->api_secret_key)) ? $creds_arr->api_secret_key : '';
        $this->test_mode = (isset($razorpay_creds->test_mode) && ($razorpay_creds->test_mode == '1')) ? true : false;

        $this->API_KEY = $api_key;
        $this->API_SECRET_KEY = $api_secret_key;
        $this->api = new Api($api_key, $api_secret_key);
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : '';
        $this->currency_id = (isset($primaryCurrency->currency_id)) ? $primaryCurrency->currency_id : '';
   
        $this->token = base64_encode($api_key.':'.$api_secret_key);
        $this->api_url = 'https://api.razorpay.com/v1/';
    
    }

    public function razorpay_create_contact(Request $request)
    {
        try{

                $vendor = Vendor::find($request->vid);
                if(isset($vendor) && empty($vendor->razorpay_contact_json))
                {
                    $jsonData = array(
                        'name'=>$vendor->name,
                        'email'=>$vendor->email,
                        'contact'=>$vendor->phone_no,
                        'type'=>'vendor',
                        'reference_id' => base64_encode($vendor->id.'@contact')
                    ); 
                    $result = $this->postCurl('contacts',$jsonData);
                    if(isset($result) && !empty($result->id))
                    {
                        $vendor->razorpay_contact_json = json_encode($result);
                        $vendor->save();
                        session()->flash('success', 'Razorpay Contact Created Successfuly!'); 
                        return response()->json(['status'=>'200']);
                    }
                    session()->flash('error', 'Something went wrong!'); 
                    return response()->json(['error' => 'Something went wrong!'], 404);

                }else{
                    session()->flash('success', 'Razorpay Contact Already Created!'); 
                    return response()->json(['status'=>'200']);
                }

                }catch(\Exception $e)
                {
                    session()->flash('error', $e->getMessage()); 
                    return response()->json(['error' => $e->getMessage()], 404);
                }
            

    }


    public function razorpay_add_funds_accounts(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:100',
                'vid' => 'required',
                'ifsc' => 'required',
                'acc_no' => 'required',
                're_acc_no' => 'required|same:acc_no'
            ],[
                're_acc_no.same' =>'Account Number and Re-account number Must be Matched!'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
           
                $vendor = Vendor::find($request->vid);

                if(isset($vendor) && !empty($vendor->razorpay_bank_json))
                {

                    $jsonData = array(
                        'active'=>false,
                    ); 
                    $old_bank   = json_decode($vendor->razorpay_bank_json,true);
                    $bank_id    = $old_bank['id'];
                    $result     = $this->patchCurl('fund_accounts/'.$bank_id,$jsonData);
                    
                    $bank_account = array(
                                    'name'=>$request->name,
                                    'ifsc'=>$request->ifsc,
                                    'account_number'=>$request->acc_no
                                ); 
    
                    $razorpayContact = json_decode($vendor->razorpay_contact_json);

                    
                    if(isset($razorpayContact) && !empty($razorpayContact->id) && !empty($vendor->razorpay_bank_json))
                    {

                       
                        $jsonData = array(
                            'contact_id'=>$razorpayContact->id,
                            'account_type'=>'bank_account',
                            'bank_account'=>$bank_account
                        ); 
                       
                        $result = $this->postCurl('fund_accounts',$jsonData);
                       
                        if(isset($result->id) && !empty($result->id))
                        {
                            $vendor->razorpay_bank_json = json_encode($result);
                            $vendor->save();
                            return redirect()->back()->with('success','Razorpay Account Added Successfuly!');
                        }
                    }else{
                        return redirect()->back()->with('success','Already Done Account');
                    }


                }
                $bank_account = array(
                    'name'=>$request->name,
                    'ifsc'=>$request->ifsc,
                    'account_number'=>$request->acc_no
                ); 

                $razorpayContact = json_decode($vendor->razorpay_contact_json);
                if(isset($razorpayContact) && !empty($razorpayContact->id) && empty($vendor->razorpay_bank_json))
                {
                        $jsonData = array(
                            'contact_id'=>$razorpayContact->id,
                            'account_type'=>'bank_account',
                            'bank_account'=>$bank_account
                        ); 
                        $result = $this->postCurl('fund_accounts',$jsonData);
                        if(isset($result->id) && !empty($result->id))
                        {
                            $vendor->razorpay_bank_json = json_encode($result);
                            $vendor->save();
                           return redirect()->back()->with('success','Razorpay Account Added Successfuly!');
                        }
                }else{
                    return redirect()->back()->with('success','Already Done Account');
                }

            }catch(\Exception $e)
            {
                return redirect()->back()->with('error',$e->getMessage());
            }

    }


    public function razorpay_complete_funds_request(Request $request)
    {
        try{
                $vendor = Vendor::find($request->vid);
                $razorpayBank = $vendor->vendor_bank_json;
                //dd($razorpayBank);
                $amount = getDollarCompareAmount($request->amount, $this->currency_id);
                if(isset($razorpayBank) && !empty($razorpayBank->id))
                {
                        $jsonData = array(
                            'account_number'=>$razorpayBank->bank_account->account_number,
                            'fund_account_id'=>$razorpayBank->id,
                            'amount'=>$amount * 100,
                            'currency'=>$this->currency??'INR',
                            'mode'=>'IMPS',
                            'purpose'=>'payout',
                            'queue_if_low_balance'=>false,
                            'reference_id'=>$vendor->id.'@123'
                        ); 
                        $result = $this->postCurl('payouts',$jsonData);
                       
                        if(isset($result->id) && !empty($result->id))
                        {
                            return response()->json(['status'=>'200','data'=>$result]);
                        }
                        return response()->json(['status'=>'400','message'=>$result->error->description]);

                }else{
                    return response()->json(['status'=>'400','message'=>'No bank funds account founds.']);
                }

            }catch(\Exception $e)
            {
                return response()->json(['status'=>'400','data' => $e->getMessage()]);
            }

    }



        private function postCurl($endpoint,$data):object{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,  $this->api_url.$endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
            curl_setopt($ch, CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
            $headers = array();
            $headers[] = 'Accept: */*';
            $headers[] = "Authorization: Basic $this->token";
            $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            return json_decode($result); 
        }


        private function patchCurl($endpoint,$data):object{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,  $this->api_url.$endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
            curl_setopt($ch, CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'PATCH');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
            $headers = array();
            $headers[] = 'Accept: */*';
            $headers[] = "Authorization: Basic $this->token";
            $headers[] = 'Content-Type: application/json';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            return json_decode($result); 
        }

        private function getCurl($endpoint,$data):object{

            $curl = curl_init();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->api_url.$endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            if($data)
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );


            $headers = array();
            $headers[] = 'Accept: */*';
            $headers[] = "Authorization: Basic $this->token";
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
                curl_close($ch);
                dd($result); 
        }




}
