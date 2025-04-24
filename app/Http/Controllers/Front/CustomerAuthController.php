<?php

namespace App\Http\Controllers\Front;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Password;
use Carbon\Carbon;
use Omnipay\Omnipay;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Omnipay\Common\CreditCard;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\InfluencerTrait;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Notifications\PasswordReset;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Controllers\Front\FrontController;
use App\Models\{AllergicItem, AppStyling, UserRegistrationDocuments, AppStylingOption, VendorCategory, Currency, Client, Category, Brand, Cart, ReferAndEarn, ClientPreference, Vendor, ClientCurrency, User, Country, UserRefferal, Wallet, WalletHistory, CartProduct, PaymentOption, UserVendor, PermissionsOld, UserPermissions, VendorDocs, VendorRegistrationDocument, EmailTemplate, NotificationTemplate, UserDevice, Page, UserDocs, WebStylingOption, Type, UserAllergicItem, VendorAdditionalInfo};

use Kutia\Larafirebase\Facades\Larafirebase;
use App\Http\Controllers\Client\VendorController;
use Math;
use SimpleXMLElement;
use Log;
use App\Http\Traits\ProductActionTrait;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use App\Services\FirebaseService;
use Illuminate\Validation\Rule;

class CustomerAuthController extends FrontController
{
    use ApiResponser;
    use ProductActionTrait, InfluencerTrait;

    private $folderName = '/vendor/extra_docs';

    public function __construct(Request $request)
    {
        $code = Client::orderBy('id', 'asc')->value('code');
        $this->folderName = '/' . $code . '/vendor/extra_docs';
    }

    public function getTestHtmlPage()
    {
        return view('test');
    }

    public function getDemoCabBookingPage()
    {
        return view('cabdemo');
    }

    public function fcm()
    {
        return view('firebase');
    }

    public function sendNotification(Request $request)
    {
        // $token = ["fXC1tzHiywg:APA91bGj3YXxPXuiBjCSAhlt0leikG2eq2gIJm3EFtSjkfp4c6akzpeDOqq2XfvUxxX99i36aCPf8gFsJIZrU7Ywcx6ZCIMh9vAPJctpxyU0_pagKF-wgVURZ2Z6C6XMaWAFZCDlas3L"];
        $token = $request->whenFilled('user_id',
            fn ($user) => UserDevice::query()->where('user_id', $user)->pluck('device_token'),
            fn () => collect($request->input('token')));
        //previous
        //$from = 'AAAAA_v18xQ:APA91bFEvE7X7b8xFL6sV5F8iT1-RDRHLniD6mVypmx39XLtDavdE25910WJMig0y43Mp3kJPuhRphXKA1SERhkH_u_lzuujc0Gpf4BGN-wdC80ddDqcccGOKfplwV9LQ5qZVyKuWZRx';

        $result = FirebaseService::sendBasicNotification(
            $request->input('title', 'This is a test notification'),
            $request->input('body', 'This notification can be safely ignored'),
            $token->toArray(),
        );

        collect($result)->groupBy('status')->map->toArray()->dd();
    }

    public function loginForm(Request $request, $domain = '')
    {
        $curId = Session::get('customerCurrency');
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();
        $preferences = ClientPreference::select('signup_image')->first();
        if ($set_template->template_id == 4) {
            $login_page = "template_four.account.loginnew";
        } elseif ($set_template->template_id == 6) {
            $login_page = "template_six.account.loginnew";
        } elseif ($set_template->template_id == 8) {
            $login_page = "template_eight.account.loginnew";
        } else {
            $login_page = "account.loginnew";
        }
        return view('frontend.' . $login_page)->with(['navCategories' => $navCategories, 'preferences' => $preferences]);
    }

    public function registerForm(Request $request, $domain = '')
    {
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        $preferences = ClientPreference::select('signup_image')->first();

        $urlPrevious = url()->previous();
        $routePrevious = app('router')->getRoutes($urlPrevious)->match(app('request')->create($urlPrevious))->getName();
        if ($routePrevious == 'showCart') {
            session()->put('user_type', 'geust');
        }

        $privacy = Page::with(['translations' => function ($q) use ($langId) {
            $q->where('language_id', $langId)->where('type_of_form', [4]);   # get privacy & terms url
        }])->whereHas('translations', function ($q) use ($langId) {
            $q->where('language_id', $langId)->where('type_of_form', [4]);   # get privacy & terms url
        })->first();

        $terms = Page::with(['translations' => function ($q) use ($langId) {
            $q->where('language_id', $langId)->where('type_of_form', [5]);   # get privacy & terms url
        }])->whereHas('translations', function ($q) use ($langId) {
            $q->where('language_id', $langId)->where('type_of_form', [5]);   # get privacy & terms url
        })->first();
        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        //pr($user_registration_documents);
        $set_template = WebStylingOption::where('web_styling_id', 1)->where('is_selected', 1)->first();
        if ($set_template->template_id == 4) {
            $register_page = "template_four.account.registernew";
        } elseif ($set_template->template_id == 6) {
            $register_page = "template_six.account.registernew";
        } elseif ($set_template->template_id == 8) {
            $register_page = "template_eight.account.registernew";
        } else {
            $register_page = "account.registernew";
        }

        $allergic_items = AllergicItem::get();
        //echo $register_page; die;
        if (! Session::get('referrer')) {
            return view('frontend.' . $register_page)->with(['navCategories' => $navCategories, 'privacy' => $privacy, 'terms' => $terms, "user_registration_documents" => $user_registration_documents, 'allergic_items' => $allergic_items, 'preferences' => $preferences]);
        } else {
            return view('frontend.account.' . $register_page)->with(['navCategories' => $navCategories, 'code' => Session::get('referrer'), 'privacy' => $privacy, 'terms' => $terms, "user_registration_documents" => $user_registration_documents, 'allergic_items' => $allergic_items, 'preferences' => $preferences]);
        }
    }

    /**     * check if cookie already exist     */
    public function checkCookies($userid)
    {
        if (Cookie::has('uuid')) {
            $existCookie = Cookie::get('uuid');
            $userFind = User::where('system_id', $existCookie)->first();
            if ($userFind) {
                $cart = Cart::where('user_id', $userFind->id)->first();
                if ($cart) {
                    $cart->user_id = $userid;
                    $cart->save();
                }
                $userFind->delete();
            }
            Cookie::queue(Cookie::forget('uuid'));
            return redirect()->route('user.checkout');
        }
    }

    /**     * Display login Form     */
    public function login(LoginRequest $req, $domain = '')
    {
        if (Auth::attempt(['email' => $req->email, 'password' => $req->password, 'status' => 1])) {
            $userid = Auth::id();
            if ($req->has('access_token')) {
                if ($req->access_token) {
                    $user_device = UserDevice::where('user_id', $userid)->where('device_token', $req->access_token)->first();
                    if (!$user_device) {
                        $user_device = new UserDevice();
                        $user_device->user_id = $userid;
                        $user_device->device_type = 'web';
                        $user_device->device_token = $req->access_token;
                        $user_device->save();
                    }
                }
            }
            $this->checkCookies($userid);
            $user_cart = Cart::where('user_id', $userid)->first();

            if ($user_cart) {
                $unique_identifier_cart = Cart::where('unique_identifier', session()->get('_token'))->first();
                if ($unique_identifier_cart) {
                    $unique_identifier_cart_products = CartProduct::where('cart_id', $unique_identifier_cart->id)->get();
                    foreach ($unique_identifier_cart_products as $unique_identifier_cart_product) {
                        $user_cart_product_detail = CartProduct::where('cart_id', $user_cart->id)->where('product_id', $unique_identifier_cart_product->product_id)->first();
                        if ($user_cart_product_detail) {
                            $user_cart_product_detail->quantity = ($unique_identifier_cart_product->quantity + $user_cart_product_detail->quantity);
                            $user_cart_product_detail->save();
                            $unique_identifier_cart_product->delete();
                        } else {
                            $unique_identifier_cart_product->cart_id = $user_cart->id;
                            $unique_identifier_cart_product->save();
                        }
                    }
                    $unique_identifier_cart->delete();
                }
            } else {
                Cart::where('unique_identifier', session()->get('_token'))->update(['user_id' => $userid, 'created_by' => $userid, 'unique_identifier' => '']);
            }
            if (session()->has('url.intended')) {
                $url = session()->get('url.intended');
                session()->forget('url.intended');
                return redirect($url);
            } else {
                return redirect()->route('user.verify');
            }
        }
        $checkEmail = User::where('email', $req->email)->first();
        if ($checkEmail) {
            return redirect()->back()->with('err_password', __('Password not matched. Please enter correct password.'));
        }
        return redirect()->back()->with('err_email', __('Email not exist. Please enter correct email.'));
    }


    /**     * Display register Form     */
    public function register(SignupRequest $req, $domain = '')
    {
        try {
            $req->merge([
                'phone_number' => $req->whenFilled('phone_number', fn($phone) => str_replace(' ', '', $phone), fn() => null),
            ]);

            if ((empty($req->email)) && (empty($req->phone_number))) {
                $req->validate([
                    'email'  => 'required',
                    'phone_number'  => 'required|unique:users'
                ], [
                    "email.required" => __('The email field is required.'),
                    "phone_number.required" => __('The phone number field is required.'),
                ]);
                if ($req->dialCode == 91) {
                    $req->validate([
                        'phone_number'  => 'numeric|min:10|max:10'
                    ]);
                }
            } else {

                $preferences = ClientPreference::first();
                if (!empty($req->email) && ($preferences->verify_email == 0)) {

                    $validator = $req->validate([
                        'email'  => 'email|unique:users'
                    ]);
                }
                if($req->is_lawyer == 1){
                $req->validate([
                    'document' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048' 
                ]);
                }
                if(!empty($req->phone_number) && isset($preferences) && ($preferences->verify_phone == 0)){
                    $validator = $req->validate([
                        'phone_number' => [
                            'string',
                            'min:7',
                            'max:15',
                            Rule::unique('users')->where(function ($query) use ($req) {
                                return $query->where('phone_number', $req->phone_number)
                                    ->where('dial_code', $req->dialCode);
                            })
                        ],
                        'dialCode' => 'required',
                    ]);
                }
            }
            $getAdditionalPreference = getAdditionalPreference(['is_user_pre_signup']);
            $user = new User();
            $county = Country::where('code', strtoupper($req->countryData))->first();
            $client_timezone = Client::where('id', '>', 0)->value('timezone');
            $phoneCode = getUserToken($preferences)['otp'];
            $emailCode = getUserToken($preferences)['otp'];
            $sendTime = \Carbon\Carbon::now()->addMinutes(10)->toDateTimeString();
            $email = (!empty($req->email)) ? $req->email : ''; //('ro_'.Carbon::now()->timestamp . '.' . uniqid() . '@royoorders.com');
            $user->type = 1;
            $user->status = 1;
            $user->role_id = 1;
            $user->name = $req->name;

            if (isset($getAdditionalPreference) && ($getAdditionalPreference['is_user_pre_signup'] == 1)) {
                $user->is_presignup = 1;
            } else {
                $user->is_presignup = 0;
            }
            $user->email = $email;
            $user->is_email_verified = 0;
            $user->is_phone_verified = 0;
            $user->country_id = $county->id;
            $user->phone_token = $phoneCode;
            $user->dial_code = $req->dialCode;
            $user->email_token = $emailCode;
            $user->phone_number = $req->phone_number;
            $user->phone_token_valid_till = $sendTime;
            $user->email_token_valid_till = $sendTime;
            $user->timezone = $client_timezone;

            if (session()->get('company_id')) {
                $user->company_id = base64_decode(session()->get('company_id'));
            }

            $user->password = Hash::make($req->password);
            $user->custom_allergic_items = $req->custom_allergic_items ?? null;
            
            if ($req->has('document') && $req->is_lawyer == 1) {
                $user->status = 0;
                $user->is_admin = 1;
                $user->enrollment_no = rand(100000, 999999);
                $file = $req->file('document');
                $path = Storage::disk('s3')->put($this->folderName, $file, 'public');
                $user->document = $path;
                $user->assignRole(7);
            }else{
                $user->status = 0;
                $user->is_admin = 0;
                $user->enrollment_no = '0';
                $user->document = '';
                $user->assignRole(9);
            }
            
            $user->save();
            if ($req->allergic_item_ids && count($req->allergic_item_ids)) {
                foreach ($req->allergic_item_ids as $key => $id) {
                    $data[$key] = [
                        'user_id' => $user->id,
                        'allergic_item_id' => $id,
                    ];
                }
                UserAllergicItem::insert($data);
            }

            // Save User Kyc Details
            if (@$req->kyc) {
                InfluencerTrait::saveKycData($req, $user->id);
            }

            $wallet = $user->wallet;
            $userRefferal = new UserRefferal();
            $userRefferal->refferal_code = $this->randomData("user_refferals", 8, 'refferal_code');
            if ($req->refferal_code != null) {
                $userRefferal->reffered_by = $req->refferal_code;
            }
            $userRefferal->user_id = $user->id;
            $userRefferal->save();
            $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
            if ($user_registration_documents->count() > 0) {
                foreach ($user_registration_documents as $user_registration_document) {
                    $doc_name = str_replace(" ", "_", $user_registration_document->primary->slug);
                    if ($user_registration_document->file_type != "Text" && $user_registration_document->file_type != "selector") {
                        if ($req->hasFile($doc_name)) {
                            $vendor_docs =  new UserDocs();
                            $vendor_docs->user_id = $user->id;
                            $vendor_docs->user_registration_document_id = $user_registration_document->id;
                            $filePath = $this->folderName . '/' . Str::random(40);
                            $file = $req->file($doc_name);
                            $vendor_docs->file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                            $vendor_docs->save();
                        }
                    } else {
                        if (!empty($req->$doc_name)) {
                            $vendor_docs =  new UserDocs();
                            $vendor_docs->user_id = $user->id;
                            $vendor_docs->user_registration_document_id = $user_registration_document->id;
                            $vendor_docs->file_name = $req->$doc_name;
                            $vendor_docs->save();
                        }
                    }
                }
            }

            if ($user->id > 0) {
                if ($req->refferal_code != null) {
                    $refferal_amounts = ClientPreference::first();
                    if ($refferal_amounts) {
                        if ($refferal_amounts->reffered_by_amount != null && $refferal_amounts->reffered_to_amount != null) {
                            $reffered_by = UserRefferal::where('refferal_code', $req->refferal_code)->first();
                            $user_refferd_by = $reffered_by->user_id;
                            $user_refferd_by = User::where('id', $reffered_by->user_id)->first();
                            if ($user_refferd_by) {
                                //user reffered by amount
                                $wallet_user_reffered_by = $user_refferd_by->wallet;
                                $wallet_user_reffered_by->depositFloat($refferal_amounts->reffered_by_amount, ['Referral code used by <b>' . $req->name . '</b>']);
                                $wallet_user_reffered_by->balance;
                                //user reffered to amount
                                $wallet->depositFloat($refferal_amounts->reffered_to_amount, ['You used referral code of <b>' . $user_refferd_by->name . '</b>']);
                                $wallet->balance;
                            }
                        }
                    }
                }
                // Auth::login($user);
                Session::put('default_country_code', $user->dial_code);
                $this->checkCookies($user->id);
                $user_cart = Cart::where('user_id', $user->id)->first();
                if ($user_cart) {
                    $unique_identifier_cart = Cart::where('unique_identifier', session()->get('_token'))->first();
                    if ($unique_identifier_cart) {
                        $unique_identifier_cart_products = CartProduct::where('cart_id', $unique_identifier_cart->id)->get();
                        foreach ($unique_identifier_cart_products as $unique_identifier_cart_product) {
                            $user_cart_product_detail = CartProduct::where('cart_id', $user_cart->id)->where('product_id', $unique_identifier_cart_product->product_id)->first();
                            if ($user_cart_product_detail) {
                                $user_cart_product_detail->quantity = ($unique_identifier_cart_product->quantity + $user_cart_product_detail->quantity);
                                $user_cart_product_detail->save();
                                $unique_identifier_cart_product->delete();
                            } else {
                                $unique_identifier_cart_product->cart_id = $user_cart->id;
                                $unique_identifier_cart_product->save();
                            }
                        }
                        $unique_identifier_cart->delete();
                    }
                } else {
                    Cart::where('unique_identifier', session()->get('_token'))->update(['user_id' => $user->id, 'created_by' => $user->id, 'unique_identifier' => '']);
                }

                ####################################################
                ## if p2p is enable then register user as a admin ##
                ####################################################

                if (getClientPreferenceDetail()->p2p_check) {

                    $user->assignRole(4); // by default make this user as vendor

                    $user->is_admin = 1;
                    $user->save();

                    // Create vendor with default images
                    $vendor = new Vendor();
                    $vendor->logo = 'default/default_logo.png';
                    $vendor->banner = 'default/default_image.png';

                    $vendor->status = 1;
                    $vendor->name = $user->name;
                    $vendor->p2p = 1;
                    $vendor->email = $user->email ?? '';
                    $vendor->phone_no = $user->phone_number ?? '';
                    $vendor->slug = Str::slug($user->name, "-");
                    $vendor->save();
                    UserVendor::create(['user_id' => $user->id, 'vendor_id' => $vendor->id]);
                    $user->createPermissionsUser();
                    $p2p_type = Type::where('service_type', 'p2p')->first();
                    if (!empty($p2p_type)) {
                        $category_id = Category::where('type_id', $p2p_type->id)->get();
                        $categories_ids = [];

                        if (!empty($category_id)) {
                            foreach ($category_id as $key => $val) {
                                $categories_ids[] = $val->id;
                            }
                        }
                        $req->request->add(['selectedCategories' => $categories_ids]);
                    }

                    $this->addDataSaveVendor($req, $vendor->id);
                }

                Session::forget('referrer');
                $prefer = ClientPreference::select(
                    'sms_credentials',
                    'mail_type',
                    'mail_driver',
                    'mail_host',
                    'mail_port',
                    'mail_username',
                    'mail_password',
                    'mail_encryption',
                    'mail_from',
                    'sms_provider',
                    'sms_key',
                    'sms_secret',
                    'sms_from',
                    'theme_admin',
                    'distance_unit',
                    'map_provider',
                    'date_format',
                    'time_format',
                    'map_key',
                    'sms_provider',
                    'verify_email',
                    'verify_phone',
                    'app_template_id',
                    'web_template_id'
                )->first();
                if (getUserToken($prefer)['status']) {
                    if (!empty($prefer->sms_key) && !empty($prefer->sms_secret) && !empty($prefer->sms_from)) {
                        if ($user->dial_code == "971") {
                            $to = '+' . $user->dial_code . "0" . $user->phone_number;
                        } else {
                            $to = '+' . $user->dial_code . $user->phone_number;
                        }
                        $provider = $prefer->sms_provider;
                        //  $body = "Dear " . ucwords($user->name) . ", Thanks for creating an account with us!";
                        // $body = "Dear " . ucwords($user->name) . ", Please enter OTP " . $phoneCode . " to verify your account.".((!empty($signReq->app_hash_key))?" ".$signReq->app_hash_key:'');
                        $keyData = ['{user_name}' => ucwords($user->name)];
                        $body = sendSmsTemplate('user-signup-sms', $keyData);
                        $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);

                        if ($prefer->verify_phone == 1) {
                            $response['send_otp'] = 1;
                            $to = '+' . $user->dial_code . $user->phone_number;
                            $provider = $prefer->sms_provider;
                            //$body = "Dear ".ucwords($user->name).", Please enter OTP ".$phoneCode." to verify your account.";
                            $keyData = ['{user_name}' => ucwords($user->name), '{otp_code}' => $phoneCode];
                            $body = sendSmsTemplate('verify-account', $keyData);
                            $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
                        }
                    }
                }
                if (!empty($prefer->mail_driver) && !empty($prefer->mail_host) && !empty($prefer->mail_port) && !empty($prefer->mail_port) && !empty($prefer->mail_password) && !empty($prefer->mail_encryption)) {
                    $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
                    $confirured = $this->setMailDetail($prefer->mail_driver, $prefer->mail_host, $prefer->mail_port, $prefer->mail_username, $prefer->mail_password, $prefer->mail_encryption);
                    $client_name = $client->name;
                    $mail_from = $prefer->mail_from;
                    $sendto = $req->email;
                    try {
                        $email_template_content = '';
                        $email_template = EmailTemplate::where('id', 2)->first();
                        if ($email_template) {
                            $email_template_content = $email_template->content;
                            $email_template_content = str_ireplace("{code}", $emailCode, $email_template_content);
                            $email_template_content = str_ireplace("{customer_name}", ucwords($user->name), $email_template_content);
                        }
                        $data = [
                            'code' => $emailCode,
                            'link' => "link",
                            'email' => $sendto,
                            'mail_from' => $mail_from,
                            'client_name' => $client_name,
                            'logo' => $client->logo['original'],
                            'subject' => $email_template->subject,
                            'customer_name' => ucwords($user->name),
                            'email_template_content' => $email_template_content,
                        ];
                        dispatch(new \App\Jobs\SendVerifyEmailJob($data))->onQueue('verify_email');
                        $notified = 1;
                    } catch (\Exception $e) {
                        $user->save();
                    }
                }
                
                return redirect()->route('userHome')->with('success', __('You have successfully registered. Please wait for admin approval.'));
                // return redirect()->route('user.verify');
            }
        } catch (Exception $e) {
        }
    }

    /**     * proceed to user login on phone number     */
    public function proceedToPhoneLogin($req, $domain = '')
    {
        $user = User::where('phone_number', $req->phone_number)->where('dial_code', $req->dialCode)->where('status', 1)->first();
        if ($user) {
            Auth::login($user);
            $user->is_phone_verified = 1;
            $user->phone_token = NULL;
            $user->phone_token_valid_till = NULL;
            $user->save();
            $userid = $user->id;
            if ($req->has('access_token')) {
                if ($req->access_token) {
                    $user_device = UserDevice::where('user_id', $userid)->where('device_token', $req->access_token)->first();
                    if (!$user_device) {
                        $user_device = new UserDevice();
                        $user_device->user_id = $userid;
                        $user_device->device_type = 'web';
                        $user_device->device_token = $req->access_token;
                        $user_device->save();
                    }
                }
            }
            $this->checkCookies($userid);

            $user_cart = Cart::where('user_id', $userid)->first();
            if ($user_cart) {
                $unique_identifier_cart = Cart::where('unique_identifier', session()->get('_token'))->first();
                if ($unique_identifier_cart) {
                    $unique_identifier_cart_products = CartProduct::where('cart_id', $unique_identifier_cart->id)->get();
                    foreach ($unique_identifier_cart_products as $unique_identifier_cart_product) {
                        $user_cart_product_detail = CartProduct::where('cart_id', $user_cart->id)->where('product_id', $unique_identifier_cart_product->product_id)->first();
                        if ($user_cart_product_detail) {
                            $user_cart_product_detail->quantity = ($unique_identifier_cart_product->quantity + $user_cart_product_detail->quantity);
                            $user_cart_product_detail->save();
                            $unique_identifier_cart_product->delete();
                        } else {
                            $unique_identifier_cart_product->cart_id = $user_cart->id;
                            $unique_identifier_cart_product->save();
                        }
                    }
                    $unique_identifier_cart->delete();
                }
            } else {
                Cart::where('unique_identifier', session()->get('_token'))->update(['user_id' => $userid, 'created_by' => $userid, 'unique_identifier' => '']);
            }

            if ($this->checkTemplateForAction(8)) {

                $this->LoginActionRecentView($userid);
            }

            //Login Observer
            UserObserver::signIn(auth()->user());

            $message = ('Logged in successfully');
            $redirect_to = '';

            if (session()->has('url.intended')) {

                $redirect_to = session()->get('url.intended');
                session()->forget('url.intended');
            } else {

                $redirect_to = route('user.verify');
            }
            $req->request->add(['is_phone' => 1, 'redirect_to' => $redirect_to]);
            $response = $req->all();

            return $this->successResponse($response, $message);
        } else {
            return $this->errorResponse(__('Invalid phone number'), 404);
        }
    }

    public function checkValidEmail(Request $request, $domain = '')
    {
        try {
            $username = $request->username;

            // Define regular expressions for phone and email validation
            $phone_regex = '/^[0-9\-\(\)\/\+\s]*$/';
            $email_regex = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';

            if (preg_match($phone_regex, $username)) {
                // Handle phone number validation and existence check
                $phone_number = preg_replace('/\D+/', '', $username);
                $dialCode = $request->dialCode;

                // Check if the user exists based on phone number and dial code
                $user = User::where('dial_code', $dialCode)->where('phone_number', $phone_number)->first();

                if ($user) {
                    // User with the provided phone number exists
                    return $this->successResponse(null, 'user exists');
                } else {
                    // User with the provided phone number does not exist
                    return response()->json(['message' => __('You are not registered with us. Please sign up.')], 404);
                }
            } elseif (preg_match($email_regex, $username)) {
                // Handle email validation and existence check
                $username = str_ireplace(' ', '', $username);

                // Check if the user exists based on email
                $user = User::where('email', $username)->first();

                if ($user) {
                    // User with the provided email exists
                    return $this->successResponse(null, 'user exists', 200);
                } else {
                    // User with the provided email does not exist
                    return response()->json(['message' => __('You are not registered with us. Please sign up.')], 404);
                }
            } else {
                // Invalid username format
                return response()->json(['message' => __('Invalid email or phone number')], 400);
            }
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /*** Login user via username ***/
    public function loginViaUsername(Request $request, $domain = '')
    {
        try {
            $errors = array();

            $phone_regex = '/^[0-9\-\(\)\/\+\s]*$/';
            $email_regex = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
            $username = $request->username;

            if (preg_match($phone_regex, $username)) {
                $validator = Validator::make($request->all(), [
                    'username'  => 'required',
                    'dialCode'  => 'required',
                    'countryData'  => 'required'
                ]);

                if ($validator->fails()) {

                    foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                        $errors['error'] = __($error_value[0]);
                        return response()->json($errors, 422);
                    }
                }
                $prefer = ClientPreference::select(
                    'sms_credentials',
                    'mail_type',
                    'mail_driver',
                    'mail_host',
                    'mail_port',
                    'mail_username',
                    'mail_password',
                    'mail_encryption',
                    'mail_from',
                    'sms_provider',
                    'sms_key',
                    'sms_secret',
                    'sms_from',
                    'theme_admin',
                    'distance_unit',
                    'map_provider',
                    'date_format',
                    'time_format',
                    'map_key',
                    'sms_provider',
                    'verify_email',
                    'verify_phone',
                    'app_template_id',
                    'web_template_id'
                )->first();

                $phone_number = preg_replace('/\D+/', '', $username);
                $dialCode = $request->dialCode;
                $fullNumber = $request->full_number;
                // $fullNumberWithoutPlus = str_replace('+', '', $fullNumber);
                // $phone_number = substr($fullNumberWithoutPlus, strlen($dialCode));
                $phoneCode =  getUserToken($prefer)['otp'];
                $sendTime = Carbon::now()->addMinutes(10)->toDateTimeString();
                $request->request->add(['is_phone' => 1, 'phone_number' => $phone_number, 'phoneCode' => $phoneCode, 'sendTime' => $sendTime, 'codeSent' => 0]);

                $user = User::where('dial_code', $dialCode)->where('phone_number', $phone_number)->first();
                if (!$user) {
                    if (session()->get("locale") == "ar") {
                        return $this->errorResponse(__('أنت غير مسجل معنا. يرجى الاشتراك'), 404);
                    }
                    return $this->errorResponse(__('You are not registered with us. Please sign up.'), 404);

                    /*  $registerUser = $this->registerViaPhone($request)->getData();
                    if($registerUser->status == 'Success'){
                        $user = $registerUser->data;
                    }else{
                        return $this->errorResponse(__('Invalid data'), 404);
                    }*/
                }else{
                    $user->phone_token = $phoneCode;
                    $user->phone_token_valid_till = $sendTime;
                    $user->save();
                }

                if ($dialCode == "971") {
                    $to = '+' . $dialCode . "0" . $phone_number;
                } else {
                    $to = '+' . $dialCode . $phone_number;
                }
                $provider = $prefer->sms_provider;
                //$body = "Please enter OTP ".$phoneCode." to verify your account.";
                $keyData = ['{user_name}' => ucwords($user->name), '{otp_code}' => $phoneCode];
                $body = sendSmsTemplate('verify-account', $keyData);
                if (!empty($provider)) {
                    if (getUserToken($prefer)['status']) {
                        $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
                    } else {
                        $send = 1;
                    }
                    if ($send == 1) {
                        $request->request->add(['codeSent' => 1]);
                        $message = __('An otp has been sent to your phone. Please check.');
                        $response = $request->all();
                        unset($response->_token);
                        return $this->successResponse($response, $message);
                    } else {

                        if (session()->get("locale") == "ar") {
                            return $this->errorResponse(__('حدث خطأ ما في إرسال OTP. نأسف للإزعاج'), 404);
                        }
                        return $this->errorResponse(__('Something went wrong in sending OTP. We are sorry to for the inconvenience'), 404);
                    }
                } else {
                    if (session()->get("locale") == "ar") {
                        return $this->errorResponse(__('لم يتم تكوين خدمة الموفر. الرجاء الاتصال بالإدارة'), 404);
                    }
                    return $this->errorResponse(__('Provider service is not configured. Please contact administration'), 404);
                }
            } elseif (preg_match($email_regex, $username)) {


                $validator = Validator::make($request->all(), [
                    'username'  => 'required'
                ]);

                if ($validator->fails()) {

                    foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                        $errors['error'] = __($error_value[0]);
                        return response()->json($errors, 422);
                    }
                }
                $username = str_ireplace(' ', '', $username);
                $remember = $request->has('remember');
                if (Auth::attempt(['email' => $username, 'password' => $request->password, 'status' => 1], $remember)) {

                    //Login Observer
                    UserObserver::signIn(auth()->user());

                    $userid = Auth::id();
                    $Authuser = Auth::user();
                    $update_last_login = User::where('id',$userid)->update(['last_login_at' => Carbon::now()->toDateTimeString()]);
                    if($request->has('access_token')){
                        if($request->access_token){
                            $user_device = UserDevice::where('user_id', $userid)->where('device_token', $request->access_token)->first();
                            if (!$user_device) {
                                $user_device = new UserDevice();
                                $user_device->user_id = $userid;
                                $user_device->device_type = 'web';
                                $user_device->device_token = $request->access_token;
                                $user_device->save();
                            }
                        }
                    }
                    if ($Authuser->is_superadmin == 1 || $Authuser->is_admin == 1) {

                        Auth::logout();
                        Auth::attempt(['email' => $username, 'password' => $request->password, 'status' => 1]);
                    }
                    $this->checkCookies($userid);
                    $user_cart = Cart::where('user_id', $userid)->first();

                    if ($user_cart) {

                        $unique_identifier_cart = Cart::where('unique_identifier', session()->get('_token'))->first();
                        if ($unique_identifier_cart) {
                            $unique_identifier_cart_products = CartProduct::where('cart_id', $unique_identifier_cart->id)->get();
                            foreach ($unique_identifier_cart_products as $unique_identifier_cart_product) {
                                $user_cart_product_detail = CartProduct::where('cart_id', $user_cart->id)->where('product_id', $unique_identifier_cart_product->product_id)->first();
                                if ($user_cart_product_detail) {
                                    $user_cart_product_detail->quantity = ($unique_identifier_cart_product->quantity + $user_cart_product_detail->quantity);
                                    $user_cart_product_detail->save();
                                    $unique_identifier_cart_product->delete();
                                } else {
                                    $unique_identifier_cart_product->cart_id = $user_cart->id;
                                    $unique_identifier_cart_product->save();
                                }
                            }
                            $unique_identifier_cart->delete();
                        }
                    } else {

                        Cart::where('unique_identifier', session()->get('_token'))->update(['user_id' => $userid, 'created_by' => $userid, 'unique_identifier' => '']);
                    }


                    if ($this->checkTemplateForAction(8)) {

                        $this->LoginActionRecentView($userid);
                    }

                    $message = 'Logged in successfully';
                    $redirect_to = '';

                    if (session()->has('url.intended')) {

                        $redirect_to = session()->get('url.intended');
                        session()->forget('url.intended');
                    } else {

                        $redirect_to = route('userHome');
                        // $redirect_to = route('user.verify');
                    }
                    $request->request->add(['is_email' => 1, 'redirect_to' => $redirect_to]);
                    $response = $request->all();
                    return $this->successResponse($response, $message);
                }
                $checkEmail = User::where('email', $username)->first();
                if ($checkEmail) {

                    if ($checkEmail->status != 1) {
                        if (session()->get("locale") == "ar") {
                            if($checkEmail->hasRole('Lawyer')){
                                return $this->errorResponse(__('ستتمكن من تسجيل الدخول بمجرد تفعيل حسابك من قبل المسؤول'), 404);
                            }else{
                                return $this->errorResponse(__('أنت غير مخول للوصول إلى هذا الحساب'), 404);
                            }
                        }
                        if($checkEmail->hasRole('Lawyer')){
                            return $this->errorResponse(__('You will be able to log in once the admin activates your account.'), 404);
                        }else{
                            return $this->errorResponse(__('You are unauthorized to access this account.'), 404);
                        }

                        return $this->errorResponse(__('You are unauthorized to access this account.'), 404);
                    } else {
                        if (session()->get("locale") == "ar") {
                            return $this->errorResponse(__('كلمة سر خاطئة'), 404);
                        }
                        return $this->errorResponse(__('Incorrect Password'), 404);
                    }
                }
                if (session()->get("locale") == "ar") {
                    return $this->errorResponse(__('أنت غير مسجل معنا. يرجى الاشتراك'), 404);
                }
                return $this->errorResponse(__('You are not registered with us. Please sign up.'), 404);
            } else {
                if (session()->get("locale") == "ar") {
                    return $this->errorResponse(__('أنت غير مسجل معنا. يرجى الاشتراك.'), 404);
                }
                return $this->errorResponse(__('Invalid email or phone number'), 404);
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Verify Login user via Phone number and create token
     *
     */
    public function verifyPhoneLoginOtp(Request $request, $domain = '')
    {
        try {
            $username = $request->username;
            $dialCode = $request->dialCode;
            $phone_number = preg_replace('/\D+/', '', $username);
            $user = User::where('dial_code', $dialCode)->where('phone_number', $phone_number)->first();
            if (!$user) {
                $errors['error'] = __('Your phone number is not registered');
                return response()->json($errors, 422);
            }
            $currentTime = Carbon::now()->toDateTimeString();
            $message = 'Account verified successfully.';

            if ($user->phone_token != $request->verifyToken) {
                return $this->errorResponse(__('OTP is not valid'), 404);
            }
            if ($currentTime > $user->phone_token_valid_till) {
                return $this->errorResponse(__('OTP has been expired.'), 404);
            }
            $request->request->add(['phone_number' => $phone_number]);
            return $this->proceedToPhoneLogin($request);
        } catch (Exception $ex) {
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }

    /*** register user via phone number ***/
    public function registerViaPhone($req, $domain = '')
    {
        try {
            $user = new User();
            $country = Country::where('code', strtoupper($req->countryData))->first();
            $client_timezone = Client::where('id', '>', 0)->value('timezone');
            // $emailCode = mt_rand(100000, 999999);
            $email = ''; //'ro_'.Carbon::now()->timestamp . '.' . uniqid() . '@royoorders.com';
            $user->type = 1;
            $user->status = 1;
            $user->role_id = 1;
            $user->name = 'RO' . substr($req->phone_number, -6);
            $user->email = $email; //$req->email;
            $user->is_email_verified = 0;
            $user->is_phone_verified = 0;
            $user->country_id = $country->id;
            $user->phone_token = $req->phoneCode;
            $user->dial_code = $req->dialCode;
            // $user->email_token = $emailCode;
            $user->phone_number = $req->phone_number;
            $user->phone_token_valid_till = $req->sendTime;
            $user->timezone = $client_timezone;
            // $user->email_token_valid_till = $sendTime;
            // $user->password = Hash::make($req->password);
            $user->save();

            $wallet = $user->wallet;
            $userRefferal = new UserRefferal();
            $userRefferal->refferal_code = $this->randomData("user_refferals", 8, 'refferal_code');
            if ($req->refferal_code != null) {
                $userRefferal->reffered_by = $req->refferal_code;
            }
            $userRefferal->user_id = $user->id;
            $userRefferal->save();
            if ($user->id > 0) {
                if ($req->refferal_code != null) {
                    $refferal_amounts = ClientPreference::first();
                    if ($refferal_amounts) {
                        if ($refferal_amounts->reffered_by_amount != null && $refferal_amounts->reffered_to_amount != null) {
                            $reffered_by = UserRefferal::where('refferal_code', $req->refferal_code)->first();
                            $user_refferd_by = $reffered_by->user_id;
                            $user_refferd_by = User::where('id', $reffered_by->user_id)->first();
                            if ($user_refferd_by) {
                                //user reffered by amount
                                $wallet_user_reffered_by = $user_refferd_by->wallet;
                                $wallet_user_reffered_by->depositFloat($refferal_amounts->reffered_by_amount, ['Referral code used by <b>' . $req->phone_number . '</b>']);
                                $wallet_user_reffered_by->balance;
                                //user reffered to amount
                                $wallet->depositFloat($refferal_amounts->reffered_to_amount, ['You used referral code of <b>' . $user_refferd_by->name . '</b>']);
                                $wallet->balance;
                            }
                        }
                    }
                }
                Session::forget('referrer');
            }

            return $this->successResponse($user, 'Successfully registered');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function postVendorregister(Request $request, $domain = '')
    {
        try {

            $getAdditionalPreference = getAdditionalPreference(['is_gst_required_for_vendor_registration', 'is_baking_details_required_for_vendor_registration', 'is_advance_details_required_for_vendor_registration', 'is_vendor_category_required_for_vendor_registration']);
            // dd($getAdditionalPreference);
            DB::beginTransaction();
            $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();
            if (empty($request->input('user_id'))) {
                if ($vendor_registration_documents->count() > 0) {
                    $rules_array = [
                        'address' => 'required',
                        'full_name' => 'required',
                        'title' => 'required',
                        'email' => 'required|email|unique:users',
                        // 'vendor_registration_document.*.did_visit' => 'required',
                        'password' => 'required|string|min:6|max:50',
                        'confirm_password' => 'required|same:password',
                        'name' => 'required|string|max:150', //|unique:vendors
                        'phone_number' => 'required|string|min:6|max:15|unique:users',
                        'check_conditions' => 'required',
                        'city' => 'required',
                        'pincode' => 'required',
                        'state' => 'required',
                        'country' => 'required',
                    ];
                    foreach ($vendor_registration_documents as $vendor_registration_document) {
                        if ($vendor_registration_document->is_required == 1) {
                            $rules_array[$vendor_registration_document->primary->slug] = 'required';
                        }
                    }

                    $request->validate(
                        // [
                        //     'address' => 'required',
                        //     'full_name' => 'required',
                        //     'email' => 'required|email|unique:users',
                        //     'vendor_registration_document.*.did_visit' => 'required',
                        //     'password' => 'required|string|min:6|max:50',
                        //     'confirm_password' => 'required|same:password',
                        //     'name' => 'required|string|max:150|unique:vendors',
                        //     'phone_number' => 'required|string|min:6|max:15|unique:users',
                        //     'check_conditions' => 'required',
                        // ],
                        $rules_array,
                        ['check_conditions.required' => __('Please indicate that you have read and agree to the Terms and Conditions and Privacy Policy')]
                    );
                } else {
                    $request->validate(
                        [
                            'address' => 'required',
                            'title' => 'required',
                            'full_name' => 'required',
                            'email' => 'required|email|unique:users',
                            'password' => 'required|string|min:6|max:50',
                            'confirm_password' => 'required|same:password',
                            'name' => 'required|string|max:150',
                            'phone_number' => 'required|string|min:6|max:15|unique:users',
                            'check_conditions' => 'required',
                            'city' => 'required',
                            'pincode' => 'required',
                            'state' => 'required',
                            'country' => 'required',
                        ],
                        ['check_conditions.required' => __('Please indicate that you have read and agree to the Terms and Conditions and Privacy Policy')]
                    );
                }
            } else {
                $rules_array = [
                    'address' => 'required',
                    'name' => 'required|string|max:150',
                    'check_conditions' => 'required',
                    'city' => 'required',
                    'pincode' => 'required',
                    'state' => 'required',
                    'country' => 'required',
                ];
                foreach ($vendor_registration_documents as $vendor_registration_document) {
                    if ($vendor_registration_document->is_required == 1) {
                        $rules_array[$vendor_registration_document->primary->slug] = 'required';
                    }
                }
                $request->validate(
                    $rules_array,
                    ['check_conditions.required' => __('Please indicate that you have read and agree to the Terms and Conditions and Privacy Policy')]
                );
            }



            $client_detail = Client::first();
            $client_preference = ClientPreference::first();
            if (!$request->user_id) {
                $user = new User();
                $county = Country::where('code', strtoupper($request->countryData))->first();
                $sendTime = Carbon::now()->addMinutes(10)->toDateTimeString();
                $user->type = 1;
                $user->status = 1;
                $user->role_id = 1;
                $user->is_admin = 1;
                $user->is_email_verified = 0;
                $user->is_phone_verified = 0;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->title = $request->title;
                $user->country_id = $county->id;
                $user->dial_code = $request->dialCode;
                $user->phone_token_valid_till = $sendTime;
                $user->email_token_valid_till = $sendTime;
                $user->email_token = mt_rand(100000, 999999);
                $user->phone_token = mt_rand(100000, 999999);
                $user->phone_number = $request->phone_number;
                $user->password = Hash::make($request->password);
                $user->save();
                $wallet = $user->wallet;
            } else {
                $user = User::where('id', $request->user_id)->first();
                $user->title = $request->title;
                // if user is already exists then mark as a admin
                // if( getClientPreferenceDetail()->p2p_check ) {
                //     $user->is_admin = 1;
                // }
                $user->save();
            }
            $vendor = new Vendor();
            $count = 0;


            $single_vendor_type = "delivery";
            if ($client_preference) {
                foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
                    $clientVendorTypes = $vendor_typ_key . '_check';
                    if ($client_preference->$clientVendorTypes == 1) {
                        if ($count == 0) {
                            $single_vendor_type   = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
                        }
                        $count++;
                    }
                }
            }

            if ($count > 1) {
                foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
                    $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
                    $vendor->$VendorTypesName = ($request->has($VendorTypesName) && $request->$VendorTypesName == 'on') ? 1 : 0;
                }
            } else {
                $vendor->$single_vendor_type = 1;
            }
            $vendor->logo = 'default/default_logo.png';
            $vendor->banner = 'default/default_image.png';
            if ($request->hasFile('upload_logo')) {
                $file = $request->file('upload_logo');
                $vendor->logo = Storage::disk('s3')->put('/vendor', $file, 'public');
            }
            if ($request->hasFile('upload_banner')) {
                $file = $request->file('upload_banner');
                $vendor->banner = Storage::disk('s3')->put('/vendor', $file, 'public');
            }
            $vendor->status = 0;
            $vendor->name = $request->name;
            $vendor->email = $request->email;
            $vendor->phone_no = $user->phone_number;
            $vendor->city = $request->city;
            $vendor->state = $request->state;
            $vendor->country = $request->country;
            $vendor->pincode = $request->pincode;
            $vendor->address = $request->address;
            $vendor->website = $request->website;
            $vendor->latitude = $request->latitude;
            $vendor->longitude = $request->longitude;
            $vendor->desc = $request->vendor_description;
            $vendor->slug = Str::slug($request->name, "-");
            $vendor->is_seller = $request->vendor_type ?? 0;
            $vendor->save();
            if ($request->vendor_type == 0) {
                $permission_details = PermissionsOld::whereIn('id', [1, 2, 3, 12, 17, 18, 19, 20, 21]);
            } else {
                $permission_details = PermissionsOld::whereIn('id', [1, 2, 12, 17, 18, 19, 20, 21, 28]);
            }
            $permission_details = $permission_details->get();
            if ($vendor_registration_documents->count() > 0) {
                foreach ($vendor_registration_documents as $vendor_registration_document) {
                    $doc_name = str_replace(" ", "_", $vendor_registration_document->primary->slug);
                    if ($vendor_registration_document->file_type != "Text" && $vendor_registration_document->file_type != "selector") {
                        if ($request->hasFile($doc_name)) {
                            $vendor_docs =  new VendorDocs();
                            $vendor_docs->vendor_id = $vendor->id;
                            $vendor_docs->vendor_registration_document_id = $vendor_registration_document->id;
                            $filePath = $this->folderName . '/' . Str::random(40);
                            $file = $request->file($doc_name);
                            $vendor_docs->file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                            $vendor_docs->save();
                        }
                    } else {
                        if (!empty($request->$doc_name)) {
                            $vendor_docs =  new VendorDocs();
                            $vendor_docs->vendor_id = $vendor->id;
                            $vendor_docs->vendor_registration_document_id = $vendor_registration_document->id;
                            $vendor_docs->file_name = $request->$doc_name;
                            $vendor_docs->save();
                        }
                    }
                }
            }
            UserVendor::create(['user_id' => $user->id, 'vendor_id' => $vendor->id]);
            foreach ($permission_details as $permission_detail) {
                UserPermissions::create(['user_id' => $user->id, 'permission_id' => $permission_detail->id]);
            }
            // vendor additional data
            $this->addDataSaveVendor($request, $vendor->id);

            if ($this->checkTemplateForAction(8)) {
                $this->LoginActionRecentView($user->id);
            }

            // Add vendor additional data
            $additionalData = [];
            if (@$getAdditionalPreference['is_gst_required_for_vendor_registration'] == 1) {
                $additionalData = [
                    // 'vendor_id' => $vendor->id,
                    'company_name' => $request->company_name,
                    'gst_number' => $request->gst_num_Input,
                ];
            }

            if (@$getAdditionalPreference['is_baking_details_required_for_vendor_registration'] == 1) {
                $additionalData['account_name'] = $request->account_name;
                $additionalData['bank_name'] = $request->bank_name;
                $additionalData['account_number'] = $request->account_number;
                $additionalData['ifsc_code'] = $request->ifsc_code;
            }
            // dd($additionalData);
            if (@$getAdditionalPreference['is_gst_required_for_vendor_registration'] == 1 || @$getAdditionalPreference['is_baking_details_required_for_vendor_registration'] == 1) {
                $saveVendorAdditionalInfo = VendorAdditionalInfo::updateOrCreate(
                    ['vendor_id' => $vendor->id],
                    $additionalData
                );
            }

            $content = '';
            $email_template = EmailTemplate::where('id', 1)->first();
            if ($email_template) {
                $content = $email_template->content;
                $content = str_ireplace("{title}", $user->title, $content);
                $content = str_ireplace("{email}", $user->email, $content);
                $content = str_ireplace("{address}", $vendor->address, $content);
                $content = str_ireplace("{website}", $vendor->website, $content);
                $content = str_ireplace("{description}", $vendor->desc, $content);
                $content = str_ireplace("{vendor_name}", $vendor->name, $content);
                $content = str_ireplace("{phone_no}", $user->phone_number, $content);
            }
            $email_data = [
                'title' => $user->title,
                'email' => $user->email,
                'powered_by' => url('/'),
                'banner' => $vendor->banner,
                'website' => $vendor->website,
                'address' => $vendor->address,
                'vendor_logo' => $vendor->logo,
                'vendor_name' => $vendor->name,
                'description' => $vendor->desc,
                'phone_no' => $user->phone_number,
                'email_template_content' => $content,
                'subject' => $email_template->subject,
                'client_name' => $client_detail->name,
                'customer_name' => ucwords($user->name),
                'logo' => $client_detail->logo['original'],
                'mail_from' => $client_preference->mail_from,
            ];
            $admin_email_data = [
                'title' => $user->title,
                'email' => $user->email,
                'powered_by' => url('/'),
                'banner' => $vendor->banner,
                'website' => $vendor->website,
                'address' => $vendor->address,
                'vendor_logo' => $vendor->logo,
                'vendor_name' => $vendor->name,
                'description' => $vendor->desc,
                'phone_no' => $user->phone_number,
                'email_template_content' => $content,
                'client_name' => $client_detail->name,
                'subject' => 'New Vendor Registration',
                'customer_name' => ucwords($user->name),
                'logo' => $client_detail->logo['original'],
                'mail_from' => $client_preference->mail_from,
            ];
            try {
                dispatch(new \App\Jobs\sendVendorRegistrationEmail($email_data))->onQueue('verify_email');
                //    dispatch(new \App\Jobs\sendVendorRegistrationEmail($admin_email_data))->onQueue('verify_email');
            } catch (Exception $e) {
            }
            DB::commit();
            $is_seller = $request->vendor_type;
            $msg_text = isset($is_seller) && $is_seller == 0 ? 'Vendor' : 'Seller';
            return response()->json([
                'status' => 'success',
                'message' => $msg_text . ' Registration Created Successfully!',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function addDataSaveVendor(Request $request, $vendor_id)
    {

        $vendor = Vendor::where('id', $vendor_id)->firstOrFail();
        $VendorController = new VendorController();

        $request->merge(["return_json" => 1]);
        $VendorConfigrespons = $VendorController->updateConfig($request, '', $vendor_id)->getData(); //$this->updateConfig($vendor_id);
        // pr($VendorConfigrespons);
        if ($request->has('can_add_category')) {
            $vendor->add_category = $request->can_add_category == 'on' ? 1 : 0;
        }
        if ($request->has('assignTo')) {
            $vendor->vendor_templete_id = $request->assignTo;
        }

        $vendor->save();
        if ($request->has('category_ids')) {
            foreach ($request->category_ids as $category_id) {
                VendorCategory::create(['vendor_id' => $vendor_id, 'category_id' => $category_id, 'status' => '1']);
            }
        }
        if ($request->has('selectedCategories')) {
            foreach ($request->selectedCategories as $category_id) {
                VendorCategory::create(['vendor_id' => $vendor_id, 'category_id' => $category_id, 'status' => '1']);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Vendor created Successfully!',
            'data' => $VendorConfigrespons
        ]);
        // pr($VendorConfigrespons);
    }
    public function logout()
    {
        Auth::logout();
        if (!empty(Session::get('current_fcm_token'))) {
            UserDevice::where('device_token', Session::get('current_fcm_token'))->delete();
            Session::forget('current_fcm_token');
        }
        return redirect()->route('customer.login');
    }



    # zillowGetData

    public function zillowGetData()
    {
        $params = (array('address' => '7356 CARTER AVE', 'citystatezip' => 'NEWARK'));

        $params['zws-id'] = 'X1-ZWz16b0yk0045n_8mfo0';
        $url = 'http://www.zillow.com/webservice/GetSearchResults.htm?' . http_build_query($params);
        $result = new SimpleXMLElement($url, 0, true);
        dd($params);

        // save this in object so that we could reuse it
        if (isset($result->response->results->result->zpid)) {
            $this->zpid = (string)$result->response->results->result->zpid;
        }
        return $result->response;
    }

    // public function getDatazillo($params);
    // {
    //     $params['zws-id'] = $this->zws_id;
    // 		$url = 'http://www.zillow.com/webservice/GetSearchResults.htm?' . http_build_query($params);
    // 		$result = new SimpleXMLElement($url, 0, true);

    // 		// save this in object so that we could reuse it
    // 		if ( isset($result->response->results->result->zpid) ) {
    // 			$this->zpid = (string)$result->response->results->result->zpid;
    // 		}

    // 		return $result;
    // }
}
