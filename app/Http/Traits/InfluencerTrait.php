<?php

namespace App\Http\Traits;

use App\Models\{InfluencerKyc};
use Illuminate\Support\Facades\Storage;
use Auth;
use Illuminate\Support\Facades\Validator;

trait InfluencerTrait
{
    public static function saveKycData($request, $user_id = 0)
    {
        if($user_id == 0){
            $user_id = Auth::user()->id;
        }
        //\Log::info("asdfads");
        $folder = str_pad($user_id, 8, '0', STR_PAD_LEFT);
        $folder = 'client_' . $folder;
        $adhar_front = '';
        if ($request->hasFile('adhar_front')) {
            $file = $request->file('adhar_front');
            $file_name = uniqid() . '.' . $file->getClientOriginalExtension();
            $s3filePath = '/assets/' . $folder . '/' . $file_name;
            $path = Storage::disk('s3')->put($s3filePath, $file, 'public');
            $adhar_front = $path;
        }

        $adhar_back = '';
        if ($request->hasFile('adhar_back')) {
            $file = $request->file('adhar_back');
            $file_name = uniqid() . '.' . $file->getClientOriginalExtension();
            $s3filePath = '/assets/' . $folder . '/' . $file_name;
            $path = Storage::disk('s3')->put($s3filePath, $file, 'public');
            $adhar_back = $path;
        }
        $influencer_kyc = new InfluencerKyc;
        $influencer_kyc->user_id = $user_id;
        $influencer_kyc->account_name = $request->account_name;
        $influencer_kyc->bank_name = $request->bank_name;
        $influencer_kyc->account_number = $request->account_number;
        $influencer_kyc->ifsc_code = $request->ifsc_code;
        $influencer_kyc->adhar_front = $adhar_front;
        $influencer_kyc->adhar_back = $adhar_back;
        $influencer_kyc->adhar_number = $request->adhar_number;
        $influencer_kyc->upi_id = $request->upi_id;
        $influencer_kyc->is_approved = 0;
        $influencer_kyc->save();
        return $influencer_kyc;
    }
}
