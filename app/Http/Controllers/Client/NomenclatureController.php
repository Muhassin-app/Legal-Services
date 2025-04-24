<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Models\Nomenclature;
use App\Models\NomenclatureTranslation;
use App\Http\Controllers\Client\BaseController;
use Illuminate\Support\Facades\Cache;

class NomenclatureController extends BaseController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         
        NomenClature::updateOrCreate(['label' => 'vendors'], ['label' => 'vendors']);
        NomenClature::updateOrCreate(['label' => 'sellers'], ['label' => 'sellers']);
        NomenClature::updateOrCreate(['label' => 'Loyalty Cards'], ['label' => 'Loyalty Cards']);
        NomenClature::updateOrCreate(['label' => 'Takeaway'], ['label' => 'Takeaway']);
        NomenClature::updateOrCreate(['label' => 'Search'], ['label' => 'Search']);
        NomenClature::updateOrCreate(['label' => 'Wishlist'], ['label' => 'Wishlist']);
        NomenClature::updateOrCreate(['label' => 'Dine-In'], ['label' => 'Dine-In']);
        NomenClature::updateOrCreate(['label' => 'Delivery'], ['label' => 'Delivery']);
        NomenClature::updateOrCreate(['label' => 'Zip Code'], ['label' => 'Zip Code']);
        NomenClature::updateOrCreate(['label' => 'Want To Tip'], ['label' => 'Want To Tip']);
        NomenClature::updateOrCreate(['label' => 'Fixed Fee'], ['label' => 'Fixed Fee']);        
        NomenClature::updateOrCreate(['label' => 'Royo Dispatcher'], ['label' => 'Royo Dispatcher']);
        NomenClature::updateOrCreate(['label' => 'Referral Code'], ['label' => 'Referral Code']);
        NomenClature::updateOrCreate(['label' => 'Orders'], ['label' => 'Orders']);
        NomenClature::updateOrCreate(['label' => 'Rides'], ['label' => 'Rides']);
        NomenClature::updateOrCreate(['label' => 'Rentals'], ['label' => 'Rentals']);
        NomenClature::updateOrCreate(['label' => 'Pick & Drop'], ['label' => 'Pick & Drop']);
        NomenClature::updateOrCreate(['label' => 'Services'], ['label' => 'Services']);
        NomenClature::updateOrCreate(['label' => 'Laundry'], ['label' => 'Laundry']);
        NomenClature::updateOrCreate(['label' => 'Product Order Form'], ['label' => 'Product Order Form']);
        NomenClature::updateOrCreate(['label' => 'Appointment'], ['label' => 'Appointment']);

        NomenClature::updateOrCreate(['label' => 'Enter Drop Location'], ['label' => 'Enter Drop Location']);
        NomenClature::updateOrCreate(['label' => 'Vendor Name'], ['label' => 'Vendor Name']);
        NomenClature::updateOrCreate(['label' => 'Ride Accepted'], ['label' => 'Ride Accepted']);
        NomenClature::updateOrCreate(['label' => 'Searching For Nearby Drivers'], ['label' => 'Searching For Nearby Drivers']);
        NomenClature::updateOrCreate(['label' => 'Hold On! We are looking for drivers nearby!'], ['label' => 'Hold On! We are looking for drivers nearby!']);
        NomenClature::updateOrCreate(['label' => 'Product Name'], ['label' => 'Product Name']);
        NomenClature::updateOrCreate(['label' => 'IFSC Code'], ['label' => 'IFSC Code']);
        NomenClature::updateOrCreate(['label' => 'Stock Status'], ['label' => 'Stock Status']);
        NomenClature::updateOrCreate(['label' => 'Variant'], ['label' => 'Variant']);
        NomenClature::updateOrCreate(['label' => 'Account Name'], ['label' => 'Account Name']);
        NomenClature::updateOrCreate(['label' => 'Bank Name'], ['label' => 'Bank Name']);
        NomenClature::updateOrCreate(['label' => 'Account Number'], ['label' => 'Account Number']);
        NomenClature::updateOrCreate(['label' => 'IFSC Code'], ['label' => 'IFSC Code']);
        NomenClature::updateOrCreate(['label' => 'Aadhaar Front'], ['label' => 'Aadhaar Front']);
        NomenClature::updateOrCreate(['label' => 'Aadhaar Back'], ['label' => 'Aadhaar Back']);
        NomenClature::updateOrCreate(['label' => 'Aadhaar Number'], ['label' => 'Aadhaar Number']);
        NomenClature::updateOrCreate(['label' => 'UPI Id'], ['label' => 'UPI Id']);
        NomenClature::updateOrCreate(['label' => 'Similar Product'], ['label' => 'Similar Product']);
        NomenClature::updateOrCreate(['label' => 'P2P'], ['label' => 'P2P']);
        NomenClature::updateOrCreate(['label' => 'Where can we pick you up?'], ['label' => 'Where can we pick you up?']);
        NomenClature::updateOrCreate(['label' => 'Where To?'], ['label' => 'Where To?']);
        NomenClature::updateOrCreate(['label' => 'Agree Term'], ['label' => 'Agree Term']);
        NomenClature::updateOrCreate(['label' => 'Recurring'], ['label' => 'Recurring']);
        NomenClature::updateOrCreate(['label' => 'Open'], ['label' => 'Open']);
        NomenClature::updateOrCreate(['label' => 'Products'], ['label' => 'Products']);
        NomenClature::updateOrCreate(['label' => 'Include Gift'], ['label' => 'Include Gift']);
        NomenClature::updateOrCreate(['label' => 'Control Panel'], ['label' => 'Control Panel']);
    
        $label_array = ['vendors','sellers','Loyalty Cards','Takeaway','Search','Wishlist','Dine-In','Delivery','Zip Code','Want To Tip','Fixed Fee','Royo Dispatcher', 'Referral Code',  'Orders', 'Rides','Rentals','Pick & Drop','Services','Laundry','Product Order Form','Appointment','Enter Drop Location','Vendor Name','Ride Accepted','Searching For Nearby Drivers','Hold On! We are looking for drivers nearby!','Product Name','Stock Status', 'Variant', 'Account Name', 'Bank Name', 'Account Number', 'IFSC Code', 'Aadhaar Front', 'Aadhaar Back', 'Aadhaar Number', 'UPI Id', 'Similar Product','P2P','Where can we pick you up?','Where To?','Agree Term','Recurring', 'Open', 'Products','Include Gift', 'Control Panel'];

        $name_array = ['names','seller_names','loyalty_cards_names','takeaway_names','search_names','wishlist_names','dinein_names','delivery_names','zipCode_name','wantToTip_name','FixedFee_name','royo_dispatcher_names','referral_code_names', 'orders_names', 'rides_names','rentals_names','pick_drop_names','on_demand_names','laundry_names','product_order_form_names','appointment_names','enter_drop_location_names','enter_vendor_name_names','ride_accepted_names','search_nearby_driver_names','looking_driver_names','product_names','stock_status_names', 'variant_names', 'account_name', 'bank_name', 'account_number', 'ifsc_code', 'aadhaar_front', 'aadhaar_back', 'aadhaar_number', 'upi_id', 'similar_product','p2p_id','pickup_id','where_to_id','agree_term','recurring', 'open', 'products', 'includeGift_name', 'controlPanel_name'];

        $lang_id_array = ['language_ids','seller_language_ids','loyalty_cards_language_ids','takeaway_language_ids','search_language_ids','wishlist_language_ids','dinein_language_ids','delivery_language_ids','zipCode_language_ids','wantToTip_language_ids','FixedFee_language_ids','royo_dispatcher_language_ids','referral_code_language_ids', 'orders_language_ids', 'rides_language_ids','rentals_language_ids','pick_drop_language_ids','on_demand_language_ids','laundry_language_ids','product_order_form_language_ids','appointment_language_ids','enter_drop_location_language_ids','enter_vendor_name_language_ids','ride_accepted_language_ids','search_nearby_driver_language_ids','looking_driver_language_ids','product_name_language_ids','stock_status_language_ids', 'variant_language_ids', 'account_name_language_ids', 'bank_name_language_ids', 'account_number_language_ids', 'ifsc_code_language_ids', 'aadhaar_front_language_ids', 'aadhaar_back_language_ids', 'aadhaar_number_language_ids', 'upi_id_language_ids', 'similar_product_ids','p2p_language_ids','pickup_language_ids','where_to_language_ids','agree_term_language_ids','recurring_language_ids', 'open_language_ids', 'products_language_ids', 'includeGift_language_ids', 'controlPanel_language_ids'];

        $newrequest = $request->toArray();  
            
        
      
        for($j=0;$j<count($label_array);$j++)
        {
            if (count($newrequest[$name_array[$j]]) > 0) {
                $value_exists = [];
                foreach ($newrequest[$name_array[$j]] as $singlename) {
                    if ($singlename) {
                        $value_exists[] = $singlename;
                    }
                }
                $nomenclature = NomenClature::where('label', $label_array[$j])->first();
          
                if (count($value_exists) > 0) {
                    $this->validate($request, [
                        $name_array[$j].'.0' => 'required|string',
                    ]);
                    $m=0;
                    foreach ($newrequest[$name_array[$j]] as $single_name_array) {
                        if ($single_name_array) { 
                            $nomenclatureTranslation =  Nomenclature::where(['id' => $nomenclature->id])->first();
                            if($nomenclatureTranslation){
                                Cache::forget('nomenclature_' . $newrequest[$lang_id_array[$j]][$m] . '_' .$nomenclatureTranslation->label);
                            }
              
                            NomenclatureTranslation::updateOrCreate(['language_id' => $newrequest[$lang_id_array[$j]][$m], 'nomenclature_id' => $nomenclature->id], ['name' => $single_name_array]);
                        }
                        $m++;
                    }
                } else {
                    NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
                }
            }
        }
        return redirect()->route('configure.customize')->with('success', 'Nomenclature Saved Successfully!');
    }

    public function store_backup(Request $request)
    {     
        $names = $request->names;
        $loyalty_cards_language_ids = $request->loyalty_cards_language_ids;
        $loyalty_cards_names = $request->loyalty_cards_names;
        $takeaway_names = $request->takeaway_names;
        $search_names = $request->search_names;
        $wishlist_names = $request->wishlist_names;
        $dinein_names = $request->dinein_names;
        $delivery_names = $request->delivery_names;
        $zipCode_names = $request->zipCode_name;
        $wantToTip_names = $request->wantToTip_name;
        $FixedFee_names = $request->FixedFee_name;
        $royo_dispatcher_names = $request->royo_dispatcher_names;
        NomenClature::updateOrCreate(['id' => 1], ['label' => 'vendors']);
        NomenClature::updateOrCreate(['id' => 2], ['label' => 'Loyalty Cards']);
        NomenClature::updateOrCreate(['id' => 3], ['label' => 'Takeaway']);
        NomenClature::updateOrCreate(['id' => 4], ['label' => 'Search']);
        NomenClature::updateOrCreate(['id' => 5], ['label' => 'Wishlist']);
        NomenClature::updateOrCreate(['id' => 6], ['label' => 'Dine-In']);
        NomenClature::updateOrCreate(['id' => 7], ['label' => 'Delivery']);
        NomenClature::updateOrCreate(['id' => 8], ['label' => 'Zip Code']);
        NomenClature::updateOrCreate(['id' => 11], ['label' => 'Royo Dispatcher']);      
        

         if (count($names) > 0) {
            $names_value_exists = [];
            foreach ($names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'vendors')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'names.0' => 'required|string',
                ]);
                NomenclatureTranslation::truncate();
                $language_ids = $request->language_ids;
                foreach ($names as $key => $name) {
                    if ($name) {
                        // $nomenclature = NomenClature::where('label', 'vendors')->first();
                        NomenclatureTranslation::updateOrCreate(['language_id' => $language_ids[$key], 'nomenclature_id' => $nomenclature->id], ['name' => $name]);
                        $nomenclature_translation =  new NomenclatureTranslation();
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (count($loyalty_cards_names) > 0) {
            $value_exists = [];
            foreach ($loyalty_cards_names as $loyalty_cards_name) {
                if ($loyalty_cards_name) {
                    $value_exists[] = $loyalty_cards_name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Loyalty Cards')->first();
            if (count($value_exists) > 0) {
                $this->validate($request, [
                    'loyalty_cards_names.0' => 'required|string',
                ]);
                foreach ($loyalty_cards_names as $ke => $loyalty_cards_name) {
                    if ($loyalty_cards_name) {                         
                        NomenclatureTranslation::updateOrCreate(['language_id' => $loyalty_cards_language_ids[$ke], 'nomenclature_id' => $nomenclature->id], ['name' => $loyalty_cards_name]);
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (count($takeaway_names) > 0) {
            $names_value_exists = [];
            foreach ($takeaway_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Takeaway')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'takeaway_names.0' => 'required|string',
                ]);
                $takeaway_language_ids = $request->takeaway_language_ids;
                foreach ($takeaway_names as $takeaway_key => $takeaway_name) {
                    if ($takeaway_name) {                        
                        NomenclatureTranslation::updateOrCreate(['language_id' => $takeaway_language_ids[$takeaway_key], 'nomenclature_id' => $nomenclature->id], ['name' => $takeaway_name]);
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (count($search_names) > 0) {
            $names_value_exists = [];
            foreach ($search_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Search')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'search_names.0' => 'required|string',
                ]);
                $search_language_ids = $request->search_language_ids;
                foreach ($search_names as $takeaway_key => $search_name) {
                    if ($search_name) {                        
                        NomenclatureTranslation::updateOrCreate(['language_id' => $search_language_ids[$takeaway_key], 'nomenclature_id' => $nomenclature->id], ['name' => $search_name]);
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (count($wishlist_names) > 0) {
            $names_value_exists = [];
            foreach ($wishlist_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Wishlist')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'wishlist_names.0' => 'required|string',
                ]);
                $wishlist_language_ids = $request->wishlist_language_ids;
                foreach ($wishlist_names as $wishlist_key => $wishlist_name) {
                    if ($wishlist_name) {                        
                        NomenclatureTranslation::updateOrCreate(['language_id' => $wishlist_language_ids[$wishlist_key], 'nomenclature_id' => $nomenclature->id], ['name' => $wishlist_name]);
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (count($dinein_names) > 0) {
            $names_value_exists = [];
            foreach ($dinein_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Dine-In')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'dinein_names.0' => 'required|string',
                ]);
                $dinein_language_ids = $request->dinein_language_ids;
                foreach ($dinein_names as $dinein_key => $dinein_name) {
                    if ($dinein_name) {                        
                        NomenclatureTranslation::updateOrCreate(['language_id' => $dinein_language_ids[$dinein_key], 'nomenclature_id' => $nomenclature->id], ['name' => $dinein_name]);
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (count($delivery_names) > 0) {
            $names_value_exists = [];
            foreach ($delivery_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Delivery')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'delivery_names.0' => 'required|string',
                ]);
                $delivery_language_ids = $request->delivery_language_ids;
                foreach ($delivery_names as $delivery_key => $delivery_name) {
                    if ($delivery_name) {                        
                        NomenclatureTranslation::updateOrCreate(['language_id' => $delivery_language_ids[$delivery_key], 'nomenclature_id' => $nomenclature->id], ['name' => $delivery_name]);
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (count($zipCode_names) > 0) {
            $names_value_exists = [];
            foreach ($zipCode_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Zip Code')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'zipCode_name.0' => 'required|string',
                ]);
                $zipCode_language_ids = $request->zipCode_language_ids;
                foreach ($zipCode_names as $zipCode_key => $zipCode_name) {
                    if ($zipCode_name) {                        
                        NomenclatureTranslation::updateOrCreate(['language_id' => $zipCode_language_ids[$zipCode_key], 'nomenclature_id' => $nomenclature->id], ['name' => $zipCode_name]);
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (!empty($wantToTip_names) > 0) {
            $names_value_exists = [];
            foreach ($wantToTip_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Want To Tip')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'wantToTip_name.0' => 'required|string',
                ]);
                $wantToTip_language_ids = $request->wantToTip_language_ids;
                foreach ($wantToTip_names as $wantToTip_key => $wantToTip_name) {
                    if ($wantToTip_name) {                        
                        //echo $wantToTip_name;
                        NomenclatureTranslation::updateOrCreate(['language_id' => $wantToTip_language_ids[$wantToTip_key], 'nomenclature_id' => $nomenclature->id], ['name' => $wantToTip_name]);
                    }
                }
                //dd("reached");
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        if (!empty($FixedFee_names) > 0) {
            $names_value_exists = [];
            foreach ($FixedFee_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'FixedFee_name.0' => 'required|string',
                ]);
                $FixedFee_language_ids = $request->FixedFee_language_ids;
                foreach ($FixedFee_names as $FixedFee_key => $FixedFee_name) {
                    if ($FixedFee_name) {
                        $nomenclature = NomenClature::where('label', 'Fixed Fee')->first();
                        //echo $FixedFee_name;
                        NomenclatureTranslation::updateOrCreate(['language_id' => $FixedFee_language_ids[$FixedFee_key], 'nomenclature_id' => $nomenclature->id], ['name' => $FixedFee_name]);
                    }
                }
                //dd("reached");
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        
        if (count($royo_dispatcher_names) > 0) {
            $names_value_exists = [];
            foreach ($royo_dispatcher_names as $name) {
                if ($name) {
                    $names_value_exists[] = $name;
                }
            }
            $nomenclature = NomenClature::where('label', 'Royo Dispatcher')->first();
            if (count($names_value_exists) > 0) {
                $this->validate($request, [
                    'royo_dispatcher_names.0' => 'required|string',
                ]);
                $royo_dispatcher_language_ids = $request->royo_dispatcher_language_ids;
                foreach ($royo_dispatcher_names as $royo_dispatcher_key => $royo_dispatcher_name) {
                    if ($royo_dispatcher_name) {                        
                        NomenclatureTranslation::updateOrCreate(['language_id' => $royo_dispatcher_language_ids[$royo_dispatcher_key], 'nomenclature_id' => $nomenclature->id], ['name' => $royo_dispatcher_name]);
                    }
                }
            } else {
                NomenclatureTranslation::where('nomenclature_id', $nomenclature->id)->delete();
            }
        }
        return redirect()->route('configure.customize')->with('success', 'Nomenclature Saved Successfully!');
    }
}