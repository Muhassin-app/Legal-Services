<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\YachtTrait;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;

class YachtController extends Controller
{
    use YachtTrait,ApiResponser;
    public function productsSearchResult(Request $request)
    {
        $data = [];
        $pickup = $request->pickup ?? (object) [];
        $dropOff = $request->dropOff ?? (object) [];
        $category_id = $request->category_id ?? null;
        $data = $this->productSearch($request, (object) $pickup, (object) $dropOff,$category_id);
   
        $fields = [];
        foreach ($data['products'] as $products) {
            $allReviews = array_column($products->vendor->products()->with('reviews')->get()->toArray(),'reviews');
            $rating = array_sum(array_column($allReviews,'rating'));
            $products->rating = $rating;
    
            foreach ($products->ProductAttribute as $productAttribute) {
                if ($productAttribute->attributeOption()->exists()) {
                    if(!empty($title = $productAttribute->attributeOption->title)){
                        $fields[$productAttribute->key_name] = $title;
                    }else{
                        $fields[$productAttribute->key_name] = $productAttribute->key_value;
                    }
                }
            }
            if($request->service == 'rental'){
                $products->transmission = $fields['Transmission'] ?? '';
                $products->fuel_type = $fields['Fuel Type'] ?? '';
                $products->Seats = $fields['Seats']  ?? '';
            }else{
                $products->cabins = $fields['Cabins']. ' Cabins' ?? '0' ;
                $products->baths = $fields['Baths']. ' Baths' ?? '0' ;
                $products->berths = $fields['Berths'].' Berths' ?? '0';
            }
        }
        return response()->json(['status' => 200, 'message' => 'Product List', 'data' => $data]);
    }

    public function checkProductAvailability(Request $request, $id)
    {
        $pickup_time = $request->start_time ?? '';
        $drop_time = $request->end_time ?? '';
        $pickup_lat = $request->pickup_latitude ?? '';
        $pickup_lng = $request->pickup_longitude ?? '';
        $products= Product::whereDoesntHave('productBooked', function($q) use($pickup_time, $drop_time){
            if (!empty($pickup_time) ) {
                $q->WhereRaw("DATE(end_date_time) >= ?", [$pickup_time]);
            }
            if (!empty($drop_time)) {
                $q->whereRaw("DATE(start_date_time) <= ?", [$drop_time]);
            }
        })->where(function ($q) use ($request, $pickup_lat, $pickup_lng) {
            if (isset($pickup_lat) && isset($pickup_lng)) {
                $q->whereHas('vendor.serviceArea', function ($q) use ($pickup_lat,$pickup_lng) {
                    $q->select('id', 'vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $pickup_lat . " " . $pickup_lng . ")'))");
                });
            }

        })
        ->where('id',$id)->first();
    
        if ($products) {
            return $this->successResponse(null,'Product Available',200);
        } else {
            return $this->errorResponse('Product Not Available',400);
        }
    }
}
