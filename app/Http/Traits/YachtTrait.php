<?php

namespace App\Http\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\{Category, PaymentOption, Product, UserAddress, Vendor, VerificationOption};
use Illuminate\Support\Facades\Auth;

trait YachtTrait
{
    public function productSearch($request, $pickup, $dropOff,$category_id = null)
    {


        $pickup_time = $pickup->time ?? '';
        $drop_time = $dropOff->time ?? '';
        $clientPreference = \App\Models\ClientPreference::where(['id' => 1])->first();
        if ($request->service == 'airport') {
            $mapKey = '';
            if ($clientPreference && !empty($clientPreference->map_key)) {
                $mapKey = $clientPreference->map_key;
            }
            $response = \Http::get("https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$pickup->latitude,$pickup->longitude&rankby=distance&type=airport&key=$mapKey")['results'];
            $data['products'] = collect($response)->map(function ($result) {
                return [
                    'title' => $result['name'],
                    'path' => $result['icon'],
                    'location' => $result['vicinity'],
                    'latitude' => $result['geometry']['location']['lat'],
                    'longitude' => $result['geometry']['location']['lng'],
                ];
            });
        }
        $category = Category::where('slug', $request->service)->first();

        $data['products'] = [];
        if ($category) {
            $products= Product::with([
                'variant', 'media.image',
                'ProductAttribute' => function ($q) use ($request) {
                    if ($request->service == 'rental') {
                        $q->whereIn('key_name', ['Transmission', 'Fuel Type', 'Seats']);
                    } else {
                        $q->whereIn('key_name', ['Cabins', 'Berths', 'Baths']);
                    }
                },
                'ProductAttribute.attributeOption:id,title',
            ])
            ->whereDoesntHave('productBooked', function($q) use($pickup_time, $drop_time){
                if (!empty($pickup_time) ) {
                    $q->WhereRaw("DATE(end_date_time) >= ?", [$pickup_time]);
                }
                if (!empty($drop_time)) {
                    $q->whereRaw("DATE(start_date_time) <= ?", [$drop_time]);
                }
            })
                ->where(function ($q) use ($request) {
                    if ($request->has('seats') && !empty($request->seats)) {
                        $q->whereHas('ProductAttribute', function ($q) use ($request) {
                            if ($request->service == 'rental') {
                                $q->where('key_name', 'Seats')->where('key_value', '>=', $request->seats);
                            } else {
                                $q->where('key_name', 'Berths')->where('key_value', '>=', $request->seats);
                            }
                        });
                    }

                })
                ->where(function ($q) use ($request, $pickup, $dropOff) {
                    if (isset($pickup->latitude) && isset($pickup->longitude)) {
                        $q->whereHas('vendor.serviceArea', function ($q) use ($pickup) {
                            $q->select('id', 'vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $pickup->latitude . " " . $pickup->longitude . ")'))");
                        });
                    }

                    if ($request->has('diff-location') && !empty($dropOff->latitude) && !empty($dropOff->longitude)) {
                        $q->whereHas('vendor.serviceArea', function ($q) use ($dropOff) {
                            $q->select('id', 'vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $dropOff->latitude . " " . $dropOff->longitude . ")'))");
                        });
                    }
                })
                ->with('vendor', function ($q) use ($pickup, $clientPreference) {
                    if (!empty($pickup->latitude) && !empty($pickup->longitude)) {
                        $q->distanceInMeters($pickup->latitude, $pickup->longitude);
                    } else {
                        // $q->distanceInMeters($clientPreference->Default_latitude, $clientPreference->Default_longitude);
                    }
                });

                if($category_id)
                {
                    $products = $products->where('category_id', $category_id);
                } else {
                    $products = $products->where('category_id', $category->id);
                }
                $products =  $products->get();
                $data['products'] = $products;
        }


        $data['service'] = $request->service;
        $data['pick_drop_time'] = !empty($pickup_time && $drop_time) ? date('d M Y H:i', strtotime($pickup_time)).' to '.date('d M Y H:i', strtotime($drop_time)) : '';
        $data['pickup_time'] = $pickup_time;
        $data['drop_time'] = $drop_time;
        $data['category'] = $category;
        $data['pickup'] = $pickup;
        $data['dropoff'] = $dropOff;
        $data['diff_location'] = $request->diff_location ?? 0;
        $data['seats'] = $request->seats ?? '';
        return $data;
    }
}
