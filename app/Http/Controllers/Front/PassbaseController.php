<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\FrontController;
use Illuminate\Http\Request;
use App\Models\{VerificationOption, UserVerfication, UserVerificationResource};  
use Log, Auth;

class PassbaseController extends FrontController 
{
	use \App\Http\Traits\PassbaseManager;
	use \App\Http\Traits\ApiResponser;
	private $publish_key;
  	private $secret_key;
  	private $userVerificationObj;
    private $resourceObj;
  	public function __construct(UserVerfication $userVerfication, UserVerificationResource $resource)
	{
		$this->userVerificationObj = $userVerfication;
        $this->resourceObj = $resource;
		$passbase_creds = VerificationOption::select('credentials','test_mode')->where('code','passbase')->where('status',1)->first();
        $creds_arr = json_decode($passbase_creds->credentials);
	    $this->publish_key = $creds_arr->publish_key ?? '';
	    $this->secret_key = $creds_arr->secret_key ?? '';
	}
	public function index(Request $request)
	{
        $data = $request->all();
        $data['redirect_url'] = route('userHome');
        if(url()->previous() == route('showCart'))
        {
            $data['redirect_url'] = route('showCart');
        }
		$data['publish_key'] = $this->publish_key;
		return view('frontend.passbase')->with('data',$data);
	}
    public function storeAuthkey(Request $request)
    {
    	$response = $this->getIdentity($request->identityAccessKey);
    	$add = $this->userVerificationObj->addVerification([
    		'verification_option_id' => 1,
    		'user_id' => Auth::user()->id,
    		'response_id' => $response['id'],
    		'status' => $response['status']
    	]);
        foreach($response['resources'] as $resource)
        {
            $addResource = $this->resourceObj->addResource([
                'user_verification_id' => $add->id,
                'type' => $resource['type'],
                'datapoints' => json_encode($resource['datapoints'])
            ]);
        }
    	return $this->successResponse($response); 
    }
    public function webhook(Request $request)
    {
    	$data = $request->all();
		if($data['event'] == "VERIFICATION_REVIEWED")
		{
			$update_status = $this->userVerificationObj->updateStatus([
    			'verification_option_id' => 1,
    			'response_id' => $data['key'],
    			'status' => $data['status']
    		]);
		}
    } 
}
