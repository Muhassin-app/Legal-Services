<?php

namespace App\Http\Controllers\Client;

use DB;
use Auth;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;

use App\Models\{Product, ClientCurrency, ClientPreference, LoyaltyCard,OrderProductRating,OrderDriverRating, Vendor};

class ReviewController extends BaseController
{
    /**
     * Display a listing of the country resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $product = Product::whereHas('reviews')->withCount('reviews')->with('translation_one','media.image');
        if (Auth::user()->is_superadmin == 0) {
            $product = $product->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        if ($request->ajax()) {
            return Datatables::of($product)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {

                    $view_product  =   route('product.edit', $row->id);
                    $btn  = '<div class="form-ul"><div class="inner-div"><a href="'.$view_product.'" class="action-icon editIconBtn"><i class="mdi mdi-square-edit-outline" aria-hidden="true"></i></a>';

                    return $btn;
                })
                ->addColumn('view_rating', function ($row) {

                    $view_url  =   route('review.show', [$row->sku]);
                    $btn  = '<div class="form-ul"><div class="inner-div"><a href="'.$view_url.'" class="action-icon editIconBtn"><i class="mdi mdi-eye" aria-hidden="true"></i></a>';

                    return $btn;
                })
                ->addColumn('product_name', function ($row) {

                    $view_url    =  route('review.show', [$row->sku]);
                    $btn  = '<a href="'.$view_url.'" target="_blank" >'.($row->translation_one->title ?? $row->sku ).'</a>';

                   // $btn  = $row->translation_one->title;
                    return $btn;
                })
                ->rawColumns(['action','view_rating','product_name'])
                ->make(true);
        }
        return view('backend.review.index');

    }

    public function getVendorRating(Request $request){
        try {
            //dd($request->all());
            $rating_details = Vendor::where('id',$request->id)->first();
            if(isset($rating_details)){

                if ($request->ajax()) {
                 return \Response::json(\View::make('frontend.modals.vendor_rating', array('rating'=>  $rating_details->admin_rating,'vendor_id' => $request->id ,'rating_details' => $rating_details))->render());
                }

                return $this->successResponse($rating_details,'Rating Details.');
            }
            return \Response::json(\View::make('frontend.modals.vendor_rating', array('rating'=> 0 ,'vendor_id' => $request->id ,'rating_details' => '10'))->render());

            return $this->errorResponse('Invalid rating', 404);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for creating a new country resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
    }

    public function update_vendor_rating(Request $request){
        try{
           $ratings= Vendor::where('id', $request->vendor_id)->update(['admin_rating' => $request->rating]);
           
           if(isset($ratings)) {
            return 'Success';
        }
        return $this->errorResponse('Invalid order', 200);
        
        } catch (Exception $e) {
        return $this->errorResponse($e->getMessage(), 400);
        }
            }
        

    /**
     * Store a newly created country resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified country resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$domain = '',$product_sku)
    {
        $product =  Product::with('translation_one','media.image','vendor','reviews.reviewFiles','reviews.user')->where('sku',$product_sku)->first();
        // echo '<pre>';
        // print_r($product->toArray());
        return view('backend.review.detail',compact('product'));
    }

    /**
     * Show the form for editing the specified country resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
    }

    /**
     * Update the specified country resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        }

    /**
     * Remove the specified country resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$domain = '',$review_id)
    {
        OrderProductRating::where('id',$review_id)->delete();
        return redirect()->back()->with('success', __('Review deleted successfully!'));
    }

      /**
     * update the specified country resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeCategoryStatus($category_id,Request $request)
    {

    }


}

