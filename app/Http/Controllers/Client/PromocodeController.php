<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Promocode, Product, Vendor, PromoType, Category, PromocodeUser, PromocodeProduct, PromocodeRestriction,PromoCodeDetail};
use Auth;
class PromocodeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $promocodes = Promocode::with('type', 'restriction');
        
        if (Auth::user()->is_superadmin == 0) {
            $promocodes = $promocodes->where('added_by', Auth::user()->id);
           
        }
        $promocodes = $promocodes->get();
        
        
        
        return view('backend/promocode/index')->with(['promocodes' => $promocodes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dataIds = array();
        $promocode = new Promocode();
        $promoTypes = PromoType::where('status', 1)->get();
        $products = Product::select('id', 'sku')->where('is_live', 1);
        if (Auth::user()->is_superadmin == 0) {
            $products = $products->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $products = $products->get();
        
        $vendors = Vendor::select('id', 'name')->where('status', 1);
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendors = $vendors->get();
        $categories = Category::select('id', 'slug')->get();
        $returnHTML = view('backend.promocode.form')->with(['promo' => $promocode,  'promoTypes' => $promoTypes, 'categories' => $categories, 'vendors' => $vendors, 'products' => $products, 'restrictionType' => '', 'include' => '0', 'exclude' => '0', 'dataIds' => $dataIds])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Promocode  $promocode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id = 0){
        $rules = array(
            'name' => 'required|string|max:150||unique:promocodes,name,'.$id,
            'amount' => 'required|numeric',
            'promo_type_id' => 'required',
            'title' => 'required',
            'short_desc' => 'required',
            'expiry_date' => 'required',
            'minimum_spend' => 'required|numeric',
            'maximum_spend' => 'required|numeric',
            'limit_per_user' => 'required|numeric',
            'limit_total' => 'required|numeric',
        );
        if ($request->hasFile('image')) {    /* upload logo file */
            $rules['image'] =  'image|mimes:jpeg,png,jpg,gif';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $promocode = Promocode::findOrFail($id);
        $promoId = $this->save($request, $promocode, 'false');
        if($promoId > 0){
            return response()->json([
                'status'=>'success',
                'message' => 'Promocode updated Successfully!',
                'data' => $promoId
            ]);
        } 
    }

    /**
     * save and update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, Promocode $promocode, $update = 'false'){
        foreach ($request->only('name', 'amount', 'expiry_date', 'promo_type_id', 'minimum_spend', 'maximum_spend', 'limit_per_user', 'limit_total', 'paid_by_vendor_admin','short_desc', 'title', 'promo_visibility') as $key => $value) {
            $promocode->{$key} = $value;
        }
        if ($request->hasFile('image')) {    /* upload logo file */
            $file = $request->file('image');
            $promocode->image = Storage::disk('s3')->put('/promocode', $file,'public');
        }
        $promocode->first_order_only = ($request->has('first_order_only') && $request->first_order_only == 'on') ? 1 : 0;
        $promocode->allow_free_delivery = ($request->has('allow_free_delivery') && $request->allow_free_delivery == 'on') ? 1 : 0;
        $promocode->restriction_on = $request->restriction_on;
        $promocode->Paid_by_vendor_admin = $request->radioInline;
        $promocode->restriction_type = $request->restriction_type == 'include'?  0: 1;
        $promocode->added_by = Auth::id()??null;
        $promocode->save();
        if($promocode->id){
            PromoCodeDetail::where('promocode_id', $promocode->id)->delete();
            if($request->restriction_on == 0){
                $productList = $request->productList;
            }elseif($request->restriction_on == 1){
                $productList = $request->vendorList;
            }elseif($request->restriction_on == 2){
                $productList = $request->categoryList;
            }
            if($productList){
                foreach ($productList as  $refrence_id) {
                    $promo_code_detail = new PromoCodeDetail();
                    $promo_code_detail->promocode_id = $promocode->id;
                    $promo_code_detail->refrence_id = $refrence_id;
                    $promo_code_detail->save();
                }
            }
        }
        return $promocode->id;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array(
             'title' => 'required',
            'short_desc' => 'required',
            'expiry_date' => 'required',
            'promo_type_id' => 'required',
            'amount' => 'required|numeric',
            'limit_total' => 'required|numeric',
            'minimum_spend' => 'required|numeric',
            'maximum_spend' => 'required|numeric',
            'limit_per_user' => 'required|numeric',
            'name' => 'required|string|max:150||unique:promocodes',
        );
        if ($request->hasFile('image')) {    /* upload logo file */
            $rules['image'] =  'image|mimes:jpeg,png,jpg,gif';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $promocode = new Promocode();
        $promoId = $this->save($request, $promocode, 'false');
        if($promoId > 0){
            return response()->json([
                'status'=>'success',
                'message' => 'Promocode created Successfully!',
                'data' => $promoId
            ]);
        } 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Promocode  $promocode
     * @return \Illuminate\Http\Response
     */
    public function edit($domain = '', $id){
        $dataIds = array();
        $promoTypes = PromoType::where('status', 1)->get();
        
        $products = Product::select('id', 'sku')->where('is_live', 1);
        if (Auth::user()->is_superadmin == 0) {
            $products = $products->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $products = $products->get();
        
        $vendors = Vendor::select('id', 'name')->where('status', 1);
        if (Auth::user()->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendors = $vendors->get();




        $promocode = Promocode::with('restriction')->where('id', $id)->first();
        $categories = Category::with('english')->select('id', 'slug')
            ->where('id', '>', '1')->where('status', '!=', '2')
            ->where('can_add_products', 1)->orderBy('parent_id', 'asc')
            ->orderBy('position', 'asc')->get();
        foreach ($promocode->details as $detail) {
            $dataIds[] = $detail->refrence_id;
        }
        $returnHTML = view('backend.promocode.form')->with(['promo' => $promocode, 'promoTypes' => $promoTypes, 'dataIds' => $dataIds, 'categories' => $categories, 'vendors' => $vendors, 'products' => $products])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Promocode  $promocode
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '', $id){
        Promocode::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Promocode deleted successfully!');
    }
}
