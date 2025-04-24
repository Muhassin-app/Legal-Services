<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\FrontController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use Session;
use DB, Log;
use App\Http\Traits\ApiResponser;
use App\Models\{ProductVariant, Product, Variant, ClientPreference};
use App\Http\Traits\SquareInventoryManager;

class SquareInventoryController extends FrontController
{
    use ApiResponser, SquareInventoryManager;
    

    /******************    ---- order status update from dispatch (Need to dispatcher_status_option_id ) -----   ******************/
    public function squareInventoryEventUpdate(Request $request, $domain = '')
    {
        try {
            DB::beginTransaction();
            $getAdditionalPreference     = getAdditionalPreference(['square_enable_status', 'square_credentials']);
            $square_credentials          = json_decode($getAdditionalPreference['square_credentials'], true);
            $location_id                 = isset($square_credentials['location_id']) ? $square_credentials['location_id'] : '';
            if($request->type == 'inventory.count.updated' && $getAdditionalPreference['square_enable_status'] == 1)
            {
                
                if(isset($request->data['object']['inventory_counts']) && !empty($request->data['object']['inventory_counts']))
                {
                    foreach($request->data['object']['inventory_counts'] as $Object){
                        if(!empty($Object) && $location_id == $Object['location_id'] && $Object['catalog_object_type'] == "ITEM_VARIATION"){
                            $variantdata = ProductVariant::where('square_variant_id', '=', $Object['catalog_object_id'])->update(['quantity' => $Object['quantity']]);
                        }
                    }
                }
                
            }

            if($request->type == 'catalog.version.updated' && $getAdditionalPreference['square_enable_status'] == 1)
            {
                $timestamp_version_update = $request->data['object']['catalog_version']['updated_at'];
                $res = $this->searchCatalogObjects($timestamp_version_update);
            }
           

            DB::commit();
            return $this->errorResponse("Item Variant quantity updated", 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), $e->getCode());

        }
    }
    
}
