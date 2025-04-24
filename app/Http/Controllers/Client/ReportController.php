<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\AhoyController;
use Auth;
use Session;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Product, Order, ClientPreference, OrderProduct, OrderVendor, Vendor, OrderReturnRequest, UserVendor, LuxuryOption, OrderVendorReport,OrderRefund};
use DB;
use Carbon\Carbon;

class ReportController extends BaseController{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reports = array();
        return view('backend/report/index')->with(['reports' => $reports]);
    }

    public function productPerformance()
    {
        $reports = array();
        
        return view('backend/report/productperformance')->with(['reports' => $reports]);
    }

    public function getOrdersListAjax(Request $request)
    {
        $input = $request->all();
        
        if (!empty($input['query'])) {
            $querytext = $input['query'];
            $data = Product::select(["id", "title", "sku"])
                    ->where(function($q) use ($querytext){
                        $q->where("title", "LIKE", "%{$querytext}%")
                        ->orWhere("sku", "LIKE", "%{$querytext}%");
                    })
                    ->where('is_live', 1)
                    ->orderby('title', 'asc')
                    ->offset(0)->limit(100)->get();
        } else {

            $data = Product::select(["id", "title", "sku"])
                    ->where('is_live', 1)
                    ->orderby('title', 'asc')
                    ->offset(0)->limit(100)->get();
        }

        $orders = [];

        if (count($data) > 0) {

            foreach ($data as $order) {
                $orders[] = array(
                    "id" => $order->id,
                    "text" => ($order->title == NULL)?$order->sku:$order->title.' ('.$order->sku.')',
                );
            }
        }
        return response()->json($orders);
    }

    public function getProductReportAjax(Request $request)
    {
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        
        if(!empty($request->limit_filter)):
            $limit = $request->limit_filter;
        else:
            $limit = 20;
        endif;
        
        if($request->tabid == 1):
            $products = Product::with(['media.image', 'vendor:id,name',
                            'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title')->where('language_id', $langId)->first();
                            },
                        ])->select('products.id', 'products.vendor_id', 'products.title', 'products.sku');

            if(!empty($request->date_filter)):
                $date = explode(' to ',$request->date_filter);
                $dateF = $date[0];
                $dateT = $date[1] ?? $date[0];
                    $products->withCount([
                        'OrderProduct' => function ($query) use ($dateF, $dateT) {
                            $query->whereBetween('created_at', [$dateF. " 00:00:00", $dateT. " 00:00:00"]);
                        }]);
            else:
                    $products->withCount('OrderProduct')->having('order_product_count', '>', 0);
            endif;

            
            if(!empty($request->product_select_box)):
                $products->whereIn('products.id', $request->product_select_box);
            endif;
            
            if($limit == "All"):
                $Productdata = $products->orderBy('order_product_count', 'desc')->get();
            else:
                $Productdata = $products->orderBy('order_product_count', 'desc')->offset(0)->limit($limit)->get();
            endif;
        endif;  
        
        if($request->tabid == 2):
            $products = Product::with(['media.image', 'vendor:id,name',
                            'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title')->where('language_id', $langId)->first();
                            },
                        ])->select('products.id', 'products.vendor_id', 'products.title', 'products.sku');

            if(!empty($request->date_filter)):
                $date = explode(' to ',$request->date_filter);
                $dateF = $date[0];
                $dateT = $date[1] ?? $date[0];
                    $products->withCount([
                        'UserWishlist' => function ($query) use ($dateF, $dateT) {
                            $query->whereBetween('added_on', [$dateF. " 00:00:00", $dateT. " 00:00:00"]);
                        }])->having('user_wishlist_count', '>', 0);
            else:
                    $products->withCount('UserWishlist')->having('user_wishlist_count', '>', 0);
            endif;

            $products->orderBy('user_wishlist_count', 'desc');
            if(!empty($request->product_select_box)):
                $products->whereIn('products.id', $request->product_select_box);
            endif;
            
            if($limit == "All"):
                $Productdata = $products->get();
            else:
                $Productdata = $products->offset(0)->limit($limit)->get();
            endif;
        endif; 

        if($request->tabid == 3):
            $products = Product::with(['media.image', 'vendor:id,name', 'OrderProduct:id',
                            'translation' => function ($q) use ($langId) {
                                $q->select('product_id', 'title')->where('language_id', $langId)->first();
                            },
                        ])->select('products.id', 'products.vendor_id', 'products.title', 'products.sku');

            if(!empty($request->date_filter)):
                $date = explode(' to ',$request->date_filter);
                $dateF = $date[0];
                $dateT = $date[1] ?? $date[0];
                    $products->withCount([
                        'OrderReturnRequest' => function ($query) use ($dateF, $dateT) {
                            $query->whereBetween('added_on', [$dateF. " 00:00:00", $dateT. " 00:00:00"]);
                        }])->having('order_return_request_count', '>', 0);
            else:
                    $products->withCount('OrderReturnRequest')->having('order_return_request_count', '>', 0);
            endif;

            $products->orderBy('order_return_request_count', 'desc');
            if(!empty($request->product_select_box)):
                $products->whereIn('products.id', $request->product_select_box);
            endif;
            
            if($limit == "All"):
                $Productdata = $products->get();
            else:
                $Productdata = $products->offset(0)->limit($limit)->get();
            endif;
        endif; 

        return response()->json(array('success' => true, 'procount'=> count($Productdata), 'productdata'=>(!empty($Productdata))?$Productdata:array()));
    }
}
