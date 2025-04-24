<?php

namespace App\Http\Controllers\Api\v1;


use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Session,Auth,DB,Timezonelist,Log;
use App\Http\Traits\Giftcard\GiftCardTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\v1\{BaseController};
use App\Models\{GiftCard,UserGiftCard,User, Cart, ClientPreference, Client, ClientCurrency , Payment, PaymentOption};

class GiftcardController extends BaseController
{
    use ApiResponser,GiftCardTrait;

    
   
       
    /**
     * getGiftCard api
     *
     * @param  mixed $request
     * @return void
     */
    public function getGiftCard(Request $request)
    {   
        try{
            
            $user = Auth::user();
            $now  = Carbon::now()->toDateTimeString();
            $GiftCard        = GiftCard::orderBy('id', 'asc')->whereDate('expiry_date', '>=', $now)->get();
            $active_giftcard = $this->getUserActiveGiftCard();
            // return array
            $respons['allGiftCard']          =  $GiftCard;
            $respons['UserActiveGiftCard']   =  $active_giftcard;
            return $this->successResponse($respons, '', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage( ), $e->getCode());
        }
    }
    
  

    

    /**
     * buy user giftCard.
     *
     * @return \Illuminate\Http\Response
     */
    public function purchaseGiftCard(Request $request)
    {
       
        if( (isset($request->user_id)) && (!empty($request->user_id)) ){
            $user = User::find($request->user_id);
        }else{
            $user = Auth::user();
        }
        $GiftCard       = GiftCard::where('id', $gift_card_id)->first();
        $senderData = !empty($request->senderData) ? json_decode($request->senderData) : '';

        $sendToMail = (isset($senderData->send_card_to_email) && !empty($senderData->send_card_to_email) ) ?  $senderData->send_card_to_email : '';
        $sendToName = (isset($senderData->send_card_to_name) && !empty($senderData->send_card_to_name) ) ?  $senderData->send_card_to_name : '';
     
       // pr( $request->all());
        if( $GiftCard ){
            $code =$this->getGiftCardCode($GiftCard->title);
            $UserGiftCard               = new UserGiftCard();
            $UserGiftCard->user_id      = $user->id;
            $UserGiftCard->gift_card_id = $GiftCard->id;
            $UserGiftCard->amount       = $GiftCard->amount;
            $UserGiftCard->expiry_date  = $GiftCard->expiry_date;
            $UserGiftCard->gift_card_code = $code;
            $UserGiftCard->buy_for_data = !empty($request->senderData) ? $request->senderData : ''; 
            $UserGiftCard->save();
            if($sendToMail != ''){
                $currency_id = isset($user->currency) ? $user->currency : 1;
               
                $clientCurrency = ClientCurrency::where('currency_id', $currency_id )->first();
                $currSymbol = (isset($clientCurrency->currency->symbol)) ? $clientCurrency->currency->symbol : '$';
                $GiftCard->userCode =  $code;
                $this->GiftCardMail($sendToMail,$sendToName, $GiftCard ,$user ,$currSymbol);
            }
            $payment                        = new Payment;
            $payment->user_id               = $user->id;
            $payment->balance_transaction   = $request->amount;
            $payment->transaction_id        = $request->transaction_id;
            $payment->reference_table_id    = $UserGiftCard->id;
            $payment->payment_option_id     = $request->payment_option_id;
            $payment->date                  = Carbon::now()->format('Y-m-d');
            $payment->type                  = 'giftCard';
            $payment->save();
            
            $message = __('Your Gift Card has been activated successfully.');
            Session::put('success', $message);
            return $this->successResponse('', $message);
            
        }else{
            return $this->errorResponse(__('Invalid Data'), 402);
        }
    }

    /**
     * buy user postGiftCardLisTCart.
     *
     * @return \Illuminate\Http\Response
     */
    public function postGiftCardLisTCart(Request $request){
         try {
            $user = Auth::user();
            $langId   = Session::has('customerLanguage') ? Session::get('customerLanguage') : 1;
            $giftCard = new \Illuminate\Database\Eloquent\Collection;
            $giftcardList = $this->getUserActiveGiftCard();
            $currency_id = Session::get('customerCurrency');
            $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            $returnHTML = view('frontend.cart.giftCard')->with(['giftcardList'=>$giftcardList, 'clientCurrency'=>$clientCurrency])->render();
            return response()->json(array('success' => true, 'html' => $returnHTML));
           
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

        
    /**
     * postVerifyGiftCardCode
     *
     * @param  mixed $request
     * @return void
     */
    public function postVerifyGiftCardCode(Request $request){
        try {
            $user = Auth::user();
            $now = Carbon::now()->toDateTimeString();
            $cart_detail = Cart::where('id', $request->cart_id)->first();
            if(!$cart_detail){
                return $this->errorResponse('Invalid Cart Id', 422);
            }
           
            $giftcard = UserGiftCard::with('giftCard')->whereHas('giftCard',function ($query) use ($now,$request){
                return  $query->whereDate('expiry_date', '>=', $now)->where('name',$request->giftCardCoe);
            })->where(['is_used'=>'0','user_id'=>$user->id])->first();

            
            if($giftcard){
                if($cart_detail->gift_card_id ==  $giftcard->gift_card_id){
                    return $this->errorResponse('Gift Card already applied.', 422);
                }
                $cart_detail->gift_card_id = $giftcard->gift_card_id;
                $cart_detail->save();
                return $this->successResponse('', 'Gift Card Used Successfully.', 200);
            }
            return $this->errorResponse('Invalid gift Card', 422);
           
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function RemoveGiftCardCode(Request $request){
        try {
             $user = Auth::user();
             
             $cart_detail = Cart::where(['id'=>$request->cart_id,'user_id'=>  $user->id ])->first();
             if(!$cart_detail){
                 return $this->errorResponse('Invalid Cart Id', 422);
             }
          
            if($cart_detail){
           
                $cart_detail->gift_card_id = null;
                $cart_detail->save();
                return $this->successResponse('', 'Gift Card Delete Successfully.', 200);
            }
            return $this->errorResponse('Invalid gift Card Id', 422);
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
     }
   
}

