<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\BaseController;
use App\Models\{InfluencerCategory, Attribute, InfluencerAttribute, ReferEarnDetail, InfluencerTier, InfluencerUser};
use App\Http\Requests\InfluencerCategoryRequest;
use Auth;

class InfluencerReferAndEarnController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attributes = [];
        if( checkTableExists('influ_attributes') ) {
            // , 'varcategory.cate.primary'
            $attributes = InfluencerAttribute::with('option','translation_one', 'influencerCategory')->where('status', '!=', 2)->orderBy('position', 'asc');
            if(Auth::user()->is_superadmin) {
                $attributes = $attributes->get();
            }
            else {
                $attributes = $attributes->where('user_id', Auth::id())->get();
            }
            
        }
        
        $influencer_list = InfluencerCategory::paginate(10);

        $influencer_tier_list = InfluencerTier::paginate(10);
        // dd($influencer_tier_list);
        return view('backend.influencerreferandearn.index')->with(['influencer_list' => $influencer_list, 'attributes' => $attributes, 'influencer_tier_list'=> $influencer_tier_list]);
    }

    function edit($domain ,$id) {
        
        $influence_edit = InfluencerCategory::where('id', $id)->first();
        
        if( !empty($influence_edit) ) {
            return view('backend.influencerreferandearn.create-edit')->with(['influence_edit' => $influence_edit]);
        }
        return redirect()->route('influencer-refer-earn.index');
    }

    function create(Request $request) {
        return view('backend.influencerreferandearn.create-edit')->with(['influence_edit' => '']);
    }

    function store(InfluencerCategoryRequest $request) {
        try {
            InfluencerCategory::create([
                'name' => $request->name,
                'kyc' => ($request->kyc)?1:0,
                'is_active' => 1
            ]);

            return redirect()->route('influencer-refer-earn.index');
        }
        catch(\Exception $e) {
            return redirect()->route('influencer-refer-earn.index');
        }
    }

    function update(InfluencerCategoryRequest $request) {
        
        try {
            InfluencerCategory::where('id', $request->id)->update([
                'name' => $request->name,
                'kyc' => ($request->kyc)?1:0,
                'is_active' => 1
            ]);
            return redirect()->route('influencer-refer-earn.index');
        }
        catch(\Exception $e) {
            return redirect()->route('influencer-refer-earn.index');
        }
    }

    function userList(Request $request) {
        $influencer_users = InfluencerUser::with(['user', 'tier', 'kyc'])->paginate(10);
        return view('backend.influencerreferandearn.user-list')->with(['influencer_users' => $influencer_users]);
    }

    function editInfluencerUser(Request $request){
        if($request->ajax()){
            $influencer_users = InfluencerUser::with(['user', 'tier'])->where('id', $request->influencer_user_id)->first();
            $returnHTML = view('backend.influencerreferandearn.edit-influencer-user-ajax')->with('influencer_users', $influencer_users)->render();
            return response()->json(array('success' => true, 'html'=>$returnHTML));
        }
    }

    function updateUserCommision(Request $request){
        $influencer_user_id = $request->influencer_user_id;
        $commision_type = $request->commision_type;
        $commision = $request->commision;
        $updateInfluencerUser = InfluencerUser::where('id', $influencer_user_id)->update(['commision_type' => $commision_type,'commision' => $commision]);
        if($updateInfluencerUser){
            return redirect()->back()->withSuccess('User updated successfully');
        }else{
            return redirect()->back()->withError('Something went wrong');
        }
    }
}
