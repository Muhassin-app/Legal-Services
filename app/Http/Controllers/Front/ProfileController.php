<?php
namespace App\Http\Controllers\Front;

use Auth;
use Session,Str;
use Timezonelist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Front\FrontController;
use App\Models\UserDevice;
use App\Models\{UserWishlist, User, Product, UserAddress, UserRefferal, ClientPreference, Client, Order, Transaction,UserDocs,UserRegistrationDocuments, UserVendor};

class ProfileController extends FrontController
{
    private $folderName = '/profile/image';

    public function __construct()
    {
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/profile/image';
    }
    /**
     * Display send refferal page
     *
     * @return \Illuminate\Http\Response
     */
    public function showRefferal(){
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        return view('frontend/account/sendRefferal')->with(['navCategories' => $navCategories]);
    }

     /**
     * Send Refferal Code
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendRefferalCode(Request $request){
        $rae = UserRefferal::where('user_id', Auth::user()->id)->first()->toArray();
        $otp = $rae['refferal_code'];
        $client = Client::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from')->where('id', '>', 0)->first();
        if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
            $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
            $client_name = $client->name;
            $mail_from = $data->mail_from;
            $sendto = $request->email;
            try{
                Mail::send('email.verify',[
                        'customer_name' => "Link from ".Auth::user()->name,
                        'code_text' => 'Register yourself using this refferal code below to get bonus offer',
                        'code' => $otp,
                        'logo' => $client->logo['original'],
                        'link'=> "http://local.myorder.com/user/register?refferal_code=".$otp,
                ],
                function ($message) use($sendto, $client_name, $mail_from) {
                    $message->from($mail_from, $client_name);
                    $message->to($sendto)->subject('OTP to verify account');
                });
                $response['send_email'] = 1;
            }
            catch(\Exception $e){
                return response()->json(['data' => $e->getMessage()]);
            }
        }
        return response()->json(array('success' => true, 'message' => 'Send Successfully'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request, $domain = ''){
        $curId = Session::get('customerCurrency');
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        $user = User::with('country', 'address')->select('id', 'name', 'email', 'description', 'phone_number', 'dial_code', 'image', 'type', 'country_id', 'timezone')->where('id', Auth::user()->id)->first();
        $user_addresses = UserAddress::where('user_id', Auth::user()->id)->get();
        $refferal_code = UserRefferal::where('user_id', Auth::user()->id)->first();
        if(!$refferal_code){
            $userRefferal = new UserRefferal();
            $userRefferal->refferal_code = $this->randomData("user_refferals", 8, 'refferal_code');
            $userRefferal->user_id = Auth::user()->id;
            $userRefferal->save();
        }


        $timezone_list = Timezonelist::create('timezone', $user->timezone, [
            'id'    => 'timezone',
            'class' => 'styled form-control',
        ]);
        return view('frontend.account.profile')->with(['user' => $user, 'navCategories' => $navCategories, 'userAddresses'=>$user_addresses, 'userRefferal' => $refferal_code,'timezone_list' => $timezone_list]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateAccount(Request $request, $domain = '')
    {

        $phonenumber= str_replace('-', '', $request->phone_number);
        $request->phone_number = str_replace(' ', '', $phonenumber);
        $user = User::where('id', Auth::user()->id)->first();

        $rules = [
            'name' => 'required|string|min:3|max:80',
            'phone_number' => 'required|unique:users',
        ];

        if($user->phone_number == $request->phone_number){
            $rules['phone_number'] = 'required';
        }

        if(!empty($request->email)){
            $rules['email'] = 'email|unique:users,email,'.$user->id.',id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                $errors['error'] = $error_value[0];
                return redirect()->back()->withInput()->withErrors($errors);
            }
        }


        if ($user){
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $user->image = Storage::disk('s3')->put($this->folderName, $file,'public');
            }
            $user->name = $request->name;
            $user->email = $request->email;
            $user->timezone = $request->timezone;
            $user->dial_code = $request->dialCode;
            $user->description = $request->description;
            $user->phone_number = $request->phone_number;
            
            $user->save();
            
            $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
            if ($user_registration_documents->count() > 0) {
                foreach ($user_registration_documents as $user_registration_document) {
                    $doc_name = str_replace(" ", "_", $user_registration_document->primary->slug);
                    if ($user_registration_document->file_type != "Text") {
                        if ($request->hasFile($doc_name)) {
                            $filePath = $this->folderName . '/' . Str::random(40);
                            $file = $request->file($doc_name);
                            $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                            UserDocs::updateOrCreate(['user_id' => $user->id, 'user_registration_document_id' => $user_registration_document->id],['file_name' => $file_name]);
                        }
                    } else {
                        UserDocs::updateOrCreate(['user_id' => $user->id, 'user_registration_document_id' => $user_registration_document->id],['file_name' => $request->$doc_name]);
                    }
                }
            }
            return redirect()->back()->with('success', 'Profile has been updated');
        }
        return redirect()->back()->with('errors', 'Profile updation failed');
    }

    /**
     * Update user timezone.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTimezone(Request $request, $domain = ''){
        $timezone = $request->timezone ? $request->timezone : NULL;
        $user = User::where('id', Auth::user()->id)->first();
        if ($user){
            $user = Auth::user();
            $user->timezone = $timezone;
            Auth::user()->timezone = $timezone;
            $user->save();
            return redirect()->back()->with('success', 'Timezone has been updated');
        }
        return redirect()->back()->with('error', 'Timezone cannot be updated');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function editAccount(Request $request){
        $user = User::select('id', 'name', 'email', 'description', 'phone_number', 'dial_code', 'image', 'type', 'country_id','timezone')->where('id', Auth::user()->id)->first();
        $user_addresses = UserAddress::where('user_id', Auth::user()->id)->get();
        $timezone_list = Timezonelist::create('timezone', $user->timezone, [
            'id'    => 'timezone',
            'class' => 'styled form-control',
        ]);
        $user_docs = UserDocs::where('user_id', Auth::user()->id)->get();
        $user_registration_documents = UserRegistrationDocuments::get();
        $returnHTML = view('frontend.account.edit-profile')->with(['user' => $user,'user_docs'=>$user_docs,'user_registration_documents'=>$user_registration_documents , 'userAddresses' => $user_addresses, 'timezone_list' => $timezone_list])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */ 
    public function changePassword(Request $request, $domain = ''){
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
       return view('frontend/account/changePassword')->with(['navCategories' => $navCategories]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitChangePassword(Request $request, $domain = ''){
        $user = User::where('id', Auth::user()->id)->first();
        $request->validate([
            'old_password' => ['required', function ($attribute, $value, $fail) use ($user) {
            if (!Hash::check($value, $user->password)) {
                $fail('Your old password does not match.');
            }
            }],
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|same:new_password',
        ],[
            'old_password.required' => __('The old password field is required.'),
            'new_password.required' => __('The new password field is required.'),
            'new_password.min' => __('The new password must be at least 6 characters.'),
            'confirm_password.required' => __('The confirm password field is required.'),
            'confirm_password.same' => __('The confirm password and new password must match.'),
        ]);
        if ($user){
            if (Hash::check($request['old_password'], $user->password)) {
                $user->password = Hash::make($request['new_password']);
                $user->save();
            }else{
                return redirect()->route('user.changePassword')->with('error', __('Your Old password is incorrect'));
            }
        }
        return redirect()->route('user.profile')->with('success', __('Your Password has been changed successfully'));
    }

    public function save_fcm(Request $request){
        UserDevice::updateOrCreate(['device_token' => $request->fcm_token],['user_id' => Auth::user()->id, 'device_type' => "web"])->first();
        Session::put('current_fcm_token', $request->fcm_token);
        return response()->json([ 'status'=>'success', 'message' => 'Token updated successfully']);
    }

    //get my ads/products
    public function getMyAds(){
        $user = Auth::user();	
        $user_vendor = UserVendor::where('user_id', $user->id)->pluck('vendor_id')->toArray();
        $products = [];
        if(@$user_vendor){
            $products = Product::with(['media.image', 'primary', 'category.cat', 'category.categoryDetail', 'brand', 'variant' => function ($v) {
                $v->select('id', 'product_id', 'quantity', 'price')->groupBy('product_id');
            }])->select('id', 'sku', 'vendor_id', 'is_live', 'is_new', 'is_featured', 'has_inventory', 'has_variant', 'sell_when_out_of_stock', 'Requires_last_mile', 'averageRating', 'brand_id','minimum_order_count','batch_count', 'title','category_id')
                ->whereIn('vendor_id', $user_vendor)->whereHas('category.categoryDetail', function ($query) {
                    $query->where('type_id','!=','7');
                })->get()->sortBy('primary.title', SORT_REGULAR, false);
        }
        // dd($products);
        return view('frontend.account.my-ads',compact('products'));
    }

    public function getNotification(){
        return view('frontend.account.notifications');
    }

    public function updatePostStatus(Request $request,$domain = ''){
        if ($request->ajax()) {
            $product = Product::where('id', $request->product_id)->update([
                'is_live' => $request->status
            ]);
            return response()->json([ 'status'=>'success', 'message' => 'Post status updated successfully']);
        }
    }

}
