<?php
namespace App\Http\Traits;

use DB;
use Auth;
use HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\ClientPreference;
use App\Models\Client;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;

trait CustomerSignupSuccessEmailTrait{

    /**
     * getMultiBanner
     *
     * @param  mixed
     * @return void
     */

     public function sendCustomerSignupSuccessEmail($user)
     {
        if(getAdditionalPreference(['is_cust_success_signup_email'])['is_cust_success_signup_email'] == 1)
        {
          $content = '';
          $client_detail = Client::first();
          $email_template = EmailTemplate::where('slug', '=', 'newcustomersignup')->first();
          $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
          if($email_template){
              $content = $email_template->content;
              $content = str_ireplace("{name}", $user->name, $content);
              $content = str_ireplace("{client_name}", $client_detail->name, $content);
              $email_data = [
                  'name' => $user->name,
                  'client_name' => $client_detail->name,
                  'email' => $user->email,
                  'mail_from' => $data->mail_from,
                  'powered_by' => url('/'),
                  'phone_no' => $user->phone_number,
                  'logo' => $client_detail->logo['original'],
                  'email_template_content' => $content,
                  'subject' => $email_template->subject,      
              ];
              dispatch(new \App\Jobs\sendCustomerRegistrationEmail($email_data))->onQueue('customer_signup_email');
          }
        }
     }
}
