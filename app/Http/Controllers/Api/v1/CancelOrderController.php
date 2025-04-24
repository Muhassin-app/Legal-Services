<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnReason;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponser;
class CancelOrderController extends Controller
{
    use ApiResponser;
    public function getCancelOrderReason(Request $request){
        try {
            $user = Auth::user();
            $lang_id = $user->language;
            $cancellation_reason = ReturnReason::where(['status' => 'Active', 'type' => 3])->get();
            
            if(isset($cancellation_reason)){
                return $this->successResponse($cancellation_reason,'Cancellation Reason Data.');
            }
            return $this->errorResponse('Invalid reason', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
