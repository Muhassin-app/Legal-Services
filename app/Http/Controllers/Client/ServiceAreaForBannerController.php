<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{ServiceAreaForBanner};

class ServiceAreaForBannerController extends BaseController{

    use ApiResponser;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $domain = ''){
        $rules = array(
            'name' => 'required',
            'latlongs' => 'required'
        );
        $messages = array(
            'name.required' => 'Area name is required',
            'latlongs.required' => 'Service area is required',
        );
        $validation  = Validator::make($request->all(), $rules, $messages);

        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $area = new ServiceAreaForBanner();
        if($request->has('area_id')){
            $area = ServiceAreaForBanner::where('id', $request->area_id)->first();
        }
        $latlng = str_replace('),(', ';', $request->latlongs);
        $latlng = str_replace(')', '', $latlng);
        $latlng = str_replace('(', '', $latlng);
        $latlng = str_replace(', ', ' ', $latlng);
        $codsArray = explode(';', $latlng);
        $latlng = implode(', ', $codsArray);
        $latlng = $latlng. ', ' . $codsArray[0];
        $area->name             = $request->name;
        $area->geo_array        = $request->latlongs;
        $area->zoom_level       = $request->zoom_level;
        $area->description      = $request->description;
        $area->type             = $request->type ?? 1;
        $area->polygon          = \DB::raw("ST_GEOMFROMTEXT('POLYGON((".$latlng."))')");
        $area->save();

        return redirect()->back()->with('success', 'Service area saved successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceAreaForBanner  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $domain = '', $id){
        $area = ServiceAreaForBanner::where('id', $id)->first();
        $returnHTML = view('backend.banner.editArea')->with(['area' => $area])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML, 'zoomLevel'  => $area->zoom_level, 'coordinate'  => $area->geo_array));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceAreaForBanner  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id){
        $rules = array(
            'name' => 'required',
            'latlongs_edit' => 'required'
        );
        $messages = array(
            'name.required' => 'Area name is required',
            'latlongs_edit.required' => 'Service area is required',
        );
        $validation  = Validator::make($request->all(), $rules, $messages);

        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $area = ServiceAreaForBanner::where('id', $id)->firstOrFail();
        $latlng = str_replace('),(', ';', $request->latlongs_edit);
        $latlng = str_replace(')', '', $latlng);
        $latlng = str_replace('(', '', $latlng);
        $latlng = str_replace(', ', ' ', $latlng);
        $codsArray = explode(';', $latlng);
        $latlng = implode(', ', $codsArray);
        $latlng = $latlng. ', ' . $codsArray[0];
        $area->name           = $request->name;
        $area->description    = $request->description;
        $area->geo_array      = $request->latlongs_edit;
        $area->zoom_level     = $request->zoom_level_edit;
        $area->type           = $request->type ?? 1;
        $area->polygon        = \DB::raw("ST_GEOMFROMTEXT('POLYGON((".$latlng."))')");
        $area->save();
        return redirect()->back()->with('success', 'Service area updated successfully!');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceAreaForBanner  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $domain = '', $id){
        $area = ServiceAreaForBanner::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Service area deleted successfully!');
    }


    # draw a circle with radius in vendor service area
    public function drawCircleWithRadius(Request $request, $domain = '')
    {
        $client_preference_detail =   getClientPreferenceDetail();

        if(isset($client_preference_detail->Default_latitude) && !empty($client_preference_detail->Default_latitude) && isset($client_preference_detail->Default_longitude) && !empty($client_preference_detail->Default_longitude)) {
            $xCoords = [];
            $yCoords = [];
            $points = [];
            $centerX = $client_preference_detail->Default_latitude;
            $centerY = $client_preference_detail->Default_longitude;
            $steps = 30;
            $distance = $request->radius;

            if($client_preference_detail->distance_unit_for_time == 'kilometer'){
                $distance = $distance / 1.609344 ;
            }

            $for_lat_deg = (1/69)*$distance;
            $for_lng_deg = (1/54)*$distance;
                for ($i = 0; $i < $steps; $i++) {

                    $x = ($centerX + $for_lat_deg * cos(2 * pi() * ($i / $steps)));
                    $y = ($centerY + $for_lng_deg * sin(2 * pi() * ($i / $steps)));
                    $point = "(".$x.", ".$y.")";
                    array_push($points,$point);

            }
            $pointse = implode(',',$points);
            $latlng = str_replace('),(', ';', $pointse);
            $latlng = str_replace(')', '', $latlng);
            $latlng = str_replace('(', '', $latlng);
            $latlng = str_replace(', ', ' ', $latlng);
            $codsArray = explode(';', $latlng);
            $latlng = implode(', ', $codsArray);
            $latlng = $latlng. ', ' . $codsArray[0];

            $area = new ServiceAreaForBanner();
            $area->name             = "Area - ".$request->radius." ".$client_preference_detail->distance_unit_for_time." Radius";
            $area->geo_array        = $pointse;
            $area->zoom_level       = $request->zoom_level ?? 13;
            $area->description      = $request->description??null;
            $area->type             = $request->type ?? 1;
            $area->polygon          = \DB::raw("ST_GEOMFROMTEXT('POLYGON((".$latlng."))')");
            $area->save();


            return redirect()->back()->with('success', 'Service area updated successfully!');
        }

        return redirect()->back()->with('error', 'Error in create Service area!');

    }
}
