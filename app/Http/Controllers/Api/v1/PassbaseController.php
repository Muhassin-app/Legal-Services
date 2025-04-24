<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{VerificationOption, UserVerfication, UserVerificationResource,User};  
use Log, Auth; 

class PassbaseController extends BaseController 
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
	}
    public function storeAuthkey(Request $request) 
    {
    	$response = $this->getIdentity($request->identityAccessKey);
        $user = User::where('auth_token',$request->header('authorization'))->first();
    	$add = $this->userVerificationObj->addVerification([
    		'verification_option_id' => 1,
    		'user_id' => $user->id??12,
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
}
