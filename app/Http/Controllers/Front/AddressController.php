<?php

namespace App\Http\Controllers\Front;

use Auth;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Front\FrontController;
use App\Http\Traits\ShipEngineTrait;
use App\Models\{Country, UserWishlist, User,CarImages ,Product, UserAddress};

class AddressController extends FrontController{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use ShipEngineTrait;
    public function index(Request $request, $domain = ''){
        $langId = Session::get('customerLanguage');
        $countries = Country::get();
        $useraddress = UserAddress::where('user_id', Auth::user()->id)->where('status', 1)->orderBy('id','desc')->with('country')->get();
        $navCategories = $this->categoryNav($langId);
        return view('frontend/account/addressbook')->with(['useraddress' => $useraddress, 'navCategories' => $navCategories, 'countries'=>$countries]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request, $domain = ''){
        $address = new UserAddress();
        $langId = Session::get('customerLanguage');
        $countries = Country::all();
        $navCategories = $this->categoryNav($langId);
        return view('frontend/account/editAddress')->with(['navCategories' => $navCategories,'countries' => $countries, 'address' => $address]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $domain = ''){


        $validatedData = $request->validate([
                'type' => 'required',
                // 'city' => 'required',
                // 'state' => 'required',
                // 'pincode' => 'required',
                'address' => 'required',
                'country' => 'required',
        ], [
            'address.required' => __('The address field is required.'),
            'type.required' => __('Address Type is required'),
            'city.required' => __('The city field is required.'),
            'state.required' => __('The state field is required.'),
            'pincode.required' => __('The zip code field is required.'),
        ]);
        $client = getClientPreferenceDetail();

        $country = Country::select('code', 'name')->where('id', $request->country)->first();
        $address = new UserAddress;
        $address->type = $request->type;
        $address->city = $request->city??"";
        $address->state = $request->state??"";
        $address->state_code = $request->state_code??"";
        $address->street = $request->street;
        $address->country = $country->name;
        $address->country_code = $country->code??'US';
        $address->user_id = Auth::user()->id;
        $address->address = $request->address;
        $address->pincode = $request->pincode??"";
        $address->latitude  = $request->latitude;
        $address->longitude  = $request->longitude;
        $address->house_number = $request->house_number??"";
        $address->extra_instruction = $request->extra_instruction??"";

        $msg = __('Address Has Been Added Successfully');
        $status = 'success';

        if (shipEngineEnable()) {
            $res = $this->shipEngineAddressValidate($address);
            if ($res[0]['status'] == 'verified') {
                // $address->state_code = $res[0]['matched_address']['state_province'];
                $address->save();
            }else{
                $msg = $res['message'];
                $status = 'error';
            }
        }else{
            $address->save();
        }

        if($request->ajax()){
            return response()->json(['status' => $status, 'message' => $msg, 'address' => $address]);
        }else{
            return redirect()->route('user.addressBook')->with('success', $msg);
        }
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($domain = '', Request $request, $id){
        $validatedData = $request->validate([
            'type' => 'required',
            'city' => 'required',
            'state' => 'required',
            'pincode' => 'required',
            'address' => 'required',
            'country' => 'required',
        ], [
            'type.required' => __('Address Type is required'),
            'pincode.required' => __('The zip code field is required.')
        ]);
        $country = Country::select('code', 'name')->where('id', $request->country)->first();
        $user = User::where('id', Auth::user()->id)->first();
        if ($user){
            $user->country_id = $request->country;
            $user->save();
        }
        //mark previous entry to delete
        $updateaddress = UserAddress::where('id', $id)->update(['status' => 0]);

        //create a new address
        $prevaddress = UserAddress::find($id);
        $address = new UserAddress;
        $address->user_id = $prevaddress->user_id;
        $address->type = $request->type;
        $address->address = $request->address;
        $address->street = $request->street;
        $address->city = $request->city;
        $address->state = $request->state;
        $address->country = $country->name;
        $address->country_code = $country->code;
        $address->pincode = $request->pincode;
        $address->latitude  = $request->latitude;
        $address->longitude  = $request->longitude;
        $address->house_number = $request->house_number??"";
        $address->extra_instruction = $request->extra_instruction??"";
        $address->is_primary = $prevaddress->is_primary;
        $address->phonecode = $prevaddress->phonecode;
        $address->type_name = $prevaddress->type_name;
        $address->created_at = $prevaddress->created_at;
        $address->save();
        return redirect()->route('user.addressBook')->with('success', __('Address Has Been Updated Successfully'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($domain = '', $id){
        $address = UserAddress::findOrFail($id);
        $langId = Session::get('customerLanguage');
        $countries = Country::all();
        $navCategories = $this->categoryNav($langId);
        return view('frontend/account/editAddress')->with(['navCategories' => $navCategories,'countries' => $countries, 'address' => $address]);
    }

    /**
     * Get address details by id
     *
     * @return \Illuminate\Http\Response
     */
    public function address($domain = '', $id){
        $address = UserAddress::leftJoin('countries', 'user_addresses.country', 'countries.name')
                    ->select('user_addresses.*','countries.id as country_id')
                    ->where('user_addresses.id', $id)->first();
        $countries = Country::all();
        return response()->json(['status' => 'success', 'countries' => $countries, 'address' => $address]);
    }

    /**
     * Set Primary Address for user
     *
     * @return \Illuminate\Http\Response
     */
    public function setPrimaryAddress($domain = '', $id)
    {
        $user = Auth::user();
        $address = UserAddress::where('user_id', $user->id)->update(['is_primary' => 0]);
        $address = UserAddress::where('user_id', $user->id)->where('id', $id)->update(['is_primary' => 1]);
        return redirect()->route('user.addressBook')->with('success', __('Primary Address Has Been Changed Successfully'));
    }

    /**
     * delete address of user
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($domain = '', $id){
        //$address = UserAddress::find($id)->delete();
        $address = UserAddress::where('id', $id)->update(['status' => 0]);
        return redirect()->route('user.addressBook')->with('success', __('Address Has Been Deleted Successfully'));
    }


}
