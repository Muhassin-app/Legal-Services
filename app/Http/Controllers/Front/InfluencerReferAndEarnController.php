<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\InfluencerUser;
use Illuminate\Http\Request;
use App\Models\{InfluencerCategory, InfluencerAttribute, ReferEarnDetail, OrderVendor};
use Auth;
use Session;
use App\Http\Traits\InfluencerTrait;
use Illuminate\Support\Facades\Validator;

class InfluencerReferAndEarnController extends Controller
{
    use InfluencerTrait;
    function index(Request $request) {
        $user =  Auth::user();
        $influencer_user = [];
        $influencer_category = [];
        if (checkTableExists('influencer_users')) {
            $influencer_user = InfluencerUser::with('user', 'tier', 'promo')->where('user_id', $user->id)->first();
        }
        if (checkTableExists('influencer_categories')) {
            $influencer_category = InfluencerCategory::get();
        }

        $order_user_promo_product = OrderVendor::with(['user', 'orderDetail'])->where(['coupon_id' => @$influencer_user->promo->id])->get();

        return view('frontend/account/referAndEarn')->with(['influencer_category' => $influencer_category, 'influencer_user' => $influencer_user, 'order_user_promo_product' => $order_user_promo_product]);
    }

    function getReferEarnForm(Request $request, $domain, $id) {
        
        $productAttributes = [];
        $influencer_category = InfluencerCategory::find($id);
        if( checkTableExists('influ_attributes') ) {
            // , 'varcategory.cate.primary'
            $productAttributes = InfluencerAttribute::with('option')
                ->select('influ_attributes.*')
                ->join('influ_attr_cat', 'influ_attr_cat.attribute_id', 'influ_attributes.id')
                ->where('influ_attr_cat.category_id', $id)
                ->where('influ_attributes.status', '!=', 2)
                ->orderBy('position', 'asc')->get();
        }
        
        
        return view('frontend/account/referAndEarnForm')->with(['productAttributes' => $productAttributes, 'influencer_category' => $influencer_category]);
    }

    function save(Request $request) {
        try {
            if(@$request->kyc){
                $validator = Validator::make($request->all(), [
                    'adhar_front' => 'required',
                    'adhar_back' => 'required',
                    'adhar_number' => 'required',
                    'upi_id' => 'required',
                    'account_name' => 'required',
                    'bank_name' => 'required',
                    'account_number' => 'required',
                    'ifsc_code' => 'required',
                ]);
        
                if ($validator->fails()) {
                    foreach ($validator->errors()->toArray() as $error_key => $error_value) {
                        $errors['error'] = __($error_value[0]);
                        return response()->json($errors, 422);
                    }
                }
                InfluencerTrait::saveKycData($request);
            }
            if(@$request->attribute && !empty($request->attribute) ) {
            
                $insert_arr = [];
                $insert_count = 0;
                $user_id = Auth::user()->id;

                $influencer_user_id = InfluencerUser::insertGetId([
                    "user_id" => $user_id
                ]);
    
                foreach($request->attribute as $key => $value) {
                    if( !empty($value) && !empty($value['option'] && is_array($value) )) {
                        
                        if(!empty($value['type']) && $value['type'] == 1 ) { // dropdown
                            $value_arr = @$value['value'];
                            
                            foreach( $value['option'] as $key1 => $val1 ) {
                                if( @in_array($val1['option_id'], $value_arr) ) {
    
                                    $insert_arr[$insert_count]['influencer_user_id'] = $influencer_user_id ;
                                    $insert_arr[$insert_count]['user_id'] = $user_id;
                                    $insert_arr[$insert_count]['attribute_id'] = $value['id'];
                                    $insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
                                    $insert_arr[$insert_count]['attribute_option_id'] = $val1['option_id'];
                                    $insert_arr[$insert_count]['key_value'] = $val1['option_id'];
                                }
                                $insert_count++;
                            }
                        }
                        else {
                            foreach($value['option'] as $option_key => $option) {
                                if(@$option['value']){
                                    $insert_arr[$insert_count]['influencer_user_id'] = $influencer_user_id ;
                                    $insert_arr[$insert_count]['user_id'] = $user_id;
                                    $insert_arr[$insert_count]['attribute_id'] = $value['id'];
                                    $insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
                                    $insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
                                    $insert_arr[$insert_count]['key_value'] = $option['value'] ?? $option['option_title'];
                                }
                                $insert_count++;
                            }
                        }
                    }
    
                
                }
                ReferEarnDetail::insert($insert_arr);
                Session::flash('success', 'Thanks for registering with us');
                // return redirect()->back();
               
            }
            
            return redirect()->route('refer-earn.index');
        } catch (\Exception $e) {
            Session::flash('danger', 'Something went wrong');
            return redirect()->route('refer-earn.index');
        }
        
    }

    public function updateRefferalCode(Request $request){
        $validator = Validator::make($request->all(), [
            'refferal_code' => 'required|unique:influencer_users,reffered_code,'.$request->influencer_user_id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        InfluencerUser::where('id', $request->influencer_user_id)->update(['reffered_code' => $request->refferal_code]);
        return response()->json(['success' => 'Refferal code updated successfully.']);
    }
}
