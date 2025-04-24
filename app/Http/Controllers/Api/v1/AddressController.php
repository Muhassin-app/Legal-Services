<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use Config;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{User, Client, StaticDropoffLocation, UserAddress};

class AddressController extends BaseController{
	use ApiResponser;

    public function getAddressList($id = ''){
        $address = UserAddress::where('user_id', Auth::user()->id);
        if($id > 0){
            $address = $address->where('id', $id);
        }
        $address = $address->orderBy('is_primary', 'desc')->orderBy('id', 'desc')->get();
        return response()->json(['data' => $address]);
    }

    public function postSaveAddress(Request $request, $addressId = 0){
        try {
            $validator = Validator::make($request->all(), [
                'address' => 'required',
                'country' => 'required',
            ]);
            $user = Auth::user();
            if($validator->fails()){
                foreach($validator->errors()->toArray() as $error_key => $error_value){
                    $errors['error'] = __($error_value[0]);
                    return response()->json($errors, 422);
                }
            }
            if($request->has('is_primary') && $request->is_primary == 1){
                $add = UserAddress::where('user_id', $user->id)->update(['is_primary' => 0]);
            }
            $address = UserAddress::where('id', $addressId)->where('user_id', $user->id)->first();
            $message = __("Address updated successfully.");
            if(!$address){
                $message = __("Address added successfully.");
                $address = new UserAddress();
                $address->user_id = $user->id;
                $address->is_primary = $request->has('is_primary') ? 1 : 0;
            }
            $request->request->add(['type' =>($request->has('address_type') && $request->address_type < 3) ? $request->address_type : 3]);
            foreach ($request->only('address', 'house_number','street', 'city', 'state', 'latitude', 'longitude', 'pincode', 'phonecode', 'country_code', 'type', 'country', 'type_name','extra_instruction') as $key => $value) {
                $address[$key] = $value;
            }

            $address->save();
            return $this->successResponse($address, $message);
        }catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function postUpdatePrimaryAddress($addressId = 0){
        try {
            $user = Auth::user();
            $address = UserAddress::where('id', $addressId)->where('user_id', $user->id)->first();
            if(!$address){
                return $this->errorResponse(__('Address not found.'), 404);
            }
            $add = UserAddress::where('user_id', $user->id)->update(['is_primary' => 0]);
            $add = UserAddress::where('user_id', $user->id)->where('id', $addressId)->update(['is_primary' => 1]);
            return $this->successResponse('', __('Address is set as primary address successfully.'));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function postDeleteAddress($addressId = 0){
        try {
            $address = UserAddress::where('id', $addressId)->where('user_id', Auth::user()->id)->first();
            if(!$address){
                return $this->errorResponse(__('Address not found.'), 404);
            }
            $address->delete();
            return $this->successResponse($address, __('Address deleted successfully.'));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function staticDropoffLocations()
    {
        $addresses = StaticDropoffLocation::select('*', 'title as name')->get();
        if ($addresses->count()) {
            return $this->successResponse($addresses, __('Address Found successfully.'));
        } else {
            return $this->successResponse([], 'No address found');
        }
    }

}
