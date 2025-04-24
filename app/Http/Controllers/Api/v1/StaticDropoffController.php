<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{StaticDropoffLocation}; 
use Log;
//use App\Http\Traits\MpesaStkpush;

class StaticDropoffController extends BaseController
{
    use ApiResponser;

    public function getStaticLocation(Request $request)
    {
        try{
            $staticLocationQuery = StaticDropoffLocation::query();
            if($request->has('search')){
                $keyword = $request->search;
                $staticLocationQuery =  $staticLocationQuery->where(function ($q1) use ($keyword) {
                                            $q1->where('title', 'LIKE', '%' . $keyword . '%')
                                            ->orWhere('address', 'LIKE', '%' . $keyword . '%');
                                        });
            }
            $staticLocation = $staticLocationQuery->get();
            return $this->successResponse($staticLocation, '', 200);
        }
        catch(Exception $ex){
            return $this->errorResponse($ex->getMessage(), $ex->getCode());
        }
    }


   
}
