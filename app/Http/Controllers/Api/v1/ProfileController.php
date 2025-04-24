<?php
namespace App\Http\Controllers\Api\v1;

use App\Models\Country;
use DB;
use Config;
use Validation;
use Carbon\Carbon;
use ConvertCurrency;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Log;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Http\Requests\ {
    SendReferralRequest
};
use App\Models\ {
    User,
    UserRefferal,
    ClientPreference,
    Client,
    UserWishlist,
    ClientCurrency,
    EmailTemplate,
    UserRegistrationDocuments,
    Product,
    UserDocs,
    UserVendorWishlist,
    Vendor
};
use App\Models\UserDataVault;
use App\Http\Controllers\Front\AzulPaymentController;

class ProfileController extends BaseController
{

    use ApiResponser;

    private $curLang = 0;

    private $field_status = 2;

    private $folderName = '/user/document';

    public function postSendReffralCode(Request $SendReferralRequest)
    {
        try {
            $validator = Validator::make($SendReferralRequest->all(), [
                'email' => 'required|email|max:50|unique:users'
            ],[
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'This email is already registered.',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                    $errors['error'] = __($error_value[0]);
                    return response()->json($errors, 422);
                }
            }

            $user = Auth::user();
            $client = Client::first();
            $client_preference_detail = ClientPreference::first();
            $user_refferal_detail = UserRefferal::where('user_id', $user->id)->first();





            
            if ($user_refferal_detail) {
                $refferal_code = $user_refferal_detail->refferal_code;
                if ($client_preference_detail) {
                    if ($client_preference_detail->mail_driver && $client_preference_detail->mail_host && $client_preference_detail->mail_port && $client_preference_detail->mail_port && $client_preference_detail->mail_password && $client_preference_detail->mail_encryption) {
                        $confirured = $this->setMailDetail($client_preference_detail->mail_driver, $client_preference_detail->mail_host, $client_preference_detail->mail_port, $client_preference_detail->mail_username, $client_preference_detail->mail_password, $client_preference_detail->mail_encryption);
                        $client_name = $client->name;
                        $sendto = $SendReferralRequest->email;
                        $mail_from = $client_preference_detail->mail_from;
                        try {
                            $email_template_content = '';
                            $email_template = EmailTemplate::where('id', 8)->first();
                            if ($email_template) {
                                $email_template_content = $email_template->content;
                                $email_template_content = str_ireplace("{code}", $refferal_code, $email_template_content);
                                $email_template_content = str_ireplace("{customer_name}", ucwords($user->name), $email_template_content);
                            }

                            $t = Mail::send('email.verify', [
                                'email' => $sendto,
                                'mail_from' => $mail_from,
                                'client_name' => $client_name,
                                'code' => $refferal_code,
                                'logo' => $client->logo['original'],
                                'customer_name' => "Link from " . $user->name,
                                'code_text' => 'Register yourself using this referral code below to get bonus offer',
                                'link' => "http://local.myorder.com/user/register?refferal_code=" . $refferal_code,
                                'email_template_content' => $email_template_content
                            ], function ($message) use ($sendto, $client_name, $mail_from) {
                                $message->from($mail_from, $client_name);
                                $message->to($sendto)->subject('Referral For Registration');
                            });
                        } catch (\Exception $e) {
                            \Log::error($e->getMessage());
                        }
                    }
                    return response()->json(array(
                        'success' => true,
                        'message' => __('Send Successfully')
                    ));
                }
            }
        } catch (Exception $e) {
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function wishlists(Request $request)
    {
        $user = Auth::user();
        $language_id = $user->language;
        $paginate = $request->has('limit') ? $request->limit : 12;
        $clientCurrency = ClientCurrency::where('currency_id', $user->currency)->first();
        $user_wish_details = UserWishlist::with([
            'product.category.categoryDetail',
            'product.category.categoryDetail.translation' => function ($q) use ($language_id) {
                $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')->where('category_translations.language_id', $language_id);
            },
            'product.media.image',
            'product.translation' => function ($q) use ($language_id) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $language_id);
            },
            'product.variant' => function ($q) use ($language_id) {
                $q->select('sku', 'product_id', 'quantity', 'price', 'barcode');
                $q->groupBy('product_id');
            }
        ])->whereHas('product.category.categoryDetail', function ($q) {
            $q->whereNotNull('products.category_id')
                ->whereNull('categories.deleted_at');
        })
            ->select("id", "user_id", "product_id")
            ->where('user_id', $user->id)
            ->paginate($paginate);
        if ($user_wish_details) {
            foreach ($user_wish_details as $user_wish_detail) {
                if (isset($user_wish_detail->product) && ! empty($user_wish_detail->product->category)) {
                    $user_wish_detail->product->is_wishlist = isset($user_wish_detail->product->category) ? $user_wish_detail->product->category->categoryDetail->show_wishlist : null;
                    if ($user_wish_detail->product->variant) {
                        foreach ($user_wish_detail->product->variant as $variant) {
                            $variant->multiplier = $clientCurrency->doller_compare;
                        }
                    }
                }
            }
        }
        return response()->json([
            'data' => $user_wish_details
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateWishlist(Request $request, $pid = 0)
    {
        $product = Product::where('id', $pid)->first();
        if (! $product) {
            return response()->json([
                'error' => __('No record found.')
            ], 404);
        }
        $exist = UserWishlist::where('user_id', Auth::user()->id)->where('product_id', $product->id)->first();
        if ($exist) {
            $exist->delete();
            return response()->json([
                'data' => $product->id,
                'message' => __('Product has been removed from wishlist.')
            ]);
        }
        $wishlist = new UserWishlist();
        $wishlist->user_id = Auth::user()->id;
        $wishlist->product_id = $product->id;
        $wishlist->added_on = Carbon::now();
        $wishlist->save();
        return response()->json([
            'data' => $product->id,
            'message' => __('Product has been added in wishlist.')
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function newsLetter(Request $request, $domain = '')
    {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {
        $user = User::with('country', 'address')->select('name', 'email', 'phone_number', 'type', 'country_id')
            ->where('id', Auth::user()->id)
            ->first();
        if (! $user) {
            return response()->json([
                'error' => __('No record found.')
            ], 404);
        }
        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request, $domain = '')
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|max:50',
            'confirm_password' => 'required|same:new_password'
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                $errors['error'] = __($error_value[0]);
                return response()->json($errors, 422);
            }
        }

        $current_password = Auth::User()->password;
        if (! Hash::check($request->current_password, $current_password)) {
            return response()->json([
                'error' => __('Password did not matched.')
            ], 404);
        }
        $user_id = Auth::User()->id;
        $obj_user = User::find(Auth::User()->id);
        $obj_user->password = Hash::make($request->new_password);
        $obj_user->save();
        return response()->json([
            'message' => __('Password updated successfully.')
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|string',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                $errors['error'] = __($error_value[0]);
                return response()->json($errors, 422);
            }
        }
    
        $img = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->avatar));
        $user = User::where('id', Auth::user()->id)->first();
    
        // Delete old image from S3 if exists
        if (!empty($user->image)) {
            Storage::disk('s3')->delete($user->image);
        }
    
        $imgType = $request->has('type') ? $request->type : 'jpg';
        $code = Client::orderBy('id', 'asc')->value('code');
        $imageName = $code . '/profile/' . $user->id . substr(md5(microtime()), 0, 15) . '.' . $imgType;
    
        // Save the image to S3
        $save = Storage::disk('s3')->put($imageName, $img, 'public');
    
        // Get the S3 URL of the uploaded image
        $url = Storage::disk('s3')->url($imageName);
    
        // Update user's image
        $user->image = $imageName;
        $user->save();
        return response()->json([
            'message' => __('Profile image updated successfully.'),
            'data' => $user,
        ]);
    }
    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        $usr = Auth::user()->id;
        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        $rules = [
            'country_code' => 'required|string',
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|max:50||unique:users,email,' . $usr,
            'phone_number' => 'required|string|min:7|max:15|unique:users,phone_number,' . $usr
        ];
        foreach ($user_registration_documents as $user_registration_document) {
            if ($user_registration_document->is_required == 1) {
                $rules[$user_registration_document->primary->slug] = 'required';
            }
        }

        $validator = Validator::make($request->all(), $rules);
        // $validator = Validator::make($request->all(), [
        // 'country_code' => 'required|string',
        // 'name' => 'required|string|min:3|max:50',
        // 'email' => 'required|email|max:50||unique:users,email,'.$usr,
        // 'phone_number' => 'required|string|min:7|max:15|unique:users,phone_number,'.$usr,
        // ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                $errors['error'] = __($error_value[0]);
                return response()->json($errors, 422);
            }
        }
        $country_detail = Country::where('code', $request->country_code)->first();
        if (! $country_detail) {
            return response()->json([
                'error' => __('Invalid country code.')
            ], 404);
        }
        $prefer = ClientPreference::select('mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'theme_admin', 'distance_unit', 'map_provider', 'date_format', 'time_format', 'map_key', 'sms_provider', 'verify_email', 'verify_phone', 'app_template_id', 'web_template_id')->first();
        $user = User::where('id', $usr)->first();
        $user->name = $request->name;
        $user->country_id = $country_detail->id;
        $sendTime = \Carbon\Carbon::now()->addMinutes(10)->toDateTimeString();
        if ($user->phone_number != trim($request->phone_number)) {
            $phoneCode = mt_rand(100000, 999999);
            $user->is_phone_verified = 0;
            $user->phone_token = $phoneCode;
            $user->phone_token_valid_till = $sendTime;
            $user->phone_number = $request->phone_number;
            $user->dial_code = $request->callingCode ?? '';
            if (! empty($prefer->sms_key) && ! empty($prefer->sms_secret) && ! empty($prefer->sms_from)) {
                $to = $request->phone_number;
                $provider = $prefer->sms_provider;
                $body = "Dear " . ucwords($request->phone_number) . ", Please enter OTP " . $phoneCode . " to verify your account.";
                // $body =
                $send = $this->sendSms($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
                $response['send_otp'] = 1;
            }
        }
        if ($user->email != trim($request->email)) {
            $emailCode = mt_rand(100000, 999999);
            $user->email = $request->email;
            $user->is_email_verified = 0;
            $user->email_token = $emailCode;
            $user->email_token_valid_till = $sendTime;
            if (! empty($prefer->mail_driver) && ! empty($prefer->mail_host) && ! empty($prefer->mail_port) && ! empty($prefer->mail_port) && ! empty($prefer->mail_password) && ! empty($prefer->mail_encryption)) {
                $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
                $confirured = $this->setMailDetail($prefer->mail_driver, $prefer->mail_host, $prefer->mail_port, $prefer->mail_username, $prefer->mail_password, $prefer->mail_encryption);
                $client_name = $client->name;
                $mail_from = $prefer->mail_from;
                $sendto = $user->email;
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
                        'email_template_content' => $email_template_content
                    ];
                    $user->is_email_verified = 0;
                    dispatch(new \App\Jobs\SendVerifyEmailJob($data))->onQueue('verify_email');
                    $notified = 1;
                } catch (\Exception $e) {
                    $user->save();
                }
            }
        }
        $user->save();
        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        if ($user_registration_documents->count() > 0) {
            foreach ($user_registration_documents as $user_registration_document) {
                $doc_name = str_replace(" ", "_", $user_registration_document->primary->slug);
                if ($user_registration_document->file_type != "Text") {
                    if ($request->hasFile($doc_name)) {
                        $filePath = $this->folderName . '/' . Str::random(40);
                        $file = $request->file($doc_name);
                        $orignal_name = $request->file($doc_name)->getClientOriginalName();
                        $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                       
                        UserDocs::updateOrCreate(
                            
                            ['user_id' => $user->id, 'user_registration_document_id' => $user_registration_document->id]
                            ,
                            ['file_name' => $file_name,'file_original_name'=>$orignal_name]);
                    }
                } else {
                    UserDocs::updateOrCreate([
                        'user_id' => $user->id,
                        'user_registration_document_id' => $user_registration_document->id
                    ], [
                        'file_name' => $request->$doc_name
                    ]);
                }
            }
        }
        $user_id = $user->id;
        $user_registration = UserRegistrationDocuments::with([
            'user_document' => function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            },
            'primary'
        ])->get();
        $data['user_document'] = $user_registration;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['cca2'] = $request->country_code;
        $data['phone_number'] = $user->phone_number;
        $data['is_phone_verified'] = $user->is_phone_verified;
        $data['is_email_verified'] = $user->is_email_verified;
        $data['image'] = $user->image;
        return response()->json([
            'data' => $data,
            'message' => __('Profile updated successfully.')
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = Auth::user();
        $client = Client::first();
        $code = ((@$user->country->code) ? @$user->country->code : $client->country->code);
        $user_id = $user->id;
        $user_registration = UserRegistrationDocuments::with([
            'user_document' => function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            },
            'primary'
        ])->get();
        $data['user_document'] = $user_registration;
        $data['name'] = $user->name;
        $data['image'] = $user->image;
        $data['email'] = $user->email;
        $data['cca2'] = $code ?? '';
        $data['phone_number'] = $user->phone_number;
        $data['is_phone_verified'] = $user->is_phone_verified;
        $data['is_email_verified'] = $user->is_email_verified;
        return response()->json([
            'data' => $data,
            'message' => __('Profile get successfully.')
        ]);
    }

    public function getUserCards(Request $request)
    {
        $azul = new AzulPaymentController();
        $listData = $azul->getUserCardsList($request);
        return response()->json([
            'data' => $listData ?? []
        ]);
    }

    public function setDefaultCard(Request $request)
    {
        $isTrue =  UserDataVault::defaultCard($request->id);
        if($isTrue){
            return response()->json([
                'message' => __('Default Card Has Been Changed Successfully')
            ]);
        }
        return response()->json([
            'message' => __('Card does\'nt exist')
        ]);
    }

    /**
     * delete card of user
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteCard(Request $request)
    {
        $card = UserDataVault::deleteCard($request->id);
        if ($card) {
            return response()->json([
                'message' => __('Card Has Been Deleted Successfully')
            ]);
        }
        return response()->json([
            'message' => __('Card does\'nt exist')
        ]);
        
    }

    public function updateWishlistVendor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 201, 'message' => $validator->errors()->first()], 201);
        }

        $vendor = UserVendorWishlist::where(['user_id' => Auth::id(), 'vendor_id' => $request->vendor_id])->first();
        if($vendor){
            $vendor->delete();
            return response()->json([
                'success' => 200,
                'message' => __('Vendor has been removed from wishlist.')
            ]);
        }

        UserVendorWishlist::create([
            'user_id' => Auth::id(),
            'vendor_id' => $request->vendor_id
        ]);

        return response()->json([
            'success' => 200,
            'message' => __('Vendor has been added in wishlist.')
        ]);
    }

    public function wishlistVendors(Request $request)
    {
        $user = Auth::user();
        $preferences = ClientPreference::select('distance_to_time_multiplier', 'distance_unit_for_time', 'is_hyperlocal', 'Default_location_name', 'Default_latitude', 'Default_longitude', 'is_service_area_for_banners','subscription_mode')->first();
        $latitude = !empty($request->latitude) ? ($request->latitude ?? $user->latitude ) :  $preferences->Default_latitude ;
        $longitude =!empty($request->longitude) ? ($request->longitude ?? $user->longitude ) :  $preferences->Default_longitude ;
        $type = $request->has('type') ? $request->type : 'delivery';

        // $wishlist = UserVendorWishlist::with('vendor')->where('user_id', Auth::id())->get();
        $vendors = Vendor::wherehas('wishlistByUsers', function($q){
            $q->where('user_id', Auth::id());
        })->withAvg('product', 'averageRating','closed_store_order_scheduled')->get();

        $latitude = ($latitude) ? $latitude : $preferences->Default_latitude;
        $longitude = ($longitude) ? $longitude : $preferences->Default_longitude;

        foreach($vendors as $vendor){
            $vendor = $this->getVendorDistanceWithTime($latitude, $longitude, $vendor, $preferences, $type);
        }

        if(count($vendors)){
            return response()->json([
                'success' => 200,
                'message' => __('List for all wishlist vendors.'),
                'data' => $vendors
            ]);
        }
        return response()->json([
            'success' => 200,
            'message' => __('No Record Found.'),
            'data' => []
        ]);
    }
}
