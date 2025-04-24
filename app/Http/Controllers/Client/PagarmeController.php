<?php

namespace App\Http\Controllers\Client;

use Auth, Log, Redirect, Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Client\{BaseController};
use App\Models\{ClientCurrency, PaymentOption, PayoutOption, Cart, SubscriptionPlansUser, Order, Payment, CartAddon, CartCoupon, CartProduct, CartProductPrescription, UserVendor, User,OrderProductAddon, OrderProduct, OrderProductPrescription, VendorOrderStatus, OrderVendor, OrderTax, VendorConnectedAccount};

class PagarmeController extends BaseController
{
    use \App\Http\Traits\PagarmePaymentManager;
	use \App\Http\Traits\ToasterResponser;
	use \App\Http\Traits\ApiResponser;

	private $api_key;
	private $secret_key;
    private $pagarme;
	public $currency;

	public function __construct()
  	{
		$pagarme_creds = PayoutOption::getCredentials('pagarme');
	    $creds_arr = json_decode($pagarme_creds->credentials);
	    $this->api_key = $creds_arr->api_key ?? '';
	    $this->secret_key = $creds_arr->secret_key ?? '';

        $this->pagarme = new \PagarMe\Client($this->api_key);

		$primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $this->currency = (isset($primaryCurrency->currency->iso_code)) ? $primaryCurrency->currency->iso_code : '';
        $this->currency_id = (isset($primaryCurrency->currency_id)) ? $primaryCurrency->currency_id : '';
	}

	public function createVendorPayoutAccount(Request $request, $domain='')
    {
		// try{
			$user = Auth::user();
			$vendor = $request->vendor;
			$checkIfExists = VendorConnectedAccount::where('vendor_id', $vendor)->first();
			if($vendor > 0){
				if($checkIfExists){
					return response()->json(['status'=> 'Error', 'message' => __('Account has been already created')]);
				}

				$request->validate([
					'bank_code' => 'required|numeric|digits:3',
					'agencia' => 'required|numeric',
					// 'agencia_dv' => 'required|numeric|max:1',
					'conta' => 'required|numeric',
					'conta_dv' => 'required|string|max:2',
					'document_number' => 'required',
					'legal_name' => 'required|regex:/^[a-zA-Z ]*$/|max:30',
					'type' => 'required'
				], [
					'bank_code.required' => __('The bank code field is required.'),

					'agencia.required' => __('The agency field is required.'),
					'agencia.numeric' => __('The agency must be a number.'),
					// 'agencia.digits' => __('The agency must be 5 digits.'),
					
					'conta.required' => __('The account number field is required.'),
					'conta.numeric' => __('The account number must be a number.'),
					// 'conta.digits' => __('The account number must be 13 digits.'),
					
					'conta_dv.required' => __('The account verification digit field is required.'),
					'conta_dv.max' => __('The account verification digit may not be greater than 2.'),

					'document_number.required' => __('The account CPF or CNPJ field is required.'),

					'legal_name.required' => __('The full name or business name field is required.'),
					'legal_name.regex' => __('The full name or business name format is invalid.'),
					'legal_name.max' => __('The full name or business name may not be greater than 30.'),

					'type.required' => __('The account type field is required.'),
				]);

				$bankAccount = $this->pagarme->bankAccounts()->create([
					'bank_code' => $request->bank_code,
					'agencia' => $request->agencia,
					'agencia_dv' => $request->agencia_dv,
					'conta' => $request->conta,
					'conta_dv' => $request->conta_dv,
					'document_number' => $request->document_number,
					'legal_name' => $request->legal_name,
					'type' => $request->type
				]);
				$bank_account_id = $recipient_id = '';
				if($bankAccount){
					$bank_account_id = $bankAccount->id;
				}

				if(!empty($bank_account_id)){
					$recipient = $this->pagarme->recipients()->create([
						'automatic_anticipation_enabled' => 'false', 
						'bank_account_id' => $bank_account_id,
						'transfer_enabled' => 'false',
					]);
					if($recipient){
						$connectdAccount = new VendorConnectedAccount();
						$connectdAccount->user_id = $user->id;
						$connectdAccount->vendor_id = $vendor;
						$connectdAccount->account_id = $recipient->id;
						$connectdAccount->payment_option_id = 3;
						$connectdAccount->status = 1;
						$connectdAccount->save();
					}
				}
				
			}else{
				return response()->json(['status'=> 'Error', 'message' => __('Invalid Data')]);
			}

			return $this->successResponse($bankAccount, __('Your bank account has been created successfully'));
		// }
		// catch(\Exception $ex){
		// 	return response()->json(['status'=> 'Error', 'message' => $ex->getMessage()]);
		// }
    }

    public function getBankAccounts()
    {
        $bankAccounts = $this->pagarme->bankAccounts()->getList();
        return $bankAccounts;
    }

	public function vendorPayoutViaPagarme(request $request, $domain='')
    {
        try{
            $user = Auth::user();
            $connected_account = VendorConnectedAccount::where('vendor_id', $request->vendor_id)->first();
            if($connected_account && (!empty($connected_account->account_id)))
			{
                $amount = getDollarCompareAmount($request->amount, $this->currency_id);
                
				$balance = $this->pagarme->balances()->get();
				// dd($balance);
				if($balance->available->amount < $amount * 100){
					return $this->errorResponse(__('Insufficient balance in your pagarme account'), 400);
				}

                // Create transfer
                $transfer = $this->pagarme->transfers()->create([
					'amount' => $amount * 100,
					'recipient_id' => $connected_account->account_id
				]);
                $transactionReference = $transfer->balance_transaction;
                return $this->successResponse($transactionReference, __('Payout is completed successfully'), 200);

            }else{
                return $this->errorResponse(getNomenclatureName('vendors', false) . __(' is not connected to pagarme'), 400);
            }
        }catch(\Exception $ex){
            return $this->errorResponse($ex->getMessage(), 400);
        }
    }
}
