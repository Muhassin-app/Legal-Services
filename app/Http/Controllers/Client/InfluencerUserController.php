<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\InfluencerKyc;
use App\Models\InfluencerTier;
use App\Models\InfluencerUser;
use App\Models\ReferEarnDetail;
use App\Models\Promocode;
use Illuminate\Http\Request;
use Session;
use Carbon\Carbon;

class InfluencerUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getUploadedData(Request $request)
    {
        // $request 
        $id = $request->did;
        $influencer_tier = InfluencerTier::where('status', 1)->get();
        $influencer_uploaded_detail = ReferEarnDetail::where('influencer_user_id', $request->did)->get();
        $submitUrl = route('influencer-user.approveReject');
        $returnHTML = view('backend.influencer.approveReject')->with(['id' => $id,'influencer_tier' => $influencer_tier, 'influencer_uploaded_detail' => $influencer_uploaded_detail])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML, 'submitUrl' => $submitUrl));
        // dd($influencer_uploaded_detail);
    }

    public function approveReject(Request $request)
    {
        if($request->approveRejectSubmit){ //Approve
            $promo = 'Promo'.rand(1000,9999);
            $influencer_tier = InfluencerTier::find($request->tier);
            $expiryDateTime = Carbon::now()->addYear(5);
            $promo_data = [
                'name' => $promo,
                'amount' => $influencer_tier->commision??0,
                'promo_type_id' => $influencer_tier->commision_type,
                'limit_per_user' => 1,
                'limit_total' => 1,
                'added_by' => 1,
                'promo_visibility' => 'private',
                'promo_type' => 1,
                'expiry_date' => $expiryDateTime
            ];
            InfluencerUser::where('id', $request->id)->update(['is_approved'=> 1, 'reffered_code' => $promo, 'influencer_tier_id' => $request->tier, 'commision_type' => $influencer_tier->commision_type, 'commision' => $influencer_tier->commision]);
            Promocode::create($promo_data);
            Session::flash('success', 'Influencer request approved');
        }else{ //Reject
            InfluencerUser::where('id', $request->id)->update(['is_approved'=> 2]);
            Session::flash('success', 'Influencer request rejected');
        }
        return redirect()->back();
    }

    public function getKycData(Request $request)
    {
        // $request 
        $influencer_user  = InfluencerUser::with('kyc')->where('id', $request->did)->first();
        
        $returnHTML = view('backend.influencer.kyc_modal_data')->with(['influencer_user' => $influencer_user])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML));
        // dd($influencer_uploaded_detail);
    }
}
