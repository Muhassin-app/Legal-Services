<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\FrontController;
use App\Models\{BidRequest,Bid, BidProduct};
use App\Models\Cart;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\{ApiResponser,BiddingCartTrait};
class BiddingController extends Controller
{
    use ApiResponser,BiddingCartTrait;
    public function uploadBiddingPrescription(Request $request, $domain = '')
    {
        $user = Auth::user();
        if ($user) {
            if ($request->hasFile('prescriptions')) {
                    
                $file = $request->file('prescriptions');
                //pr($file);
                $folder = 'bid/prescriptions';
                //foreach ($files as $file) {
                    $file_name = uniqid() .'.'.  $file->getClientOriginalExtension();
                    $s3filePath = '/assets/'.$folder.'/orders' . $file_name;
                    $path = Storage::disk('s3')->put($s3filePath, $file, 'public');
                    $url = Storage::disk('s3')->url($path);
                    $BidRequest = new BidRequest();
                    $BidRequest->user_id   =  $user->id; 
                    $BidRequest->description = $request->description;
                    $BidRequest->prescription =  $url;
                    $BidRequest->bid_number = time();
                    $BidRequest->save();
               // }
            }
        }
        return response()->json(['status' => 'success', 'message' => "Uploaded Successfully"]);
    }

    public function getVendorPrescription(Request $request,$vid){
            $limit = $request->limit??10; 
            $vendor_id = $vid;
            $userBids = BidRequest::withCount(['bid'=>function($q)use($vendor_id){
               $q->where('vendor_id',$vendor_id);
            }])->where('status',0)->orderBy('id','desc')->paginate($limit);
    
            return response()->json($userBids);
    }


    public function getUserPrescription(Request $request){
        $user = Auth::user();
        $langId     = Auth::user()->language;
        $bidPrescription = BidRequest::where('user_id' ,$user->id)->withCount('bids')->with(['bids.vendor','bids.bidProducts.product.translation_one' => function ($q) use ($langId) {
            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
            $q->where('language_id', $langId);
        }])->get();
        return response()->json($bidPrescription);
    }

    public function getbidList($bid_id){
        $user       = Auth::user();
        $langId     = Auth::user()->language;
        $bidPrescription = Bid::where('prescription_id' ,$bid_id)->with(['vendor','bidProducts.product.translation_one' => function ($q) use ($langId) {
            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
            $q->where('language_id', $langId);
        }])->get();
        return response()->json($bidPrescription);
    }

    public function deleteProductPrescription(Request $request){
        if(!empty($request->prescription_id)){
            BidRequest::where('id', $request->prescription_id)->delete();
            return response()->json(['status' => 'success', 'message' => "Prescription remove successfully"]);
        }
    }

    public function search(Request $request,$vid,$key)
    {
        try {
            // dd($key);
            $response = [];
            // $keyword = $request->keyword;
            $vendorId[] = $vid;
            $language_id = Auth::user()->language??1;
            // $area = new FrontController();
            // $allowed_vendors = $area->getServiceAreaVendors();
            $response  = $this->searchProduct($language_id,$key,$vendorId);
            // dd($response);
            return $this->successResponse($response);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
    public function addBidProductToCart(Request $request)
    {
        $this->biddingCart($request->bid);
        return response()->json(['status' => 'success', 'message' => __("Bid  product Added successfully")]);
       
    }

    public function bidReject(Request $request)
    {
        $accept = Bid::where('id', $request->bid)->update(['status'=>2]);
        return response()->json(['status' => 'success', 'message' => __("Bid  reject successfully")]);
    }

    public function bidAccept(Request $request,$domain="",$id)
    {
        $bid_products = BidProduct::where('bid_id', $request->bid)->with('product.variant')->get();
        $CartController  = new CartController();
        foreach($bid_products as $product) {
            $newRequest = new Request();
            $newRequest->merge(['product_id'=> $product->product_id, 'quantity'=>$product->quantity, 'variant_id'=>$product->product->variant[0]->id, 'vendor_id'=>$product->product->vendor_id,'bid_number'=>$id,'bid_discount'=>$product->bids->discount]);
            $data = $CartController->postAddToCart($newRequest);
        }

        return response()->json(['status' => 'success', 'message' => __("Bid accept successfully")]);

    }


    public function placeBid(Request $request)
    {
       $vendors = Vendor::where('status','1');
       if (auth()->user()->is_superadmin == 0) {
           $vendors = $vendors->whereHas('permissionToUser', function ($query) {
               $query->where('user_id', auth()->id);
           });
       }
       $prod_vendor =  $vendors->first();
       if(!$prod_vendor){
            Session::flash('error', 'Somthing went wrong!');
            return $this->successResponse(__('Somthing went wrong!'),'400');
       }
       $vendor_id = $prod_vendor->id;
       $products    = json_decode($request->products);
       $discount = $request->discount;

       $vendor_id = $request->vendor_id;
       $prescription_id = $request->prescription_id;
       if($products){
        $total = 0;
        foreach ($products as $key => $bidTotal) {
            $total += $bidTotal->qty * $bidTotal->price;
        }
        $amountPayable = $total - ($total * ($discount/ 100));
        $data = [
            'bid_req_id' => (int) $prescription_id,
            'vendor_id'    => $vendor_id,
            'discount'     => $discount,
            'bid_total'    => $total,
            'final_amount' => $amountPayable,
            'bid_order_number'   => time()
        ];

        $vendor_bids = Bid::create($data);
        $total = 0;
        foreach ($products as $key => $data) {
            $total = $data->qty * $data->price;
           $bids = BidProduct::create([
              'bid_id'       =>  $vendor_bids->id,
              'product_id'   =>  $data->id,
              'quantity'     =>  $data->qty,
              'price'        =>  $data->price,
              'total'        =>  $total,
            ]);
        }

       }

       return response()->json(['status' => 'success', 'message' => __("Bid Placed successfully")]);
    }


}
