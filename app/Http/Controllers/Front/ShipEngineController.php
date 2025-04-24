<?php
namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;

use App\Http\Traits\ShipEngineTrait;
use Illuminate\Support\Facades\Request;

class ShipEngineController extends Controller
{
	
	use ShipEngineTrait;


	public function getEstimateFee($data)
	{
		return $this->shipEngineRateEstimate($data);
	}

	public function getShippingFee($data)
	{
		return $this->getLabelFee($data);
	}

    public function placeOrderRequest($data)
    {
		return $this->getLabelFee($data);
    }

	public function trackingUrl($label_id)
	{
		return $this->trackingUrlByLabelId($label_id);
	}

	public function webhook(Request $request)
	{
		\Log::info($request->all());
	}
}
