<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\BidController;
use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ToasterResponser;
use App\Http\Controllers\Client\{BaseController};
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\HomeController;
use App\Models\{Bid, BidProduct, BidRequest, UserVendor, Vendor};
use App\Http\Traits\{ApiResponser,BiddingCartTrait};
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;

class VendorBidController extends BaseController{
    use ApiResponser,BiddingCartTrait;
    use ToasterResponser;

    public function __construct(){
        
    }

    public function bidRequests(Request $request,$domain = '',$id = null)
    {
        $prescriptions = BidRequest::withCount(['bid'=>function($q) use ($id)
        {
            $q->where('vendor_id',$id);
        }])->where('status' , '=' , 0)->orderBy('id','desc')->get();
        return view('backend.bidding_module.vendorBidRequests', compact('prescriptions','id'));
    }

    public function storeBidRequests(Request $request)
    {
        
    try{
            $vendors = Vendor::where('status','1');
            if (Auth::user()->is_superadmin == 0) {
                $vendors = $vendors->whereHas('permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            $prod_vendor =  $vendors->first();

            if(!$prod_vendor){
                    // Session::flash('error', 'Somthing went wrong!');
                    // return $this->successResponse(__('Somthing went wrong!'),'400');
                    return redirect()->back()->with('error','Somthing went wrong!');
            }
            $vendor_id = $prod_vendor->id;
            
            $products    = json_decode($request->data,true);
            $discount = $products[0]['discount'];
            $vendor_id = $products[0]['vendor_id'];
            $prescription_id = $products[0]['prescription_id'];

            if($products){
                $total = 0;
                foreach ($products as $key => $bidTotal) {
                    $total += $bidTotal['qty'] * $bidTotal['price'];
                }
                $amountPayable = $total - ($total * ($discount/ 100));
                $data = [
                    'bid_req_id' => (int) $prescription_id,
                    'vendor_id'    => $vendor_id,
                    'discount'     => $discount,
                    'bid_total'    => $total,
                    'final_amount' => $amountPayable,
                    'bid_order_number'=> time()
                ];

                $vendor_bids = Bid::create($data);
                $total = 0;
                foreach ($products as $key => $data) {
                    $total = $data['qty'] * $data['price'];
                $bids = BidProduct::create([
                    'bid_id'       =>  $vendor_bids->id,
                    'product_id'   =>  $data['id'],
                    'quantity'     =>  $data['qty'],
                    'price'        =>  $data['price'],
                    'total'        =>  $total,
                    ]);
                }

            }
            $bidUserId = BidRequest::where('id' ,$prescription_id)->first();
            $sendNoti = New BidController();
            $sendNoti->sendBidPushNotificationUser($bidUserId->user_id);

            Session()->flash('success', 'Bid Placed Successfully');
            return ['status'=>1];
        }catch(\Exception $e)
        {
            Session()->flash('error', $e->getMessage());
            return ['status'=>0];
        }
    //    return redirect()->back()->with('success','Bid Placed Successfully');
    }


    public function search(Request $request)
    {
        $response = [];
        $keyword = $request->input('keyword');
        $vendorId[] = $request->input('vendor_id');
        $language_id = Session()->get('customerLanguage')??1;
        // $area = new FrontController();
        // $allowed_vendors = $area->getServiceAreaVendors();
        $response  = $this->searchProduct($language_id,$keyword,$vendorId);
        return $this->successResponse($response);
    }

}
