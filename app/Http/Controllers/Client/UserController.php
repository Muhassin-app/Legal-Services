<?php

namespace App\Http\Controllers\Client;

use Auth;
use Image;
use Password;
use DataTables;
use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\UserVendor;
use App\Models\PermissionsOld;
use Illuminate\Http\Request;
use App\Models\UserPermissions;
use App\Models\Timezone;
use Illuminate\Support\Str;
use App\Imports\CustomerImport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Notifications\PasswordReset;
use App\Http\Traits\ToasterResponser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExport;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\VendorTrait;
use App\Models\UserDevice;
use Session;
use DB;
use Spatie\Permission\Models\Role;
use App\Models\{Payment, User, Client, ClientPreference, Country, CsvCustomerImport, Currency, Language, UserVerification, RoleOld, Transaction, UserDocs, UserRegistrationDocuments, OrderVendor, VendorOrderStatus, ClientCurrency, Company, ServiceArea};
use Illuminate\Support\Facades\DB as FacadesDB;

class UserController extends BaseController
{
    use ApiResponser,VendorTrait;
    private $folderName = '/profile/document';

    public function __construct()
    {
        $code = Client::orderBy('id', 'asc')->value('code');
        $this->folderName = '/' . $code . '/user/document';
    }
    use ToasterResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        if(!auth()->user()->can('customers-view') && !auth()->user()->is_superadmin)
        {
            return redirect('client/dashboard')->with('error','You do not have permission to do this task.');
        }
        $roles = RoleOld::all();
        $countries = Country::all();
        //exclude lawyer user ids
        $lawyerUserIds = FacadesDB::table('model_has_roles')
        ->where('role_id', 7)
        ->pluck('model_id')
  


      ->toArray();
        $active_users = User::where('status', 1)->where('is_superadmin', '!=', 1)->whereNotIn('id', $lawyerUserIds)->count();
        $inactive_users = User::where('status', 3)->whereNotIn('id', $lawyerUserIds)->count();
        $users = User::withCount(['orders', 'activeOrders'])->where('status', '!=', 3)->where('is_superadmin', '!=', 1)->whereNotIn('id', $lawyerUserIds)->orderBy('id', 'desc')->paginate(10);
        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        $social_logins = 0;
        foreach ($users as  $user) {
            if (!empty($user->facebook_auth_id)) {
                $social_logins++;
            } elseif (!empty($user->twitter_auth_id)) {
                $social_logins++;
            } elseif (!empty($user->google_auth_id)) {
                $social_logins++;
            } elseif (!empty($user->apple_auth_id)) {
                $social_logins++;
            }
        }
        $csvCustomers = CsvCustomerImport::all();
        $companies = Company::get();
        if(!auth()->user()->is_superadmin && !auth()->user()->can('can-manage-admins')){
            $users = $users->where('is_admin',0);
            $active_users = User::where('status', 1)->where('is_superadmin', '!=', 1)->where('is_admin',0)->whereNotIn('id', $lawyerUserIds)->count();
            $inactive_users = User::where('status', 3)->where('is_admin',0)->whereNotIn('id', $lawyerUserIds)->count();
        }
        return view('backend/users/index')->with(['inactive_users' => $inactive_users, 'social_logins' => $social_logins, 'active_users' => $active_users, 'users' => $users, 'roles' => $roles, 'countries' => $countries, 'csvCustomers' => $csvCustomers, 'user_registration_documents' => $user_registration_documents,'companies'=>$companies]);
    }

    public function getFilterData(Request $request)
    {


        $current_user = Auth::user();
          //exclude lawyer user ids
          $lawyerUserIds = FacadesDB::table('model_has_roles')
          ->where('role_id', 7)
          ->pluck('model_id')
          ->toArray();
        $users = User::with('orders')->withCount(['orders', 'currentlyWorkingOrders'])->where('is_superadmin', '!=', 1)->whereNotIn('id', $lawyerUserIds)->orderBy('id', 'desc');

        if (!empty($request->date_filter)) {
            $date = explode(",", $request->date_filter);
            // dd($date);
            if (isset($date[0]) && isset($date[1])) {

                $e_day      = date('Y-m-d', strtotime($date[1]. ' + 1 day'));
                $start_date = Carbon::parse($date[0])->format('Y-m-d');
                $end_date   = Carbon::parse($e_day)->format('Y-m-d');
                $start_date = $start_date . ' 00:00:00';
                $end_date   = $end_date . ' 00:00:00';
                $query      = 'SELECT * FROM users WHERE EXISTS (SELECT 1 FROM orders WHERE orders.user_id = users.id AND orders.created_at >= ? AND orders.created_at <= ?)';

                $user_ids   = DB::select($query, [$start_date, $end_date]);
                $user_ids   = array_column($user_ids, 'id');

                $users = User::with('orders')->withCount(['orders', 'currentlyWorkingOrders'])->whereNotIn('id',$user_ids)->where('is_superadmin', '!=', 1)->whereNotIn('id', $lawyerUserIds)->where('created_at', '<=', $end_date )
                ->orderBy('id', 'desc');




            }
        }


        if ($request->type == 'active') {
                $users->where('status', 1)->where('is_superadmin', '!=', 1);
        } else if ($request->type == 'inactive') {
            $users->where('status', 3);
        }
        if ($request->company_filter) {
            $users->where('company_id', $request->company_filter);
        }
        if(!auth()->user()->is_superadmin && !auth()->user()->can('can-manage-admins')){
            $users = $users->where('is_admin',0);
        }
        return Datatables::of($users)
            ->addColumn('edit_url', function ($users) {
                return route('customer.new.edit', $users->id);
            })
            ->addColumn('delete_url', function ($users) {
                return route('customer.account.action', [$users->id, 3]);
            })
            ->addColumn('image_url', function ($users) {
                return $users->image['proxy_url'] . '40/40' . $users->image['image_path'];
            })
            ->addColumn('user_type', function ($users) {
                if ($users->hasRole('Admin')) {
                    return 'Admin';
                } elseif ($users->hasRole('Lawyer')) {
                    return 'Lawyer';
                } elseif ($users->hasRole('Vendor')) {
                    return 'Vendor';
                } else {
                    return 'Customer'.((count($users->getRoleNames())>0)?
                    ' ('.$users->getRoleNames()[0].')':'');
                }
            })
            ->addColumn('login_type', function ($users) {
                if (!empty($users->facebook_auth_id)) {
                    return 'Facebook';
                } elseif (!empty($users->twitter_auth_id)) {
                    return 'Twitter';
                } elseif (!empty($users->google_auth_id)) {
                    return 'Google';
                } elseif (!empty($users->apple_auth_id)) {
                    return 'Apple';
                } else {
                    return 'Email';
                }
            })
            ->addColumn('is_superadmin', function ($users) use ($current_user) {
                return $current_user->is_superadmin??'-';
            })
            ->addColumn('refferal', function ($users) {
                return $users->referral->refferal_code ??'-';
            })
            ->addColumn('wallet_id', function ($users) {
                return $users->wallet->id ?? '';
            })
            ->addColumn('signup_date', function ($users) {
                $date = dateTimeInUserTimeZone($users->created_at, $users->timezone);
                return explode(' ', $date)[0];
            })
            ->addColumn('last_login', function ($users) use ($current_user) {
                return is_null($users->last_login_at) ? ' - ' : dateTimeInUserTimeZone($users->last_login_at, $current_user->timezone);
            })
            ->addColumn('total_order_value', function ($users) {
                return decimal_format($users->orders->sum('total_amount'));
            })
            ->addColumn('total_discount_value', function ($users) {
                return decimal_format($users->orders->sum('total_discount'));
            })
            ->addColumn('login_type_value', function ($users) {
                if (!empty($users->facebook_auth_id)) {
                    return $users->facebook_auth_id;
                } elseif (!empty($users->twitter_auth_id)) {
                    return $users->twitter_auth_id;
                } elseif (!empty($users->google_auth_id)) {
                    return $users->google_auth_id;
                } elseif (!empty($users->apple_auth_id)) {
                    return $users->apple_auth_id;
                } else {
                    return $users->email;
                }
            })
            ->addColumn('balanceFloat', function ($users) {
                return decimal_format($users->balanceFloat);
            })
            ->addColumn('edit_url', function ($users) {
                return route('customer.new.edit', $users->id);
            })
            ->addIndexColumn()
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $search = $request->get('search');
                    $instance->where(function ($query) use ($search) {
                        $query->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('email', 'LIKE', '%' . $search . '%')
                            ->orWhere('phone_number', 'LIKE', '%' . $search . '%')
                            ->orWhere('import_user_id', 'LIKE', '%' . $search . '%');
                    });
                }
            }, true)
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteCustomer($domain = '', $uid, $action)
    {
        $user = User::where('id', $uid)->firstOrFail();

        if($user->status == 3)
        {
            User::where('id', $uid)->update([
                'email' => $user->email.'_'.$user->id."_D",
                'phone_number' => $user->phone_number.'_'.$user->id."_D",
                'auth_token' =>'',
                'system_id' =>'',
                'remember_token' => '',
                'facebook_auth_id' => '',
                'twitter_auth_id' => '',
                'google_auth_id' => '',
                'apple_auth_id' => ''
                ]);

            $user->delete();
            return redirect()->back()->with('success', 'Customer account successfully!');
        }
        $user->status = 3;
        $user->save();
        $msg = 'activated';
        if ($action == 2) {
            $msg = 'blocked';
        }
        if ($action == 3) {
            $msg = 'deleted';
        }
        return redirect()->back()->with('success', 'Customer account ' . $msg . ' successfully!');
    }

    /*      block - activate customer account*/
    public function changeStatus(Request $request, $domain = '')
    {
        $user = User::where('id', $request->userId)->firstOrFail();
        $user->status = ($request->value == 1) ? 1 : 2; // 1 for active 2 for block
        $user->save();
        $msg = 'activated';
        if ($request->value == 0) {
            $msg = 'blocked';
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Customer account ' . $msg . ' successfully!',
        ]);
    }

    /**              Add customer             */
    public function show($domain = '', $uid)
    {
        $user = User::where('id', $uid)->firstOrFail();
        return redirect()->back();
    }
    // public function validator(array $data)
    // {


    //     $full_number = '';
    //     if (isset($data['dial_code']) && !empty($data['dial_code']) && isset($data['phone_number']) && !empty($data['phone_number']))
    //         $full_number = '+' . $data['dial_code'] . $data['phone_number'];

    //     $data['phone_number'] = '+' . $data['dial_code'] . $data['phone_number'];
    //     return Validator::make($data, [
    //         'name' => ['required', 'string', 'min:3', 'max:50'],
    //         'email' => ['required', 'email', 'max:50', Rule::unique('users')],
    //         'phone_number' =>  ['required', 'min:7', 'max:15', Rule::unique('users')->where(function ($query) use ($full_number) {
    //          $query->where('phone_number', $full_number);
    //         })],
    //         'password' => ['required', 'string', 'min:6', 'max:50'],


    //     ]);
    // }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $customer = new User();

                    $validation  = Validator::make($request->all(), $customer->rules())->validate();
                //$validator = $this->validator($request->all())->validate();

        $saveId = $this->save($request, $customer, 'false');
        if ($saveId > 0) {
            $user = User::where('id', $saveId)->firstOrFail();
            return response()->json([
                'status' => 'success',
                'message' => 'Customer created Successfully!',
                'data' => $saveId,
                'Userdata' => $user,
                'aaa' => $request->all()
            ]);
        }
    }

    /**
     * save and update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, User $user, $update = 'false')
    {
        $request->contact;
        $request->phone_number;
        $phone = ($request->has('contact') && !empty($request->contact)) ? $request->contact : $request->phone_number;
        $user->name = $request->name;
        $user->dial_code = $request->dial_code;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone_number = $phone;
        $user->is_email_verified = ($request->has('is_email_verified') && $request->is_email_verified == 'on') ? 1 : 0;
        $user->is_phone_verified = ($request->has('is_phone_verified') && $request->is_phone_verified == 'on') ? 1 : 0;
        $user->status = 1;
        if ($request->hasFile('image')) {    /* upload logo file */
            $file = $request->file('image');
            $user->image = Storage::disk('s3')->put('/profile', $file, 'public');
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
                        $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                        UserDocs::updateOrCreate(['user_id' => $user->id, 'user_registration_document_id' => $user_registration_document->id], ['file_name' => $file_name]);
                    }
                } else {
                    UserDocs::updateOrCreate(['user_id' => $user->id, 'user_registration_document_id' => $user_registration_document->id], ['file_name' => $request->$doc_name]);
                }
            }
        }

        $wallet = $user->wallet;
        $userCustomData = $this->userMetaData($user->id, 'web', 'web');
        return $user->id;
    }



    /**
     * Import Excel file for vendors
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importCsv(Request $request)
    {
        if ($request->has('customer_csv')) {
            $csv_vendor_import = new CsvCustomerImport();
            if ($request->file('customer_csv')) {
                $fileName = time() . '_' . $request->file('customer_csv')->getClientOriginalName();
                $filePath = $request->file('customer_csv')->storeAs('csv_customers', $fileName, 'public');
                $csv_vendor_import->name = $fileName;
                $csv_vendor_import->path = '/storage/' . $filePath;
                $csv_vendor_import->status = 1;
                $csv_vendor_import->save();
            }
            $data = Excel::import(new CustomerImport($csv_vendor_import->id), $request->file('customer_csv'));
            return response()->json([
                'status' => 'success',
                'message' => 'File Successfully Uploaded!'
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'File Upload Pending!'
        ]);
    }



    public function edit($domain = '', $id)
    {
        $user = User::where('id', $id)->first();
        return response()->json(array('success' => true, 'user' => $user->toArray()));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function newEdit($domain = '', $id)
    {
        $subadmin = User::find($id);
        $userRole = @$subadmin->roles[0]->id;
        $geoIds = explode(',',$subadmin->geo_ids);

        $permissions = PermissionsOld::where('status', 1)->whereNotin('id', [4, 5, 6, 7, 8, 9, 10, 11, 14, 15, 16, 22, 23, 24, 25])->get();
        $user_permissions = UserPermissions::where('user_id', $id)->get();
        $vendor_permissions = UserVendor::where('user_id', $id)->pluck('vendor_id')->toArray();
        $user_docs = UserDocs::where('user_id', $id)->get();
        $user_registration_documents = UserRegistrationDocuments::get();
        $vendors = Vendor::where('status', 1)->get();
        $active_orders = $this->getUserOrders($id, 'active');
        $completed_orders =  $this->getUserOrders($id, 'completed');
        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        $langId = Session::get('customerLanguage');
        $fixedFee = $this->fixedFee($langId);
        $getAdditionalPreference = getAdditionalPreference(['is_price_by_role']);
        $roles = RoleOld::where('status', 1)->get();
        $rolesNew = Role::where('id','>','0')->where('name','!=','Lawyer')->where('name','!=','Vendor');
        if (! auth()->user()->can('can-manage-admins') && !auth()->user()->is_superadmin) {

            $rolesNew = $rolesNew->where('name', '!=', 'Admin')->where('name', '!=', 'Lawyer')->where('name','!=','Vendor')
                ->Where('name', '!=', 'Super Admin');
        }

        $rolesNew = $rolesNew->get();


        $serviceArea = ServiceArea::all();        // dd($serviceArea);

        return view('backend.users.editUser')->with(['subadmin' => $subadmin, 'vendors' => $vendors, 'permissions' => $permissions, 'user_permissions' => $user_permissions, 'vendor_permissions' => $vendor_permissions, 'user_docs' => $user_docs, 'user_registration_documents' => $user_registration_documents, 'active_orders' => $active_orders, 'completed_orders' => $completed_orders, 'clientCurrency' => $clientCurrency, 'fixedFee' => $fixedFee, 'getAdditionalPreference' => $getAdditionalPreference, 'roles' => $roles,'rolesNew'=>$rolesNew,'userRole'=>$userRole,'serviceArea'=>$serviceArea,'geoIds'=>$geoIds]);
    }
    public function getUserOrders($id, $order_type)
    {
        $user = Auth::user();
        if ($order_type == 'active') {
            $order_status_option_id = [2, 4, 5];
        } elseif ($order_type == 'completed') {
            $order_status_option_id = [3, 6];
        }
        $orders = OrderVendor::with('orderDetail', 'products')->where('user_id', $id)->whereIn('order_status_option_id', $order_status_option_id)->orderBy('id', 'desc')->get();
        foreach ($orders as $key => $order) {
            $order->created_date = dateTimeInUserTimeZone($order->created_at, $user->timezone);
            $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->order_id)->where('vendor_id', $order->vendor_id)->orderBy('id', 'DESC')->first();
            $order->order_status = $vendor_order_status ? __($vendor_order_status->OrderStatusOption->title) : '';
            $product_total_count = 0;
            foreach ($order->products as $product) {
                $product_total_count += $product->quantity * $product->price;
                $product->image_path  = $product->media->first() &&  !is_null($product->media->first()->image) ? $product->media->first()->image->path : getDefaultImagePath();
            }
        }
        return $orders;
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function newUpdate(Request $request, $domain = '', $id)
    {
        $user = User::where('id', $id)->first();

        $vendorRole = @$user->roles[0]->id;
        if(@$vendorRole && $vendorRole == 4){
         //Need to remove vendor permissons from user table
         $this->removeVendorPermissionAndRole($id);
        }
        if ($request->hasFile('image')) {    /* upload logo file */
            $file = $request->file('image');
            $user->image = Storage::disk('s3')->put('/profile', $file, 'public');
        }
        $user->save();
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'status'        => $request->status,
            'role_id'       => $request->has('role_id') ? $request->get('role_id') : $user->role_id,
            'is_admin'      => $request->is_admin,
            'is_superadmin' => 0,
            'is_email_verified' => ($request->has('is_email_verified') && $request->is_email_verified == 'on') ? 1 : 0,
            'is_phone_verified' => ($request->has('is_phone_verified') && $request->is_phone_verified == 'on') ? 1 : 0
        ];
        $data['geo_ids'] = ((@$request->geo_ids)?implode(',',$request->geo_ids):'');
        $client = $user->update($data);

        //Assign user to role for permission
        if(@$request->input('role')){
            if($request->input('role') == 4 && empty($request->vendor_permissions))
            {
               return redirect()->back()->with('error','Select aleast one Vendor.');
            }

            DB::table('model_has_roles')->where('model_id',$id)->delete();
            $user->assignRole($request->input('role'));
        }else{
            DB::table('model_has_roles')->where('model_id',$id)->delete();
        }


        //for updating permissions
        $removepermissions = UserPermissions::where('user_id', $id)->delete();
        if ($request->permissions) {
            $userpermissions = $request->permissions;
            $addpermission = [];
            for ($i = 0; $i < count($userpermissions); $i++) {
                $addpermission[] =  array('user_id' => $id, 'permission_id' => $userpermissions[$i]);
            }
            UserPermissions::insert($addpermission);
        }
        // dd($request->vendor_permissions);
        //for updating vendor permissions
        $removeteampermissions = UserVendor::where('user_id', $id)->delete();
        if ($request->vendor_permissions) {
            $teampermissions = $request->vendor_permissions;
            $addteampermission = [];
            for ($i = 0; $i < count($teampermissions); $i++) {
                $addteampermission[] =  array('user_id' => $id, 'vendor_id' => $teampermissions[$i]);
            }
            UserVendor::insert($addteampermission);
        }
        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        if ($user_registration_documents->count() > 0) {
            foreach ($user_registration_documents as $user_registration_document) {
                $doc_name = str_replace(" ", "_", $user_registration_document->primary->slug);
                if ($user_registration_document->file_type != "Text") {
                    if ($request->hasFile($doc_name)) {
                        $filePath = $this->folderName . '/' . Str::random(40);
                        $file = $request->file($doc_name);
                        $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                        UserDocs::updateOrCreate(['user_id' => $id, 'user_registration_document_id' => $user_registration_document->id], ['file_name' => $file_name]);
                    }
                } else {
                    UserDocs::updateOrCreate(['user_id' => $id, 'user_registration_document_id' => $user_registration_document->id], ['file_name' => $request->$doc_name]);
                }
            }
        }
        // //Need to remove vendor permissons from user table
        // $this->removeVendorPermissionAndRole($id);
        return redirect()->back()->with('success','Customer Updated successfully!');
    }

    public function profile()
    {
        $countries = Country::all();
        $client = Client::first();
        $tzlist = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        $tzlist = Timezone::whereIn('timezone', $tzlist)->get();
        return view('backend/setting/profile')->with(['client' => $client, 'countries' => $countries, 'tzlist' => $tzlist]);
    }

    public function updateProfile(Request $request, $domain = '', $id)
    {
        $user = Auth::user();
        $client = Client::where('code', $user->code)->firstOrFail();
        $rules = array(
            'name' => 'required|string|max:50',
            'phone_number' => 'required|min:7|max:15',
            'company_name' => 'required',
            'company_address' => 'required',
            'country_id' => 'required',
            'timezone' => 'required',
        );
        $validation  = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $data = array();
        foreach ($request->only('name', 'phone_number', 'company_name', 'company_address', 'country_id', 'timezone') as $key => $value) {
            $data[$key] = $value;
        }
        $client = Client::where('code', $user->code)->first();
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $file_name = 'Clientlogo/' . uniqid() . '.' .  $file->getClientOriginalExtension();
            $path = Storage::disk('s3')->put($file_name, file_get_contents($file), 'public');
            $data['logo'] = $file_name;
        } else {
            $data['logo'] = $client->getRawOriginal('logo');
        }

        if ($request->hasFile('dark_logo')) {
            $file = $request->file('dark_logo');
            $file_name = 'Clientlogo/' . uniqid() . '.' .  $file->getClientOriginalExtension();
            $path = Storage::disk('s3')->put($file_name, file_get_contents($file), 'public');
            $data['dark_logo'] = $file_name;
        } else {
            $data['dark_logo'] = $client->getRawOriginal('dark_logo');
        }
        // pr($data);
        $client = Client::where('code', $user->code)->first();
        $client->update($data);
        $userdata = array();
        foreach ($request->only('name', 'phone_number', 'timezone') as $key => $value) {
            $userdata[$key] = $value;
        }
        $user = $user->update($userdata);
        return redirect()->back()->with('success', 'Client Updated successfully!');
    }
    // public function changePassword(Request $request)
    // {
    //     $client = User::where('id', Auth::id())->first();
    //     $validator = Validator::make($request->all(), [
    //         'old_password' => 'required',
    //         'password' => 'required|confirmed|min:6',
    //     ]);
    //     if ($validator->fails()) {
    //         return redirect()->back()->withErrors($validator);
    //     }
    //     if (Hash::check($request->old_password, $client->password)) {
    //         $client->password = Hash::make($request->password);
    //         $client->save();
    //         $clientData = 'empty';
    //         return redirect()->back()->with('success', 'Password Changed successfully!');
    //     } else {
    //         $request->session()->flash('error', 'Wrong Old Password');
    //         return redirect()->back();
    //     }
    // }

    public function changePassword(Request $request)
    {
        $client = User::where('id', Auth::id())->first();
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            $message = $validator->getMessageBag()->toArray();
            $data = array('type' => 'error', 'message' => $message['password'][0]);
            return json_encode($data);
        }
        if (Hash::check($request->old_password, $client->password)) {
            $client->password = Hash::make($request->password);
            $client->save();
            $clientData = 'empty';
            //return redirect()->back()->with('success', 'Password Changed successfully!');
            $data = array('type' => 'success', 'message' => 'Password Changed successfully!');


            // $prefer = ClientPreference::select('mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username','mail_password', 'mail_encryption', 'mail_from', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'theme_admin', 'distance_unit', 'map_provider', 'date_format', 'time_format', 'map_key', 'sms_provider', 'verify_email', 'verify_phone', 'app_template_id', 'web_template_id')->first();
            // $user = Auth()->user();
            //     $phone_number = "+919999999999";
            //     if(!empty($prefer->sms_key) && !empty($prefer->sms_secret) && !empty($prefer->sms_from)){
            //         $to = $phone_number;
            //         $provider = $prefer->sms_provider;
            //         $body = "Dear ".ucwords( $user->name)." password reset successfully.";
            //         $this->sendSms($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
            // }

            return json_encode($data);
        } else {
            $data = array('type' => 'error', 'message' => 'Wrong Old Password');
            return json_encode($data);

            // $request->session()->flash('error', 'Wrong Old Password');
            // return redirect()->back();
        }
    }

    public function filterWalletTransactions(Request $request)
    {
        $pagiNate = 10;
        $trans = Transaction::where('wallet_id', $request->walletId)->orderBy('id', 'desc');
        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        // dd($user_transactions->toArray());
        // foreach ($user_transactions as $key => $trans) {
        //     // $user = User::find($trans->payable_id);
        //     $trans->serial = $key + 1;
        //     $trans->date = Carbon::parse($trans->created_at)->format('M d, Y, H:i A');
        //     // $trans->date = convertDateTimeInTimeZone($trans->created_at, $user->timezone, 'l, F d, Y, H:i A');
        //     $reason = json_decode($trans->meta, true);
        //     $trans->description = $reason['description'] ?? $reason[0];
        //     $trans->amount = $clientCurrency->currency->symbol . sprintf("%.2f", ($trans->amount / 100));
        //     $trans->type = $trans->type;
        // }
        return Datatables::of($trans)
            ->addColumn('date', function ($trans) {
                return Carbon::parse($trans->created_at)->format('M d, Y, H:i A');
            })
            ->editColumn('amount', function ($trans) use ($clientCurrency) {
                return $clientCurrency->currency->symbol . sprintf("%.2f", ($trans->amount / 100));
            })
            ->addColumn('description', function ($trans) {
                $reason = json_decode($trans->meta, true);
                $description = $reason['description'] ?? $reason[0];
                return $description;
            })
            ->addColumn('remarks', function ($trans) {
                $reason = json_decode($trans->meta, true);
                $remarks = $reason['remarks'] ?? '';
                return $remarks;
            })
            ->addColumn('created_by', function ($trans) {
                $reason = json_decode($trans->meta, true);
                $created_by = $reason['created_by'] ?? '';
                if ($created_by > 0) {
                    $user = User::find($created_by)->value('name');
                    return $user;
                } else {
                    return '';
                }
            })
            ->addIndexColumn()
            ->rawColumns(['description'])
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $search = $request->get('search');
                    $instance->where(function ($query) use ($search) {
                        $query->where('created_at', 'LIKE', '%' . $search . '%')
                            ->orWhere('meta', 'LIKE', '%' . $search . '%')
                            ->orWhere('amount', 'LIKE', '%' . $search . '%');
                    });
                }
            })->make(true);
    }

    public function export(Request $request)
    {

        $fileName ="users.xlsx";
        if(!empty($request->start_date) && !empty($request->end_date)){
            $daterange = $request->start_date.' to '.$request->end_date;
            $fileName ="no_order_by_users_for($daterange).xlsx";
        }


        return Excel::download(new CustomerExport($request),$fileName);
    }

    public function save_fcm(Request $request)
    {
        UserDevice::updateOrCreate(['device_token' => $request->fcm_token], ['user_id' => Auth::user()->id, 'device_type' => "web"])->first();
        Session::put('current_fcm_token', $request->fcm_token);
        return response()->json(['status' => 'success', 'message' => 'Token updated successfully']);
    }

    public function customNotification()
    {
        $users = User::withCount(['orders', 'activeOrders'])->where('status', '!=', 3)->where('is_superadmin', '!=', 1)->orderBy('id', 'desc')->paginate(10);
        $social_logins = 0;
        foreach ($users as  $user) {
            if (!empty($user->facebook_auth_id)) {
                $social_logins++;
            } elseif (!empty($user->twitter_auth_id)) {
                $social_logins++;
            } elseif (!empty($user->google_auth_id)) {
                $social_logins++;
            } elseif (!empty($user->apple_auth_id)) {
                $social_logins++;
            }
        }
        return view('backend.users.send_notification')->with(['users' => $users]);
    }

    public function sendNotification(Request $request)
    {
        //dd($request->all());
        if (isset($request->all_customer)) {
            //return $request->all();
            return $customers = User::where('status', 1)->where('is_superadmin', '!=', 1)->orderBy('id', 'desc')->get();
        } else {
            //return "sdfsd";
        }
    }

    public function sendPushNotification($user_ids, $orderData, $header_code = '')
    {
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();

        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            $notification_content = NotificationTemplate::where('id', 4)->first();

            if ($notification_content) {
                if ($header_code == '') {
                    $header_code = Client::orderBy('id', 'asc')->first()->code;
                }
                $code = $header_code;
                $client = Client::where('code', $code)->first();
                $redirect_URL = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/client/order";
                $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);

                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => $redirect_URL,
                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $notification_content->content,
                        'data' => $orderData,
                        'type' => "order_created"
                    ],
                    "priority" => "high"
                ];
                sendFcmCurlRequest($data);
            }
        }
    }

    public function customSearch(Request $request, $domain = '')
    {
        $search = $request->search;
        if (isset($search)) {
            if ($search == '') {
                $users = User::orderby('name', 'asc')->select('id', 'name', 'email')->where('status', '1')->limit(10)->get();
            } else {
                $users = User::orderby('name', 'asc')->select('id', 'name', 'email')->where('status', '1')
                    ->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->limit(10)->get();
            }
            $response = array();
            foreach ($users as $user) {
                $response[] = array("value" => $user->id, "label" => $user->name . '(' . $user->email . ')');
            }

            return response()->json($response);
        } else {
            return response()->json([]);
        }
    }

    public function payReceive(Request $request, $domain = '')
    {
        try {
            $user_id = $request->cusid;
            $user = User::where('id', $user_id)->where('status', 1)->first();
            $amount = $request->amount;
            $wallet = $user->wallet;
            if ($amount > 0) {
                if ($request->payment_type == 1) {
                    $wallet->depositFloat($amount, [
                        'description' => 'Wallet has been <b>Credited</b>',
                        'remarks' => $request->remarks,
                        'created_by' => Auth::id()
                    ]);
                } elseif ($request->payment_type == 2) {
                    if ($amount > $user->balanceFloat) {
                        return $this->errorResponse(__('Amount is greater than customer available funds'), 422);
                    }
                    $wallet->withdrawFloat($amount, [
                        'description' => 'Wallet has been <b>Debited</b>',
                        'remarks' => $request->remarks,
                        'created_by' => Auth::id()
                    ]);
                } else {
                    return $this->errorResponse(__('Invalid Data'), 422);
                }
                return $this->successResponse('', __('Payment is successfully completed'), 201);
            } else {
                return $this->errorResponse(__('Insufficient Amount'), 422);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
