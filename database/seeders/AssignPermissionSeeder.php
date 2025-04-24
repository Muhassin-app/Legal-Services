<?php
namespace Database\Seeders;

use DB;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){

        $permissions = array(
            //Dashboard Page
            array('id'=>'1','name' => 'dashboard-view','controller'=>'DashBoardController'),
            array('id'=>'2','name' => 'dashboard-weekRevenue','controller'=>'DashBoardController'),
            array('id'=>'3','name' => 'dashboard-locationRevenue','controller'=>'DashBoardController'),
            array('id'=>'4','name' => 'dashboard-monthRevenue','controller'=>'DashBoardController'),
            array('id'=>'5','name' => 'dashboard-totalRevenue','controller'=>'DashBoardController'),

            //Order Page
            array('id'=>'6','name' => 'order-view','controller'=>'OrderController'),
            array('id'=>'7','name' => 'order-accept','controller'=>'OrderController'),

            //Vendor Page
            array('id'=>'8','name' => 'vendor-view','controller'=>'VendorController'),
            array('id'=>'9','name' => 'vendor-add','controller'=>'VendorController'),
            array('id'=>'10','name' => 'vendor-setting','controller'=>'VendorController'),
            array('id'=>'11','name' => 'vendor-catalog','controller'=>'VendorController'),
            array('id'=>'12','name' => 'vendor-config','controller'=>'VendorController'),
            array('id'=>'13','name' => 'vendor-categories','controller'=>'VendorController'),
            array('id'=>'14','name' => 'vendor-payout','controller'=>'VendorController'),
            array('id'=>'15','name' => 'vendor-add-users','controller'=>'VendorController'),

            //Account Page
            array('id'=>'16','name' => 'accounting-view','controller'=>'AccountController'),
            array('id'=>'17','name' => 'accounting-orders','controller'=>'AccountController'),
            array('id'=>'18','name' => 'accounting-loyalty-cards','controller'=>'AccountController'),
            array('id'=>'19','name' => 'accounting-promo-codes','controller'=>'AccountController'),
            array('id'=>'20','name' => 'accounting-taxes','controller'=>'AccountController'),
            array('id'=>'21','name' => 'accounting-vendors','controller'=>'AccountController'),

            array('id'=>'22','name' => 'accounting-payout-request','controller'=>'AccountController'),
            array('id'=>'23','name' => 'accounting-order-refund','controller'=>'AccountController'),
            array('id'=>'24','name' => 'accounting-subscription-discount','controller'=>'AccountController'),


            //Subscription Page
            array('id'=>'25','name' => 'subscription-customer-view','controller'=>'SubscriptionPlansUserController'),
            array('id'=>'26','name' => 'subscription-customer-add','controller'=>'SubscriptionPlansUserController'),
            array('id'=>'27','name' => 'subscription-vendor-view','controller'=>'SubscriptionPlansVendorController'),
            array('id'=>'28','name' => 'subscription-vendor-add','controller'=>'SubscriptionPlansVendorController'),

            //Customers Page
            array('id'=>'29','name' => 'customers-view','controller'=>'UserController'),
            array('id'=>'30','name' => 'customers-add','controller'=>'UserController'),
            array('id'=>'31','name' => 'user-add-role-permission','controller'=>'UserController'),


            //Review Page
            array('id'=>'32','name' => 'review-view','controller'=>'ReviewController'),
            array('id'=>'33','name' => 'review-product-performance','controller'=>'ReportController'),


            //UserController Client Profile Page
            array('id'=>'34','name' => 'setting-profile-view','controller'=>'UserController'),
            array('id'=>'35','name' => 'setting-profile-add','controller'=>'UserController'),

            //ClientPreferenceController Page
            array('id'=>'36','name' => 'setting-customize-view','controller'=>'ClientPreferenceController'),
            array('id'=>'37','name' => 'setting-customize-add','controller'=>'ClientPreferenceController'),


            //WebStylingController Page
            array('id'=>'38','name' => 'setting-webstyle-view','controller'=>'WebStylingController'),


            //AppStylingController Page
            array('id'=>'39','name' => 'setting-appstyle-view','controller'=>'AppStylingController'),


            //PageController Page
            array('id'=>'40','name' => 'cms-pages-view','controller'=>'PageController'),

            //EmailController Page
            array('id'=>'41','name' => 'cms-email-view','controller'=>'EmailController'),



            //NotificationController Page
            array('id'=>'42','name' => 'cms-notification-view','controller'=>'NotificationController'),

            //SmsController Page
            array('id'=>'43','name' => 'cms-sms-view','controller'=>'SmsController'),

            //ReasonController Page
            array('id'=>'44','name' => 'cms-reason-view','controller'=>'ReasonController'),

            //CategoryController Page
            array('id'=>'45','name' => 'category-view','controller'=>'CategoryController'),
            array('id'=>'46','name' => 'category-add','controller'=>'CategoryController'),
            array('id'=>'47','name' => 'variant-view','controller'=>'CategoryController'),
            array('id'=>'48','name' => 'variant-add','controller'=>'CategoryController'),
            array('id'=>'49','name' => 'brand-view','controller'=>'CategoryController'),
            array('id'=>'50','name' => 'brand-add','controller'=>'CategoryController'),
            array('id'=>'51','name' => 'tags-view','controller'=>'CategoryController'),
            array('id'=>'52','name' => 'tags-add','controller'=>'CategoryController'),


            //ClientPreferenceController Page
            array('id'=>'53','name' => 'configuration-view','controller'=>'ClientPreferenceController'),
            array('id'=>'54','name' => 'configuration-add','controller'=>'ClientPreferenceController'),


            //TaxController Page
            array('id'=>'55','name' => 'tax-view','controller'=>'TaxController'),
            array('id'=>'56','name' => 'tax-add','controller'=>'TaxController'),


            //PaymentOption Page
            array('id'=>'57','name' => 'payment-option-view','controller'=>'PaymentOptionController'),
            array('id'=>'58','name' => 'payment-option-add','controller'=>'PaymentOptionController'),


            //DeliveryOption Page
            array('id'=>'59','name' => 'delivery-option-view','controller'=>'DeliveryOptionController'),
            array('id'=>'60','name' => 'delivery-option-add','controller'=>'DeliveryOptionController'),

            //Banners Page
            array('id'=>'61','name' => 'banner-option-view','controller'=>'BannerController'),
            array('id'=>'62','name' => 'banner-option-add','controller'=>'BannerController'),


            //PromocodeController Page
            array('id'=>'63','name' => 'promo-code-view','controller'=>'PromoCodeController'),
            array('id'=>'64','name' => 'promo-code-add','controller'=>'PromoCodeController'),


            //LoyaltyController Page
            array('id'=>'65','name' => 'loyalty-code-view','controller'=>'LoyaltyController'),
            array('id'=>'66','name' => 'loyalty-code-add','controller'=>'LoyaltyController'),

            //campaign Page
            array('id'=>'67','name' => 'campaign-code-view','controller'=>'CampaignController'),
            array('id'=>'68','name' => 'campaign-code-add','controller'=>'CampaignController'),


            //inquiry Page
            array('id'=>'69','name' => 'inquiry-code-view','controller'=>'ProductInquiryController'),

            //ToolsController Page
            array('id'=>'70','name' => 'tool-view','controller'=>'ToolsController'),

            //database-logs Page
            array('id'=>'71','name' => 'database-log-view','controller'=>'ToolsController'),

            //Role Permission page
            array('id'=>'72','name' => 'role-permission','controller'=>'RolePermissionController'),
            
            array('id'=>'73','name' => 'vendor-subscription','controller'=>'VendorSubscriptionController'),

            //Role vendor_config-view
            array('id'=>'74','name' => 'vendor_config-view','controller'=>'VendorSlotController'),

            //Role vendor_pincode-view
            array('id'=>'75','name' => 'vendor_pincode-view','controller'=>'PincodeController'),
            array('id'=>'76','name' => 'vendor_pincode-add','controller'=>'PincodeController'),

            // Product Controller
            array('id'=>'77','name' => 'permission_product_draft-published-view','controller'=>'ProductController'),
            array('id'=>'78','name' => 'permission_product_draft-published-add','controller'=>'ProductController'),
            array('id'=>'79','name' => 'chat-view','controller'=>'ChatController'),
            array('id'=>'80','name' => 'accounting-tax-rate','controller'=>'TaxRateController'),
            array('id'=>'81','name' => 'seller-module','controller'=>'SellerController'),

);

    $supperAdminPermisson = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81];

    $vendorPermisson = [
        1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,55,56,
        63,64,65,66,69,73,79,80,81];
        

        $role = Role::get();
        foreach ($role as $key=> $role) {
            //First Revoke all permission then assign new 
            $role->syncPermissions(); 
            if($role->id == '1'){
                //Assign all selected permisson to role
                $role->syncPermissions($supperAdminPermisson);
            }elseif($role->id == '4'){
                //Assign all selected permisson to role
                $role->syncPermissions($vendorPermisson);
            }
            
        }
       
    
    }
        
}
