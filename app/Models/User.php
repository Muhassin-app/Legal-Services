<?php

namespace App\Models;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\WalletFloat;
use App\Notifications\PasswordReset;
use Illuminate\Notifications\Notifiable;
use Yadahan\AuthenticationLog\AuthenticationLogable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Contracts\Role;

class User extends Authenticatable implements Wallet, WalletFloat, Auditable
{
    use Notifiable, AuthenticationLogable;
    use \OwenIt\Auditing\Auditable;
    use HasWallet;
    use HasWalletFloat;
    use SoftDeletes;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'name', 'email', 'password', 'description', 'phone_number','dial_code', 'image', 'is_email_verified','email_verified_at', 'is_phone_verified', 'type', 'status', 'device_type', 'device_token', 'country_id', 'role_id', 'auth_token', 'remember_token', 'timezone','import_user_id','last_login_at', 'is_admin','geo_ids','is_presignup', 'custom_allergic_items','last_name','enrollment_no','document','refferal_code'

    ];
    protected $appends = ['loyalty_name'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getLoyaltyNameAttribute(){
        $count_loyalty_points_earned = $this->hasMany('App\Models\Order', 'user_id', 'id')->sum('loyalty_points_earned');
        $loyalty_name = LoyaltyCard::getLoyaltyName($count_loyalty_points_earned);

        $data['loyalty_name'] = $loyalty_name;
        $data['count_loyalty_points_earned'] = $count_loyalty_points_earned;
        return $data;
    }

    public function country(){
       return $this->belongsTo('App\Models\Country')->select('id', 'code', 'name','phonecode');
    }
    public function referral(){
        return $this->hasOne('App\Models\UserRefferal');
     }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token));
    }

    public function address(){
        return $this->hasMany('App\Models\UserAddress');
     }
    public function defaultAddress(){
        return $this->hasOne('App\Models\UserAddress')->where('is_primary',1);
     }

     public function refund(){
        return $this->hasMany('App\Models\OrderRefund', 'user_id', 'id');
     }

    public function role(){
       return $this->belongsTo('App\Models\Role')->select('id', 'role');
    }

    public function device(){
       return $this->hasMany('App\Models\UserDevice');
    }


    public function getImageAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');
      $values['original'] = $value;

      return $values;
    }

    public function rules($id = ''){
        $rules = array(
            'name'          => 'required|string|min:3|max:50',
            'email'         => 'required|email|max:50|unique:users,email,NULL,id,deleted_at,NULL',
            'password'      => 'required|string|min:6|max:50',
            'phone_number'  => 'required|string|min:7|max:15|unique:users,phone_number,NULL,id,deleted_at,NULL',
        );
        $user_registration_documents = UserRegistrationDocuments::with('primary')->get();
        foreach ($user_registration_documents as $user_registration_document) {
            if($user_registration_document->is_required == 1){
                $rules[$user_registration_document->primary->slug] = 'required';
            }
        }

        /*if(!empty($id)){
            $rule['email'] = 'email|max:60|unique:clients,email,'.$id;
            $rule['database_name'] = 'max:60|unique:clients,database_name,'.$id;
        }*/
        return $rules;
    }

    public function orders(){
       return $this->hasMany('App\Models\Order', 'user_id', 'id')->select('id', 'user_id','total_amount','total_discount');
    }

    public function activeOrders(){
       return $this->hasMany('App\Models\Order', 'user_id', 'id')->select('id', 'user_id')
              ->where('is_deleted', '!=', 1);
    }
    public function passbase_verification()
    {
      return $this->hasOne('App\Models\UserVerfication', 'user_id', 'id')->where('verification_option_id',1);
    }

    /**
     * Get All permisions
    */
    public function getAllPermissions()
    {
      return $this->hasMany('App\Models\UserPermissions','user_id','id');
    }


    public function getCodeAttribute($value)
    {
     $value = Client::first();
     return $value->code;
    }

    public function currentlyWorkingOrders(){
        return $this->hasMany('App\Models\Order', 'user_id', 'id')->select('id', 'user_id')
               ->where('is_deleted', '!=', 1)->whereHas('orderStatusVendor', function($query){
                   $query->whereIn('order_status_option_id',[2,4,5]);
               });
    }

    public function loyaltyCard(){
        $count_loyalty_points_earned = $this->hasMany('App\Models\Order', 'user_id', 'id')->sum('loyalty_points_earned');
        //Order::where('user_id',$this->id)->sum('loyalty_points_earned');

        print_r(LoyaltyCard::getLoyaltyName($count_loyalty_points_earned));
        exit();
        //return $count_loyalty_points_earned;
    }

    public function authentication_logs(){
        return $this->hasMany('Yadahan\AuthenticationLog\AuthenticationLog', 'authenticatable_id');
    }

    public function createPermissionsUser(){
        $id = $this->id;
        $permission_details = PermissionsOld::select('id as permission_id',\DB::raw("$id as user_id"))->whereIn('id', [1,2,3,12,17,18,19,20,21])->get()->toArray();
        UserPermissions::insert($permission_details);
    }

    public function bidRequests()
    {
        return $this->hasMany(BidRequest::class, 'user_id');
    }

    public function manager()
    {
        return $this->hasOne(Role::class, 'id');
    }

    public function userVendor()
    {
        return $this->hasOne(UserVendor::class, 'user_id', 'id');
    }

    public function allergicItems()
    {
        return $this->belongsToMany(AllergicItem::class, 'user_allergic_items','user_id','allergic_item_id');
    }

}
