<?php

namespace App\Http\Controllers\Client\GiftCard;
use App\Http\Controllers\Client\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session,Log,DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;
use App\Http\Traits\ApiResponser;
use App\Models\{GiftCard,OrderStatusOption,OrderVendor};

use Exception;
class GiftcardController extends BaseController
{
    use ApiResponser;
   
    

    /**
     * Add Block slot for rental
     *
     * @param Request $request
     * @param mixed $name
     * @return void
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
        	$data = GiftCard::where('is_deleted',0);//->get();
           // pr($data->toArray());
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('image_url', function($users) {
                        return $users->image['proxy_url'] . '40/40' . $users->image['image_path'];
                      // return "<img src='.$url.' class='rounded-circle'>";
                    })
                    // ->editColumn('is_user_verified',function($row){
                    //     $modify_url = route('change_agent_status',[$row->id]);
                    //     if($row->is_user_verified)
                    //     $btn = '<input type="checkbox" name="change_status" checked data-bootstrap-switch data-off-color="danger" data-on-color="success"  data-on-text="ACTIVE" data-off-text="INACTIVE" data-href ="'.$modify_url.'">';
                    //     else
                    //     $btn = '<input type="checkbox" name="change_status" data-bootstrap-switch data-off-color="danger" data-on-color="success"  data-on-text="ACTIVE" data-off-text="INACTIVE" data-href ="'.$modify_url.'">';
                    //     return $btn;
                    // })
                    ->editColumn('action', function ($data) use ($request) {
                        $approve_action = '';
                    
                     
                        $action = '<div class="inner-div"> 
                                    <a href="#" class="action-icon editGiftCard" data-gify-card="' . $data->id . '"> <i class="mdi mdi-square-edit-outline"></i></a>
                                    <a href="#" class="action-icon deleteGiftCard"  data-gify-card="' . $data->id . '"> <i class="mdi mdi mdi-delete"></i></a>
                                    </div>
                                    ';
                        return $action;
                    })
                    ->rawColumns(['action','image_url'])
                    ->make(true);
        }
        return view('backend/giftcard/index')->with(['listdata'=>'']);
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
            'amount' => 'required|numeric',
           // 'name' => 'required|string|max:150||unique:gift_cards',
        );
        if ($request->hasFile('image')) {    /* upload logo file */
            $rules['image'] =  'image|mimes:jpeg,png,jpg,gif';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $giftcard = new GiftCard();
        $giftcardReturn = $this->save($request, $giftcard, 'false');
   
        if($giftcardReturn){
            return response()->json([
                'status'=>'success',
                'message' => __('Gift Card created Successfully!'),
                'data' => []
            ]);
        }else{
            return response()->json([
                'status'=>'error',
                'message' => __('Something went wrong. please try again!'),
                'data' => []
            ]);
        }
    }

     /**
     * save and update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\GiftCard  $banner
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, GiftCard $GiftCard, $update = 'false'){
        try{
            foreach ($request->only( 'amount', 'expiry_date','short_desc', 'title') as $key => $value) {
                $GiftCard->{$key} = $value;
            }
            if ($request->hasFile('image')) {    /* upload logo file */
                $file            = $request->file('image');
                $GiftCard->image = Storage::disk('s3')->put('/giftcard', $file,'public');
            }
    
            $GiftCard->added_by = Auth::id()??null;
            $GiftCard->save();
        
            return $GiftCard;
        } catch (Exception $e) {
           // Log::info('add gift Card: '. $e->getCode());
            return [];
        }
    }

     /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\GiftCard  $promocode
     * @return \Illuminate\Http\Response
     */
    public function edit($domain = '', $id){
      
        $GiftCard = GiftCard::where('id', $id)->first();
      
        $returnHTML = view('backend.giftcard.editForm')->with(['GiftCard' => $GiftCard])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\GiftCard  $promocode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id = 0){
        $rules = array(
           // 'name' => 'required|string|max:150||unique:gift_cards,name,'.$id,
            'amount' => 'required|numeric',
            'title' => 'required',
            'short_desc' => 'required',
            'expiry_date' => 'required',
        );
        if ($request->hasFile('image')) {    /* upload logo file */
            $rules['image'] =  'image|mimes:jpeg,png,jpg,gif';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $GiftCard = GiftCard::findOrFail($id);
        $giftcardReturn = $this->save($request, $GiftCard, 'false');
        if($giftcardReturn){
            return response()->json([
                'status'=>'success',
                'message' => __('Gift Card Updated Successfully!'),
                'data' => $giftcardReturn
            ]);
        }else{
            return response()->json([
                'status'=>'error',
                'message' => __('Something went wrong. please try again!'),
                'data' => []
            ]);
        }
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\GiftCard  $promocode
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '', $id){
        try{
            GiftCard::where('id', $id)->delete();
            return response()->json([
                'status'=>'success',
                'message' => __('GiftCard deleted successfully!'),
                'data' => []
            ]);
        } catch (Exception $e) {
           // Log::info('destroy gift Card: '. $e->getCode());
            return response()->json([
                'status'=>'error',
                'message' => $e->getCode(),
                'data' => []
            ]);
        }
        
    }


    public function filter(Request $request){
        try {
            $user = Auth::user();
            $search_value = $request->get('search');
            $timezone = $user->timezone ? $user->timezone : 'Asia/Kolkata';
   
            $vendor_orders_query = OrderVendor::with(['orderDetail.paymentOption','vendor'])->whereHas('orderDetail.giftCard');
            if (Auth::user()->is_superadmin == 0) {
                $vendor_orders_query = $vendor_orders_query->whereHas('vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            if (!empty($request->get('date_filter'))) {
                $date_date_filter = explode(' to ', $request->get('date_filter'));
                $to_date = (!empty($date_date_filter[1]))?$date_date_filter[1]:$date_date_filter[0];
                $from_date = $date_date_filter[0];
                $vendor_orders_query = $vendor_orders_query->between($from_date." 00:00:00", $to_date." 23:59:59");
            }
            if (!empty($request->get('promo_code_filter'))) {
                $promo_code_filter = $request->get('promo_code_filter');
                $vendor_orders_query = $vendor_orders_query->where('coupon_id', $promo_code_filter);
            }
            if (!empty($request->get('status_filter'))) {
                $status_filter = $request->get('status_filter');
                $vendor_orders_query = $vendor_orders_query->where('order_status_option_id', $status_filter);
               
            }
            $vendor_orders = $vendor_orders_query->orderBy('id', 'desc');
            return Datatables::of($vendor_orders)
                ->addColumn('gift_card_amount', function($vendor_orders) {
                    return decimal_format($vendor_orders->orderDetail->gift_card_amount??0);
                })
                ->addColumn('gift_name', function($vendor_orders) {
                    return $vendor_orders->orderDetail->giftCard->name??'Null';
                })
                ->addColumn('gift_amount', function($vendor_orders) {
                    return decimal_format($vendor_orders->orderDetail->giftCard->amount??0);
                })
                ->addColumn('expiry_date', function($vendor_orders) use($timezone) {
                   if(isset($vendor_orders->orderDetail->giftCard->expiry_date)){
                    return dateTimeInUserTimeZone($vendor_orders->orderDetail->giftCard->expiry_date,$timezone);
                   }
                   return 'Null';
                })
                ->addColumn('is_delivery', function($vendor_orders) {
                   // $sender = $vendor_orders->orderDetail->
              
                    if(isset($vendor_orders->orderDetail->userGiftCard)){
                        $Sender = isset($vendor_orders->orderDetail->userGiftCard->buy_for_data)? json_decode($vendor_orders->orderDetail->userGiftCard->buy_for_data) : [];
                        return !empty( $Sender ) ? ((isset($Sender->send_card_is_delivery) && ($Sender->send_card_is_delivery ==1)) ? 'Yes' : 'NO' ) : 'NO';
                    }
                    return 'NO';
                 })
                 ->addColumn('delivery_address', function($vendor_orders) {
                    // $sender = $vendor_orders->orderDetail->
               
                     if(isset($vendor_orders->orderDetail->userGiftCard)){
                         $Sender = isset($vendor_orders->orderDetail->userGiftCard->buy_for_data)? json_decode($vendor_orders->orderDetail->userGiftCard->buy_for_data) : [];
                         return !empty( $Sender ) ? ( (isset($Sender->send_card_to_address) && (!empty($Sender->send_card_to_address) ) )  ? $Sender->send_card_to_address : 'NA' )  : 'NA';
                     }
                     return 'NA';
                  })
                ->addColumn('order_number', function($vendor_orders) {
                    return $vendor_orders->orderDetail ? $vendor_orders->orderDetail->order_number : '';
                })
                ->addColumn('view_url', function($vendor_orders) {
                    if(!empty($vendor_orders->order_id) && !empty($vendor_orders->vendor_id)){
                        return route('order.show.detail', [$vendor_orders->order_id, $vendor_orders->vendor_id]);
                    }else{
                        return '#';
                    }
                })
                ->addColumn('created_date', function($vendor_orders) use($timezone) {
                    return dateTimeInUserTimeZone($vendor_orders->created_at, $timezone);
                })

                ->addColumn('user_name', function($vendor_orders) {
                    return $vendor_orders->user ? $vendor_orders->user->name : '';
                })
                ->addColumn('vendor_name',function($vendor_orders){
                    return $vendor_orders->vendor ? __($vendor_orders->vendor->name) : '';
                })
                ->addIndexColumn()
                ->filter(function ($instance) use ($request) {
                    if (!empty($request->get('search'))) {
                        $search = $request->get('search');
                        $instance->where(function($query) use($search){
                            $query->whereHas('orderDetail', function($q) use($search){
                                $q->where('order_number', 'LIKE', '%'.$search.'%');
                            })
                            ->orwhereHas('orderDetail.giftCard', function($q) use($search){
                                $q->where('name', 'LIKE', '%'.$search.'%');
                            })
                            ->orWhereHas('user', function($q) use($search){
                                $q->where('name', 'LIKE', '%'.$search.'%');
                            })
                            ->orWhereHas('vendor', function($q) use($search){
                                $q->where('name', 'LIKE', '%'.$search.'%');
                            });
                        });
                    }
                })->make(true);
        } catch (Exception $e) {

        }
    }

    
    
    /**
     * redeemedCard
     *
     * @param  mixed $request
     * @return void
     */
    public function redeemedCard(Request $request){
        $order_status_options = OrderStatusOption::where('type', 1)->get();

        //  promo_code_uses_count
        $promo_code_uses_count = OrderVendor::distinct('coupon_code');
        if (Auth::user()->is_superadmin == 0) {
            $promo_code_uses_count = $promo_code_uses_count->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $promo_code_uses_count = $promo_code_uses_count->count('coupon_code');

        /// unique_users_to_use_promo_code_count
        $unique_users_to_use_promo_code_count = OrderVendor::whereNotNull('coupon_id')->distinct('user_id');
        if (Auth::user()->is_superadmin == 0) {
            $unique_users_to_use_promo_code_count = $unique_users_to_use_promo_code_count->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $unique_users_to_use_promo_code_count = $unique_users_to_use_promo_code_count->count('user_id');

        /// admin_paid_total_amt
        $admin_paid_total_amt = OrderVendor::where('coupon_paid_by', 1);
        if (Auth::user()->is_superadmin == 0) {
            $admin_paid_total_amt = $admin_paid_total_amt->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $admin_paid_total_amt = $admin_paid_total_amt->sum('discount_amount');

    /// vendor_paid_total_amt
        $vendor_paid_total_amt = OrderVendor::where('coupon_paid_by', 0);
        if (Auth::user()->is_superadmin == 0) {
            $vendor_paid_total_amt = $vendor_paid_total_amt->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $vendor_paid_total_amt = $vendor_paid_total_amt->sum('discount_amount');

        /// promo_code_options
        $promo_code_options = OrderVendor::whereNotNull('coupon_id')->distinct('coupon_id');

        if (Auth::user()->is_superadmin == 0) {
            $promo_code_options = $promo_code_options->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $promo_code_options = $promo_code_options->get();



        return view('backend/giftcard/giftcardlist', compact('vendor_paid_total_amt','admin_paid_total_amt','promo_code_uses_count','unique_users_to_use_promo_code_count','order_status_options','promo_code_options'));
    }


}
