<?php

namespace App\Http\Controllers\Front;

use Auth;
use Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\VendorTrait;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Front\FrontController;
use App\Http\Traits\{ProductActionTrait};
use App\Models\{Currency, Banner,Tag ,Category, Brand, Product, ProductCategory, VendorSocialMediaUrls, ClientLanguage, Vendor, VendorCategory, ClientCurrency, ProductVariantSet,CabBookingLayout,ProductTag,Facilty,WebStylingOption,VendorSection};
use Log;
class RecentViewProductController extends FrontController
{
    use ApiResponser;
    use VendorTrait,ProductActionTrait;
    private $field_status = 2;


    public function viewAll(){
        $langId = Session::get('customerLanguage');
        $vendorType = Session::get('vendorType');
        if(!$vendorType){
           $vendorType = 'delivery';
        }
        $preferences = (object)Session::get('preferences');
        $navCategories = $this->categoryNav($langId);
       
        $pagiNate = (Session::has('cus_paginate')) ? Session::get('cus_paginate') : 30;
     
        $recent_ids = $this->getRecentProductIds();
        $rc_ids = [];
        if(sizeof($recent_ids) > 0){
             $rc_ids = $recent_ids->toArray();
        } else {
            return [];
        }
        $ses_vendors = $this->getServiceAreaVendors();

        $recently_viewed = $this->productvendorProducts($ses_vendors, $langId, '', '', $vendorType,'260/100','1');
        $page_title = _('All ').getNomenclatureName('Recent View Product', true);  ;
        $for_no_product_found_html = CabBookingLayout::with('translations')->where('is_active', 1)->where('for_no_product_found_html',1)->orderBy('order_by')->get();
        return view('frontend/recent_view_product_all')->with(['navCategories' => $navCategories,'for_no_product_found_html' => $for_no_product_found_html,'products' => $recently_viewed,'page_title' => $page_title]);
    }
   
}
