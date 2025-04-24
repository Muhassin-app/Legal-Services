<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Support\Facades\DB;
use Password;
use JWT\Token;
use Validation;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GCLIENT;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Controllers\Front\CustomerAuthController;
use App\Http\Requests\{LoginRequest, SignupRequest};
use App\Http\Controllers\Client\VendorController;
use App\Models\{User, UserVendor, Client, ClientPreference, BlockedToken, Otp, Country, ShowSubscriptionPlanOnSignup, UserDevice, UserVerification, ClientLanguage, CartProduct, Cart, UserRefferal, EmailTemplate, SmsTemplate, UserRegistrationDocuments, UserDocs, Vendor, PermissionsOld, UserPermissions, Type, Category, SubscriptionInvoicesUser, VendorCategory, UserAddress, UserPaymentCards, UserAllergicItem};
use Log;
use App\Http\Traits\CustomerSignupSuccessEmailTrait;
use App\Http\Traits\InfluencerTrait;

class AuthController extends BaseController
{
    use ApiResponser, InfluencerTrait;
    use CustomerSignupSuccessEmailTrait;
    /**
     * Get Country List
     * * @return country array
     */
    private $folderName = '/user/document';

    public function __construct()
    {
        $code = Client::orderBy('id', 'asc')->value('code');
        $this->folderName = '/' . $code . '/user/document';
    }
    public function countries(Request $request)
    {
        $country = Country::select('id', 'code', 'name', 'nicename', 'phonecode')->get();
        return response()->json([
            'data' => $country
        ]);
    }

    /**
     * Login user and create token
     *
     */
    public function login(LoginRequest $loginReq)
    {
        $phoneCheck = 0;
        $errors = array();
        if (!is_numeric($loginReq->email)) {
            $user = User::with('country')->where('email', $loginReq->email)->first();
            if (!$user) {
                $errors['error'] = __('Invalid email');
                return response()->json($errors, 422);
            }

            if (!Auth::attempt(['email' => $loginReq->email, 'password' => $loginReq->password])) {
                $errors['error'] = __('Invalid password');
                return response()->json($errors, 422);
            }
        } else {

            $user  = User::with('country')->where('phone_number', $loginReq->email)->first();
            if (!$user) {
                $errors['error'] = __('Invalid phone number');
                return response()->json($errors, 422);
            }
            $phoneCheck = 1;
            Auth::login($user);
            $loginReq->merge(['type' => 'phone', 'dial_code' => $loginReq->dialCode, 'phone_number' => $loginReq->email, 'sendSms' => 1]);
            $this->sendToken($loginReq);
        }

        $user = Auth::user();
        if ($user->status != 1) {
            $errors['error'] = __('Lawyer is inactive. Please wait, the admin will approve you shortly.');
            return response()->json($errors, 422);
        }
        $prefer = ClientPreference::select('theme_admin', 'distance_unit', 'map_provider', 'date_format', 'time_format', 'map_key', 'sms_provider', 'verify_email', 'verify_phone', 'app_template_id', 'web_template_id')->first();
        $verified['is_email_verified'] = $user->is_email_verified;
        $verified['is_phone_verified'] = $user->is_phone_verified;
        $token1 = new Token;
        $token = $token1->make([
            'key' => 'royoorders-jwt',
            'issuer' => 'royoorders.com',
            'expiry' => strtotime('+1 month'),
            'issuedAt' => time(),
            'algorithm' => 'HS256',
        ])->get();
        $token1->setClaim('user_id', $user->id);
        try {
            Token::validate($token, 'secret');
        } catch (\Exception $e) {
        }
        $user_refferal = UserRefferal::where('user_id', $user->id)->first();

        // $device = UserDevice::where('user_id', $user->id)->first();
        // if(!$device){
        //     $device = new UserDevice();
        //     $device->user_id = $user->id;
        // }
        // $device->device_type = $loginReq->device_type;
        // $device->device_token = $loginReq->device_token;
        // $device->access_token = $token;
        // $device->save();

        //check login from individual vendor app
        $fromVendorAppLogin = 0;
        if (!empty($loginReq->is_vendor_app)) {
            $fromVendorAppLogin = 1;
        }

        if (!empty($loginReq->fcm_token)) {
            $device = UserDevice::updateOrCreate(
                ['device_token' => $loginReq->fcm_token],
                [
                    'user_id' => $user->id,
                    'device_type' => $loginReq->device_type,
                    'access_token' => $token,
                    'is_vendor_app' => $fromVendorAppLogin
                ]
            );
        } else {
            $device = UserDevice::updateOrCreate(
                ['device_token' => $loginReq->device_token],
                [
                    'user_id' => $user->id,
                    'device_type' => $loginReq->device_type,
                    'access_token' => $token,
                    'is_vendor_app' => $fromVendorAppLogin
                ]
            );
        }


        $user->auth_token = $token;
        $user->save();

        $user_cart = Cart::where('user_id', $user->id)->first();
        if ($user_cart) {
            $unique_identifier_cart = Cart::where('unique_identifier', $loginReq->device_token)->first();
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
            Cart::where('unique_identifier', $loginReq->device_token)->update(['user_id' => $user->id,  'unique_identifier' => '']);
        }
        $checkSystemUser = $this->checkCookies($user->id);
        $data['id'] = $user->id;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['auth_token'] =  $token;
        $data['source'] = $user->image;
        $data['verify_details'] = $verified;
        $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
        $data['is_admin'] = $user_vendor_count > 0 ? 1 : 0;
        $data['is_superadmin'] = $user->is_superadmin ?? 0;
        //$data['is_admin'] = $user->is_admin;
        $data['client_preference'] = $prefer;
        $data['dial_code'] = $user->dial_code;
        $data['phone_number'] = $user->phone_number;
        $data['cca2'] = $user->country ? $user->country->code : '';
        $data['callingCode'] = $user->country ? $user->country->phonecode : '';
        $data['refferal_code'] = $user_refferal ? $user_refferal->refferal_code : '';
        $data['is_phone'] = $phoneCheck ?? 0;
        return response()->json(['data' => $data]);
    }

    public function vendorlogin(LoginRequest $loginReq)
    {
        //dd($loginReq->all());
        $errors = array();
        $user = User::with('country')->where('email', $loginReq->email)->first();
        if (!$user) {
            $errors['error'] = __('Invalid email');
            return response()->json($errors, 422);
        }
        if (!Auth::attempt(['email' => $loginReq->email, 'password' => $loginReq->password])) {
            $errors['error'] = __('Invalid password');
            return response()->json($errors, 422);
        }
        $user = Auth::user();
        //check vendor
        $checkvendor = UserVendor::where('user_id', $user->id)->first();
        if (!$checkvendor) {
            if ($user->is_admin == 0) {
                $errors['error'] = __('User is not register as vendor');
            } else {
                $errors['error'] = __('User is not approved by admin');
            }
            return response()->json($errors, 422);
        }
        $prefer = ClientPreference::select('theme_admin', 'distance_unit', 'map_provider', 'date_format', 'time_format', 'map_key', 'sms_provider', 'verify_email', 'verify_phone', 'app_template_id', 'web_template_id')->first();
        $verified['is_email_verified'] = $user->is_email_verified;
        $verified['is_phone_verified'] = $user->is_phone_verified;
        $token1 = new Token;
        $token = $token1->make([
            'key' => 'royoorders-jwt',
            'issuer' => 'royoorders.com',
            'expiry' => strtotime('+1 month'),
            'issuedAt' => time(),
            'algorithm' => 'HS256',
        ])->get();
        $token1->setClaim('user_id', $user->id);
        try {
            Token::validate($token, 'secret');
        } catch (\Exception $e) {
        }
        $user_refferal = UserRefferal::where('user_id', $user->id)->first();
        if (!empty($loginReq->fcm_token)) {
            $device = UserDevice::updateOrCreate(
                ['device_token' => $loginReq->fcm_token],
                [
                    'user_id' => $user->id,
                    'device_type' => $loginReq->device_type,
                    'access_token' => $token
                ]
            );
        } else {
            $device = UserDevice::updateOrCreate(
                ['device_token' => $loginReq->device_token],
                [
                    'user_id' => $user->id,
                    'device_type' => $loginReq->device_type,
                    'access_token' => $token
                ]
            );
        }


        $user->auth_token = $token;
        $user->save();

        $user_cart = Cart::where('user_id', $user->id)->first();
        if ($user_cart) {
            $unique_identifier_cart = Cart::where('unique_identifier', $loginReq->device_token)->first();
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
            Cart::where('unique_identifier', $loginReq->device_token)->update(['user_id' => $user->id,  'unique_identifier' => '']);
        }
        $checkSystemUser = $this->checkCookies($user->id);
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['auth_token'] =  $token;
        $data['source'] = $user->image;
        $data['verify_details'] = $verified;
        $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
        $data['is_admin'] = $user_vendor_count > 0 ? 1 : 0;
        $data['is_superadmin'] = $user->is_superadmin ?? 0;
        //$data['is_admin'] = $user->is_admin;
        $data['client_preference'] = $prefer;
        $data['dial_code'] = $user->dial_code;
        $data['phone_number'] = $user->phone_number;
        $data['cca2'] = $user->country ? $user->country->code : '';
        $data['callingCode'] = $user->country ? $user->country->phonecode : '';
        $data['refferal_code'] = $user_refferal ? $user_refferal->refferal_code : '';
        return response()->json(['data' => $data]);
    }

    /**
     * User registraiotn
     * @return [status, email, need_email_verify, need_phone_verify]
     */
    public function signup(Request $signReq)
    {
        $preferences = ClientPreference::first();
        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        $rules = [
            'dial_code'   => 'required_with:phone_number|string',
            'device_type'   => 'required|string',
            'device_token'  => 'required|string',
            'country_code'  => 'required|string',
            'name'          => 'required|string|min:3|max:50',
            'password'      => 'required|string|min:6|max:50',
            'refferal_code' => 'nullable|exists:user_refferals,refferal_code',
        ];
        if ($signReq->is_lawyer) {
            $rules += [
                'last_name' => 'string',
                // 'enrollment_no' => 'required|integer',
                'registeration-document' => 'required|file|mimes:pdf',
            ];
            unset($rules['device_type'], $rules['device_token']);
        }
        if ($preferences->verify_email == 1 && !empty($signReq->email)) {
            $rules['email'] = 'required_without:phone_number|email|unique:users';
        }
        $signup_through_phone = 0;
        if ($preferences->verify_phone == 1 && !empty($signReq->phone_number)) {
            if ($signReq->input('phone_number')) {
                $signup_through_phone = 1;
            }
            $rules['phone_number'] = 'required_without:email|string|min:7|max:15|unique:users';
        }
        if ($signReq->has('has_address') && $signReq->has_address == 1) {
            $rules['address_type']  = 'required';
            $rules['address']       = 'required';
            $rules['street']        = 'required';
            $rules['latitude']      = 'required';
            $rules['longitude']     = 'required';
            $rules['house_number']  = 'required';
            $rules['pincode']       = 'required';
        }
        if ($signReq->has('has_Payment_card') && $signReq->has_Payment_card == 1) {
            $rules['card_number']        = 'required';
            $rules['card_holder_name']   = 'required';
            $rules['card_cvv']           = 'required';
            $rules['expiry_date']        = 'required';
        }
        foreach ($user_registration_documents as $user_registration_document) {
            if ($user_registration_document->is_required == 1) {
                $rules[$user_registration_document->primary->slug] = 'required';
            }
        }

        if ((empty($signReq->email)) && (empty($signReq->phone_number))) {
            $rules['email']  = 'required';
            $rules['phone_number']  = 'required';
        } else {
            if (!empty($signReq->email) && ($preferences->verify_email == 0)) {
                $rules['email'] = 'email|unique:users';
            }

            if (!empty($signReq->phone_number) && ($preferences->verify_phone == 0)) {
                $rules['phone_number'] = 'string|min:7|max:15|unique:users';
            }
        }
        $message['email.required'] = __('The email or phone number field is required.');
        $message['phone_number.required'] = __('The email or phone number field is required.');
        $validator = Validator::make($signReq->all(), $rules, $message);

        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                $errors['error'] = __($error_value[0]);
                return response()->json($errors, 422);
            }
        }

        if (!empty($signReq->email)) {
            $userEmailCheck = User::where(['email' => $signReq->email])->first();
            if ($userEmailCheck) {
                return response()->json(['error' => 'The email has already been taken.'], 422);
            }
        }
        $client_timezone = Client::where('id', '>', 0)->value('timezone');

        $user = new User();

        foreach ($signReq->only('name', 'country_id', 'phone_number', 'dial_code') as $key => $value) {
            $user->{$key} = $value;
        }
        $country_detail = Country::where('code', $signReq->country_code)->first();
        $email = (!empty($signReq->email)) ? $signReq->email : ''; //('ro_'.Carbon::now()->timestamp . '.' . uniqid() . '@royoorders.com');
        $phoneCode = getUserToken($preferences)['otp'];
        $emailCode = getUserToken($preferences)['otp'];
        $sendTime = Carbon::now()->addMinutes(10)->toDateTimeString();
        $user->password = Hash::make($signReq->password);
        $user->type = 1;
        $user->status = 1;
        $user->role_id = 1;
        $user->email = $email;
        $user->is_email_verified = 0;
        $user->is_phone_verified = 0;
        $user->phone_token = $phoneCode;
        $user->email_token = $emailCode;
        $user->country_id = $country_detail->id;
        $user->phone_token_valid_till = $sendTime;
        $user->email_token_valid_till = $sendTime;
        $user->timezone = $client_timezone;
        if ($signReq->is_lawyer) {
            $user->status = 0;
            $user->is_admin = 1;
            $user->last_name = @$signReq->last_name;
            $user->enrollment_no = rand(100000, 999999);
            $file = $signReq->file('registeration-document');
            $path = Storage::disk('s3')->put($this->folderName, $file, 'public');
            $user->document = $path;
            $user->assignRole(7);
        }



        if ($signReq->hasFile('image')) {
            $file = $signReq->file('image');
            $user->image = Storage::disk('s3')->put($this->folderName, $file, 'public');
        }
        $user->save();
        // user upload document
        if ($user_registration_documents->count() > 0) {
            foreach ($user_registration_documents as $user_registration_document) {
                $doc_name = str_replace(" ", "_", $user_registration_document->primary->slug);
                if ($user_registration_document->file_type != "Text" && $user_registration_document->file_type != "selector") {
                    if ($signReq->hasFile($doc_name)) {
                        $vendor_docs =  new UserDocs();
                        $vendor_docs->user_id = $user->id;
                        $vendor_docs->user_registration_document_id = $user_registration_document->id;
                        $vendor_docs->file_original_name = $signReq->file($doc_name)->getClientOriginalName();
                        $filePath = $this->folderName . '/' . Str::random(40);
                        $file = $signReq->file($doc_name);
                        $vendor_docs->file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                        $vendor_docs->save();
                    }
                } else {
                    if (!empty($signReq->$doc_name)) {
                        $vendor_docs =  new UserDocs();
                        $vendor_docs->user_id = $user->id;
                        $vendor_docs->user_registration_document_id = $user_registration_document->id;
                        $vendor_docs->file_name = $signReq->$doc_name;
                        $vendor_docs->save();
                    }
                }
            }
        }
        $user_id = $user->id;
        if ($signReq->has('has_address') && $signReq->has_address == 1) {
            $address = new UserAddress();
            $address->user_id = $user->id;
            $address->is_primary =  1;
            $signReq->request->add(['type' => ($signReq->has('address_type') && $signReq->address_type < 3) ? $signReq->address_type : 3]);
            //address_type ,  address, street,latitude,longitude,house_number,pincode
            foreach ($signReq->only('address', 'house_number', 'street', 'city', 'state', 'latitude', 'longitude', 'pincode', 'phonecode', 'country_code', 'type', 'country', 'type_name', 'extra_instruction') as $key => $value) {
                $address[$key] = $value;
            }
            $address->save();
        }
        if ($signReq->has('has_Payment_card') && $signReq->has_Payment_card == 1) {
            $PaymentCards           = new UserPaymentCards();
            $PaymentCards->user_id  = $user->id;
            // 'card_number', 'card_holder_name','card_cvv', 'expiry_date'
            foreach ($signReq->only('card_number', 'card_holder_name', 'card_cvv', 'expiry_date') as $key => $value) {
                $PaymentCards[$key] = $value;
            }
            $PaymentCards->save();
        }
        if (@$signReq->kyc) {
            InfluencerTrait::saveKycData($signReq, $user_id);
        }

        $user_registration_documents = UserRegistrationDocuments::with(['user_document' => function ($q) use ($user_id) {
            $q->where('user_id', $user_id);
        }, 'primary'])->get();
        $response['user_document'] = $user_registration_documents;
        $response['app_hash_key'] = (!empty($signReq->app_hash_key)) ? $signReq->app_hash_key : '';

        //end user upload document
        $wallet = $user->wallet;
        $userRefferal = new UserRefferal();
        $userRefferal->refferal_code = $this->randomData("user_refferals", 8, 'refferal_code');
        if ($signReq->refferal_code != null) {
            $userRefferal->reffered_by = $signReq->refferal_code;
        }
        $userRefferal->user_id = $user->id;
        $userRefferal->save();

        $user_cart = Cart::where('user_id', $user->id)->first();
        if ($user_cart) {
            $unique_identifier_cart = Cart::where('unique_identifier', $signReq->device_token)->first();
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
            Cart::where('unique_identifier', $signReq->device_token)->update(['user_id' => $user->id,  'unique_identifier' => '']);
        }
        $token1 = new Token;
        $token = $token1->make([
            'key' => 'royoorders-jwt',
            'issuer' => 'royoorders.com',
            'expiry' => strtotime('+1 month'),
            'issuedAt' => time(),
            'algorithm' => 'HS256',
        ])->get();
        $token1->setClaim('user_id', $user->id);
        $user->auth_token = $token;
        $user->custom_allergic_items = $signReq->custom_allergic_items ?? null;
        $user->save();

        if ($signReq->allergic_item_ids && count($signReq->allergic_item_ids)) {
            foreach ($signReq->allergic_item_ids as $key => $id) {
                $data[$key] = [
                    'user_id' => $user->id,
                    'allergic_item_id' => $id,
                ];
            }
            UserAllergicItem::insert($data);
        }

        if ($user->id > 0) {
            if ($signReq->refferal_code) {
                $refferal_amounts = ClientPreference::first();

                $dispatch_domain = $this->checkIfLastMileOn();
                $postdata = [
                    'refferal_code' => $signReq->refferal_code,
                    'user_name' => $user->name ?? ''
                ];
                if ($dispatch_domain && $dispatch_domain != false) {
                    $client = new GCLIENT([
                        'headers' => [
                            'personaltoken' => $dispatch_domain->pickup_delivery_service_key,
                            'shortcode' => $dispatch_domain->pickup_delivery_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);
                    $url = $dispatch_domain->pickup_delivery_service_key_url;
                    $res = $client->post(
                        $url . '/api/auth/get-driver-refferal',
                        ['form_params' => ($postdata)]
                    );
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['message'] == 'success') {
                        $refferal_amount = $response['refferal_amount'];
                        $wallet->deposit($refferal_amount, ['You used referal code of <b>' . $response['refer_by_name'] . '</b>']);
                        $wallet->balance;
                    }
                }

                if ($refferal_amounts) {
                    if ($refferal_amounts->reffered_by_amount != null && $refferal_amounts->reffered_to_amount != null) {
                        $reffered_by = UserRefferal::where('refferal_code', $signReq->refferal_code)->first();
                        $user_refferd_by_user = $reffered_by->user_id ?? 0;
                        $user_refferd_by = User::where('id', $user_refferd_by_user)->first();
                        if ($user_refferd_by) {
                            //user reffered by amount
                            $wallet_user_reffered_by = $user_refferd_by->wallet;
                            $wallet_user_reffered_by->depositFloat($refferal_amounts->reffered_by_amount, ['Referral code used by <b>' . $signReq->name . '</b>']);
                            $wallet_user_reffered_by->balance;
                            //user reffered to amount
                            $wallet->depositFloat($refferal_amounts->reffered_to_amount, ['You used referal code of <b>' . $user_refferd_by->name . '</b>']);
                            $wallet->balance;
                        }
                    }
                }
            }

            $checkSystemUser = $this->checkCookies($user->id);
            $response['status'] = 'Success';
            $response['name'] = $user->name;
            $response['source'] = $user->image;
            $response['id'] = $user->id;
            $response['auth_token'] =  $token;
            $response['email'] = $user->email;
            $response['dial_code'] = $user->dial_code;
            $response['phone_number'] = $user->phone_number;
            $verified['is_email_verified'] = 0;
            $verified['is_phone_verified'] = 0;
            $prefer = ClientPreference::select(
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
            $response['verify_details'] = $verified;
            $response['cca2'] = $user->country ? $user->country->code : '';
            $preferData['map_key'] = $prefer->map_key;
            $preferData['theme_admin'] = $prefer->theme_admin;
            $preferData['date_format'] = $prefer->date_format;
            $preferData['time_format'] = $prefer->time_format;
            $preferData['map_provider'] = $prefer->map_provider;
            $preferData['sms_provider'] = $prefer->sms_provider;
            $preferData['verify_email'] = $prefer->verify_email;
            $preferData['verify_phone'] = $prefer->verify_phone;
            $preferData['distance_unit'] = $prefer->distance_unit;
            $preferData['app_template_id'] = $prefer->app_template_id;
            $preferData['web_template_id'] = $prefer->web_template_id;

            $preferData['show_subscription_plan_popup_signup'] = 0;
            $showSubscriptionPlan = ShowSubscriptionPlanOnSignup::find(1);
            if (@$showSubscriptionPlan->show_plan_customer == 1 && @$showSubscriptionPlan->every_sign_up == 1) {
                $preferData['show_subscription_plan_popup_signup'] = 1;
            }
            $response['client_preference'] = $preferData;
            $response['refferal_code'] = $userRefferal ? $userRefferal->refferal_code : '';

            // $user_device[] = [
            //     'access_token' => '',
            //     'user_id' => $user->id,
            //     'device_type' => $signReq->device_type,
            //     'device_token' => $signReq->device_token,
            // ];
            // UserDevice::insert($user_device);

            //check login from individual vendor app
            $fromVendorAppLogin = 0;
            if (!empty($signReq->is_vendor_app)) {
                $fromVendorAppLogin = 1;
            }

            if (!empty($signReq->fcm_token)) {
                $user_device = UserDevice::updateOrCreate(
                    ['device_token' => $signReq->fcm_token],
                    [
                        'user_id' => $user->id,
                        'device_type' => $signReq->device_type,
                        'access_token' => $token,
                        'is_vendor_app' => $fromVendorAppLogin
                    ]
                );
            } else {
                $user_device = UserDevice::updateOrCreate(
                    ['device_token' => $signReq->device_token],
                    [
                        'user_id' => $user->id,
                        'device_type' => $signReq->device_type,
                        'access_token' => $token,
                        'is_vendor_app' => $fromVendorAppLogin
                    ]
                );
            }

            ####################################################
            ## if p2p is enable then register user as a admin ##
            ####################################################
            if (getClientPreferenceDetail()->p2p_check) {

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

                $response['vendor_id'] = $vendor->id;
                $p2p_type = Type::where('service_type', 'p2p')->first();
                if (!empty($p2p_type)) {
                    $category_id = Category::where('type_id', $p2p_type->id)->get();
                    $categories_ids = [];

                    if (!empty($category_id)) {
                        foreach ($category_id as $key => $val) {
                            $categories_ids[] = $val->id;
                        }
                    }
                    $signReq->request->add(['selectedCategories' => $categories_ids]);
                }

                $this->addDataSaveVendor($signReq, $vendor->id);
            }

            if (!empty($prefer->sms_key) && !empty($prefer->sms_secret) && !empty($prefer->sms_from)) {
                $response['send_otp'] = 1;
                if ($user->dial_code == "971") {
                    $to = '+' . $user->dial_code . "0" . $user->phone_number;
                } else {
                    $to = '+' . $user->dial_code . $user->phone_number;
                }
                $provider = $prefer->sms_provider;
                // $body = "Dear " . ucwords($user->name) . ", Thanks for creating an account with us!";
                // $body = "Dear " . ucwords($user->name) . ", Please enter OTP " . $phoneCode . " to verify your account.".((!empty($signReq->app_hash_key))?" ".$signReq->app_hash_key:'');
                // $keyData = ['{user_name}'=>ucwords($user->name)];
                // $body = sendSmsTemplate('user-signup-sms',$keyData);
                // $send = $this->sendSms($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
            }
            $response['signup_through_phone'] = $signup_through_phone;

            return response()->json(['data' => $response]);
        } else {
            $errors['errors']['user'] = 'Something went wrong. Please try again.';
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendToken(Request $request, $domain = '', $uid = 0)
    {
        try {
            $user = User::where('id', Auth::user()->id)->first();
            if (!$user) {
                return response()->json(['error' => __('User not found.')], 404);
            }
            if ($user->is_email_verified == 1 && $user->is_phone_verified == 1) {
                return response()->json(['message' => __('Account already verified.')], 200);
            }
            $notified = 1;
            $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
            $data = ClientPreference::select('sms_credentials', 'sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
            $newDateTime = Carbon::now()->addMinutes(10)->toDateTimeString();
            if ($request->type == "phone") {
                if ($user->is_phone_verified == 0 || $request->sendSms == 1) {
                    $otp = getUserToken($data)['otp'];
                    $user->phone_token = $otp;
                    $user->phone_token_valid_till = $newDateTime;
                    $user->save();
                    $provider = $data->sms_provider;
                    $to = '+' . $request->dial_code . $request->phone_number;
                    // $body = "Dear " . ucwords($user->name) . ", Please enter OTP " . $otp . " to verify your account.";
                    $keyData = ['{user_name}' => ucwords($user->name), '{otp_code}' => $otp];
                    $body = sendSmsTemplate('verify-account', $keyData);
                    if (!empty($provider)) {
                        if (getUserToken($data)['status']) {
                            $send = $this->sendSmsNew($provider, $data->sms_key, $data->sms_secret, $data->sms_from, $to, $body);
                        } else {
                            $send = 1;
                        }
                        if ($send == 1) {
                            $message = __('An otp has been sent to your phone. Please check.');
                            return $this->successResponse([], $message);
                        }
                    } else {
                        return $this->errorResponse(__('Provider service is not configured. Please contact administration.'), 404);
                    }
                }
            } else {
                if (!$user->is_email_verified == 0) {
                    $otp = getUserToken($data)['otp'];
                    $user->email_token = $otp;
                    $user->email_token_valid_till = $newDateTime;
                    $user->save();
                    if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
                        $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
                        $client_name = $client->name;
                        $mail_from = $data->mail_from;
                        $sendto = $user->email;
                        $email_template_content = '';
                        $email_template = EmailTemplate::where('id', 2)->first();
                        if ($email_template) {
                            $email_template_content = $email_template->content;
                            $email_template_content = str_ireplace("{code}", $otp, $email_template_content);
                            $email_template_content = str_ireplace("{customer_name}", ucwords($user->name), $email_template_content);
                        }
                        $data = [
                            'code' => $otp,
                            'link' => "link",
                            'mail_from' => $mail_from,
                            'email' => $request->email,
                            'client_name' =>  $client->name,
                            'logo' => $client->logo['original'],
                            'subject' => $email_template->subject,
                            'customer_name' => ucwords($user->name),
                            'email_template_content' => $email_template_content,
                        ];
                        dispatch(new \App\Jobs\SendVerifyEmailJob($data))->onQueue('verify_email');
                        $message = __('An otp has been sent to your email. Please check.');
                        return $this->successResponse([], $message);
                    } else {
                        return $this->errorResponse(__('Provider service is not configured. Please contact administration.'), 404);
                    }
                }
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyToken(Request $request, $domain = '')
    {
        try {
            $user = User::where('id', Auth::user()->id)->first();
            if (!$user || !$request->has('type')) {
                return $this->errorResponse(__('User not found.'), 404);
            }
            $currentTime = Carbon::now()->toDateTimeString();
            $message = 'Account verified successfully.';
            if ($request->has('is_forget_password') && $request->is_forget_password == 1) {
                $message = 'OTP matched successfully.';
            }
            if ($request->type == 'phone') {
                $message = 'Mobile number verified successfully.';
                if ($request->has('phone_number') && !empty($request->phone_number)) {
                    $phone_number = str_ireplace(' ', '', $request->phone_number);
                    $user_detail_exist = User::where('phone_number', $phone_number)->whereNotIn('id', [$user->id])->first();
                    if ($user_detail_exist) {
                        return response()->json(['error' => __('phone number in use!')], 404);
                    }
                }
                if ($user->phone_token != $request->otp) {
                    return $this->errorResponse(__('OTP is not valid'), 404);
                }
                if ($currentTime > $user->phone_token_valid_till) {
                    return $this->errorResponse(__('OTP has been expired.'), 404);
                }
                $user->phone_token = NULL;
                $user->phone_number = $request->phone_number;
                $user->is_phone_verified = 1;
                $user->phone_token_valid_till = NULL;
                $user->save();
                // if($user->hasRole('Lawyer')){
                //     return response()->json(['status' => 'Success']);
                // }
                return $this->successResponse(getUserDetailViaApi($user), $message);
            } elseif ($request->type == 'email') {
                $message = 'Email verified successfully.';
                if ($request->has('email')) {
                    $user_detail_exist = User::where('email', $request->email)->whereNotIn('id', [$user->id])->first();
                    if ($user_detail_exist) {
                        return $this->errorResponse(__('Email already in use!'), 404);
                    }
                }
                if ($user->email_token != $request->otp) {
                    return $this->errorResponse(__('OTP is not valid'), 404);
                }
                if ($currentTime > $user->email_token_valid_till) {
                    return $this->errorResponse(__('OTP has been expired.'), 404);
                }
                $user->email_token = NULL;
                $user->is_email_verified = 1;
                $user->email_token_valid_till = NULL;
                $user->save();
                // $this->sendCustomerSignupSuccessEmail($user);
                if ($user->hasRole('Lawyer')) {
                    if ($user->status == 1) {
                        $is_lawyer_active = 1;
                    }
                    $is_lawyer_active = 0;
                    $user->is_lawyer_active = $is_lawyer_active;
                }

                return $this->successResponse(getUserDetailViaApi($user), $message);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $blockToken = new BlockedToken();
        $header = $request->header();
        $blockToken->token = $header['authorization'][0];
        $blockToken->expired = '1';
        $blockToken->save();

        $del_token = UserDevice::where('access_token', $header['authorization'][0])->delete();

        return response()->json([
            'message' => __('Successfully logged out')
        ]);
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users'
            ], ['email.required' => __('The email field is required.'), 'email.exists' => __('You are not registered with us. Please sign up.')]);
            if ($validator->fails()) {
                foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                    $errors['error'] = __($error_value[0]);
                    return response()->json($errors, 422);
                }
            }
            $client = Client::select('id', 'name', 'email', 'phone_number', 'logo', 'sub_domain', 'custom_domain')->where('id', '>', 0)->first();
            $data = ClientPreference::select('mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
            if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
                $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
                $token = Str::random(60);
                $client_name = $client->name;
                $mail_from = $data->mail_from;
                DB::table('password_resets')->insert(['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]);
                $email_template_content = '';
                $email_template = EmailTemplate::where('id', 3)->first();
                if ($email_template) {
                    $email_template_content = $email_template->content;

                    if (isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain)
                        $domain_link = "https://" . $client->custom_domain;
                    else
                        $domain_link = "https://" . $client->sub_domain . env('SUBMAINDOMAIN');

                    // $email_template_content = str_ireplace("{reset_link}", url('/reset-password/' . $token), $email_template_content);
                    //    $email_template_content = str_ireplace("{reset_link}", "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/reset-password/" . $token, $email_template_content);
                    $email_template_content = str_ireplace("{reset_link}", $domain_link . "/reset-password/" . $token, $email_template_content);
                }
                $data = [
                    'token' => $token,
                    'mail_from' => $mail_from,
                    'email' => $request->email,
                    'client_name' => $client_name,
                    'logo' => $client->logo['original'],
                    'subject' => $email_template->subject,
                    'email_template_content' => $email_template_content,
                ];
                dispatch(new \App\Jobs\sendForgotPasswordEmail($data))->onQueue('forgot_password_email');
            }
            return response()->json(['success' => __('We have e-mailed your password reset link!')], 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }


        // $user = User::where('email', $request->email)->first();
        // if(!$user){
        //     return response()->json(['error' => 'Invalid email'], 404);
        // }
        // $notified = 1;
        // $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        // $data = ClientPreference::select('mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
        // $newDateTime = Carbon::now()->addMinutes(10)->toDateTimeString();
        // $otp = mt_rand(100000, 999999);
        // $user->email_token = $otp;
        // $user->email_token_valid_till = $newDateTime;
        // if(!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)){
        //     $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
        //     $client_name = $client->name;
        //     $mail_from = $data->mail_from;
        //     $sendto = $user->email;
        //     try{
        //         Mail::send('email.verify',[
        //                 'customer_name' => ucwords($user->name),
        //                 'code_text' => 'We have gotton a forget password request from your account. Please enter below otp of verify that it is you.',
        //                 'code' => $otp,
        //                 'logo' => $client->logo['original'],
        //                 'link'=>"link"
        //             ],
        //             function ($message) use($sendto, $client_name, $mail_from) {
        //             $message->from($mail_from, $client_name);
        //             $message->to($sendto)->subject('OTP to verify account');
        //         });
        //         $notified = 1;
        //     }
        //     catch(\Exception $e){
        //         $user->save();
        //     }
        // }
        // $user->save();
        // if($notified == 1){
        //     return response()->json(['success' => 'An otp has been sent to your email. Please check.'], 200);
        // }
    }

    /**
     * reset password.
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request, $domain = '')
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'otp' => 'required|string|min:6|max:50',
            'new_password' => 'required|string|min:6|max:50',
            'confirm_password' => 'required|same:new_password',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                $errors['error'] = __($error_value[0]);
                return response()->json($errors, 422);
            }
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => __('User not found.')], 404);
        }
        if ($user->email_token != $request->otp) {
            return response()->json(['error' => __('OTP is not valid')], 404);
        }
        $currentTime = Carbon::now()->toDateTimeString();
        if (($currentTime > $user->phone_token_valid_till) && !isStaticOtpEnable()) {
            return response()->json(['error' => __('OTP has been expired.')], 404);
        }
        $user->password = Hash::make($request['new_password']);
        $user->save();
        return response()->json(['message' => __('Password updated successfully.')]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function sacialData(Request $request)
    {
        return response()->json([
            'message' => __('Successfully logged out')
        ]);
    }

    /**     * proceed to user login on phone number     */
    public function proceedToPhoneLogin($req, $domain = '')
    {
        $user = User::where('phone_number', $req->phone_number)->where('dial_code', $req->dialCode)->where('status', 1)->first();
        if ($user) {
            Auth::login($user);
            $prefer = ClientPreference::select('theme_admin', 'distance_unit', 'map_provider', 'date_format', 'time_format', 'map_key', 'sms_provider', 'verify_email', 'verify_phone', 'app_template_id', 'web_template_id')->first();
            $token1 = new Token;
            $token = $token1->make([
                'key' => 'royoorders-jwt',
                'issuer' => 'royoorders.com',
                'expiry' => strtotime('+1 month'),
                'issuedAt' => time(),
                'algorithm' => 'HS256',
            ])->get();
            $token1->setClaim('user_id', $user->id);
            try {
                Token::validate($token, 'secret');
            } catch (\Exception $e) {
            }
            $user_refferal = UserRefferal::where('user_id', $user->id)->first();

            //check login from individual vendor app
            $fromVendorAppLogin = 0;
            if (!empty($req->is_vendor_app)) {
                $fromVendorAppLogin = 1;
            }

            if (!empty($req->fcm_token)) {
                $device = UserDevice::updateOrCreate(
                    ['device_token' => $req->fcm_token],
                    [
                        'user_id' => $user->id,
                        'device_type' => $req->device_type,
                        'access_token' => $token,
                        'is_vendor_app' => $fromVendorAppLogin
                    ]
                );
            } else {
                $device = UserDevice::updateOrCreate(
                    ['device_token' => $req->device_token],
                    [
                        'user_id' => $user->id,
                        'device_type' => $req->device_type,
                        'access_token' => $token,
                        'is_vendor_app' => $fromVendorAppLogin
                    ]
                );
            }
            $user->is_phone_verified = 1;
            $user->phone_token = NULL;
            $user->phone_token_valid_till = NULL;
            $user->auth_token = $token;
            $user->save();

            $verified['is_email_verified'] = $user->is_email_verified;
            $verified['is_phone_verified'] = $user->is_phone_verified;

            $user_cart = Cart::where('user_id', $user->id)->first();
            if ($user_cart) {
                $unique_identifier_cart = Cart::where('unique_identifier', $req->device_token)->first();
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
                Cart::where('unique_identifier', $req->device_token)->update(['user_id' => $user->id,  'unique_identifier' => '']);
            }

            if (getClientPreferenceDetail()->p2p_check) {
                $vendorUser =  UserVendor::select('vendor_id')->where('user_id', $user->id)->first();
                $data['vendor_id'] = $vendorUser->vendor_id ?? '';
            }
            $checkSystemUser = $this->checkCookies($user->id);
            $data['id'] = $user->id;
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['auth_token'] =  $token;
            $data['source'] = $user->image;
            $data['verify_details'] = $verified;
            $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
            $data['is_admin'] = $user_vendor_count > 0 ? 1 : 0;
            $data['is_superadmin'] = $user->is_superadmin ?? 0;
            //$data['is_admin'] = $user->is_admin;
            $data['client_preference'] = $prefer;
            $data['dial_code'] = $user->dial_code;
            $data['phone_number'] = $user->phone_number;
            $data['cca2'] = $user->country ? $user->country->code : '';
            $data['callingCode'] = $user->country ? $user->country->phonecode : '';
            $data['refferal_code'] = $user_refferal ? $user_refferal->refferal_code : '';

            $message = __('Logged in successfully');
            // return response()->json(['data' => $data]);
            return $this->successResponse($data, $message);
        } else {
            return $this->errorResponse(__('Invalid phone number'), 404);
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
                    'countryData'  => 'required|string',
                    'device_type'   => 'required|string',
                    'device_token'  => 'required|string',
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
                $phoneCode =  getUserToken($prefer)['otp'];
                $sendTime = Carbon::now()->addMinutes(10)->toDateTimeString();
                $request->request->add(['is_phone' => 1, 'phone_number' => $phone_number, 'phoneCode' => $phoneCode, 'sendTime' => $sendTime, 'codeSent' => 0]);
                $user = User::where('dial_code', $dialCode)->where('phone_number', $phone_number)->first();
                // pr($user->toArray());
                if (!$user) {
                    if (session()->get("locale") == "ar") {
                        return $this->errorResponse(__('أنت غير مسجل معنا. يرجى الاشتراك'), 404);
                    }
                    return $this->errorResponse(__('You are not registered with us. Please sign up.'), 404);
                    /*   $registerUser = $this->registerViaPhone($request)->getData();
                    if ($registerUser->status == 'Success') {
                        $user = $registerUser->data;
                    } else {
                        return $this->errorResponse(__('Invalid data'), 404);
                    }*/
                } else {
                    if ($request->customer && $user->hasRole('Lawyer')) {
                        return $this->errorResponse(__('You are not registered with us. Please sign up as a customer'), 404);
                    }
                    $user->phone_token = $phoneCode;
                    $user->phone_token_valid_till = $sendTime;
                    $user->save();
                }
                // if ($dialCode == "971") {
                //     $to = '+' . $dialCode . "0" . $phone_number;
                // } else {
                //     $to = '+' . $dialCode . $phone_number;
                // }

                // $provider = $prefer->sms_provider;
                // $body = "Please enter OTP " . $phoneCode . " to verify your account.";
                // $keyData = ['{user_name}'=>ucwords($user->name),'{otp_code}'=>$phoneCode];
                // $body = sendSmsTemplate('verify-account',$keyData);
                // if (!empty($provider)) {
                //     if(getUserToken($prefer)['status']){
                //         $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
                //     }else{
                //         $send = 1;
                //     }
                //     if ($send) {
                //         $request->request->add(['codeSent' => 1]);
                //         $message = __('An otp has been sent to your phone. Please check.');
                //         $response = $request->all();
                //         return $this->successResponse($response, $message);
                //     } else {
                //         return $this->errorResponse(__('Something went wrong in sending OTP. We are sorry to for the inconvenience'), 404);
                //     }
                // } else {
                //     return $this->errorResponse(__('Provider service is not configured. Please contact administration'), 404);

                if (!Auth::attempt(['phone_number' => $request->phone_number, 'password' => $request->password])) {
                    $errors['error'] = __('Invalid phone number or password');
                    return response()->json($errors, 422);
                }

                $token1 = new Token;
                $token = $token1->make([
                    'key' => 'royoorders-jwt',
                    'issuer' => 'royoorders.com',
                    'expiry' => strtotime('+1 month'),
                    'issuedAt' => time(),
                    'algorithm' => 'HS256',
                ])->get();

                $fromVendorAppLogin = 0;

                if (!empty($request->is_vendor_app)) {
                    $fromVendorAppLogin = 1;
                }

                if (!empty($request->fcm_token)) {
                    UserDevice::updateOrCreate(
                        ['device_token' => $request->fcm_token, 'user_id' => $user->id],
                        [
                            'device_type' => $request->device_type,
                            'access_token' => $token,
                            'is_vendor_app' => $fromVendorAppLogin
                        ]
                    );
                } else {
                    UserDevice::updateOrCreate(
                        ['device_token' => $request->device_token, 'user_id' => $user->id],
                        [
                            'user_id' => $user->id,
                            'device_type' => $request->device_type,
                            'access_token' => $token,
                            'is_vendor_app' => $fromVendorAppLogin
                        ]
                    );
                }

                $user->auth_token = $token;

                return response()->json(['data' => $user]);
            } elseif (preg_match($email_regex, $username)) {
                $validator = Validator::make($request->all(), [
                    'username'  => 'required',
                    'device_type'   => 'required|string',
                    'device_token'  => 'required|string',
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                        $errors['error'] = __($error_value[0]);
                        return response()->json($errors, 422);
                    }
                }
                $username = str_ireplace(' ', '', $username);

                $user = User::with('country')->where('email', $username)->first();
                if ($user) {
                    if ($user->status != 1) {
                        $errors['error'] = __('You are unauthorized to access this account.');
                        return response()->json($errors, 422);
                    }
                } else {
                    return $this->errorResponse(__('You are not registered with us. Please sign up.'), 404);
                }

                if (!Auth::attempt(['email' => $username, 'password' => $request->password])) {
                    $errors['error'] = __('Invalid password');
                    return response()->json($errors, 422);
                }
                if ($request->customer && $user->hasRole('Lawyer')) {
                    return $this->errorResponse(__('You are not registered with us. Please sign up as a customer'), 404);
                }
                $user = Auth::user();
                $prefer = ClientPreference::select('theme_admin', 'distance_unit', 'map_provider', 'date_format', 'time_format', 'map_key', 'sms_provider', 'verify_email', 'verify_phone', 'app_template_id', 'web_template_id')->first();
                $verified['is_email_verified'] = $user->is_email_verified;
                $verified['is_phone_verified'] = $user->is_phone_verified;
                $user_id = $user->id;
                $user_registration_documents = UserRegistrationDocuments::with(['user_document' => function ($q) use ($user_id) {
                    $q->where('user_id', $user_id);
                }, 'primary'])->get();
                $token1 = new Token;
                $token = $token1->make([
                    'key' => 'royoorders-jwt',
                    'issuer' => 'royoorders.com',
                    'expiry' => strtotime('+1 month'),
                    'issuedAt' => time(),
                    'algorithm' => 'HS256',
                ])->get();
                $token1->setClaim('user_id', $user->id);
                try {
                    Token::validate($token, 'secret');
                } catch (\Exception $e) {
                }
                $user_refferal = UserRefferal::where('user_id', $user->id)->first();

                //check login from individual vendor app
                $fromVendorAppLogin = 0;
                if (!empty($request->is_vendor_app)) {
                    $fromVendorAppLogin = 1;
                }

                if (!empty($request->fcm_token)) {
                    $device = UserDevice::updateOrCreate(
                        ['device_token' => $request->fcm_token],
                        [
                            'user_id' => $user->id,
                            'device_type' => $request->device_type,
                            'access_token' => $token,
                            'is_vendor_app' => $fromVendorAppLogin
                        ]
                    );
                } else {
                    $device = UserDevice::updateOrCreate(
                        ['device_token' => $request->device_token],
                        [
                            'user_id' => $user->id,
                            'device_type' => $request->device_type,
                            'access_token' => $token,
                            'is_vendor_app' => $fromVendorAppLogin
                        ]
                    );
                }

                $user->auth_token = $token;
                $user->save();

                $user_cart = Cart::where('user_id', $user->id)->first();
                if ($user_cart) {
                    $unique_identifier_cart = Cart::where('unique_identifier', $request->device_token)->first();
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
                    Cart::where('unique_identifier', $request->device_token)->update(['user_id' => $user->id,  'unique_identifier' => '']);
                }

                if (getClientPreferenceDetail()->p2p_check) {
                    $vendorUser =  UserVendor::select('vendor_id')->where('user_id', $user->id)->first();
                    $data['vendor_id'] = $vendorUser->vendor_id ?? '';
                }




                $checkSystemUser = $this->checkCookies($user->id);
                $data['id'] = $user->id;
                $data['name'] = $user->name;
                $data['email'] = $user->email;
                $data['auth_token'] =  $token;
                $data['source'] = $user->image;
                $data['verify_details'] = $verified;
                $user_vendor_count = UserVendor::where('user_id', $user->id)->count();
                $data['is_admin'] = $user_vendor_count > 0 ? 1 : 0;
                $data['is_superadmin'] = $user->is_superadmin ?? 0;
                $data['client_preference'] = $prefer;
                $data['dial_code'] = $user->dial_code;
                $data['phone_number'] = $user->phone_number;
                $data['cca2'] = $user->country ? $user->country->code : '';
                $data['callingCode'] = $user->country ? $user->country->phonecode : '';
                $data['refferal_code'] = $user_refferal ? $user_refferal->refferal_code : '';
                $data['user_document'] = $user_registration_documents;
                $data['user_subscription'] = SubscriptionInvoicesUser::where('status_id', 1)->where('end_date', '>=', now()->format('Y-m-d'))->count();
                return response()->json(['data' => $data]);
            } else {
                return $this->errorResponse(__('Invalid email or phone number'), 404);
            }
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), 400);
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
            // if (!$user) {
            //     $errors['error'] = __('Your phone number is not registered');
            //     return response()->json($errors, 422);
            // }
            $currentTime = Carbon::now()->toDateTimeString();
            $message = 'Account verified successfully.';

            if ($user->phone_token != $request->verifyToken) {
                return $this->errorResponse(__('OTP is not valid'), 404);
            }
            if ($currentTime > $user->phone_token_valid_till) {
                return $this->errorResponse(__('OTP has been expired.'), 404);
            }

            if ($currentTime <= $user->phone_token_valid_till && $user->phone_token == $request->verifyToken) {
                if ($user->status == 0) {
                    User::where('id', $user->id)->update(['status' => 1]);
                }
                if ($user->status == 2) {
                    return $this->errorResponse(__('User is Blocked.'), 404);
                }
                if ($user->status == 3) {
                    return $this->errorResponse(__('User is Inactive.'), 404);
                }
            }


            $request->request->add(['phone_number' => $phone_number]);
            return $this->proceedToPhoneLogin($request);
        } catch (\Exception $ex) {
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
            $user_cart = Cart::where('user_id', $user->id)->first();
            if ($user_cart) {
                $unique_identifier_cart = Cart::where('unique_identifier', $req->device_token)->first();
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
                Cart::where('unique_identifier', $req->device_token)->update(['user_id' => $user->id,  'unique_identifier' => '']);
            }
            if ($user->id > 0) {
                if ($req->refferal_code) {
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
                                $wallet->depositFloat($refferal_amounts->reffered_to_amount, ['You used referal code of <b>' . $user_refferd_by->name . '</b>']);
                                $wallet->balance;
                            }
                        }
                    }
                }

                return $this->successResponse($user, 'Successfully registered');
            } else {
                return $this->errorResponse('Something went wrong. Please try again.', 422);
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function driverSignup(Request $request)
    {
        try {
            $dispatch_domain = $this->checkIfLastMileDeliveryOn();
            if ($dispatch_domain && $dispatch_domain != false) {

                $data = json_decode($this->driverDocuments());
                $driver_registration_documents = $data->documents;

                $rules_array = [
                    'name' => 'required',
                    'phone_number' => 'required',
                    'type' => 'required',
                    'team' => 'required',
                    // 'vehicle_type_id' => 'required',
                    // 'make_model' => 'required',
                    // 'uid' => 'required',
                    // 'plate_number' => 'required',
                    // 'color' => 'required'
                ];
                foreach ($driver_registration_documents as $driver_registration_document) {
                    if ($driver_registration_document->is_required == 1) {
                        $name = str_replace(" ", "_", $driver_registration_document->name);
                        $rules_array[$name] = 'required';
                    }
                }
                $validator = Validator::make($request->all(), $rules_array, [
                    "name.required" => __('The name field is required.'),
                    "phone_number.required" => __('The phone number field is required.'),
                    "type.required" => __('The type field is required.'),
                    "vehicle_type_id.required" => __('The transport type is required.'),
                    "make_model.required" => __('The transport details field is required.'),
                    "uid.required" => __('The UID field is required.'),
                    "plate_number.required" => __('The licence plate field is required.'),
                    "color.required" => __('The color field is required.'),
                    "team.required" => __('The team field is required.')
                ]);
                if ($validator->fails()) {
                    foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                        $error = __($error_value[0]);
                        return $this->errorResponse($error, 422);
                    }
                }

                $files = [];
                if ($driver_registration_documents != null) {
                    foreach ($driver_registration_documents as $key => $driver_registration_document) {
                        $driver_registration_document_file_type[$key] = $driver_registration_document->file_type;
                        $files[$key]['file_type'] = $driver_registration_document_file_type[$key];
                        $driver_registration_document_id[$key] = $driver_registration_document->id;
                        $files[$key]['id'] = $driver_registration_document_id[$key];
                        $driver_registration_document_name[$key] = $driver_registration_document->name;
                        $files[$key]['name'] = $driver_registration_document_name[$key];
                        $name = str_replace(" ", "_", $driver_registration_document->name);
                        // $arr = explode(' ', $name);
                        // $name = implode('_', $arr);
                        $driver_registration_document_file_name[$key] = $request->$name;
                        $files[$key]['file_name'] =  $driver_registration_document_file_name[$key];
                    }
                }
                // $dispatch_domain->delivery_service_key_code = '649a9a';
                //  $dispatch_domain->delivery_service_key = 'icDerSAVT4Fd795DgPsPfONXahhTOA';
                if ($dispatch_domain->business_type == 'taxi') {
                    $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key, 'shortcode' => $dispatch_domain->pickup_delivery_service_key_code]]);
                    $url = $dispatch_domain->pickup_delivery_service_key_url;
                } else if ($dispatch_domain->business_type == 'laundry') {
                    $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key, 'shortcode' => $dispatch_domain->pickup_delivery_service_key_code]]);
                    $url = $dispatch_domain->pickup_delivery_service_key_url;
                } else {
                    $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->delivery_service_key, 'shortcode' => $dispatch_domain->delivery_service_key_code]]);
                    $url = $dispatch_domain->delivery_service_key_url;
                }
                $key1 = 0;
                $key2 = 0;
                $filedata = [];
                $other = [];
                $abc = [];
                foreach ($files as $file) {
                    if ($file['file_name'] != null) {
                        if ($file['file_type'] != "Text" && $file['file_type'] != "selector" && $file['file_type'] != "Date") {
                            $file_path          = $file['file_name']->getPathname();
                            $file_mime          = $file['file_name']->getMimeType('image');
                            $file_uploaded_name = $file['file_name']->getClientOriginalName();
                            $filedata[$key2] =  [
                                'Content-type' => 'multipart/form-data',
                                'name' => 'uploaded_file[]',
                                'file_type' => $file['file_type'],
                                'id' => $file['id'],
                                'filename' => $file_uploaded_name,
                                'contents' => fopen($file_path, 'r'),

                            ];
                            $other[$key2] = [
                                'filename1' => $file['name'],
                                'file_type' => $file['file_type'],
                                'id' => $file['id'],
                            ];
                            $key2++;
                        } else {
                            $abc[$key1] =  [
                                'file_type' => $file['file_type'],
                                'id' => $file['id'],
                                'contents' => $file['file_name'],
                                'label_name' => $file['name']
                            ];
                            $key1++;
                        }
                    }
                }
                $profile_photo = [];
                if ($request->hasFile('upload_photo')) {
                    $profile_photo =
                        [
                            'Content-type' => 'multipart/form-data',
                            'name' => 'upload_photo',
                            'filename' => $request->upload_photo->getClientOriginalName(),
                            'Mime-Type' => $request->upload_photo->getMimeType('image'),
                            'contents' =>  fopen($request->upload_photo, 'r'),
                        ];
                }
                if ($profile_photo == null) {
                    $profile_photo = ['name' => 'profile_photo[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(0, $filedata)) {
                    $filedata[0] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(1, $filedata)) {
                    $filedata[1] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(2, $filedata)) {
                    $filedata[2] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(3, $filedata)) {
                    $filedata[3] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(4, $filedata)) {
                    $filedata[4] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(5, $filedata)) {
                    $filedata[5] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(6, $filedata)) {
                    $filedata[6] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(7, $filedata)) {
                    $filedata[7] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(8, $filedata)) {
                    $filedata[8] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }
                if (!array_key_exists(9, $filedata)) {
                    $filedata[9] = ['name' => 'uploaded_file[]', 'contents' => 'abc'];
                }

                $tags = '';
                if ($request->has('tags') && !empty($request->get('tags'))) {
                    $tagsArray = $request->get('tags');
                    $tags = implode(',', $tagsArray);
                }

                $res = $client->post($url . '/api/agent/create', [

                    'multipart' => [
                        $filedata[0],
                        $profile_photo,
                        $filedata[1],
                        $filedata[2],
                        $filedata[3],
                        $filedata[4],
                        $filedata[5],
                        $filedata[6],
                        $filedata[7],
                        $filedata[8],
                        $filedata[9],
                        [
                            'name' => 'other',
                            'contents' => json_encode($other)
                        ],
                        [
                            'name' => 'files_text',
                            'contents' => json_encode($abc)
                        ],

                        [
                            'name' => 'count',
                            'contents' => count($files)
                        ],
                        [
                            'name' => 'name',
                            'contents' => $request->name
                        ],
                        [
                            'name' => 'phone_number',
                            'contents' => $request->phone_number
                        ],
                        [
                            'name' => 'country_code',
                            'contents' => $request->dialCode
                        ],
                        [
                            'name' => 'type',
                            'contents' => $request->type
                        ],
                        [
                            'name' => 'vehicle_type_id',
                            'contents' => $request->vehicle_type_id ?? null
                        ],
                        [
                            'name' => 'make_model',
                            'contents' => $request->make_model ?? null
                        ],
                        [
                            'name' => 'uid',
                            'contents' => $request->uid ?? null
                        ],
                        [
                            'name' => 'plate_number',
                            'contents' => $request->plate_number ?? null
                        ],
                        [
                            'name' => 'color',
                            'contents' => $request->color ?? null
                        ],
                        [
                            'name' => 'team_id',
                            'contents' => $request->team
                        ],
                        [
                            'name' => 'tags',
                            'contents' => $tags
                        ],
                    ]

                ]);
                $response = json_decode($res->getBody(), true);
                return $response;
            }
        } catch (\Exception $e) {
            $data = [];
            $data['status'] = 400;
            $data['message'] =  $e->getMessage();
            return $data;
        }
    }
    public function deleteUser(Request $request)
    {
        try {
            DB::beginTransaction(); //Initiate transaction
            $user = Auth::user();
            if (!$user) {
                return response()->json(['massage' => __('User not found!')], 200);
            }
            User::where('id', $user->id)->update([
                // 'email' => $user->email.'_'.$user->id."_D",
                // 'phone_number' => $user->phone_number.'_'.$user->id."_D",
                'auth_token' => '',
                'system_id' => '',
                'remember_token' => '',
                'facebook_auth_id' => '',
                'twitter_auth_id' => '',
                'google_auth_id' => '',
                'apple_auth_id' => '',
                'status' => 3
            ]);

            //$user->delete();
            DB::commit(); //Commit transaction after all the operations
            return response()->json(['massage' => __('User Deleted Successfully')], 200);
            //code...

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['massage' => __('Something went wrong!')], 400);
        }
    }
    /**
     * Mark user as a vendor
     */
    public function addDataSaveVendor(Request $request, $vendor_id)
    {

        $vendor = Vendor::where('id', $vendor_id)->firstOrFail();
        $VendorController = new VendorController();

        $request->merge(["return_json" => 1]);
        $VendorConfigrespons = $VendorController->updateConfig($request, '', $vendor_id)->getData(); //$this->updateConfig($vendor_id);

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

    public function checkIfLastMileOn()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }
}
