<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\FrontController;
use App\Http\Traits\ApiResponser;
use App\Models\Rider;
use Auth;

class RiderController extends FrontController
{
	use ApiResponser;
	private $riderObj;
    public function __construct(Rider $rider)
    {
        $this->riderObj = $rider;
    }
    public function addRider(Request $request){
    	$data = $request->all();
        // return $data['phone_number'];
        if(empty($data['phone_number']))
        {
            $all_riders = $this->riderObj->getAllByUserId(Auth::user()->id);
    	    return response()->json(['riders' => $all_riders],200);
        }

    	$data['user_id'] = Auth::user()->id;
        $data['phone_number'] = '+'.$data['dial_code'].$data['phone_number'];
        $data['dial_code'] = $data['dial_code'];
        $data['email'] = $data['email'] ?? null;
    	$add = $this->riderObj->createRider($data);
    	$all_riders = $this->riderObj->getAllByUserId($data['user_id']);
    	return response()->json(['riders' => $all_riders],200);
    }
    public function removeRider(Request $request)
    {
    	$delete = $this->riderObj->deleteRider($request->rider_id);
    	$rider_count = $this->riderObj->getCount(Auth::user()->id);
    	return response()->json(['rider_count' => $rider_count],200);
    }
}
