<?php

namespace App\Http\Traits\Giftcard;

use App\Models\{UserGiftCard ,ClientPreference, Client,EmailTemplate};
use Carbon\Carbon;
use Session, DB , Auth,Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail; 


trait GiftCardTrait
{


    public function getUserActiveGiftCard(){
        $active_giftcard = [];
        try {
            $user = Auth::user();
            if( $user ){
                $now = Carbon::now()->toDateTimeString();
                $active_giftcard = UserGiftCard::select('*',DB::raw('count(*) as total'))->with('giftCard')->whereHas('giftCard',function ($query) use ($now){
                    return  $query->whereDate('expiry_date', '>=', $now);
                })->where(['is_used'=>'0','user_id'=>$user->id])->groupBy('gift_card_id')->get();
            }
            return $active_giftcard;
        } catch (Exception $e) {
            return $active_giftcard;
        }
    }

    public function getGiftCardCode($str = 'ROYO',$length = 4){
        
            $code =strtoupper( mb_substr($str, 0, 4) );
   
            do {
                for ($i = $length; $i--; $i > 0) {
                    $code .= mt_rand(0, 9);
                }
            } while (!empty(UserGiftCard::where('gift_card_code', $code)->first(['gift_card_code'])));
            return $code;
       
    }
    public function GiftCardMail( $mail_to,$mail_to_name ,$GiftCard ,$user ,$currSymbol = '$' ){   
        try {
            $email_template = EmailTemplate::where('slug',  'GiftCard')->first();
           
            if($email_template){
                $content = $email_template->content;
                $giftCardHtml = view('email.giftCard')->with(['GiftCard' => $GiftCard,'currSymbol'=>$currSymbol])->render();
             
                $content = str_ireplace("{GiftCard}", $giftCardHtml, $content);
                $content = str_ireplace("{sender_name}", $user->name, $content);
                $content = str_ireplace("{gift_amount}", $currSymbol. $GiftCard->amount , $content);
                $content = str_ireplace("{customer_name}", $mail_to_name , $content);
           
                $data = ClientPreference::select('mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username',  'mail_password', 'mail_encryption', 'mail_from', 'admin_email')->first();
                $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->first();
                if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_from) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
           
                    $email_data = [
                        'GiftCard' => $GiftCard,
                        'currSymbol' => $currSymbol,
                        'email' => $mail_to, //'harbans.singh@codebrewinnovations.com',//"harbans.sayonakh@gmail.com",//  $mail_to,//
                        'mail_from' => $data->mail_from,
                        'client_name' => $client->name ?? 'Royo',
                        'logo' => $client->logo['original'],
                        'subject' => __('Gift Card Buy From Royo '). $client->name,
                        'email_template_content' => $content,
                        'user' => $user,
                    ];
                    // pr($email_data);
                    // pr(new \App\Mail\GiftCardEmail($email_data));
                    // $mail = 	Mail::to('harbans.singh@codebrewinnovations.com')->send(new \App\Mail\GiftCardEmail($email_data));
                    // pr($mail);
                     dispatch(new \App\Jobs\GiftCardEmailJob($email_data))->onQueue('verify_email');
                   
                }
            }
            
        } catch (\Exception $e) {   
           Log::info($e->getMessage()); 
        }
    }

    
}
