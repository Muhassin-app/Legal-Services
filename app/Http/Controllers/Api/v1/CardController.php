<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Models\PaymentOption;
use App\Models\SavedCards;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserSavedPaymentMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Omnipay\Omnipay;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class CardController extends Controller
{

    use ApiResponser;

    public $gateway;
    public $API_KEY;
    public $currency;
    public $api_key_new;
    public $testmodenew;

    public function config()
    {
        $stripe_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'stripe')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $api_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $this->api_key_new = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $testmode = (isset($stripe_creds->test_mode) && ($stripe_creds->test_mode == '1')) ? true : false;
        $this->testmodenew = (isset($stripe_creds->test_mode) && ($stripe_creds->test_mode == '1')) ? true : false;
        $this->gateway = Omnipay::create('Stripe');
        $this->gateway->setApiKey($api_key);
        $this->gateway->setTestMode($testmode); //set it to 'false' when go live
        $this->API_KEY = $api_key;

       
    }
    public function addCard(Request $request)
    {
        $this->config();
        try {
            \Stripe\Stripe::setApiKey($this->api_key_new);
            $user = Auth::user();

           
          
            if (empty($user->stripe_customer_id)) {
                $customer = \Stripe\Customer::create(array(
                    'description' => 'Creating Customer',
                    'name' => $user->name,
                    'email' => $user->email,
                    'metadata' => [
                        'user_id' => $user->id,
                        'phone_number' => $user->phone_number
                    ],
                    'address' => [
                        'line1'=>  '1575 Evergreen Ave',
                        'city'=> 'Juneau',
                        'state'=> 'Alaska',
                        'postal_code'=> '99801',
                        'country'=> 'US'
                    ]
                ));
            } else {
                $customer = \Stripe\Customer::retrieve($user->stripe_customer_id);
            }

            
            $customer_id = $customer['id'];
            if ($customer_id) {
                $auth_user = User::find(Auth::user()->id);
                // $auth_user->user_id = Auth::user()->id;
                $auth_user->stripe_customer_id = $customer_id;
                $auth_user->save();
            }
          

            $card = \Stripe\Customer::createSource(
                $customer_id,
                ['source' => $request->token] 
            );
             
                $saved_card = new SavedCards();
                $saved_card->user_id = $user->id;
                $saved_card->token = $request->token;
                $saved_card->card_holder_name = $request->card_holder_name ?? null;
                $saved_card->bank_name = $request->bank_name ?? null;
                $saved_card->customer_id = $customer_id;
                $saved_card->card_id = $card->id;
                $saved_card->save();
            
            return $this->successResponse(null, 'Card Added Successfully', 200);
        } catch (\Stripe\Exception\ApiErrorException $e) {


            return $this->errorResponse($e->getMessage(), '500');
        }
    }

    public function cardDetails()
    {
        try {
            $this->config();
            $stripe = new \Stripe\StripeClient($this->api_key_new);

            $customerId = Auth::user()->stripe_customer_id; // Replace with the actual customer ID

            if (!empty($customerId)) {
                $cardDetails = $stripe->customers->allSources(
                    $customerId,
                    [
                        'object' => 'card',
                        'limit' => 10
                    ]
                );

                $apiCardData = $cardDetails->data; // Assuming $apiResponse contains the API response data
              
                $savedCards = SavedCards::all(); // Retrieve the saved cards from your table

                $cardsWithDetails = [];
       
                foreach ($apiCardData as $apiCard) {
                   
                    
                    $apiCard = $apiCard->toArray();
                         
                    $cardId = $apiCard['id'];
              
                    $matchingSavedCard = $savedCards->firstWhere('card_id', $cardId);

                 
                    if ($matchingSavedCard) {
                        $apiCard['card_holder_name'] = $matchingSavedCard->card_holder_name;
                        $apiCard['bank_name'] = $matchingSavedCard->bank_name;
                    }

                    $cardsWithDetails[] = $apiCard;
                }
                
                return $this->successResponse($cardsWithDetails, null, 200);
            }

            return $this->successResponse(null, 'Cards Not Found!', 200);
        } catch (\Exception $e) {


            return $this->errorResponse($e->getMessage(), '500');
        }
    }

    public function deleteCard(Request $request)
    {

        try {
            $this->config();
            \Stripe\Stripe::setApiKey($this->api_key_new);

            $customerId = Auth::user()->stripe_customer_id; 
            $customer = \Stripe\Customer::retrieve($customerId);

            $cardId = $request->card_id;
            $card = \Stripe\Customer::retrieveSource(
                $customerId,
                $cardId
            );
            
           
            $saved_card = SavedCards::where(['card_id' => $cardId,'user_id' => Auth::id()])->first();


            $card->delete();
            if(!empty($saved_card))
            {
                $saved_card->delete();
            }
            return $this->successResponse(null, 'Cards Deleted Successfully', 200);

        } catch (ApiErrorException $e) {

            
            return $this->errorResponse('Card Not Found', 500);

        }

    }
}
