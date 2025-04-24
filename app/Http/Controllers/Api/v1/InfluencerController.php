<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\InfluencerAttribute;
use App\Models\InfluencerCategory;
use App\Models\InfluencerUser;
use Illuminate\Http\Request;
use Auth;
use App\Http\Traits\ApiResponser;
use App\Models\ReferEarnDetail;
use App\Http\Traits\InfluencerTrait;
use Illuminate\Support\Facades\Validator;
use Session;

class InfluencerController extends Controller
{
    use ApiResponser, InfluencerTrait;
    function index(Request $request) {
        $user =  Auth::user();
        $influencer_user = [];
        $influencer_category = [];
        if (checkTableExists('influencer_users')) {
            $influencer_user = InfluencerUser::with('user')->where('user_id', $user->id)->first();
        }
        if (checkTableExists('influencer_categories')) {
            $influencer_category = InfluencerCategory::get();
        }
        $data = ['influencer_category' => $influencer_category, 'influencer_user' => $influencer_user];
        return $this->successResponse($data);
    }

    function getInfluencerForm(Request $request, $id) {
        
        $productAttributes = [];
        if( checkTableExists('influ_attributes') ) {
            // , 'varcategory.cate.primary'
            $productAttributes = InfluencerAttribute::with('option')
                ->select('influ_attributes.*')
                ->join('influ_attr_cat', 'influ_attr_cat.attribute_id', 'influ_attributes.id')
                ->where('influ_attr_cat.category_id', $id)
                ->where('influ_attributes.status', '!=', 2)
                ->orderBy('position', 'asc')->get();
        }
        $data = ['attributes' => $productAttributes];
        return $this->successResponse($data);
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
            if( !empty($request->attribute) ) {
            
                $insert_arr = [];
                $insert_count = 0;
                $user_id = Auth::user()->id;

                $influencer_user_id = InfluencerUser::insertGetId([
                    "user_id" => $user_id
                ]);
                $attribute = json_decode($request->attribute, true);
                foreach($attribute as $key => $value) {
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
                
                
            }
            
            return $this->successResponse('', 'Thanks for registering with us');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
        
    }
}
