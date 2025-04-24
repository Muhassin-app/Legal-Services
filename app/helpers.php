<?php

use App\Models\CartProduct;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\{CaregoryKycDoc, Cart, CartAddon, CartCoupon, CartProductPrescription, Currency, SmsTemplate, User, TempCartProduct, Vendor, VendorAdditionalInfo, WebStylingOption};
use App\Models\Nomenclature;
use App\Models\UserRefferal;
use App\Models\ProductVariant;
use App\Models\ClientPreference;
use App\Models\Client as ClientData;
use App\Models\PaymentOption;
use App\Models\ShippingOption;
use App\Models\ShowSubscriptionPlanOnSignup;
use App\Models\{VendorSlot, ClientCurrency, Order, Type, ClientPreferenceAdditional, UserVendor, VendorCategory, Product,ClientLanguage,Language,};
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Storage;
use App\Services\FirebaseService;

if (!function_exists('getFcmOauthToken')) {
    function getFcmOauthToken($url = null) {
        try {
            $preference = ClientPreferenceAdditional::where('key_name', 'firebase_account_json_file')->first();
            $fileName = $preference->key_value ?? null;
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            if ($fileName) {
                // Generate a temporary URL with the Content-Disposition header set to attachment
                $url = Storage::disk('s3')->url($fileName);
            }

            // If the URL is null, use the local file path
            $serviceAccountPath = $url ?? "voltaic-e59be-c73103aa2b73.json";


            // Determine if the file is local or on S3 based on the URL scheme
            if (filter_var($serviceAccountPath, FILTER_VALIDATE_URL)) {
                // Fetch the content from the URL and save it temporarily
                $serviceAccountContent = file_get_contents($serviceAccountPath);
                if ($serviceAccountContent === false) {
                    throw new \Exception('Failed to fetch the service account JSON file from S3.');
                }

                // Save the content to a temporary file
                $tempFilePath = tempnam(sys_get_temp_dir(), 'service_account');
                file_put_contents($tempFilePath, $serviceAccountContent);
                // Use the temporary file path for credentials
                $credentials = new ServiceAccountCredentials($scopes, $tempFilePath);
            } else {
                // Use the local file path for credentials
                $credentials = new ServiceAccountCredentials($scopes, $serviceAccountPath);
            }

            $accessToken = $credentials->fetchAuthToken();


            return $accessToken['access_token'] ?? "N/A";
        } catch (\Exception $e) {
            Log::error('Error fetching FCM OAuth token: ' . $e->getMessage());
            return null;
        }
    }
}



if (!function_exists('getFcmOauthTokenVendor')) {
    function getFcmOauthTokenVendor($url = null) {
        try {
            $preference = ClientPreferenceAdditional::where('key_name', 'firebase_vendor_account_json_file')->first();
            $fileName = $preference->key_value ?? null;
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            if ($fileName) {
                // Generate a temporary URL with the Content-Disposition header set to attachment
                $url = Storage::disk('s3')->url($fileName);
            }

            // If the URL is null, use the local file path
            $serviceAccountPath = $url ?? "voltaic-e59be-c73103aa2b73.json";


            // Determine if the file is local or on S3 based on the URL scheme
            if (filter_var($serviceAccountPath, FILTER_VALIDATE_URL)) {
                // Fetch the content from the URL and save it temporarily
                $serviceAccountContent = file_get_contents($serviceAccountPath);
                if ($serviceAccountContent === false) {
                    throw new \Exception('Failed to fetch the service account JSON file from S3.');
                }

                // Save the content to a temporary file
                $tempFilePath = tempnam(sys_get_temp_dir(), 'service_account');
                file_put_contents($tempFilePath, $serviceAccountContent);
                // Use the temporary file path for credentials
                $credentials = new ServiceAccountCredentials($scopes, $tempFilePath);
            } else {
                // Use the local file path for credentials
                $credentials = new ServiceAccountCredentials($scopes, $serviceAccountPath);
            }

            $accessToken = $credentials->fetchAuthToken();


            return $accessToken['access_token'] ?? "N/A";
        } catch (\Exception $e) {
            Log::error('Error fetching FCM OAuth token: ' . $e->getMessage());
            return null;
        }
    }
}


/*

sample notification code new

     $token = 'ezVpMQ4OT2SriCILkLhdpb:APA91bEOM8in8QOkO3-CMtQUgpX7aL5gDvV9_VFRAIG41tJLjquHasiUwkoTbiTYgGF4944Y2vNAbE8HiHg-ligtmkrTr1lF20TfZW1BNJ9XzOrjp_3lOUzmrUB0vTiFWVezniJbr3h3';
        $title = 'test notification yash';
        $body = 'test notification body';
        $project_id = 'voltaic-e59be';
        $data = [
            'key' => 'value'
        ];


        $result = sendFirebaseNotification($token,$title,$body,$data,$project_id);

*/


if (!function_exists('setUserCode')) {
    function setUserCode(){
        $userCode = session()->has('userCode');
        if(!$userCode){
            $user = ClientData::first();
            session()->put('userCode', $user->code);
        }
    }
}

// Returns the values of the additional preferences.
if (!function_exists('checkColumnExists')) {
  /** check if column exits in table
     * @param string $tableName
     * @param string @columnName
     * @return boolean true or false
     */
        function checkColumnExists($tableName, $columnName)
        {
            if (Schema::hasColumn($tableName, $columnName)){
                $cacheKey = "$tableName$columnName";
                $columnExists = Cache::remember($cacheKey, 60 * 60, function () use($tableName, $columnName) {
                    return Schema::hasColumn($tableName, $columnName);
                });
            if ($columnExists){
                return true;
            }else{
                return false;
            }
        }
    }
}

if (!function_exists('getAdditionalPreference')) {
    /**
     * getAdditionalPreference
     *
     * @param  mixed $key
     * @return void
     */
    function getAdditionalPreference($key=array() , $time = '60'){
        setUserCode();
        $return = [];
        $dbreturn= [];
        if(sizeof($key)){

            $result = ClientPreferenceAdditional::select('key_name','key_value')->whereIn('key_name',$key)->get();
            // $cacheKey = 'client_preferences_additional_'.json_encode($key);

            // $result = Cache::remember($cacheKey, $time, function () use ($key) {
            //     return ClientPreferenceAdditional::select('key_name','key_value')->whereIn('key_name',$key)->get();
            // });
            $return = array_column($result->toArray(), 'key_value', 'key_name');
            if (sizeof($result)) {
                $dbreturn = array_column($result->toArray(), 'key_value', 'key_name');
            }
            $emp = array_diff($key, array_keys($dbreturn));
            $emptyArr = array_fill_keys($emp, 0);
            $return = array_merge($emptyArr, $dbreturn);
        }
        return $return;
    }
}

if (!function_exists('getMapConfigrationPreference')) {
    /**
     * getMapConfigrationPreference
     *
     * @param  mixed $key
     * @return void
     */

    function getMapConfigrationPreference(){
        $iso3 = '';
        $mapConfigration =  getAdditionalPreference(['is_map_search_perticular_country']);
        if(isset($mapConfigration) && $mapConfigration['is_map_search_perticular_country'] == 1){
            $iso3 = ClientData::first()->country->iso3 ?? '';
        }
        return $iso3;
    }
}

// if (!function_exists('getAdditionalImageAttribute')) {
//     function getAdditionalImageAttribute($value)
//     {
//         $values = array();
//         $img = 'default/default_image.png';
//         if(!empty($value)){
//             $img = $value;
//         }
//         $ex = checkImageExtension($img);
//         $values['proxy_url'] = \Config::get('app.IMG_URL1');
//         $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
//         $values['image_fit'] = \Config::get('app.FIT_URl');

//         //$values['small'] = url('showImage/small/' . $img);
//         return $values;
//     }
// }

if (!function_exists('getVendorAdditionalPreference')) {
    function getVendorAdditionalPreference($vendorId,$key = ''){
        $vendorInfo =  VendorAdditionalInfo::where('vendor_id',$vendorId);
        if($key){
            $data =  $vendorInfo->value($key);
            if($key == 'compare_categories' && !empty($data))
                $data = explode(',',$data);

        }else{
            $data =$vendorInfo->first();
        }

        return $data??[];
    }
}

if (!function_exists('changeDateFormate')) {
    function changeDateFormate($date,$date_format){
        return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format($date_format);
    }
}

if (!function_exists('getInToken')) {
    function getInToken($amount = 1){
        setUserCode();
        $redis = Redis::connection();
        $compareCurrency = session()->has('compareCurrency');
        if(!$compareCurrency){
            $currency_id = session()->get('customerCurrency');
            $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            session()->put('compareCurrency', $clientCurrency->doller_compare);
        }

        $tokenCurrency = $redis->get("tCurrency_".session()->get('userCode'));
        $tokenCurrency = json_decode($tokenCurrency);
        if(empty($tokenCurrency)){
            $tokenCurrency = getAdditionalPreference(['token_currency'])['token_currency'];
            $redis->set("tCurrency_".session()->get('userCode'), json_encode($tokenCurrency), 'EX', 36000);
        }
        return decimal_format(($amount * ( session()->get('compareCurrency') ?? 1)) * (!empty($tokenCurrency) ? $tokenCurrency : 1));
    }
}

if (!function_exists('getJsToken')) {
    function getJsToken(){
        setUserCode();
        $redis = Redis::connection();
        $tokenCurrency = $redis->get("tCurrency_".session()->get('userCode'));
        $tokenCurrency = json_decode($tokenCurrency);
        if(empty($tokenCurrency)){
            $tokenCurrency = getAdditionalPreference(['token_currency'])['token_currency'];
            $redis->set("tCurrency_".session()->get('userCode'), json_encode($tokenCurrency), 'EX', 36000);
        }
        return decimal_format(!empty($tokenCurrency) ? $tokenCurrency : 1);
    }
}

if (!function_exists('checkShowSubscriptionPlanOnSignup')) {
    function checkShowSubscriptionPlanOnSignup(){
        $showSubscriptionPlanPopUp = 0;
        $user = Auth::user();
        $showSubscriptionPlan = ShowSubscriptionPlanOnSignup::find(1);
        if(@$showSubscriptionPlan->show_plan_customer == 1 && @$showSubscriptionPlan->every_sign_up == 1 && !empty($user)){
            $showSubscriptionPlanPopUp = 1;
        }
        return $showSubscriptionPlanPopUp;
    }
}



if (!function_exists('transformToFcmV1Format')) {
    function transformToFcmV1Format($oldData,$token)
    {
        try {
            $newData = [
                "notification" => [
                    'title' =>(string) $oldData['notification']['title'] ?? '',
                    'body'  =>(string)  $oldData['notification']['body'] ?? '',
                    // 'sound' => (string)$oldData['notification']['sound'] ?? '',
                    // "icon" => (string)$oldData['notification']['icon'] ?? '',
                    // 'click_action' =>(string) $oldData['notification']['click_action'] ?? '',
                    // "android_channel_id" => (string) $oldData['notification']['android_channel_id'] ?? ''
                ],
                "data" => [
                    'title' => $oldData['data']['title'] ?? "",
                    'body'  => $oldData['data']['body']?? ""
                ],
                // "priority" => $oldData['priority'] ?? ''
            ];

            // Check if 'registration_ids' is present in the old data
            $newData['token'] =(string)  $token ??  null;

            return $newData;
        } catch (\Exception $e) {
            Log::error('FCM Data Transformation Error: ' . $e->getMessage());
            return null;
        }
    }
}



if (!function_exists('sendFcmCurlRequest')) {
    function sendFcmCurlRequest($data ,$fcm_server_key = '',$is_vendor = 0)
    {
        $response = (new FirebaseService)->sendNotification($data,$is_vendor);
        return $response;
    }
}

if (! function_exists('sendNotificationToCustomer')) {
    function sendNotificationToCustomer($devices,$order_number=''){
        $client_preferences = ClientPreference::select('fcm_server_key','favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            $data = [
                "registration_ids" => $devices,
                "notification" => [
                    'title'     => 'Order Received',
                    'body'      => 'Your order no. #'.$order_number.' has been received!',
                    'sound' => "default",
                    "icon"  => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                    "android_channel_id" => "default-channel-id"
                ],
                "data" => [
                    'title'     => 'Order Received',
                    'body'      => 'Your order no. #'.$order_number.' has been received!',
                    'data'  => 'received_order',
                    'type'  => ""
                ],
                "priority" => "high"
            ];

            $response = sendFcmCurlRequest($data,$client_preferences->fcm_server_key);
            // $result = json_decode($response);
            $result = $response;
            return $result;
        }
    }
}

if (! function_exists('orderProductDetails')) {
    function orderProductDetails($order_id)
    {
        $order = Order::find($order_id);
        $itemsDetails = 'Order No : '.$order->order_number;
        foreach ($order->products as $items) {
            $itemsDetails .=  ', Item Name : '.$items->product_name.', '.$items->product_variant_sets;
        }
        return $itemsDetails;
    }
}



if (! function_exists('EasebuzzSubMerchent')) {
    function EasebuzzSubMerchent()
    {
        $access = 0;
        $payOpt = PaymentOption::select('credentials', 'test_mode', 'status')->where('code', 'easebuzz')->where('status', 1)->first();
        if ($payOpt) {
            $json = json_decode($payOpt->credentials);
            $access = $json->easebuzz_Sub_merchant ;
        }
        return $access;
    }
}

if (!function_exists('pr')) {
    function pr($var) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
         exit();
    }
}
if (!function_exists('http_check')) {
    function http_check($url) {
        $return = $url;
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $return = 'http://' . $url;
        }
        return $return;
    }
}

if (!function_exists('getUserDetailViaApi')) {
    function getUserDetailViaApi($user)
    {
        $user_refferal = UserRefferal::where('user_id', $user->id)->first();
        $client_preference = ClientPreference::select('theme_admin', 'distance_unit', 'map_provider', 'date_format', 'time_format', 'map_key', 'sms_provider', 'verify_email', 'verify_phone', 'app_template_id', 'web_template_id')->first();
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['source'] = $user->image;
        $data['is_admin'] = $user->is_admin;
        $data['dial_code'] = $user->dial_code;
        $data['auth_token'] =  $user->auth_token;
        $data['phone_number'] = $user->phone_number;
        $data['client_preference'] = $client_preference;
        $data['cca2'] = $user->country ? $user->country->code : '';
        $data['callingCode'] = $user->country ? $user->country->phonecode : '';
        $data['refferal_code'] = $user_refferal ? $user_refferal->refferal_code: '';
        $data['verify_details'] = ['is_email_verified' => $user->is_email_verified, 'is_phone_verified' => $user->is_phone_verified];
        if($user->hasRole('Lawyer')){
            $data['is_lawyer_active'] = $user->is_lawyer_active;
        }
        return $data;
    }
}


if (!function_exists('getMonthNumber')) {
    function getMonthNumber($month_name)
    {
        if ($month_name == 'January') {
            return 1;
        } elseif ($month_name == 'February') {
            return 2;
        } elseif ($month_name=='March') {
            return 3;
        } elseif ($month_name=='April') {
            return 4;
        } elseif ($month_name=='May') {
            return 5;
        } elseif ($month_name=='June') {
            return 6;
        } elseif ($month_name=='July') {
            return 7;
        } elseif ($month_name=='August') {
            return 8;
        } elseif ($month_name=='September') {
            return 9;
        } elseif ($month_name=='October') {
            return 10;
        } elseif ($month_name=='November') {
            return 11;
        } elseif ($month_name=='December') {
            return 12;
        }
    }
}


if (!function_exists('generateOrderNo')) {
    function generateOrderNo($length = 8)
    {
        $number = '';
        do {
            for ($i=$length; $i--; $i>0) {
                $number .= mt_rand(0, 9);
            }
        } while (!empty(\DB::table('orders')->where('order_number', $number)->first(['order_number'])));
        return $number;
    }
}

if (!function_exists('generateWalletTransactionReference')) {
    function generateWalletTransactionReference($length = 8)
    {
        $number = '';
        do {
            $number = 'txn_' . md5(uniqid(rand(), true));
        } while (!empty(\DB::table('transactions')->where('meta', 'Like', '%'. $number .'%')->first(['meta'])));
        return $number;
    }
}


if (!function_exists('getNomenclatureName')) {
    function getNomenclatureName($searchTerm, $plural = true)
    {
        // $searchTerm = "Appointment";
        $result = Nomenclature::with(['translations' => function ($q) {
            $q->where('language_id', session()->get('customerLanguage'));
        }])->where('label', 'LIKE', "%{$searchTerm}%")->first();
        if ($result) {
            $searchTerm = $result->translations->count() != 0 ? $result->translations->first()->name : ucfirst($searchTerm);
        }
        // return $plural ? $searchTerm : rtrim($searchTerm, 's');
        return $searchTerm;
    }
}

if (!function_exists('convertDateTimeInTimeZone')) {
    function convertDateTimeInTimeZone($date, $timezone, $format = 'Y-m-d H:i:s')
    {
        $date = Carbon::parse($date, 'UTC');
        $date->setTimezone($timezone);
        return $date->format($format);
    }
}

if (!function_exists('convertDateTimeInClientTimeZone')) {
    function convertDateTimeInClientTimeZone($date,$format = 'Y-m-d H:i:s'){
        $date = Carbon::parse($date, 'UTC');
        $clientTimezone = ClientData::find(1);
        $date->setTimezone($clientTimezone->timezone);
        return $date->format($format);
    }
}

if (!function_exists('limit_text')) {
    function limit_text($text, $limit) {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos   = array_keys($words);
            $text  = substr($text, 0, $pos[$limit]) . '...';
        }
        return $text;
    }
}


if (!function_exists('getClientPreferenceDetail')) {
    function getClientPreferenceDetail()
    {
        $client_preference_detail = ClientPreference::first();
        if ($client_preference_detail) {
            list($r, $g, $b) = sscanf($client_preference_detail->web_color??'#fff', "#%02x%02x%02x");
            $client_preference_detail->wb_color_rgb = "rgb(".$r.", ".$g.", ".$b.")";
        }
        return $client_preference_detail;
    }
}

if (!function_exists('getClientDetail')) {
    function getClientDetail()
    {
        $clientData = ClientData::first();
        $clientData->logo_image_url = $clientData ? $clientData->logo['original'] : ' ';
        return $clientData;
    }
}

if (!function_exists('getRazorPayApiKey')) {
    function getRazorPayApiKey()
    {
        $razorpay_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'razorpay')->where('status', 1)->first();
        $api_key_razorpay = "";
        if ($razorpay_creds) {
            $creds_arr_razorpay = json_decode($razorpay_creds->credentials);
            $api_key_razorpay = (isset($creds_arr_razorpay->api_key)) ? $creds_arr_razorpay->api_key : '';
        }
        return $api_key_razorpay;
    }
}

if (!function_exists('getKhaltiPayApiKey')) {
    function getKhaltiPayApiKey()
    {
        $khaltipay_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'khalti')->where('status', 1)->first();
        $api_key_khaltipay = "";
        if($khaltipay_creds)
        {
            $creds_arr_khaltipay = json_decode($khaltipay_creds->credentials);
            $api_key_khaltipay = (isset($creds_arr_khaltipay->api_key)) ? $creds_arr_khaltipay->api_key : '';
        }
        return $api_key_khaltipay;
    }
}

if (!function_exists('dateTimeInUserTimeZone')) {
    function dateTimeInUserTimeZone($date, $timezone, $showDate=true, $showTime=true, $showSeconds=false)
    {
        $preferences = ClientPreference::select('date_format', 'time_format')->where('id', '>', 0)->first();
        $date_format = (!empty($preferences->date_format)) ? $preferences->date_format : 'YYYY-MM-DD';
        if ($date_format == 'DD/MM/YYYY') {
            $date_format = 'DD-MM-YYYY';
        }
        $time_format = (!empty($preferences->time_format)) ? $preferences->time_format : '24';
        $date = Carbon::parse($date, 'UTC');
        $date->setTimezone($timezone);
        $secondsKey = '';
        $timeFormat = '';
        $dateFormat = '';
        if ($showDate) {
            $dateFormat = $date_format;
        }
        if ($showTime) {
            if ($showSeconds) {
                $secondsKey = ':ss';
            }
            if ($time_format == '12') {
                $timeFormat = ' hh:mm'.$secondsKey.' A';
            } else {
                $timeFormat = ' HH:mm'.$secondsKey;
            }
        }

        $format = $dateFormat . $timeFormat;
        return $date->isoFormat($format);
    }
}


if (!function_exists('dateTimeInUserTimeZone24')) {
    function dateTimeInUserTimeZone24($date, $timezone, $showDate=true, $showTime=true, $showSeconds=false)
    {
        $preferences = ClientPreference::select('date_format', 'time_format')->where('id', '>', 0)->first();
        $date_format = (!empty($preferences->date_format)) ? $preferences->date_format : 'YYYY-MM-DD';
        if ($date_format == 'DD/MM/YYYY') {
            $date_format = 'DD-MM-YYYY';
        }
        $time_format = (!empty($preferences->time_format)) ? $preferences->time_format : '24';
        $date = Carbon::parse($date, 'UTC');
        $date->setTimezone($timezone);
        $secondsKey = '';
        $timeFormat = '';
        $dateFormat = '';
        if ($showDate) {
            $dateFormat = $date_format;
        }
        if ($showTime) {
            $timeFormat = 'HH:mm:ss';
        }
        $format = $dateFormat . $timeFormat;
        return $date->isoFormat($format);
    }
}



if (!function_exists('productvariantQuantity')) {
    function productvariantQuantity($variantId, $type=1)
    {
        if ($type==1) {
            $ProductVariant =  ProductVariant::where('id', $variantId)
        ->select('quantity')->first();
        } else {
            $ProductVariant =  ProductVariant::where('sku', $variantId)
        ->select('quantity')->first();
        }
        if ($ProductVariant) {
            return  $ProductVariant->quantity;
        }
        return "variant not found";
    }
}


if (!function_exists('checkImageExtension')) {
    function checkImageExtension($image)
    {
        $ex = "@webp";
        if(!empty($image))
        {
            $ch =  substr($image, strpos($image, ".") + 1);
            if ($ch == 'svg') {
                $ex = "";
            }
        }
            return $ex;
    }
}


if (!function_exists('getDefaultImagePath')) {
    function getDefaultImagePath()
    {
        $values = array();
        $img = 'default/default_image.png';
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).'@webp';
        $values['image_fit'] = \Config::get('app.FIT_URl');
        return $values;
    }
}
if (!function_exists('loadDefaultImage')) {
    function loadDefaultImage(){
        $proxy_url = \Config::get('app.IMG_URL1');
        $image_path = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url('default/default_image.png');
        $image_fit = \Config::get('app.FIT_URl');
        $default_url = $image_fit .'300/300'. $image_path.'@webp';

        if (imageExists($default_url)) {
            return $default_url;
        } else {
            return asset('assets/images/bg-material.png');

        }
    }
}


if (!function_exists('imageExists')) {

     function imageExists($url) {
        // You can use either File or Storage to check if the image exists.
        // Here, I'm using the File class.
        return \File::exists(public_path($url));
    }
}

if (!function_exists('imageExistsS3')) {
    function imageExistsS3($url)
    {
        $headers = @get_headers($url);
        if ($headers && strpos($headers[0], '200')) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('getImageUrl')) {
    function getImageUrl($image, $dim)
    {
        $server = env('APP_ENV', 'development');
        if ($server == 'local') {
            return $image;
        }
        return \Config::get('app.FIT_URl').$dim.\Config::get('app.IMG_URL2').'/'.$image.'@webp';
    }
}

if (!function_exists('getUserIP')) {
    function getUserIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }
}


if (!function_exists('createSlug')) {
    function createSlug($str, $delimiter = '-')
    {
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }
}

if (!function_exists('remove_special_chars')) {
    function remove_special_chars($str, $delimiter = '')
    {
        // $result = strtolower(trim(preg_replace('/[^A-Za-z0-9\-]/', $delimiter, $str)));
        $result = strtolower(trim(preg_replace('/[.*+?^${}()\/|[\]\\/]+/', $delimiter, $str)));
        return $result;
    }
}


if (!function_exists('getBaseprice')) {
    function getBaseprice($dist, $option = 'lalamove')
    {
        $simp_creds = ShippingOption::select('credentials', 'test_mode', 'status')->where('code', $option)->where('status', 1)->first();
        if ($simp_creds && $simp_creds->credentials) {
            $creds_arr = json_decode($simp_creds->credentials);
            $base_price = $creds_arr->base_price??'0';
            if ($base_price>0) {
                $distance = $creds_arr->distance??'0';
                $amount_per_km = $creds_arr->amount_per_km??'0';
            }
            $lalamove_status = $simp_creds->status??'';
        }
        $distance = $dist;
        if ($distance < 1 || $base_price < 1) {
            return 0;
        }

        $base_price = $base_price;
        $amount_per_km = $amount_per_km;
        $total = $base_price + ($distance * $amount_per_km);
        return  $total;
        // + ($paid_duration * $pricingRule->duration_price);
    }
}

if (!function_exists('SplitTime')) {
    function SplitTime($myDate, $StartTime, $EndTime, $Duration="60", $delayMin = 0)
    {
        $Duration = (($Duration==0)?'60':$Duration);

        $user = Auth::user();
        if (isset($user->timezone) && !empty($user->timezone)) {
            $timezoneset = $user->timezone;
        } else {
            $client = ClientData::orderBy('id', 'desc')->select('id', 'timezone')->first();

            if (isset($client->timezone) && !empty($client->timezone)) {
                $timezoneset = $client->timezone;
            } else {
                $timezoneset = 'Asia/Kolkata';
            }
        }
        $cr = Carbon::now()->addMinutes($delayMin);
        $now = dateTimeInUserTimeZone24($cr, $timezoneset);
        $nowT = strtotime($now);
        $nowA = Carbon::createFromFormat('Y-m-d H:i:s', $myDate.' '.$StartTime);
        $nowS = Carbon::createFromFormat('Y-m-d H:i:s', $nowA)->timestamp;
        $nowE = Carbon::createFromFormat('Y-m-d H:i:s', $myDate.' '.$EndTime)->timestamp;
        // dd($nowT);
        // if ($nowT > $nowE) {
        //     return [];
        // } else {
        //     $StartTime = date('H:i', strtotime($nowA));
        // }
        $ReturnArray = array();
        $StartTime = strtotime($StartTime); //Get Timestamp
        $EndTime = strtotime($EndTime); //Get Timestamp
        $AddMins = $Duration * 60;
        $endtm = 0;
        while ($StartTime <= $EndTime) {
            $endtm = $StartTime + $AddMins;
            if ($endtm>$EndTime) {
                $endtm = $EndTime;
            }
            if ($nowT>$nowS && $StartTime > $nowT){//Condition to get slots from next available time on current datetime according to start time set while creating slots in vendor configuration
                $ReturnArray[] = date("G:i", $StartTime).' - '.date("G:i", $endtm);
            }
            if($nowT <= $nowS){//Condition to get slots from next available time on other than current datetime according to start time set while creating slots in vendor configuration
                $ReturnArray[] = date("G:i", $StartTime).' - '.date("G:i", $endtm);
            }

            $StartTime += $AddMins;
            $endtm = 0;
        }
        return $ReturnArray;
    }
}

if (!function_exists('showSlot')) {
    function showSlot($myDate = null, $vid, $type = 'delivery', $duration="60", $slot_type=0, $request_from='',$cart_id = 0)
    {
        $type = empty($type)? "delivery": $type;
        $slotDuration = Vendor::select('slot_minutes')->where('id', $vid)->first();
        $duration = ($slotDuration->slot_minutes) ?? $duration;
        $type = ((session()->get('vendorType'))?session()->get('vendorType'):$type);
        //type must be a : delivery , takeaway,dine_in
        $client = ClientData::select('timezone')->first();
        $preferences = ClientPreference::select('scheduling_with_slots', 'business_type')->first();
        $viewSlot = array();
        if (!empty($myDate)) {
            $mytime = Carbon::createFromFormat('Y-m-d', $myDate)->setTimezone($client->timezone);
        } else {
            $myDate = date('Y-m-d');
            $mytime = Carbon::createFromFormat('Y-m-d', $myDate)->setTimezone($client->timezone);
        }
        $slots = [];
        $mytime =$mytime->dayOfWeek+1;
        if ($preferences->scheduling_with_slots == 1 && $preferences->business_type == 'laundry') {
            $slots = VendorSlot::where('vendor_id', $vid)->where('slot_type', $slot_type)->whereHas('days', function ($q) use ($mytime) {
                return $q->where('day', $mytime)->where('laundry', '1');
            })->get();
        } else {
            if(!empty($type) && $type == 'car_rental'){
                $type ='rental';
            }
            $slots = VendorSlot::where('vendor_id', $vid)
                    ->whereHas('days', function ($q) use ($mytime, $type) {
                        return $q->where('day', $mytime)->where($type, '1');
                    })
                    ->get();
        }

        // check if vendor has added slots. if not added then no need to execute this.
        if (isset($slots) && count($slots)>0) {
            $min[] = '';
            $cart = CartProduct::where('vendor_id', $vid);
            if(!empty($cart_id)){
                $cart->where('cart_id',$cart_id);
            }
            $cart = $cart->get();
            if (isset($cart) && $cart->count()>0) {
                foreach ($cart as $product) {
                    $delayHr= isset($product->product->delay_order_hrs) ? ($product->product->delay_order_hrs) : 0;
                    $delayMin= isset($product->product->delay_order_min) ? ($product->product->delay_order_min) : 0;
                    $min[] = (($delayHr * 60) + $delayMin);
                }
            }
            if (isset($slots) && count($slots)>0) {
                $slotss = [];
                foreach ($slots as $slott) {
                    if (isset($slott->days->id)) {
                        $new_slot = SplitTime($myDate, $slott->start_time, $slott->end_time, $duration, max($min));
                        if (!in_array($new_slot, $slotss)) {
                            $slotss[] = $new_slot;
                        }

                    } else {
                        $slotss[] = [];
                    }
                }


                $arr = array();
                $count = count($slotss);
                for ($i=0;$i<$count;$i++) {
                    $arr = array_merge($arr, $slotss[$i]);
                }

                if (isset($arr)) {
                    foreach ($arr as $k=> $slt) {
                        $sl = explode(' - ', $slt);
                        $viewSlot[$k]['name'] = date('h:i:A', strtotime($sl[0])).' - '.date('h:i:A', strtotime($sl[1]));
                        $viewSlot[$k]['value'] = $slt;
                    }
                }
            }
        }

        return $viewSlot;
    }
}

if (!function_exists('showPriceWithCurrency')) {
function showPriceWithCurrency($price = 0,$compare = 0)
    {
        setUserCode();
        $redis = Redis::connection();
        $multiply =  session()->get('currencyMultiplier') ?? 1;
        $currencysymbol = session()->get('currencySymbol').' ';
        $is_token_currency = $redis->get("ifTCurrency_".session()->get('userCode'));
        if($is_token_currency == null){
            $is_token_currency = getAdditionalPreference(['is_token_currency_enable'])['is_token_currency_enable'];
            $redis->set("ifTCurrency_".session()->get('userCode'), $is_token_currency, 'EX', 36000);
        }
        $is_token_currency = 0;
        if($is_token_currency == 1)
        {
            $currencysymbol = "<i class='fa fa-money' aria-hidden='true'></i> ";
            $amount =  getInToken($price * $multiply);
        }else{
            $amount =  decimal_format($price * $multiply);
        }

        //check to compare price greater > 0 return
        if($compare>0)
        {
            if($price>0){
                return '<del class="ml-2 compare_at_price">'.$currencysymbol.$amount.'</del>';
                }else{
                return '';
            }
        }

        return $currencysymbol.$amount;
    }
}

if (!function_exists('showNumericPrice')) {
    function showNumericPrice($price = 0)
        {
                $multiply =  session()->get('currencyMultiplier') ?? 1;
                $additionalPreference = getAdditionalPreference(['is_token_currency_enable']);
                if($additionalPreference['is_token_currency_enable'] == 1)
                {
                    //$currencysymbol = "<i class='fa fa-money' aria-hidden='true'></i> ";
                    $amount =  getInToken($price * $multiply);
                }else{
                    $amount =  decimal_format($price * $multiply);
                }

                return $amount??0;
        }
    }

if (!function_exists('getShowSlot')) {
    function getShowSlot($myDate = null, $vid, $type = 'delivery', $duration="60", $slot_type=0, $request_from='',$cart_id = 0)
    {
        $slots = (object)showSlot($myDate,$vid,$type,$duration, $slot_type,'', $cart_id);
        if(count((array)$slots) == 0){
            $myDate  = date('Y-m-d',strtotime('+1 day'));
            $slots = (object)showSlot($myDate,$vid,$type,$duration, $slot_type,'', $cart_id);
        }
        if(count((array)$slots) == 0){
            $myDate  = date('Y-m-d',strtotime('+2 day'));
            $slots = (object)showSlot($myDate,$vid,$type,$duration, $slot_type,'', $cart_id);
        }

        if(count((array)$slots) == 0){
            $myDate  = date('Y-m-d',strtotime('+3 day'));
            $slots = (object)showSlot($myDate,$vid,$type,$duration, $slot_type,'', $cart_id);
        }
        $response['slots']=$slots;
        $response['date']=$myDate;
        return  $response;
    }
}
if (!function_exists('showSlotTemp')) {
    function showSlotTemp($myDate = null, $vid, $user_id, $type = 'delivery', $duration="60")
    {
        $slotDuration = Vendor::select('slot_minutes')->where('id', $vid)->first();
        $duration = ($slotDuration->slot_minutes) ?? $duration;

        //type must be a : delivery , takeaway,dine_in
        $client = ClientData::select('timezone')->first();
        $viewSlot = array();
        if (!empty($myDate)) {
            $mytime = Carbon::createFromFormat('Y-m-d', $myDate)->setTimezone($client->timezone);
        } else {
            $myDate = date('Y-m-d');
            $mytime = Carbon::createFromFormat('Y-m-d', $myDate)->setTimezone($client->timezone);
        }
        $mytime =$mytime->dayOfWeek+1;
        $slots = VendorSlot::where('vendor_id', $vid)
    ->whereHas('days', function ($q) use ($mytime, $type) {
        return $q->where('day', $mytime)->where($type, '1');
    })
    ->get();
        $min[] = '';
        $cart = TempCartProduct::where('vendor_id', $vid)->get();
        if (isset($cart) && $cart->count()>0) {
            foreach ($cart as $product) {
                $delayHr= isset($product->product->delay_order_hrs) ? ($product->product->delay_order_hrs) : 0;
                $delayMin= isset($product->product->delay_order_min) ? ($product->product->delay_order_min) : 0;
                $min[] = (($delayHr * 60) + $delayMin);
            }
        }

        if (isset($slots) && count($slots)>0) {
            foreach ($slots as $slott) {
                if (isset($slott->days->id)) {
                    $slotss[] = SplitTime($myDate, $slott->start_time, $slott->end_time, $duration, max($min));
                } else {
                    $slotss[] = [];
                }
            }

            $arr = array();
            $count = count($slotss);
            for ($i=0;$i<$count;$i++) {
                $arr = array_merge($arr, $slotss[$i]);
            }

            if (isset($arr)) {
                foreach ($arr as $k=> $slt) {
                    $sl = explode(' - ', $slt);
                    $viewSlot[$k]['name'] = date('h:i:A', strtotime($sl[0])).' - '.date('h:i:A', strtotime($sl[1]));
                    $viewSlot[$k]['value'] = $slt;
                }
            }
        }

        return $viewSlot;
    }
}


if (!function_exists('SplitTimeTemp')) {
    function SplitTimeTemp($user_id, $myDate, $StartTime, $EndTime, $Duration="60", $delayMin = 0)
    {
        $Duration = (($Duration==0)?'60':$Duration);

        $user = Auth::user();
        if (isset($user->timezone) && !empty($user->timezone)) {
            $timezoneset = $user->timezone;
        } else {
            $client = ClientData::orderBy('id', 'desc')->select('id', 'timezone')->first();

            if (isset($client->timezone) && !empty($client->timezone)) {
                $timezoneset = $client->timezone;
            } else {
                $timezoneset = 'Asia/Kolkata';
            }
        }

        $cr   = Carbon::now()->addMinutes($delayMin);
        $now  = dateTimeInUserTimeZone24($cr, $timezoneset);
        $nowT = strtotime($now);
        $nowA = Carbon::createFromFormat('Y-m-d H:i:s', $myDate.' '.$StartTime);
        $nowS = Carbon::createFromFormat('Y-m-d H:i:s', $nowA)->timestamp;
        $nowE = Carbon::createFromFormat('Y-m-d H:i:s', $myDate.' '.$EndTime)->timestamp;
        if ($nowT > $nowE) {
            return [];
        /* } elseif ($nowT>$nowS) {
            $StartTime = date('H:i', strtotime($now)); */
        } else {
            $StartTime = date('H:i', strtotime($nowA));
        }

        $ReturnArray = array();
        $StartTime = strtotime($StartTime); //Get Timestamp
    $EndTime = strtotime($EndTime); //Get Timestamp
    $AddMins = $Duration * 60;
        $endtm = 0;

        while ($StartTime <= $EndTime) {
            $endtm = $StartTime + $AddMins;
            if ($endtm>$EndTime) {
                $endtm = $EndTime;
            }

            if ($nowT>$nowS && $StartTime > $nowT){//Condition to get slots from next available time on current datetime according to start time set while creating slots in vendor configuration
                $ReturnArray[] = date("G:i", $StartTime).' - '.date("G:i", $endtm);
            }
            if($nowT <= $nowS){//Condition to get slots from next available time on other than current datetime according to start time set while creating slots in vendor configuration
                $ReturnArray[] = date("G:i", $StartTime).' - '.date("G:i", $endtm);
            }
            $StartTime += $AddMins+60;
            $endtm = 0;
        }
        return $ReturnArray;
    }
}


if (!function_exists('findSlot')) {
    function findSlot($myDate = null, $vid, $type = 'delivery', $api = null,$cart_id = 0)
    {
        $type = empty($type) ? 'delivery' :$type;
        $myDate  = date('Y-m-d');
        $type = ((session()->get('vendorType'))?session()->get('vendorType'):$type);
        $slots = showSlot($myDate, $vid,  $type,"60",0,'',$cart_id);

        if (count((array)$slots) == 0) {
            $myDate  = date('Y-m-d', strtotime('+1 day'));
            $slots = showSlot($myDate, $vid, $type,"60",0,'',$cart_id);
        }

        if (count((array)$slots) == 0) {
            $myDate  = date('Y-m-d', strtotime('+2 day'));
            $slots = showSlot($myDate, $vid, $type,"60",0,'',$cart_id);
        }

        if (count((array)$slots) == 0) {
            $myDate  = date('Y-m-d', strtotime('+3 day'));
            $slots = showSlot($myDate, $vid, $type,"60",0,'',$cart_id);
        }
        if (isset($slots) && count((array)$slots)>0) {
            $time = explode(' - ', $slots[0]['value']);

            if ($api != 'api') {
                if($api == 'webFormet'){ // webFormet for geting date and time
                    return ['date'=>$myDate,
                            'time'=>$time[0],
                            'datetime'=>date('d M, Y h:i:A', strtotime($myDate.'T'.$time[0]))
                        ];
                }
                return date('d M, Y h:i:A', strtotime($myDate.'T'.$time[0]));
            } else {
                return date('Y-m-d', strtotime($myDate.'T'.$time[0]));
            }
        } else {
            return 0;
        }
    }
}
if (!function_exists('findSlotNew')) {
    function findSlotNew($myDate,$vid,$type = 'delivery', $duration = 0)
    {
            $slots = showSlot($myDate,$vid,$type, $duration);
                if(count((array)$slots) == 0){
                    $myDate  = date('Y-m-d',strtotime('+1 day'));
                    $slots = showSlot($myDate,$vid,$type, $duration);
                }

                if(count((array)$slots) == 0){
                    $myDate  = date('Y-m-d',strtotime('+2 day'));
                    $slots = showSlot($myDate,$vid,$type, $duration);
                }

                if(count((array)$slots) == 0){
                    $myDate  = date('Y-m-d',strtotime('+3 day'));
                    $slots = showSlot($myDate,$vid,$type, $duration);
                }
                if(isset($slots)){
                    $slots = $slots;
                    return array('mydate'=>$myDate,'slots'=>$slots);
                }else{
                    return array('mydate'=>'','slots'=>[]);
                }
    }
}
if (!function_exists('GoogleDistanceMatrix')) {
    function GoogleDistanceMatrix($latitude, $longitude)
    {
        $send   = [];
        $client = ClientPreference::select('map_key', 'distance_unit')->where('id', 1)->first();
        $lengths = count($latitude) - 1;
        $value = [];

        for ($i = 1; $i<=$lengths; $i++) {
            $count  = 0;
            $count1 = 1;
            $ch = curl_init();
            $headers = array('Accept: application/json',
                    'Content-Type: application/json',
                    );
            $url =  'https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$latitude[$count].','.$longitude[$count].'&destinations='.$latitude[$count1].','.$longitude[$count1].'&key='.$client->map_key.'';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $result = json_decode($response);
            curl_close($ch); // Close the connection
            $new =   $result;
        // dd($result);
            array_push($value, $result->rows[0]->elements);
            $count++;
            $count1++;
        }

        if (isset($value)) {
            $totalDistance = 0;
            $totalDuration = 0;
            foreach ($value as $item) {
                //dd($item);
                $totalDistance = $totalDistance + $item[0]->distance->value;
                $totalDuration = $totalDuration + $item[0]->duration->value;
            }


            if ($client->distance_unit == 'metric') {
                $send['distance'] = round($totalDistance/1000, 2);      //km
            } else {
                $send['distance'] = round($totalDistance/1609.34, 2);  //mile
            }
            //
            $newvalue = round($totalDuration/60, 2);
            $whole = floor($newvalue);
            $fraction = $newvalue - $whole;

            if ($fraction >= 0.60) {
                $send['duration'] = $whole + 1;
            } else {
                $send['duration'] = $whole;
            }
        }
        return $send;
    }
}
if (!function_exists('getDynamicMail')) {
    function getDynamicMail()
    {
        $data = ClientPreference::select('mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
        $config = array(
            'driver' => $data->mail_driver,
            'host' => $data->mail_host,
            'port' => $data->mail_port,
            'from'       => array('address' => $data->mail_from, 'name' => $data->mail_from),
            'encryption' => $data->mail_encryption,
            'username' => $data->mail_username,
            'password' => $data->mail_password,
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
        );
        \Config::set('mail.mailers.smtp', $config);
        return 2;
    }
}
if (!function_exists('getDynamicTypeName')) {
    function getDynamicTypeName($name)
    {
        $new_name = getNomenclatureName($name, true);
        $new_name = ($new_name === $name) ? __($name) : $new_name;
        return $new_name;
    }
}

if (!function_exists('stripePaymentCredentials')) {
    function stripePaymentCredentials(){
        $stripe_creds = PaymentOption::select('credentials')->where('code', 'stripe')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $response = collect();
        $response->secret_key = (isset($creds_arr->api_key)) ? $creds_arr->api_key : '';
        $response->publishable_key = (isset($creds_arr->publishable_key)) ? $creds_arr->publishable_key : '';
        return $response;
    }
}

if (!function_exists('stripeFPXPaymentCredentials')) {
    function stripeFPXPaymentCredentials(){
        $stripe_creds = PaymentOption::select('credentials')->where('code', 'stripe_fpx')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $response = collect();
        $response->secret_key = (isset($creds_arr->secret_key)) ? $creds_arr->secret_key : '';
        $response->publishable_key = (isset($creds_arr->publishable_key)) ? $creds_arr->publishable_key : '';
        return $response;
    }
}


if (!function_exists('stripeOXXOPaymentCredentials')) {
    function stripeOXXOPaymentCredentials(){
        $stripe_creds = PaymentOption::select('credentials')->where('code', 'stripe_oxxo')->where('status', 1)->first();
        $creds_arr = json_decode($stripe_creds->credentials);
        $response = collect();
        $response->secret_key = (isset($creds_arr->secret_key)) ? $creds_arr->secret_key : '';
        $response->publishable_key = (isset($creds_arr->publishable_key)) ? $creds_arr->publishable_key : '';
        return $response;
    }
}


if (!function_exists('stripeDynamicPaymentCredentials')) {
    function stripeDynamicPaymentCredentials($name){
        $stripe_creds = PaymentOption::select('credentials')->where('code', $name)->where('status', 1)->first();
        $creds_arr = @json_decode(@$stripe_creds->credentials);
        $response = collect();
        $response->secret_key = (isset($creds_arr->secret_key)) ? $creds_arr->secret_key : '';
        $response->publishable_key = (isset($creds_arr->publishable_key)) ? $creds_arr->publishable_key : '';
        return $response;
    }
}

if (!function_exists('convertDateToHumanReadable')) {
    function convertDateToHumanReadable($date){
        return Carbon::parse($date)->diffForHumans();
    }
}


if (!function_exists('OnLAstMileDelivery')) {
    function OnLAstMileDelivery()
    {
        $count = ShippingOption::where('status',1)->count();
        return $count;
    }
}


if (!function_exists('getServerURL')) {
    function getServerURL(){
        $client = ClientData::where('id', '>', 0)->first();
        $domain = '';
        if(!empty($client->custom_domain)){
            $domain = $client->custom_domain;
        }else{
            $domain = $client->sub_domain.env('SUBMAINDOMAIN');
        }
        $server_url = "https://".$domain."/";
        return $server_url;
    }
}


if (!function_exists('getDollarCompareAmount')) {
/* doller compare amount */
    function getDollarCompareAmount($amount, $customerCurrency='')
    {
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        if(empty($customerCurrency)){
            $clientCurrency = $primaryCurrency;
        }else{
            $clientCurrency = ClientCurrency::where('currency_id', $customerCurrency)->first();
        }
        $divider = (empty($clientCurrency->doller_compare) || $clientCurrency->doller_compare < 0) ? 1 : $clientCurrency->doller_compare;
        $amount = ($amount / $divider) * $primaryCurrency->doller_compare;
        $amount = number_format($amount, 2,'.','');
        return $amount;
    }
}

if (!function_exists('getPrimaryCurrencySymbol')) {
    /* doller compare amount */
    function getPrimaryCurrencySymbol()
    {
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $currency = Currency::find($primaryCurrency->currency_id);

        $currencySymbol = $currency->symbol;
        return $currencySymbol;
    }
}

if (!function_exists('getPrimaryCurrencyName')) {
    /* doller compare amount */
    function getPrimaryCurrencyName()
    {
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        $currencyName = Currency::find($primaryCurrency->currency_id);

        $currencyName = $currencyName->iso_code;
        return $currencyName;
    }
}


if (!function_exists('getPrimaryLanguageName')) {
    function getPrimaryLanguageName()
    {
        $primaryLanguage = ClientLanguage::where('is_primary', '=', 1)->first();
        $primaryLanguageName = Language::find($primaryLanguage->language_id);
        $languageName = $primaryLanguageName->sort_code;
        return $languageName;
    }
}

if (!function_exists('decimal_format')) {
    // Number Format according to Client preferences
    function decimal_format($number,$format="")
    {
        $number = is_numeric($number)?$number:0;
        $preference = session()->get('preferences');
        $digits = $preference['digit_after_decimal'] ?? 2;
        return number_format($number,$digits,'.',$format);
    }
}


if (!function_exists('taxRates')) {
    function taxRates(){
        return App\Models\TaxRate::all();
    }
}

if (!function_exists('getRoleId')) {
    function getRoleId($name){
        if($name){
            return  \Spatie\Permission\Models\Role::where('name',$name)->value('id');
        }else{
            return null;
        }
    }
}



if (!function_exists('getServiceTypesCategory')) {
    /**
     * config('constants.ServiceTypes')
     */
    function getServiceTypesCategory($vendorType, $client_preference = NULL) {
        try {
            if($client_preference ==NULL){
                $client_preference = ClientPreference::select('business_type', 'p2p_check')->first();
            }



            $types =   Type::query();

            $service_types = [];

            $alltypes = [
                'delivery'     => ['products_service'],
                'dine_in'      => ['products_service'],
                'takeaway'     => ['products_service'],
                'rental'       => ['rental_service'],
                'pick_drop'    => ['pick_drop_service'],
                'on_demand'    => ['on_demand_service'],
                'laundry'      => ['laundry_service'],
                'appointment'  => ['appointment_service'],
                'taxi'         => ['pick_drop_service'],
                'p2p'          => ['p2p'],
                'home_service' => ['on_demand_service', 'appointment_service'],
                'car_rental'   => ['car_rental'],
                // 'car_rental'   => ['rental_services'],
            ];
            $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
            if(@$getAdditionalPreference['is_rental_weekly_monthly_price']){

                $alltypes['p2p'] = ['p2p', 'rental_service'];
            }

            if ($vendorType == 'delivery' || $vendorType == 'dine_in' || $vendorType == 'takeaway' || $vendorType == 'rental' || $vendorType == 'pick_drop' || $vendorType == 'on_demand' || $vendorType == 'laundry' || $vendorType == 'appointment' || $vendorType == 'p2p' || $vendorType == 'car_rental') {
                $service_types = $alltypes[$vendorType];
            }

            if ($client_preference->business_type == 'taxi' || $client_preference->business_type == 'laundry' || $client_preference->business_type == 'home_service' || $client_preference->business_type == 'p2p') {
                $service_types = $alltypes[$client_preference->business_type];
            }
            $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
            if($client_preference->business_type == 'p2p' && @$getAdditionalPreference['is_rental_weekly_monthly_price']){
                $service_types = $alltypes['p2p'];
            }

            /* if ($vendorType == "delivery" || $vendorType == "dine_in" || $vendorType == "takeaway") {
                $service_types = ['products_service'];
            } elseif ($vendorType == "rental") {
                $service_types = ['rental_service'];
            } elseif ($vendorType == "pick_drop") {
                $service_types = ['pick_drop_service'];
            } elseif ($vendorType == "on_demand") {
                $service_types = ['on_demand_service'];
            } elseif ($vendorType == "laundry") {
                $service_types = ['laundry_service'];
            } elseif ($vendorType == "appointment") {
                $service_types = ['appointment_service'];
            }

            elseif ($vendorType == "p2p") {
                $service_types = ['p2p'];
            }*/

            /* if ($client_preference->business_type == 'taxi') {
                $service_types = ['pick_drop_service'];
            } elseif ($client_preference->business_type == 'laundry') {
                $service_types = ['laundry_service'];
            } elseif ($client_preference->business_type == 'home_service') {
                $service_types = ['on_demand_service', 'appointment_service'];
            }
            if($client_preference->business_type == 'laundry'){
                $service_types= ['laundry_service'];
            }
            // if ($client_preference->business_type == 'p2p') {
            //     $service_types = ['products_service'];
            // }
            if ($client_preference->business_type == 'p2p') {
                $service_types = ['p2p'];
            } */
            $types =  $types->whereIn('service_type', $service_types);

            $types_id = $types->pluck('id')->toArray();

            return $types_id ;
        } catch (\Throwable $th) {
           return [];
        }

    }
}

if (!function_exists('getCategoryTypes')) {
    /**
     * config('constants.ServiceTypes')
     */
    function getCategoryTypes() {
        $client_preference = ClientPreference::select('business_type')->first();
        switch($client_preference->business_type){
            case "taxi":
                $typeArray =['pick_drop'];
            break;
            case "food_grocery_ecommerce":
                $typeArray =['delivery','dinein','takeaway'];
            break;
            case "home_service":
                $typeArray =['on_demand','appointment'];
            break;
            case "laundry":
                $typeArray =['laundry'];
            break;
            case "rental":
                $typeArray = ['rental','car_rental'];
                break;
            case "p2p":
                $typeArray = ['p2p'];
                break;
            case "emart":
                $typeArray = ['delivery'];
                break;
            case "super_app":
                $typeArray = ['delivery', 'dinein', 'takeaway', 'rental', 'pick_drop', 'on_demand', 'appointment', 'p2p','car_rental' ];
                break;
            default:
            $typeArray =['delivery','dinein','takeaway','pick_drop','on_demand','appointment'];
        }
        return $typeArray;
    }
}

if (!function_exists('getCategoryTypesServices')) {
    /**
     * config('constants.ServiceTypes')
     */
    function getCategoryTypesServices() {

        $client_preference = ClientPreference::select('business_type')->first();

        switch($client_preference->business_type){
            case "taxi":
                $typeArray =['pick_drop_service'];
            break;
            case "food_grocery_ecommerce":
                $typeArray =['products_service'];
            break;
            case "home_service":
                $typeArray =['on_demand_service','appointment_service'];
            break;
            case "laundry":
                $typeArray =['laundry_service','pick_drop_service'];
            break;
            case "rental":
                $typeArray = ['rental_service'];
                break;
            case "p2p":
                $typeArray = ['p2p'];
                break;
            case "super_app":
                $typeArray = ['pick_drop_service', 'on_demand_service', 'appointment_service', 'rental_service', 'products_service', 'p2p','car_rental'];

                break;
            default:
            $typeArray =['products_service','pick_drop_service','on_demand_service','appointment_service'];
        }
        return $typeArray;
    }
}

if (!function_exists('getHoursMinutes')) {
    /**
     * config('constants.ServiceTypes')
     */
    function getHoursMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $min = $minutes - ($hours * 60);
        return $hours.' hour ' .$min. ' min ';

    }
}


if (!function_exists('getMinutes')) {
    /**
     * config('constants.ServiceTypes')
     */
    function getMinutes($hrs,$minutes)
    {
        $minutes = ($hrs*60)+($minutes);
        return $minutes;

    }
}

// Returns the values of the additional preferences.
if (!function_exists('checkTableExists')) {
    /** check if column exits in table
    * @param string $tableName
    * @return boolean true or false
    */
    function checkTableExists($tableName){
        if (Schema::hasTable($tableName)){
            return true;
        }else{
            return false;
        }
    }
}

if (!function_exists('sendSmsTemplate')) {
    /**
     * sendSmsTemplate dynamic selection and replace tags
     */
    function sendSmsTemplate($slug,$data)
    {
        $smsTemp = SmsTemplate::where('slug',$slug)->select('content','tags','template_id')->first();
        $smsBody = $smsTemp->content ?? '';
        if(isset($smsTemp->tags) && !empty($smsTemp->tags))
        {
            $tages = explode(',',$smsTemp->tags);
            foreach($tages as $tag)
            {
                $value = $data[$tag]??'';
                $smsBody = str_replace($tag,$value,$smsBody);
            }
        }
        $sms = array('body'=>$smsBody,'template_id'=>$smsTemp->template_id??'');
        return $sms;
    }
}



if (!function_exists('inventorySyncOnOff')) {
    function inventorySyncOnOff($vendor_id)
    {
        if (!empty($vendor_id)) {
            $client_preferences = ClientPreference::first();

            $client = new \GuzzleHttp\Client([
                'headers' => [
                    'shortcode' => $client_preferences->inventory_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);
            $url = $client_preferences->inventory_service_key_url;

            $request = $client->get($url . '/api/v1/sync-status', [
                'json' => ['royo_vendor_id' => $vendor_id]
            ]);

            $response = json_decode($request->getBody());
            if ($response->status) {
                return $response->msg;
            }
        } else {
            return false;
        }
    }
}
// Returns the values of the additional preferences.
if (!function_exists('checkTableExists')) {
    /** check if column exits in table
    * @param string $tableName
    * @return boolean true or false
    */
    function checkTableExists($tableName){
        if (Schema::hasTable($tableName)){
            return true;
        }else{
            return false;
        }
    }
}
if (!function_exists('inventorySyncOnOff')) {
    function inventorySyncOnOff($vendor_id, $client_preferences)
    {
        if (!empty($vendor_id))
        {
            $client = new \GuzzleHttp\Client([
                'headers' => [
                    'shortcode' => $client_preferences->inventory_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);
            $url = $client_preferences->inventory_service_key_url;

            $request = $client->get($url . '/api/v1/sync-status', [
                'json' => ['royo_vendor_id' => $vendor_id]
            ]);

            $response = json_decode($request->getBody());

            if ($response->status) {
                return $response->msg;
            }
            return false;
        } else {
            return false;
        }
    }
}

if( !function_exists('clientPrefrenceModuleStatus') ) {
    function clientPrefrenceModuleStatus($module_name) {
            return ClientPreference::select($module_name)->first()->value($module_name);

    }
}

if( !function_exists('p2p_module_status') ) {
    function p2p_module_status() {
        $additional_preference = getAdditionalPreference(['is_attribute']);

        if(clientPrefrenceModuleStatus('p2p_check') && $additional_preference['is_attribute']) {
            return true;
        }
        return false;
    }
}

if( !function_exists('is_attribute_enabled') ) {
    function is_attribute_enabled() {
        $additional_preference = getAdditionalPreference(['is_attribute']);
        if($additional_preference['is_attribute']) {
            return true;
        }
        return false;
    }
}

if( !function_exists('check_influencer_enable') ) {
    function check_influencer_enable() {
        $additional_preference = getAdditionalPreference(['is_attribute']);
        if(@$additional_preference['is_attribute']) {
            return true;
        }
        return false;
    }
}

if( !function_exists('is_p2p_vendor') ) {
    function is_p2p_vendor($vendor_id = '') {
        $auth_user = auth()->user();
        $user_vendor = UserVendor::where('user_id', $auth_user->id)->first();
        if(auth()->user() && (auth()->user()->is_superadmin != 1)) {
            if( !empty($user_vendor->vendor_id) ) {
                $vendor_id = $user_vendor->vendor_id;
            } else {
                return false;
            }
        }

        $vendor = Vendor::where('id', $vendor_id)->first();

        if(@$vendor->p2p && $vendor->p2p == 1) {
            return true;
        }
        return false;

    }
}

if( !function_exists('is_category_p2p') ) {
    function is_category_p2p($category) {
        if($category->categoryDetail->type_id == 13){
            return true;
        }
        return false;
    }
}

if( !function_exists('is_category_products') ) {
    function is_category_products($category) {
        return $products = Product::where('category_id',$category)->count();
    }
}

// if( !function_exists('is_p2p_vendor') ) {
//     function is_p2p_vendor() {

//         if( p2p_module_status() ) {

//             if(auth()->user() && (auth()->user()->is_superadmin != 1)) {

//                 $auth_user = auth()->user();
//                 $user_vendor = UserVendor::where('user_id', $auth_user->id)->first();



//                 if( !empty($user_vendor->vendor_id) ) {

//                     $vendor = Vendor::where('id', $user_vendor->vendor_id)->first();
//                     $client_preference = (object)session()->get('preferences');

//                     foreach(config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value){
//                         $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key ;
//                         $clientVendorTypes = $vendor_typ_key.'_check';
//                         $NomenclitureName =  $vendor_typ_key == "dinein" ? 'Dine-In' : $vendor_typ_value;
//                         if($client_preference->$clientVendorTypes == 1 && $vendor->$VendorTypesName){
//                             $offers[]=  $vendor->$VendorTypesName == 1 ? getNomenclatureName($NomenclitureName) : $NomenclitureName;
//                         }
//                     }

//                     if( count($offers) > 1 ) {
//                         return false;
//                     }
//                     elseif( count($offers) == 1 && ($vendor->p2p == 1) ) {
//                         return true;
//                     }
//                     else {
//                         return false;
//                     }
//                 }
//             }
//         }
//         return false;
//     }
// }

if( !function_exists('productDiscountPercentage') ) {
    function productDiscountPercentage($product_price = 0, $product_compare_price)
    {
        if($product_compare_price > 0) {
            $discount = ($product_compare_price - $product_price) / $product_compare_price * 100;
            if($discount>0){
                return round($discount);
            }
            return 0;
        }
        return 0;
    }
}

if( !function_exists('generateSlug') ) {
    function generateSlug($name)
    {
        if (Product::whereSku($slug = $name)->exists()) {
            $max = Product::whereSku($name)->latest('id')->value('sku');
            if (isset($max[-1]) && is_numeric($max[-1])) {
                return preg_replace_callback('/(\d+)$/', function($mathces) {
                    return $mathces[1] + 1;
                }, $max);
            }
            return $slug.'-'.rand();
        }
        return $slug;
    }
}

if( !function_exists('printOldOrDbValue') ) {
    function printOldOrDbValue($key, $data=null) {

        $value = '';

        if( !empty($key) ) {
            if( !empty(old($key)) ) {
                // Return Old Value
                $value = old($key);
            }
            elseif( !empty($data) ) {
                // Return Value from db
                if( is_object($data) ) {
                    $value = $data->$key;
                }
                elseif( is_array($data) ) {
                    $value = $data[$key];
                }
            }
            return $value;
        }
        return $value;
    }
}
if( !function_exists('get_tiny_url') ) {
    function get_tiny_url($url)  {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,'https://tinyurl.com/api-create.php?url='.$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}


if( !function_exists('makeCartEmpty') ) {
    function makeCartEmpty()
    {
        $cart = Cart::where('user_id',auth()->id())->select('id')->first();
        $cartid = $cart->id;
        Cart::where('id', $cartid)->update([
        'schedule_type' => null, 'scheduled_date_time' => null,
        'comment_for_pickup_driver' => null, 'comment_for_dropoff_driver' => null, 'comment_for_vendor' => null, 'schedule_pickup' => null, 'schedule_dropoff' => null, 'specific_instructions' => null
        ]);
        CaregoryKycDoc::where('cart_id',$cartid)->delete();
        CartAddon::where('cart_id', $cartid)->delete();
        CartCoupon::where('cart_id', $cartid)->delete();
        CartProduct::where('cart_id', $cartid)->delete();
        CartProductPrescription::where('cart_id', $cartid)->delete();

        return true;
    }
}
if (!function_exists('GerenalSlot')) {
    function GerenalSlot($myDate, $StartTime, $EndTime, $Duration="60",$delayMin=0)
    {
        $myDate  = date('Y-m-d',strtotime($myDate));
        //pr($myDate);
        $Duration = (($Duration==0)?'60':$Duration);

        $user = Auth::user();
        if (isset($user->timezone) && !empty($user->timezone)) {
            $timezoneset = $user->timezone;
        } else {
            $client = ClientData::orderBy('id', 'desc')->select('id', 'timezone')->first();

            if (isset($client->timezone) && !empty($client->timezone)) {
                $timezoneset = $client->timezone;
            } else {
                $timezoneset = 'Asia/Kolkata';
            }
        }
        $cr = Carbon::now()->addMinutes($delayMin);
        $now = dateTimeInUserTimeZone24($cr, $timezoneset);
        $nowT = strtotime($now);
        $nowA = Carbon::createFromFormat('Y-m-d H:i:s', $myDate.' '.$StartTime);
        $nowS = Carbon::createFromFormat('Y-m-d H:i:s', $nowA)->timestamp;
        $nowE = Carbon::createFromFormat('Y-m-d H:i:s', $myDate.' '.$EndTime)->timestamp;
        if ($nowT > $nowE) {
            return [];
        } else {
            $StartTime = date('H:i', strtotime($nowA));
        }

        $ReturnArray = array();
        $StartTime = strtotime($StartTime); //Get Timestamp
        $EndTime = strtotime($EndTime); //Get Timestamp
        $AddMins = $Duration * 60;
        $endtm = 0;
        $key = 0;
        while ($StartTime <= $EndTime) {
            $endtm = $StartTime + $AddMins;
            if ($endtm>$EndTime) {
                $endtm = $EndTime;
            }
            if( $StartTime < $endtm){

                if ($nowT>$nowS && $StartTime > $nowT ){
                    $key++;
                    //Condition to get slots from next available time on current datetime according to start time set while creating slots in vendor configuration
                  //  $ReturnArray[] = date("G:i", $StartTime).' - '.date("G:i", $endtm);

                    $ReturnArray[$key]['name'] = date('h:i A',$StartTime).' - '.date('h:i A', $endtm);
                    $ReturnArray[$key]['value'] = date("G:i", $StartTime).'-'.date("G:i", $endtm);
                }
                if($nowT <= $nowS){//Condition to get slots from next available time on other than current datetime according to start time set while creating slots in vendor configuration
                     $key++;
                    //$ReturnArray[] = date("G:i", $StartTime).' - '.date("G:i", $endtm);
                    $ReturnArray[$key]['name'] = date('h:i A',$StartTime).' - '.date('h:i A', $endtm);
                    $ReturnArray[$key]['value'] = date("G:i", $StartTime).'-'.date("G:i", $endtm);
                }
            }

            $StartTime += $AddMins;
            $endtm = 0;

        }
        return $ReturnArray;
    }
}



if (!function_exists('GetDayFromDate')) {
    function GetDayFromDate($date)
    {
        return strtolower(date('l', strtotime($date)));
    }
}

if (!function_exists('weekDaysArray')) {
    function weekDaysArray($daysArray='')
    {
        $daysArray = explode(',',$daysArray);
        $daysArrayName = [];
        $days = ['0'=>'Sunday','1'=>'Monday','2'=>'Tuesday','3'=>'Wednesday','4'=>'Thursday','5'=>'Friday','6'=>'Saturday'];
        foreach($days as $key=> $day)
        {
            if(in_array($key,$daysArray)){
                $daysArrayName[] = $day;
            }
        }
        return implode(',',$daysArrayName);
    }
}

if (!function_exists('getDaysArrayBetweenTwoDates')) {

    function getDaysArrayBetweenTwoDates($sdate,$edate,$matchDays=[],$alternate = ''){
      $period = CarbonPeriod::create($sdate, $edate);
        // Iterate over the period
        $periods = [];
            foreach ($period as $k => $date) {
                if($alternate){

                    if($k%2==0)
                        $periods[] =  $date->format('Y-m-d');


                }elseif(count($matchDays)>0){
                    $dayNumber = $date->dayOfWeek; // get day number
                    if(in_array($dayNumber,$matchDays))
                    {
                        $periods[] =  $date->format('Y-m-d');
                    }
                }else{
                    $periods[] =  $date->format('Y-m-d');
                }
            }
        // Convert the period to an array of dates
        return $periods;
    }
}

if( !function_exists('get_file_path') ) {
    function get_file_path($url,$type="FILL_URL",$height="260",$width="260")  {

        $img = 'default/default_image.png';
      if(!empty($url)){
        $img = $url;
      }

      $ex = checkImageExtension($img);
      $return_url = $values =  \Config::get('app.'.$type);

      $img = str_replace(' ', '', $img);
      if (substr($img, 0, 7) == "http://" || substr($img, 0, 8) == "https://"){
        $return_url  = $values.$height.'/'.$width.\Config::get('app.IMG_URL2').'/'.$img;
      } else {
        $return_url  = $values.$height.'/'.$width.\Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      }

      //pr($values);
    //   $img = 'default/default_image.png';
    //   if(!empty($value)){
    //     $img = $value;
    //     $values['is_original'] = true;
    //   }
    //   $ex = checkImageExtension($img);
    //   $values['proxy_url'] = \Config::get('app.IMG_URL1');
    //   $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
    //   $values['image_fit'] = \Config::get('app.FIT_URl');
    //   $values['image'] = $value;
      //return $values.$height.'/'.$width.\Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      return   $return_url  ;
    }
}

if (!function_exists('getUserToken')) {
    function getUserToken($credential)
    {
        $data['otp'] = rand(100000, 999999);
        $data['status'] = true;
        if(!empty($credential) && isset($credential->sms_credentials)){

            $credentials = json_decode($credential->sms_credentials);
            if (isset($credentials->static_otp) && $credentials->static_otp == '1') {
                $data['otp'] = '123456';
                $data['status'] = false;
            }
        }
            return $data;
    }
}

if (!function_exists('getOnDemandPricingRule')) {
    /**
     * getOnDemandPricingRule
     *
     * @param  mixed $vendorType = user selected vendor mode
     * @param  mixed $userSelection =  user selected pricing geting from vendor or freelancer
     * @param  mixed $is_service_product_price_from_dispatch custoom mode selecter by admin
     * @param  mixed $is_service_price_selection custoom mode selecter by admin  $is_service_product_price_from_dispatch = 0,$is_service_price_selection = 0,
     * @return void
     */
    function getOnDemandPricingRule($vendorType = "on_demand",$userSelection = "vendor",$additionalPreference)
    {

        $is_service_product_price_from_dispatch = @$additionalPreference['is_service_product_price_from_dispatch'] ?? 0;
        $is_service_price_selection             = @$additionalPreference['is_service_price_selection'] ?? 0;
        $return['is_price_from_freelancer'] = 0;
        $return['is_ondemand_multi_pricing'] = 0;
            $value = 0;
            if(($vendorType == "on_demand") && ($is_service_product_price_from_dispatch ==1 )){
                $return['is_price_from_freelancer'] =1;
                if($is_service_price_selection ==1 ){
                    $return['is_ondemand_multi_pricing'] = 1;
                    if($userSelection =='freelancer'){
                        $return['is_price_from_freelancer'] =1;
                    }else{
                        $return['is_price_from_freelancer'] =0;
                    }
                }
            }
            return $return;
    }

}
if (!function_exists('getDatesBetweenTwoDates')) {
    function getDatesBetweenTwoDates($start_date, $end_date)
    {
        $period = CarbonPeriod::create($start_date, $end_date);

        // Convert the period to an array of dates
        $dates = $period->toArray();
        return $dates;
    }
}

if(!function_exists('getDaysBetweenTwoDates')){
    function getDaysBetweenTwoDates($startDate, $endDate){
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        return $startDate->diffInDays($endDate) + 1;
}

}
if (!function_exists('recurringCalculationFunction')) {
    function recurringCalculationFunction($request)
    {
        $recurringformPost = (object)$request->recurringformPost;
        $weekTypes ='';
        $daysCnt ='';
        if(!empty($recurringformPost->weekDay)){
            $weekTypes = implode(',',$recurringformPost->weekDay);
        }

        $startDate = $recurringformPost->startDate;
        $endDate = $recurringformPost->endDate;

        $selectedCustomdates = [];

        if($recurringformPost->action=='2' || $recurringformPost->action=='1'){
            $startDate = $recurringformPost->startDate;
            $endDate = $recurringformPost->endDate;

            if($recurringformPost->action=='1'){
                $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate,$endDate);
            } else {
                $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate,$endDate,$recurringformPost->weekDay);
            }

            $daysCnt =count($selectedCustomdates);
            $selectedCustomdates = implode(',',$selectedCustomdates);
        }elseif($recurringformPost->action=='3'){
            $startDate = Carbon::now()->addDays(1);
            $endDate = Carbon::now()->addDays(1);
            $endDate = $endDate->addMonths($recurringformPost->month_number);
            $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate,$endDate);
            $daysCnt =count($selectedCustomdates);
            $selectedCustomdates = implode(',',$selectedCustomdates);
        }elseif($recurringformPost->action=='4'){
            if(!empty($recurringformPost->selectedCustomdates)){
                $daysCnt =count($recurringformPost->selectedCustomdates);
                $selectedCustomdates = implode(',',$recurringformPost->selectedCustomdates);
            }
        }elseif($recurringformPost->action=='6'){
            $startDate = $recurringformPost->startDate;
            $endDate = $recurringformPost->endDate;
            if($recurringformPost->action=='1'){
                $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate,$endDate);
            } else {
                $selectedCustomdates = getDaysArrayBetweenTwoDates($startDate,$endDate,$recurringformPost->weekDay,'A');
            }

            $daysCnt =count($selectedCustomdates);
            $selectedCustomdates = implode(',',$selectedCustomdates);
        }


        if(empty($daysCnt)){
            $days = getDaysArrayBetweenTwoDates($startDate,$endDate);
            $daysCnt =count($days);
        }

            return (object)[
                'weekTypes' => @$weekTypes,
                'selectedCustomdates' => @$selectedCustomdates,
                'startDate' => @$startDate,
                'endDate' => @$endDate,
                'action'  => @$recurringformPost->action,
                'schedule_time'=>@$recurringformPost->schedule_time??'10:00',
                'daysCnt'=>@$daysCnt??'1'
            ];

    }

    if (!function_exists('shipEngineEnable')) {
        function shipEngineEnable(){
            $shipping_option = ShippingOption::select('id', 'code','status')->where(['code' => 'shipengine', 'status' => 1])->first();
            if ($shipping_option) {
                return true;
            }
            return false;
        }
    }

    if (!function_exists('taxJarEnable')) {
        function taxJarEnable(){
            $key = ['is_taxjar_enable','taxjar_testmode','taxjar_api_token'];
            $creds = ClientPreferenceAdditional::select('key_name','key_value')->whereIn('key_name',$key)->get();
            $creds = array_column($creds->toArray(), 'key_value', 'key_name');
            if(isset($creds) && !empty($creds) && $creds['is_taxjar_enable'] == 1){
                return true;
            }
            return false;
        }
    }

    if (!function_exists('productPriceAfterVendorDiscount'))
    {
         function productPriceAfterVendorDiscount($vendorData,$product_discount_amount,$doller_compare,$cart)
        {
            $allProductsSum = 0;
            $cart_products = CartProduct::with(['product.variant', 'addon.option'])
            ->where('vendor_id', $vendorData->vendor_id)
            ->where('cart_id', $vendorData->cart_id)
            ->get();
            foreach ($cart_products as $cart_product) {
                // Calculate total price for the product variant
                $total_price =$cart_product->pvariant->actual_price ?? $cart_product->pvariant->price??0;
                // Calculate the total price of the product (variant price * quantity)
                $allProductsSum += $total_price * $cart_product->quantity;
                // Calculate the total price of the addons for the product
                $product_addon_price = 0;
                foreach ($cart_product->addon as $addon) {
                    $addon_option = $addon->option;
                    if ($addon_option) {
                        $addon_price = $addon_option->price * $cart_product->quantity;
                        $product_addon_price += $addon_price;
                    }
                }
                // Add the total addon price to the overall sum
                $allProductsSum += $product_addon_price;
            }
            $PromoDelete = 0;
            $data['vendor_discount_amount'] = 0;
            $data['deliveryfeeOnCoupon'] = 0;
            if (isset($vendorData->coupon) && !empty($vendorData->coupon) )
            {
                if ( $PromoDelete !=1)
                {
                        $minimum_spend = 0;
                        if (isset($vendorData->coupon->promo->minimum_spend)) {
                            $minimum_spend = $vendorData->coupon->promo->minimum_spend * $doller_compare;
                        }
                        $maximum_spend = 0;
                        if (isset($vendorData->coupon->promo->maximum_spend)) {
                            $maximum_spend = $vendorData->coupon->promo->maximum_spend * $doller_compare;
                        }
                        if( ($minimum_spend <= $allProductsSum ) && ($maximum_spend >= $allProductsSum))
                        {
                                if ($vendorData->coupon->promo->promo_type_id == 2) {
                                    $data['vendor_discount_amount'] = $vendorData->coupon->promo->amount;
                                } else {
                                    $data['vendor_discount_amount'] = ($product_discount_amount * $vendorData->coupon->promo->amount / 100);
                                }
                                if ($vendorData->coupon->promo->allow_free_delivery == 1) {
                                    $data['deliveryfeeOnCoupon'] = 1;
                                }
                        }else{
                            $cart->coupon()->delete();
                            $vendorData->coupon()->delete();
                            unset($vendorData->coupon);
                           return $data;
                        }
                }
            }
            return $data??0;
        }
    }
}

if (!function_exists('mastercardGateway')) {
    function mastercardGateway() {
        $payopt = PaymentOption::where('code', 'mastercard')->get(['test_mode', 'credentials'])->first();
        $test_url = 'test-gateway.mastercard.com';
        if(!empty($payopt) && $payopt->test_mode != 1){
            $creds = json_decode($payopt->credentials);
            $test_url = $creds->mastercard_gateway??$test_url;
        }
        return $test_url;
    }
}
