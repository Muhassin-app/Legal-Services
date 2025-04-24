<?php
namespace App\Http\Controllers\Client;

use Session;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ {
    ToasterResponser,
    MtnMomoPaymentManager,
    PaymentTrait
};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use Illuminate\Support\Facades\DB;
use App\Models\ {
    Client,
    ClientPreference,
    PaymentOption,
    PayoutOption
};
use Log;

class PaymentOptionController extends BaseController
{
    use ToasterResponser,MtnMomoPaymentManager,PaymentTrait;

    private $folderName = 'payoption';

    public function __construct()
    {
        $code = Client::orderBy('id', 'asc')->value('code');
        $this->folderName = '/' . $code . '/payoption';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payment_codes = $this->paymentOptionArray('payment_codes');
        $payOption = PaymentOption::whereIn('code', $payment_codes)->get();
          
        $payout_codes = $this->paymentOptionArray('payout');
        $payoutOption = PayoutOption::whereIn('code', $payout_codes)->get();

        return view('backend/payoption/index')->with([
            'payOption' => $payOption,
            'payoutOption' => $payoutOption
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Brand $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id)
    {
        $status = 0;
        $msg = $request->method_name . ' deactivated successfully!';

        $saved_creds = PaymentOption::select('credentials')->where('id', $id)->first();

        if ((isset($saved_creds)) && (! empty($saved_creds->credentials))) {
            $json_creds = $saved_creds->credentials;
        } else {
            $json_creds = NULL;
        }

        if ($request->has('active') && $request->active == 'on') {
            $status = 1;
            $msg = $request->method_name . ' activated successfully!';

            if (strtolower($request->method_name) == 'paypal') {
                $json_creds = json_encode(array(
                    'username' => $request->paypal_username,
                    'password' => $request->paypal_password,
                    'signature' => $request->paypal_signature
                ));
            } else if (strtolower($request->method_name) == 'stripe') {
                if ($request->stripe_api_key != 'admin@640') {
                    $json_creds = json_encode(array(
                        'api_key' => $request->stripe_api_key
                    ));
                }
            }
        }

        PaymentOption::where('id', $id)->update([
            'status' => $status,
            'credentials' => $json_creds
        ]);

        return redirect()->back()->with('success', $msg);
    }

    public function updateAll(Request $request, $domain = '')
    {
        $msg = 'Payment options have been saved successfully!';
        $method_id_arr = $request->input('method_id');
        $method_name_arr = $request->input('method_name');
        $active_arr = $request->input('active');
        $test_mode_arr = $request->input('sandbox');

        foreach ($method_id_arr as $key => $id) {
            $saved_creds = PaymentOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (! empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr[$id])) && ($active_arr[$id] == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr[$id])) && ($test_mode_arr[$id] == 'on')) {
                    $test_mode = 1;
                }

                if (isset($method_name_arr[$key])) {
                    switch (strtolower($method_name_arr[$key])) {
                        case 'paypal':
                            $validatedData = $request->validate([
                                'paypal_username' => 'required',
                                'paypal_password' => 'required',
                                'paypal_signature' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'username' => $request->paypal_username,
                                'password' => $request->paypal_password,
                                'signature' => $request->paypal_signature
                            ));
                            break;

                        case 'cod':
                            $json_creds = json_encode(array(
                                'cod_min_amount' => $request->cod_min_amount
                            ));
                            break;

                        case 'stripe':
                            $validatedData = $request->validate([
                                'stripe_api_key' => 'required',
                                'stripe_publishable_key' => 'required',
                                'stripe_webhook_signature' => 'required'
                            ], [
                                'stripe_api_key.required' => 'Stripe secret key field is required'
                            ]);

                            if ($request->stripe_api_key != 'admin@640') {
                                $stripe_arr = array(
                                    'api_key' => $request->stripe_api_key,
                                    'publishable_key' => $request->stripe_publishable_key,
                                    'webhook_signature' =>  $request->stripe_webhook_signature
                                );
                                if (isset($request->stripe_client_id)) {
                                    $stripe_arr['client_id'] = $request->stripe_client_id;
                                }
                                $json_creds = json_encode($stripe_arr);
                            }
                            break;

                        case 'toyyibpay':
                            $validatedData = $request->validate([
                                'toyyibpay_api_key' => 'required',
                                'toyyibpay_redirect_uri' => 'required'
                            ], [
                                'toyyibpay_api_key.required' => 'Toyyibpay secret key field is required'
                            ]);

                            if ($request->stripe_api_key != 'admin@640') {
                                $toyyibpay_arr = array(
                                    'toyyibpay_api_key' => $request->toyyibpay_api_key,
                                    'toyyibpay_redirect_uri' => $request->toyyibpay_redirect_uri
                                );

                                $json_creds = json_encode($toyyibpay_arr);
                            }
                            break;

                        case 'stripe_fpx':
                            $validatedData = $request->validate([
                                'stripe_fpx_secret_key' => 'required',
                                'stripe_fpx_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'secret_key' => $request->stripe_fpx_secret_key,
                                'publishable_key' => $request->stripe_fpx_publishable_key
                            ));
                            break;

                        case 'yoco':
                            $validatedData = $request->validate([
                                'yoco_secret_key' => 'required',
                                'yoco_public_key' => 'required'
                            ], [
                                'yoco_secret_key.required' => 'Yoco secret key field is required'
                            ]);
                            $json_creds = json_encode(array(
                                'secret_key' => $request->yoco_secret_key,
                                'public_key' => $request->yoco_public_key
                            ));
                            break;

                        case 'paystack':
                            $validatedData = $request->validate([
                                'paystack_secret_key' => 'required',
                                'paystack_public_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'secret_key' => $request->paystack_secret_key,
                                'public_key' => $request->paystack_public_key
                            ));
                            break;

                        case 'paylink':
                            $validatedData = $request->validate([
                                'paylink_api_key' => 'required',
                                'paylink_api_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'api_key' => $request->paylink_api_key,
                                'api_secret_key' => $request->paylink_api_secret_key
                            ));
                            break;

                        case 'razorpay':
                            $validatedData = $request->validate([
                                'razorpay_api_key' => 'required',
                                'razorpay_api_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'api_key' => $request->razorpay_api_key,
                                'api_secret_key' => $request->razorpay_api_secret_key
                            ));
                            break;

                        case 'payfast':
                            $validatedData = $request->validate([
                                'payfast_merchant_id' => 'required',
                                'payfast_merchant_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'merchant_id' => $request->payfast_merchant_id,
                                'merchant_key' => $request->payfast_merchant_key,
                                'passphrase' => $request->payfast_passphrase
                            ));
                            break;

                        case 'mobbex':
                            $validatedData = $request->validate([
                                'mobbex_api_key' => 'required',
                                'mobbex_api_access_token' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'api_key' => $request->mobbex_api_key,
                                'api_access_token' => $request->mobbex_api_access_token
                            ));
                            break;

                        case 'gcash':
                            $validatedData = $request->validate([
                                'gcash_public_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'public_key' => $request->gcash_public_key
                            ));
                            break;

                        case 'simplify':
                            $validatedData = $request->validate([
                                'simplify_public_key' => 'required',
                                'simplify_private_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'public_key' => $request->simplify_public_key,
                                'private_key' => $request->simplify_private_key
                            ));
                            break;

                        case 'square':
                            $validatedData = $request->validate([
                                'square_application_id' => 'required',
                                'square_access_token' => 'required',
                                'square_location_id' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'application_id' => $request->square_application_id,
                                'api_access_token' => $request->square_access_token,
                                'location_id' => $request->square_location_id
                            ));
                            break;

                        case 'ozow':
                            $validatedData = $request->validate([
                                'ozow_site_code' => 'required',
                                'ozow_private_key' => 'required',
                                'ozow_api_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'site_code' => $request->ozow_site_code,
                                'private_key' => $request->ozow_private_key,
                                'api_key' => $request->ozow_api_key
                            ));
                            break;
                        case 'pagarme':
                            $validatedData = $request->validate([
                                'pagarme_api_key' => 'required',
                                'pagarme_secret_key' => 'required',
                                'pagarme_multiplier' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'api_key' => $request->pagarme_api_key,
                                'secret_key' => $request->pagarme_secret_key,
                                'multiplier' => $request->pagarme_multiplier
                            ));
                            break;

                        case 'checkout':
                            $validatedData = $request->validate([
                                'checkout_secret_key' => 'required',
                                'checkout_public_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'secret_key' => $request->checkout_secret_key,
                                'public_key' => $request->checkout_public_key
                            ));
                            break;

                        case 'authorize_net':
                            $validatedData = $request->validate([
                                'authorize_net_login_id' => 'required',
                                'authorize_net_transaction_key' => 'required',
                                'authorize_net_client_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'login_id' => $request->authorize_net_login_id,
                                'transaction_key' => $request->authorize_net_transaction_key,
                                'client_key' => $request->authorize_net_client_key
                            ));
                            break;

                        case 'kongapay':
                            $validatedData = $request->validate([
                                'kongapay_api_key' => 'required',
                                'kongapay_merchant_id' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'api_key' => $request->kongapay_api_key,
                                'merchant_id' => $request->kongapay_merchant_id
                            ));
                            break;

                        case 'ccavenue':
                            $validatedData = $request->validate([
                                'ccavenue_enc_key' => 'required',
                                'ccavenue_access_code' => 'required',
                                'ccavenue_merchant_id' => 'required',
                                'custom_url' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'enc_key' => $request->ccavenue_enc_key,
                                'access_code' => $request->ccavenue_access_code,
                                'merchant_id' => $request->ccavenue_merchant_id,
                                'custom_url' => $request->custom_url
                            ));
                            break;

                        case 'viva_wallet':
                            $validatedData = $request->validate([
                                'viva_wallet_client_id' => 'required',
                                'viva_wallet_client_key' => 'required',
                                'viva_wallet_merchant_id' => 'required',
                                'viva_wallet_merchant_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'client_id' => $request->viva_wallet_client_id,
                                'client_key' => $request->viva_wallet_client_key,
                                'merchant_id' => $request->viva_wallet_merchant_id,
                                'merchant_key' => $request->viva_wallet_merchant_key
                            ));
                            break;

                        case 'easypaisa':
                            $validatedData = $request->validate([
                                'easypaisa_store_id' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'easypaisa_store_id' => $request->easypaisa_store_id
                            ));
                            break;

                        case 'cashfree':
                            $validatedData = $request->validate([
                                'cashfree_app_id' => 'required',
                                'cashfree_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'app_id' => $request->cashfree_app_id,
                                'secret_key' => $request->cashfree_secret_key
                            ));
                            break;

                        case 'easebuzz':
                            $validatedData = $request->validate([
                                'easebuzz_merchant_key' => 'required',
                                'easebuzz_salt' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'easebuzz_Sub_merchant' => ($request->has('easebuzz_Sub_merchant') && $request->easebuzz_Sub_merchant == 'on') ? 1 : 0,
                                'easebuzz_merchant_key' => $request->easebuzz_merchant_key,
                                'easebuzz_salt' => $request->easebuzz_salt
                            ));
                            break;

                        case 'paytab':
                            $validatedData = $request->validate([
                                'paytab_profile_id' => 'required',
                                'paytab_server_key' => 'required',
                                'paytab_client_key' => 'required',
                                'paytab_mobile_server_key' => 'required',
                                'paytab_mobile_client_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'profile_id' => $request->paytab_profile_id,
                                'server_key' => $request->paytab_server_key,
                                'client_key' => $request->paytab_client_key,
                                'mobile_server_key' => $request->paytab_mobile_server_key,
                                'mobile_client_key' => $request->paytab_mobile_client_key
                            ));
                            break;

                        case 'vnpay':
                            $validatedData = $request->validate([
                                'vnpay_website_id' => 'required',
                                'vnpay_server_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'vnpay_website_id' => $request->vnpay_website_id,
                                'vnpay_server_key' => $request->vnpay_server_key
                            ));
                            break;

                        case 'mvodafone':
                            $validatedData = $request->validate([
                                'mvodafone_client_id' => 'required',
                                'mvodafone_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'client_id' => $request->mvodafone_client_id,
                                'secret_key' => $request->mvodafone_secret_key
                            ));
                            break;

                        case 'flutterwave':
                            $validatedData = $request->validate([
                                'flutterwave_client_id' => 'required',
                                'flutterwave_secret_key' => 'required',
                                'flutterwave_enc_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'client_id' => $request->flutterwave_client_id,
                                'secret_key' => $request->flutterwave_secret_key,
                                'enc_key' => $request->flutterwave_enc_key
                            ));
                            break;

                        case 'braintree':
                            $validatedData = $request->validate([
                                'braintree_merchant_id' => 'required',
                                'braintree_public_key' => 'required',
                                'braintree_private_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'merchant_id' => $request->braintree_merchant_id,
                                'public_key' => $request->braintree_public_key,
                                'private_key' => $request->braintree_private_key
                            ));
                            break;

                        case 'payphone':
                            $validatedData = $request->validate([
                                'payphone_id' => 'required',
                                'payphone_client_id' => 'required',
                                'payphone_token' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'id' => $request->payphone_id,
                                'client_id' => $request->payphone_client_id,
                                'token' => $request->payphone_token
                            ));
                            break;

                        case 'payu':
                            $validatedData = $request->validate([
                                'payu_merchant_key' => 'required',
                                'payu_merchant_salt_v1' => 'required',
                                'payu_merchant_salt_v2' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'merchant_key' => $request->payu_merchant_key,
                                'merchant_salt_v1' => $request->payu_merchant_salt_v1,
                                'merchant_salt_v2' => $request->payu_merchant_salt_v2
                            ));
                            break;

                        case 'windcave':
                            $validatedData = $request->validate([
                                'windcave_id' => 'required',
                                'windcave_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'app_id' => $request->windcave_id,
                                'api_key' => $request->windcave_key
                            ));
                            break;

                        case 'paytech':
                            $validatedData = $request->validate([
                                'paytech_key' => 'required',
                                'paytech_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'api_key' => $request->paytech_key,
                                'secret_key' => $request->paytech_secret_key
                            ));
                            break;

                        case 'mycash':
                            $validatedData = $request->validate([
                                'mycash_api_key' => 'required',
                                'mycash_username' => 'required',
                                'mycash_password' => 'required',
                                'mycash_merchant_phone' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'api_key' => $request->mycash_api_key,
                                'username' => $request->mycash_username,
                                'password' => $request->mycash_password,
                                'merchant_phone' => $request->mycash_merchant_phone
                            ));
                            break;

                        case 'stripe_oxxo':
                            $validatedData = $request->validate([
                                'stripe_oxxo_secret_key' => 'required',
                                'stripe_oxxo_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'secret_key' => $request->stripe_oxxo_secret_key,
                                'publishable_key' => $request->stripe_oxxo_publishable_key
                            ));
                            break;

                        case 'stripe_ideal':
                            $validatedData = $request->validate([
                                'stripe_ideal_secret_key' => 'required',
                                'stripe_ideal_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'secret_key' => $request->stripe_ideal_secret_key,
                                'publishable_key' => $request->stripe_ideal_publishable_key
                            ));
                            break;
                        case 'plugnpay':
                            $validatedData = $request->validate([
                                'plugnpay_publisher_name' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'plugnpay_publisher_name' => $request->plugnpay_publisher_name
                            ));
                            break;

                        case 'offline_manual':
                            $validatedData = $request->validate([
                                'manule_payment_title' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'manule_payment_title' => $request->manule_payment_title
                            ));
                            break;
                        case 'userede':
                            $validatedData = $request->validate([
                                'userede_Rede_PV' => 'required',
                                'userede_Rede_token' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'userede_Rede_PV' => $request->userede_Rede_PV,
                                'userede_Rede_token' => $request->userede_Rede_token
                            ));
                            break;
                        case 'openpay':
                            $validatedData = $request->validate([
                                'openpay_merchant_id' => 'required',
                                'openpay_private_key' => 'required',
                                'openpay_public_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'openpay_merchant_id' => $request->openpay_merchant_id,
                                'openpay_private_key' => $request->openpay_private_key,
                                'openpay_public_key' => $request->openpay_public_key,
                                'openpay_verification_key' => $request->openpay_verification_key
                            ));
                            break;
                        case 'azul':
                            $creds = ! empty($json_creds) ? json_decode($json_creds) : '';
                            $validatedData = $request->validate([
                                'azul_main_url' => 'required',
                                'azul_alternate_url' => 'required',
                                'azul_ecommerce_url' => 'required',
                                'azul_merchant_id' => 'required',
                                'azul_auth_header_one' => 'required',
                                'azul_auth_header_two' => 'required',
                                'azul_test_url' => 'required'
                                // 'azul_ssl_certificate' => 'required',
                                // 'azul_ssl_key' => 'required'
                            ]);
                            if ($request->hasFile('azul_ssl_certificate')) {
                                $file = $request->file('azul_ssl_certificate');
                                $file_name = 'Cert/' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $path = Storage::disk('local')->put($file_name, file_get_contents($file), 'public');
                                $azul_ssl_certificate = $file_name;
                            } else {
                                $azul_ssl_certificate = (! empty($creds) && isset($creds->azul_ssl_certificate)) ? $creds->azul_ssl_certificate : '';
                            }

                            if ($request->hasFile('azul_ssl_key')) {
                                $file = $request->file('azul_ssl_key');
                                $file_name = 'Cert/' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $path = Storage::disk('local')->put($file_name, file_get_contents($file), 'public');
                                $azul_ssl_key = $file_name;
                            } else {
                                $azul_ssl_key = (! empty($creds) && isset($creds->azul_ssl_key)) ? $creds->azul_ssl_key : '';
                            }

                            $json_creds = json_encode(array(
                                'azul_main_url' => $request->azul_main_url,
                                'azul_alternate_url' => $request->azul_alternate_url,
                                'azul_ecommerce_url' => $request->azul_ecommerce_url,
                                'azul_test_url' => $request->azul_test_url,
                                'azul_merchant_id' => $request->azul_merchant_id,
                                'azul_auth_header_one' => $request->azul_auth_header_one,
                                'azul_auth_header_two' => $request->azul_auth_header_two,
                                'azul_ssl_certificate' => $azul_ssl_certificate,
                                'azul_ssl_key' => $azul_ssl_key
                            ));
                            break;
                        case 'dpo':
                            $validatedData = $request->validate([
                                'company_token' => 'required',
                                'service_type' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'company_token' => $request->company_token,
                                'service_type' => $request->service_type
                            ));
                            break;
                        case 'upay':
                            $validatedData = $request->validate([
                                'uuid_key' => 'required',
                                'aes_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'uuid_key' => $request->uuid_key,
                                'aes_key' => $request->aes_key
                            ));
                            break;
                        case 'conekta':
                            $validatedData = $request->validate([
                                'conekta_public_key' => 'required',
                                'conekta_private_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'public_key' => $request->conekta_public_key,
                                'private_key' => $request->conekta_private_key
                            ));
                            break;
                        case 'telr':
                            $validatedData = $request->validate([
                                'telr_merchant_id' => 'required',
                                'telr_api_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'merchant_id' => $request->telr_merchant_id,
                                'api_key' => $request->telr_api_key
                            ));
                            break;

                        case 'khalti':
                            $validatedData = $request->validate([
                                'khalti_public_key' => 'required',
                                'khalti_secret_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'api_key' => $request->khalti_public_key,
                                'api_secret_key' => $request->khalti_secret_key
                            ));

                        case 'mtn_momo':
                            $validatedData = $request->validate([
                                'subscription_key' => 'required',
                                'reference_id' => 'required',
                                'api_key' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'subscription_key' => $request->subscription_key,
                                'reference_id' => $request->reference_id,
                                'api_key' => $request->api_key
                            ));
                            break;
                        case 'skip_cash':
                            $validatedData = $request->validate([
                                'skip_cash_client_id' => 'required',
                                'skip_cash_key_id' => 'required',
                                'skip_cash_api_secret' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'skip_cash_client_id' => $request->skip_cash_client_id,
                                'skip_cash_key_id' => $request->skip_cash_key_id,
                                'skip_cash_api_secret' => $request->skip_cash_api_secret,
                                'skip_cash_testing_url' => $request->skip_cash_testing_url,
                                'skip_cash_live_url' => $request->skip_cash_live_url
                            ));
                            break;
                        case 'nmi':
                            $validatedData = $request->validate([
                                'nmi_client_id' => 'required',
                                'nmi_key_id' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'nmi_client_id' => $request->nmi_client_id,
                                'nmi_key_id' => $request->nmi_key_id
                            ));
                        break;

                        case 'obo':
                            $validatedData = $request->validate([
                                'obo_business_name' => 'required',
                                'obo_client_id' => 'required',
                                'obo_key_id' => 'required',
                                'obo_market_place_id' => 'required',
                                'obo_company_reference' => 'required',
                            ]);
                            $json_creds = json_encode(array(
                                'obo_business_name' => $request->obo_business_name,
                                'obo_client_id' => $request->obo_client_id,
                                'obo_key_id' => $request->obo_key_id,
                                'obo_market_place_id' => $request->obo_market_place_id,
                                'obo_company_reference' => $request->obo_company_reference,
                            ));
                        break;
                            break;

                        case 'data_trans':
                            $validatedData = $request->validate([
                                'data_trans_merchant_id' => 'required',
                                'data_trans_password' => 'required'
                            ]);

                            $data_trans_arr = array(
                                'merchant_id' => $request->data_trans_merchant_id,
                                'password' => $request->data_trans_password
                            );
                            $json_creds = json_encode($data_trans_arr);

                            break;
                        break;
                        case 'pesapal':
                            $request->validate([
                                'pesapal_consumer_key' => 'required',
                                'pesapal_consumer_secret' => 'required',
                            ]);
                            $json_creds = json_encode(array(
                                'pesapal_consumer_key' => $request->pesapal_consumer_key,
                                'pesapal_consumer_secret' => $request->pesapal_consumer_secret,
                            ));
                        break;
                        case 'powertrans':
                            $request->validate([
                                'powertrans_id' => 'required',
                                'powertrans_password' => 'required',
                            ]);
                            $json_creds = json_encode(array(
                                'powertrans_id' => $request->powertrans_id,
                                'powertrans_password' => $request->powertrans_password,
                            ));
                        break;
                        case 'mpesasafari':
                            $request->validate([
                            'mpesasafari_consumer_key' => 'required',
                            'mpesasafari_consumer_secret' => 'required',
                            'mpesasafari_shortcode' => 'required',
                            'mpesasafari_webhook' => 'required'
                            ]);
                            $json_creds = json_encode(array(
                                'mpesasafari_consumer_key' => $request->mpesasafari_consumer_key,
                                'mpesasafari_consumer_secret' => $request->mpesasafari_consumer_secret,
                                'mpesasafari_shortcode' => $request->mpesasafari_shortcode,
                                'mpesasafari_webhook' => $request->mpesasafari_webhook
                            ));
                            break;

                        case 'livee':
                            $request->validate([
                                'livee_merchant_key' => 'required',
                                'livee_resource_key' => 'required',
                            ]);
                            $json_creds = json_encode(array(
                                'livee_merchant_key' => $request->livee_merchant_key,
                                'livee_resource_key' => $request->livee_resource_key,
                            ));
                        break;
                        case 'totalpay':
                                    $request->validate([
                                    'totalpay_MerchantId' => 'required',
                                    'totalpay_password' => 'required',
                                    ]);

                                    $json_creds = json_encode(array(
                                        'totalpay_MerchantId' => $request->totalpay_MerchantId,
                                        'totalpay_password' => $request->totalpay_password,
                                    ));
                        break;
                        case 'thawani':
                                    $validatedData = $request->validate([
                                    'thawani_Apikey' => 'required',
                                    'thawani_publishKey' => 'required',


                                    ]);
                                    $json_creds = json_encode(array(
                                    'thawani_Apikey' => $request->thawani_Apikey,
                                    'thawani_publishKey' => $request->thawani_publishKey,
                                    ));
                        break;
                        case 'mastercard':
                            $json_creds = json_encode($request->validate([
                                'mastercard_merchant_id' => 'required|string',
                                'mastercard_merchant_key' => 'required|string',
                                'mastercard_operator_id' => 'required|string',
                                'mastercard_gateway' => 'required_if:sandbox[46],!=,"on"|nullable|string',
                            ]));
                            break;
                        case 'hitpay':
                            $validatedData = $request->validate([
                                'hitpay_business_key' => 'required',
                                'hitpay_salt_key' => 'required',
                            ]);
                            $json_creds = json_encode(
                                array(
                                    'hitpay_business_key' => $request->hitpay_business_key,
                                    'hitpay_salt_key' => $request->hitpay_salt_key,
                                )
                            );
                        break;

                        case 'orange_pay':
                            $request->validate([
                            'orangepay_MerchantKey' => 'required',
                            'orangepay_MerchantToken' => 'required',
                            ]);
                            $json_creds = json_encode(array(
                                'orangepay_MerchantKey' => $request->orangepay_MerchantKey,
                                'orangepay_MerchantToken' => $request->orangepay_MerchantToken,
                            ));
                        break;

                        case 'cyber_source':
                            $request->validate([
                            'cyber_source_merchant_id' => 'required',
                            'cyber_source_profile_id' => 'required',
                            'cyber_source_access_key' => 'required',
                            'cyber_source_secret_key' => 'required',
                            'bill_to_address_line1' => 'required',
                            'bill_to_address_city' => 'required',
                            'bill_to_address_state' => 'required',
                            'bill_to_address_country' => 'required',
                            'bill_to_address_postal_code' => 'required',
                            ]);
                            $json_creds = json_encode(array(
                                'cyber_source_merchant_id' => $request->cyber_source_merchant_id,
                                'cyber_source_profile_id' => $request->cyber_source_profile_id,
                                'cyber_source_access_key' => $request->cyber_source_access_key,
                                'cyber_source_secret_key' => $request->cyber_source_secret_key,
                                'bill_to_address_line1' => $request->bill_to_address_line1,
                                'bill_to_address_city' => $request->bill_to_address_city,
                                'bill_to_address_state' => $request->bill_to_address_state,
                                'bill_to_address_country' => $request->bill_to_address_country,
                                'bill_to_address_postal_code' => $request->bill_to_address_postal_code,
                            ));
                        break;
                    }
                }
            }
            PaymentOption::where('id', $id)->update([
                'status' => $status,
                'credentials' => $json_creds,
                'test_mode' => $test_mode
            ]);
        }
        $toaster = $this->successToaster(__('Success'), $msg);
        return redirect()->back()->with('toaster', $toaster);
    }

    public function payoutUpdateAll(Request $request, $domain = '')
    {
        $msg = 'Payout options have been saved successfully!';
        $method_id_arr = $request->input('method_id');
        $method_name_arr = $request->input('method_name');
        $active_arr = $request->input('active');
        $test_mode_arr = $request->input('sandbox');

        foreach ($method_id_arr as $key => $id) {
            $saved_creds = PayoutOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (! empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr[$id])) && ($active_arr[$id] == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr[$id])) && ($test_mode_arr[$id] == 'on')) {
                    $test_mode = 1;
                }

                if ((isset($method_name_arr[$key])) && (strtolower($method_name_arr[$key]) == 'stripe')) {
                    $validatedData = $request->validate([
                        'stripe_payout_secret_key' => 'required',
                        'stripe_payout_publishable_key' => 'required',
                        'stripe_payout_client_id' => 'required'
                    ]);
                    $json_creds = json_encode(array(
                        'secret_key' => $request->stripe_payout_secret_key,
                        'publishable_key' => $request->stripe_payout_publishable_key,
                        'client_id' => $request->stripe_payout_client_id
                    ));
                } else if ((isset($method_name_arr[$key])) && (strtolower($method_name_arr[$key]) == 'pagarme')) {
                    $validatedData = $request->validate([
                        'pagarme_payout_api_key' => 'required',
                        'pagarme_payout_secret_key' => 'required',
                        'pagarme_payout_multiplier' => 'required'
                    ]);
                    $json_creds = json_encode(array(
                        'api_key' => $request->pagarme_payout_api_key,
                        'secret_key' => $request->pagarme_payout_secret_key,
                        'multiplier' => $request->pagarme_payout_multiplier
                    ));
                } else if ((isset($method_name_arr[$key])) && (strtolower($method_name_arr[$key]) == 'razorpay')) {
                    $validatedData = $request->validate([
                        'razorpay_payout_api_key' => 'required',
                        'razorpay_payout_secret_key' => 'required'
                    ]);
                    $json_creds = json_encode(array(
                        'api_key' => $request->razorpay_payout_api_key,
                        'secret_key' => $request->razorpay_payout_secret_key
                    ));
                }
            }
            PayoutOption::where('id', $id)->update([
                'status' => $status,
                'credentials' => $json_creds,
                'test_mode' => $test_mode
            ]);
        }
        $toaster = $this->successToaster(__('Success'), $msg);
        return redirect()->back()->with('toaster', $toaster);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Brand $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '', $id)
    {
        $brand = Brand::where('id', $id)->first();
        $brand->status = 2;
        $brand->save();
        return redirect()->back()->with('success', 'Brand deleted successfully!');
    }

    /**
     * save the order of variant.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Variant $variant
     * @return \Illuminate\Http\Response
     */
    public function updateOrders(Request $request)
    {
        $arr = explode(',', $request->orderData);
        foreach ($arr as $key => $value) {
            $brand = Brand::where('id', $value)->first();
            if ($brand) {
                $brand->position = $key + 1;
                $brand->save();
            }
        }
        return redirect('client/category')->with('success', 'Brand order updated successfully!');
    }

    /**
     * Generate Mtn momo payment gateway api key
     */
    public function MtnmomoApiKey(Request $request)
    {
        if (empty($request)) {
            return json_encode([
                'status' => 404,
                'message' => 'Request is Empty'
            ]);
        }

        self::$_subscriptionKey = $request->subscription_key;
        self::$_referenceId = $request->reference_id;
        self::$_isSandbox = $request->sandboxCheckbox;
        self::__init(true);

        $result = self::createApiUser();
        if ($result['status'] == 201) {
            $api_data = self::createApiKey();
            if ($api_data['status'] == 201) {
                return json_encode([
                    'status' => 201,
                    'api_key' => $api_data['apiKey'],
                    'message' => 'Api key generated successfully.'
                ]);
            } else {
                return json_encode([
                    'status' => $api_data['status'],
                    'message' => $api_data['message']
                ]);
            }
        } else {
            return json_encode([
                'status' => $result['status'],
                'message' => $result['message']
            ]);
        }
    }
}
