<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmsTemplate;
use DB;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $option_count = DB::table('sms_templates')->count();
        $sms_templates = array(
            ['id' => 1,'label' => "Order Placed Successfully", 'slug' => 'order-place-Successfully', 'subject' => "Order Placed Successfully", 'content' => "Hi {user_name} Your order of amount {amount} for order number {order_number}", 'tags' => "{user_name},{amount},{order_number}"],
            ['id' => 2,'label' => "Otp Sms For Vendor Login", 'slug' => 'otp-sms-vendor-login', 'subject' => "Otp Sms For Vendor Login", 'content' => "Please enter otp-{otp_code}. Keep it safe and don't show to other.", 'tags' => "{otp_code}"],
            ['id' => 3,'label' => "Otp Sms For User Signup", 'slug' => 'otp-sms-user-signup', 'subject' => "Otp Sms User For Signup", 'content' => "Please enter otp-{otp_code}. Keep it safe and don't show to other.", 'tags' => "{otp_code}"],
            ['id' => 4,'label' => "Otp Sms For User Login", 'slug' => 'otp-sms-user-login', 'subject' => "Otp Sms For User Login", 'content' => "Please enter otp-{otp_code}. Keep it safe and don't show to other.", 'tags' => "{otp_code}"],
            ['id' => 5,'label' => "User Signup Sms", 'slug' => 'user-signup-sms', 'subject' => "User Signup Sms", 'content' => "Dear {user_name}, Thanks for creating an account with us!", 'tags' => "{user_name}"],
            ['id' => 6,'label' => "Otp to verify Account", 'slug' => 'verify-account', 'subject' => "Otp to verify Account", 'content' => "Dear {user_name}, Please enter OTP {otp_code} to verify your account.{app_hash_key}", 'tags' => "{user_name},{otp_code},{app_hash_key}"],
            ['id' => 7,'label' => "Order Tracking", 'slug' => 'order-tracking-url', 'subject' => "Order Tracking", 'content' => "Hi {user_name} Your order number {order_number} has been on the way.please track your order via this link {track_url}", 'tags' => "{user_name},{amount},{order_number},{track_url},{order_status}"],
            ['id' => 8,'label' => "Otp Sms For Tracking url", 'slug' => 'otp-sms-tracking-url', 'subject' => "Otp Sms Access For Tracking Url", 'content' => "Please enter OTP {otp_code}. Keep it safe and don't show to other.", 'tags' => "{otp_code}"],
            ['id' => 9,'label' => "Order Canceled", 'slug' => 'order-canceled', 'subject' => "Ride Cancel By Driver", 'content' => "Hi {user_name} Your ride {order_number} canceled by driver.", 'tags' => "{user_name},{order_number}"],
            ['id' => 10,'label' => "Order Completed", 'slug' => 'order-completed', 'subject' => "Order Completed", 'content' => "Hi {user_name} Your ride {order_number} successfully completed.", 'tags' => "{user_name},{order_number}"],
            ['id' => 11,'label' => "Ride Booked", 'slug' => 'ride-booked', 'subject' => "Ride Booked Confirmation.", 'content' => "Hi {user_name} Your ride {order_number} successfully booked.", 'tags' => "{user_name},{order_number}"],
            ['id' => 12,'label' => "Order Canceled (Vendor)", 'slug' => 'order-canceled-vendor', 'subject' => "Order Canceled (Vendor)", 'content' => "Your order ({order_id}) is canecelled by Admin", 'tags' => "{order_number}"],

        );

        if($option_count == 0)
      	{
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('shipping_options')->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                DB::table('sms_templates')->insert($sms_templates);
            }
        else{
            foreach ($sms_templates as $option) {

                $find = SmsTemplate::where('id',$option['id'])->first();
                if ($find !== null) {
                    $find->update(['tags' => $option['tags']]);
                }
                else{
                    $newUser = SmsTemplate::Create($option);
                }


            }


        }

    }
}
