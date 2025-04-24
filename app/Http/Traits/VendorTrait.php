<?php
namespace App\Http\Traits;

use DB;
use Auth;
use HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\{Client as CP,SubscriptionInvoicesVendor};
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\{User, UserVendor, VendorAdditionalInfo, VendorMultiBanner,WebStylingOption};


trait VendorTrait{

    /**
     * getMultiBanner
     *
     * @param  mixed $vendor_id
     * @return void
     */

    public function getMultiBanner($vendor_id){
        $webStyle =   WebStylingOption::where(['is_selected'=>'1'])->first('id');
        $banner = [];
        if($webStyle && ($webStyle->id == 6)){
            $banner = VendorMultiBanner::where(['vendor_id'=> $vendor_id])->whereNotNull('image')->get();
        }
        $respons=[
            'webStyleId' => $webStyle->id,
            'banner' => $banner,
        ];
        return $respons;
     
    }
    public function getVendorActiveSubscription($vendor_id){
     

        $now = Carbon::now()->toDateTimeString();
        $vendor_on_subcription = SubscriptionInvoicesVendor::where('end_date', '>=', $now)->where('vendor_id',$vendor_id)->first();
        return  $vendor_on_subcription ;
    }

    public function getSubscriptionVendorId(){
        $now = date('Y-m-d') ;
       
        $query = "SELECT subscription_invoices_vendor.* FROM `subscription_invoices_vendor`  where (`subscription_invoices_vendor`.order_count > (select count(id) from order_vendors where order_vendors.vendor_id = subscription_invoices_vendor.vendor_id and order_vendors.subscription_invoices_vendor_id = `subscription_invoices_vendor`.id)) and date(subscription_invoices_vendor.end_date) >= ?";
        $order_count = DB::select( DB::raw($query), [$now]);
        $vendor_id = array_column( $order_count,'vendor_id');
       return  $vendor_id;
    }

    public function updateVendorAdditionalPreference($id,$additionalData)
    {
        return  VendorAdditionalInfo::updateOrCreate(
                ['vendor_id'=> $id],
                $additionalData
            );
    }


    public function removeVendorPermissionAndRole($id)
    {
        $cnt = UserVendor::where('user_id',$id)->count();
        if($cnt==0){
            //Remove permission of is_admin from user table
            User::whereId($id)->update(['is_admin'=>0]);
            DB::table('model_has_roles')->where('model_id',$id)->delete();
        }

    }



}
